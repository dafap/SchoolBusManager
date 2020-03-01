<?php
/**
 * Méthodes communes aux classes Emails et Telephones permettant d'obtenir un groupe de responsables
 *
 * @project sbm
 * @package SbmCommun/src/Model/Db/Service/Query
 * @filesource QueryTrait.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 29 fév. 2020
 * @version 2020-2.6.0
 */
namespace SbmCommun\Model\Db\Service\Query\Responsable;

use Zend\Db\Sql\Predicate\Predicate;

trait QueryTrait
{

    /**
     * Prend tous les responsables cochés
     *
     * @return \Zend\Db\ResultSet\HydratingResultSet|\Zend\Db\Adapter\Driver\ResultInterface
     */
    public function getResponsablesSelectionnes()
    {
        $this->select->where('selection = 1');
        return $this->renderResult($this->select);
    }

    /**
     * Ne prend que les responsables qui ont des enfants scolarisés cette année scolaire,
     * non rayés
     *
     * @param string $communeId
     *
     * @return \Zend\Db\ResultSet\HydratingResultSet|\Zend\Db\Adapter\Driver\ResultInterface
     */
    public function getResponsablesCommune($communeId)
    {
        return $this->renderResult(
            $this->joinScolarites()
                ->where(
                [
                    'millesime' => $this->millesime,
                    'inscrit' => 1,
                    'res.communeId' => $communeId
                ]));
    }

    /**
     * Ne prend que les responsables qui ont des enfants scolarisés cette année scolaire,
     * non rayés
     *
     * @param string $etablissementId
     *
     * @return \Zend\Db\ResultSet\HydratingResultSet|\Zend\Db\Adapter\Driver\ResultInterface
     */
    public function getResponsablesEtablissement($etablissementId)
    {
        return $this->renderResult(
            $this->joinScolarites()
                ->where(
                [
                    'millesime' => $this->millesime,
                    'inscrit' => 1,
                    'etablissementId' => $etablissementId
                ]));
    }

    /**
     * Ne prend que les responsables qui ont des enfants scolarisés cette année scolaire,
     * non rayés
     *
     * @param int $classeId
     *
     * @return \Zend\Db\ResultSet\HydratingResultSet|\Zend\Db\Adapter\Driver\ResultInterface
     */
    public function getResponsablesClasse($classeId)
    {
        return $this->renderResult(
            $this->joinScolarites()
                ->where(
                [
                    'millesime' => $this->millesime,
                    'inscrit' => 1,
                    'classeId' => $classeId
                ]));
    }

    /**
     * Ne prend que les responsables qui ont des enfants scolarisés cette année scolaire,
     * non rayés
     *
     * @param int $grilleTarif
     *
     * @return \Zend\Db\ResultSet\HydratingResultSet|\Zend\Db\Adapter\Driver\ResultInterface
     */
    public function getResponsablesGrilleTarif($grilleTarif)
    {
        return $this->renderResult(
            $this->joinScolarites()
                ->where(
                [
                    'millesime' => $this->millesime,
                    'inscrit' => 1,
                    'grilleTarif' => $grilleTarif
                ]));
    }

    /**
     * Ne prend que les responsables qui ont des enfants scolarisés cette année scolaire,
     * non rayés
     *
     * @param int $organismeId
     *
     * @return \Zend\Db\ResultSet\HydratingResultSet|\Zend\Db\Adapter\Driver\ResultInterface
     */
    public function getResponsablesOrganisme($organismeId)
    {
        return $this->renderResult(
            $this->joinScolarites()
                ->where(
                [
                    'millesime' => $this->millesime,
                    'inscrit' => 1,
                    'organismeId' => $organismeId
                ]));
    }

    /**
     * Ne prend que les responsables d'enfants transportés cette année, y compris les
     * rayés
     *
     * @param array $circuitKeys
     *            ['ligneId' => string, 'sens' => int, 'moment' => int, 'ordre' => int,
     *            'stationId' => int]
     * @return \Zend\Db\ResultSet\HydratingResultSet|\Zend\Db\Adapter\Driver\ResultInterface
     */
    public function getResponsablesPointCircuit($circuitKeys)
    {
        $where = new Predicate();
        $where->equalTo('millesime', $this->millesime)
            ->equalTo('moment', $circuitKeys['moment'])
            ->nest()
            ->nest()
            ->equalTo('ligne1Id', $circuitKeys['ligneId'])
            ->equalTo('sensligne1', $circuitKeys['sens'])
            ->equalTo('ordreligne1', $circuitKeys['ordre'])
            ->unnest()->or->nest()
            ->equalTo('ligne2Id', $circuitKeys['ligneId'])
            ->equalTo('sensligne2', $circuitKeys['sens'])
            ->equalTo('ordreligne2', $circuitKeys['ordre'])
            ->unnest()
            ->unnest()
            ->nest()
            ->equalTo('station1Id', $circuitKeys['stationId'])->or->equalTo('station2Id',
            $circuitKeys['stationId'])->unnest();
        return $this->renderResult($this->joinAffectations()
            ->where($where));
    }

    /**
     * Ne prend que les responsables d'enfants transportés cette année, y compris les
     * rayés
     *
     * @param array $serviceKeys
     *            ['ligneId' => string, 'sens' => int, 'moment' => int, 'ordre' => int]
     * @return \Zend\Db\ResultSet\HydratingResultSet|\Zend\Db\Adapter\Driver\ResultInterface
     */
    public function getResponsablesService($serviceKeys)
    {
        $where = new Predicate();
        $where->equalTo('millesime', $this->millesime)
            ->equalTo('moment', $serviceKeys['moment'])
            ->nest()
            ->nest()
            ->equalTo('ligne1Id', $serviceKeys['ligneId'])
            ->equalTo('sensligne1', $serviceKeys['sens'])
            ->equalTo('ordreligne1', $serviceKeys['ordre'])
            ->unnest()->or->nest()
            ->equalTo('ligne2Id', $serviceKeys['ligneId'])
            ->equalTo('sensligne2', $serviceKeys['sens'])
            ->equalTo('ordreligne2', $serviceKeys['ordre'])
            ->unnest()
            ->unnest();
        return $this->renderResult($this->joinAffectations()
            ->where($where));
    }

    /**
     * Ne prend que les responsables d'enfants transportés cette année, y compris les
     * rayés
     *
     * @param string $ligneId
     *
     * @return \Zend\Db\ResultSet\HydratingResultSet|\Zend\Db\Adapter\Driver\ResultInterface
     */
    public function getResponsablesLigne($ligneId)
    {
        return $this->renderResult(
            $this->joinServices()
                ->where([
                'millesime' => $this->millesime,
                'ligneId' => $ligneId
            ]));
    }

    /**
     * Ne prend que les responsables d'enfants transportés cette année, y compris les
     * rayés
     *
     * @param int $stationId
     *
     * @return \Zend\Db\ResultSet\HydratingResultSet|\Zend\Db\Adapter\Driver\ResultInterface
     */
    public function getResponsableStation($stationId)
    {
        $where = new Predicate();
        $where->equalTo('millesime', $this->millesime)
            ->nest()
            ->equalTo('station1Id', $stationId)->or->equalTo('station2Id', $stationId)->unnest();
        return $this->renderResult($this->joinAffectations()
            ->where($where));
    }

    /**
     * Ne prend que les responsables d'enfants transportés cette année, y compris les
     * rayés
     *
     * @param int $lotId
     *
     * @return \Zend\Db\ResultSet\HydratingResultSet|\Zend\Db\Adapter\Driver\ResultInterface
     */
    public function getResponsablesLot($lotId)
    {
        return $this->renderResult(
            $this->joinServices()
                ->where([
                'millesime' => $this->millesime,
                'lotId' => $lotId
            ]));
    }

    /**
     * Ne prend que les responsables d'enfants transportés cette année, y compris les
     * rayés
     *
     * @param int $transporteurId
     *
     * @return \Zend\Db\ResultSet\HydratingResultSet|\Zend\Db\Adapter\Driver\ResultInterface
     */
    public function getResponsablesTransporteur($transporteurId)
    {
        return $this->renderResult(
            $this->joinServices()
                ->where(
                [
                    'millesime' => $this->millesime,
                    'transporteurId' => $transporteurId
                ]));
    }

    private function joinScolarites()
    {
        return $this->select->join(
            [
                'ele' => $this->db_manager->getCanonicName('eleves', 'table')
            ],
            'ele.responsable1Id = res.responsableId OR ele.responsable2Id = res.responsableId',
            [])
            ->join([
            'sco' => $this->db_manager->getCanonicName('scolarites', 'table')
        ], 'ele.eleveId = sco.eleveId', []);
    }

    private function joinAffectations()
    {
        return $this->select->join(
            [
                'aff' => $this->db_manager->getCanonicName('affectations', 'table')
            ], 'aff.responsableId = res.responsableId', []);
    }

    private function joinServices()
    {
        $format = '(ser.millesime = aff.millesime AND ser.ligneId = aff.ligne%1$dId AND ' .
            'ser.sens = aff.sensligne%1$d AND ser.moment = aff.moment AND ser.ordre = aff.ordreligne%1$d)';
        $jointure = sprintf($format, 1) . ' OR ' . sprintf($format, 2);
        return $this->joinAffectations()->join(
            [
                'ser' => $this->db_manager->getCanonicName('services', 'table')
            ], $jointure, []);
    }
}