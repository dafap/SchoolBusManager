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
        $this->select->from(array(
            'e' => $this->db->getCanonicName('eleves', 'table')
        ))
            ->join(array(
            's' => $this->db->getCanonicName('scolarites', 'table')
        ), 'e.eleveId=s.eleveId', array())
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
        if (is_array($keys)) {
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
        $select = $this->sql->select();
        $where = new Where();
        $where->equalTo('s.millesime', $millesime)->equalTo('classeId', $classeId);
        $select->from(array(
            'e' => $this->db->getCanonicName('eleves', 'table')
        ))
            ->join(array(
            's' => $this->db->getCanonicName('scolarites', 'table')
        ), 'e.eleveId=s.eleveId', array())
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
        $statement = $this->sql->prepareStatementForSqlObject($select);
        return $statement->execute();
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
        $where = new Where();
        $where->equalTo('s.millesime', $millesime)->NEST->equalTo('s.communeId', $communeId)->OR->equalTo('r.communeId', $communeId);
        $this->select->where($where)
            ->order($order)
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
     * Renvoie la liste des élèves pour un millesime et un établissement donnés
     *
     * @param int $millesime            
     * @param int $etablissementId            
     * @param string|array $order            
     *
     * @return \Zend\Db\Adapter\Driver\ResultInterface
     */
    public function byEtablissement($millesime, $etablissementId, $order = array('commune', 'nom', 'prenom'))
    {
        $where = new Where();
        $where->equalTo('s.millesime', $millesime)->equalTo('etablissementId', $etablissementId);
        $this->select->where($where)
            ->order($order)
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
        $where = new Where();
        $where->equalTo('s.millesime', $millesime)->NEST->equalTo('service1Id', $serviceId)->OR->equalTo('service2Id', $serviceId)->UNNEST;
        $this->select->where($where)
            ->order($order)
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
        $where = new Where();
        $where->equalTo('s.millesime', $millesime)->NEST->equalTo('s1.transporteurId', $transporteurId)->OR->equalTo('s2.transporteurId', $transporteurId)->UNNEST;
        $this->select->join(array(
            's1' => $this->db->getCanonicName('services', 'table')
        ), 's1.serviceId=a.service1Id', array(), Select::JOIN_LEFT)
            ->join(array(
            's2' => $this->db->getCanonicName('services', 'table')
        ), 's2.serviceId=a.service2Id', array(), Select::JOIN_LEFT)
            ->where($where)
            ->order($order)
            ->columns(array(
            'nom',
            'prenom',
            'adresseL1' => new Literal('IFNULL(s.adresseL1, r.adresseL1)'),
            'adresseL2' => new Literal('IFNULL(s.adresseL2, r.adresseL2)'),
            'codePostal' => new Literal('IFNULL(s.codePostal, r.codePostal)'),
            'commune' => new Literal('IFNULL(d.nom, c.nom)')
        ));
        ;
        $statement = $this->sql->prepareStatementForSqlObject($this->select);
        return $statement->execute();
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