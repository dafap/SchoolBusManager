<?php
/**
 * Règle de sectorisation pour les collèges
 * 
 * Ici, chaque commune appartient à un seul secteur de collège.
 * A modifier si une commune est partagée entre plusieurs secteurs.
 * 
 * @project sbm
 * @package SbmGestion/Model/Db/Service/Simulation
 * @filesource SectorisationCollege.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 24 août 2018
 * @version 2018-2.4.3
 */
namespace SbmGestion\Model\Db\Service\Simulation;

use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;
use SbmCommun\Model\Db\Service\DbManager;

class SectorisationCollege
{

    /**
     *
     * @var DbManager
     */
    private $db_manager;

    /**
     *
     * @var int
     */
    private $eleveId;

    /**
     *
     * @var \Zend\Db\Sql\Sql
     */
    private $sql;

    public function __construct($db_manager, $eleveId)
    {
        $this->db_manager = $db_manager;
        $this->eleveId = $eleveId;
        $this->sql = new Sql($this->db_manager->getDbAdapter());
    }

    /**
     * Renvoie l'identifiant de l'établissement du secteur scolaire de l'élève
     *
     * @return string
     *
     * @throws Exception
     */
    public function getEtablissementId()
    {
        $tSecteursScolairesClgPu = $this->db_manager->get(
            'Sbm\Db\Table\SecteursScolairesClgPu');
        $commune = $this->getCommune();
        $where = new Where();
        $where->equalTo('communeId', $commune['communeId'])->like('etablissementId', 
            '012%');
        $result = $tSecteursScolairesClgPu->fetchAll($where);
        if ($result->count() != 1) {
            $msg = "Mauvaise configuration des secteurs scolaires de collèges.\n";
            if ($result->count()) {
                $msg = "La commune %s est dans plusieurs secteurs scolaires.";
            } else {
                $msg = "La commune %s n'a pas de secteur scolaire.";
            }
            throw new Exception(sprintf($msg, $commune['commune']));
        }
        return $result->current()->etablissementId;
    }

    /**
     * Renvoie la communeId de résidence (du responsable 1)
     *
     * @return array ['communeId' => string, 'commune' => string]
     *        
     * @throws \SbmGestion\Model\Db\Service\Simulation\Exception (Exception)
     */
    private function getCommune()
    {
        $select = new Select();
        $select->from(
            [
                'c' => $this->db_manager->getCanonicName('communes', 'table')
            ])
            ->join(
            [
                'r' => $this->db_manager->getCanonicName('responsables', 'table')
            ], 'c.communeId = r.communeId', [])
            ->join(
            [
                'e' => $this->db_manager->getCanonicName('eleves', 'table')
            ], 'e.responsable1Id = r.responsableId', [])
            ->columns(
            [
                'communeId' => 'communeId',
                'commune' => 'nom'
            ])->where->equalTo('eleveId', $this->eleveId);
        $statement = $this->sql->prepareStatementForSqlObject($select);
        $rowset = $statement->execute();
        if ($rowset->count()) {
            return $rowset->current();
        }
        $tEleves = $this->db_manager->get('Sbm\Db\Table\Eleves');
        $eleve = $tEleves->getRecord($this->eleveId);
        $msg = sprintf('La commune de l\'élève %s %s n\'a pas été trouvée.', $eleve->nom, 
            $eleve->prenom);
        throw new Exception($msg);
    }
}