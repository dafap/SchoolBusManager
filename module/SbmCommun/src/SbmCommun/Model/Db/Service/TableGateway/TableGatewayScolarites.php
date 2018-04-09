<?php
/**
 * Service donnant un Tablegateway pour la table `scolarites`
 * (à déclarer dans module.config.php)
 *
 * @project sbm
 * @package SbmCommun/Model/Db/Service/TableGateway
 * @filesource TableGatewayScolarites.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 4 avr. 2018
 * @version 2018-2.4.0
 */
namespace SbmCommun\Model\Db\Service\TableGateway;

use SbmCommun\Model\Hydrator\Scolarites as Hydrator;

class TableGatewayScolarites extends AbstractSbmTableGateway
{

    protected function init()
    {
        $this->table_name = 'scolarites';
        $this->type = 'table';
        $this->data_object_alias = 'Sbm\Db\ObjectData\Scolarite';
        $this->hydrator = new Hydrator();
    }
}