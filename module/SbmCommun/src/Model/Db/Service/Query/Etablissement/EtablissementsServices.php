<?php
/**
 * Requêtes concernant la table `etablissements-sservices`
 * (déclarée dans module.config.php sous l'alias 'Sbm\Db\Query\EtablissementsServices')
 *
 * @project sbm
 * @package SbmCommun/src/Model/Db/Service/Query/Etablissement
 * @filesource EtablissementsServices.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 5 août 2021
 * @version 2021-2.5.14
 */
namespace SbmCommun\Model\Db\Service\Query\Etablissement;

use SbmCommun\Model\Db\Service\Query\AbstractQuery;
use Zend\Db\Sql\Literal;
use Zend\Db\Sql\Where;

class EtablissementsServices extends AbstractQuery
{
    use \SbmCommun\Model\Traits\ArrayToWhereTrait;

    protected function init()
    {
        $this->select = $this->sql->select(
            [
                'rel' => $this->db_manager->getCanonicName('etablissements-services',
                    'table')
            ])
            ->columns([
            'etablissementId',
            'serviceId',
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
        ], 'com1.communeId = eta.communeId', [
            'etab_commune' => 'nom'
        ])
            ->join([
            'ser' => $this->db_manager->getCanonicName('services', 'table')
        ], 'rel.serviceId = ser.serviceId',
            [
                'serv_alias' => 'alias',
                'serv_aliasTr' => 'aliasTr',
                'serv_aliasCG' => 'aliasCG',
                'serv_nom' => 'nom',
                'horaire1',
                'horaire2',
                'horaire3',
                'serv_transporteurId' => 'transporteurId',
                'serv_nbPlaces' => 'nbPlaces',
                'serv_surEtatCG' => 'surEtatCG',
                'serv_operateur' => 'operateur',
                'serv_kmAVide' => 'kmAVide',
                'serv_kmEnCharge' => 'kmEnCharge',
                'serv_natureCarte' => 'natureCarte',
                'serv_selection' => 'selection'
            ])
            ->join([
            'lot' => $this->db_manager->getCanonicName('lots', 'table')
        ], 'ser.lotId = lot.lotId',
            [
                'lot_marche' => 'marche',
                'lot_lot' => 'lot',
                'lot_libelle' => 'libelle',
                'lot_transporteurId' => 'transporteurId',
                'lot_dateDebut' => 'dateDebut',
                'lot_dateFin' => 'dateFin',
                'lot_actif' => 'actif',
                'lot_selection' => 'selection'
            ])
            ->join(
            [
                'tit' => $this->db_manager->getCanonicName('transporteurs', 'table')
            ], 'tit.transporteurId = lot.transporteurId', [
                'lot_transporteur' => 'nom'
            ])
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
        ], '(cir.serviceId = rel.serviceId) and (cir.stationId = rel.stationId)',
            [
                'circuitId',
                'cir_selection' => 'selection',
                'cir_millesime' => 'millesime',
                'cir_semaine' => 'semaine',
                'cir_m1' => new Literal('max(`cir`.`m1`)'),
                'cir_s1' => new Literal('min(`cir`.`s1`)'),
                'cir_z1' => new Literal('min(`cir`.`z1`)'),
                'cir_m2' => new Literal('max(`cir`.`m2`)'),
                'cir_s2' => new Literal('min(`cir`.`s2`)'),
                'cir_z2' => new Literal('min(`cir`.`z2`)'),
                'cir_m3' => new Literal('max(`cir`.`m3`)'),
                'cir_s3' => new Literal('min(`cir`.`s3`)'),
                'cir_z3' => new Literal('min(`cir`.`z3`)'),
                'cir_distance' => 'distance',
                'cir_montee' => 'montee',
                'cir_descente' => 'descente',
                'cir_emplacement' => 'emplacement',
                'cir_typeArret' => 'typeArret',
                'cir_commentaire1' => 'commentaire1',
                'cir_commentaire2' => 'commentaire2'
            ])
            ->where([
            'cir.millesime' => $this->millesime
        ])
            ->group([
            'rel.etablissementId',
            'rel.serviceId'
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
        return $this->paginator($this->selectES($where, $order));
    }

    protected function selectES($conditions, $order = [])
    {
        if ($conditions instanceof Where) {
            $where = $conditions;
        } else {
            $where = $this->arrayToWhere(null, $conditions);
        }
        $select = clone $this->select;
        if ($order) {
            $select->order($order);
        }
        return $select->having($where);
    }

    public function fetchAll($where, $order = [])
    {
        if (! $where instanceof Where) {
            $where = $this->arrayToWhere($where);
        }
        $select = clone $this->select;
        if ($order) {
            $select->order($order);
        }
        return $this->renderResult($select->having($where));
    }
}