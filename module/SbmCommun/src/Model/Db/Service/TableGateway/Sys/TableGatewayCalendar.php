<?php
/**
 * Service donnant un Tablegateway pour le table système `calendar`
 *
 * (à déclarer dans module.config.php)
 * 
 * @project sbm
 * @package SbmCommun/Model/Db/Service/TableGateway/Sys
 * @filesource TableGatewayCalendar.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 4 nov. 2014
 * @version 2014-1
 */
namespace SbmCommun\Model\Db\Service\TableGateway\Sys;

use SbmCommun\Model\Db\Service\TableGateway\AbstractSbmTableGateway;

class TableGatewayCalendar extends AbstractSbmTableGateway
{
    protected function init()
    {
        $this->table_name = 'calendar';
        $this->type = 'system';
        $this->data_object_alias = 'Sbm\Db\SysObjectData\Calendar';
    }
}
 