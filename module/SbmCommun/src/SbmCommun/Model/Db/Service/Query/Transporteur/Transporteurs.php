<?php
/**
 * Requêtes portant sur les transporteurs
 * (classe déclarée dans module.config.php sous l'alias 'Sbm\Db\Query\Transporteurs')
 *
 * 
 * @project sbm
 * @package SbmCommun/Model/Db/Service/Query/Transporteur
 * @filesource Transporteurs.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 18 févr. 2016
 * @version 2016-1.7.3
 */
namespace SbmCommun\Model\Db\Service\Query\Transporteur;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use DafapSession\Model\Session;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Expression;

class Transporteurs implements FactoryInterface
{

    /**
     *
     * @var \SbmCommun\Model\Db\Service\DbLibService
     */
    protected $db;

    /**
     *
     * @var \Zend\Db\Adapter\Adapter
     */
    private $dbAdapter;

    /**
     *
     * @var \Zend\Db\Sql\Sql
     */
    protected $sql;

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
        return $this;
    }

    /**
     * Renvoie la liste des emails des utilisateurs associés à un transporteur
     * 
     * @param int $transporteurId
     * @param string|array $order
     * 
     * @return \Zend\Db\Adapter\Driver\ResultInterface
     */
    public function getUserEmails($transporteurId, $order = null)
    {
        $select = $this->selectUserEmails($transporteurId, $order);
        $statement = $this->sql->prepareStatementForSqlObject($select);
        return $statement->execute();
    }
    
    private function selectUserEmails($transporteurId, $order)
    {
        $select = $this->sql->select([
            'ut' => $this->db->getCanonicName('users-transporteurs', 'table')
        ])
            ->join([
            'u' => $this->db->getCanonicName('users', 'table')
        ], 'u.userId = ut.userId', [
            'email'
        ])
            ->columns([
            'nomprenom' => new Expression('CONCAT(u.prenom, " ", u.nom)')
        ]);
        if (!empty($order)) {
            $select->order($order);
        }
        $where = new Where();
        $where->equalTo('ut.transporteurId', $transporteurId);
        //die($this->getSqlString($select));
        return $select->where($where);
    }
}