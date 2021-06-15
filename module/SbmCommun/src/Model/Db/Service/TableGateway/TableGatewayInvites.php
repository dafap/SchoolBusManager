<?php
/**
 * Service donnant un Tablegateway pour la table Invites
 * (à déclarer dans module.config.php)
 *
 * L'hydrateur utilisé est celui des élèves (dateCreation, dateModification, champs se terminant par SA)
 *
 * @project sbm
 * @package SbmCommun/src/Model/Db/Service/TableGateway
 * @filesource TableGatewayInvites.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 14 oct. 2020
 * @version 2020-2.6.1
 */
namespace SbmCommun\Model\Db\Service\TableGateway;

use SbmCommun\Model\Hydrator\Invites as Hydrator;

class TableGatewayInvites extends AbstractSbmTableGateway
{
    protected function init()
    {
        $this->table_name = 'invites';
        $this->type = 'table';
        $this->data_object_alias = 'Sbm\Db\ObjectData\Invite';
        $this->hydrator = new Hydrator();
    }
}