<?php
/**
 * Requêtes pour extraire des stations
 *
 *
 * @project sbm
 * @package SbmCommun/src/Model/Db/Service/Query/Station
 * @filesource Stations.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 13 avr. 2019
 * @version 2019-2.5.0
 */
namespace SbmCommun\Model\Db\Service\Query\Station;

use SbmCommun\Model\Db\Service\Query\AbstractQuery;
use Zend\Db\Sql\Where;

class Stations extends AbstractQuery
{

    protected function init()
    {
    }

    /**
     * Requête préparée renvoyant la position géographique des stations,
     *
     * @param Where $where
     * @param string $order
     * @return \Zend\Db\Adapter\Driver\ResultInterface
     */
    public function getLocalisation(Where $where, $order = null)
    {
        return $this->renderResult($this->selectLocalisation($where, $order));
    }

    private function selectLocalisation(Where $where, $order = null)
    {
        $where->equalTo('millesime', $this->millesime);
        $select = clone $this->sql->select();
        $select->from([
            'sta' => $this->db_manager->getCanonicName('stations', 'table')
        ])
            ->columns([
            'nom',
            'x',
            'y'
        ])
            ->join([
            'com' => $this->db_manager->getCanonicName('communes', 'table')
        ], 'sta.communeId=com.communeId', [
            'commune' => 'nom'
        ])
            ->join([
            'cir' => $this->db_manager->getCanonicName('circuits', 'table')
        ], 'cir.stationId = sta.stationId', [
            'serviceId'
        ]);
        if (! is_null($order)) {
            $select->order($order);
        }
        return $select->where($where);
    }
}
