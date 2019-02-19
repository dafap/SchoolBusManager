<?php
/**
 * Service donnant un Tablegateway pour le vue Transporteurs
 * (à déclarer dans module.config.php)
 *
 * @project sbm
 * @package SbmCommun/Model/Db/Service/TableGateway/Vue
 * @filesource TableGatewayTransporteurs.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 4 avr. 2018
 * @version 2018-2.4.0
 */
namespace SbmCommun\Model\Db\Service\TableGateway\Vue;

use SbmCommun\Model\Db\Service\TableGateway\AbstractSbmTableGateway;

class TableGatewayTransporteurs extends AbstractSbmTableGateway
{

    protected function init()
    {
        $this->table_name = 'transporteurs';
        $this->type = 'vue';
        $this->data_object_alias = 'Sbm\Db\ObjectData\Transporteur';
    }
}