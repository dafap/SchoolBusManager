<?php
/**
 * Service donnant un Tablegateway pour le table UsersEtablissements
 *
 * (à déclarer dans module.config.php)
 * 
 * @project sbm
 * @package SbmCommun/Model/Db/Service/TableGateway
 * @filesource TableGatewayUsersEtablissements.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 1 août 2015
 * @version 2015-1
 */
namespace SbmCommun\Model\Db\Service\TableGateway;

class TableGatewayUsersEtablissements extends AbstractSbmTableGateway
{
    protected function init()
    {
        $this->table_name = 'users-etablissements';
        $this->type = 'table';
        $this->data_object_alias = 'Sbm\Db\ObjectData\UserEtablissement';
    }
} 