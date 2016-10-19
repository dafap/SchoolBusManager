<?php
/**
 * Quelques requêtes utiles à partir de la table des responsables
 * (enregistré dans module.config.php sous 'Sbm\Db\Query\Responsables')
 *
 * @project sbm
 * @package SbmCommun/Model/Db/Service/Query
 * @filesource Responsables.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 17 août 2016
 * @version 2016-2.2.0
 */
namespace SbmCommun\Model\Db\Service\Query\Responsable;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Predicate\Predicate;
use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\DbSelect;
use SbmBase\Model\Session;
use SbmCommun\Model\Db\Service\DbManager;
use SbmCommun\Model\Db\Exception;

class Responsables implements FactoryInterface
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
     *
     * @var \Zend\Db\Sql\Select
     */
    protected $select;

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
        $this->select = $this->sql->select()
            ->from(array(
            'res' => $this->db_manager->getCanonicName('responsables', 'table')
        ))
            ->columns(array(
            'responsableId' => 'responsableId',
            'selection' => 'selection',
            'dateCreation' => 'dateCreation',
            'dateModification' => 'dateModification',
            'nature' => 'nature',
            'titre' => 'titre',
            'nom' => 'nom',
            'nomSA' => 'nomSA',
            'prenom' => 'prenom',
            'prenomSA' => 'prenomSA',
            'titre2' => 'titre2',
            'nom2' => 'nom2',
            'nom2SA' => 'nom2SA',
            'prenom2' => 'prenom2',
            'prenom2SA' => 'prenom2SA',
            'adresseL1' => 'adresseL1',
            'adresseL2' => 'adresseL2',
            'codePostal' => 'codePostal',
            'communeId' => 'communeId',
            'ancienAdresseL1' => 'ancienAdresseL1',
            'ancienAdresseL2' => 'ancienAdresseL2',
            'ancienCodePostal' => 'ancienCodePostal',
            'ancienCommuneId' => 'ancienCommuneId',
            'email' => 'email',
            'telephoneF' => 'telephoneF',
            'telephoneP' => 'telephoneP',
            'telephoneT' => 'telephoneT',
            'etiquette' => 'etiquette',
            'demenagement' => 'demenagement',
            'dateDemenagement' => 'dateDemenagement',
            'facture' => 'facture',
            'grilleTarif' => 'grilleTarif',
            'ribTit' => 'ribTit',
            'ribDom' => 'ribDom',
            'iban' => 'iban',
            'bic' => 'bic',
            'x' => 'x',
            'y' => 'y',
            'userId' => 'userId',
            'note' => 'note'
        ))
            ->join(array(
            'com' => $this->db_manager->getCanonicName('communes', 'table')
        ), 'com.communeId=res.communeId', array(
            'commune' => 'nom'
        ));
        return $this;
    }

    /**
     * Renvoie la liste des responsables avec le nombre d'élèves inscrits répondant au where passé en paramètre, dans l'ordre demandé
     *
     * @param Where $where            
     * @param string $order            
     * @return \Zend\Db\Adapter\Driver\ResultInterface
     */
    public function withNbElevesInscrits(Where $where, $order = null)
    {
        $where->literal('inscrit = 1')
            ->nest()
            ->literal('paiement = 1')->OR->literal('fa = 1')->OR->literal('gratuit > 0')
            ->unnest()
            ->equalTo('millesime', $this->millesime);
        $select = clone $this->select;
        $select->join(array(
            'ele' => $this->db_manager->getCanonicName('eleves', 'table')
        ), 'res.responsableId = ele.responsable1Id Or res.responsableId = ele.responsable2Id', array(
            'nb' => new Expression('count(ele.eleveId)')
        ))
            ->join(array(
            'sco' => $this->db_manager->getCanonicName('scolarites', 'table')
        ), 'ele.eleveId=sco.eleveId', array())
            ->where($where);
        if (! is_null($order)) {
            $select->order($order);
        }
        $statement = $this->sql->prepareStatementForSqlObject($select);
        return $statement->execute();
    }

    public function getNbEnfantsInscrits($responsableId)
    {
        $where = new Where();
        $where->equalTo('responsableId', $responsableId);
        $result = $this->withNbElevesInscrits($where);
        return $result->current()['nb'];
    }

    public function hasEnfantInscrit($responsableId)
    {
        return $this->getNbEnfantsInscrits($responsableId) > 0;
    }

    /**
     * Renvoie le résultat d'une requête avec nombre d'enfants, d'inscrits et de préinscrits
     *
     * @param \Zend\Db\Sql\Where $where            
     * @param array $order            
     * @return \Zend\Db\Adapter\Driver\ResultInterface
     */
    public function withEffectifs($where, $order = null)
    {
        $select = $this->selectResponsables($where, $order);
        $statement = $this->sql->prepareStatementForSqlObject($select);
        return $statement->execute();
    }

    /**
     * Renvoie un paginator sur la requête donnant les responsables avec la commune et le nombre d'enfants
     * connus, inscrits et préinscrits
     *
     * @param \Zend\Db\Sql\Where $where            
     * @param array $order            
     * @return \Zend\Paginator\Paginator
     */
    public function paginator($where, $order)
    {
        return new Paginator(new DbSelect($this->selectResponsables($where, $order), $this->db_manager->getDbAdapter()));
    }

    /**
     * Renvoie un Select définissant la requête
     *
     * @param \Zend\Db\Sql\Where $where            
     * @param array $order            
     * @return \Zend\Db\Sql\Select
     */
    private function selectResponsables($where, $order)
    {
        // préinscrits
        $where1 = new Where();
        $where1->literal('inscrit = 1')
            ->literal('paiement = 0')
            ->equalTo('millesime', $this->millesime);
        $select1 = new Select();
        $select1->from($this->db_manager->getCanonicName('scolarites', 'table'))
            ->columns(array(
            'eleveId'
        ))
            ->where($where1);
        // inscrits payants
        $where2 = new Where();
        $where2->literal('inscrit = 1')
            ->literal('paiement = 1')
            ->equalTo('millesime', $this->millesime);
        $select2 = new Select();
        $select2->from($this->db_manager->getCanonicName('scolarites', 'table'))
            ->columns(array(
            'eleveId'
        ))
            ->where($where2);
        // inscrits gratuits
        $where3 = new Where();
        $where3->literal('inscrit = 1')
            ->literal('gratuit = 1')
            ->equalTo('millesime', $this->millesime);
        $select3 = new Select();
        $select3->from($this->db_manager->getCanonicName('scolarites', 'table'))
            ->columns(array(
            'eleveId'
        ))
            ->where($where3);
        // inscrits en famille d'accueil
        $where4 = new Where();
        $where4->literal('inscrit = 1')
            ->literal('fa = 1')
            ->equalTo('millesime', $this->millesime);
        $select4 = new Select();
        $select4->from($this->db_manager->getCanonicName('scolarites', 'table'))
            ->columns(array(
            'eleveId'
        ))
            ->where($where4);
        // duplicata
        $where5 = new Where();
        $where5->literal('inscrit = 1')
            ->literal('duplicata > 0')
            ->equalTo('millesime', $this->millesime);
        $select5 = new Select();
        $select5->from($this->db_manager->getCanonicName('scolarites', 'table'))
            ->columns(array(
            'eleveId',
            'duplicata'
        ))
            ->where($where5);
        // requête principale
        $select = clone $this->select;
        $select->join(array(
            'ele' => $this->db_manager->getCanonicName('eleves', 'table')
        ), 'res.responsableId = ele.responsable1Id Or res.responsableId = ele.responsable2Id', array(
            'nbEnfants' => new Expression('count(ele.eleveId)')
        ), $select::JOIN_LEFT)
            ->join(array(
            'pre' => $select1
        ), 'ele.eleveId=pre.eleveId', array(
            'nbPreinscrits' => new Expression('count(pre.eleveId)')
        ), $select::JOIN_LEFT)
            ->join(array(
            'ins' => $select2
        ), 'ele.eleveId=ins.eleveId', array(
            'nbInscrits' => new Expression('count(ins.eleveId)')
        ), $select::JOIN_LEFT)
            ->join(array(
            'gra' => $select3
        ), 'ele.eleveId=gra.eleveId', array(
            'nbGratuits' => new Expression('count(gra.eleveId)')
        ), $select::JOIN_LEFT)
            ->join(array(
            'fa' => $select4
        ), 'ele.eleveId=fa.eleveId', array(
            'nbFa' => new Expression('count(fa.eleveId)')
        ), $select::JOIN_LEFT)
            ->join(array(
            'dup' => $select5
        ), 'ele.eleveId=dup.eleveId', array(
            'nbDuplicata' => new Expression('sum(dup.duplicata)')
        ), $select::JOIN_LEFT)
            ->group('responsableId')
            ->order($order);
        return $where->count() ? $select->having($where) : $select;
    }

    /**
     * Renvoie vrai si le responsable existe et s'il a des enfants inscrits dans ce millesime
     *
     * @param string $nomSA            
     * @param string $prenomSA            
     */
    public function estDejaInscritCetteAnnee($nomSA, $prenomSA)
    {
        $where = new Where();
        $where->equalTo('res.nomSA', $nomSA)
            ->equalTo('res.prenomSA', $prenomSA)
            ->equalTo('sco.millesime', $this->millesime);
        $select = $this->sql->select([
            'res' => $this->db_manager->getCanonicName('responsables', 'table')
        ])
            ->join(array(
            'ele' => $this->db_manager->getCanonicName('eleves', 'table')
        ), 'res.responsableId = ele.responsable1Id Or res.responsableId = ele.responsable2Id', [])
            ->join([
            'sco' => $this->db_manager->getCanonicName('scolarites', 'table')
        ], 'sco.eleveId = ele.eleveId', [])
            ->columns([
            'nbEnfants' => new Expression('count(sco.eleveId)')
        ])
            ->where($where);
        $statement = $this->sql->prepareStatementForSqlObject($select);
        return $statement->execute()->current()['nbEnfants'] > 0;
    }
}