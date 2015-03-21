<?php
/**
 * Listes extraites de la table des circuits
 *
 * @project sbm
 * @package SbmGestion/Model/Db/Service/Circuit
 * @filesource Liste.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 19 mars 2015
 * @version 2015-1
 */
namespace SbmGestion\Model\Db\Service\Circuit;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Literal;
use Zend\Db\Sql\Where;
use DafapSession\Model\Session;

class Liste implements FactoryInterface
{

    private $db;

    private $dbAdapter;

    private $select;

    private $sql;

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $this->db = $serviceLocator->get('Sbm\Db\DbLib');
        $this->dbAdapter = $this->db->getDbAdapter();
        $this->sql = new Sql($this->dbAdapter);
        $this->select = $this->sql->select();
        return $this;
    }

    /**
     * Liste des services pour une station donnée
     *
     * @param int $stationId            
     * @return \Zend\Db\Adapter\Driver\ResultInterface
     */
    public function byStation($stationId)
    {
        $this->select->from(array(
            'c' => $this->db->getCanonicName('circuits', 'table')
        ))
            ->quantifier(Select::QUANTIFIER_DISTINCT)
            ->columns(array())
            ->join(array(
            's' => $this->db->getCanonicName('services')
        ), 's.serviceId=c.serviceId')
            ->where(array(
            'stationId' => $stationId
        ));
        $statement = $this->sql->prepareStatementForSqlObject($this->select);
        return $statement->execute();
    }

    /**
     * Liste des stations qui ne sont pas desservies
     *
     * @return \Zend\Db\Adapter\Driver\ResultInterface
     */
    public function stationsNonDesservies()
    {
        $select1 = new Select();
        $select1->from($this->db->getCanonicName('circuits'))
            ->columns(array(
            'stationId'
        ))
            ->where(array(
            'millesime' => Session::get('millesime')
        ));
        $this->select->from(array(
            's' => $this->db->getCanonicName('stations')
        ))
            ->join(array(
            'v' => $this->db->getCanonicName('communes')
        ), 'v.communeId=s.communeId', array(
            'commune' => 'nom'
        ))
            ->join(array(
            'c' => $select1
        ), 's.stationId=c.stationId', array(), Select::JOIN_LEFT)
            ->where(function ($where) {
            $where->isNull('c.stationId');
        });
        $statement = $this->sql->prepareStatementForSqlObject($this->select);
        return $statement->execute();
    }
}