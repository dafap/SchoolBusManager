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
 * @date 26 oct. 2018
 * @version 2019-2.5.0
 */
namespace SbmCommun\Model\Db\Service\Query\Eleve;

use SbmBase\Model\Session;
use SbmCommun\Model\Db\Exception;
use SbmCommun\Model\Db\Service\DbManager;
use SbmCommun\Model\Db\Sql\Predicate\Not;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Where;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

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
    protected $db_manager;

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
        $this->millesime = Session::get('millesime');
        $this->dbAdapter = $this->db_manager->getDbAdapter();
        $this->sql = new Sql($this->dbAdapter);
        return $this;
    }

    /**
     * Renvoie le tableau statistiques des élèves enregistrés par millesime
     *
     * @param int $millesime
     *            Si le millesime est donné, le tableau renvoyé n'a qu'un seul élément d'index 0
     *            
     * @return array Les enregistrements du tableau (indexé à partir de 0) sont des tableaux
     *         associatifs dont les clés sont 'millesime' et 'effectif'
     */
    public function getNbEnregistresByMillesime($millesime = null)
    {
        $select = $this->sql->select();
        $select->from($this->db_manager->getCanonicName('scolarites', 'table'))
            ->columns([
            'millesime',
            'effectif' => new Expression('count(eleveId)')
        ])
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
     * @return array Les enregistrements du tableau (indexé à partir de 0) sont des tableaux
     *         associatifs dont les clés sont 'millesime' et 'effectif'
     */
    public function getNbInscritsByMillesime($millesime = null)
    {
        $where = new Where();
        $where->literal('inscrit = 1')
            ->nest()
            ->literal('paiement = 1')->or->literal('fa = 1')->or->literal('gratuit > 0')->unnest();
        if (isset($millesime)) {
            $where->equalTo('millesime', $millesime);
        }
        $select = $this->sql->select();
        $select->from($this->db_manager->getCanonicName('scolarites', 'table'))
            ->columns([
            'millesime',
            'effectif' => new Expression('count(eleveId)')
        ])
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
     * @return array Les enregistrements du tableau (indexé à partir de 0) sont des tableaux
     *         associatifs dont les clés sont 'millesime' et 'effectif'
     */
    public function getNbPreinscritsByMillesime($millesime = null)
    {
        $where = new Where();
        $where->literal('inscrit = 1')
            ->literal('paiement = 0')
            ->literal('fa = 0')
            ->literal('gratuit = 0');
        if (isset($millesime)) {
            $where->equalTo('millesime', $millesime);
        }
        $select = $this->sql->select();
        $select->from($this->db_manager->getCanonicName('scolarites', 'table'))
            ->columns([
            'millesime',
            'effectif' => new Expression('count(eleveId)')
        ])
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
     * @return array Les enregistrements du tableau (indexé à partir de 0) sont des tableaux
     *         associatifs dont les clés sont 'millesime' et 'effectif'
     */
    public function getNbFamilleAccueilByMillesime($millesime = null)
    {
        $where = new Where();
        $where->literal('inscrit = 1')->literal('fa = 1');
        if (isset($millesime)) {
            $where->equalTo('millesime', $millesime);
        }
        $select = $this->sql->select();
        $select->from($this->db_manager->getCanonicName('scolarites', 'table'))
            ->columns([
            'millesime',
            'effectif' => new Expression('count(eleveId)')
        ])
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
     * @param string $inscrits
     *            si true alors on ne compte que les inscrits rayés, sinon on ne compte que les
     *            préinscrits
     *            
     * @return array Les enregistrements du tableau (indexé à partir de 0) sont des tableaux
     *         associatifs dont les clés sont 'millesime' et 'effectif'
     */
    public function getNbRayesByMillesime($millesime = null, $inscrits = true)
    {
        $where = new Where();
        $where->literal('inscrit = 0');
        if ($inscrits) {
            $where->nest()->literal('paiement = 1')->or->literal('fa = 1')->or->literal(
                'gratuit > 0')->unnest();
        } else {
            $where1 = new Where();
            $where1->literal('paiement = 1')->or->literal('fa = 1')->or->literal(
                'gratuit > 0');
            $where->addPredicate(new Not($where1));
        }
        if (isset($millesime)) {
            $where->equalTo('millesime', $millesime);
        }
        $select = $this->sql->select();
        $select->from($this->db_manager->getCanonicName('scolarites', 'table'))
            ->columns([
            'millesime',
            'effectif' => new Expression('count(eleveId)')
        ])
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
     * @return array Les enregistrements du tableau (indexé à partir de 0) sont des tableaux
     *         associatifs dont les clés sont 'millesime' et 'effectif'
     */
    public function getNbGardeAlterneeByMillesime($millesime = null)
    {
        $where = new Where();
        $where->literal('inscrit = 1')
            ->nest()
            ->literal('paiement = 1')->or->literal('fa = 1')->or->literal('gratuit > 0')
            ->unnest()
            ->isNotNull('responsable2Id');
        if (isset($millesime)) {
            $where->equalTo('millesime', $millesime);
        }
        $select = $this->sql->select();
        $select->from(
            [
                'sco' => $this->db_manager->getCanonicName('scolarites', 'table')
            ])
            ->columns([
            'millesime',
            'effectif' => new Expression('count(ele.eleveId)')
        ])
            ->join([
            'ele' => $this->db_manager->getCanonicName('eleves', 'table')
        ], 'ele.eleveId = sco.eleveId', [])
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
     * @return array Les enregistrements du tableau (indexé à partir de 0) sont des tableaux
     *         associatifs dont les clés sont 'millesime' et 'effectif'
     */
    public function getNbByMillesimeEtablissement($millesime = null)
    {
        $where = new Where();
        $where->literal('inscrit = 1')
            ->nest()
            ->literal('paiement = 1')->or->literal('fa = 1')->or->literal('gratuit > 0')->unnest();
        if (isset($millesime)) {
            $where->equalTo('millesime', $millesime);
        }
        $select = $this->sql->select();
        $select->from(
            [
                'sco' => $this->db_manager->getCanonicName('scolarites', 'table')
            ])
            ->columns([
            'millesime',
            'effectif' => new Expression('count(eleveId)')
        ])
            ->join(
            [
                'eta' => $this->db_manager->getCanonicName('etablissements', 'table')
            ], 'sco.etablissementId = eta.etablissementId', [])
            ->join([
            'com' => $this->db_manager->getCanonicName('communes', 'table')
        ], 'com.communeId = eta.communeId',
            [
                'etablissement' => new Expression('concat(com.nom, " - ", eta.nom)')
            ])
            ->where($where)
            ->group([
            'millesime',
            'com.nom',
            'eta.nom'
        ]);
        $statement = $this->sql->prepareStatementForSqlObject($select);
        return iterator_to_array($statement->execute());
    }

    /**
     * Renvoie un tableau statistiques des élèves inscrits par millesime et classe
     *
     * @param string $millesime
     *            Si le millesime est donné, le tableau renvoyé n'a qu'un seul élément d'index 0
     *            
     * @return array Les enregistrements du tableau (indexé à partir de 0) sont des tableaux
     *         associatifs dont les clés sont 'millesime' et 'effectif'
     */
    public function getNbByMillesimeClasse($millesime = null)
    {
        $where = new Where();
        $where->literal('inscrit = 1')
            ->nest()
            ->literal('paiement = 1')->or->literal('fa = 1')->or->literal('gratuit > 0')->unnest();
        if (isset($millesime)) {
            $where->equalTo('millesime', $millesime);
        }
        $select = $this->sql->select();
        $select->from(
            [
                'sco' => $this->db_manager->getCanonicName('scolarites', 'table')
            ])
            ->columns([
            'millesime',
            'effectif' => new Expression('count(eleveId)')
        ])
            ->join([
            'cla' => $this->db_manager->getCanonicName('classes', 'table')
        ], 'sco.classeId = cla.classeId', [
            'classe' => 'nom'
        ])
            ->where($where)
            ->group([
            'millesime',
            'cla.nom'
        ]);
        $statement = $this->sql->prepareStatementForSqlObject($select);
        return iterator_to_array($statement->execute());
    }

    /**
     * Renvoie le tableau statistiques des élèves enregistrés à une distance inférieure à 1 km par
     * millesime
     *
     * @param int $millesime
     *            Si le millesime est donné, le tableau renvoyé n'a qu'un seul élément d'index 0
     *            
     * @return array Les enregistrements du tableau (indexé à partir de 0) sont des tableaux
     *         associatifs dont les clés sont 'millesime' et 'effectif'
     */
    public function getNbMoins1KmByMillesime($millesime = null)
    {
        $where = new Where();
        $where->lessThan('distanceR1', 1)->lessThan('distanceR2', 1);
        if (isset($millesime)) {
            $where->equalTo('millesime', $millesime);
        }
        $select = $this->sql->select();
        $select->from($this->db_manager->getCanonicName('scolarites', 'table'))
            ->columns([
            'millesime',
            'effectif' => new Expression('count(eleveId)')
        ])
            ->where($where)
            ->group('millesime');
        $statement = $this->sql->prepareStatementForSqlObject($select);
        // $statement->execute() renvoie un \Zend\Db\Adapter\Driver\ResultInterface
        return iterator_to_array($statement->execute());
    }

    /**
     * Renvoie le tableau statistiques des élèves enregistrés à une distance de 1 km à moins de 3km
     * par millesime
     *
     * @param int $millesime
     *            Si le millesime est donné, le tableau renvoyé n'a qu'un seul élément d'index 0
     *            
     * @return array Les enregistrements du tableau (indexé à partir de 0) sont des tableaux
     *         associatifs dont les clés sont 'millesime' et 'effectif'
     */
    public function getNbDe1A3KmByMillesime($millesime = null)
    {
        $where = new Where();
        $where->lessThan('distanceR1', 3)
            ->lessThan('distanceR2', 3)
            ->nest()
            ->greaterThanOrEqualTo('distanceR1', 1)->or->greaterThanOrEqualTo(
            'distanceR2', 1)->unnest();
        if (isset($millesime)) {
            $where->equalTo('millesime', $millesime);
        }
        $select = $this->sql->select();
        $select->from($this->db_manager->getCanonicName('scolarites', 'table'))
            ->columns([
            'millesime',
            'effectif' => new Expression('count(eleveId)')
        ])
            ->where($where)
            ->group('millesime');
        $statement = $this->sql->prepareStatementForSqlObject($select);
        // $statement->execute() renvoie un \Zend\Db\Adapter\Driver\ResultInterface
        return iterator_to_array($statement->execute());
    }

    /**
     * Renvoie le tableau statistiques des élèves enregistrés à une distance d'au moins 3 km par
     * millesime
     *
     * @param int $millesime
     *            Si le millesime est donné, le tableau renvoyé n'a qu'un seul élément d'index 0
     *            
     * @return array Les enregistrements du tableau (indexé à partir de 0) sont des tableaux
     *         associatifs dont les clés sont 'millesime' et 'effectif'
     */
    public function getNb3kmEtPlusByMillesime($millesime = null)
    {
        $where = new Where();
        $where->nest()->greaterThanOrEqualTo('distanceR1', 3)->or->greaterThanOrEqualTo(
            'distanceR2', 3)->unnest();
        if (isset($millesime)) {
            $where->equalTo('millesime', $millesime);
        }
        $select = $this->sql->select();
        $select->from($this->db_manager->getCanonicName('scolarites', 'table'))
            ->columns([
            'millesime',
            'effectif' => new Expression('count(eleveId)')
        ])
            ->where($where)
            ->group('millesime');
        $statement = $this->sql->prepareStatementForSqlObject($select);
        // $statement->execute() renvoie un \Zend\Db\Adapter\Driver\ResultInterface
        return iterator_to_array($statement->execute());
    }
}