<?php
/**
 * Listes extraites de la table des circuits
 *
 * @project sbm
 * @package SbmGestion/Model/Db/Service/Circuit
 * @filesource Liste.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 26 mars 2020
 * @version 2020-2.6.0
 */
namespace SbmGestion\Model\Db\Service\Circuit;

use SbmBase\Model\Session;
use SbmCommun\Model\Db\Exception;
use SbmCommun\Model\Db\Service\DbManager;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\DbSelect;

class Liste implements FactoryInterface
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
     * @var \Zend\Db\Sql\Sql
     */
    private $sql;

    /**
     * Renvoie la chaine de requête (après l'appel de la requête)
     *
     * @param \Zend\Db\Sql\Select $select
     *
     * @return string
     */
    public function getSqlString($select)
    {
        return $select->getSqlString($this->dbAdapter->getPlatform());
    }

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        if (! ($serviceLocator instanceof DbManager)) {
            $message = 'SbmCommun\Model\Db\Service\DbManager attendu. %s reçu.';
            throw new Exception\ExceptionNoDbManager(
                sprintf($message, gettype($serviceLocator)));
        }
        $this->db_manager = $serviceLocator;
        $this->dbAdapter = $this->db_manager->getDbAdapter();
        $this->sql = new Sql($this->dbAdapter);
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
        $select = $this->sql->select();
        $select->from([
            'c' => $this->db_manager->getCanonicName('circuits', 'table')
        ])
            ->where([
            'millesime' => Session::get('millesime')
        ])
            ->quantifier(Select::QUANTIFIER_DISTINCT)
            ->columns([])
            ->join([
            's' => $this->db_manager->getCanonicName('services')
        ], 's.serviceId=c.serviceId')
            ->where([
            'stationId' => $stationId
        ]);
        $statement = $this->sql->prepareStatementForSqlObject($select);
        return $statement->execute();
    }

    /**
     * Liste des stations qui ne sont pas desservies
     *
     * @return \Zend\Db\Adapter\Driver\ResultInterface
     */
    public function stationsNonDesservies()
    {
        $statement = $this->sql->prepareStatementForSqlObject(
            $this->selectStationsNonDesservies());
        return $statement->execute();
    }

    public function paginatorStationsNonDesservies()
    {
        return new Paginator(
            new DbSelect($this->selectStationsNonDesservies(),
                $this->db_manager->getDbAdapter()));
    }

    private function selectStationsNonDesservies()
    {
        $select1 = new Select();
        $select1->from($this->db_manager->getCanonicName('circuits'))
            ->columns([
            'stationId'
        ])
            ->where([
            'millesime' => Session::get('millesime')
        ]);
        $select = $this->sql->select();
        $select->from([
            's' => $this->db_manager->getCanonicName('stations')
        ])
            ->join([
            'v' => $this->db_manager->getCanonicName('communes')
        ], 'v.communeId=s.communeId',
            [
                'commune' => 'nom',
                'lacommune' => 'alias',
                'laposte' => 'alias_laposte'
            ])
            ->join([
            'c' => $select1
        ], 's.stationId=c.stationId', [], Select::JOIN_LEFT)
            ->where(function ($where) {
            $where->isNull('c.stationId');
        })
            ->order([
            'commune',
            'nom'
        ]);
        return $select;
    }
}