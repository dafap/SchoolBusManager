<?php
/**
 * Requêtes permettant d'obtenir les enfants inscrits ou préinscrits d'un responsable donné
 * (enregistré dans module.config.php sous l'alias 'Sbm\Db\Query\ElevesScolarites')
 *
 * @project sbm
 * @package SbmCommun/Model/Db/Service/Query/Eleve
 * @filesource ElevesScolarites.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 4 avr. 2018
 * @version 2018-2.4.0
 */
namespace SbmCommun\Model\Db\Service\Query\Eleve;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Predicate\Predicate;
use Zend\Db\Sql\Select;
use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\DbSelect;
use SbmBase\Model\Session;
use SbmCommun\Model\Db\Service\DbManager;
use SbmCommun\Model\Db\Exception;

class ElevesScolarites implements FactoryInterface
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
        $this->dbAdapter = $this->db_manager->getDbAdapter();
        $this->sql = new Sql($this->dbAdapter);
        $this->select = $this->sql->select()
            ->from(
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
            ]);
        return $this;
    }

    public function getEleve($eleveId)
    {
        $select = clone $this->select;
        $where = new Where();
        $where->equalTo('millesime', Session::get('millesime'))->equalTo('ele.eleveId', 
            $eleveId);
        $statement = $this->sql->prepareStatementForSqlObject($select->where($where));
        return $statement->execute()->current();
    }

    public function getEleveAdresse($eleveId, $trajet)
    {
        $select = clone $this->select;
        $select->join(
            [
                'r' => $this->db_manager->getCanonicName('responsables', 'table')
            ], 
            $trajet == 1 ? 'ele.responsable1Id = r.responsableId' : 'ele.responsable2Id=r.responsableId', 
            [
                'responsableId' => 'responsableId',
                'adresseL1' => 'adresseL1',
                'adresseL2' => 'adresseL2',
                'codePostal' => 'codePostal',
                'x' => 'x',
                'y' => 'y'
            ])
            ->join(
            [
                'comresp' => $this->db_manager->getCanonicName('communes', 'table')
            ], 'r.communeId = comresp.communeId', 
            [
                'commune' => 'nom'
            ]);
        $where = new Where();
        $where->equalTo('millesime', Session::get('millesime'))->equalTo('ele.eleveId', 
            $eleveId);
        $statement = $this->sql->prepareStatementForSqlObject($select->where($where));
        return $statement->execute();
    }

    public function getElevesInscrits($responsableId)
    {
        $select = clone $this->select;
        $where = new Where();
        $where->equalTo('millesime', Session::get('millesime'))
            ->literal('inscrit = 1')
            ->nest()
            ->literal('paiement = 1')->or->literal('fa = 1')->or->literal('gratuit > 0')
            ->unnest()
            ->nest()
            ->equalTo('responsable1Id', $responsableId)->or->equalTo('responsable2Id', 
            $responsableId)->unnest();
        $statement = $this->sql->prepareStatementForSqlObject($select->where($where));
        return $statement->execute();
    }

    public function getElevesPreinscrits($responsableId)
    {
        $select = clone $this->select;
        $where = new Where();
        $where->equalTo('millesime', Session::get('millesime'))
            ->literal('inscrit = 1')
            ->literal('paiement = 0')
            ->literal('fa = 0')
            ->literal('gratuit = 0')
            ->nest()
            ->equalTo('responsable1Id', $responsableId)->or->equalTo('responsable2Id', 
            $responsableId)->unnest();
        $statement = $this->sql->prepareStatementForSqlObject($select->where($where));
        return $statement->execute();
    }

    public function getMontantElevesInscrits($responsableId)
    {
        $select = $this->sql->select()
            ->from(
            [
                'ele' => $this->db_manager->getCanonicName('eleves', 'table')
            ])
            ->join(
            [
                'sco' => $this->db_manager->getCanonicName('scolarites', 'table')
            ], 'ele.eleveId = sco.eleveId', [])
            ->join(
            [
                'tar' => $this->db_manager->getCanonicName('tarifs', 'table')
            ], 'tar.tarifId = sco.tarifId', 
            [
                'montant',
                'nomTarif' => 'nom'
            ])
            ->columns(
            [
                'montantTotal' => new Expression('sum(tar.montant)')
            ]);
        $where = new Where();
        $where->equalTo('millesime', Session::get('millesime'))
            ->literal('inscrit = 1')
            ->nest()
            ->literal('paiement = 1')->or->literal('fa = 1')->or->literal('gratuit > 0')
            ->unnest()
            ->nest()
            ->equalTo('responsable1Id', $responsableId)->or->equalTo('responsable2Id', 
            $responsableId)->unnest();
        $statement = $this->sql->prepareStatementForSqlObject($select->where($where));
        return $statement->execute()->current()['montantTotal'];
    }

    public function getElevesPreinscritsWithMontant($responsableId)
    {
        $select = clone $this->select;
        $select->join(
            [
                'tar' => $this->db_manager->getCanonicName('tarifs', 'table')
            ], 'tar.tarifId = sco.tarifId', 
            [
                'montant',
                'nomTarif' => 'nom'
            ]);
        $where = new Where();
        $where->equalTo('millesime', Session::get('millesime'))
            ->literal('inscrit = 1')
            ->literal('paiement = 0')
            ->literal('fa = 0')
            ->literal('gratuit = 0')
            ->nest()
            ->equalTo('responsable1Id', $responsableId)->or->equalTo('responsable2Id', 
            $responsableId)->unnest();
        $statement = $this->sql->prepareStatementForSqlObject($select->where($where));
        return $statement->execute();
    }

    /**
     * On renvoie la liste des enfants de l'année inscrits ou préinscrits
     * à l'exception des `gratuits`, des `famille d'accueil` et des pris en charges par un organisme
     *
     * @param int $responsableId            
     * @return \Zend\Db\Adapter\Driver\ResultInterface
     */
    public function getElevesPayantsWithMontant($responsableId)
    {
        $select = clone $this->select;
        $select->join(
            [
                'tar' => $this->db_manager->getCanonicName('tarifs', 'table')
            ], 'tar.tarifId = sco.tarifId', 
            [
                'montant',
                'nomTarif' => 'nom'
            ]);
        $where = new Where();
        $where->equalTo('millesime', Session::get('millesime'))
            ->literal('inscrit = 1')
            ->literal('fa = 0')
            ->literal('gratuit = 0')
            ->nest()
            ->equalTo('responsable1Id', $responsableId)->or->equalTo('responsable2Id', 
            $responsableId)->unnest();
        $statement = $this->sql->prepareStatementForSqlObject($select->where($where));
        return $statement->execute();
    }

    public function getNbDuplicatas($responsableId)
    {
        $select = $this->sql->select()
            ->from(
            [
                'ele' => $this->db_manager->getCanonicName('eleves', 'table')
            ])
            ->join(
            [
                'sco' => $this->db_manager->getCanonicName('scolarites', 'table')
            ], 'ele.eleveId = sco.eleveId', [])
            ->columns(
            [
                'nbDuplicatas' => new Expression('sum(sco.duplicata)')
            ]);
        $where = new Where();
        $where->equalTo('millesime', Session::get('millesime'))
            ->nest()
            ->equalTo('responsable1Id', $responsableId)->or->equalTo('responsable2Id', 
            $responsableId)->unnest();
        $statement = $this->sql->prepareStatementForSqlObject($select->where($where));
        return $statement->execute()->current()['nbDuplicatas'];
    }

    public function getInscritsNonAffectes()
    {
        $statement = $this->sql->prepareStatementForSqlObject(
            $this->sqlInscritsNonAffectes());
        return $statement->execute();
    }

    public function paginatorInscritsNonAffectes()
    {
        return new Paginator(
            new DbSelect($this->sqlInscritsNonAffectes(), 
                $this->db_manager->getDbAdapter()));
    }

    private function sqlInscritsNonAffectes()
    {
        $select = clone $this->select;
        $select->quantifier($select::QUANTIFIER_DISTINCT)
            ->join(
            [
                'r' => $this->db_manager->getCanonicName('responsables', 'table')
            ], 
            'ele.responsable1Id = r.responsableId OR ele.responsable2Id=r.responsableId', 
            [
                'estR1' => new Expression(
                    'CASE WHEN r.responsableId=ele.responsable1Id THEN 1 ELSE 0 END'),
                'responsableId' => 'responsableId',
                'adresseL1' => 'adresseL1',
                'adresseL2' => 'adresseL2'
            ])
            ->join(
            [
                'comresp' => $this->db_manager->getCanonicName('communes', 'table')
            ], 'r.communeId = comresp.communeId', 
            [
                'commune' => 'nom'
            ])
            ->join(
            [
                'aff' => $this->db_manager->getCanonicName('affectations', 'table')
            ], 
            'aff.eleveId=ele.eleveId AND aff.responsableId=r.responsableId AND aff.millesime=sco.millesime', 
            [], Select::JOIN_LEFT);
        // inscrit : bon millesime dans scolarites et paiement enregistré ou fa
        $predicate1 = new Predicate();
        $predicate1->equalTo('sco.millesime', Session::get('millesime'))
            ->literal('inscrit = 1')
            ->nest()
            ->literal('paiement = 1')->or->literal('fa = 1')->or->literal('gratuit > 0')->unnest();
        // demande non traitée
        $predicate2 = new Predicate();
        $predicate2->nest()->literal('ele.responsable1Id = r.responsableId')->and->literal(
            'sco.demandeR1 = 1')->unnest()->OR->nest()->literal(
            'ele.responsable2Id=r.responsableId')->and->literal('sco.demandeR2 = 1')->unnest();
        // accord mais pas d'affectation
        $predicate3 = new Predicate();
        $predicate3->isNull('aff.eleveId')
            ->nest()
            ->nest()
            ->literal('ele.responsable1Id = r.responsableId')->and->literal(
            'sco.demandeR1 = 2')
            ->literal('sco.accordR1 = 1')
            ->unnest()->or->nest()->literal('ele.responsable2Id = r.responsableId')->and->literal(
            'sco.demandeR2 = 2')
            ->literal('sco.accordR2 = 1')
            ->unnest()
            ->unnest();
        // composition du where
        $where = new Where();
        $where->predicate($predicate1)
            ->nest()
            ->predicate($predicate2)->or->predicate($predicate3)->unnest();
        return $select->where($where)->order(
            [
                'nom',
                'prenom'
            ]);
    }

    public function getPreinscritsNonAffectes()
    {
        $statement = $this->sql->prepareStatementForSqlObject(
            $this->sqlPreinscritsNonAffectes());
        return $statement->execute();
    }

    public function paginatorPreinscritsNonAffectes()
    {
        return new Paginator(
            new DbSelect($this->sqlPreinscritsNonAffectes(), 
                $this->db_manager->getDbAdapter()));
    }

    private function sqlPreinscritsNonAffectes()
    {
        $select = clone $this->select;
        $select->quantifier($select::QUANTIFIER_DISTINCT)
            ->join(
            [
                'r' => $this->db_manager->getCanonicName('responsables', 'table')
            ], 
            'ele.responsable1Id = r.responsableId OR ele.responsable2Id=r.responsableId', 
            [
                'estR1' => new Expression(
                    'CASE WHEN r.responsableId=ele.responsable1Id THEN 1 ELSE 0 END'),
                'responsableId' => 'responsableId',
                'adresseL1' => 'adresseL1',
                'adresseL2' => 'adresseL2'
            ])
            ->join(
            [
                'comresp' => $this->db_manager->getCanonicName('communes', 'table')
            ], 'r.communeId = comresp.communeId', 
            [
                'commune' => 'nom'
            ])
            ->join(
            [
                'aff' => $this->db_manager->getCanonicName('affectations', 'table')
            ], 
            'aff.eleveId=ele.eleveId AND aff.responsableId=r.responsableId AND aff.millesime=sco.millesime', 
            [], Select::JOIN_LEFT);
        // inscrit : bon millesime dans scolarites et paiement enregistré ou fa
        $predicate1 = new Predicate();
        $predicate1->equalTo('sco.millesime', Session::get('millesime'))
            ->literal('inscrit = 1')
            ->literal('paiement = 0')
            ->literal('fa = 0')
            ->literal('gratuit = 0');
        // demande non traitée
        $predicate2 = new Predicate();
        $predicate2->nest()
            ->literal('ele.responsable1Id = r.responsableId')
            ->literal('sco.demandeR1 = 1')
            ->unnest()->OR->nest()
            ->literal('ele.responsable2Id=r.responsableId')
            ->literal('sco.demandeR2 = 1')
            ->unnest();
        // accord mais pas d'affectation
        $predicate3 = new Predicate();
        $predicate3->isNull('aff.eleveId')
            ->nest()
            ->nest()
            ->literal('ele.responsable1Id = r.responsableId')->and->literal(
            'sco.demandeR1 = 2')
            ->literal('sco.accordR1 = 1')
            ->unnest()->or->nest()->literal('ele.responsable1Id = r.responsableId')->and->literal(
            'sco.demandeR1 = 2')
            ->literal('sco.accordR2 = 1')
            ->unnest()
            ->unnest();
        // composition du where
        $where = new Where();
        $where->predicate($predicate1)
            ->nest()
            ->predicate($predicate2)->or->predicate($predicate3)->unnest();
        return $select->where($where)->order(
            [
                'nom',
                'prenom'
            ]);
    }

    public function getDemandeGaDistanceR2Zero()
    {
        $select = clone $this->select;
        $select->columns(
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
                'responsable2Id' => 'responsable2Id',
                'responsableFId' => 'responsableFId',
                'selectionEleve' => 'selection',
                'noteEleve' => 'note'
            ])
            ->join(
            [
                'r1' => $this->db_manager->getCanonicName('responsables', 'table')
            ], 'ele.responsable1Id = r1.responsableId', 
            [
                'responsable1' => new Expression('CONCAT(r1.nom, " ", r1.prenom)'),
                'adresseR1L1' => 'adresseL1',
                'adresseR1L2' => 'adresseL2',
                'x1' => 'x',
                'y1' => 'y'
            ])
            ->join(
            [
                'comr1' => $this->db_manager->getCanonicName('communes', 'table')
            ], 'r1.communeId = comr1.communeId', 
            [
                'communeR1' => 'nom'
            ])
            ->join(
            [
                'r2' => $this->db_manager->getCanonicName('responsables', 'table')
            ], 'ele.responsable2Id = r2.responsableId', 
            [
                'adresseR2L1' => 'adresseL1',
                'adresseR2L2' => 'adresseL2',
                'x2' => 'x',
                'y2' => 'y'
            ], Select::JOIN_LEFT)
            ->join(
            [
                'comr2' => $this->db_manager->getCanonicName('communes', 'table')
            ], 'r2.communeId = comr2.communeId', 
            [
                'communeR2' => 'nom'
            ], Select::JOIN_LEFT);
        $where = new Where();
        $where->equalTo('millesime', Session::get('millesime'))
            ->isNotNull('responsable2Id')
            ->literal('demandeR2 = 1')
            ->literal('distanceR2 = 0');
        $statement = $this->sql->prepareStatementForSqlObject($select->where($where));
        return $statement->execute();
    }

    public function getEnfants($responsableId, $ga = 1)
    {
        $select = clone $this->select;
        $where = new Where();
        $where->equalTo('millesime', Session::get('millesime'))->equalTo(
            sprintf('responsable%dId', $ga), $responsableId);
        $statement = $this->sql->prepareStatementForSqlObject($select->where($where));
        return $statement->execute();
    }

    public function getScolaritePrecedente($eleveId)
    {
        $millesime = Session::get('millesime');
        $millesime --;
        $where = new Where();
        $where->equalTo('millesime', $millesime)->equalTo('eleveId', $eleveId);
        $select = $this->sql->select(
            [
                'sco' => $this->db_manager->getCanonicName('scolarites', 'table')
            ])
            ->columns([
            'eleveid'
        ])
            ->join(
            [
                'eta' => $this->db_manager->getCanonicName('etablissements', 'table')
            ], 'eta.etablissementId = sco.etablissementId', 
            [
                'etablissement' => 'nom'
            ], Select::JOIN_LEFT)
            ->join(
            [
                'cla' => $this->db_manager->getCanonicName('classes', 'table')
            ], 'cla.classeId = sco.classeId', 
            [
                'classe' => 'nom'
            ], Select::JOIN_LEFT)
            ->where($where);
        $statement = $this->sql->prepareStatementForSqlObject($select);
        $result = $statement->execute()->current();
        if (! $result) {
            $result = [
                'eleveId' => $eleveId,
                'etablissement' => 'non inscrit l\'année précédente',
                'classe' => ''
            ];
        }
        return $result;
    }

    /**
     * Pour le portail de l'organisateur (secretariat)
     *
     * @param string|array|Zend\Db\Sql\Where $where            
     * @param string $order            
     * @param int $millesime
     *            (inutilisé mais gardé pour la compatibilité des appels)
     * @return \Zend\Paginator\Paginator
     */
    public function paginatorScolaritesR($where, $order = null, $millesime = null)
    {
        $select = $this->selectScolaritesR($where, $order);
        // die($this->getSqlString($select));
        return new Paginator(new DbSelect($select, $this->db_manager->getDbAdapter()));
    }

    public function getScolaritesR($where, $order = null, $millesime = null)
    {
        $statement = $this->sql->prepareStatementForSqlObject(
            $this->selectScolaritesR($where, $order));
        return $statement->execute();
    }

    private function selectScolaritesR($where, $order = null, $millesime = null)
    {
        $select = $this->sql->select(
            [
                'sco' => $this->db_manager->getCanonicName('scolarites', 'table')
            ])
            ->columns(
            [
                'millesime',
                'eleveid',
                'inscrit',
                'fa',
                'paiement',
                'gratuit'
            ])
            ->join(
            [
                'eta' => $this->db_manager->getCanonicName('etablissements', 'table')
            ], 'eta.etablissementId = sco.etablissementId', 
            [
                'etablissement' => new Expression(
                    '(CASE WHEN isnull(eta.alias) THEN eta.nom ELSE eta.alias END)')
            ])
            ->join(
            [
                'etacom' => $this->db_manager->getCanonicName('communes', 'table')
            ], 'eta.communeId = etacom.communeId', 
            [
                'communeEtablissement' => 'nom'
            ])
            ->join(
            [
                'cla' => $this->db_manager->getCanonicName('classes', 'table')
            ], 'cla.classeId = sco.classeId', 
            [
                'classe' => 'nom'
            ])
            ->join(
            [
                'ele' => $this->db_manager->getCanonicName('eleves', 'table')
            ], 'ele.eleveId = sco.eleveId', 
            [
                'nom' => 'nom',
                'nomSA' => 'nomSA',
                'prenom' => 'prenom',
                'prenomSA' => 'prenomSA',
                'dateN' => 'dateN',
                'numero' => 'numero'
            ])
            ->join(
            [
                'res1' => $this->db_manager->getCanonicName('responsables', 'table')
            ], 
            new Expression('ele.responsable1Id = res1.responsableId AND sco.demandeR1 > 0'), 
            [
                'responsable1' => new Expression(
                    '(CASE WHEN isnull(res1.responsableId) THEN NULL ELSE concat(res1.nomSA," ",res1.prenomSA) END)')
            ], Select::JOIN_LEFT)
            ->join(
            [
                'affr1' => $this->db_manager->getCanonicName('affectations', 'table')
            ], 
            'sco.millesime=affr1.millesime AND sco.eleveId=affr1.eleveId AND res1.responsableId=affr1.responsableId', 
            [], Select::JOIN_LEFT)
            ->join(
            [
                'sta1r1' => $this->db_manager->getCanonicName('stations', 'table')
            ], 'affr1.station1Id = sta1r1.stationId', 
            [
                'station1r1' => 'nom',
                'station1IdR1' => 'stationId'
            ], Select::JOIN_LEFT)
            ->join(
            [
                'sta2r1' => $this->db_manager->getCanonicName('stations', 'table')
            ], 'affr1.station2Id = sta2r1.stationId', 
            [
                'station2r1' => 'nom',
                'station2IdR1' => 'stationId'
            ], Select::JOIN_LEFT)
            ->join(
            [
                'ser1r1' => $this->db_manager->getCanonicName('services', 'table')
            ], 'affr1.service1Id = ser1r1.serviceId', 
            [
                'service1r1' => 'nom',
                'service1IdR1' => 'serviceId'
            ], Select::JOIN_LEFT)
            ->join(
            [
                'tra1r1' => $this->db_manager->getCanonicName('transporteurs', 'table')
            ], 'ser1r1.transporteurId = tra1r1.transporteurId', 
            [
                'transporteur1r1' => 'nom'
            ], Select::JOIN_LEFT)
            ->join(
            [
                'ser2r1' => $this->db_manager->getCanonicName('services', 'table')
            ], 'affr1.service2Id = ser2r1.serviceId', 
            [
                'service2r1' => 'nom',
                'service2IdR1' => 'serviceId'
            ], Select::JOIN_LEFT)
            ->join(
            [
                'tra2r1' => $this->db_manager->getCanonicName('transporteurs', 'table')
            ], 'ser2r1.transporteurId = tra2r1.transporteurId', 
            [
                'transporteur2r1' => 'nom'
            ], Select::JOIN_LEFT)
            ->join(
            [
                'res2' => $this->db_manager->getCanonicName('responsables', 'table')
            ], 
            new Expression('ele.responsable2Id = res2.responsableId AND sco.demandeR2 > 0'), 
            [
                'responsable2' => new Expression(
                    '(CASE WHEN isnull(res2.responsableId) THEN NULL ELSE concat(res2.nomSA," ",res2.prenomSA) END)')
            ], Select::JOIN_LEFT)
            ->join(
            [
                'affr2' => $this->db_manager->getCanonicName('affectations', 'table')
            ], 
            'sco.millesime = affr2.millesime AND sco.eleveId = affr2.eleveId AND res2.responsableId = affr2.responsableId', 
            [], Select::JOIN_LEFT)
            ->join(
            [
                'sta1r2' => $this->db_manager->getCanonicName('stations', 'table')
            ], 'affr2.station1Id = sta1r2.stationId', 
            [
                'station1r2' => 'nom',
                'station1IdR2' => 'stationId'
            ], Select::JOIN_LEFT)
            ->join(
            [
                'sta2r2' => $this->db_manager->getCanonicName('stations', 'table')
            ], 'affr2.station2Id = sta2r2.stationId', 
            [
                'station2r2' => 'nom',
                'station2IdR2' => 'stationId'
            ], Select::JOIN_LEFT)
            ->join(
            [
                'ser1r2' => $this->db_manager->getCanonicName('services', 'table')
            ], 'affr2.service1Id = ser1r2.serviceId', 
            [
                'service1r2' => 'nom',
                'service1IdR2' => 'serviceId'
            ], Select::JOIN_LEFT)
            ->join(
            [
                'tra1r2' => $this->db_manager->getCanonicName('transporteurs', 'table')
            ], 'ser1r2.transporteurId = tra1r2.transporteurId', 
            [
                'transporteur1r2' => 'nom'
            ], Select::JOIN_LEFT)
            ->join(
            [
                'ser2r2' => $this->db_manager->getCanonicName('services', 'table')
            ], 'affr2.service2Id = ser2r2.serviceId', 
            [
                'service2r2' => 'nom',
                'service2IdR2' => 'serviceId'
            ], Select::JOIN_LEFT)
            ->join(
            [
                'tra2r2' => $this->db_manager->getCanonicName('transporteurs', 'table')
            ], 'ser2r2.transporteurId = tra2r2.transporteurId', 
            [
                'transporteur2r2' => 'nom'
            ], Select::JOIN_LEFT);
        if (! empty($order)) {
            $select->order($order);
        }
        return $select->where($where);
    }
}