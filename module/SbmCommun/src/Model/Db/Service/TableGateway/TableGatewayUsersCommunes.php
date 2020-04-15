<?php
/**
 * Service donnant un Tablegateway pour le table UsersCommunes
 *
 * (à déclarer dans module.config.php)
 *
 * @project sbm
 * @package SbmCommun/src/Model/Db/Service/TableGateway
 * @filesource TableGatewayUsersCommunes.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 15 avr. 2020
 * @version 2020-2.6.0
 */
namespace SbmCommun\Model\Db\Service\TableGateway;

class TableGatewayUsersCommunes extends AbstractSbmTableGateway
{

    protected function init()
    {
        $this->table_name = 'users-communes';
        $this->type = 'table';
        $this->data_object_alias = 'Sbm\Db\ObjectData\UserCommune';
    }
}