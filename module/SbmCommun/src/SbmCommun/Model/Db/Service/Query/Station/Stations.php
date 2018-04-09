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
 * @date 4 avr. 2018
 * @version 2018-2.4.0
 */
namespace SbmCommun\Model\Db\Service\Query\Station;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Where;
use SbmBase\Model\Session;
use SbmCommun\Model\Db\Service\DbManager;
use SbmCommun\Model\Db\Exception;

class Stations implements FactoryInterface
{

    /**
     *
     * @var \SbmCommun\Model\Db\Service\DbManager
     */
    protected $db_manager;

    /**
     *
     * @var \Zend\Db\Adapter\Adapter
     */
    private $dbAdapter;

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
        $this->millesime = Session::get('millesime');
        $this->dbAdapter = $this->db_manager->getDbAdapter();
        $this->sql = new Sql($this->dbAdapter);
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
        $select->from(
            [
                'sta' => $this->db_manager->getCanonicName('stations', 'table')
            ])
            ->columns(
            [
                'nom',
                'x',
                'y'
            ])
            ->join(
            [
                'com' => $this->db_manager->getCanonicName('communes', 'table')
            ], 'sta.communeId=com.communeId', 
            [
                'commune' => 'nom'
            ])
            ->join(
            [
                'cir' => $this->db_manager->getCanonicName('circuits', 'table')
            ], 'cir.stationId = sta.stationId', 
            [
                'serviceId'
            ]);
        if (! is_null($order)) {
            $select->order($order);
        }
        return $select->where($where);
    }
}
