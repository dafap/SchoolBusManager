<?php
/**
 * Diverses requêtes et paginateurs associés
 *
 * Liste des requêtes présentes :
 * getScolaritesR et paginatorScolaritesR : renvoie une requête lourde composée de 35 tables jointes
 *
 * @project sbm
 * @package SbmCommun/Model/Db/Service/Query/Eleve
 * @filesource Divers.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 4 mars 2020
 * @version 2020-2.6.0
 */
namespace SbmCommun\Model\Db\Service\Query\Eleve;

use SbmCommun\Model\Db\Service\Query\AbstractQuery;
use SbmCommun\Model\Traits\ExpressionSqlTrait;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Select;

class Divers extends AbstractQuery
{
    use ExpressionSqlTrait;

    protected function init()
    {
        ;
    }

    /**
     * Pour le portail de l'organisateur (secretariat)
     *
     * @param \Zend\Db\Sql\Where|\Closure|string|array|\Zend\Db\Sql\Predicate\PredicateInterface $where
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
            ->columns(
            [
                'millesime',
                'eleveid',
                'inscrit',
                'fa',
                'paiement',
                'gratuit',
                'dateCarte',
                'service1R1' => new Expression(
                    $this->getSqlSemaineLigneHoraireSens('ser1r1.semaine',
                        'ser1r1.ligneId', 'cir1r1.horaireA', 'ser1r1.sens')),
                'service2R1' => new Expression(
                    $this->getSqlSemaineLigneHoraireSens('ser1r1.semaine',
                        'ser2r1.ligneId', 'cir2r1.horaireA', 'ser2r1.sens')),
                'service1R2' => new Expression(
                    $this->getSqlSemaineLigneHoraireSens('ser1r2.semaine',
                        'ser1r2.ligneId', 'cir1r2.horaireA', 'ser1r2.sens')),
                'service2R2' => new Expression(
                    $this->getSqlSemaineLigneHoraireSens('ser1r2.semaine',
                        'ser2r2.ligneId', 'cir2r2.horaireA', 'ser2r2.sens'))
            ])
            ->join(
            [
                'eta' => $this->db_manager->getCanonicName('etablissements', 'table')
            ], 'eta.etablissementId = sco.etablissementId',
            [
                'etablissement' => new Expression(
                    '(CASE WHEN isnull(eta.alias) OR eta.alias = "" THEN eta.nom ELSE eta.alias END)')
            ])
            ->join([
            'etacom' => $this->db_manager->getCanonicName('communes', 'table')
        ], 'eta.communeId = etacom.communeId', $this->columnsCommune('Etablissement'))
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
            $this->columnsResponsable(1), Select::JOIN_LEFT)
            ->join([
            'comr1' => $this->db_manager->getCanonicName('communes', 'table')
        ], 'res1.communeId = comr1.communeId', $this->columnsCommune('R1'),
            Select::JOIN_LEFT)
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
            ->join(
            [
                'sta1r1com' => $this->db_manager->getCanonicName('communes', 'table')
            ], 'sta1r1.communeId=sta1r1com.communeId', $this->columnsCommune('Station1r1'),
            Select::JOIN_LEFT)
            ->join([
            'sta2r1' => $this->db_manager->getCanonicName('stations', 'table')
        ], 'affr1.station2Id = sta2r1.stationId',
            [
                'station2r1' => 'nom',
                'station2IdR1' => 'stationId'
            ], Select::JOIN_LEFT)
            ->join(
            [
                'sta2r1com' => $this->db_manager->getCanonicName('communes', 'table')
            ], 'sta2r1.communeId=sta2r1com.communeId', $this->columnsCommune('Station2r1'),
            Select::JOIN_LEFT)
            ->join([
            'cir1r1' => $this->db_manager->getCanonicName('circuits', 'table')
        ], $this->jointureAffectationsCircuits('cir1r1', 'affr1', 1, 1), [],
            Select::JOIN_LEFT)
            ->join([
            'cir2r1' => $this->db_manager->getCanonicName('circuits', 'table')
        ], $this->jointureAffectationsCircuits('cir2r1', 'affr1', 2, 2), [],
            Select::JOIN_LEFT)
            ->join([
            'ser1r1' => $this->db_manager->getCanonicName('services', 'table')
        ], $this->jointureAffectationsServices('ser1r1', 'affr1', 1),
            $this->columnsService('service1r1'), Select::JOIN_LEFT)
            ->join([
            'lig1r1' => $this->db_manager->getCanonicName('lignes', 'table')
        ], $this->jointureServicesLignes('ser1r1', 'lig1r1'),
            $this->columnsLigne('ligne1r1'), Select::JOIN_LEFT)
            ->join([
            'lot1r1' => $this->db_manager->getCanonicName('lots', 'table')
        ], 'lig1r1.lotId = lot1r1.lotId',
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
        ], $this->jointureAffectationsServices('ser2r1', 'affr1', 2),
            $this->columnsService('service2r1'), Select::JOIN_LEFT)
            ->join([
            'lig2r1' => $this->db_manager->getCanonicName('lignes', 'table')
        ], $this->jointureServicesLignes('ser2r1', 'lig2r1'),
            $this->columnsLigne('ligne2r1'), Select::JOIN_LEFT)
            ->join([
            'lot2r1' => $this->db_manager->getCanonicName('lots', 'table')
        ], 'lig2r1.lotId = lot2r1.lotId',
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
            $this->columnsResponsable(2), Select::JOIN_LEFT)
            ->join([
            'comr2' => $this->db_manager->getCanonicName('communes', 'table')
        ], 'res2.communeId = comr2.communeId', $this->columnsCommune('R2'),
            Select::JOIN_LEFT)
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
            'cir1r2' => $this->db_manager->getCanonicName('circuits', 'table')
        ], $this->jointureAffectationsCircuits('cir1r2', 'affr2', 1, 1), [],
            Select::JOIN_LEFT)
            ->join([
            'cir2r2' => $this->db_manager->getCanonicName('circuits', 'table')
        ], $this->jointureAffectationsCircuits('cir2r2', 'affr2', 2, 2), [],
            Select::JOIN_LEFT)
            ->join(
            [
                'sta1r2com' => $this->db_manager->getCanonicName('communes', 'table')
            ], 'sta1r2.communeId=sta1r2com.communeId', $this->columnsCommune('Station1r2'),
            Select::JOIN_LEFT)
            ->join([
            'sta2r2' => $this->db_manager->getCanonicName('stations', 'table')
        ], 'affr2.station2Id = sta2r2.stationId',
            [
                'station2r2' => 'nom',
                'station2IdR2' => 'stationId'
            ], Select::JOIN_LEFT)
            ->join(
            [
                'sta2r2com' => $this->db_manager->getCanonicName('communes', 'table')
            ], 'sta2r2.communeId=sta2r2com.communeId', $this->columnsCommune('Station2r2'),
            Select::JOIN_LEFT)
            ->join([
            'ser1r2' => $this->db_manager->getCanonicName('services', 'table')
        ], $this->jointureAffectationsServices('ser1r2', 'affr2', 1),
            $this->columnsService('service1r2'), Select::JOIN_LEFT)
            ->join([
            'lig1r2' => $this->db_manager->getCanonicName('lignes', 'table')
        ], $this->jointureServicesLignes('ser1r2', 'lig1r2'),
            $this->columnsLigne('ligne1r2'), Select::JOIN_LEFT)
            ->join([
            'lot1r2' => $this->db_manager->getCanonicName('lots', 'table')
        ], 'lig1r2.lotId = lot1r2.lotId',
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
        ], $this->jointureAffectationsServices('ser2r2', 'affr2', 2),
            $this->columnsService('service2r2'), Select::JOIN_LEFT)
            ->join([
            'lig2r2' => $this->db_manager->getCanonicName('lignes', 'table')
        ], $this->jointureServicesLignes('ser2r2', 'lig2r2'),
            $this->columnsLigne('ligne2r2'), Select::JOIN_LEFT)
            ->join([
            'lot2r2' => $this->db_manager->getCanonicName('lots', 'table')
        ], 'lig2r2.lotId = lot2r2.lotId',
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

    /**
     *
     * @param string $cir
     *            alias de la table circuits
     * @param string $aff
     * @param int $numeroService
     *            alias de la table affectations
     * @param int $numeroStation
     * @return string
     */
    private function jointureAffectationsCircuits(string $cir, string $aff,
        int $numeroService, int $numeroStation)
    {
        return sprintf(
            implode(' AND ',
                [
                    '%2$s.millesime = %1$s.millesime',
                    '%2$s.ligne%3$dId = %1$s.ligneId',
                    '%2$s.sensligne%3$d = %1$s.sens',
                    '%2$s.moment = %1$s.moment',
                    '%2$s.ordreligne%3$d = %1$s.ordre',
                    '%2$s.station%4$dId = %1$s.stationId'
                ]), $cir, $aff, $numeroService, $numeroStation);
    }

    /**
     *
     * @param string $ser
     *            alias de la table services
     * @param string $aff
     *            alias de la table affectations
     * @param int $numeroService
     * @return string
     */
    private function jointureAffectationsServices(string $ser, string $aff,
        int $numeroService)
    {
        return sprintf(
            implode(' AND ',
                [
                    '%2$s.millesime = %1$s.millesime',
                    '%2$s.ligne%3$dId = %1$s.ligneId',
                    '%2$s.sensligne%3$d = %1$s.sens',
                    '%2$s.moment = %1$s.moment',
                    '%2$s.ordreligne%3$d = %1$s.ordre'
                ]), $ser, $aff, $numeroService);
    }

    /**
     *
     * @param string $ser
     * @param string $ligne
     * @return string
     */
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
     * Renvoie les colonnes de la table services préfixées par prefixe_
     *
     * @param string $prefice
     * @return string[]
     */
    private function columnsService(string $prefice)
    {
        return [
            $prefice . '_semaine' => 'semaine',
            $prefice . '_nbPlaces' => 'nbPlaces',
            $prefice . '_alias' => 'alias',
            $prefice . '_commentaire' => 'commentaire'
        ];
    }

    /**
     * Renvoie les colonnes de la table lignes préfixées par prefixe_
     *
     * @param string $prefixe
     * @return string[]
     */
    private function columnsLigne(string $prefixe)
    {
        return [
            $prefixe . '_operateur' => 'operateur',
            $prefixe . '_internes' => 'internes',
            $prefixe . '_extremite1' => 'extremite1',
            $prefixe . '_extremite2' => 'extremite2',
            $prefixe . '_via' => 'via',
            $prefixe . '_commentaire' => 'commentaire'
        ];
    }

    private function columnsResponsable(int $numero)
    {
        return [
            'responsable' . $numero => new Expression(
                sprintf(
                    '(CASE WHEN isnull(res%1$d.responsableId) THEN NULL ELSE concat(res%1$d.nomSA," ",res%1$d.prenomSA) END)',
                    $numero)),
            'adresseR' . $numero . 'L1' => 'adresseL1',
            'adresseR' . $numero . 'L2' => 'adresseL2',
            'adresseR' . $numero . 'L3' => 'adresseL3',
            'telephoneFR' . $numero => 'telephoneF',
            'telephonePR' . $numero => 'telephoneP',
            'telephoneTR' . $numero => 'telephoneT',
            'emailR' . $numero => 'email'
        ];
    }

    private function columnsCommune(string $suffixe)
    {
        return [
            'commune' . $suffixe => 'nom',
            'lacommune' . $suffixe => 'alias',
            'laposte' . $suffixe => 'alias_laposte'
        ];
    }
}