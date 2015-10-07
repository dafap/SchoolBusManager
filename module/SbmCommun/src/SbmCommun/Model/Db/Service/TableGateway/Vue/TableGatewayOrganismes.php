<?php
/**
 * Service donnant un Tablegateway pour la vue Organismes
 * (à déclarer dans module.config.php)
 *
 * @project sbm
 * @package SbmCommun/Model/Db/Service/TableGateway/Vue
 * @filesource TableGatewayOrganismes.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 6 oct. 2015
 * @version 2015-1
 */
namespace SbmCommun\Model\Db\Service\TableGateway\Vue;

use SbmCommun\Model\Db\Service\TableGateway\AbstractSbmTableGateway;
class TableGatewayOrganismes extends AbstractSbmTableGateway
{
    protected function init()
    {
        $this->table_name = 'organismes';
        $this->type = 'vue';
        $this->data_object_alias = 'Sbm\Db\ObjectData\Organisme';
    }
}