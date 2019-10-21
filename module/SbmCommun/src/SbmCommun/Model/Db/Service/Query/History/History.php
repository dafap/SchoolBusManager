<?php
/**
 * Requêtes sur la table système `history`
 *
 * Description longue du fichier s'il y en a une
 * 
 * @project sbm
 * @package SbmCommun/Model/Db/Service/Query/History
 * @filesource History.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 21 oct. 2019
 * @version 2019-2.4.11
 */
namespace SbmCommun\Model\Db\Service\Query\History;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Db\ResultSet\HydratingResultSet;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Literal;
use Zend\Db\Sql\Where;
use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\DbSelect;
use SbmCommun\Model\Db\Service\DbManager;
use SbmCommun\Model\Db\Exception;

class History implements FactoryInterface
{

    /**
     *
     * @var \SbmCommun\Model\Db\Service\DbManager
     */
    private $db_manager;

    /**
     *
     * @var \Zend\Db\Adapter\Adapter
     */
    private $dbAdapter;

    /**
     *
     * @var string
     */
    private $history_name;

    /**
     *
     * @var \Zend\Db\Sql\Sql
     */
    private $sql;

    /**
     * Renvoie la chaine de requête (après l'appel de la requête)
     *
     * @param \Zend\Db\Sql\Select $select            
     *
     * @return \Zend\Db\Adapter\mixed
     */
    public function getSqlString($select)
    {
        return $select->getSqlString($this->dbAdapter->getPlatform());
    }

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        if (! ($serviceLocator instanceof DbManager)) {
            $message = 'SbmCommun\Model\Db\Service\DbManager attendu. %s reçu.';
            throw new Exception(sprintf($message, gettype($serviceLocator)));
        }
        $this->db_manager = $serviceLocator;
        $this->dbAdapter = $this->db_manager->getDbAdapter();
        $this->sql = new Sql($this->dbAdapter);
        $this->history_name = $this->db_manager->getCanonicName('history', 'sys');
        return $this;
    }

    /**
     * Changements du dernier jour
     * On vérifie si la table affectation a été modifié pour le millesime indiqué
     *
     * @return \Zend\Db\Adapter\Driver\ResultInterface
     */
    public function getLastDayChanges($table_name, $millesime)
    {
        $select = $this->sql->select($this->history_name);
        $table_affectations = $this->db_manager->getCanonicName($table_name);
        $hier = date('Y-m-d H:i', strtotime('-1 day'));
        $where = new Where();
        $where->equalTo('table_name', $table_affectations)
            ->greaterThanOrEqualTo('dt', $hier)
            ->like('id_txt', "$millesime%");
        $select->columns(array(
            'id_txt',
            'log'
        ))
            ->where($where)
            ->order(array(
            'dt'
        ));
        $statement = $this->sql->prepareStatementForSqlObject($select);
        return $statement->execute();
    }

    public function getPaiementsChanges(int $exercice, int $paiementId = null)
    {
        $statement = $this->sql->prepareStatementForSqlObject(
            $this->selectPaiementsChanges($exercice, $paiementId));
        return $statement->execute();
    }

    public function paginatorPaiementsChanges(int $exercice, int $paiementId = null)
    {
        return new Paginator(
            new DbSelect($this->selectPaiementsChanges($exercice, $paiementId), 
                $this->db_manager->getDbAdapter(), new HydratingResultSet()));
    }

    /**
     * SELECT * FROM sbm_s_history WHERE table_name='sbm_t_paiements' AND id_txt=2019 AND
     * (action = 'delete' OR (action = 'update' AND (id_int, dt) NOT IN ( SELECT id_int,
     * dt FROM `sbm_s_history` WHERE table_name='sbm_t_paiements' AND id_txt=2019 AND
     * `action`='delete')))
     *
     * @param int $exercice            
     * @param int $paiementId            
     * @return \Zend\Db\Sql\Select
     */
    private function selectPaiementsChanges(int $exercice, int $paiementId = null)
    {
        // sous requête 'les delete'
        $where1 = new Where();
        $where1->equalTo('table_name', $this->db_manager->getCanonicName('paiements'))
            ->equalTo('action', 'delete')
            ->equalTo('id_txt', $exercice);
        $select1 = $this->sql->select($this->history_name)
            ->columns([
            'id_int',
            'dt'
        ])
            ->where($where1);
        // requête principale
        $where = new Where();
        $where->equalTo('table_name', $this->db_manager->getCanonicName('paiements'))
            ->equalTo('id_txt', $exercice)
            ->nest()
            ->equalTo('action', 'delete')->OR->nest()->equalTo('action', 'update')->AND->notIn(
            [
                'id_int',
                'dt'
            ], $select1)
            ->unnest()
            ->unnest();
        if ($paiementId) {
            $where->equalTo('id_int', $paiementId);
        }
        return $this->sql->select($this->history_name)
            ->columns(
            [
                'action',
                'id_int',
                'dt',
                'log'
            ])
            ->where($where)
            ->order([
            'dt'
        ]);
    }
}