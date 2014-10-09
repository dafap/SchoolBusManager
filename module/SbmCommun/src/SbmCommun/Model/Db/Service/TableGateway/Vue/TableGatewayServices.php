<?php
/**
 * Service donnant un Tablegateway pour le vue Services
 * (à déclarer dans module.config.php)
 *
 * @project sbm
 * @package SbmCommun/Model/Db/Service/TableGateway/vue
 * @filesource TableGatewayServices.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 16 mai 2014
 * @version 2014-1
 */
namespace SbmCommun\Model\Db\Service\TableGateway\Vue;

use SbmCommun\Model\Db\Service\TableGateway\AbstractSbmTableGateway;
class TableGatewayServices extends AbstractSbmTableGateway
{
    protected function init()
    {
        $this->table_name = 'services';
        $this->type = 'vue';
        $this->data_object_alias = 'Sbm\Db\ObjectData\Service';
    }
}