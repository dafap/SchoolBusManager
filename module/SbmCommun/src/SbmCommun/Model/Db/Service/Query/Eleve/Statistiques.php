<?php
/**
 * Requêtes pour les statistiques concernant les élèves
 * (classe déclarée dans mocule.config.php sous l'alias 'Sbm\Statistiques\Eleve')
 * 
 * @project sbm
 * @package SbmCommun/Model/Db/Service/Query/Eleve
 * @filesource Statistiques.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 25 juin 2015
 * @version 2015-1
 */
namespace SbmCommun\Model\Db\Service\Query\Eleve;

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

    /**
     * Renvoie le tableau statistiques des élèves enregistrés par millesime
     *
     * @param int $millesime
     *            Si le millesime est donné, le tableau renvoyé n'a qu'un seul élément d'index 0
     *            
     * @return array Les enregistrements du tableau (indexé à partir de 0) sont des tableaux associatifs dont les clés sont 'millesime' et 'effectif'
     */
    public function getNbEnregistresByMillesime($millesime = null)
    {
        $select = $this->sql->select();
        $select->from($this->db->getCanonicName('scolarites', 'table'))
            ->columns(array(
            'millesime',
            'effectif' => new Expression('count(eleveId)')
        ))
            ->group('millesime');
        if (isset($millesime)) {
            $where = new Where();
            $where->equalTo('millesime', $millesime);
            $select->where($where);
        }
        $statement = $this->sql->prepareStatementForSqlObject($select);
        // $statement->execute() renvoie un \Zend\Db\Adapter\Driver\ResultInterface
        return iterator_to_array($statement->execute());
    }

    /**
     * Renvoie un tableau statistiques des élèves inscrits par millesime
     *
     * @param string $millesime
     *            Si le millesime est donné, le tableau renvoyé n'a qu'un seul élément d'index 0
     *            
     * @return array Les enregistrements du tableau (indexé à partir de 0) sont des tableaux associatifs dont les clés sont 'millesime' et 'effectif'
     */
    public function getNbInscritsByMillesime($millesime = null)
    {
        $where = new Where();
        $where->literal('inscrit = 1')
            ->nest()
            ->literal('paiement = 1')->or->literal('fa = 1')->unnest();
        if (isset($millesime)) {
            $where->equalTo('millesime', $millesime);
        }
        $select = $this->sql->select();
        $select->from($this->db->getCanonicName('scolarites', 'table'))
            ->columns(array(
            'millesime',
            'effectif' => new Expression('count(eleveId)')
        ))
            ->where($where)
            ->group('millesime');
        $statement = $this->sql->prepareStatementForSqlObject($select);
        return iterator_to_array($statement->execute());
    }

    /**
     * Renvoie un tableau statistiques des élèves préinscrits par millesime
     *
     * @param string $millesime
     *            Si le millesime est donné, le tableau renvoyé n'a qu'un seul élément d'index 0
     *            
     * @return array Les enregistrements du tableau (indexé à partir de 0) sont des tableaux associatifs dont les clés sont 'millesime' et 'effectif'
     */
    public function getNbPreinscritsByMillesime($millesime = null)
    {
        $where = new Where();
        $where->literal('inscrit = 1')
            ->literal('paiement = 0')
            ->literal('fa = 0');
        if (isset($millesime)) {
            $where->equalTo('millesime', $millesime);
        }
        $select = $this->sql->select();
        $select->from($this->db->getCanonicName('scolarites', 'table'))
            ->columns(array(
            'millesime',
            'effectif' => new Expression('count(eleveId)')
        ))
            ->where($where)
            ->group('millesime');
        $statement = $this->sql->prepareStatementForSqlObject($select);
        return iterator_to_array($statement->execute());
    }

    /**
     * Renvoie un tableau statistiques des élèves en famille d'accueil par millesime
     *
     * @param string $millesime
     *            Si le millesime est donné, le tableau renvoyé n'a qu'un seul élément d'index 0
     *            
     * @return array Les enregistrements du tableau (indexé à partir de 0) sont des tableaux associatifs dont les clés sont 'millesime' et 'effectif'
     */
    public function getNbFamilleAccueilByMillesime($millesime = null)
    {
        $where = new Where();
        $where->literal('inscrit = 1')->literal('fa = 1');
        if (isset($millesime)) {
            $where->equalTo('millesime', $millesime);
        }
        $select = $this->sql->select();
        $select->from($this->db->getCanonicName('scolarites', 'table'))
            ->columns(array(
            'millesime',
            'effectif' => new Expression('count(eleveId)')
        ))
            ->where($where)
            ->group('millesime');
        $statement = $this->sql->prepareStatementForSqlObject($select);
        return iterator_to_array($statement->execute());
    }

    /**
     * Renvoie un tableau statistiques des élèves rayés par millesime
     *
     * @param string $millesime
     *            Si le millesime est donné, le tableau renvoyé n'a qu'un seul élément d'index 0
     *            
     * @return array Les enregistrements du tableau (indexé à partir de 0) sont des tableaux associatifs dont les clés sont 'millesime' et 'effectif'
     */
    public function getNbRayesByMillesime($millesime = null)
    {
        $where = new Where();
        $where->literal('inscrit = 0');
        if (isset($millesime)) {
            $where->equalTo('millesime', $millesime);
        }
        $select = $this->sql->select();
        $select->from($this->db->getCanonicName('scolarites', 'table'))
            ->columns(array(
            'millesime',
            'effectif' => new Expression('count(eleveId)')
        ))
            ->where($where)
            ->group('millesime');
        $statement = $this->sql->prepareStatementForSqlObject($select);
        return iterator_to_array($statement->execute());
    }

    /**
     * Renvoie un tableau statistiques des élèves en garde alternée par millesime
     *
     * @param string $millesime
     *            Si le millesime est donné, le tableau renvoyé n'a qu'un seul élément d'index 0
     *            
     * @return array Les enregistrements du tableau (indexé à partir de 0) sont des tableaux associatifs dont les clés sont 'millesime' et 'effectif'
     */
    public function getNbGardeAlterneeByMillesime($millesime = null)
    {
        $where = new Where();
        $where->literal('inscrit = 1')
            ->nest()
            ->literal('paiement = 1')->or->literal('fa = 1')
            ->unnest()
            ->isNotNull('responsable2Id');
        if (isset($millesime)) {
            $where->equalTo('millesime', $millesime);
        }
        $select = $this->sql->select();
        $select->from(array(
            'sco' => $this->db->getCanonicName('scolarites', 'table')
        ))
            ->columns(array(
            'millesime',
            'effectif' => new Expression('count(ele.eleveId)')
        ))
            ->join(array(
            'ele' => $this->db->getCanonicName('eleves', 'table')
        ), 'ele.eleveId = sco.eleveId', array())
            ->where($where)
            ->group('millesime');
        $statement = $this->sql->prepareStatementForSqlObject($select);
        return iterator_to_array($statement->execute());
    }

    /**
     * Renvoie un tableau statistiques des élèves inscrits par millesime et etablissement
     *
     * @param string $millesime
     *            Si le millesime est donné, le tableau renvoyé n'a qu'un seul élément d'index 0
     *            
     * @return array Les enregistrements du tableau (indexé à partir de 0) sont des tableaux associatifs dont les clés sont 'millesime' et 'effectif'
     */
    public function getNbByMillesimeEtablissement($millesime = null)
    {
        $where = new Where();
        $where->literal('inscrit = 1')
            ->nest()
            ->literal('paiement = 1')->or->literal('fa = 1')->unnest();
        if (isset($millesime)) {
            $where->equalTo('millesime', $millesime);
        }
        $select = $this->sql->select();
        $select->from(array(
            'sco' => $this->db->getCanonicName('scolarites', 'table')
        ))
            ->columns(array(
            'millesime',
            'effectif' => new Expression('count(eleveId)')
        ))
            ->join(array(
            'eta' => $this->db->getCanonicName('etablissements', 'table')
        ), 'sco.etablissementId = eta.etablissementId', array())
            ->join(array(
            'com' => $this->db->getCanonicName('communes', 'table')
        ), 'com.communeId = eta.communeId', array(
            'etablissement' => new Expression('concat(com.nom, " - ", eta.nom)')
        ))
            ->where($where)
            ->group(array(
            'millesime',
            'com.nom',
            'eta.nom'
        ));
        $statement = $this->sql->prepareStatementForSqlObject($select);
        return iterator_to_array($statement->execute());
    }

    /**
     * Renvoie un tableau statistiques des élèves inscrits par millesime et classe
     *
     * @param string $millesime
     *            Si le millesime est donné, le tableau renvoyé n'a qu'un seul élément d'index 0
     *            
     * @return array Les enregistrements du tableau (indexé à partir de 0) sont des tableaux associatifs dont les clés sont 'millesime' et 'effectif'
     */
    public function getNbByMillesimeClasse($millesime = null)
    {
        $where = new Where();
        $where->literal('inscrit = 1')
            ->nest()
            ->literal('paiement = 1')->or->literal('fa = 1')->unnest();
        if (isset($millesime)) {
            $where->equalTo('millesime', $millesime);
        }
        $select = $this->sql->select();
        $select->from(array(
            'sco' => $this->db->getCanonicName('scolarites', 'table')
        ))
            ->columns(array(
            'millesime',
            'effectif' => new Expression('count(eleveId)')
        ))
            ->join(array(
            'cla' => $this->db->getCanonicName('classes', 'table')
        ), 'sco.classeId = cla.classeId', array(
            'classe' => 'nom'
        ))
            ->where($where)
            ->group(array(
            'millesime',
            'cla.nom'
        ));
        $statement = $this->sql->prepareStatementForSqlObject($select);
        return iterator_to_array($statement->execute());
    }

    /**
     * Renvoie le tableau statistiques des élèves enregistrés à une distance inférieure à 1 km par millesime
     *
     * @param int $millesime
     *            Si le millesime est donné, le tableau renvoyé n'a qu'un seul élément d'index 0
     *            
     * @return array Les enregistrements du tableau (indexé à partir de 0) sont des tableaux associatifs dont les clés sont 'millesime' et 'effectif'
     */
    public function getNbMoins1KmByMillesime($millesime = null)
    {
        $where = new Where();
        $where->lessThan('distanceR1', 1)->lessThan('distanceR2', 1);
        if (isset($millesime)) {
            $where->equalTo('millesime', $millesime);
        }
        $select = $this->sql->select();
        $select->from($this->db->getCanonicName('scolarites', 'table'))
            ->columns(array(
            'millesime',
            'effectif' => new Expression('count(eleveId)')
        ))
            ->where($where)
            ->group('millesime');
        $statement = $this->sql->prepareStatementForSqlObject($select);
        // $statement->execute() renvoie un \Zend\Db\Adapter\Driver\ResultInterface
        return iterator_to_array($statement->execute());
    }

    /**
     * Renvoie le tableau statistiques des élèves enregistrés à une distance de 1 km à moins de 3km par millesime
     *
     * @param int $millesime
     *            Si le millesime est donné, le tableau renvoyé n'a qu'un seul élément d'index 0
     *            
     * @return array Les enregistrements du tableau (indexé à partir de 0) sont des tableaux associatifs dont les clés sont 'millesime' et 'effectif'
     */
    public function getNbDe1A3KmByMillesime($millesime = null)
    {
        $where = new Where();
        $where->lessThan('distanceR1', 3)
            ->lessThan('distanceR2', 3)
            ->nest()->greaterThanOrEqualTo('distanceR1', 1)->or->greaterThanOrEqualTo('distanceR2', 1)->unnest();
        if (isset($millesime)) {
            $where->equalTo('millesime', $millesime);
        }
        $select = $this->sql->select();
        $select->from($this->db->getCanonicName('scolarites', 'table'))
            ->columns(array(
            'millesime',
            'effectif' => new Expression('count(eleveId)')
        ))
            ->where($where)
            ->group('millesime');
        $statement = $this->sql->prepareStatementForSqlObject($select);
        // $statement->execute() renvoie un \Zend\Db\Adapter\Driver\ResultInterface
        return iterator_to_array($statement->execute());
    }
    
    /**
     * Renvoie le tableau statistiques des élèves enregistrés à une distance d'au moins 3 km par millesime
     *
     * @param int $millesime
     *            Si le millesime est donné, le tableau renvoyé n'a qu'un seul élément d'index 0
     *
     * @return array Les enregistrements du tableau (indexé à partir de 0) sont des tableaux associatifs dont les clés sont 'millesime' et 'effectif'
     */
    public function getNb3kmEtPlusByMillesime($millesime = null)
    {
        $where = new Where();
        $where->nest()->greaterThanOrEqualTo('distanceR1', 3)->or->greaterThanOrEqualTo('distanceR2', 3)->unnest();
        if (isset($millesime)) {
            $where->equalTo('millesime', $millesime);
        }
        $select = $this->sql->select();
        $select->from($this->db->getCanonicName('scolarites', 'table'))
        ->columns(array(
            'millesime',
            'effectif' => new Expression('count(eleveId)')
        ))
        ->where($where)
        ->group('millesime');
        $statement = $this->sql->prepareStatementForSqlObject($select);
        // $statement->execute() renvoie un \Zend\Db\Adapter\Driver\ResultInterface
        return iterator_to_array($statement->execute());
    }
    
}