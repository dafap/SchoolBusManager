<?php
/**
 * Service donnant un Tablegateway pour la table Transporteurs
 * (à déclarer dans module.config.php)
 *
 * @project sbm
 * @package SbmCommun/Model/Db/Service/TableGateway
 * @filesource TableGatewayZonage.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 1 août 2019
 * @version 2019-2.5.1
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