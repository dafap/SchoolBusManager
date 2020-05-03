<?php
/**
 * Gestion de la table `stations-stations`
 * (à déclarer dans module.config.php)
 *
 * Version pour TRANSDEV ALBERTVILLE
 *
 * @project sbm
 * @package module/SbmCommun/src/Model/Db/Service/Table
 * @filesource StationsStations.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 2 mai 2020
 * @version 2020-2.6.0
 */
namespace SbmCommun\Model\Db\Service\Table;

class StationsStations extends AbstractSbmTable
{
    /**
     * Initialisation du service
     */
    protected function init()
    {
        $this->table_name = 'stations-stations';
        $this->table_type = 'table';
        $this->table_gateway_alias = 'Sbm\Db\TableGateway\StationsStations';
        $this->id_name = [
            'station1Id',
            'station2Id'
        ];
    }
}

