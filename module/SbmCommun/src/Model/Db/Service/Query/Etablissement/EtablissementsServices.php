<?php
/**
 * Requêtes concernant la table `etablissements-sservices`
 * (déclarée dans module.config.php sous l'alias 'Sbm\Db\Query\EtablissementsServices')
 *
 * Version pour TRANSDEV ALBERTVILLE
 *
 * @project sbm
 * @package SbmCommun/src/Model/Db/Service/Query/Etablissement
 * @filesource EtablissementsServices.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 28 fév. 2020
 * @version 2020-2.6.0
 */
namespace SbmCommun\Model\Db\Service\Query\Etablissement;

use SbmCommun\Model\Db\Service\Query\AbstractQuery;
use Zend\Db\Sql\Select;

class EtablissementsServices extends AbstractQuery
{

    protected function init()
    {
        $this->select = $this->sql->select(
            [
                'rel' => $this->db_manager->getCanonicName('etablissements-services',
                    'table')
            ])
            ->columns(
            [
                'etablissementId',
                'millesime',
                'ligneId',
                'sens',
                'moment',
                'ordre',
                'stationId'
            ])
            ->join(
            [
                'eta' => $this->db_manager->getCanonicName('etablissements', 'table')
            ], 'rel.etablissementId = eta.etablissementId',
            [
                'etab_nom' => 'nom',
                'etab_alias' => 'alias',
                'etab_aliasCG' => 'aliasCG',
                'etab_adresse1' => 'adresse1',
                'etab_adresse2' => 'adresse2',
                'etab_codePostal' => 'codePostal',
                'etab_communeId' => 'communeId',
                'etab_niveau' => 'niveau',
                'etab_statut' => 'statut',
                'etab_visible' => 'visible',
                'etab_desservie' => 'desservie',
                'etab_regrPeda' => 'regrPeda',
                'etab_rattacheA' => 'rattacheA',
                'etab_telephone' => 'telephone',
                'etab_fax' => 'fax',
                'etab_email' => 'email',
                'etab_directeur' => 'directeur',
                'etab_jOuverture' => 'jOuverture',
                'etab_hMatin' => 'hMatin',
                'etab_hMidi' => 'hMidi',
                'etab_hAMidi' => 'hAMidi',
                'etab_hSoir' => 'hSoir',
                'etab_hGarderieOMatin' => 'hGarderieOMatin',
                'etab_hGarderieFMidi' => 'hGarderieFMidi',
                'etab_hGarderieFSoir' => 'hGarderieFSoir',
                'etab_x' => 'x',
                'etab_y' => 'y'
            ])
            ->join([
            'com1' => $this->db_manager->getCanonicName('communes', 'table')
        ], 'com1.communeId = eta.communeId',
            [
                'etab_commune' => 'nom',
                'etab_lacommune' => 'alias',
                'etab_laposte' => 'alias_laposte'
            ])
            ->join([
            'ser' => $this->db_manager->getCanonicName('services', 'table')
        ],
            'rel.millesime = ser.millesime AND  rel.ligneId = ser.ligneId AND rel.sens = ser.sens AND rel.moment = ser.moment AND rel.ordre = ser.ordre',
            [
                'serv_transporteurId' => 'transporteurId',
                'serv_selection' => 'selection',
                'serv_actif' => 'actif',
                'serv_visible' => 'visible',
                'serv_semaine' => 'semaine',
                'serv_rang' => 'rang',
                'serv_type' => 'type',
                'serv_nbPlaces' => 'nbPlaces',
                'serv_alias' => 'alias',
                'serv_commentaire' => 'commentaire'
            ])
            ->join([
            'lig' => $this->db_manager->getCanonicName('lignes', 'table')
        ], 'lig.millesime = ser.millesime AND lig.ligneId = ser.ligneId',
            [
                'ligne_operateur' => 'operateur',
                'ligne_extremite1' => 'extremite1',
                'ligne_extremite2' => 'extremite2',
                'ligne_via' => 'via',
                'ligne_internes' => 'internes',
                'ligne_actif' => 'actif',
                'ligne_selection' => 'selection',
                'ligne_commentaire' => 'commentaire'
            ])
            ->join([
            'lot' => $this->db_manager->getCanonicName('lots', 'table')
        ], 'lig.lotId = lot.lotId',
            [
                'lotId' => 'lotId',
                'lot_marche' => 'marche',
                'lot_lot' => 'lot',
                'lot_libelle' => 'libelle',
                'lot_transporteurId' => 'transporteurId',
                'lot_dateDebut' => 'dateDebut',
                'lot_dateFin' => 'dateFin',
                'lot_actif' => 'actif',
                'lot_selection' => 'selection'
            ], Select::JOIN_LEFT)
            ->join(
            [
                'tit' => $this->db_manager->getCanonicName('transporteurs', 'table')
            ], 'tit.transporteurId = lot.transporteurId', [
                'lot_titulaire' => 'nom'
            ], Select::JOIN_LEFT)
            ->join(
            [
                'tra' => $this->db_manager->getCanonicName('transporteurs', 'table')
            ], 'tra.transporteurId = ser.transporteurId', [
                'serv_transporteur' => 'nom'
            ])
            ->join([
            'com2' => $this->db_manager->getCanonicName('communes', 'table')
        ], 'com2.communeId = tra.communeId', [
            'serv_communeTransporteur' => 'nom'
        ])
            ->join([
            'sta' => $this->db_manager->getCanonicName('stations', 'table')
        ], 'rel.stationId = sta.stationId',
            [
                'sta_nom' => 'nom',
                'sta_ouverte' => 'ouverte',
                'sta_visible' => 'visible',
                'sta_selection' => 'selection',
                'sta_x' => 'x',
                'sta_y' => 'y'
            ])
            ->join([
            'comsta' => $this->db_manager->getCanonicName('communes', 'table')
        ], 'comsta.communeId = sta.communeId', [
            'sta_commune' => 'nom'
        ])
            ->join([
            'cir' => $this->db_manager->getCanonicName('circuits', 'table')
        ],
            'cir.millesime = rel.millesime AND cir.ligneId = rel.ligneId ' .
            'AND cir.sens = rel.sens AND cir.moment = rel.moment AND cir.ordre = rel.ordre AND cir.stationId = rel.stationId',
            [
                'circuitId',
                'cir_passage' => 'passage',
                'cir_selection' => 'selection',
                'cir_visible' => 'visible',
                'cir_ouvert' => 'ouvert',
                'cir_semaine' => 'semaine',
                'cir_horaireA' => 'horaireA',
                'cir_distance' => 'distance',
                'cir_montee' => 'montee',
                'cir_descente' => 'descente',
                'cir_correspondance' => 'correspondance',
                'cir_emplacement' => 'emplacement',
                'cir_typeArret' => 'typeArret',
                'cir_commentaire1' => 'commentaire1',
                'cir_commentaire2' => 'commentaire2'
            ], Select::JOIN_LEFT)
            ->where([
            'cir.millesime' => $this->millesime
        ]);
    }

    /**
     * Renvoie un paginator
     *
     * @param \Zend\Db\Sql\Where|array $where
     * @param string|array $order
     *
     * @return \Zend\Paginator\Paginator
     */
    public function paginatorES($where, $order = [])
    {
        if ($order) {
            $this->select->order($order);
        }
        return $this->paginator($this->select->where($where));
    }

    public function fetchAll($where, $order = [])
    {
        if ($order) {
            $this->select->order($order);
        }
        return $this->renderResult($this->select->where($where));
    }
}