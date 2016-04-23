<?php
/**
 * Requêtes pour extraire des services
 *
 * @project sbm
 * @package SbmCommun/Model/Db/Service/Query/Service
 * @filesource Services.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 10 avr. 2016
 * @version 2016-2
 */
namespace SbmCommun\Model\Db\Service\Query\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use DafapSession\Model\Session;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Where;
use SbmCommun\Model\Db\Service\DbManager;
use SbmCommun\Model\Db\Exception;

class Services implements FactoryInterface
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

    public function getServicesGivenEtablissement($etablissementId)
    {
        $statement = $this->sql->prepareStatementForSqlObject($this->selectServicesGivenEtablissement($etablissementId));
        return $statement->execute();
    }

    private function selectServicesGivenEtablissement($etablissementId)
    {
        $where = new Where();
        $where->equalTo('etablissementId', $etablissementId);
        $select = $this->sql->select();
        return $select->from(array(
            'ser' => $this->db_manager->getCanonicName('services', 'table')
        ))
            ->columns(array(
            'serviceId',
            'nom',
            'operateur',
            'nbPlaces'
        ))
            ->join(array(
            'etaser' => $this->db_manager->getCanonicName('etablissements-services', 'table')
        ), 'ser.serviceId = etaser.serviceId', array())
            ->join(array(
            'tra' => $this->db_manager->getCanonicName('transporteurs')
        ), 'ser.transporteurId = tra.transporteurId', array(
            'transporteur' => 'nom'
        ))
            ->where($where);
    }

    /**
     * Renvoie un tableau des services avec leur transporteur et un tableau d'établissements desservis par chaque service
     * 
     * @return Ambigous <multitype:multitype:multitype: unknown  , multitype:unknown >
     */
    public function getServicesWithEtablissements()
    {
        $statement = $this->sql->prepareStatementForSqlObject($this->selectServicesWithEtablissements());
        $rowset = $statement->execute();
        $result = array();
        foreach ($rowset as $row) {
            if (! array_key_exists($row['serviceId'], $result)) {
                $result[$row['serviceId']] = array(
                    'serviceId' => $row['serviceId'],
                    'nom' => $row['nom'],
                    'operateur' => $row['operateur'],
                    'nbPlaces' => $row['nbPlaces'],
                    'transporteur' => $row['transporteur'],
                    'etablissements' => array()
                );
            }
            $result[$row['serviceId']]['etablissements'][] = array(
                'etablissement' => $row['etablissement'],
                'communeEtablissement' => $row['communeEtablissement']
            );
        }
        return $result;
    }

    public function paginatorServicesWithEtablissements()
    {
        return new \Zend\Paginator\Paginator(new \Zend\Paginator\Adapter\ArrayAdapter($this->getServicesWithEtablissements()));
    }
    
    private function selectServicesWithEtablissements()
    {
        $where = new Where();
        $select = $this->sql->select();
        return $select->from(array(
            'ser' => $this->db_manager->getCanonicName('services', 'table')
        ))
            ->columns(array(
            'serviceId',
            'nom',
            'operateur',
            'nbPlaces'
        ))
            ->join(array(
            'tra' => $this->db_manager->getCanonicName('transporteurs')
        ), 'ser.transporteurId = tra.transporteurId', array(
            'transporteur' => 'nom'
        ))
            ->join(array(
            'etaser' => $this->db_manager->getCanonicName('etablissements-services', 'table')
        ), 'ser.serviceId = etaser.serviceId', array())
            ->join(array(
            'eta' => $this->db_manager->getCanonicName('etablissements', 'table')
        ), 'eta.etablissementId = etaser.etablissementId', array(
            'etablissement' => 'nom'
        ))
            ->join(array(
            'com' => $this->db_manager->getCanonicName('communes', 'table')
        ), 'com.communeId = eta.communeId', array(
            'communeEtablissement' => 'nom'
        ))
            ->order('serviceId');
    }
} 