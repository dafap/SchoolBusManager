<?php
/**
 * Requête permettant d'obtenir les renseignements complets sur les élèves et leurs responsables
 *
 * 
 * @project sbm
 * @package SbmCommun/Model/Db/Service/Query/Eleve
 * @filesource ElevesResponsables.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 10 mai 2015
 * @version 2015-1
 */
namespace SbmCommun\Model\Db\Service\Query\Eleve;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use DafapSession\Model\Session;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Predicate\Predicate;
use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\DbSelect;

class ElevesResponsables implements FactoryInterface
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
            'responsable2Id' => 'responsable2Id',
            'responsableFId' => 'responsableFId',
            'selectionEleve' => 'selection',
            'noteEleve' => 'note'
        ))
            ->join(array(
            'r1' => $this->db->getCanonicName('responsables', 'table')
        ), 'ele.responsable1Id=r1.responsableId', array(
            'titreR1' => 'titre',
            'responsable1NomPrenom' => new Expression('concat(r1.nom," ",r1.prenom)'),
            'adresseL1R1' => 'adresseL1',
            'adresseL2R1' => 'adresseL2',
            'codePostalR1' => 'codePostal',
            'telephoneFR1' => 'telephoneF',
            'telephonePR1' => 'telephoneP',
            'telephoneTR1' => 'telephoneT',
            'emailR1' => 'email',           
            'x1' => 'x',
            'y1' => 'y'
        ))
            ->join(array(
            'r1c' => $this->db->getCanonicName('communes', 'table')
        ), 'r1.communeId=r1c.communeId', array(
            'communeR1' => 'nom'
        ));
        return $this;
    }

    /**
     * Renvoie le résultat de la requête
     * 
     * @param Where $where            
     * @param string|array $order            
     * @return \Zend\Db\Adapter\Driver\ResultInterface
     */
    public function withR2(Where $where, $order = null)
    {
        $select = $this->selectR2($where, $order);
        $statement = $this->sql->prepareStatementForSqlObject($select);
        return $statement->execute();
    }
    public function paginatorR2(Where $where, $order = null)
    {
        return new Paginator(new DbSelect($this->selectR2($where, $order), $this->db->getDbAdapter()));
    }
    private function selectR2(Where $where, $order = null)
    {
        $select = clone $this->select;
        $select->join(array(
            'r2' => $this->db->getCanonicName('responsables', 'table')
        ), 'ele.responsable2Id=r2.responsableId', array(
            'titreR2' => 'titre',
            'responsable2NomPrenom' => new Expression('CASE WHEN isnull(r2.responsableId) THEN "" ELSE concat(r2.nom," ",r2.prenom) END'),
            'adresseL1R2' => 'adresseL1',
            'adresseL2R2' => 'adresseL2',
            'codePostalR2' => 'codePostal',
            'telephoneFR2' => 'telephoneF',
            'telephonePR2' => 'telephoneP',
            'telephoneTR2' => 'telephoneT',
            'emailR2' => 'email',
            'x2' => 'x',
            'y2' => 'y'
        ), $select::JOIN_LEFT)
            ->join(array(
            'r2c' => $this->db->getCanonicName('communes', 'table')
        ), 'r2.communeId=r2c.communeId', array(
            'communeR2' => 'nom'
        ), $select::JOIN_LEFT);
        if (! is_null($order)) {
            $select->order($order);
        }
        return $select->where($where);
    }

    /**
     * Si on ne précise pas le millesime, on utilise le millesime courant
     *
     * @param Where $where            
     * @param string|array $order 
     * @param int $millesime            
     * @return \Zend\Db\Adapter\Driver\ResultInterface
     */
    public function withScolaritesR2(Where $where, $order = null, $millesime = null)
    {
        $select = $this->selectScolaritesR2($where, $order, $millesime);
        $statement = $this->sql->prepareStatementForSqlObject($select);
        return $statement->execute();
    }
    public function paginatorScolaritesR2(Where $where, $order = null, $millesime = null)
    {
        return new Paginator(new DbSelect($this->selectScolaritesR2($where, $order), $this->db->getDbAdapter()));
    }
    private function selectScolaritesR2(Where $where, $order = null, $millesime = null)
    { 
        if (is_null($millesime)) {
            $millesime = $this->millesime;
        }
        $where->equalTo('millesime', $millesime);
        $select = clone $this->select;
        $select->join(array(
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
            'distanceR1' => 'distanceR1',
            'distanceR2' => 'distanceR2',
            'dateEtiquette' => 'dateEtiquette',
            'dateCarte' => 'dateCarte',
            'inscrit' => 'inscrit',
            'gratuit' => 'gratuit',
            'paiement' => 'paiement',
            'fa' => 'fa',
            'anneeComplete' => 'anneeComplete',
            'subventionR1' => 'subventionR1',
            'subventionR2' => 'subventionR2',
            'demandeR1' => 'demandeR1',
            'demandeR2' => 'demandeR2',
            'accordR1' => 'accordR1',
            'accordR2' => 'accordR2',
            'internet' => 'internet',
            'district' => 'district',
            'derogation' => 'derogation',
            'dateDebut' => 'dateDebut',
            'dateFin' => 'dateFin',
            'joursTransport' => 'joursTransport',
            'subventionTaux' => 'subventionTaux',
            'tarifId' => 'tarifId',
            'regimeId' => 'regimeId',
            'motifDerogation' => 'motifDerogation',
            'motifRefusR1' => 'motifRefusR1',
            'motifRefusR2' => 'motifRefusR2',
            'commentaire' => 'commentaire'
        ))
            ->join(array(
            'eta' => $this->db->getCanonicName('etablissements', 'table')
        ), 'sco.etablissementId = eta.etablissementId', array(
            'etablissement' => new Expression('CASE WHEN isnull(eta.alias) THEN eta.nom ELSE eta.alias END'),
            'xeta' => 'x',
            'yeta' => 'y'
        ))
            ->join(array(
            'com' => $this->db->getCanonicName('communes', 'table')
        ), 'eta.communeId = com.communeId', array(
            'communeEtablissement' => 'nom'
        ))
            ->join(array(
            'r2' => $this->db->getCanonicName('responsables', 'table')
        ), 'ele.responsable2Id=r2.responsableId', array(
            'responsable2NomPrenom' => new Expression('CASE WHEN isnull(r2.responsableId) THEN "" ELSE concat(r2.nom," ",r2.prenom) END'),
            'adresseL1R2' => 'adresseL1',
            'adresseL2R2' => 'adresseL2',
            'codePostalR2' => 'codePostal',
            'x2' => 'x',
            'y2' => 'y'
        ), $select::JOIN_LEFT)
            ->join(array(
            'r2c' => $this->db->getCanonicName('communes', 'table')
        ), 'r2.communeId=r2c.communeId', array(
            'communeR2' => 'nom'
        ), $select::JOIN_LEFT);
        if (! is_null($order)) {
            $select->order($order);
        }
        return $select->where($where);
    }
}