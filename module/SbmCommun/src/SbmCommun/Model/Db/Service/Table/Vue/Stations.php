<?php
/**
 * Gestion de la vue `stations`
 *
 *
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Model/Db/Service/Table/Vue
 * @filesource Stations.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 4 avr. 2018
 * @version 2018-2.4.0
 */
namespace SbmCommun\Model\Db\Service\Table\Vue;

use SbmCommun\Model\Db\Service\Table\Stations as TableStations;

class Stations extends TableStations
{

    /**
     * Initialisation de la station
     */
    protected function init()
    {
        parent::init();
        $this->table_type = 'vue';
        $this->table_gateway_alias = 'Sbm\Db\VueGateway\Stations';
    }
}

