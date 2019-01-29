<?php
/**
 * Service donnant un Tablegateway pour la table ElevesPhotos
 * (à déclarer dans module.config.php)
 *
 * @project sbm
 * @package SbmCommun/Model/Db/Service/TableGateway
 * @filesource TableGatewayElevesPhotos.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 28 janv. 2019
 * @version 2019-2.4.6
 */
namespace SbmCommun\Model\Db\Service\TableGateway;

use SbmCommun\Model\Hydrator\ElevesPhotos as Hydrator;

class TableGatewayElevesPhotos extends AbstractSbmTableGateway
{

    protected function init()
    {
        $this->table_name = 'elevesphotos';
        $this->type = 'table';
        $this->data_object_alias = 'Sbm\Db\ObjectData\ElevePhoto';
        $this->hydrator = new Hydrator();
    }
}