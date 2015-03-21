<?php
/**
 * Service donnant un Tablegateway pour la table Circuits
 * (à déclarer dans module.config.php)
 *
 * @project sbm
 * @package SbmCommun/Model/Db/Service/TableGateway
 * @filesource TableGatewayCircuits.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 11 mai 2014
 * @version 2014-1
 */
namespace SbmCommun\Model\Db\Service\TableGateway;

class TableGatewayCircuits extends AbstractSbmTableGateway
{
    protected function init()
    {
        $this->table_name = 'circuits';
        $this->type = 'table';
        $this->data_object_alias = 'Sbm\Db\ObjectData\Circuit';
    }
}