<?php
/**
 * Requête permettant d'obtenir les renseignements complets sur les élèves et leurs responsables
 *
 *
 * @project sbm
 * @package SbmCommun/src/Model/Db/Service/Query/Eleve
 * @filesource ElevesResponsables.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 25 sept. 2020
 * @version 2020-2.6.0
 */
namespace SbmCommun\Model\Db\Service\Query\Eleve;

use SbmCommun\Model\Db\Service\Query\AbstractQuery;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Where;

class ElevesResponsables extends AbstractQuery
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
                'responsable2Id' => 'responsable2Id',
                'responsableFId' => 'responsableFId',
                'selectionEleve' => 'selection',
                'noteEleve' => 'note'
            ])
            ->join([
            'r1' => $this->db_manager->getCanonicName('responsables', 'table')
        ], 'ele.responsable1Id=r1.responsableId',
            [
                'titreR1' => 'titre',
                'responsable1NomPrenom' => new Expression('concat(r1.nom," ",r1.prenom)'),
                'adresseL1R1' => 'adresseL1',
                'adresseL2R1' => 'adresseL2',
                'adresseL3R1' => 'adresseL3',
                'codePostalR1' => 'codePostal',
                'communeIdR1' => 'communeId',
                'telephoneFR1' => 'telephoneF',
                'telephonePR1' => 'telephoneP',
                'telephoneTR1' => 'telephoneT',
                'emailR1' => 'email',
                'x1' => 'x',
                'y1' => 'y'
            ])
            ->join([
            'r1c' => $this->db_manager->getCanonicName('communes', 'table')
        ], 'r1.communeId=r1c.communeId',
            [
                'communeR1' => 'nom',
                'lacommuneR1' => 'alias',
                'laposteR1' => 'alias_laposte'
            ]);
    }

    /**
     * Renvoie un tableau contenant les données de l'élève et de son responsable 1
     *
     * @param int $eleveId
     *
     * @return array
     */
    public function getEleveResponsable1($eleveId)
    {
        $where = new Where();
        $where->equalTo('eleveId', $eleveId);
        $select = clone $this->select;
        ;
        return $this->renderResult($select->where($where))
            ->current();
    }

    /**
     * Renvoie le résultat de la requête
     *
     * @param Where $where
     * @param string|array $order
     *
     * @return \Zend\Db\Adapter\Driver\ResultInterface
     */
    public function withR2(Where $where, $order = null)
    {
        return $this->renderResult($this->selectR2($where, $order));
    }

    public function paginatorR2(Where $where, $order = null)
    {
        return $this->paginator($this->selectR2($where, $order));
    }

    protected function selectR2(Where $where, $order = null)
    {
        $select = clone $this->select;
        $select->join(
            [
                'r2' => $this->db_manager->getCanonicName('responsables', 'table')
            ], 'ele.responsable2Id=r2.responsableId',
            [
                'titreR2' => 'titre',
                'responsable2NomPrenom' => new Expression(
                    'CASE WHEN isnull(r2.responsableId) THEN "" ELSE concat(r2.nom," ",r2.prenom) END'),
                'adresseL1R2' => 'adresseL1',
                'adresseL2R2' => 'adresseL2',
                'adresseL3R2' => 'adresseL3',
                'codePostalR2' => 'codePostal',
                'telephoneFR2' => 'telephoneF',
                'telephonePR2' => 'telephoneP',
                'telephoneTR2' => 'telephoneT',
                'emailR2' => 'email',
                'x2' => 'x',
                'y2' => 'y'
            ], $select::JOIN_LEFT)
            ->join([
            'r2c' => $this->db_manager->getCanonicName('communes', 'table')
        ], 'r2.communeId=r2c.communeId',
            [
                'communeR2' => 'nom',
                'lacommuneR2' => 'alias',
                'laposteR2' => 'alias_laposte'
            ], $select::JOIN_LEFT);
        if (! is_null($order)) {
            $select->order($order);
        }
        return $select->where($where);
    }

    /**
     * Si on ne précise pas le millesime, on utilise le millesime courant Noter que pour
     * examiner le contenu de la requête, on peut la transformer en tableau par la
     * fonction php iterator_to_array().
     *
     * @param Where $where
     * @param string|array $order
     * @param int $millesime
     *
     * @return \Zend\Db\Adapter\Driver\ResultInterface
     */
    public function withScolaritesR2(Where $where, $order = null, $millesime = null)
    {
        return $this->renderResult($this->selectScolaritesR2($where, $order, $millesime));
    }

    public function paginatorScolaritesR2(Where $where, $order = null, $millesime = null)
    {
        return $this->paginator($this->selectScolaritesR2($where, $order, $millesime));
    }

    protected function selectScolaritesR2(Where $where, $order, $millesime)
    {
        if (is_null($millesime)) {
            $millesime = $this->millesime;
        }
        $where_appel = new Where();
        $where_appel->literal('notified = 0')->like('refdet', $this->millesime . '%');
        $select_appels = $this->sql->select(
            $this->db_manager->getCanonicName('appels', 'table'))
            ->columns([
            'eleveId'
        ])
            ->quantifier(Select::QUANTIFIER_DISTINCT)
            ->where($where_appel);
        $where->equalTo('sco.millesime', $millesime);
        $select = clone $this->select;
        $select->join(
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
                'adresseL1' => 'adresseL1',
                'adresseL2' => 'adresseL2',
                'codePostal' => 'codePostal',
                'communeId' => 'communeId',
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
                'grilleTarifR1' => 'grilleTarifR1',
                'grilleCodeR1' => 'grilleTarifR1',
                'reductionR1' => 'reductionR1',
                'grilleCodeR2' => 'grilleTarifR2',
                'reductionR2' => 'reductionR2',
                'tarifId' => 'tarifId',
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
                'etablissement' => new Expression(
                    'CASE WHEN isnull(eta.alias) OR eta.alias = "" THEN eta.nom ELSE eta.alias END'),
                'xeta' => 'x',
                'yeta' => 'y',
                'niveauEtablissement' => 'niveau'
            ])
            ->join([
            'com' => $this->db_manager->getCanonicName('communes', 'table')
        ], 'eta.communeId = com.communeId',
            [
                'communeEtablissement' => 'nom',
                'lacommuneEtablissement' => 'aliasCG',
                'laposteEtablissement' => 'alias_laposte'
            ])
            ->join([
            'cla' => $this->db_manager->getCanonicName('classes', 'table')
        ], 'cla.classeId = sco.classeId', [
            'niveau' => 'niveau',
            'classe' => 'nom'
        ])
            ->join([
            'ori1' => $this->db_manager->getCanonicName('stations', 'table')
        ], 'ori1.stationId = sco.stationIdR1', [
            'origine1' => 'nom'
        ])
            ->join([
            'cor1' => $this->db_manager->getCanonicName('communes', 'table')
        ], 'cor1.communeId = ori1.communeId',
            [
                'communeOrigine1' => 'nom',
                'lacommuneOrigine1' => 'alias',
                'laposteOrigine1' => 'alias_laposte'
            ])
            ->join([
            'r2' => $this->db_manager->getCanonicName('responsables', 'table')
        ], 'ele.responsable2Id=r2.responsableId',
            [
                'titreR2' => 'titre',
                'responsable2NomPrenom' => new Expression(
                    'CASE WHEN isnull(r2.responsableId) THEN "" ELSE concat(r2.nom," ",r2.prenom) END'),
                'adresseL1R2' => 'adresseL1',
                'adresseL2R2' => 'adresseL2',
                'adresseL3R2' => 'adresseL3',
                'codePostalR2' => 'codePostal',
                'communeIdR2' => 'communeId',
                'telephoneFR2' => 'telephoneF',
                'telephonePR2' => 'telephoneP',
                'telephoneTR2' => 'telephoneT',
                'emailR2' => 'email',
                'x2' => 'x',
                'y2' => 'y'
            ], $select::JOIN_LEFT)
            ->join([
            'r2c' => $this->db_manager->getCanonicName('communes', 'table')
        ], 'r2.communeId=r2c.communeId',
            [
                'communeR2' => 'nom',
                'lacommuneR2' => 'alias',
                'laposteR2' => 'alias_laposte'
            ], $select::JOIN_LEFT)
            ->join([
            'ori2' => $this->db_manager->getCanonicName('stations', 'table')
        ], 'ori2.stationId = sco.stationIdR2', [
            'origine2' => 'nom'
        ], $select::JOIN_LEFT)
            ->join([
            'cor2' => $this->db_manager->getCanonicName('communes', 'table')
        ], 'cor2.communeId = ori2.communeId',
            [
                'communeOrigine2' => 'nom',
                'lacommuneOrigine2' => 'alias',
                'laposteOrigine2' => 'alias_laposte'
            ], $select::JOIN_LEFT)
            ->join(
            [
                $this->aliasEffectifAffectations(1) => $this->subselectAffectations(1)
            ], $this->jointureEffectifAffectations(1), [
                'affecteR1matin' => 'eleveId'
            ], $select::JOIN_LEFT)
            ->join(
            [
                $this->aliasEffectifAffectations(2) => $this->subselectAffectations(2)
            ], $this->jointureEffectifAffectations(2), [
                'affecteR1midi' => 'eleveId'
            ], $select::JOIN_LEFT)
            ->join(
            [
                $this->aliasEffectifAffectations(3) => $this->subselectAffectations(3)
            ], $this->jointureEffectifAffectations(3), [
                'affecteR1soir' => 'eleveId'
            ], $select::JOIN_LEFT)
            ->join(
            [
                $this->aliasEffectifAffectations(1, 2) => $this->subselectAffectations(1,
                    2)
            ], $this->jointureEffectifAffectations(1, 2),
            [
                'affecteR2matin' => 'eleveId'
            ], $select::JOIN_LEFT)
            ->join(
            [
                $this->aliasEffectifAffectations(2, 2) => $this->subselectAffectations(2,
                    2)
            ], $this->jointureEffectifAffectations(2, 2), [
                'affecteR2midi' => 'eleveId'
            ], $select::JOIN_LEFT)
            ->join(
            [
                $this->aliasEffectifAffectations(3, 2) => $this->subselectAffectations(3,
                    2)
            ], $this->jointureEffectifAffectations(3, 2), [
                'affecteR2soir' => 'eleveId'
            ], $select::JOIN_LEFT)
            /*->join(
            [
                'aff' => $this->db_manager->getCanonicName('affectations', 'table')
            ], 'aff.millesime = sco.millesime And aff.eleveId = sco.eleveId',
            [
                'affecte' => new Expression('count(aff.eleveId) > 0')
            ], $select::JOIN_LEFT)*/
            ->join(
            [
                'photos' => $this->db_manager->getCanonicName('elevesphotos', 'table')
            ], 'photos.eleveId = ele.eleveId',
            [
                'sansphoto' => new Expression(
                    'CASE WHEN isnull(photos.eleveId) THEN TRUE ELSE FALSE END')
            ], $select::JOIN_LEFT)
            ->join([
            'appels' => $select_appels
        ], 'appels.eleveId = ele.eleveId',
            [
                'appelNotifieOk' => new Expression(
                    'CASE WHEN isnull(appels.eleveId) THEN TRUE ELSE FALSE END')
            ], $select::JOIN_LEFT)
            ->group('ele.eleveId');
        if (! is_null($order)) {
            $select->order($order);
        }
        $this->addStrategy('grilleTarifR1',
            $this->db_manager->get('Sbm\Db\Table\Tarifs')
                ->getStrategie('grille'));
        $select->where($where);
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

    private function expressionSqlEffectifAffectations(int $moment, int $trajet = 1): string
    {
        return sprintf('count(%s.eleveId) > 0',
            $this->aliasEffectifAffectations($moment, $trajet));
    }

    /**
     *
     * @param Where $where
     * @param string $order
     * @param string $millesime
     *
     * @return \Zend\Db\Adapter\Driver\ResultInterface
     */
    public function withScolaritesEleveGroup(Where $where, $order = null,
        $millesime = null)
    {
        return $this->renderResult(
            $this->selectScolaritesEleveGroup($where, $order, $millesime));
    }

    /**
     *
     * @param Where $where
     * @param string $order
     * @param string $millesime
     *
     * @return \Zend\Paginator\Paginator
     */
    public function paginatorScolaritesEleveGroup(Where $where, $order = null,
        $millesime = null)
    {
        return $this->paginator(
            $this->selectScolaritesEleveGroup($where, $order, $millesime));
    }

    /**
     *
     * @param Where $where
     * @param string $order
     * @param string $millesime
     *
     * @return \Zend\Db\Sql\Select
     */
    protected function selectScolaritesEleveGroup(Where $where, $order = null,
        $millesime = null)
    {
        // table de recherche du plus grand millesime pour chaque élève
        $select1 = $this->sql->select();
        $select1->from($this->db_manager->getCanonicName('scolarites', 'table'))
            ->columns([
            'max_millesime' => new Expression('MAX(millesime)'),
            'eleveId'
        ])
            ->group('eleveId');

        // table des scolarites à prendre en compte pour le millesime en question
        if (is_null($millesime)) {
            $millesime = $this->millesime;
        }
        $select2 = $this->sql->select();
        $select2->from($this->db_manager->getCanonicName('scolarites', 'table'))
            ->columns([
            'eleveId'
        ])->where->equalTo('millesime', $millesime);

        // requête principale
        $select = clone $this->select;
        $select->join([
            'filtre' => $select1
        ], 'filtre.eleveId = ele.eleveId', [
            'max_millesime'
        ])
            ->join([
            'sco' => $this->db_manager->getCanonicName('scolarites', 'table')
        ], 'filtre.max_millesime = sco.millesime AND filtre.eleveId = sco.eleveId',
            [
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
                'dateEtiquetteR1' => 'dateEtiquetteR1',
                'dateEtiquetteR2' => 'dateEtiquetteR2',
                'dateCarteR1' => 'dateCarteR1',
                'dateCarteR2' => 'dateCarteR2',
                'inscrit' => 'inscrit',
                'gratuit' => 'gratuit',
                'paiementR1' => 'paiementR1',
                'paiementR2' => 'paiementR2',
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
                'grilleTarifR1' => 'grilleTarifR1',
                'grilleCodeR1' => 'grilleTarifR1',
                'reductionR1' => 'reductionR1',
                'grilleCodeR2' => 'grilleTarifR2',
                'reductionR2' => 'reductionR2',
                'tarifId' => 'tarifId',
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
                'etablissement' => new Expression(
                    'CASE WHEN isnull(eta.alias) OR eta.alias = "" THEN eta.nom ELSE eta.alias END'),
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
            ->join([
            'cla' => $this->db_manager->getCanonicName('classes', 'table')
        ], 'cla.classeId = sco.classeId', [
            'classe' => 'nom'
        ])
            ->join([
            'r2' => $this->db_manager->getCanonicName('responsables', 'table')
        ], 'ele.responsable2Id=r2.responsableId',
            [
                'titreR2' => 'titre',
                'responsable2NomPrenom' => new Expression(
                    'CASE WHEN isnull(r2.responsableId) THEN "" ELSE concat(r2.nom," ",r2.prenom) END'),
                'adresseL1R2' => 'adresseL1',
                'adresseL2R2' => 'adresseL2',
                'adresseL3R2' => 'adresseL3',
                'codePostalR2' => 'codePostal',
                'emailR2' => 'email',
                'x2' => 'x',
                'y2' => 'y'
            ], $select::JOIN_LEFT)
            ->join([
            'r2c' => $this->db_manager->getCanonicName('communes', 'table')
        ], 'r2.communeId=r2c.communeId',
            [
                'communeR2' => 'nom',
                'lacommuneR2' => 'alias',
                'laposteR2' => 'alias_laposte'
            ], $select::JOIN_LEFT)
            ->join([
            's' => $select2
        ], 'ele.eleveId = s.eleveId',
            [
                'scolarise' => new Expression('s.eleveId IS NOT NULL')
            ], $select::JOIN_LEFT)
            ->join(
            [
                'photos' => $this->db_manager->getCanonicName('elevesphotos', 'table')
            ], 'photos.eleveId = ele.eleveId',
            [
                'sansphoto' => new Expression(
                    'CASE WHEN isnull(photos.eleveId) THEN TRUE ELSE FALSE END')
            ], $select::JOIN_LEFT);
        if (! is_null($order)) {
            $select->order($order);
        }
        $this->addStrategy('grilleTarifR1',
            $this->db_manager->get('Sbm\Db\Table\Tarifs')
                ->getStrategie('grille'));
        // die($this->getSqlString($select));
        return $select->where($where);
    }

    /**
     * Requête préparée renvoyant les positions géographiques des domiciles de l'élève
     * (chez, responsable1, responsable2),
     *
     * @param Where $where
     * @param string $order
     *
     * @return \Zend\Db\Adapter\Driver\ResultInterface
     */
    public function getLocalisation(Where $where, $order = null)
    {
        return $this->renderResult($this->selectLocalisation($where, $order));
    }

    protected function selectLocalisation(Where $where, $order = null)
    {
        $where->equalTo('millesime', $this->millesime);
        $sql = new Sql($this->db_manager->getDbAdapter());
        $select = $sql->select();
        $select->from([
            'ele' => $this->db_manager->getCanonicName('eleves', 'table')
        ])
            ->columns(
            [
                'id_tra',
                'numero',
                'nom_eleve' => 'nomSA',
                'prenom_eleve' => 'prenomSA',
                'dateN',
                'sexe',
                'X' => new Expression('IF(sco.x = 0 AND sco.y = 0, r1.x, sco.x)'),
                'Y' => new Expression('IF(sco.x = 0 AND sco.y = 0, r1.y, sco.y)')
            ])
            ->join([
            'sco' => $this->db_manager->getCanonicName('scolarites', 'table')
        ], 'ele.eleveId=sco.eleveId',
            [
                'regimeId' => 'regimeId',
                'transportGA' => new Expression(
                    'CASE WHEN demandeR2 > 0 THEN "Oui" ELSE "Non" END'),
                'x_eleve' => 'x',
                'y_eleve' => 'y',
                'chez',
                'adresseL1_chez' => 'adresseL1',
                'adresseL2_chez' => 'adresseL2',
                'codePostal_chez' => 'codePostal',
                'commentaire'
            ])
            ->join([
            'comsco' => $this->db_manager->getCanonicName('communes', 'table')
        ], 'sco.communeId=comsco.communeId',
            [
                'commune_chez' => 'nom',
                'lacommune_chez' => 'alias',
                'laposte_chez' => 'alias_laposte'
            ], $select::JOIN_LEFT)
            ->join(
            [
                'eta' => $this->db_manager->getCanonicName('etablissements', 'table')
            ], 'sco.etablissementId=eta.etablissementId',
            [
                'etablissement' => new Expression(
                    'CASE WHEN isnull(eta.alias) OR eta.alias = "" THEN eta.nom ELSE eta.alias END'),
                'x_etablissement' => 'x',
                'y_etablissement' => 'y'
            ])
            ->join([
            'cometa' => $this->db_manager->getCanonicName('communes', 'table')
        ], 'cometa.communeId=eta.communeId',
            [
                'commune_etablissement' => 'nom',
                'lacommune_etablissement' => 'alias',
                'laposte_etablissement' => 'alias_laposte'
            ])
            ->join([
            'cla' => $this->db_manager->getCanonicName('classes', 'table')
        ], 'sco.classeId=cla.classeId', [
            'classe' => 'nom'
        ])
            ->join([
            'r1' => $this->db_manager->getCanonicName('responsables', 'table')
        ], 'r1.responsableId=ele.responsable1Id',
            [
                'responsable1' => new Expression('concat(r1.nom," ",r1.prenom)'),
                'x_responsable1' => 'x',
                'y_responsable1' => 'y',
                'telephoneF_responsable1' => 'telephoneF',
                'telephoneP_responsable1' => 'telephoneP',
                'telephoneT_responsable1' => 'telephoneT',
                'email_responsable1' => 'email',
                'adresseL1_responsable1' => 'adresseL1',
                'adresseL2_responsable1' => 'adresseL2',
                'adresseL3_responsable1' => 'adresseL3',
                'codePostal_responsable1' => 'codePostal'
            ])
            ->join([
            'comr1' => $this->db_manager->getCanonicName('communes', 'table')
        ], 'comr1.communeId=r1.communeId',
            [
                'commune_responsable1' => 'nom',
                'lacommune_responsable1' => 'alias',
                'laposte_responsable1' => 'alias_laposte'
            ])
            ->join([
            'ori1' => $this->db_manager->getCanonicName('stations', 'table')
        ], 'ori1.stationId=sco.stationIdR1', [
            'station_origineR1' => 'nom'
        ])
            ->join(
            [
                'comori1' => $this->db_manager->getCanonicName('communes', 'table')
            ], 'ori1.communeId=comori1.communeId',
            [
                'commune_origineR1' => 'nom',
                'lacommune_origineR1' => 'alias',
                'laposte_origineR1' => 'alias_laposte'
            ])
            ->join([
            'r2' => $this->db_manager->getCanonicName('responsables', 'table')
        ], 'r2.responsableId=ele.responsable2Id',
            [
                'responsable2' => new Expression(
                    'CASE WHEN isnull(r2.responsableId) THEN "" ELSE concat(r2.nom," ",r2.prenom) END'),
                'x_responsable2' => 'x',
                'y_responsable2' => 'y',
                'telephoneF_responsable2' => 'telephoneF',
                'telephoneP_responsable2' => 'telephoneP',
                'telephoneT_responsable2' => 'telephoneT',
                'email_responsable2' => 'email',
                'adresseL1_responsable2' => 'adresseL1',
                'adresseL2_responsable2' => 'adresseL2',
                'adresseL3_responsable2' => 'adresseL3',
                'codePostal_responsable2' => 'codePostal'
            ], $select::JOIN_LEFT)
            ->join([
            'comr2' => $this->db_manager->getCanonicName('communes', 'table')
        ], 'comr2.communeId=r2.communeId',
            [
                'commune_responsable2' => 'nom',
                'lacommune_responsable2' => 'alias',
                'laposte_responsable2' => 'alias_laposte'
            ], $select::JOIN_LEFT)
            ->join([
            'ori2' => $this->db_manager->getCanonicName('stations', 'table')
        ], 'ori2.stationId=sco.stationIdR2', [
            'station_origineR2' => 'nom'
        ], $select::JOIN_LEFT)
            ->join(
            [
                'comori2' => $this->db_manager->getCanonicName('communes', 'table')
            ], 'ori2.communeId=comori2.communeId',
            [
                'commune_origineR2' => 'nom',
                'lacommune_origineR2' => 'alias',
                'laposte_origineR2' => 'alias_laposte'
            ], $select::JOIN_LEFT);
        if (! is_null($order)) {
            $select->order($order);
        }
        return $select->where($where);
    }
}