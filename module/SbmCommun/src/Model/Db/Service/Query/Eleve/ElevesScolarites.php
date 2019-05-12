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
 * @date 12 mai 2019
 * @version 2019-2.5.0
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
                'grilleCode' => 'grilleTarif',
                'grilleTarif' => 'grilleTarif',
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
        ], 'eta.communeId = com.communeId', [
            'communeEtablissement' => 'nom'
        ])
            ->join(
            [
                'photos' => $this->db_manager->getCanonicName('elevesphotos', 'table')
            ], 'photos.eleveId = ele.eleveId',
            [
                'hasphoto' => new Expression(
                    'CASE WHEN isnull(photos.eleveId) THEN FALSE ELSE TRUE END')
            ], Select::JOIN_LEFT);
        $this->addStrategy('grilleTarif',
            $this->db_manager->get('Sbm\Db\Table\Tarifs')
                ->getStrategie('grille'));
    }

    private function jointureElevesResponsables(int $responsableId)
    {
        $jointure = new Predicate(null, Predicate::COMBINED_BY_OR);
        return $jointure->equalTo('responsable1Id', $responsableId)->equalTo('responsable2Id',
            $responsableId);
    }

    public function getEleve($eleveId)
    {
        $select = clone $this->select;
        $where = new Where();
        $where->equalTo('millesime', $this->millesime)->equalTo('ele.eleveId', $eleveId);
        return $this->renderResult($select->where($where))
            ->current();
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
            ], 'r.communeId = comresp.communeId', [
                'commune' => 'nom'
            ]);
        $where = new Where();
        $where->equalTo('millesime', $this->millesime)->equalTo('ele.eleveId', $eleveId);
        return $this->renderResult($select->where($where));
    }

    public function getElevesInscrits($responsableId)
    {
        $select = clone $this->select;
        $elevesSansPreinscrits = new PredicateEleve\ElevesSansPreinscrits(
            $this->millesime, 'sco', [
                $this->jointureElevesResponsables($responsableId)
            ]);
        $where = $elevesSansPreinscrits();
        return $this->renderResult($select->where($where));
    }

    public function getElevesPreinscritsOuEnAttente($responsableId)
    {
        $select = clone $this->select;
        $jointure = $this->jointureElevesResponsables($responsableId);
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
        return $this->renderResult($select->where($where));
    }

    public function getElevesPreinscrits($responsableId)
    {
        $select = clone $this->select;
        $elevesPreinscrits = new PredicateEleve\ElevesPreinscrits($this->millesime, 'sco',
            [
                $this->jointureElevesResponsables($responsableId)
            ]);
        return $this->renderResult($select->where($elevesPreinscrits()));
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
        $tTarif = $this->db_manager->getCanonicName('tarifs', 'table');
        $montant = 0.0;
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
                $this->jointureElevesResponsables($responsableId)
            ]);
        $resultset = $this->renderResult($select->where($elevesInscrits()));
        foreach ($resultset as $row) {
            $montant += $tTarif->getMontant($row['grilleCode'], $row['quantite']);
        }
        return $montant;
    }

    public function getElevesPreinscritsWithGrille($responsableId)
    {
        $select = clone $this->select;
        $where = new Where(null, Where::COMBINED_BY_OR);
        $where->equalTo('responsable1Id', $responsableId)->equalTo('responsable2Id',
            $responsableId);
        $predicate = new PredicateEleve\ElevesPreinscrits($this->millesime, 'sco',
            [
                $where
            ]);
        return $this->renderResult($select->where($predicate()));
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
        $select = clone $this->select;
        $elevesResponsablePayant = new PredicateEleve\ElevesResponsablePayant(
            $this->millesime, 'sco', [
                $this->jointureElevesResponsables($responsableId)
            ]);
        return $this->renderResult($select->where($elevesResponsablePayant));
    }

    public function getElevesPayantsWithMontant($responsableId)
    {
        throw new \Exception(
            'TODO : remplacer cette méthode par `getElevesPayantsWithGrille()');
    }

    public function getNbDuplicatas($responsableId)
    {
        $select = $this->sql->select()
            ->from([
            'ele' => $this->db_manager->getCanonicName('eleves', 'table')
        ])
            ->join([
            'sco' => $this->db_manager->getCanonicName('scolarites', 'table')
        ], 'ele.eleveId = sco.eleveId', [])
            ->columns([
            'nbDuplicatas' => new Expression('sum(sco.duplicata)')
        ]);
        $where = new Where();
        $where->equalTo('millesime', $this->millesime)
            ->nest()
            ->equalTo('responsable1Id', $responsableId)->or->equalTo('responsable2Id',
            $responsableId)->unnest();
        return $this->renderResult($select->where($where))
            ->current()['nbDuplicatas'];
    }

    public function getInscritsNonAffectes()
    {
        return $this->renderResult($this->selectInscritsNonAffectes());
    }

    public function paginatorInscritsNonAffectes()
    {
        return $this->paginator($this->selectInscritsNonAffectes());
    }

    private function selectInscritsNonAffectes()
    {
        $select = clone $this->select;
        $select->quantifier($select::QUANTIFIER_DISTINCT)
            ->join([
            'r' => $this->db_manager->getCanonicName('responsables', 'table')
        ], 'ele.responsable1Id = r.responsableId OR ele.responsable2Id=r.responsableId',
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
            ], 'r.communeId = comresp.communeId', [
                'commune' => 'nom'
            ])
            ->join(
            [
                'aff' => $this->db_manager->getCanonicName('affectations', 'table')
            ],
            'aff.eleveId=ele.eleveId AND aff.responsableId=r.responsableId AND aff.millesime=sco.millesime',
            [], Select::JOIN_LEFT);
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
        return $select->where($elevesInscritsNonAffectes)->order([
            'nom',
            'prenom'
        ]);
    }

    public function getPreinscritsNonAffectes()
    {
        return $this->renderResult($this->selectPreinscritsNonAffectes());
    }

    public function paginatorPreinscritsNonAffectes()
    {
        return $this->paginator($this->selectPreinscritsNonAffectes());
    }

    private function selectPreinscritsNonAffectes()
    {
        $select = clone $this->select;
        $select->quantifier($select::QUANTIFIER_DISTINCT)
            ->join([
            'r' => $this->db_manager->getCanonicName('responsables', 'table')
        ], 'ele.responsable1Id = r.responsableId OR ele.responsable2Id=r.responsableId',
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
            ], 'r.communeId = comresp.communeId', [
                'commune' => 'nom'
            ])
            ->join(
            [
                'aff' => $this->db_manager->getCanonicName('affectations', 'table')
            ],
            'aff.eleveId=ele.eleveId AND aff.responsableId=r.responsableId AND aff.millesime=sco.millesime',
            [], Select::JOIN_LEFT);
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
        return $select->where($elevesPreinscritsNonAffectes)->order([
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

    private function selectDemandeGaDistanceR2Zero()
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
                'xr1' => 'x',
                'yr1' => 'y'
            ])
            ->join([
            'comr1' => $this->db_manager->getCanonicName('communes', 'table')
        ], 'r1.communeId = comr1.communeId', [
            'communeR1' => 'nom'
        ])
            ->join([
            'r2' => $this->db_manager->getCanonicName('responsables', 'table')
        ], 'ele.responsable2Id = r2.responsableId',
            [
                'adresseR2L1' => 'adresseL1',
                'adresseR2L2' => 'adresseL2',
                'x2' => 'x',
                'y2' => 'y'
            ], Select::JOIN_LEFT)
            ->join([
            'comr2' => $this->db_manager->getCanonicName('communes', 'table')
        ], 'r2.communeId = comr2.communeId', [
            'communeR2' => 'nom'
        ], Select::JOIN_LEFT);
        $predicate = new Predicate();
        $predicate->literal('demandeR2 = 1')->literal('distanceR2 = 0');
        $demandeEnGA_distance0 = new PredicateEleve\ElevesEnGA($$this->millesime, 'sco',
            [
                $predicate
            ]);
        return $select->where($demandeEnGA_distance0);
    }

    public function getEnfants($responsableId, $ga = 1)
    {
        $select = clone $this->select;
        $where = new Where();
        $where->equalTo('millesime', $this->millesime)->equalTo(
            sprintf('responsable%dId', $ga), $responsableId);
        return $this->renderResult($select->where($where));
    }

    public function getScolaritePrecedente($eleveId)
    {
        $millesime = $this->millesime;
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
            ], 'eta.etablissementId = sco.etablissementId', [
                'etablissement' => 'nom'
            ], Select::JOIN_LEFT)
            ->join([
            'cla' => $this->db_manager->getCanonicName('classes', 'table')
        ], 'cla.classeId = sco.classeId', [
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
     * @param Where|\Closure|string|array|\Zend\Db\Sql\Predicate\PredicateInterface $where
     * @param string $order
     * @param int $millesime
     *            (inutilisé mais gardé pour la compatibilité des appels)
     * @return \Zend\Paginator\Paginator
     */
    public function paginatorScolaritesR($where, $order = null, $millesime = null)
    {
        return $this->paginator($this->selectScolaritesR($where, $order));
    }

    public function getScolaritesR($where, $order = null, $millesime = null)
    {
        return $this->renderResult($this->selectScolaritesR($where, $order));
    }

    private function selectScolaritesR($where, $order = null, $millesime = null)
    {
        $select = $this->sql->select(
            [
                'sco' => $this->db_manager->getCanonicName('scolarites', 'table')
            ])
            ->columns([
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
            ->join([
            'etacom' => $this->db_manager->getCanonicName('communes', 'table')
        ], 'eta.communeId = etacom.communeId', [
            'communeEtablissement' => 'nom'
        ])
            ->join([
            'cla' => $this->db_manager->getCanonicName('classes', 'table')
        ], 'cla.classeId = sco.classeId', [
            'classe' => 'nom'
        ])
            ->join([
            'ele' => $this->db_manager->getCanonicName('eleves', 'table')
        ], 'ele.eleveId = sco.eleveId',
            [
                'nom' => 'nom',
                'nomSA' => 'nomSA',
                'prenom' => 'prenom',
                'prenomSA' => 'prenomSA',
                'dateN' => 'dateN',
                'sexe' => 'sexe',
                'numero' => 'numero'
            ])
            ->join(
            [
                'res1' => $this->db_manager->getCanonicName('responsables', 'table')
            ],
            new Expression(
                'ele.responsable1Id = res1.responsableId AND sco.demandeR1 > 0'),
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
            ->join([
            'sta1r1' => $this->db_manager->getCanonicName('stations', 'table')
        ], 'affr1.station1Id = sta1r1.stationId',
            [
                'station1r1' => 'nom',
                'station1IdR1' => 'stationId'
            ], Select::JOIN_LEFT)
            ->join([
            'sta2r1' => $this->db_manager->getCanonicName('stations', 'table')
        ], 'affr1.station2Id = sta2r1.stationId',
            [
                'station2r1' => 'nom',
                'station2IdR1' => 'stationId'
            ], Select::JOIN_LEFT)
            ->join([
            'ser1r1' => $this->db_manager->getCanonicName('services', 'table')
        ], 'affr1.service1Id = ser1r1.serviceId',
            [
                'service1r1' => 'nom',
                'service1IdR1' => 'serviceId',
                'service1AliasR1' => 'alias',
                'service1AliasTrR1' => 'aliasTr'
            ], Select::JOIN_LEFT)
            ->join([
            'lot1r1' => $this->db_manager->getCanonicName('lots', 'table')
        ], 'ser1r1.lotId = lot1r1.lotId',
            [
                'service1MarcheR1' => 'marche',
                'service1LotR1' => 'lot'
            ], Select::JOIN_LEFT)
            ->join(
            [
                'tit1r1' => $this->db_manager->getCanonicName('transporteurs', 'table')
            ], 'lot1r1.transporteurId = tit1r1.transporteurId',
            [
                'service1TitulaireR1' => 'nom'
            ], Select::JOIN_LEFT)
            ->join(
            [
                'tra1r1' => $this->db_manager->getCanonicName('transporteurs', 'table')
            ], 'ser1r1.transporteurId = tra1r1.transporteurId',
            [
                'transporteur1r1' => 'nom'
            ], Select::JOIN_LEFT)
            ->join([
            'ser2r1' => $this->db_manager->getCanonicName('services', 'table')
        ], 'affr1.service2Id = ser2r1.serviceId',
            [
                'service2r1' => 'nom',
                'service2IdR1' => 'serviceId',
                'service2AliasR1' => 'alias',
                'service2AliasTrR1' => 'aliasTr'
            ], Select::JOIN_LEFT)
            ->join([
            'lot2r1' => $this->db_manager->getCanonicName('lots', 'table')
        ], 'ser2r1.lotId = lot2r1.lotId',
            [
                'service2MarcheR1' => 'marche',
                'service2LotR1' => 'lot'
            ], Select::JOIN_LEFT)
            ->join(
            [
                'tit2r1' => $this->db_manager->getCanonicName('transporteurs', 'table')
            ], 'lot2r1.transporteurId = tit2r1.transporteurId',
            [
                'service2TitulaireR1' => 'nom'
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
            new Expression(
                'ele.responsable2Id = res2.responsableId AND sco.demandeR2 > 0'),
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
            ->join([
            'sta1r2' => $this->db_manager->getCanonicName('stations', 'table')
        ], 'affr2.station1Id = sta1r2.stationId',
            [
                'station1r2' => 'nom',
                'station1IdR2' => 'stationId'
            ], Select::JOIN_LEFT)
            ->join([
            'sta2r2' => $this->db_manager->getCanonicName('stations', 'table')
        ], 'affr2.station2Id = sta2r2.stationId',
            [
                'station2r2' => 'nom',
                'station2IdR2' => 'stationId'
            ], Select::JOIN_LEFT)
            ->join([
            'ser1r2' => $this->db_manager->getCanonicName('services', 'table')
        ], 'affr2.service1Id = ser1r2.serviceId',
            [
                'service1r2' => 'nom',
                'service1IdR2' => 'serviceId',
                'service1AliasR2' => 'alias',
                'service1AliasTrR2' => 'aliasTr'
            ], Select::JOIN_LEFT)
            ->join([
            'lot1r2' => $this->db_manager->getCanonicName('lots', 'table')
        ], 'ser1r2.lotId = lot1r2.lotId',
            [
                'service1MarcheR2' => 'marche',
                'service1LotR2' => 'lot'
            ], Select::JOIN_LEFT)
            ->join(
            [
                'tit1r2' => $this->db_manager->getCanonicName('transporteurs', 'table')
            ], 'lot1r2.transporteurId = tit1r2.transporteurId',
            [
                'service1TitulaireR2' => 'nom'
            ], Select::JOIN_LEFT)
            ->join(
            [
                'tra1r2' => $this->db_manager->getCanonicName('transporteurs', 'table')
            ], 'ser1r2.transporteurId = tra1r2.transporteurId',
            [
                'transporteur1r2' => 'nom'
            ], Select::JOIN_LEFT)
            ->join([
            'ser2r2' => $this->db_manager->getCanonicName('services', 'table')
        ], 'affr2.service2Id = ser2r2.serviceId',
            [
                'service2r2' => 'nom',
                'service2IdR2' => 'serviceId',
                'service2AliasR2' => 'alias',
                'service2AliasTrR2' => 'aliasTr'
            ], Select::JOIN_LEFT)
            ->join([
            'lot2r2' => $this->db_manager->getCanonicName('lots', 'table')
        ], 'ser2r2.lotId = lot2r2.lotId',
            [
                'service2MarcheR2' => 'marche',
                'service2LotR2' => 'lot'
            ], Select::JOIN_LEFT)
            ->join(
            [
                'tit2r2' => $this->db_manager->getCanonicName('transporteurs', 'table')
            ], 'lot2r2.transporteurId = tit2r2.transporteurId',
            [
                'service2TitulaireR2' => 'nom'
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