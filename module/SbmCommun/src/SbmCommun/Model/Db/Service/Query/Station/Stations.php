<?php
/**
 * Requêtes pour extraire des stations
 *
 * 
 * @project sbm
 * @package SbmCommun/Model/Db/Service/Query/Station
 * @filesource Stations.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 8 juin 2015
 * @version 2015-1
 */
namespace SbmCommun\Model\Db\Service\Query\Station;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use DafapSession\Model\Session;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Where;

class Stations implements FactoryInterface
{

    /**
     *
     * @var \SbmCommun\Model\Db\Service\DbLibService
     */
    protected $db;

    /**
     *
     * @var int
     */
    protected $millesime;

    /**
     *
     * @var \Zend\Db\Sql\Sql
     */
    protected $sql;

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $this->millesime = Session::get('millesime');
        $this->db = $serviceLocator->get('Sbm\Db\DbLib');
        $this->sql = new Sql($this->db->getDbAdapter());
        return $this;
    }

    /**
     * Requête préparée renvoyant la position géographique des stations,
     *
     * @param Where $where            
     * @param string $order            
     * @return \Zend\Db\Adapter\Driver\ResultInterface
     */
    public function getLocalisation(Where $where, $order = null)
    {
        $select = $this->selectLocalisation($where, $order);
        $statement = $this->sql->prepareStatementForSqlObject($select);
        return $statement->execute();
        ;
    }

    private function selectLocalisation(Where $where, $order = null)
    {
        $where->equalTo('millesime', $this->millesime);
        $select = clone $this->sql->select();
        $select->from(array(
            'sta' => $this->db->getCanonicName('stations', 'table')
        ))
            ->columns(array(
            'nom',
            'x',
            'y'
        ))
            ->join(array(
            'com' => $this->db->getCanonicName('communes', 'table')
        ), 'sta.communeId=com.communeId', array(
            'commune' => 'nom'
        ))
            ->join(array(
            'cir' => $this->db->getCanonicName('circuits', 'table')
        ), 'cir.stationId = sta.stationId', array(
            'serviceId'
        ));
        if (! is_null($order)) {
            $select->order($order);
        }
        return $select->where($where);
    }
}
