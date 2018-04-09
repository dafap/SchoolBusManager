<?php
/**
 * Service donnant un Tablegateway pour le table système `docaffectations`
 * (à déclarer dans module.config.php)
 *
 * @project sbm
 * @package SbmCommun/Model/Db/Service/TableGateway/Sys
 * @filesource TableGatewayDocAffectations.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 4 avr. 2018
 * @version 2018-2.4.0
 */
namespace SbmCommun\Model\Db\Service\TableGateway\Sys;

use SbmCommun\Model\Db\Service\TableGateway\AbstractSbmTableGateway;

class TableGatewayDocAffectations extends AbstractSbmTableGateway
{

    protected function init()
    {
        $this->table_name = 'docaffectations';
        $this->type = 'system';
        $this->data_object_alias = 'Sbm\Db\SysObjectData\DocAffectation';
    }
}