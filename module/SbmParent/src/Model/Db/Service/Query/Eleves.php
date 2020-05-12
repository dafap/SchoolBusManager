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
 * @date 12 mai 2020
 * @version 2020-2.6.0
 */
namespace SbmParent\Model\Db\Service\Query;

use SbmBase\Model\Session;
use SbmCommun\Model\Db\Service\DbManager;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Where;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

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
            throw new \SbmCommun\Model\Db\Exception\ExceptionNoDbManager(
                sprintf($message), gettype($serviceLocator));
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
     * @return string
     */
    public function getSqlString($select)
    {
        return $select->getSqlString($this->dbAdapter->getPlatform());
    }

    /**
     * Donne les informations (tables eleves, scolarites, etablissements, communes) d'une
     * scolarité précédant le millésime courant. S'il y en a plusieurs, donne la plus
     * récente.
     *
     * @param int $eleveId
     *
     * @return array
     */
    public function getEleve($eleveId)
    {
        $where = new Where();
        $where->lessThan('millesime', $this->millesime)->equalTo('ele.eleveId', $eleveId);
        $select = $this->sql->select();
        $select->from([
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
                'sexe' => 'sexe',
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
            ->join([
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
                'dateEtiquetteR1' => 'dateEtiquetteR1',
                'dateEtiquetteR2' => 'dateEtiquetteR2',
                'dateCarteR1' => 'dateCarteR1',
                'dateCarteR2' => 'dateCarteR2',
                'inscrit' => 'inscrit',
                'gratuit' => 'gratuit',
                'paiementR1' => 'paiementR1',
                'paiementR2' => 'paiementR2',
                'duplicataR1' => 'duplicataR1',
                'duplicataR2' => 'duplicataR2',
                'fa' => 'fa',
                'anneeComplete' => 'anneeComplete',
                'subventionR1' => 'subventionR1',
                'subventionR2' => 'subventionR2',
                'demandeR1' => 'demandeR1',
                'demandeR2' => 'demandeR2',
                'dateDemandeR2' => 'dateDemandeR2',
                'accordR1' => 'accordR1',
                'accordR2' => 'accordR2',
                'internet' => 'internet',
                'district' => 'district',
                'derogation' => 'derogation',
                'dateDebut' => 'dateDebut',
                'dateFin' => 'dateFin',
                'joursTransportR1' => 'joursTransportR1',
                'joursTransportR2' => 'joursTransportR2',
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
            ->join([
            'com' => $this->db_manager->getCanonicName('communes', 'table')
        ], 'eta.communeId = com.communeId',
            [
                'communeEtablissement' => 'nom',
                'lacommuneEtablissement' => 'aliasCG',
                'laposteEtablissement' => 'alias_laposte'
            ])
            ->where($where);
        $statement = $this->sql->prepareStatementForSqlObject($select);
        try {
            return $statement->execute()->current();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Liste des élèves d'un responsable donné que l'on peut réinscrire. Les élèves déjà
     * inscrits ou préinscrits ne sont plus renvoyés.
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
        $select->from([
            'e' => $this->db_manager->getCanonicName('eleves', 'table')
        ])
            ->columns([
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

    public function aReincrireOrganisme($responsableId)
    {
        // liste des responsableId de l'organisme
        $select2 = $this->sql->select()
            ->columns([])
            ->from(
            [
                'uo1' => $this->db_manager->getCanonicName('users-organismes', 'table')
            ])
            ->join([
            'u1' => $this->db_manager->getCanonicName('users', 'table')
        ], 'u1.userId = uo1.userId', [])
            ->join(
            [
                'r1' => $this->db_manager->getCanonicName('responsables', 'table')
            ], 'r1.email = u1.email', [
                'responsableId'
            ])
            ->join(
            [
                'uo2' => $this->db_manager->getCanonicName('users-organismes', 'table')
            ], 'uo1.organismeId = uo2.organismeId', [])
            ->join([
            'u2' => $this->db_manager->getCanonicName('users', 'table')
        ], 'u2.userId = uo2.userId', [])
            ->join(
            [
                'r2' => $this->db_manager->getCanonicName('responsables', 'table')
            ], 'r2.email = u2.email', [])
            ->where((new Where())->equalTo('r2.responsableId', $responsableId));
        // tous les élèves scolarisés cette année
        $where1 = new Where();
        $where1->equalTo('millesime', $this->millesime);
        $select1 = $this->sql->select();
        $select1->from($this->db_manager->getCanonicName('scolarites', 'table'))
            ->columns([
            'eleveId'
        ])
            ->where($where1);
        // ------------------------
        // élèves de ces responsables non scolarisés cette année
        $where = new Where();
        $where->isNull('s.eleveId')->in('responsable1Id', $select2);
        $select = $this->sql->select();
        $select->from([
            'e' => $this->db_manager->getCanonicName('eleves', 'table')
        ])
            ->columns([
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
     * Indique si l'élève a le même établissement et le même régime que l'année précédente
     *
     * @param int $eleveId
     * @return <b>boolean</b> : 0 s'il a changé d'établissement ; 1 s'il a le même
     */
    public function memeScolarite($eleveId)
    {
        $where = new Where();
        $where->equalTo('s1.millesime', $this->millesime - 1)
            ->equalTo('s2.millesime', $this->millesime)
            ->equalTo('s1.eleveId', $eleveId)
            ->literal('s1.etablissementId = s2.etablissementId')
            ->literal('s1.regimeId = s2.regimeId');
        $select = $this->sql->select();
        $select->from(
            [
                's1' => $this->db_manager->getCanonicName('scolarites', 'table')
            ])
            ->join([
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