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
 * @date 4 janv. 2016
 * @version 2016-1.7.1
 */
namespace SbmCommun\Model\Db\Service\Query\History;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Literal;
use Zend\Db\Sql\Where;
use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\DbSelect;

class History implements FactoryInterface
{

    /**
     *
     * @var \SbmCommun\Model\Db\Service\DbLibService
     */
    private $db;

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
        $this->db = $serviceLocator->get('Sbm\Db\DbLib');
        $this->dbAdapter = $this->db->getDbAdapter();
        $this->sql = new Sql($this->dbAdapter);
        $this->history_name = $this->db->getCanonicName('history', 'sys');
        return $this;
    }

    /**
     * Changements du dernier jour
     * 
     * @return \Zend\Db\Adapter\Driver\ResultInterface
     */
    public function getLastDayChanges($table_name)
    {
        $select = $this->sql->select($this->history_name);
        $table_affectations = $this->db->getCanonicName($table_name);
        $hier = date('Y-m-d H:i', strtotime('-1 day'));
        $where = new Where();
        $where->equalTo('table_name', $table_affectations)->greaterThanOrEqualTo('dt', $hier);
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
}