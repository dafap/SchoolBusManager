<?php
/**
 * Service donnant un Tablegateway pour le table Lot
 * (à déclarer dans module.config.php)
 *
 * @project sbm
 * @package SbmCommun/src/Model/Db/Service/TableGateway
 * @filesource TableGatewayLots.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 19 avr. 2019
 * @version 2019-2.5.0
 */
namespace SbmCommun\Model\Db\Service\TableGateway;

class TableGatewayLots extends AbstractSbmTableGateway
{

    protected function init()
    {
        $this->table_name = 'lots';
        $this->type = 'table';
        $this->data_object_alias = 'Sbm\Db\ObjectData\Lot';
    }
}