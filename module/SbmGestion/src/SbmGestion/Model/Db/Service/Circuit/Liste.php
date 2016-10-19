<?php
/**
 * Listes extraites de la table des circuits
 *
 * @project sbm
 * @package SbmGestion/Model/Db/Service/Circuit
 * @filesource Liste.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 17 août 2016
 * @version 2016-2.2.0
 */
namespace SbmGestion\Model\Db\Service\Circuit;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Literal;
use Zend\Db\Sql\Where;
use SbmBase\Model\Session;
use SbmCommun\Model\Db\Service\DbManager;
use SbmCommun\Model\Db\Exception;

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
        $select->from(array(
            'c' => $this->db_manager->getCanonicName('circuits', 'table')
        ))
            ->where(array(
            'millesime' => Session::get('millesime')
        ))
            ->quantifier(Select::QUANTIFIER_DISTINCT)
            ->columns(array())
            ->join(array(
            's' => $this->db_manager->getCanonicName('services')
        ), 's.serviceId=c.serviceId')
            ->where(array(
            'stationId' => $stationId
        ));
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
        $select1 = new Select();
        $select1->from($this->db_manager->getCanonicName('circuits'))
            ->columns(array(
            'stationId'
        ))
            ->where(array(
            'millesime' => Session::get('millesime')
        ));
        $select = $this->sql->select();
        $select->from(array(
            's' => $this->db_manager->getCanonicName('stations')
        ))
            ->join(array(
            'v' => $this->db_manager->getCanonicName('communes')
        ), 'v.communeId=s.communeId', array(
            'commune' => 'nom'
        ))
            ->join(array(
            'c' => $select1
        ), 's.stationId=c.stationId', array(), Select::JOIN_LEFT)
            ->where(function ($where) {
            $where->isNull('c.stationId');
        });
        $statement = $this->sql->prepareStatementForSqlObject($select);
        return $statement->execute();
    }
}