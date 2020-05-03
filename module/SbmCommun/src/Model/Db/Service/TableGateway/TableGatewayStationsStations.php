<?php
/**
 * Station donnant un Tablegateway pour la table StationsStations
 * (à déclarer dans module.config.php)
 *
 * @project sbm
 * @package SbmCommun/Model/Db/Service/TableGateway
 * @filesource TableGatewayStationsStations.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 2 mai 2020
 * @version 2020-2.6.0
 */
namespace SbmCommun\Model\Db\Service\TableGateway;

class TableGatewayStationsStations extends AbstractSbmTableGateway
{

    protected function init()
    {
        $this->table_name = 'stations-stations';
        $this->type = 'table';
        $this->data_object_alias = 'Sbm\Db\ObjectData\StationStation';
    }
}
