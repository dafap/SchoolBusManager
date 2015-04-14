<?php
/**
 * Requêtes permettant d'obtenir les enfants inscrits ou préinscrits d'un responsable donné
 *
 * @project sbm
 * @package SbmCommun/Model/Db/Service/Query/Eleve
 * @filesource ElevesScolarites.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 9 avr. 2015
 * @version 2015-1
 */
namespace SbmCommun\Model\Db\Service\Query\Eleve;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use DafapSession\Model\Session;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Where;

class ElevesScolarites implements FactoryInterface
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
            'ele' => $this->db->getCanonicName('eleves', 'table')
        ))
            ->columns(array(
            'eleveId' => 'eleveId',
            'dateCreation' => 'dateCreation',
            'dateModificationEleve' => 'dateModification',
            'nom' => 'nom',
            'nomSA' => 'nomSA',
            'prenom' => 'prenom',
            'prenomSA' => 'prenomSA',
            'dateN' => 'dateN',
            'numero' => 'numero',
            'responsable1Id' => 'responsable1Id',
            'x1' => 'x1',
            'y1' => 'y1',
            'responsable2Id' => 'responsable2Id',
            'x2' => 'x2',
            'y2' => 'y2',
            'responsableFId' => 'responsableFId',
            'selectionEleve' => 'selection',
            'noteEleve' => 'note'
        ))
            ->join(array(
            'sco' => $this->db->getCanonicName('scolarites', 'table')
        ), 'ele.eleveId = sco.eleveId', array(
            'millesime' => 'millesime',
            'selectionScolarite' => 'selection',
            'dateInscription' => 'dateInscription',
            'dateModificationScolarite' => 'dateModification',
            'etablissementId' => 'etablissementId',
            'classeId' => 'classeId',
            'chez' => 'chez',
            'adresseL1' => 'adresseL1',
            'adresseL2' => 'adresseL2',
            'codePostal' => 'codePostal',
            'communeId' => 'communeId',
            'x' => 'x',
            'y' => 'y',
            'distance' => 'distance',
            'dateEtiquette' => 'dateEtiquette',
            'dateCarte' => 'dateCarte',
            'inscrit' => 'inscrit',
            'gratuit' => 'gratuit',
            'paiement' => 'paiement',
            'anneeComplete' => 'anneeComplete',
            'subvention' => 'subvention',
            'derogation' => 'derogation',
            'dateDebut' => 'dateDebut',
            'dateFin' => 'dateFin',
            'joursTransport' => 'joursTransport',
            'subventionTaux' => 'subventionTaux',
            'tarifId' => 'tarifId',
            'regimeId' => 'regimeId',
            'derogationMotif' => 'derogationMotif'
        ))
        // 'noteScolarite' => 'note'
        
            ->join(array(
            'eta' => $this->db->getCanonicName('etablissements', 'table')
        ), 'sco.etablissementId = eta.etablissementId', array(
            'etablissement' => 'nom'
        ))
            ->join(array(
            'com' => $this->db->getCanonicName('communes', 'table')
        ), 'eta.communeId = com.communeId', array(
            'commune' => 'nom'
        ));
        return $this;
    }

    public function getElevesInscrits($responsableId)
    {
        $select = clone $this->select;
        $where = new Where();
        $where->equalTo('millesime', Session::get('millesime'))->and->literal('paiement = 1')->and->nest()->equalTo('responsable1Id', $responsableId)->or->equalTo('responsable2Id', $responsableId)->unnest();
        $statement = $this->sql->prepareStatementForSqlObject($select->where($where));
        return $statement->execute();
    }

    public function getElevesPreinscrits($responsableId)
    {
        $select = clone $this->select;
        $where = new Where();
        $where->equalTo('millesime', Session::get('millesime'))->and->literal('paiement = 0')->and->nest()->equalTo('responsable1Id', $responsableId)->or->equalTo('responsable2Id', $responsableId)->unnest();
        $statement = $this->sql->prepareStatementForSqlObject($select->where($where));
        return $statement->execute();
    }
}