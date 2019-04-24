<?php
/**
 * Service donnant un Tablegateway pour le table CleverSms
 * (à déclarer dans module.config.php)
 *
 * @project sbm
 * @package SbmCleverSms/src/Model/Db/Service/TableGateway
 * @filesource TableGatewayCleverSms.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 19 avr. 2019
 * @version 2019-2.5.0
 */
namespace SbmCleverSms\Model\Db\Service\TableGateway;

use SbmCommun\Model\Db\Service\TableGateway\AbstractSbmTableGateway;

class TableGatewayCleverSms extends AbstractSbmTableGateway
{
    protected function init()
    {
        $this->table_name = 'cleversms';
        $this->type = 'table';
        $this->data_object_alias = 'Sbm\Db\ObjectData\CleverSms';
    }
}