<?php
/**
 * Requêtes pour extraire des services
 *
 * @project sbm
 * @package SbmCommun/Model/Db/Service/Query/Service
 * @filesource Services.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 4 août 2015
 * @version 2015-1
 */
namespace SbmCommun\Model\Db\Service\Query\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use DafapSession\Model\Session;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Where;

class Services implements FactoryInterface
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
            'ser' => $this->db->getCanonicName('services', 'table')
        ))
            ->columns(array(
            'serviceId',
            'nom',
            'operateur',
            'nbPlaces'
        ))
            ->join(array(
            'etaser' => $this->db->getCanonicName('etablissements-services', 'table')
        ), 'ser.serviceId = etaser.serviceId', array())
            ->join(array(
            'tra' => $this->db->getCanonicName('transporteurs')
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
            'ser' => $this->db->getCanonicName('services', 'table')
        ))
            ->columns(array(
            'serviceId',
            'nom',
            'operateur',
            'nbPlaces'
        ))
            ->join(array(
            'tra' => $this->db->getCanonicName('transporteurs')
        ), 'ser.transporteurId = tra.transporteurId', array(
            'transporteur' => 'nom'
        ))
            ->join(array(
            'etaser' => $this->db->getCanonicName('etablissements-services', 'table')
        ), 'ser.serviceId = etaser.serviceId', array())
            ->join(array(
            'eta' => $this->db->getCanonicName('etablissements', 'table')
        ), 'eta.etablissementId = etaser.etablissementId', array(
            'etablissement' => 'nom'
        ))
            ->join(array(
            'com' => $this->db->getCanonicName('communes', 'table')
        ), 'com.communeId = eta.communeId', array(
            'communeEtablissement' => 'nom'
        ))
            ->order('serviceId');
    }
} 