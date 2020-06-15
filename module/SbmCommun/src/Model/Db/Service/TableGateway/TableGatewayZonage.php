<?php
/**
 * Service donnant un Tablegateway pour la table 'zonage'
 * (à déclarer dans module.config.php)
 *
 * @project sbm
 * @package SbmCommun/Model/Db/Service/TableGateway
 * @filesource TableGatewayZonage.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 11 juin 2020
 * @version 2020-2.5.4
 */
namespace SbmCommun\Model\Db\Service\TableGateway;

use SbmCommun\Model\Hydrator\Zonage as Hydrator;

class TableGatewayZonage extends AbstractSbmTableGateway
{

    protected function init()
    {
        $this->table_name = 'zonage';
        $this->type = 'table';
        $this->data_object_alias = 'Sbm\Db\ObjectData\Zonage';
        $this->hydrator = new Hydrator();
    }
}