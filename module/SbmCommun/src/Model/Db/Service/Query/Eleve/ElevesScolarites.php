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
 * @date 28 juin 2020
 * @version 2020-2.6.0
 */
namespace SbmCommun\Model\Db\Service\Query\Eleve;

use SbmCommun\Model\Db\Service\Query\AbstractQuery;
use SbmCommun\Model\Db\Sql\Predicate as PredicateEleve;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Predicate\Predicate;

class ElevesScolarites extends AbstractQuery
{

    protected function init()
    {
        $this->select = $this->sql->select()
            ->from([
            'ele' => $this->db_manager->getCanonicName('eleves', 'table')
        ])
            ->columns(
            [
                'eleveId' => 'eleveId',
                'mailchimp' => 'mailchimp',
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
                'stationIdR1' => 'stationIdR1',
                'stationIdR2' => 'stationIdR2',
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
                'grilleTarifR1' => 'grilleTarifR1',
                'grilleCodeR1' => 'grilleTarifR1',
                'reductionR1' => 'reductionR1',
                'grilleCodeR2' => 'grilleTarifR2',
                'reductionR2' => 'reductionR2',
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
            ->join(
            [
                'photos' => $this->db_manager->getCanonicName('elevesphotos', 'table')
            ], 'photos.eleveId = ele.eleveId',
            [
                'hasphoto' => new Expression(
                    'CASE WHEN isnull(photos.eleveId) THEN FALSE ELSE TRUE END')
            ], Select::JOIN_LEFT);
        $this->addStrategy('grilleTarifR1',
            $this->db_manager->get('Sbm\Db\Table\Tarifs')
                ->getStrategie('grille'));
    }

    private function lienElevesResponsables(int $responsableId, int $categorieId = 1)
    {
        $jointure = new Predicate(null, Predicate::COMBINED_BY_OR);
        if ($categorieId == 1) {
            return $jointure->equalTo('responsable1Id', $responsableId)->equalTo(
                'responsable2Id', $responsableId);
        } else {
            // liste des responsableId de l'organisme
            $select = $this->sql->select()
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
            return $jointure->in('responsable1Id', $select)->in('responsable2Id', $select);
        }
    }

    public function getEleve($eleveId)
    {
        return $this->renderResult($this->selectEleve($eleveId))
            ->current();
    }

    protected function selectEleve($eleveId)
    {
        $select = clone $this->select;
        $where = new Where();
        $where->equalTo('millesime', $this->millesime)->equalTo('ele.eleveId', $eleveId);
        return $select->where($where);
    }

    public function getEleveAdresse($eleveId, $trajet)
    {
        return $this->renderResult($this->selectEleveAdresse($eleveId, $trajet));
    }

    protected function selectEleveAdresse($eleveId, $trajet)
    {
        $where = new Where();
        $where->equalTo('millesime', $this->millesime)->equalTo('ele.eleveId', $eleveId);
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
                'adresseL3' => 'adresseL3',
                'codePostal' => 'codePostal',
                'x' => 'x',
                'y' => 'y'
            ])
            ->join(
            [
                'comresp' => $this->db_manager->getCanonicName('communes', 'table')
            ], 'r.communeId = comresp.communeId',
            [
                'commune' => 'nom',
                'lacommune' => 'alias',
                'laposte' => 'alias_laposte'
            ])
            ->where($where);
        return $select;
    }

    public function getElevesInscrits(int $responsableId, int $categorieId = 1)
    {
        return $this->renderResult(
            $this->selectElevesInscrits($responsableId, $categorieId));
    }

    protected function selectElevesInscrits(int $responsableId, int $categorieId = 1)
    {
        $select = clone $this->select;
        $elevesSansPreinscrits = new PredicateEleve\ElevesSansPreinscrits(
            $this->millesime, 'sco',
            [
                $this->lienElevesResponsables($responsableId, $categorieId)
            ]);
        $where = $elevesSansPreinscrits();
        return $select->where($where);
    }

    public function getElevesPreinscritsOuEnAttente(int $responsableId,
        int $categorieId = 1)
    {
        return $this->renderResult(
            $this->selectElevesPreinscritsOuEnAttente($responsableId, $categorieId));
    }

    protected function selectElevesPreinscritsOuEnAttente(int $responsableId,
        int $categorieId = 1)
    {
        $select = clone $this->select;
        $jointure = $this->lienElevesResponsables($responsableId, $categorieId);
        $elevesPreinscrits = new PredicateEleve\ElevesPreinscrits($this->millesime, 'sco',
            [
                $jointure
            ]);
        $elevesEnAttente = new PredicateEleve\ElevesEnAttente($this->millesime, 'sco',
            [
                $jointure
            ]);
        $where = new Where([
            $elevesPreinscrits(),
            $elevesEnAttente()
        ], Where::COMBINED_BY_OR);
        return $select->where($where);
    }

    public function getElevesPreinscrits($responsableId)
    {
        return $this->renderResult($this->selectElevesPreinscrits($responsableId));
    }

    protected function selectElevesPreinscrits($responsableId)
    {
        $select = clone $this->select;
        $elevesPreinscrits = new PredicateEleve\ElevesPreinscrits($this->millesime, 'sco',
            [
                $this->lienElevesResponsables($responsableId)
            ]);
        return $select->where($elevesPreinscrits());
    }

    /**
     * Renvoie le montant des abonnements dus par un responsable pour les élèves inscrits.
     * Les droits d'inscription ont déjà été payés sauf pour les élèves gratuit , en
     * famille d'accueil ou pris en charge par un organisme. Les duplicatas ne sont pas
     * pris en compte.
     *
     * @param int $responsableId
     *
     * @return float (currency)
     */
    public function getMontantElevesInscrits($responsableId)
    {
        $resultset = $this->renderResult(
            $this->selectMontantElevesInscrits($responsableId));
        $tTarif = $this->db_manager->getCanonicName('tarifs', 'table');
        $montant = 0.0;
        foreach ($resultset as $row) {
            $montant += $tTarif->getMontant($row['grilleCode'], $row['quantite'],
                $this->millesime);
        }
        return $montant;
    }

    protected function selectMontantElevesInscrits($responsableId)
    {
        $select = $this->sql->select()
            ->from([
            'sco' => $this->db_manager->getCanonicName('scolarites', 'table')
        ])
            ->join([
            'ele' => $this->db_manager->getCanonicName('eleves', 'table')
        ], 'ele.eleveId = sco.eleveId', [])
            ->columns([
            'grilleCode',
            'quantite' => new Expression('count(*)')
        ])
            ->group('grilleCode');
        $elevesInscrits = new PredicateEleve\ElevesSansPreinscrits($this->millesime, 'sco',
            [
                $this->lienElevesResponsables($responsableId)
            ]);
        return $select->where($elevesInscrits());
    }

    /**
     * VERSION SAVOIE : ALBERTVILLE On prend [les préinscrits R1] UNION [les préinscrits
     * R2] et on renvoie le résultat de la requête avec, pour colonnes, eleveId, nom,
     * prenom, grilleTarif, reduction, duplicata (où grilleTarif est en clair)
     *
     * @param int $responsableId
     * @return \Zend\Db\ResultSet\HydratingResultSet|\Zend\Db\Adapter\Driver\ResultInterface
     */
    public function getElevesPreinscritsWithGrille(int $responsableId)
    {
        $this->addStrategy('grilleTarif',
            $this->db_manager->get('Sbm\Db\Table\Tarifs')
                ->getStrategie('grille'));
        return $this->renderResult(
            $this->selectElevesPreinscritsWithGrille($responsableId));
    }

    /**
     * VERSION SAVOIE : ALBERTVILLE
     *
     * @param int $rx
     * @param int $responsableId
     * @return \Zend\Db\Sql\Select
     */
    protected function selectElevesPreinscritsWithGrille(int $responsableId)
    {
        return $this->subSelectElevesPreinscritsWithGrille(1, $responsableId)->combine(
            $this->subSelectElevesPreinscritsWithGrille(2, $responsableId));
    }

    private function subSelectElevesPreinscritsWithGrille(int $rx, int $responsableId)
    {
        $where = new Where();
        $where->equalTo('responsable' . $rx . 'Id', $responsableId);
        $predicate = new PredicateEleve\ElevesPreinscrits($this->millesime, 'sco',
            [
                $where
            ]);
        return $this->sql->select()
            ->columns([
            'eleveId',
            'nom',
            'prenom'
        ])
            ->from([
            'ele' => $this->db_manager->getCanonicName('eleves', 'table')
        ])
            ->join([
            'sco' => $this->db_manager->getCanonicName('scolarites', 'table')
        ], 'ele.eleveId = sco.eleveId',
            [
                'grilleTarif' => 'grilleTarifR' . $rx,
                'reduction' => 'reductionR' . $rx,
                'duplicata' => 'duplicataR' . $rx
            ])
            ->where($predicate($rx));
    }

    public function getElevesPreinscritsWithMontant($responsableId)
    {
        throw new \Exception(
            'TODO : remplacer cette méthode par `getElevesPreinscritsWithGrille()');
    }

    /**
     * On renvoie la liste des enfants de l'année inscrits ou préinscrits à l'exception
     * des `en attente`, des `gratuits`, des `famille d'accueil` et des pris en charges
     * par un organisme
     *
     * @param int $responsableId
     *
     * @return \Zend\Db\Adapter\Driver\ResultInterface
     */
    public function getElevesPayantsWithGrille($responsableId)
    {
        return $this->renderResult($this->selectElevesPayantsWithGrille($responsableId));
    }

    protected function selectElevesPayantsWithGrille($responsableId)
    {
        $select = clone $this->select;
        $elevesResponsablePayant = new PredicateEleve\ElevesResponsablePayant(
            $this->millesime, 'sco', [
                $this->lienElevesResponsables($responsableId)
            ]);
        return $select->where($elevesResponsablePayant());
    }

    public function getElevesPayantsWithMontant($responsableId)
    {
        throw new \Exception(
            'TODO : remplacer cette méthode par `getElevesPayantsWithGrille()');
    }

    public function getNbDuplicatas($responsableId)
    {
        return $this->renderResult($this->selectNbDuplicatas($responsableId))
            ->current()['nbDuplicatas'];
    }

    protected function selectNbDuplicatas($responsableId)
    {
        $select = $this->sql->select()
            ->from([
            'ele' => $this->db_manager->getCanonicName('eleves', 'table')
        ])
            ->join([
            'sco' => $this->db_manager->getCanonicName('scolarites', 'table')
        ], 'ele.eleveId = sco.eleveId', [])
            ->columns([
            'nbDuplicatas' => new Expression('sum(sco.duplicataR1)')
        ]);
        $where = new Where();
        $where->equalTo('millesime', $this->millesime)
            ->nest()
            ->equalTo('responsable1Id', $responsableId)->or->equalTo('responsable2Id',
            $responsableId)->unnest();
        return $select->where($where);
    }

    public function getInscritsNonAffectes()
    {
        return $this->renderResult($this->selectInscritsNonAffectes());
    }

    public function paginatorInscritsNonAffectes()
    {
        return $this->paginator($this->selectInscritsNonAffectes());
    }

    protected function selectInscritsNonAffectes()
    {

        // composition du where à partir des élèves inscrits
        $nonAffectes = new Predicate(
            [
                new PredicateEleve\PredicateDemandeNonTraitee(),
                new PredicateEleve\PredicateAccordSansAffectation()
            ], Predicate::COMBINED_BY_OR);
        $elevesInscritsNonAffectes = new PredicateEleve\ElevesSansPreinscrits(
            $this->millesime, 'sco', [
                $nonAffectes
            ], Predicate::COMBINED_BY_AND);
        // Requête
        $select = clone $this->select;
        $select->quantifier($select::QUANTIFIER_DISTINCT)
            ->join([
            'cla' => $this->db_manager->getCanonicName('classes', 'table')
        ], 'sco.classeId = cla.classeId', [])
            ->join([
            'r' => $this->db_manager->getCanonicName('responsables', 'table')
        ], 'ele.responsable1Id = r.responsableId OR ele.responsable2Id=r.responsableId',
            [
                'estR1' => new Expression(
                    'CASE WHEN r.responsableId=ele.responsable1Id THEN 1 ELSE 0 END'),
                'responsableId' => 'responsableId',
                'adresseL1' => 'adresseL1',
                'adresseL2' => 'adresseL2',
                'adresseL3' => 'adresseL3'
            ])
            ->join(
            [
                'comresp' => $this->db_manager->getCanonicName('communes', 'table')
            ], 'r.communeId = comresp.communeId',
            [
                'commune' => 'nom',
                'lacommune' => 'alias',
                'laposte' => 'alias_laposte'
            ])
            ->join(
            [
                $this->aliasEffectifAffectations(1, 1) => $this->subselectAffectations(1,
                    1)
            ], $this->jointureEffectifAffectations(1, 1), [], Select::JOIN_LEFT)
            ->join(
            [
                $this->aliasEffectifAffectations(2, 1) => $this->subselectAffectations(2,
                    1)
            ], $this->jointureEffectifAffectations(2, 1), [], Select::JOIN_LEFT)
            ->join(
            [
                $this->aliasEffectifAffectations(3, 1) => $this->subselectAffectations(3,
                    1)
            ], $this->jointureEffectifAffectations(3, 1), [], Select::JOIN_LEFT)
            ->join(
            [
                $this->aliasEffectifAffectations(1, 2) => $this->subselectAffectations(1,
                    2)
            ], $this->jointureEffectifAffectations(1, 2), [], Select::JOIN_LEFT)
            ->join(
            [
                $this->aliasEffectifAffectations(2, 2) => $this->subselectAffectations(2,
                    2)
            ], $this->jointureEffectifAffectations(2, 2), [], Select::JOIN_LEFT)
            ->join(
            [
                $this->aliasEffectifAffectations(3, 2) => $this->subselectAffectations(3,
                    2)
            ], $this->jointureEffectifAffectations(3, 2), [], Select::JOIN_LEFT)
            ->where($elevesInscritsNonAffectes())
            ->order([
            'nom',
            'prenom'
        ]);
        return $select;
    }

    /**
     * SELECT DISTINCT millesime, eleveId FROM `sbm_t_affectations` WHERE moment=2 AND
     * trajet=1
     */
    private function subselectAffectations(int $moment, int $trajet = 1): Select
    {
        $where = new Where();
        $where->equalTo('moment', $moment)->equalTo('trajet', $trajet);
        $select = new Select($this->db_manager->getCanonicName('affectations'));
        return $select->columns([
            'millesime',
            'eleveId'
        ])
            ->quantifier(Select::QUANTIFIER_DISTINCT)
            ->where($where);
    }

    private function jointureEffectifAffectations(int $moment, int $trajet = 1): string
    {
        return sprintf('%1$s.millesime = sco.millesime And %1$s.eleveId = sco.eleveId',
            $this->aliasEffectifAffectations($moment, $trajet));
    }

    private function aliasEffectifAffectations(int $moment, int $trajet = 1): string
    {
        return 'aff' . $moment . 'R' . $trajet;
    }

    public function getPreinscritsNonAffectes()
    {
        return $this->renderResult($this->selectPreinscritsNonAffectes());
    }

    public function paginatorPreinscritsNonAffectes()
    {
        return $this->paginator($this->selectPreinscritsNonAffectes());
    }

    protected function selectPreinscritsNonAffectes()
    {
        $select = clone $this->select;
        $select->quantifier($select::QUANTIFIER_DISTINCT)
            ->join([
            'cla' => $this->db_manager->getCanonicName('classes', 'table')
        ], 'sco.classeId = cla.classeId', [])
            ->join([
            'r' => $this->db_manager->getCanonicName('responsables', 'table')
        ], 'ele.responsable1Id = r.responsableId OR ele.responsable2Id=r.responsableId',
            [
                'estR1' => new Expression(
                    'CASE WHEN r.responsableId=ele.responsable1Id THEN 1 ELSE 0 END'),
                'responsableId' => 'responsableId',
                'adresseL1' => 'adresseL1',
                'adresseL2' => 'adresseL2',
                'adresseL3' => 'adresseL3'
            ])
            ->join(
            [
                'comresp' => $this->db_manager->getCanonicName('communes', 'table')
            ], 'r.communeId = comresp.communeId',
            [
                'commune' => 'nom',
                'lacommune' => 'alias',
                'laposte' => 'alias_laposte'
            ])
            ->join(
            [
                $this->aliasEffectifAffectations(1, 1) => $this->subselectAffectations(1,
                    1)
            ], $this->jointureEffectifAffectations(1, 1), [], Select::JOIN_LEFT)
            ->join(
            [
                $this->aliasEffectifAffectations(2, 1) => $this->subselectAffectations(2,
                    1)
            ], $this->jointureEffectifAffectations(2, 1), [], Select::JOIN_LEFT)
            ->join(
            [
                $this->aliasEffectifAffectations(3, 1) => $this->subselectAffectations(3,
                    1)
            ], $this->jointureEffectifAffectations(3, 1), [], Select::JOIN_LEFT)
            ->join(
            [
                $this->aliasEffectifAffectations(1, 2) => $this->subselectAffectations(1,
                    2)
            ], $this->jointureEffectifAffectations(1, 2), [], Select::JOIN_LEFT)
            ->join(
            [
                $this->aliasEffectifAffectations(2, 2) => $this->subselectAffectations(2,
                    2)
            ], $this->jointureEffectifAffectations(2, 2), [], Select::JOIN_LEFT)
            ->join(
            [
                $this->aliasEffectifAffectations(3, 2) => $this->subselectAffectations(3,
                    2)
            ], $this->jointureEffectifAffectations(3, 2), [], Select::JOIN_LEFT);
        // composition du where à partir des élèves préinscrits
        $nonAffectes = new Predicate(
            [
                new PredicateEleve\PredicateDemandeNonTraitee(),
                new PredicateEleve\PredicateAccordSansAffectation()
            ], Predicate::COMBINED_BY_OR);
        $elevesPreinscritsNonAffectes = new PredicateEleve\ElevesPreinscrits(
            $this->millesime, 'sco', [
                $nonAffectes
            ], Predicate::COMBINED_BY_AND);
        return $select->where($elevesPreinscritsNonAffectes())->order([
            'nom',
            'prenom'
        ]);
    }

    public function getDemandeGaDistanceR2Zero()
    {
        return $this->renderResult($this->selectDemandeGaDistanceR2Zero());
    }

    public function paginatorDemandeGaDistanceR2Zero()
    {
        return $this->paginator($this->selectDemandeGaDistanceR2Zero());
    }

    protected function selectDemandeGaDistanceR2Zero()
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
                'sexe' => 'sexe',
                'numero' => 'numero',
                'responsable1Id' => 'responsable1Id',
                'responsable2Id' => 'responsable2Id',
                'responsableFId' => 'responsableFId',
                'selectionEleve' => 'selection',
                'noteEleve' => 'note'
            ])
            ->join([
            'r1' => $this->db_manager->getCanonicName('responsables', 'table')
        ], 'ele.responsable1Id = r1.responsableId',
            [
                'responsable1' => new Expression('CONCAT(r1.nom, " ", r1.prenom)'),
                'adresseR1L1' => 'adresseL1',
                'adresseR1L2' => 'adresseL2',
                'adresseR1L3' => 'adresseL3',
                'xr1' => 'x',
                'yr1' => 'y'
            ])
            ->join([
            'comr1' => $this->db_manager->getCanonicName('communes', 'table')
        ], 'r1.communeId = comr1.communeId',
            [
                'communeR1' => 'nom',
                'lacommuneR1' => 'alias',
                'laposteR1' => 'alias_laposte'
            ])
            ->join([
            'r2' => $this->db_manager->getCanonicName('responsables', 'table')
        ], 'ele.responsable2Id = r2.responsableId',
            [
                'adresseR2L1' => 'adresseL1',
                'adresseR2L2' => 'adresseL2',
                'adresseR2L3' => 'adresseL3',
                'x2' => 'x',
                'y2' => 'y'
            ], Select::JOIN_LEFT)
            ->join([
            'comr2' => $this->db_manager->getCanonicName('communes', 'table')
        ], 'r2.communeId = comr2.communeId',
            [
                'communeR2' => 'nom',
                'lacommuneR2' => 'alias',
                'laposteR2' => 'alias_laposte'
            ], Select::JOIN_LEFT);
        $predicate = new Predicate();
        $predicate->literal('demandeR2 = 1')->literal('distanceR2 = 0');
        $demandeEnGA_distance0 = new PredicateEleve\ElevesEnGA($this->millesime, 'sco',
            [
                $predicate
            ]);
        return $select->where($demandeEnGA_distance0());
    }

    public function getEnfants($responsableId, $ga = 1)
    {
        return $this->renderResult($this->selectEnfants($responsableId, $ga));
    }

    protected function selectEnfants($responsableId, $ga = 1)
    {
        $select = clone $this->select;
        $where = new Where();
        $where->equalTo('millesime', $this->millesime)->equalTo(
            sprintf('responsable%dId', $ga), $responsableId);
        return $select->where($where);
    }

    public function getScolaritePrecedente($eleveId)
    {
        $statement = $this->sql->prepareStatementForSqlObject(
            $this->selectScolaritePrecedente($eleveId));
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

    protected function selectScolaritePrecedente($eleveId)
    {
        $millesime = $this->millesime;
        $millesime --;
        $where = new Where();
        $where->equalTo('millesime', $millesime)->equalTo('eleveId', $eleveId);
        return $this->sql->select(
            [
                'sco' => $this->db_manager->getCanonicName('scolarites', 'table')
            ])
            ->columns([
            'eleveId'
        ])
            ->join(
            [
                'eta' => $this->db_manager->getCanonicName('etablissements', 'table')
            ], 'eta.etablissementId = sco.etablissementId', [
                'etablissement' => 'nom'
            ], Select::JOIN_LEFT)
            ->join([
            'cla' => $this->db_manager->getCanonicName('classes', 'table')
        ], 'cla.classeId = sco.classeId', [
            'classe' => 'nom'
        ], Select::JOIN_LEFT)
            ->where($where);
    }
}