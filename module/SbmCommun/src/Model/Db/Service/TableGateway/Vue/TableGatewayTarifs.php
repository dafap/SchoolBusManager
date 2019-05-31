<?php
/**
 * Service donnant un Tablegateway pour le vue Tarifs
 * (à déclarer dans module.config.php)
 *
 * @project sbm
 * @package SbmCommun/Model/Db/Service/TableGateway/Vue
 * @filesource TableGatewayTarifs.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 31 mai 2019
 * @version 2019-2.5.0
 */
namespace SbmCommun\Model\Db\Service\TableGateway\Vue;

use SbmCommun\Model\Db\Service\TableGateway\AbstractSbmTableGateway;

class TableGatewayTarifs extends AbstractSbmTableGateway
{

    protected function init()
    {
        $this->table_name = 'tarifs';
        $this->type = 'vue';
        $this->data_object_alias = 'Sbm\Db\ObjectData\Tarif';
    }
}