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
 * @date 21 mai 2015
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
use Zend\Db\Sql\Predicate\Predicate;
use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\DbSelect;

class Responsables implements FactoryInterface
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

    /**
     *
     * @var \Zend\Db\Sql\Select
     */
    protected $select;

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $this->millesime = Session::get('millesime');
        $this->db = $serviceLocator->get('Sbm\Db\DbLib');
        $this->sql = new Sql($this->db->getDbAdapter());
        $this->select = $this->sql->select()
            ->from(array(
            'res' => $this->db->getCanonicName('responsables', 'table')
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
            'com' => $this->db->getCanonicName('communes', 'table')
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
        $where->nest()->literal('paiement=1')->OR->literal('fa=1')
            ->unnest()
            ->equalTo('millesime', $this->millesime);
        $select = clone $this->select;
        $select->join(array(
            'ele' => $this->db->getCanonicName('eleves', 'table')
        ), 'res.responsableId = ele.responsable1Id Or res.responsableId = ele.responsable2Id', array(
            'nb' => new Expression('count(ele.eleveId)')
        ))
            ->join(array(
            'sco' => $this->db->getCanonicName('scolarites', 'table')
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
     * Renvoie un paginator sur la requête donnant les responsables avec la commune et le nombre d'enfants
     * connus, inscrits et préinscrits
     *
     * @param \Zend\Db\Sql\Where $where            
     * @param array $order            
     * @return \Zend\Paginator\Paginator
     */
    public function paginator($where, $order)
    {
        return new Paginator(new DbSelect($this->selectResponsables($where, $order), $this->db->getDbAdapter()));
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
        $select1->from($this->db->getCanonicName('scolarites', 'table'))
            ->columns(array(
            'eleveId'
        ))
            ->where($where1);
        // inscrits
        $where2 = new Where();
        $where2->literal('inscrit = 1')
            ->literal('paiement = 1')
            ->equalTo('millesime', $this->millesime);
        $select2 = new Select();
        $select2->from($this->db->getCanonicName('scolarites', 'table'))
            ->columns(array(
            'eleveId'
        ))
            ->where($where2);
        
        // requête principale
        $select = clone $this->select;
        $select->join(array(
            'ele' => $this->db->getCanonicName('eleves', 'table')
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
            ->group('responsableId')
            ->order($order);
        return $where->count() ? $select->having($where) : $select;
    }
}