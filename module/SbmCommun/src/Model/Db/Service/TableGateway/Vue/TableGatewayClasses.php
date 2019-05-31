<?php
/**
 * Service donnant un Tablegateway pour le vue Classes
 * (à déclarer dans module.config.php)
 *
 * @project sbm
 * @package SbmCommun/Model/Db/Service/TableGateway/Vue
 * @filesource TableGatewayClasses.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 31 mai 2019
 * @version 2019-2.5.0
 */
namespace SbmCommun\Model\Db\Service\TableGateway\Vue;

use SbmCommun\Model\Db\Service\TableGateway\AbstractSbmTableGateway;

class TableGatewayClasses extends AbstractSbmTableGateway
{

    protected function init()
    {
        $this->table_name = 'classes';
        $this->type = 'vue';
        $this->data_object_alias = 'Sbm\Db\ObjectData\Classe';
    }
}