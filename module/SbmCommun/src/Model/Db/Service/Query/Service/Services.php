<?php
/**
 * Requêtes pour extraire des services
 *
 * @project sbm
 * @package SbmCommun/Model/Db/Service/Query/Service
 * @filesource Services.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 10 sept. 2018
 * @version 2018-2.4.5
 */
namespace SbmCommun\Model\Db\Service\Query\Service;

use SbmBase\Model\Session;
use SbmCommun\Model\Db\Exception;
use SbmCommun\Model\Db\Service\DbManager;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Where;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

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
        $statement = $this->sql->prepareStatementForSqlObject(
            $this->selectServicesGivenEtablissement($etablissementId));
        return $statement->execute();
    }

    private function selectServicesGivenEtablissement($etablissementId)
    {
        $where = new Where();
        $where->equalTo('etablissementId', $etablissementId);
        $select = $this->sql->select();
        return $select->from(
            [
                'ser' => $this->db_manager->getCanonicName('services', 'table')
            ])
            ->columns([
            'serviceId',
            'nom',
            'operateur',
            'nbPlaces'
        ])
            ->join(
            [
                'etaser' => $this->db_manager->getCanonicName('etablissements-services',
                    'table')
            ], 'ser.serviceId = etaser.serviceId', [])
            ->join([
            'tra' => $this->db_manager->getCanonicName('transporteurs')
        ], 'ser.transporteurId = tra.transporteurId', [
            'transporteur' => 'nom'
        ])
            ->where($where);
    }

    /**
     * Renvoie un tableau des services avec leur transporteur et un tableau d'établissements
     * desservis par chaque service
     *
     * @return array
     */
    public function getServicesWithEtablissements()
    {
        $statement = $this->sql->prepareStatementForSqlObject(
            $this->selectServicesWithEtablissements());
        $rowset = $statement->execute();
        $result = [];
        foreach ($rowset as $row) {
            if (! array_key_exists($row['serviceId'], $result)) {
                $result[$row['serviceId']] = [
                    'serviceId' => $row['serviceId'],
                    'nom' => $row['nom'],
                    'operateur' => $row['operateur'],
                    'nbPlaces' => $row['nbPlaces'],
                    'transporteur' => $row['transporteur'],
                    'etablissements' => []
                ];
            }
            $result[$row['serviceId']]['etablissements'][] = [
                'etablissement' => $row['etablissement'],
                'communeEtablissement' => $row['communeEtablissement']
            ];
        }
        return $result;
    }

    public function paginatorServicesWithEtablissements()
    {
        return new \Zend\Paginator\Paginator(
            new \Zend\Paginator\Adapter\ArrayAdapter(
                $this->getServicesWithEtablissements()));
    }

    private function selectServicesWithEtablissements()
    {
        $select = $this->sql->select();
        return $select->from(
            [
                'ser' => $this->db_manager->getCanonicName('services', 'table')
            ])
            ->columns([
            'serviceId',
            'nom',
            'operateur',
            'nbPlaces'
        ])
            ->join([
            'tra' => $this->db_manager->getCanonicName('transporteurs')
        ], 'ser.transporteurId = tra.transporteurId', [
            'transporteur' => 'nom'
        ])
            ->join(
            [
                'etaser' => $this->db_manager->getCanonicName('etablissements-services',
                    'table')
            ], 'ser.serviceId = etaser.serviceId', [])
            ->join(
            [
                'eta' => $this->db_manager->getCanonicName('etablissements', 'table')
            ], 'eta.etablissementId = etaser.etablissementId', [
                'etablissement' => 'nom'
            ])
            ->join([
            'com' => $this->db_manager->getCanonicName('communes', 'table')
        ], 'com.communeId = eta.communeId', [
            'communeEtablissement' => 'nom'
        ])
            ->order('serviceId');
    }
} 