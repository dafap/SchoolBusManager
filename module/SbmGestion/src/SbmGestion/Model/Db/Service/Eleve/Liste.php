<?php
/**
 * Listes d'élèves pour un ou plusieurs critères donnés
 *
 * 
 * @project sbm
 * @package SbmGestion/Model/Db/Service/Eleve
 * @filesource Liste.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 17 mars 2015
 * @version 2015-1
 */
namespace SbmGestion\Model\Db\Service\Eleve;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Literal;
use Zend\Db\Sql\Where;
use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\DbSelect;

class Liste implements FactoryInterface
{

    /**
     *
     * @var \SbmCommun\Model\Db\Service\DbLibService
     */
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
        $this->select->from(array(
            'e' => $this->db->getCanonicName('eleves', 'table')
        ))
            ->join(array(
            's' => $this->db->getCanonicName('scolarites', 'table')
        ), 'e.eleveId=s.eleveId', array())
            ->join(array(
            'eta' => $this->db->getCanonicName('etablissements', 'table')
        ), 's.etablissementId = eta.etablissementId', array(
            'etablissement' => 'nom'
        ))
            ->join(array(
            'cla' => $this->db->getCanonicName('classes', 'table')
        ), 's.classeId = cla.classeId', array(
            'classe' => 'nom'
        ))
            ->join(array(
            'a' => $this->db->getCanonicName('affectations', 'table')
        ), 'a.millesime=s.millesime And e.eleveId=a.eleveId', array())
            ->join(array(
            'r' => $this->db->getCanonicName('responsables', 'table')
        ), 'r.responsableId=a.responsableId', array())
            ->join(array(
            'c' => $this->db->getCanonicName('communes', 'table')
        ), 'r.communeId=c.communeId', array())
            ->join(array(
            'd' => $this->db->getCanonicName('communes', 'table')
        ), 'd.communeId=s.communeId', array(), Select::JOIN_LEFT);
        return $this;
    }

    public function byCircuit($millesime, $keys, $order = array('commune', 'nom', 'prenom'))
    {
        $where = new Where();
        $where->equalTo('s.millesime', $millesime);
        if (!empty($keys) && is_array($keys)) {
            $predicateSet = $where->nest();
            foreach ($keys as $partie) {
                if (is_array($partie)) {
                    $predicatePart = $predicateSet->nest();
                    foreach ($partie as $key => $value) {
                        $predicatePart->equalTo($key, $value);
                    }
                    $predicatePart->unnest();
                } elseif ($partie == 'or') {
                    $predicateSet->OR;
                } elseif ($partie == 'and') {
                    $predicateSet->AND;
                }
            }
            $predicateSet->unnest();
        }
        $this->select->where($where)
            ->order($order)
            ->quantifier(Select::QUANTIFIER_DISTINCT)
            ->columns(array(
            'nom',
            'prenom',
            'adresseL1' => new Literal('IFNULL(s.adresseL1, r.adresseL1)'),
            'adresseL2' => new Literal('IFNULL(s.adresseL2, r.adresseL2)'),
            'codePostal' => new Literal('IFNULL(s.codePostal, r.codePostal)'),
            'commune' => new Literal('IFNULL(d.nom, c.nom)')
        ));
        $statement = $this->sql->prepareStatementForSqlObject($this->select);
        //die($statement->getSql());
        return $statement->execute();
    }

    /**
     * Renvoie la liste des élèves pour un millesime et une classe donnés
     *
     * @param int $millesime            
     * @param int $classeId            
     * @param string|array $order            
     *
     * @return \Zend\Db\Adapter\Driver\ResultInterface
     */
    public function byClasse($millesime, $classeId, $order = array('commune', 'nom', 'prenom'))
    {
        $select = $this->selectByClasse($millesime, $classeId, $order);
        $statement = $this->sql->prepareStatementForSqlObject($select);
        return $statement->execute();
    }

    public function paginatorByClasse($millesime, $classeId, $order = array('commune', 'nom', 'prenom'))
    {
        $select = $this->selectByClasse($millesime, $classeId, $order);
        return new Paginator(new DbSelect($select, $this->db->getDbAdapter()));
    }

    private function selectByClasse($millesime, $classeId, $order)
    {
        $select = $this->sql->select();
        $where = new Where();
        $where->equalTo('s.millesime', $millesime)->equalTo('classeId', $classeId)->NEST->literal('accordR1 = 1')->or->literal('accordR2=1')->unnest();
        $select->from(array(
            'e' => $this->db->getCanonicName('eleves', 'table')
        ))
            ->join(array(
            's' => $this->db->getCanonicName('scolarites', 'table')
        ), 'e.eleveId=s.eleveId', array())
            ->join(array(
            'eta' => $this->db->getCanonicName('etablissements', 'table')
        ), 's.etablissementId = eta.etablissementId', array(
            'etablissement' => 'nom'
        ))
            ->join(array(
            'r' => $this->db->getCanonicName('responsables', 'table')
        ), 'r.responsableId=e.responsable1Id OR r.responsableId=e.responsable2Id', array())
            ->join(array(
            'c' => $this->db->getCanonicName('communes', 'table')
        ), 'r.communeId=c.communeId', array())
            ->join(array(
            'd' => $this->db->getCanonicName('communes', 'table')
        ), 'd.communeId=s.communeId', array(), Select::JOIN_LEFT)
            ->where($where)
            ->order($order)
            ->columns(array(
            'nom',
            'prenom',
            'adresseL1' => new Literal('IFNULL(s.adresseL1, r.adresseL1)'),
            'adresseL2' => new Literal('IFNULL(s.adresseL2, r.adresseL2)'),
            'codePostal' => new Literal('IFNULL(s.codePostal, r.codePostal)'),
            'commune' => new Literal('IFNULL(d.nom, c.nom)')
        ))
            ->quantifier(Select::QUANTIFIER_DISTINCT);
        return $select;
    }

    /**
     * Renvoie la liste des élèves pour un millesime et une commune donnés
     *
     * @param int $millesime            
     * @param string $communeId            
     * @param string|array $order            
     *
     * @return \Zend\Db\Adapter\Driver\ResultInterface
     */
    public function byCommune($millesime, $communeId, $order = array('nom', 'prenom'))
    {
        $select = $this->selectByCommune($millesime, $communeId, $order);
        $statement = $this->sql->prepareStatementForSqlObject($this->select);
        return $statement->execute();
    }

    public function paginatorByCommune($millesime, $communeId, $order = array('nom', 'prenom'))
    {
        $select = $this->selectByCommune($millesime, $communeId, $order);
        return new Paginator(new DbSelect($select, $this->db->getDbAdapter()));
    }

    private function selectByCommune($millesime, $communeId, $order)
    {
        $where = new Where();
        $where->equalTo('s.millesime', $millesime)
            ->nest()
            ->equalTo('s.communeId', $communeId)->OR->equalTo('r.communeId', $communeId)->unnest();
        $select = clone $this->select;
        $select->where($where)
            ->order($order)
            ->columns(array(
            'nom',
            'prenom',
            'adresseL1' => new Literal('IFNULL(s.adresseL1, r.adresseL1)'),
            'adresseL2' => new Literal('IFNULL(s.adresseL2, r.adresseL2)'),
            'codePostal' => new Literal('IFNULL(s.codePostal, r.codePostal)'),
            'commune' => new Literal('IFNULL(d.nom, c.nom)')
        ));
        return $select;
    }

    /**
     * Renvoie la liste des élèves pour un millesime et un établissement donnés
     *
     * @param int $millesime            
     * @param int $etablissementId            
     * @param string|array $order            
     *
     * @return \Zend\Db\Adapter\Driver\ResultInterface
     */
    public function byEtablissement($millesime, $etablissementId, $order = array('nom', 'prenom'))
    {
        $select = $this->selectByEtablissement($millesime, $etablissementId, $order);
        $statement = $this->sql->prepareStatementForSqlObject($this->select);
        return $statement->execute();
    }

    public function paginatorByEtablissement($millesime, $etablissementId, $order = array('nom', 'prenom'))
    {
        $select = $this->selectByEtablissement($millesime, $etablissementId, $order);
        return new Paginator(new DbSelect($select, $this->db->getDbAdapter()));
    }

    private function selectByEtablissement($millesime, $etablissementId, $order)
    {
        $where = new Where();
        $where->equalTo('s.millesime', $millesime)->equalTo('s.etablissementId', $etablissementId);
        $select = clone $this->select;
        $select->where($where)
            ->order($order)
            ->columns(array(
            'nom',
            'prenom',
            'adresseL1' => new Literal('IFNULL(s.adresseL1, r.adresseL1)'),
            'adresseL2' => new Literal('IFNULL(s.adresseL2, r.adresseL2)'),
            'codePostal' => new Literal('IFNULL(s.codePostal, r.codePostal)'),
            'commune' => new Literal('IFNULL(d.nom, c.nom)')
        ));
        return $select;
    }

    /**
     * Renvoie la liste des élèves pour un millesime, un établissement et un service donnés
     *
     * @param int $millesime            
     * @param int $etablissementId            
     * @param string|array $order            
     *
     * @return \Zend\Db\Adapter\Driver\ResultInterface
     */
    public function byEtablissementService($millesime, $etablissementId, $serviceId, $order = array('nom', 'prenom'))
    {
        $select = $this->selectByEtablissementService($millesime, $etablissementId, $serviceId, $order);
        $statement = $this->sql->prepareStatementForSqlObject($this->select);
        return $statement->execute();
    }

    public function paginatorByEtablissementService($millesime, $etablissementId, $serviceId, $order = array('nom', 'prenom'))
    {
        $select = $this->selectByEtablissementService($millesime, $etablissementId, $serviceId, $order);
        return new Paginator(new DbSelect($select, $this->db->getDbAdapter()));
    }

    private function selectByEtablissementService($millesime, $etablissementId, $serviceId, $order)
    {
        $tableAffectations = $this->db->getCanonicName('affectations', 'table');
        $select1 = new Select();
        $select1->from(array(
            'a1' => $tableAffectations
        ))
            ->columns(array(
            'millesime',
            'eleveId',
            'trajet',
            'jours',
            'sens',
            'responsableId',
            'stationId' => 'station1Id',
            'serviceId' => 'service1Id'
        ))
            ->where(array(
            'a1.millesime' => $millesime
        ));
        
        $select1cor2 = new Select();
        $select1cor2->from(array(
            'a1c2' => $tableAffectations
        ))
            ->columns(array(
            'millesime',
            'eleveId',
            'trajet',
            'jours',
            'sens',
            'responsableId',
            'stationId' => 'station1Id',
            'serviceId' => 'service1Id'
        ))
            ->where(array(
            'a1c2.millesime' => $millesime,
            'correspondance' => 2
        ));
        
        $jointure = "a2.millesime=correspondances.millesime AND a2.eleveId=correspondances.eleveId AND a2.trajet=correspondances.trajet AND a2.jours=correspondances.jours AND a2.sens=correspondances.sens AND a2.station2Id=correspondances.stationId";
        $where2 = new Where();
        $where2->equalTo('a2.millesime', $millesime)
            ->isNotNull('service2Id')
            ->isNull('correspondances.millesime');
        $select2 = new Select();
        $select2->from(array(
            'a2' => $tableAffectations
        ))
            ->columns(array(
            'millesime',
            'eleveId',
            'trajet',
            'jours',
            'sens',
            'responsableId',
            'stationId' => 'station2Id',
            'serviceId' => 'service2Id'
        ))
            ->join(array(
            'correspondances' => $select1cor2
        ), $jointure, array(), Select::JOIN_LEFT)
            ->where($where2);
        
        $where = new Where();
        $where->equalTo('s.millesime', $millesime)
            ->equalTo('s.etablissementId', $etablissementId)
            ->equalTo('a.serviceId', $serviceId);
        $select = $this->sql->select();
        $select->from(array(
            'e' => $this->db->getCanonicName('eleves', 'table')
        ))
            ->join(array(
            's' => $this->db->getCanonicName('scolarites', 'table')
        ), 'e.eleveId=s.eleveId', array())
            ->join(array(
            'eta' => $this->db->getCanonicName('etablissements', 'table')
        ), 's.etablissementId = eta.etablissementId', array(
            'etablissement' => 'nom'
        ))
            ->join(array(
            'cla' => $this->db->getCanonicName('classes', 'table')
        ), 's.classeId = cla.classeId', array(
            'classe' => 'nom'
        ))
            ->join(array(
            'a' => $select1->combine($select2)
        ), 'a.millesime=s.millesime And e.eleveId=a.eleveId', array())
            ->join(array(
            'r' => $this->db->getCanonicName('responsables', 'table')
        ), 'r.responsableId=a.responsableId', array())
            ->join(array(
            'c' => $this->db->getCanonicName('communes', 'table')
        ), 'r.communeId=c.communeId', array())
            ->join(array(
            'd' => $this->db->getCanonicName('communes', 'table')
        ), 'd.communeId=s.communeId', array(), Select::JOIN_LEFT)
            ->join(array(
            'sta' => $this->db->getCanonicName('stations', 'table')
        ), 'sta.stationId = a.stationId', array(
            'station' => 'nom'
        ))
            ->where($where)
            ->order($order)
            ->quantifier(Select::QUANTIFIER_DISTINCT)
            ->columns(array(
            'nom',
            'prenom',
            'adresseL1' => new Literal('IFNULL(s.adresseL1, r.adresseL1)'),
            'adresseL2' => new Literal('IFNULL(s.adresseL2, r.adresseL2)'),
            'codePostal' => new Literal('IFNULL(s.codePostal, r.codePostal)'),
            'commune' => new Literal('IFNULL(d.nom, c.nom)')
        ));
        // die($select->getSqlString());
        return $select;
    }

    /**
     * Renvoie la liste des élèves pour un millesime et un service donnés
     *
     * @param int $millesime            
     * @param string $serviceId            
     * @param string|array $order            
     *
     * @return \Zend\Db\Adapter\Driver\ResultInterface
     */
    public function byService($millesime, $serviceId, $order = array('commune', 'nom', 'prenom'))
    {
        $select = $this->selectByService($millesime, $serviceId, $order);
        $statement = $this->sql->prepareStatementForSqlObject($select);
        return $statement->execute();
    }

    public function paginatorByService($millesime, $serviceId, $order = array('commune', 'nom', 'prenom'))
    {
        $select = $this->selectByService($millesime, $serviceId, $order);
        return new Paginator(new DbSelect($select, $this->db->getDbAdapter()));
    }

    private function selectByService($millesime, $serviceId, $order)
    {
        $where = new Where();
        $where->equalTo('s.millesime', $millesime)->NEST->equalTo('service1Id', $serviceId)->OR->equalTo('service2Id', $serviceId)->UNNEST;
        $select = clone $this->select;
        $select->where($where)
            ->order($order)
            ->quantifier(Select::QUANTIFIER_DISTINCT)
            ->columns(array(
            'nom',
            'prenom',
            'adresseL1' => new Literal('IFNULL(s.adresseL1, r.adresseL1)'),
            'adresseL2' => new Literal('IFNULL(s.adresseL2, r.adresseL2)'),
            'codePostal' => new Literal('IFNULL(s.codePostal, r.codePostal)'),
            'commune' => new Literal('IFNULL(d.nom, c.nom)')
        ));
        return $select;
    }

    /**
     * Renvoie la liste des élèves pour un millesime et une station donnés
     *
     * @param int $millesime            
     * @param string $stationId            
     * @param string|array $order            
     *
     * @return \Zend\Db\Adapter\Driver\ResultInterface
     */
    public function byStation($millesime, $stationId, $order = array('commune', 'nom', 'prenom'))
    {
        $where = new Where();
        $where->equalTo('s.millesime', $millesime)->NEST->equalTo('station1Id', $stationId)->OR->equalTo('station2Id', $stationId)->UNNEST;
        $this->select->where($where)
            ->order($order)
            ->quantifier(Select::QUANTIFIER_DISTINCT)
            ->columns(array(
            'nom',
            'prenom',
            'adresseL1' => new Literal('IFNULL(s.adresseL1, r.adresseL1)'),
            'adresseL2' => new Literal('IFNULL(s.adresseL2, r.adresseL2)'),
            'codePostal' => new Literal('IFNULL(s.codePostal, r.codePostal)'),
            'commune' => new Literal('IFNULL(d.nom, c.nom)')
        ));
        $statement = $this->sql->prepareStatementForSqlObject($this->select);
        return $statement->execute();
    }

    /**
     * Renvoie la liste des élèves pour un millesime et un tarif donnés
     *
     * @param int $millesime            
     * @param int $tarifId            
     * @param string|array $order            
     *
     * @return \Zend\Db\Adapter\Driver\ResultInterface
     */
    public function byTarif($millesime, $tarifId, $order = array('nom', 'prenom'))
    {
        $select = $this->selectByTarif($millesime, $tarifId, $order);
        $statement = $this->sql->prepareStatementForSqlObject($select);
        return $statement->execute();
    }

    public function paginatorByTarif($millesime, $tarifId, $order = array('nom', 'prenom'))
    {
        $select = $this->selectByTarif($millesime, $tarifId, $order);
        return new Paginator(new DbSelect($select, $this->db->getDbAdapter()));
    }

    private function selectByTarif($millesime, $tarifId, $order)
    {
        $select = $this->sql->select();
        $where = new Where();
        $where->equalTo('s.millesime', $millesime)->equalTo('tarifId', $tarifId);
        $select->from(array(
            'e' => $this->db->getCanonicName('eleves', 'table')
        ))
            ->join(array(
            's' => $this->db->getCanonicName('scolarites', 'table')
        ), 'e.eleveId=s.eleveId', array())
            ->join(array(
            'eta' => $this->db->getCanonicName('etablissements', 'table')
        ), 's.etablissementId = eta.etablissementId', array(
            'etablissement' => 'nom'
        ))
            ->join(array(
            'cla' => $this->db->getCanonicName('classes', 'table')
        ), 's.classeId = cla.classeId', array(
            'classe' => 'nom'
        ))
            ->join(array(
            'r' => $this->db->getCanonicName('responsables', 'table')
        ), 'r.responsableId=e.responsable1Id OR r.responsableId=e.responsable2Id', array())
            ->join(array(
            'c' => $this->db->getCanonicName('communes', 'table')
        ), 'r.communeId=c.communeId', array())
            ->join(array(
            'd' => $this->db->getCanonicName('communes', 'table')
        ), 'd.communeId=s.communeId', array(), Select::JOIN_LEFT)
            ->where($where)
            ->order($order)
            ->columns(array(
            'nom',
            'prenom',
            'adresseL1' => new Literal('IFNULL(s.adresseL1, r.adresseL1)'),
            'adresseL2' => new Literal('IFNULL(s.adresseL2, r.adresseL2)'),
            'codePostal' => new Literal('IFNULL(s.codePostal, r.codePostal)'),
            'commune' => new Literal('IFNULL(d.nom, c.nom)')
        ))
            ->quantifier(Select::QUANTIFIER_DISTINCT);
        return $select;
    }

    /**
     * Renvoie la liste des élèves pour un millesime et un transporteur donnés
     *
     * @param int $millesime            
     * @param int $transporteurId            
     * @param string|array $order            
     *
     * @return \Zend\Db\Adapter\Driver\ResultInterface
     */
    public function byTransporteur($millesime, $transporteurId, $order = array('commune', 'nom', 'prenom'))
    {
        $select = $this->selectByTransporteur($millesime, $transporteurId, $order);
        $statement = $this->sql->prepareStatementForSqlObject($select);
        return $statement->execute();
    }

    public function paginatorByTransporteur($millesime, $transporteurId, $order = array('commune', 'nom', 'prenom'))
    {
        $select = $this->selectByTransporteur($millesime, $transporteurId, $order);
        return new Paginator(new DbSelect($select, $this->db->getDbAdapter()));
    }

    private function selectByTransporteur($millesime, $transporteurId, $order)
    {
        $tableAffectations = $this->db->getCanonicName('affectations', 'table');
        $select1 = new Select();
        $select1->from(array(
            'a1' => $tableAffectations
        ))
        ->columns(array(
            'millesime',
            'eleveId',
            'trajet',
            'jours',
            'sens',
            'responsableId',
            'stationId' => 'station1Id',
            'serviceId' => 'service1Id'
        ))
        ->where(array(
            'a1.millesime' => $millesime
        ));
        
        $select1cor2 = new Select();
        $select1cor2->from(array(
            'a1c2' => $tableAffectations
        ))
        ->columns(array(
            'millesime',
            'eleveId',
            'trajet',
            'jours',
            'sens',
            'responsableId',
            'stationId' => 'station1Id',
            'serviceId' => 'service1Id'
        ))
        ->where(array(
            'a1c2.millesime' => $millesime,
            'correspondance' => 2
        ));
        
        $jointure = "a2.millesime=correspondances.millesime AND a2.eleveId=correspondances.eleveId AND a2.trajet=correspondances.trajet AND a2.jours=correspondances.jours AND a2.sens=correspondances.sens AND a2.station2Id=correspondances.stationId";
        $where2 = new Where();
        $where2->equalTo('a2.millesime', $millesime)
        ->isNotNull('service2Id')
        ->isNull('correspondances.millesime');
        $select2 = new Select();
        $select2->from(array(
            'a2' => $tableAffectations
        ))
        ->columns(array(
            'millesime',
            'eleveId',
            'trajet',
            'jours',
            'sens',
            'responsableId',
            'stationId' => 'station2Id',
            'serviceId' => 'service2Id'
        ))
        ->join(array(
            'correspondances' => $select1cor2
        ), $jointure, array(), Select::JOIN_LEFT)
        ->where($where2);
        
        $where = new Where();
        $where->equalTo('s.millesime', $millesime)
        ->equalTo('ser.transporteurId', $transporteurId);
        $select = $this->sql->select();
        $select->from(array(
            'e' => $this->db->getCanonicName('eleves', 'table')
        ))
        ->join(array(
            's' => $this->db->getCanonicName('scolarites', 'table')
        ), 'e.eleveId=s.eleveId', array())
        ->join(array(
            'eta' => $this->db->getCanonicName('etablissements', 'table')
        ), 's.etablissementId = eta.etablissementId', array(
            'etablissement' => 'nom'
        ))
        ->join(array(
            'cla' => $this->db->getCanonicName('classes', 'table')
        ), 's.classeId = cla.classeId', array(
            'classe' => 'nom'
        ))
        ->join(array(
            'a' => $select1->combine($select2)
        ), 'a.millesime=s.millesime And e.eleveId=a.eleveId', array())
        ->join(array(
            'r' => $this->db->getCanonicName('responsables', 'table')
        ), 'r.responsableId=a.responsableId', array())
        ->join(array(
            'c' => $this->db->getCanonicName('communes', 'table')
        ), 'r.communeId=c.communeId', array())
        ->join(array(
            'd' => $this->db->getCanonicName('communes', 'table')
        ), 'd.communeId=s.communeId', array(), Select::JOIN_LEFT)
        ->join(array(
            'ser' => $this->db->getCanonicName('services', 'table')
        ), 'ser.serviceId = a.serviceId', array())
        ->where($where)
        ->order($order)
        ->quantifier(Select::QUANTIFIER_DISTINCT)
        ->columns(array(
            'nom',
            'prenom',
            'adresseL1' => new Literal('IFNULL(s.adresseL1, r.adresseL1)'),
            'adresseL2' => new Literal('IFNULL(s.adresseL2, r.adresseL2)'),
            'codePostal' => new Literal('IFNULL(s.codePostal, r.codePostal)'),
            'commune' => new Literal('IFNULL(d.nom, c.nom)')
        ));
        // die($select->getSqlString());
        return $select;
    }

    /**
     * Renvoie la chaine de requête (après l'appel de la requête)
     *
     * @return \Zend\Db\Adapter\mixed
     */
    public function getSql()
    {
        $statement = $this->sql->prepareStatementForSqlObject($this->select);
        return $statement->getSql();
    }
}