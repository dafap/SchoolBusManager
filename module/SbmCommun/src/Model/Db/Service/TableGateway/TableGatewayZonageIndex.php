<?php
/**
 * Service donnant un Tablegateway pour la table 'zonage-index'
 * (à déclarer dans module.config.php)
 *
 * @project sbm
 * @package SbmCommun/Model/Db/Service/TableGateway
 * @filesource TableGatewayZonageIndex.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 11 juin 2020
 * @version 2020-2.5.4
 */
namespace SbmCommun\Model\Db\Service\TableGateway;

class TableGatewayZonageIndex extends AbstractSbmTableGateway
{

    protected function init()
    {
        $this->table_name = 'zonage-index';
        $this->type = 'table';
        $this->data_object_alias = 'Sbm\Db\ObjectData\ZonageIndex';
    }
}