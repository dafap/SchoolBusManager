<?php
/**
 * Service donnant un Tablegateway pour le table EsendexSms
 * (à déclarer dans module.config.php)
 *
 * @project sbm
 * @package SbmEsendex/src/Model/Db/Service/TableGateway
 * @filesource TableGatewayEsendexSms.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 29 mai 2020
 * @version 2020-2.6.0
 */
namespace SbmEsendex\Model\Db\Service\TableGateway;

use SbmCommun\Model\Db\Service\TableGateway\AbstractSbmTableGateway;

class TableGatewayEsendexSms extends AbstractSbmTableGateway
{
    protected function init()
    {
        $this->table_name = 'esendexsms';
        $this->type = 'table';
        $this->data_object_alias = 'Sbm\Db\ObjectData\EsendexSms';
    }
}