<?php
/**
 * Requêtes donnant l'affectation ou la pré-affectation d'un élève
 *
 * 
 * @project sbm
 * @package package_name
 * @filesource AffectationsServicesStations.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 10 avr. 2015
 * @version 2015-1
 */
namespace SbmCommun\Model\Db\Service\Query\Eleve;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use DafapSession\Model\Session;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Where;

class AffectationsServicesStations implements FactoryInterface
{

    protected $db;

    protected $sql;

    protected $select;

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $this->db = $serviceLocator->get('Sbm\Db\DbLib');
        $this->sql = new Sql($this->db->getDbAdapter());
        $this->select = $this->sql->select()
            ->from(array(
            'aff' => $this->db->getCanonicName('affectations', 'table')
        ))
            ->columns(array(
            'millesime' => 'millesime',
            'eleveId' => 'eleveId',
            'trajet' => 'trajet',
            'jours' => 'jours',
            'sens' => 'sens',
            'correspondance' => 'correspondance',
            'selection' => 'selection',
            'responsableId' => 'responsableId',
            'station1Id' => 'station1Id',
            'service1Id' => 'service1Id',
            'station2Id' => 'station2Id',
            'service2Id' => 'service2Id'
        ));
        return $this;
    }

    /**
     * Renvoie le ou les codes des services affectés à l'élève pour le domicile de ce responsable
     *
     * @param int $eleveId            
     * @param int $responsableId            
     * @param int $trajet
     *            1 ou 2 selon que c'est le responsable n°1 ou n°2
     *            
     * @return \Zend\Db\Adapter\Driver\ResultInterface
     */
    public function getServices($eleveId, $responsableId, $trajet = null)
    {
        $select = clone $this->select;
        $select->order(array(
            'trajet',
            'jours',
            'sens',
            'correspondance'
        ));
        $where = new Where();
        $where->equalTo('millesime', Session::get('millesime'))
            ->equalTo('eleveId', $eleveId)
            ->equalTo('responsableId', $responsableId);
        if (isset($trajet)) {
            $where->equalTo('trajet', $trajet);
        }
        $statement = $this->sql->prepareStatementForSqlObject($select->where($where));
        return $statement->execute();
    }

    public function getAffectations($eleveId, $trajet = null)
    {
        $select = clone $this->select;
        $select->join(array(
            'ser1' => $this->db->getCanonicName('services', 'table')
        ), 'aff.service1Id = ser1.serviceId', array(
            'service1' => 'nom',
            'operateur1' => 'operateur'
        ))
            ->join(array(
            'tra1' => $this->db->getCanonicName('transporteurs', 'table')
        ), 'ser1.transporteurId = tra1.transporteurId', array(
            'transporteur1' => 'nom'
        ))
            ->join(array(
            'sta1' => $this->db->getCanonicName('stations', 'table')
        ), 'aff.station1Id = sta1.stationId', array(
            'station1' => 'nom'
        ))
            ->join(array(
            'com1' => $this->db->getCanonicName('communes', 'table')
        ), 'sta1.communeId = com1.communeId', array(
            'commune1' => 'nom'
        ))
            ->join(array(
            'sta2' => $this->db->getCanonicName('stations', 'table')
        ), 'aff.station2Id = sta2.stationId', array(
            'station2' => 'nom'
        ), $select::JOIN_LEFT)
            ->join(array(
            'com2' => $this->db->getCanonicName('communes', 'table')
        ), 'sta2.communeId = com2.communeId', array(
            'commune2' => 'nom'
        ), $select::JOIN_LEFT)
            ->order(array(
            'trajet',
            'jours',
            'sens',
            'correspondance'
        ));
        $where = new Where();
        $where->equalTo('millesime', Session::get('millesime'))->and->equalTo('eleveId', $eleveId);
        if (isset($trajet)) {
            $where->equalTo('trajet', $trajet);
        }
        $statement = $this->sql->prepareStatementForSqlObject($select->where($where));
        return $statement->execute();
    }

    public function getCorrespondances($eleveId)
    {
        $select = clone $this->select;
        $select->join(array(
            'ser1' => $this->db->getCanonicName('services', 'table')
        ), 'aff.service1Id = ser1.serviceId', array(
            'service1' => 'nom',
            'operateur1' => 'operateur'
        ))
            ->join(array(
            'tra1' => $this->db->getCanonicName('transporteurs', 'table')
        ), 'ser1.transporteurId = tra1.transporteurId', array(
            'transporteur1' => 'nom'
        ))
            ->join(array(
            'sta1' => $this->db->getCanonicName('stations', 'table')
        ), 'aff.station1Id = sta1.stationId', array(
            'station1' => 'nom'
        ))
            ->join(array(
            'com1' => $this->db->getCanonicName('communes', 'table')
        ), 'sta1.communeId = com1.communeId', array(
            'commune1' => 'nom'
        ))
            ->join(array(
            'ser2' => $this->db->getCanonicName('services', 'table')
        ), 'aff.service2Id = ser2.serviceId', array(
            'service2' => 'nom',
            'operateur2' => 'operateur'
        ), $select::JOIN_LEFT)
            ->join(array(
            'tra2' => $this->db->getCanonicName('transporteurs', 'table')
        ), 'ser2.transporteurId = tra2.transporteurId', array(
            'transporteur2' => 'nom'
        ), $select::JOIN_LEFT)
            ->join(array(
            'sta2' => $this->db->getCanonicName('stations', 'table')
        ), 'aff.station2Id = sta2.stationId', array(
            'station2' => 'nom'
        ), $select::JOIN_LEFT)
            ->join(array(
            'com2' => $this->db->getCanonicName('communes', 'table')
        ), 'sta2.communeId = com2.communeId', array(
            'commune2' => 'nom'
        ), $select::JOIN_LEFT);
        $where = new Where();
        $where->equalTo('millesime', Session::get('millesime'))->and->equalTo('eleveId', $eleveId);
        $statement = $this->sql->prepareStatementForSqlObject($select->where($where));
        return $statement->execute();
    }
}