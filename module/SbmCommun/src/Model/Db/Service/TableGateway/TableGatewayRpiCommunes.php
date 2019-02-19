<?php
/**
 * Service donnant un Tablegateway pour le table RpiCommunes
 * (à déclarer dans module.config.php)
 *
 * @project sbm
 * @package SbmCommun/Model/Db/Service/TableGateway
 * @filesource TableGatewayRpiCommunes.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 9 avr. 2018
 * @version 2018-2.4.0
 */
namespace SbmCommun\Model\Db\Service\TableGateway;

class TableGatewayRpiCommunes extends AbstractSbmTableGateway
{

    protected function init()
    {
        $this->table_name = 'rpi-communes';
        $this->type = 'table';
        $this->data_object_alias = 'Sbm\Db\ObjectData\RpiCommune';
    }
}