<?php
/**
 * Requêtes pour extraire des services
 *
 * @project sbm
 * @package SbmCommun/Model/Db/Service/Query/Service
 * @filesource Services.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 17 mai 2019
 * @version 2019-2.5.0
 */
namespace SbmCommun\Model\Db\Service\Query\Service;

use SbmCommun\Model\Db\Service\Query\AbstractQuery;
use Zend\Db\Sql\Where;

class Services extends AbstractQuery
{

    protected function init()
    {
    }

    public function getServicesGivenEtablissement($etablissementId)
    {
        return $this->renderResult(
            $this->selectServicesGivenEtablissement($etablissementId));
    }

    private function selectServicesGivenEtablissement($etablissementId)
    {
        $where = new Where();
        $where->equalTo('etablissementId', $etablissementId);
        $select = $this->sql->select();
        return $select->from(
            [
                'ser' => $this->db_manager->getCanonicName('services', 'table')
            ])
            ->columns([
            'serviceId',
            'alias',
            'aliasTr',
            'nom',
            'operateur',
            'nbPlaces'
        ])
            ->join(
            [
                'etaser' => $this->db_manager->getCanonicName('etablissements-services',
                    'table')
            ], 'ser.serviceId = etaser.serviceId', [])
            ->join([
            'lot' => $this->db_manager->getCanonicName('lots')
        ], 'ser.lotId = lot.lotId', [])
            ->join([
            'tit' => $this->db_manager->getCanonicName('transporteurs')
        ], 'lot.transporteurId = tit.transporteurId', [
            'titulaire' => 'nom'
        ])
            ->join([
            'tra' => $this->db_manager->getCanonicName('transporteurs')
        ], 'ser.transporteurId = tra.transporteurId', [
            'transporteur' => 'nom'
        ])
            ->where($where);
    }

    /**
     * Renvoie un tableau des services avec leur transporteur et un tableau
     * d'établissements desservis par chaque service
     *
     * @return array
     */
    public function getServicesWithEtablissements()
    {
        $rowset = $this->renderResult($this->selectServicesWithEtablissements());
        $result = [];
        foreach ($rowset as $row) {
            if (! array_key_exists($row['serviceId'], $result)) {
                $result[$row['serviceId']] = [
                    'serviceId' => $row['serviceId'],
                    'alias' => $row['alias'],
                    'aliasTr' => $row['aliasTr'],
                    'nom' => $row['nom'],
                    'operateur' => $row['operateur'],
                    'nbPlaces' => $row['nbPlaces'],
                    'titulaire' => $row['titulaire'],
                    'transporteur' => $row['transporteur'],
                    'etablissements' => []
                ];
            }
            $result[$row['serviceId']]['etablissements'][] = [
                'etablissement' => $row['etablissement'],
                'communeEtablissement' => $row['communeEtablissement']
            ];
        }
        return $result;
    }

    /**
     * Attention, la structure du résultat est celle de getServicesWithEtablissements()
     * mais le résultat est paginé.
     *
     * @return \Zend\Paginator\Paginator
     */
    public function paginatorServicesWithEtablissements()
    {
        return new \Zend\Paginator\Paginator(
            new \Zend\Paginator\Adapter\ArrayAdapter(
                $this->getServicesWithEtablissements()));
    }

    private function selectServicesWithEtablissements()
    {
        $select = $this->sql->select();
        return $select->from(
            [
                'ser' => $this->db_manager->getCanonicName('services', 'table')
            ])
            ->columns([
            'serviceId',
            'alias',
            'aliasTr',
            'nom',
            'operateur',
            'nbPlaces'
        ])
            ->join([
            'lot' => $this->db_manager->getCanonicName('lots')
        ], 'ser.lotId = lot.lotId', [])
            ->join([
            'tit' => $this->db_manager->getCanonicName('transporteurs')
        ], 'lot.transporteurId = tit.transporteurId', [
            'titulaire' => 'nom'
        ])
            ->join([
            'tra' => $this->db_manager->getCanonicName('transporteurs')
        ], 'ser.transporteurId = tra.transporteurId', [
            'transporteur' => 'nom'
        ])
            ->join(
            [
                'etaser' => $this->db_manager->getCanonicName('etablissements-services',
                    'table')
            ], 'ser.serviceId = etaser.serviceId', [])
            ->join(
            [
                'eta' => $this->db_manager->getCanonicName('etablissements', 'table')
            ], 'eta.etablissementId = etaser.etablissementId',
            [
                'etablissement' => 'nom'
            ])
            ->join([
            'com' => $this->db_manager->getCanonicName('communes', 'table')
        ], 'com.communeId = eta.communeId', [
            'communeEtablissement' => 'nom'
        ])
            ->order('serviceId');
    }
}