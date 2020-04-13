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
 * @date 12 avr. 2020
 * @version 2020-2.6.0
 */
namespace SbmCommun\Model\Db\Service\TableGateway;

use \SbmCommun\Model\Hydrator\Circuits as Hydrator;

class TableGatewayCircuits extends AbstractSbmTableGateway
{

    protected function init()
    {
        $this->table_name = 'circuits';
        $this->type = 'table';
        $this->data_object_alias = 'Sbm\Db\ObjectData\Circuit';
        $this->hydrator = new Hydrator();
    }
}