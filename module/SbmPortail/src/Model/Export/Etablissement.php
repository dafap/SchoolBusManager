<?php
/**
 * Description du fichier
 *
 * @project sbm
 * @package
 * @filesource Etablissement.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 4 déc. 2020
 * @version 2020-2.6.1
 */
namespace SbmPortail\Model\Export;

use Zend\Http\PhpEnvironment\Response;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;

class Etablissement
{

    /**
     *
     * @var Response
     */
    private $response;

    /**
     *
     * @var Spreadsheet
     */
    private $spreadsheet;

    /**
     *
     * @var boolean
     */
    private $has_column_headers;

    /**
     * Taille maxi de mémoire avant d'utiliser un fichier temporaire pour la sortie
     *
     * @var int
     */
    private $tailleMBs;

    /**
     * Nom du fichier proposé lors du téléchargement
     *
     * @var string
     */
    private $file_name;

    public function __construct(array $params = [], Response $response = null)
    {
        $this->tailleMBs = 1 * 1024 * 1024; // 1 Mo
        $this->spreadsheet = new Spreadsheet();
        $this->has_column_headers = false;
        if ($response) {
            $this->response = $response;
        } else {
            $this->response = new Response();
        }
        if (array_key_exists('file_name', $params)) {
            $this->file_name = $params['file_name'];
        }
        $properties = &$this->spreadsheet->getProperties();
        if (array_key_exists('category', $params)) {
            $properties->setCategory($params['category']);
        }
        if (array_key_exists('company', $params)) {
            $properties->setCompany($params['company']);
        }
        if (array_key_exists('created', $params)) {
            $properties->setCreated($params['created']);
        }
        if (array_key_exists('creator', $params)) {
            $properties->setCreator($params['creator']);
        }
        if (array_key_exists('description', $params)) {
            $properties->setDescription($params['description']);
        }
        if (array_key_exists('keywords', $params)) {
            $properties->setKeywords($params['keywords']);
        }
        if (array_key_exists('lastModifiedBy', $params)) {
            $properties->setLastModifiedBy($params['lastModifiedBy']);
        }
        if (array_key_exists('manager', $params)) {
            $properties->setManager($params['manager']);
        }
        if (array_key_exists('modified', $params)) {
            $properties->setModified($params['modified']);
        }
        if (array_key_exists('subject', $params)) {
            $properties->set($params['subject']);
        }
        if (array_key_exists('title', $params)) {
            $properties->set($params['title']);
        }
    }

    private function setRow(int $rang, $record)
    {
        if (is_array($record) || $record instanceof \Traversable) {
            $activeSheet = $this->spreadsheet->setActiveSheetIndex(0);
            if ($this->has_column_headers) {
                $rang ++;
            }
            $columnIndex = 0;
            foreach ($record as $value) {
                $columnIndex ++;
                if (! is_null($value)) {
                    $activeSheet->setCellValueByColumnAndRow($columnIndex, $rang, $value);
                }
            }
        } else {
            throw new \Exception('La ligne est de type incorrect.');
        }
    }

    /**
     * Place les entêtes en ligne 1 de la feuille active d'index 0
     *
     * @param array $array
     * @return self
     */
    public function setColumnHeaders(array $array): self
    {
        $this->has_column_headers = true;
        $this->setRow(0, $array);
        return $this;
    }

    /**
     *
     * @param array|\Traversable $data
     * @throws \Exception
     * @return self
     */
    public function setData($data): self
    {
        if (is_array($data) || $data instanceof \Traversable) {
            $rang = 0;
            foreach ($data as $record) {
                $rang ++;
                $this->setRow($rang, $record);
            }
        } else {
            throw new \Exception('Les données sont de type incorrect.');
        }
        return $this;
    }

    /**
     * Fixe le nom de la feuille active
     *
     * @param string $title
     * @return self
     */
    public function setSheetTitle(string $title): self
    {
        $this->spreadsheet->getActiveSheet()->setTitle($title);
        return $this;
    }

    /**
     * Renvoie la Response pour télécharger le fichier
     *
     * @return \Zend\Http\PhpEnvironment\Response
     */
    public function getResponse()
    {
        $this->response->getHeaders()->addHeaders(
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment;filename="' .
                str_replace('"', '\\"', $this->file_name) . '.xlsx"'
            ]);
        $writer = IOFactory::createWriter($this->spreadsheet, 'Xlsx');
        $fp = fopen("php://temp/maxmemory:$this->tailleMBs", 'r+');
        $writer->save($fp);
        rewind($fp);
        $this->response->setContent(stream_get_contents($fp));
        return $this->response;
    }
}