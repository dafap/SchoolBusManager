<?php
/**
 * Méthodes pour extraire les photos
 * 
 * 
 * @project sbm
 * @package SbmGestion/Model/Photos
 * @filesource Photos.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 3 fév. 2019
 * @version 2019-2.5.0
 */
namespace SbmGestion\Model\Photos;

use SbmBase\Model\DateLib;
use SbmBase\Model\StdLib;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Where;

class Photos
{

    /**
     *
     * @var array
     */
    private $config;

    /**
     *
     * @var \ZipArchive
     */
    private $zip;

    /**
     *
     * @var string
     */
    private $tmpZipFileName;

    /**
     * Code erreur de la méthode zip::open() ou true en cas de succès
     *
     * @var int
     */
    private $zipError;

    /**
     *
     * @var resource
     */
    private $csvFile;

    /**
     * Les éléments du tableau sont les paramètres nécessaires.
     * Ils sont initialisés dans PhotosFactory.
     * Chaque paramètre s'obtient comme une propriété de la classe Photos par la
     * méthode __get()
     *
     * @param array $config
     */
    public function __construct($config)
    {
        if (! is_array($config)) {
            throw new Exception(__METHOD__ . ' - Un tableau est attendu comme paramètre.');
        }
        $this->config = $config;
    }

    /**
     * Renvoie la valeur associée à la clé $param de la propriété $config
     *
     * @param string $param
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function __get($param)
    {
        if (array_key_exists($param, $this->config)) {
            return $this->config[$param];
        }
        $message = sprintf(
            'Le paramètre %s n\'est pas une propriété définie par le PhotosFactory.',
            $param);
        throw new Exception($message);
    }

    /**
     * Rechercher les eleveId à marquer
     * Marquer les dateExtraction dans scolarites pour les eleveId trouvés
     *
     * @param int $millesime
     * @param string $dateDebut
     *            date au format Y-m-d H:i:s
     * @param int $natureCarte
     */
    public function nouveauLot($millesime, $dateDebut, $natureCarte)
    {
        $now = DateLib::nowToMysql();
        $where = new Where();
        $where->lessThan('dateExtraction', $dateDebut)->in('eleveId',
            $this->selectNouveauLot($millesime, $natureCarte));

        return $this->tElevesPhotos->getTableGateway()->update(
            [
                'dateExtraction' => $now
            ], $where);
    }

    private function selectNouveauLot($millesime, $naturecarte)
    {
        // préparation du WHERE
        $where = new Where();
        $where->equalTo('millesime', $millesime);
        $or = false;
        $predicate = null;
        foreach ($this->codesNatureCartes[$naturecarte] as $code) {
            if ($or) {
                $predicate->OR;
            } else {
                $predicate = $where->nest;
                $or = true;
            }
            $predicate->equalTo('s1.natureCarte', $code)->OR->equalTo('s2.natureCarte',
                $code);
        }
        if ($or) {
            $predicate->unnest;
        }

        // préparation du SELECT DISTINCT
        $select = new Select();
        $select->columns([
            'eleveId'
        ])
            ->from([
            'aff' => $this->table_affectations
        ])
            ->join([
            's1' => $this->table_services
        ], 'aff.service1Id = s1.serviceId', [])
            ->join([
            's2' => $this->table_services
        ], 'aff.service2Id = s2.serviceId', [], Select::JOIN_LEFT)
            ->where($where)
            ->quantifier(Select::QUANTIFIER_DISTINCT);
        return $select;
    }

    /**
     * Lance l'extraction et renvoie une réponse http
     *
     * @param bool $parselection
     * @param Where $where
     */
    public function renderZip(Where $where)
    {
        $select = new Select();
        $select->columns([
            'photo'
        ])
            ->from([
            'photos' => $this->table_elevesphotos
        ])
            ->join([
            'ele' => $this->table_eleves
        ], 'photos.eleveId = ele.eleveId', [
            'nom',
            'prenom',
            'numero'
        ])
            ->where($where)
            ->order([
            'nom',
            'prenom'
        ]);
        $sql = new Sql($this->dbAdapter);
        $result = $sql->prepareStatementForSqlObject($select)->execute();
        $this->initZipArchive();
        foreach ($result as $array) {
            $this->archivePhoto($array);
            $this->addCommentaire($array['nom'], $array['prenom'], $array['numero']);
        }
        $this->archiveCommentaires();
        $this->zip->close();
        return $this->responseHttp();
    }

    private function archivePhoto($array)
    {
        $fileName = implode('-',
            [
                $this->filter($array['nom']),
                $this->filter($array['prenom']),
                $array['numero']
            ]);
        $blob = stripslashes($array['photo']);
        $this->zip->addFromString($fileName . '.jpg', $blob);
    }

    private function filter($str)
    {
        $str = preg_replace('#[\W]#', ' ', $str);
        $array = explode(' ', $str);
        foreach ($array as &$word) {
            $word = strtolower($word);
            $word = ucfirst($word);
        }
        return implode('', $array);
    }

    private function addCommentaire($nom, $prenom, $numero)
    {
        fputcsv($this->csvFile, [
            stripslashes($nom),
            $prenom,
            $numero
        ], ';', '"');
    }

    private function archiveCommentaires()
    {
        rewind($this->csvFile);
        $this->zip->addFromString('liste.csv', stream_get_contents($this->csvFile));
        fclose($this->csvFile);
    }

    private function initZipArchive()
    {
        $this->zip = new \ZipArchive();
        $this->tmpZipFileName = StdLib::findParentPath(__DIR__, 'data') . '/photos.zip';
        $this->zipError = $this->zip->open($this->tmpZipFileName,
            \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        $this->csvFile = fopen('php://memory', 'r+');
    }

    private function responseHttp()
    {
        $response = new \Zend\Http\Response\Stream();
        $response->setStream(fopen($this->tmpZipFileName, 'r'));
        $response->setStatusCode(200);

        $headers = new \Zend\Http\Headers();
        $headers->addHeaderLine('Content-Type', 'application/zip')
            ->addHeaderLine('Content-Disposition',
            'attachment; filename="' . basename($this->tmpZipFileName) . '"')
            ->addHeaderLine('Content-Length', filesize($this->tmpZipFileName));

        $response->setHeaders($headers);
        return $response;
    }
}