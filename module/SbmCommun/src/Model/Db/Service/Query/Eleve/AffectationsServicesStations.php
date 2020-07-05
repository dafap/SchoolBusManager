<?php
/**
 * Requêtes donnant l'affectation ou la pré-affectation d'un élève
 *
 *
 * @project sbm
 * @package package_name
 * @filesource AffectationsServicesStations.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 5 juil. 2020
 * @version 2020-2.6.0
 */
namespace SbmCommun\Model\Db\Service\Query\Eleve;

use SbmCommun\Model\Db\Service\Query\AbstractQuery;
use SbmCommun\Model\Traits\ExpressionSqlTrait;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;

class AffectationsServicesStations extends AbstractQuery
{
    use ExpressionSqlTrait;

    protected function init()
    {
        $this->select = $this->sql->select()
            ->from(
            [
                'aff' => $this->db_manager->getCanonicName('affectations', 'table')
            ])
            ->columns(
            [
                'millesime' => 'millesime',
                'eleveId' => 'eleveId',
                'trajet' => 'trajet',
                'jours' => 'jours',
                'moment' => 'moment',
                'correspondance' => 'correspondance',
                'selection' => 'selection',
                'responsableId' => 'responsableId',
                'station1Id' => 'station1Id',
                'ligne1Id' => 'ligne1Id',
                'sensligne1' => 'sensligne1',
                'ordreligne1' => 'ordreligne1',
                // 'service1Id' => 'service1Id',
                'station2Id' => 'station2Id',
                // 'service2Id' => 'service2Id',
                'ligne2Id' => 'ligne2Id',
                'sensligne2' => 'sensligne2',
                'ordreligne2' => 'ordreligne2'
            ]);
    }

    /**
     * Renvoie le ou les codes des services affectés à l'élève pour le domicile de ce
     * responsable
     *
     * @param int $eleveId
     * @param int $responsableId
     * @param int $trajet
     *            1 ou 2 selon que c'est le responsable n°1 ou n°2
     * @param int $moment
     *            1 Matin, 2 Midi, 3 Soir
     * @return \Zend\Db\Adapter\Driver\ResultInterface
     */
    public function getServices(int $eleveId, int $responsableId, int $trajet = null,
        int $moment = null)
    {
        $select = clone $this->select;
        $select->order([
            'trajet',
            'jours',
            'moment',
            'correspondance'
        ]);
        $where = new Where();
        $where->equalTo('millesime', $this->millesime)
            ->equalTo('eleveId', $eleveId)
            ->equalTo('responsableId', $responsableId);
        if (isset($trajet)) {
            $where->equalTo('trajet', $trajet);
        }
        return $this->renderResult($select->where($where));
    }

    /**
     * Renvoie les affectations de l'année courante ou de l'année précédente
     *
     * @param int $eleveId
     * @param int $trajet
     *            1 ou 2 selon que c'est le responsable n°1 ou n°2
     * @param boolean $annee_precedente
     *
     * @return \Zend\Db\Adapter\Driver\ResultInterface
     */
    public function getAffectations(int $eleveId, int $trajet = null,
        bool $annee_precedente = false)
    {
        $millesime = $this->millesime;
        if ($annee_precedente) {
            $millesime --;
        }
        $select = clone $this->select;
        $select->join(
            [
                'cir1' => $this->db_manager->getCanonicName('circuits', 'table')
            ], $this->jointureAffectationsCircuits(1, 'cir1'),
            [
                'horaire1' => 'horaireA'
            ])
            ->join([
            'ser1' => $this->db_manager->getCanonicName('services', 'table')
        ], $this->jointureAffectationsServices(1, 'ser1'),
            [
                'service1_nbPlaces' => 'nbPlaces',
                'service1_alias' => 'alias',
                'semaine' => 'semaine'
            ])
            ->join([
            'lign1' => $this->db_manager->getCanonicName('lignes', 'table')
        ], $this->jointureServicesLignes('ser1', 'lign1'),
            [
                'ligne1_operateur' => 'operateur',
                'ligne1_internes' => 'internes'
            ])
            ->join(
            [
                'tra1' => $this->db_manager->getCanonicName('transporteurs', 'table')
            ], 'ser1.transporteurId = tra1.transporteurId', [
                'transporteur1' => 'nom'
            ])
            ->join([
            'sta1' => $this->db_manager->getCanonicName('stations', 'table')
        ], 'aff.station1Id = sta1.stationId', [
            'station1' => 'nom'
        ])
            ->join([
            'com1' => $this->db_manager->getCanonicName('communes', 'table')
        ], 'sta1.communeId = com1.communeId', [
            'commune1' => 'nom'
        ])
            ->join([
            'sta2' => $this->db_manager->getCanonicName('stations', 'table')
        ], 'aff.station2Id = sta2.stationId', [
            'station2' => 'nom'
        ], Select::JOIN_LEFT)
            ->join([
            'com2' => $this->db_manager->getCanonicName('communes', 'table')
        ], 'sta2.communeId = com2.communeId', [
            'commune2' => 'nom'
        ], Select::JOIN_LEFT)
            ->join([
            'cir2' => $this->db_manager->getCanonicName('circuits', 'table')
        ], $this->jointureAffectationsCircuits(1, 'cir2', 2), [
            'horaire2' => 'horaireA'
        ])
            ->join([
            'lot1' => $this->db_manager->getCanonicName('lots', 'table')
        ], 'lign1.lotId = lot1.lotId', [
            'lot1_marche' => 'marche',
            'lot1_lot' => 'lot'
        ], Select::JOIN_LEFT)
            ->join(
            [
                'tit1' => $this->db_manager->getCanonicName('transporteurs', 'table')
            ], 'lot1.transporteurId = tit1.transporteurId', [
                'lot1_titulaire' => 'nom'
            ], Select::JOIN_LEFT)
            ->order(
            [
                'trajet',
                'moment',
                'cir1.horaireA',
                // semaine (de Services), remplace jours (Affectations) non traité
                'semaine DESC'
            ]);
        $where = new Where();
        $where->equalTo('aff.millesime', $millesime)->and->equalTo('aff.eleveId', $eleveId);
        if (isset($trajet)) {
            $where->equalTo('trajet', $trajet);
        }
        return $this->renderResult($select->where($where));
    }

    /**
     * Renvoie les correspondances de l'année courante
     *
     * @param int $eleveId
     *
     * @return \Zend\Db\Adapter\Driver\ResultInterface
     */
    public function getCorrespondances($eleveId)
    {
        $select = clone $this->select;
        $select->join(
            [
                'ser1' => $this->db_manager->getCanonicName('services', 'table')
            ], $this->jointureAffectationsServices(1, 'ser1'),
            [
                'service1Id' => new Expression($this->getSqlEncodeServiceId('ser1')),
                'service1_nbPlaces' => 'nbPlaces',
                'service1_alias' => 'alias',
                'semaine' => 'semaine'
            ])
            ->join([
            'lign1' => $this->db_manager->getCanonicName('lignes', 'table')
        ], $this->jointureServicesLignes('ser1', 'lign1'),
            [
                'ligne1_operateur' => 'operateur',
                'ligne1_internes' => 'internes'
            ])
            ->join(
            [
                'tra1' => $this->db_manager->getCanonicName('transporteurs', 'table')
            ], 'ser1.transporteurId = tra1.transporteurId', [
                'transporteur1' => 'nom'
            ])
            ->join([
            'sta1' => $this->db_manager->getCanonicName('stations', 'table')
        ], 'aff.station1Id = sta1.stationId', [
            'station1' => 'nom'
        ])
            ->join([
            'com1' => $this->db_manager->getCanonicName('communes', 'table')
        ], 'sta1.communeId = com1.communeId', [
            'commune1' => 'nom'
        ])
            ->join([
            'cir1' => $this->db_manager->getCanonicName('circuits', 'table')
        ], $this->jointureAffectationsCircuits(1, 'cir1'),
            [
                'horaire' => 'horaireA',
                'circuit1Id' => 'circuitId',
                'service1' => new Expression(
                    $this->getSqlSemaineLigneHoraireSens('semaine', 'ligneId', 'horaireA',
                        'sens', 'cir1'))
            ], $select::JOIN_LEFT)
            ->join([
            'lot1' => $this->db_manager->getCanonicName('lots', 'table')
        ], 'lign1.lotId = lot1.lotId', [
            'lot1_marche' => 'marche',
            'lot1_lot' => 'lot'
        ], $select::JOIN_LEFT)
            ->join(
            [
                'tit1' => $this->db_manager->getCanonicName('transporteurs', 'table')
            ], 'lot1.transporteurId = tit1.transporteurId', [
                'lot1_titulaire' => 'nom'
            ], $select::JOIN_LEFT)
            ->join([
            'ser2' => $this->db_manager->getCanonicName('services', 'table')
        ], $this->jointureAffectationsServices(2, 'ser2'),
            [
                'service2Id' => new Expression($this->getSqlEncodeServiceId('ser2')),
                'service2_nbPlaces' => 'nbPlaces',
                'service2_alias' => 'alias',
                'service2_semaine' => 'semaine'
            ], $select::JOIN_LEFT)
            ->join([
            'lign2' => $this->db_manager->getCanonicName('lignes', 'table')
        ], $this->jointureServicesLignes('ser2', 'lign2'),
            [
                'ligne2_operateur' => 'operateur',
                'ligne2_internes' => 'internes'
            ], $select::JOIN_LEFT)
            ->join([
            'lot2' => $this->db_manager->getCanonicName('lots', 'table')
        ], 'lign2.lotId = lot2.lotId', [
            'lot2_marche' => 'marche',
            'lot2_lot' => 'lot'
        ], $select::JOIN_LEFT)
            ->join(
            [
                'tit2' => $this->db_manager->getCanonicName('transporteurs', 'table')
            ], 'lot2.transporteurId = tit2.transporteurId', [
                'lot2_titulaire' => 'nom'
            ], $select::JOIN_LEFT)
            ->join(
            [
                'tra2' => $this->db_manager->getCanonicName('transporteurs', 'table')
            ], 'ser2.transporteurId = tra2.transporteurId', [
                'transporteur2' => 'nom'
            ], $select::JOIN_LEFT)
            ->join([
            'sta2' => $this->db_manager->getCanonicName('stations', 'table')
        ], 'aff.station2Id = sta2.stationId', [
            'station2' => 'nom'
        ], $select::JOIN_LEFT)
            ->join([
            'com2' => $this->db_manager->getCanonicName('communes', 'table')
        ], 'sta2.communeId = com2.communeId', [
            'commune2' => 'nom'
        ], $select::JOIN_LEFT)
            ->join([
            'cir2' => $this->db_manager->getCanonicName('circuits', 'table')
        ], $this->jointureAffectationsCircuits(2, 'cir2'),
            [
                'horaire2' => 'horaireA',
                'circuit2Id' => 'circuitId',
                'service2' => new Expression(
                    $this->getSqlSemaineLigneHoraireSens('semaine', 'ligneId', 'horaireA',
                        'sens', 'cir2'))
            ], $select::JOIN_LEFT);
        $where = new Where();
        $where->equalTo('aff.millesime', $this->millesime)->equalTo('aff.millesime',
            $this->millesime)->and->equalTo('aff.eleveId', $eleveId);
        // die($this->getSqlString($select->where($where)));
        return $this->renderResult($select->where($where));
    }

    /**
     * Renvoie les localisations pour l'année courante
     *
     * @param Where $where
     * @param string|array $order
     *
     * @return \Zend\Db\Adapter\Driver\ResultInterface
     */
    public function getLocalisation(Where $where, $order = null)
    {
        return $this->renderResult($this->selectLocalisation($where, $order));
    }

    /**
     * Construit la requête SELECT pour la méthode précédente
     *
     * @param Where $where
     * @param string|array $order
     *
     * @return \Zend\Db\Sql\Select
     */
    protected function selectLocalisation(Where $where, $order = null)
    {
        $where->equalTo('aff.millesime', $this->millesime);
        $select = clone $this->select;
        ;
        $select->columns(
            [
                'millesime' => 'millesime',
                'trajet' => 'trajet',
                'X' => new Expression('IF(sco.x = 0 AND sco.y = 0, res.x, sco.x)'),
                'Y' => new Expression('IF(sco.x = 0 AND sco.y = 0, res.y, sco.y)')
            ])
            ->join([
            'ele' => $this->db_manager->getCanonicName('eleves', 'table')
        ], 'ele.eleveId=aff.eleveId',
            [
                'id_tra',
                'numero',
                'nom_eleve' => 'nomSA',
                'prenom_eleve' => 'prenomSA',
                'dateN',
                'sexe'
            ])
            ->join([
            'sco' => $this->db_manager->getCanonicName('scolarites', 'table')
        ], 'aff.millesime = sco.millesime AND aff.eleveId = sco.eleveId',
            [
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
        ], 'sco.communeId=comsco.communeId', [
            'commune_chez' => 'nom'
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
        ], 'cometa.communeId=eta.communeId', [
            'commune_etablissement' => 'nom'
        ])
            ->join([
            'cla' => $this->db_manager->getCanonicName('classes', 'table')
        ], 'sco.classeId=cla.classeId', [
            'classe' => 'nom'
        ])
            ->join(
            [
                'res' => $this->db_manager->getCanonicName('responsables', 'table')
            ], 'res.responsableId=aff.responsableId',
            [
                'responsable' => new Expression('concat(res.nom," ",res.prenom)'),
                'x_responsable' => 'x',
                'y_responsable' => 'y',
                'telephoneF_responsable' => 'telephoneF',
                'telephoneP_responsable' => 'telephoneP',
                'telephoneT_responsable' => 'telephoneT',
                'email_responsable' => 'email',
                'adresseL1_responsable' => 'adresseL1',
                'adresseL2_responsable' => 'adresseL2',
                'adresseL3_responsable' => 'adresseL3',
                'codePostal_responsable' => 'codePostal'
            ])
            ->join([
            'comres' => $this->db_manager->getCanonicName('communes', 'table')
        ], 'comres.communeId=res.communeId', [
            'commune_responsable' => 'nom'
        ])
            ->join([
            'sta1' => $this->db_manager->getCanonicName('stations', 'table')
        ], 'aff.station1Id = sta1.stationId', [
            'station1' => 'nom'
        ])
            ->join([
            'com1' => $this->db_manager->getCanonicName('communes', 'table')
        ], 'sta1.communeId = com1.communeId', [
            'commune1' => 'nom'
        ])
            ->join([
            'ser1' => $this->db_manager->getCanonicName('services', 'table')
        ], $this->jointureAffectationsServices(1, 'ser1'),
            [
                'semaine' => 'semaine',
                'service1_alias' => 'alias',
                'service1_nbPlaces' => 'nbPlaces'
            ])
            ->join(
            [
                'tra1' => $this->db_manager->getCanonicName('transporteurs', 'table')
            ], 'ser1.transporteurId=tra1.transporteurId', [
                'transporteur1' => 'nom'
            ])
            ->join([
            'lign1' => $this->db_manager->getCanonicName('lignes', 'table')
        ], $this->jointureServicesLignes('ser1', 'lign1'),
            [
                'ligne1_operateur' => 'operateur',
                'ligne1_internes' => 'internes'
            ])
            ->join([
            'lot1' => $this->db_manager->getCanonicName('lots', 'table')
        ], 'lign1.lotId = lot1.lotId', [
            'lot1_marche' => 'marche',
            'lot1_lot' => 'lot'
        ], Select::JOIN_LEFT)
            ->join(
            [
                'tit1' => $this->db_manager->getCanonicName('transporteurs', 'table')
            ], 'lot1.transporteurId = tit1.transporteurId', [
                'lot1_titulaire' => 'nom'
            ], Select::JOIN_LEFT)
            ->join([
            'ser2' => $this->db_manager->getCanonicName('services', 'table')
        ], $this->jointureAffectationsServices(2, 'ser2'),
            [
                'service2_semaine' => 'semaine',
                'service2_alias' => 'alias',
                'service2_nbPlaces' => 'nbPlaces'
            ], $select::JOIN_LEFT)
            ->join([
            'lign2' => $this->db_manager->getCanonicName('lignes', 'table')
        ], $this->jointureServicesLignes('ser2', 'lign2'),
            [
                'ligne2_operateur' => 'operateur',
                'ligne2_internes' => 'internes'
            ])
            ->join([
            'lot2' => $this->db_manager->getCanonicName('lots', 'table')
        ], 'lign2.lotId = lot2.lotId', [
            'lot2_marche' => 'marche',
            'lot2_lot' => 'lot'
        ], $select::JOIN_LEFT)
            ->join(
            [
                'tit2' => $this->db_manager->getCanonicName('transporteurs', 'table')
            ], 'lot2.transporteurId = tit2.transporteurId', [
                'lot2_titulaire' => 'nom'
            ], $select::JOIN_LEFT)
            ->join(
            [
                'tra2' => $this->db_manager->getCanonicName('transporteurs', 'table')
            ], 'ser2.transporteurId=tra2.transporteurId', [
                'transporteur2' => 'nom'
            ], $select::JOIN_LEFT)
            ->join([
            'sta2' => $this->db_manager->getCanonicName('stations', 'table')
        ], 'aff.station2Id = sta2.stationId', [
            'station2' => 'nom'
        ], $select::JOIN_LEFT)
            ->join([
            'com2' => $this->db_manager->getCanonicName('communes', 'table')
        ], 'sta2.communeId = com2.communeId', [
            'commune2' => 'nom'
        ], $select::JOIN_LEFT);
        if (! is_null($order)) {
            $select->order($order);
        }
        return $select->where($where);
    }

    /**
     * Requête renvoyant tous les résultats du selectScolaritesR
     *
     * @param Where|\Closure|string|array|\Zend\Db\Sql\Predicate\PredicateInterface $where
     * @param string|array $order
     * @param int $millesime
     *            inutilisé mais gardé pour la compatibilité des appels
     * @return \Zend\Db\Adapter\Driver\ResultInterface
     */
    public function getScolaritesR(Where $where, $order = null, $millesime = null)
    {
        return $this->renderResult($this->selectScolaritesR($where, $order));
    }

    /**
     *
     * @param Where|\Closure|string|array|\Zend\Db\Sql\Predicate\PredicateInterface $where
     * @param string|array $order
     * @param int $millesime
     *            inutilisé mais gardé pour la compatibilité des appels
     * @return \Zend\Paginator\Paginator
     */
    public function paginatorScolaritesR($where, $order = null, $millesime = null)
    {
        return $this->paginator($this->selectScolaritesR($where, $order));
    }

    /**
     * Renvoie les scolarités et responsables, avec affectations s'il y en a, pour toutes
     * les années scolaires. Pour travailler sur une année particulière, l'indiquer dans
     * le paramètre $where
     *
     * @param Where|\Closure|string|array|\Zend\Db\Sql\Predicate\PredicateInterface $where
     * @param string|array $order
     *
     * @return \Zend\Db\Sql\Select
     */
    protected function selectScolaritesR($where, $order = null)
    {
        $select = clone $this->select;
        $columns = $select->getRawState(Select::COLUMNS);
        $columns['service1'] = new Expression(
            $this->getSqlSemaineLigneHoraireSens('ser1.semaine', 'aff.ligne1Id',
                'cir1.horaireA', 'aff.sensligne1'));
        // $columns['service2Id'] = new Expression('');
        $select->columns($columns)
            ->join([
            'ele' => $this->db_manager->getCanonicName('eleves', 'table')
        ], 'ele.eleveId=aff.eleveId',
            [
                'numero',
                'nom',
                'nomSA',
                'prenom',
                'prenomSA',
                'dateN',
                'sexe'
            ])
            ->join(
            [
                'res' => $this->db_manager->getCanonicName('responsables', 'table')
            ], 'res.responsableId=aff.responsableId',
            [
                'responsable' => new Expression('concat(res.nom," ",res.prenom)'),
                'adresseL1' => 'adresseL1',
                'adresseL2' => 'adresseL2',
                'adresseL3' => 'adresseL3',
                'telephoneF' => 'telephoneF',
                'telephoneP' => 'telephoneP',
                'telephoneT' => 'telephoneT',
                'email' => 'email'
            ])
            ->join([
            'com' => $this->db_manager->getCanonicName('communes', 'table')
        ], 'res.communeId = com.communeId',
            [
                'commune' => 'nom',
                'lacommune' => 'alias',
                'laposte' => 'alias_laposte'
            ])
            ->join([
            'sco' => $this->db_manager->getCanonicName('scolarites', 'table')
        ], 'sco.eleveId=aff.eleveId AND sco.millesime=aff.millesime',
            [
                'inscrit',
                'paiementR1',
                'paiementR2',
                'fa',
                'regimeId'
            ])
            ->join(
            [
                'eta' => $this->db_manager->getCanonicName('etablissements', 'table')
            ], 'sco.etablissementId=eta.etablissementId',
            [
                'etablissement' => new Expression(
                    'CASE WHEN isnull(eta.alias) OR eta.alias = "" THEN eta.nom ELSE eta.alias END')
            ])
            ->join([
            'etacom' => $this->db_manager->getCanonicName('communes', 'table')
        ], 'eta.communeId = etacom.communeId',
            [
                'communeEtablissement' => 'nom',
                'lacommuneEtablissement' => 'aliasCG',
                'laposteEtablissement' => 'alias_laposte'
            ])
            ->join([
            'cla' => $this->db_manager->getCanonicName('classes', 'table')
        ], 'sco.classeId=cla.classeId', [
            'classe' => 'nom'
        ])
            ->join([
            'ser1' => $this->db_manager->getCanonicName('services', 'table')
        ], $this->jointureAffectationsServices(1, 'ser1'),
            [
                'semaine' => 'semaine',
                'service1_alias' => 'alias',
                'service1_nbPlaces' => 'nbPlaces'
            ])
            ->join(
            [
                'tra1' => $this->db_manager->getCanonicName('transporteurs', 'table')
            ], 'ser1.transporteurId=tra1.transporteurId', [
                'transporteur1' => 'nom'
            ])
            ->join([
            'lign1' => $this->db_manager->getCanonicName('lignes', 'table')
        ], $this->jointureServicesLignes('ser1', 'lign1'),
            [
                'ligne1_operateur' => 'operateur',
                'ligne1_internes' => 'internes'
            ])
            ->join([
            'lot1' => $this->db_manager->getCanonicName('lots', 'table')
        ], 'lign1.lotId = lot1.lotId', [
            'lot1_marche' => 'marche',
            'lot1_lot' => 'lot'
        ], $select::JOIN_LEFT)
            ->join(
            [
                'tit1' => $this->db_manager->getCanonicName('transporteurs', 'table')
            ], 'lot1.transporteurId = tit1.transporteurId', [
                'lot1_titulaire' => 'nom'
            ], $select::JOIN_LEFT)
            ->join([
            'sta1' => $this->db_manager->getCanonicName('stations', 'table')
        ], 'sta1.stationId=aff.station1Id', [
            'station1' => 'nom'
        ], $select::JOIN_LEFT)
            ->join(
            [
                'sta1com' => $this->db_manager->getCanonicName('communes', 'table')
            ], 'sta1.communeId=sta1com.communeId', [
                'communeStation1' => 'nom'
            ], $select::JOIN_LEFT)
            ->join([
            'cir1' => $this->db_manager->getCanonicName('circuits', 'table')
        ], $this->jointureAffectationsCircuits(1, 'cir1'),
            [
                'horaire' => 'horaireA',
                'circuit1Id' => 'circuitId'
            ], $select::JOIN_LEFT)
            ->join([
            'sta2' => $this->db_manager->getCanonicName('stations', 'table')
        ], 'sta2.stationId=aff.station2Id', [
            'station2' => 'nom'
        ], $select::JOIN_LEFT)
            ->join(
            [
                'sta2com' => $this->db_manager->getCanonicName('communes', 'table')
            ], 'sta2.communeId=sta2com.communeId', [
                'communeStation2' => 'nom'
            ], $select::JOIN_LEFT)
            ->join([
            'ser2' => $this->db_manager->getCanonicName('services', 'table')
        ], $this->jointureAffectationsServices(2, 'ser2'),
            [
                'service2_semaine' => 'semaine',
                'service2_alias' => 'alias',
                'service2_nbPlaces' => 'nbPlaces'
            ], $select::JOIN_LEFT)
            ->join([
            'lign2' => $this->db_manager->getCanonicName('lignes', 'table')
        ], $this->jointureServicesLignes('ser2', 'lign2'),
            [
                'ligne2_operateur' => 'operateur',
                'ligne2_internes' => 'internes'
            ], $select::JOIN_LEFT)
            ->join([
            'lot2' => $this->db_manager->getCanonicName('lots', 'table')
        ], 'lign2.lotId = lot2.lotId', [
            'lot2_marche' => 'marche',
            'lot2_lot' => 'lot'
        ], $select::JOIN_LEFT)
            ->join(
            [
                'tit2' => $this->db_manager->getCanonicName('transporteurs', 'table')
            ], 'lot2.transporteurId = tit2.transporteurId', [
                'lot2_titulaire' => 'nom'
            ], $select::JOIN_LEFT)
            ->join(
            [
                'tra2' => $this->db_manager->getCanonicName('transporteurs', 'table')
            ], 'ser2.transporteurId=tra2.transporteurId', [
                'transporteur2' => 'nom'
            ], $select::JOIN_LEFT);
        if (! empty($order)) {
            $select->order($order);
        }
        return $select->where($where);
    }

    /**
     * Requête renvoyant téléphones portables pour les fiches filtrées par $where
     *
     * @param Where|\Closure|string|array|\Zend\Db\Sql\Predicate\PredicateInterface $where
     *
     * @return \Zend\Db\Adapter\Driver\ResultInterface
     */
    public function getTelephonesPortables(Where $where)
    {
        return $this->renderResult($this->selectTelephonesPortables($where));
    }

    /**
     * Paginator sur le même modèle que la requête précédente
     *
     * @param Where|\Closure|string|array|\Zend\Db\Sql\Predicate\PredicateInterface $where
     *
     * @return \Zend\Paginator\Paginator
     */
    public function paginatorTelephonesPortables(Where $where)
    {
        return $this->paginator($this->selectTelephonesPortables($where));
    }

    protected function selectTelephonesPortables(Where $where)
    {
        $selectBase = clone $this->select;
        $selectBase->join(
            [
                'ser1' => $this->db_manager->getCanonicName('services', 'table')
            ], $this->jointureAffectationsServices(1, 'ser1'), [])
            ->join([
            'ser2' => $this->db_manager->getCanonicName('services', 'table')
        ], $this->jointureAffectationsServices(2, 'ser2'), [], Select::JOIN_LEFT)
            ->join([
            'sco' => $this->db_manager->getCanonicName('scolarites', 'table')
        ], 'aff.millesime = sco.millesime AND aff.eleveId = sco.eleveId', [
            'regimeId'
        ])
            ->join(
            [
                'eta' => $this->db_manager->getCanonicName('etablissements', 'table')
            ], 'sco.etablissementId = eta.etablissementId', [
                'etablissement' => 'nom'
            ])
            ->join(
            [
                'res' => $this->db_manager->getCanonicName('responsables', 'table')
            ], 'aff.responsableId = res.responsableId',
            [
                'responsable' => new Expression('concat(res.nomSA, " ", res.prenomSA)'),
                'telephoneF',
                'telephoneP',
                'telephoneT'
            ])
            ->join(
            [ // utile uniquement pour filtrer sur nomSA, prenomSA ou numero
                'ele' => $this->db_manager->getCanonicName('eleves', 'table')
            ], 'ele.eleveId = aff.eleveId',
            [
                'eleve' => new Expression('concat(ele.nomSA, " ", ele.prenomSA)')
            ])
            ->where($where);
        // dans le champ des téléphones fixes
        $whereF = new Where();
        $whereF->like('telephoneF', '06%')->or->like('telephoneF', '07%');
        $selectF = $this->sql->select();
        $selectF->from([
            'telF' => $selectBase
        ])
            ->columns($this->getColumnsForTelephones('telephoneF'))
            ->where($whereF);
        // dans le champ des téléphones portables
        $whereP = new Where();
        $whereP->like('telephoneP', '06%')->or->like('telephoneP', '07%');
        $selectP = $this->sql->select();
        $selectP->from([
            'telP' => $selectBase
        ])
            ->columns($this->getColumnsForTelephones('telephoneP'))
            ->where($whereP);
        // dans le champ des téléphones du travail
        $whereT = new Where();
        $whereT->like('telephoneT', '06%')->or->like('telephoneT', '07%');
        $selectT = $this->sql->select();
        $selectT->from([
            'telT' => $selectBase
        ])
            ->columns($this->getColumnsForTelephones('telephoneT'))
            ->where($whereT);

        $selectT->combine($selectP);

        $selectFPT = $this->sql->select();
        $selectFPT->from([
            'telPT' => $selectT
        ]);
        $selectFPT->combine($selectF);
        $select = $this->sql->select();
        $select->from([
            'telFPT' => $selectFPT
        ])
            ->quantifier(Select::QUANTIFIER_DISTINCT)
            ->order('responsable');
        return $select;
    }

    private function getColumnsForTelephones(string $telephone)
    {
        return [
            'responsable',
            'telephone' => $telephone,
            'eleve' => 'eleve',
            'service1' => new Expression(
                $this->getSqlDesignationService('ligne1Id', 'sensligne1', 'moment',
                    'ordreligne1')),
            'service2' => 'ligne2Id',
            'etablissement' => 'etablissement'
        ];
    }

    private function jointureAffectationsCircuits(int $numeroLigne, string $aliasCir,
        int $numeroStation = 1)
    {
        return sprintf(
            implode(' AND ',
                [
                    'aff.millesime = %2$s.millesime',
                    'aff.ligne%1$dId = %2$s.ligneId',
                    'aff.sensligne%1$d = %2$s.sens',
                    'aff.moment = %2$s.moment',
                    'aff.ordreligne%1$d = %2$s.ordre',
                    'aff.station%3$dId = %2$s.stationId'
                ]), $numeroLigne, $aliasCir, $numeroStation);
    }

    private function jointureAffectationsServices(int $n, string $ser)
    {
        return sprintf(
            implode(' AND ',
                [
                    'aff.millesime = %2$s.millesime',
                    'aff.ligne%1$dId = %2$s.ligneId',
                    'aff.sensligne%1$d = %2$s.sens',
                    'aff.moment = %2$s.moment',
                    'aff.ordreligne%1$d = %2$s.ordre'
                ]), $n, $ser);
    }

    private function jointureServicesLignes(string $ser, string $ligne)
    {
        return sprintf(
            implode(' AND ',
                [
                    '%1$s.millesime = %2$s.millesime',
                    '%1$s.ligneId = %2$s.ligneId'
                ]), $ser, $ligne);
    }

    /**
     * Renvoie les horaires d'un élève : moment, station1, commune1, horaireD, station2,
     * commune2, horaireA
     *
     * @param int $eleveId
     * @return \Zend\Db\ResultSet\HydratingResultSet|\Zend\Db\Adapter\Driver\ResultInterface
     */
    public function getHoraires(int $eleveId)
    {
        return $this->renderResult($this->selectHoraires($eleveId));
    }

    /**
     *
     * @param int $eleveId
     * @return \Zend\Db\Sql\Select
     */
    protected function selectHoraires(int $eleveId)
    {
        $where = new Where();
        $where->equalTo('aff.millesime', $this->millesime)->equalTo('aff.eleveId',
            $eleveId);
        $select = $this->sql->select()
            ->columns([
            'moment'
        ])
            ->from(
            [
                'aff' => $this->db_manager->getCanonicName('affectations', 'table')
            ])
            ->join([
            'sta1' => $this->db_manager->getCanonicName('stations', 'table')
        ], 'sta1.stationId = aff.station1Id', [
            'station1' => 'nom'
        ])
            ->join([
            'com1' => $this->db_manager->getCanonicName('communes', 'table')
        ], 'sta1.communeId = com1.communeId', [
            'commune1' => 'nom'
        ])
            ->join([
            'cir1' => $this->db_manager->getCanonicName('circuits', 'table')
        ], $this->jointureAffectationsCircuits(1, 'cir1', 1), [
            'horaireD'
        ])
            ->join([
            'sta2' => $this->db_manager->getCanonicName('stations', 'table')
        ], 'sta2.stationId = aff.station2Id', [
            'station2' => 'nom'
        ])
            ->join([
            'com2' => $this->db_manager->getCanonicName('communes', 'table')
        ], 'sta2.communeId = com2.communeId', [
            'commune2' => 'nom'
        ])
            ->join([
            'cir2' => $this->db_manager->getCanonicName('circuits', 'table')
        ], $this->jointureAffectationsCircuits(1, 'cir2', 2), [
            'horaireA'
        ])
            ->where($where)
            ->order([
            'aff.moment',
            'cir1.horaireD'
        ]);
        return $select;
    }

    /**
     * Renvoie les lignes d'un élève
     *
     * @param int $eleveId
     * @return \Zend\Db\ResultSet\HydratingResultSet|\Zend\Db\Adapter\Driver\ResultInterface
     */
    public function getLignes(int $eleveId)
    {
        return $this->renderResult($this->selectLignes($eleveId));
    }

    /**
     *
     * @param int $eleveId
     * @return \Zend\Db\Sql\Select
     */
    protected function selectLignes(int $eleveId)
    {
        $where = new Where();
        $where->equalTo('aff.millesime', $this->millesime)->equalTo('aff.eleveId',
            $eleveId);
        $select = $this->sql->select()
            ->columns([
            'moment'
        ])
            ->from(
            [
                'aff' => $this->db_manager->getCanonicName('affectations', 'table')
            ])
            ->join([
            'li1' => $this->db_manager->getCanonicName('lignes', 'table')
        ], 'li1.millesime = aff.millesime AND li1.ligneId = aff.ligne1Id',
            [
                'ligneId',
                'ligneExtremite1' => 'extremite1',
                'ligneExtremite2' => 'extremite2',
                'ligneVia' => 'via'
            ])
            ->where($where)
            ->order([
            'aff.moment'
        ]);
        return $select;
    }

    /**
     * Renvoie les itinéraires d'un élève pour le trajet demandé
     *
     * @param int $eleveId
     * @param int $trajet
     *            1 pour R1 ; 2 pour R2
     * @param int $millesime
     * @return \Zend\Db\Adapter\Driver\ResultInterface|\Zend\Db\ResultSet\HydratingResultSet
     */
    public function getItineraires(int $eleveId, int $trajet = 1, int $millesime = null)
    {
        $where = new Where();
        $where->equalTo('aff.millesime', $millesime ?: $this->millesime)
            ->equalTo('aff.eleveId', $eleveId)
            ->equalTo('aff.trajet', $trajet);
        $select = $this->sql->select()
            ->columns(
            [
                // 'jours', Non traité
                'moment',
                'correspondance',
                'ligne1Id',
                'semaine' => new Expression($this->getSqlSemaine('cir1.semaine & cir2.semaine'))
            ])
            ->from(
            [
                'aff' => $this->db_manager->getCanonicName('affectations', 'table')
            ])
            ->join([
            'sta1' => $this->db_manager->getCanonicName('stations', 'table')
        ], 'aff.station1Id = sta1.stationId', [
            'station1' => 'nom'
        ])
            ->join([
            'com1' => $this->db_manager->getCanonicName('communes', 'table')
        ], 'sta1.communeId = com1.communeId', [
            'commune1' => 'nom'
        ])
            ->join([
            'cir1' => $this->db_manager->getCanonicName('circuits', 'table')
        ], $this->jointureAffectationsCircuits(1, 'cir1'), [
            'horaire1' => new Expression('TIME_FORMAT(cir1.horaireA,"%H:%i")')
        ])
            ->join([
            'sta2' => $this->db_manager->getCanonicName('stations', 'table')
        ], 'aff.station2Id = sta2.stationId', [
            'station2' => 'nom'
        ])
            ->join([
            'com2' => $this->db_manager->getCanonicName('communes', 'table')
        ], 'sta2.communeId = com2.communeId', [
            'commune2' => 'nom'
        ])
            ->join([
            'cir2' => $this->db_manager->getCanonicName('circuits', 'table')
        ], $this->jointureAffectationsCircuits(1, 'cir2', 2), [
            'horaire2' => new Expression('TIME_FORMAT(cir2.horaireA,"%H:%i")')
        ])
            ->where($where)
            ->order(
            [
                'trajet',
                'moment',
                'cir1.horaireA',
                // semaine (de Circuit), remplace jours (Affectations) non traité
                'cir1.semaine & cir2.semaine DESC'
            ]);
        return $this->renderResult($select);
    }
}