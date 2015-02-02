<?php
/**
 * Service donnant un Tablegateway pour le vue Stations
 * (à déclarer dans module.config.php)
 *
 * @project sbm
 * @package SbmCommun/Model/Db/Service/TableGateway/Vue
 * @filesource TableGatewayPaiements.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 23 janv. 2015
 * @version 2015-1
 */
namespace SbmCommun\Model\Db\Service\TableGateway\Vue;

use SbmCommun\Model\Db\Service\TableGateway\TableGatewayPaiements as TableGatewayTablePaiements;
class TableGatewayPaiements extends TableGatewayTablePaiements
{
    protected function init()
    {
        parent::init();
        $this->type = 'vue';
    }
} 