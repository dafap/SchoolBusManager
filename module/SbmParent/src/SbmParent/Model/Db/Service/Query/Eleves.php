<?php
/**
 * Requêtes nécessaires pour ce module
 *
 * Compatibilité ZF3
 * 
 * @project sbm
 * @package SbmParent/Model/Db/Service/Query
 * @filesource Eleves.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 5 avr. 2018
 * @version 2018-2.4.0
 */
namespace SbmParent\Model\Db\Service\Query;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Expression;
use SbmBase\Model\Session;
use SbmCommun\Model\Db\Service\DbManager;

class Eleves implements FactoryInterface
{

    /**
     * Millesime de travail
     *
     * @var int
     */
    private $millesime;

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
     * @var \Zend\Db\Sql\Sql
     */
    protected $sql;

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        if (! ($serviceLocator instanceof DbManager)) {
            $message = 'DbManager attendu. On a reçu %s.';
            throw new Exception(sprintf($message), gettype($serviceLocator));
        }
        $this->millesime = Session::get('millesime');
        $this->db_manager = $serviceLocator;
        $this->dbAdapter = $this->db_manager->getDbAdapter();
        $this->sql = new Sql($this->dbAdapter);
        return $this;
    }

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

    public function getEleve($eleveId)
    {
        $where = new Where();
        $where->lessThan('millesime', $this->millesime)->equalTo('ele.eleveId', $eleveId);
        $select = $this->sql->select();
        $select->from(
            [
                'ele' => $this->db_manager->getCanonicName('eleves', 'table')
            ])
            ->columns(
            [
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
            ])
            ->join(
            [
                'sco' => $this->db_manager->getCanonicName('scolarites', 'table')
            ], 'ele.eleveId = sco.eleveId', 
            [
                'millesime' => 'millesime',
                'selectionScolarite' => 'selection',
                'dateInscription' => 'dateInscription',
                'dateModificationScolarite' => 'dateModification',
                'etablissementId' => 'etablissementId',
                'classeId' => 'classeId',
                'chez' => 'chez',
                'adresseEleveL1' => 'adresseL1',
                'adresseEleveL2' => 'adresseL2',
                'codePostalEleve' => 'codePostal',
                'communeEleveId' => 'communeId',
                'x' => 'x',
                'y' => 'y',
                'distanceR1' => 'distanceR1',
                'distanceR2' => 'distanceR2',
                'dateEtiquette' => 'dateEtiquette',
                'dateCarte' => 'dateCarte',
                'inscrit' => 'inscrit',
                'gratuit' => 'gratuit',
                'paiement' => 'paiement',
                'duplicata' => 'duplicata',
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
                'organismeId' => 'organismeId',
                'regimeId' => 'regimeId',
                'motifDerogation' => 'motifDerogation',
                'motifRefusR1' => 'motifRefusR1',
                'motifRefusR2' => 'motifRefusR2',
                'commentaire' => 'commentaire'
            ])
            ->join(
            [
                'eta' => $this->db_manager->getCanonicName('etablissements', 'table')
            ], 'sco.etablissementId = eta.etablissementId', 
            [
                'etablissement' => 'nom',
                'xeta' => 'x',
                'yeta' => 'y'
            ])
            ->join(
            [
                'com' => $this->db_manager->getCanonicName('communes', 'table')
            ], 'eta.communeId = com.communeId', 
            [
                'communeEtablissement' => 'nom'
            ])
            ->where($where);
        $statement = $this->sql->prepareStatementForSqlObject($select);
        return $statement->execute()->current();
    }

    /**
     * Liste des élèves d'un responsable donné que l'on peut réinscrire.
     * Les élèves déjà inscrits ou préinscrits ne sont plus renvoyés.
     *
     * @param int $responsableId            
     *
     * @return \Zend\Db\Adapter\Driver\ResultInterface
     */
    public function aReinscrire($responsableId)
    {
        // élèves scolarisés cette année
        $where1 = new Where();
        $where1->equalTo('millesime', $this->millesime);
        $select1 = $this->sql->select();
        $select1->from($this->db_manager->getCanonicName('scolarites', 'table'))
            ->columns([
            'eleveId'
        ])
            ->where($where1);
        // ------------------------
        // élèves de ce responsable non scolarisés cette année
        $where = new Where();
        $where->isNull('s.eleveId')
            ->nest()
            ->equalTo('responsable1Id', $responsableId)->or->equalTo('responsable2Id', 
            $responsableId)->unnest();
        $select = $this->sql->select();
        $select->from(
            [
                'e' => $this->db_manager->getCanonicName('eleves', 'table')
            ])
            ->columns(
            [
                'eleveId',
                'nom',
                'prenom'
            ])
            ->join([
            's' => $select1
        ], 'e.eleveId = s.eleveId', [], Select::JOIN_LEFT)
            ->where($where);
        $statement = $this->sql->prepareStatementForSqlObject($select);
        return $statement->execute();
    }

    /**
     * Indique si l'élève a le même établissement que l'année précédente
     *
     * @param int $eleveId            
     * @return <b>boolean</b> :
     *         0 s'il a changé d'établissement ; 1 s'il a le même
     */
    public function memeEtablissement($eleveId)
    {
        $where = new Where();
        $where->equalTo('s1.millesime', $this->millesime - 1)
            ->equalTo('s2.millesime', $this->millesime)
            ->equalTo('s1.eleveId', $eleveId)
            ->literal('s1.etablissementId = s2.etablissementId');
        $select = $this->sql->select();
        $select->from(
            [
                's1' => $this->db_manager->getCanonicName('scolarites', 'table')
            ])
            ->join(
            [
                's2' => $this->db_manager->getCanonicName('scolarites', 'table')
            ], 's1.eleveId = s2.eleveId', [])
            ->columns([
            'meme' => new Expression('count(*)')
        ])
            ->where($where);
        $statement = $this->sql->prepareStatementForSqlObject($select);
        $result = $statement->execute()->current();
        return $result['meme'];
    }
}