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
 * @date 11 déc. 2020
 * @version 2020-2.6.1
 */
namespace SbmCommun\Model\Db\Service\Query\Station;

use SbmCommun\Model\Db\Service\Query\AbstractQuery;
use Zend\Db\Sql\Literal;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;

class Stations extends AbstractQuery
{
    use \SbmCommun\Model\Traits\ExpressionSqlTrait;

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

    protected function selectLocalisation(Where $where, $order): Select
    {
        if (is_array($order)) {
            $order = str_replace('commune', 'com.nom', $order);
            $order = array_merge($order, [
                'ligneId',
                'horaireD'
            ]);
        }
        // $where->equalTo('millesime', $this->millesime);
        $select = clone $this->sql->select();
        $select->quantifier(Select::QUANTIFIER_DISTINCT)
            ->from([
            'sta' => $this->db_manager->getCanonicName('stations', 'table')
        ])
            ->columns([
            'nom',
            'x',
            'y',
            'ouverte'
        ])
            ->join([
            'com' => $this->db_manager->getCanonicName('communes', 'table')
        ], 'sta.communeId=com.communeId', [
            'codePostal',
            'lacommune' => 'alias'
        ])
            ->join([
            'cir' => $this->db_manager->getCanonicName('circuits', 'table')
        ], 'cir.stationId = sta.stationId',
            [
                'serviceId' => new Literal($this->getSqlEncodeServiceId('cir')),
                'service' => new Literal(
                    $this->getSqlSemaineLigneHoraireSens('semaine', 'ligneId', 'horaireD')),
                'ligneId',
                'sens',
                'moment',
                'ordre',
                'passage',
                'horaireD'
            ], Select::JOIN_LEFT)
            ->where($where);
        if (! is_null($order)) {
            $select->order($order);
        }
        return $select;
    }

    public function getArrayDesserteStations(Where $where = null, $order = null)
    {
        if (is_null($where)) {
            $where = new Where();
        }
        $where->nest()->isNull('millesime')->or->equalTo('millesime', $this->millesime)->unnest();
        $resultset = $this->renderResult($this->selectDesserteStations($where, $order));
        $keysService = [
            'serviceId',
            'service',
            'ligneId',
            'sens',
            'moment',
            'ordre',
            'passage',
            'horaireD'
        ];
        $array = [];
        foreach ($resultset as $row) {
            $row->setFlags(\ArrayObject::ARRAY_AS_PROPS);
            $arStation = $row->getArrayCopy();
            $arService = [];
            foreach ($keysService as $key) {
                $arService[$key] = $row->{$key};
                unset($arStation[$key]);
            }
            $aoService = new \ArrayObject($arService, \ArrayObject::ARRAY_AS_PROPS);
            if (array_key_exists($row->stationId, $array)) {
                // ajout du service et de l'horaire
                $array[$row->stationId]->services[] = $aoService;
            } else {
                // création d'un élément
                $arStation['services'][] = $aoService;
                $array[$row->stationId] = new \ArrayObject($arStation,
                    \ArrayObject::ARRAY_AS_PROPS);
            }
        }
        return $array;
    }

    protected function selectDesserteStations(Where $where, $order): Select
    {
        if (! $order) {
            $order = [];
        }
        return $this->selectLocalisation($where, $order)->columns(
            [
                'stationId',
                'nom',
                'alias',
                'x',
                'y',
                'ouverte'
            ]);
    }
}
