<?php
/**
 * Requêtes pour les statistiques concernant les responsables
 * (classe déclarée dans mocule.config.php sous l'alias 'Sbm\Statistiques\Eleve')
 * 
 * @project sbm
 * @package SbmCommun/Model/Db/Service/Query/Responsable
 * @filesource Statistiques.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 27 juin 2015
 * @version 2015-1
 */
namespace SbmCommun\Model\Db\Service\Query\Responsable;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use DafapSession\Model\Session;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Literal;
use Zend\Db\Sql\Predicate\Predicate;

class Statistiques implements FactoryInterface
{

    /**
     *
     * @var int
     */
    protected $millesime;

    /**
     *
     * @var \Zend\Db\Adapter\Adapter
     */
    protected $db;
    
    /**
     *
     * @var \Zend\Db\Adapter\Adapter
     */
    private $dbAdapter;

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
        $this->millesime = Session::get('millesime');
        $this->db = $serviceLocator->get('Sbm\Db\DbLib');
        $this->dbAdapter = $this->db->getDbAdapter();
        $this->sql = new Sql($this->dbAdapter);
        return $this;
    }

    /**
     * Renvoie le nombre de responsables enregistrés
     *
     * @return array
     */
    public function getNbEnregistres()
    {
        $select = $this->sql->select();
        $select->from($this->db->getCanonicName('responsables', 'table'))
            ->columns(array(
            'effectif' => new Expression('count(responsableId)')
        ));
        $statement = $this->sql->prepareStatementForSqlObject($select);
        // $statement->execute() renvoie un \Zend\Db\Adapter\Driver\ResultInterface
        return iterator_to_array($statement->execute());
    }

    /**
     * Renvoie le nombre de responsables ayant inscrit des enfants cette année
     *
     * @return array
     */
    public function getNbAvecEnfant()
    {
        $where1 = new Where();
        $where1->equalTo('millesime', $this->millesime);
        $select1 = $this->sql->select();
        $select1->from(array(
            'ele' => $this->db->getCanonicName('eleves', 'table')
        ))
            ->columns(array(
            'responsableId' => 'responsable1Id'
        ))
            ->join(array(
            'sco' => $this->db->getCanonicName('scolarites', 'table')
        ), 'sco.eleveId = ele.eleveId', array())
            ->where($where1);
        $where2 = new Where();
        $where2->equalTo('millesime', $this->millesime)->isNotNull('responsable2Id');
        $select2 = $this->sql->select();
        $select2->from(array(
            'ele' => $this->db->getCanonicName('eleves', 'table')
        ))
            ->columns(array(
            'responsableId' => 'responsable2Id'
        ))
            ->join(array(
            'sco' => $this->db->getCanonicName('scolarites', 'table')
        ), 'sco.eleveId = ele.eleveId', array())
            ->where($where2);
        $select1->combine($select2);
        $select3 = $this->sql->select();
        $select3->from(array('id' => $select1));
        
        $where = new Where();
        $where->in('responsableId', $select3);
        $select = $this->sql->select();
        $select->from(array(
            'res' => $this->db->getCanonicName('responsables', 'table')
        ))
            ->columns(array(
            'effectif' => new Expression('count(responsableId)')
        ))
            ->where($where);
        $statement = $this->sql->prepareStatementForSqlObject($select);
        // $statement->execute() renvoie un \Zend\Db\Adapter\Driver\ResultInterface
        return iterator_to_array($statement->execute());
    }

    /**
     * Renvoie le nombre de responsables enregistrés, sans enfant cette année
     *
     * @return array
     */
    public function getNbSansEnfant()
    {
        $where1 = new Where();
        $where1->equalTo('millesime', $this->millesime);
        $select1 = $this->sql->select();
        $select1->from(array(
            'ele' => $this->db->getCanonicName('eleves', 'table')
        ))
            ->join(array(
            'sco' => $this->db->getCanonicName('scolarites', 'table')
        ), 'sco.eleveId = ele.eleveId', array())
            ->where($where1);
        
        $where2 = new Where();
        $where2->isNull('eleveId');
        $select2 = $this->sql->select();
        $select2->from(array(
            'res' => $this->db->getCanonicName('responsables', 'table')
        ))
            ->columns(array(
            'effectif' => new Expression('count(responsableId)')
        ))
            ->join(array(
            'ele' => $select1
        ), 'ele.responsable1Id = res.responsableId Or ele.responsable2Id = res.responsableId', array(), $select2::JOIN_LEFT)
            ->where($where2);
        $statement = $this->sql->prepareStatementForSqlObject($select2);
        // $statement->execute() renvoie un \Zend\Db\Adapter\Driver\ResultInterface
        return iterator_to_array($statement->execute());
    }

    /**
     * Renvoie le nombre de responsables ayant des enfants inscrits et résidant dans une commune non membre
     *
     * @return array
     */
    public function getNbCommuneNonMembre()
    {
        $where1 = new Where();
        $where1->equalTo('millesime', $this->millesime);
        $select1 = $this->sql->select();
        $select1->from(array(
            'ele' => $this->db->getCanonicName('eleves', 'table')
        ))
        ->columns(array(
            'responsableId' => 'responsable1Id'
        ))
        ->join(array(
            'sco' => $this->db->getCanonicName('scolarites', 'table')
        ), 'sco.eleveId = ele.eleveId', array())
        ->where($where1);
        $where2 = new Where();
        $where2->equalTo('millesime', $this->millesime)->isNotNull('responsable2Id');
        $select2 = $this->sql->select();
        $select2->from(array(
            'ele' => $this->db->getCanonicName('eleves', 'table')
        ))
        ->columns(array(
            'responsableId' => 'responsable2Id'
        ))
        ->join(array(
            'sco' => $this->db->getCanonicName('scolarites', 'table')
        ), 'sco.eleveId = ele.eleveId', array())
        ->where($where2);
        $select1->combine($select2);
        $select3 = $this->sql->select();
        $select3->from(array('id' => $select1));
        
        $where = new Where();
        $where->in('responsableId', $select3)->literal('com.membre = 0');
        $select = $this->sql->select();
        $select->from(array(
            'res' => $this->db->getCanonicName('responsables', 'table')
        ))
            ->columns(array(
            'effectif' => new Expression('count(responsableId)')
        ))
            ->join(array(
            'com' => $this->db->getCanonicName('communes', 'table')
        ), 'com.communeId = res.communeId', array())
            ->where($where);
        $statement = $this->sql->prepareStatementForSqlObject($select);
        // $statement->execute() renvoie un \Zend\Db\Adapter\Driver\ResultInterface
        return iterator_to_array($statement->execute());
    }
    
    /**
     * Renvoie le nombre de responsables ayant des enfants inscrits et ayant déménagé
     * 
     * @return array
     */
    public function getNbDemenagement()
    {
        $where1 = new Where();
        $where1->equalTo('millesime', $this->millesime);
        $select1 = $this->sql->select();
        $select1->from(array(
            'ele' => $this->db->getCanonicName('eleves', 'table')
        ))
            ->columns(array(
            'responsableId' => 'responsable1Id'
        ))
            ->join(array(
            'sco' => $this->db->getCanonicName('scolarites', 'table')
        ), 'sco.eleveId = ele.eleveId', array())
            ->where($where1);
        $where2 = new Where();
        $where2->equalTo('millesime', $this->millesime)->isNotNull('responsable2Id');
        $select2 = $this->sql->select();
        $select2->from(array(
            'ele' => $this->db->getCanonicName('eleves', 'table')
        ))
            ->columns(array(
            'responsableId' => 'responsable2Id'
        ))
            ->join(array(
            'sco' => $this->db->getCanonicName('scolarites', 'table')
        ), 'sco.eleveId = ele.eleveId', array())
            ->where($where2);
        $select1->combine($select2);
        $select3 = $this->sql->select();
        $select3->from(array('id' => $select1));
        
        $where = new Where();
        $where->in('responsableId', $select3)->literal('demenagement = 1');
        $select = $this->sql->select();
        $select->from(array(
            'res' => $this->db->getCanonicName('responsables', 'table')
        ))
            ->columns(array(
            'effectif' => new Expression('count(responsableId)')
        ))
            ->where($where);
        $statement = $this->sql->prepareStatementForSqlObject($select);
        // $statement->execute() renvoie un \Zend\Db\Adapter\Driver\ResultInterface
        return iterator_to_array($statement->execute());
    }
}