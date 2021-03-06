<?php
/**
 * Service donnant un Tablegateway pour le table système `doclabels`
 * (à déclarer dans module.config.php)
 * 
 * @project sbm
 * @package SbmCommun/Model/Db/Service/TableGateway/Sys
 * @filesource TableGatewayDocLabels.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 28 juil. 2015
 * @version 2015-1
 */

namespace SbmCommun\Model\Db\Service\TableGateway\Sys;

use SbmCommun\Model\Db\Service\TableGateway\AbstractSbmTableGateway;

class TableGatewayDocLabels extends AbstractSbmTableGateway
{
    protected function init()
    {
        $this->table_name = 'doclabels';
        $this->type = 'system';
        $this->data_object_alias = 'Sbm\Db\SysObjectData\DocLabel';
    }
} 