<?php
/**
 * Service donnant un Tablegateway pour la table UsersOrganismes
 *
 * (à déclarer dans module.config.php)
 *
 * @project sbm
 * @package SbmCommun/src/Model/Db/Service/TableGateway
 * @filesource TableGatewayUsersOrganismes.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 2 mai 2020
 * @version 2020-2.6.0
 */
namespace SbmCommun\Model\Db\Service\TableGateway;

class TableGatewayUsersOrganismes extends AbstractSbmTableGateway
{

    protected function init()
    {
        $this->table_name = 'users-organismes';
        $this->type = 'table';
        $this->data_object_alias = 'Sbm\Db\ObjectData\UserOrganisme';
    }
}