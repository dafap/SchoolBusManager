<?php
/**
 * Service donnant un Tablegateway pour le table Services
 * (à déclarer dans module.config.php)
 *
 * @project sbm
 * @package SbmCommun/Model/Db/Service/TableGateway
 * @filesource TableGatewayServices.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 4 avr. 2018
 * @version 2018-2.4.0
 */
namespace SbmCommun\Model\Db\Service\TableGateway;

class TableGatewayServices extends AbstractSbmTableGateway
{

    protected function init()
    {
        $this->table_name = 'services';
        $this->type = 'table';
        $this->data_object_alias = 'Sbm\Db\ObjectData\Service';
    }
}