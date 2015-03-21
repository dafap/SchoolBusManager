<?php
/**
 * Service donnant un Tablegateway pour la table Users
 * (à déclarer dans module.config.php)
 * 
 * @project sbm
 * @package SbmCommun/Model/Db/Service/TableGateway
 * @filesource TableGatewayUsers.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 10 févr. 2015
 * @version 2015-1
 */
namespace SbmCommun\Model\Db\Service\TableGateway;

class TableGatewayUsers extends AbstractSbmTableGateway
{
    protected function init()
    {
        $this->table_name = 'users';
        $this->type = 'table';
        $this->data_object_alias = 'Sbm\Db\ObjectData\User';
    }
} 