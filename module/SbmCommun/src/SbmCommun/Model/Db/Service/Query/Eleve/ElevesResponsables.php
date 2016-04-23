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
 * @date 6 janv. 2016
 * @version 2016-1.7.1
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
use SbmCommun\Model\Db\Service\DbManager;
use SbmCommun\Model\Db\Exception;

class ElevesResponsables implements FactoryInterface
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
            'ele' => $this->db_manager->getCanonicName('eleves', 'table')
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
            'r1' => $this->db_manager->getCanonicName('responsables', 'table')
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
            'r1c' => $this->db_manager->getCanonicName('communes', 'table')
        ), 'r1.communeId=r1c.communeId', array(
            'communeR1' => 'nom'
        ));
        return $this;
    }

    /**
     * Renvoie un tableau contenant les données de l'élève et de son responsable 1
     *
     * @param int $eleveId            
     * @return array
     */
    public function getEleveResponsable1($eleveId)
    {
        $where = new Where();
        $where->equalTo('eleveId', $eleveId);
        $select = clone $this->select;
        $select->where($where);
        $statement = $this->sql->prepareStatementForSqlObject($select);
        return $statement->execute()->current();
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
        return new Paginator(new DbSelect($this->selectR2($where, $order), $this->db_manager->getDbAdapter()));
    }

    private function selectR2(Where $where, $order = null)
    {
        $select = clone $this->select;
        $select->join(array(
            'r2' => $this->db_manager->getCanonicName('responsables', 'table')
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
            'r2c' => $this->db_manager->getCanonicName('communes', 'table')
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
     * Noter que pour examiner le contenu de la requête, on peut la transformer en tableau
     * par la fonction php iterator_to_array().
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
        return new Paginator(new DbSelect($this->selectScolaritesR2($where, $order), $this->db_manager->getDbAdapter()));
    }

    private function selectScolaritesR2(Where $where, $order = null, $millesime = null)
    {
        if (is_null($millesime)) {
            $millesime = $this->millesime;
        }
        $where->equalTo('sco.millesime', $millesime);
        $select = clone $this->select;
        $select->join(array(
            'sco' => $this->db_manager->getCanonicName('scolarites', 'table')
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
            'eta' => $this->db_manager->getCanonicName('etablissements', 'table')
        ), 'sco.etablissementId = eta.etablissementId', array(
            'etablissement' => new Expression('CASE WHEN isnull(eta.alias) THEN eta.nom ELSE eta.alias END'),
            'xeta' => 'x',
            'yeta' => 'y'
        ))
            ->join(array(
            'com' => $this->db_manager->getCanonicName('communes', 'table')
        ), 'eta.communeId = com.communeId', array(
            'communeEtablissement' => 'nom'
        ))
            ->join(array(
            'cla' => $this->db_manager->getCanonicName('classes', 'table')
        ), 'cla.classeId = sco.classeId', array(
            'classe' => 'nom'
        ))
            ->join(array(
            'r2' => $this->db_manager->getCanonicName('responsables', 'table')
        ), 'ele.responsable2Id=r2.responsableId', array(
            'titreR2' => 'titre',
            'responsable2NomPrenom' => new Expression('CASE WHEN isnull(r2.responsableId) THEN "" ELSE concat(r2.nom," ",r2.prenom) END'),
            'adresseL1R2' => 'adresseL1',
            'adresseL2R2' => 'adresseL2',
            'codePostalR2' => 'codePostal',
            'emailR2' => 'email',
            'x2' => 'x',
            'y2' => 'y'
        ), $select::JOIN_LEFT)
            ->join(array(
            'r2c' => $this->db_manager->getCanonicName('communes', 'table')
        ), 'r2.communeId=r2c.communeId', array(
            'communeR2' => 'nom'
        ), $select::JOIN_LEFT)
            ->join(array(
            'aff' => $this->db_manager->getCanonicName('affectations', 'table')
        ), 'aff.millesime = sco.millesime And aff.eleveId = sco.eleveId', array(
            'affecte' => new Expression('count(aff.eleveId) > 0')
        ), $select::JOIN_LEFT)
            ->group('ele.eleveId');
        if (! is_null($order)) {
            $select->order($order);
        }
        //die($this->getSqlString($select->where($where)));
        return $select->where($where);
    }

    /**
     * Requête préparée renvoyant les positions géographiques des domiciles de l'élève (chez, responsable1, responsable2),
     *
     * @param Where $where            
     * @param string $order            
     * @return \Zend\Db\Adapter\Driver\ResultInterface
     */
    public function getLocalisation(Where $where, $order = null)
    {
        $select = $this->selectLocalisation($where, $order);
        $statement = $this->sql->prepareStatementForSqlObject($select);
        return $statement->execute();
        ;
    }

    private function selectLocalisation(Where $where, $order = null)
    {
        $where->equalTo('millesime', $this->millesime);
        $sql = new Sql($this->db_manager->getDbAdapter());
        $select = $sql->select();
        $select->from(array(
            'ele' => $this->db_manager->getCanonicName('eleves', 'table')
        ))
            ->columns(array(
            'id_ccda',
            'numero',
            'nom_eleve' => 'nomSA',
            'prenom_eleve' => 'prenomSA',
            'dateN',
            'X' => new Expression('IF(sco.x = 0 AND sco.y = 0, r1.x, sco.x)'),
            'Y' => new Expression('IF(sco.x = 0 AND sco.y = 0, r1.y, sco.y)')
        ))
            ->join(array(
            'sco' => $this->db_manager->getCanonicName('scolarites', 'table')
        ), 'ele.eleveId=sco.eleveId', array(
            'transportGA' => new Expression('CASE WHEN demandeR2 > 0 THEN "Oui" ELSE "Non" END'),
            'x_eleve' => 'x',
            'y_eleve' => 'y',
            'chez',
            'adresseL1_chez' => 'adresseL1',
            'adresseL2_chez' => 'adresseL2',
            'codePostal_chez' => 'codePostal'
        ))
            ->join(array(
            'comsco' => $this->db_manager->getCanonicName('communes', 'table')
        ), 'sco.communeId=comsco.communeId', array(
            'commune_chez' => 'nom'
        ), $select::JOIN_LEFT)
            ->join(array(
            'eta' => $this->db_manager->getCanonicName('etablissements', 'table')
        ), 'sco.etablissementId=eta.etablissementId', array(
            'etablissement' => new Expression('CASE WHEN isnull(eta.alias) THEN eta.nom ELSE eta.alias END'),
            'x_etablissement' => 'x',
            'y_etablissement' => 'y'
        ))
            ->join(array(
            'cometa' => $this->db_manager->getCanonicName('communes', 'table')
        ), 'cometa.communeId=eta.communeId', array(
            'commune_etablissement' => 'nom'
        ))
            ->join(array(
            'cla' => $this->db_manager->getCanonicName('classes', 'table')
        ), 'sco.classeId=cla.classeId', array(
            'classe' => 'nom'
        ))
            ->join(array(
            'r1' => $this->db_manager->getCanonicName('responsables', 'table')
        ), 'r1.responsableId=ele.responsable1Id', array(
            'responsable1' => new Expression('concat(r1.nom," ",r1.prenom)'),
            'x_responsable1' => 'x',
            'y_responsable1' => 'y',
            'telephoneF_responsable1' => 'telephoneF',
            'telephoneP_responsable1' => 'telephoneP',
            'telephoneT_responsable1' => 'telephoneT',
            'email_responsable1' => 'email',
            'adresseL1_responsable1' => 'adresseL1',
            'adresseL2_responsable1' => 'adresseL2',
            'codePostal_responsable1' => 'codePostal'
        ))
            ->join(array(
            'comr1' => $this->db_manager->getCanonicName('communes', 'table')
        ), 'comr1.communeId=r1.communeId', array(
            'commune_responsable1' => 'nom'
        ))
            ->join(array(
            'r2' => $this->db_manager->getCanonicName('responsables', 'table')
        ), 'r2.responsableId=ele.responsable2Id', array(
            'responsable2' => new Expression('CASE WHEN isnull(r2.responsableId) THEN "" ELSE concat(r2.nom," ",r2.prenom) END'),
            'x_responsable2' => 'x',
            'y_responsable2' => 'y',
            'telephoneF_responsable2' => 'telephoneF',
            'telephoneP_responsable2' => 'telephoneP',
            'telephoneT_responsable2' => 'telephoneT',
            'email_responsable2' => 'email',
            'adresseL1_responsable2' => 'adresseL1',
            'adresseL2_responsable2' => 'adresseL2',
            'codePostal_responsable2' => 'codePostal'
        ), $select::JOIN_LEFT)
            ->join(array(
            'comr2' => $this->db_manager->getCanonicName('communes', 'table')
        ), 'comr2.communeId=r2.communeId', array(
            'commune_responsable2' => 'nom'
        ), $select::JOIN_LEFT);
        if (! is_null($order)) {
            $select->order($order);
        }
        return $select->where($where);
    }
}