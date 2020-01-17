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
 * @date 05 jan. 2020
 * @version 2020-2.6.0
 */
namespace SbmCommun\Model\Db\Service\Query\Eleve;

use SbmCommun\Model\Db\Service\Query\AbstractQuery;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;

class AffectationsServicesStations extends AbstractQuery
{

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
                'sens' => 'sens',
                'correspondance' => 'correspondance',
                'selection' => 'selection',
                'responsableId' => 'responsableId',
                'station1Id' => 'station1Id',
                'service1Id' => 'service1Id',
                'station2Id' => 'station2Id',
                'service2Id' => 'service2Id'
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
     * @return \Zend\Db\Adapter\Driver\ResultInterface
     */
    public function getServices($eleveId, $responsableId, $trajet = null)
    {
        $select = clone $this->select;
        $select->order([
            'trajet',
            'jours',
            'sens',
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
    public function getAffectations($eleveId, $trajet = null, $annee_precedente = false)
    {
        $millesime = $this->millesime;
        if ($annee_precedente) {
            $millesime --;
        }
        $select = clone $this->select;
        $select->join(
            [
                'ser1' => $this->db_manager->getCanonicName('services', 'table')
            ], 'aff.service1Id = ser1.serviceId',
            [
                'service1' => 'nom',
                'service1_alias' => 'alias',
                'service1_aliasTr' => 'aliasTr',
                'operateur1' => 'operateur'
            ])
            ->join([
            'lot1' => $this->db_manager->getCanonicName('lots', 'table')
        ], 'ser1.lotId = lot1.lotId',
            [
                'service1_marche' => 'marche',
                'service1_lot' => 'lot'
            ])
            ->join(
            [
                'tit1' => $this->db_manager->getCanonicName('transporteurs', 'table')
            ], 'lot1.transporteurId = tit1.transporteurId',
            [
                'service1_titulaire' => 'nom'
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
        ], $select::JOIN_LEFT)
            ->join([
            'com2' => $this->db_manager->getCanonicName('communes', 'table')
        ], 'sta2.communeId = com2.communeId', [
            'commune2' => 'nom'
        ], $select::JOIN_LEFT)
            ->order([
            'trajet',
            'jours',
            'sens',
            'correspondance'
        ]);
        $where = new Where();
        $where->equalTo('millesime', $millesime)->and->equalTo('eleveId', $eleveId);
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
            ], 'aff.service1Id = ser1.serviceId',
            [
                'service1' => 'nom',
                'service1_alias' => 'alias',
                'service1_aliasTr' => 'aliasTr',
                'operateur1' => 'operateur'
            ])
            ->join([
            'lot1' => $this->db_manager->getCanonicName('lots', 'table')
        ], 'ser1.lotId = lot1.lotId',
            [
                'service1_marche' => 'marche',
                'service1_lot' => 'lot'
            ])
            ->join(
            [
                'tit1' => $this->db_manager->getCanonicName('transporteurs', 'table')
            ], 'lot1.transporteurId = tit1.transporteurId',
            [
                'service1_titulaire' => 'nom'
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
        ], 'ser1.serviceId = cir1.serviceId AND cir1.stationId = sta1.stationId',
            [
                'circuit1Id' => 'circuitId'
            ], $select::JOIN_LEFT)
            ->join([
            'ser2' => $this->db_manager->getCanonicName('services', 'table')
        ], 'aff.service2Id = ser2.serviceId',
            [
                'service2' => 'nom',
                'service2_alias' => 'alias',
                'service2_aliasTr' => 'aliasTr',
                'operateur2' => 'operateur'
            ], $select::JOIN_LEFT)
            ->join([
            'lot2' => $this->db_manager->getCanonicName('lots', 'table')
        ], 'ser2.lotId = lot2.lotId',
            [
                'service2_marche' => 'marche',
                'service2_lot' => 'lot'
            ], $select::JOIN_LEFT)
            ->join(
            [
                'tit2' => $this->db_manager->getCanonicName('transporteurs', 'table')
            ], 'lot2.transporteurId = tit2.transporteurId',
            [
                'service2_titulaire' => 'nom'
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
        ], 'ser2.serviceId = cir2.serviceId AND cir2.stationId = sta2.stationId',
            [
                'circuit2Id' => 'circuitId'
            ], $select::JOIN_LEFT);
        $where = new Where();
        $where->equalTo('cir1.millesime', $this->millesime)->equalTo('aff.millesime',
            $this->millesime)->and->equalTo('eleveId', $eleveId)
            ->nest()
            ->isNull('cir2.millesime')->or->equalTo('cir2.millesime', $this->millesime)->unnest();
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
    private function selectLocalisation(Where $where, $order = null)
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
            'ser1' => $this->db_manager->getCanonicName('services', 'table')
        ], 'ser1.serviceId=aff.service1Id',
            [
                'service1' => 'serviceId',
                'service1_alias' => 'alias',
                'service1_aliasTr' => 'aliasTr'
            ])
            ->join([
            'lot1' => $this->db_manager->getCanonicName('lots', 'table')
        ], 'ser1.lotId = lot1.lotId',
            [
                'service1_marche' => 'marche',
                'service1_lot' => 'lot'
            ])
            ->join(
            [
                'tit1' => $this->db_manager->getCanonicName('transporteurs', 'table')
            ], 'lot1.transporteurId = tit1.transporteurId',
            [
                'service1_titulaire' => 'nom'
            ])
            ->join(
            [
                'tra1' => $this->db_manager->getCanonicName('transporteurs', 'table')
            ], 'ser1.transporteurId=tra1.transporteurId', [
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
            'ser2' => $this->db_manager->getCanonicName('services', 'table')
        ], 'ser2.serviceId=aff.service2Id',
            [
                'service2' => 'serviceId',
                'service2_alias' => 'alias',
                'service2_aliasTr' => 'aliasTr'
            ], $select::JOIN_LEFT)
            ->join([
            'lot2' => $this->db_manager->getCanonicName('lots', 'table')
        ], 'ser2.lotId = lot2.lotId',
            [
                'service2_marche' => 'marche',
                'service2_lot' => 'lot'
            ], $select::JOIN_LEFT)
            ->join(
            [
                'tit2' => $this->db_manager->getCanonicName('transporteurs', 'table')
            ], 'lot2.transporteurId = tit2.transporteurId',
            [
                'service2_titulaire' => 'nom'
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
    private function selectScolaritesR($where, $order = null)
    {
        $select = clone $this->select;
        $select->join([
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
        ], 'res.communeId = com.communeId', [
            'commune' => 'nom',
            'lacommune' => 'alias',
            'laposte' => 'alias_laposte'
        ])
            ->join([
            'sco' => $this->db_manager->getCanonicName('scolarites', 'table')
        ], 'sco.eleveId=aff.eleveId AND sco.millesime=aff.millesime',
            [
                'inscrit',
                'paiement',
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
        ], 'eta.communeId = etacom.communeId', [
            'communeEtablissement' => 'nom'
        ])
            ->join([
            'cla' => $this->db_manager->getCanonicName('classes', 'table')
        ], 'sco.classeId=cla.classeId', [
            'classe' => 'nom'
        ])
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
            'ser1' => $this->db_manager->getCanonicName('services', 'table')
        ], 'ser1.serviceId=aff.service1Id',
            [
                'service1' => 'nom',
                'service1_alias' => 'alias',
                'service1_aliasTr' => 'aliasTr'
            ])
            ->join(
            [
                'tra1' => $this->db_manager->getCanonicName('transporteurs', 'table')
            ], 'ser1.transporteurId=tra1.transporteurId', [
                'transporteur1' => 'nom'
            ])
            ->join([
            'ser2' => $this->db_manager->getCanonicName('services', 'table')
        ], 'ser2.serviceId=aff.service2Id',
            [
                'service2' => 'nom',
                'service2_alias' => 'alias',
                'service2_aliasTr' => 'aliasTr'
            ], $select::JOIN_LEFT)
            ->join([
            'lot2' => $this->db_manager->getCanonicName('lots', 'table')
        ], 'ser2.lotId = lot2.lotId',
            [
                'service2_marche' => 'marche',
                'service2_lot' => 'lot'
            ], $select::JOIN_LEFT)
            ->join(
            [
                'tit2' => $this->db_manager->getCanonicName('transporteurs', 'table')
            ], 'lot2.transporteurId = tit2.transporteurId',
            [
                'service2_titulaire' => 'nom'
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

    private function selectTelephonesPortables(Where $where)
    {
        $selectBase = clone $this->select;
        $selectBase->join(
            [
                'ser1' => $this->db_manager->getCanonicName('services', 'table')
            ], 'ser1.serviceId = aff.service1Id', [])
            ->join([
            'ser2' => $this->db_manager->getCanonicName('services', 'table')
        ], 'ser2.serviceId = aff.service2Id', [], Select::JOIN_LEFT)
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
            ->columns(
            [
                'responsable',
                'telephone' => 'telephoneF',
                'eleve' => 'eleve',
                'service1' => 'service1Id',
                'service2' => 'service2Id',
                'etablissement' => 'etablissement'
            ])
            ->where($whereF);
        // dans le champ des téléphones portables
        $whereP = new Where();
        $whereP->like('telephoneP', '06%')->or->like('telephoneP', '07%');
        $selectP = $this->sql->select();
        $selectP->from([
            'telP' => $selectBase
        ])
            ->columns(
            [
                'responsable',
                'telephone' => 'telephoneP',
                'eleve' => 'eleve',
                'service1' => 'service1Id',
                'service2' => 'service2Id',
                'etablissement' => 'etablissement'
            ])
            ->where($whereP);
        // dans le champ des téléphones du travail
        $whereT = new Where();
        $whereT->like('telephoneT', '06%')->or->like('telephoneT', '07%');
        $selectT = $this->sql->select();
        $selectT->from([
            'telT' => $selectBase
        ])
            ->columns(
            [
                'responsable',
                'telephone' => 'telephoneT',
                'eleve' => 'eleve',
                'service1' => 'service1Id',
                'service2' => 'service2Id',
                'etablissement' => 'etablissement'
            ])
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
}