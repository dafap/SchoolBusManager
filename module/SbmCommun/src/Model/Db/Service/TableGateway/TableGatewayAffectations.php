<?php
/**
 * Service donnant un Tablegateway pour la table Affectations
 * (à déclarer dans module.config.php)
 * 
 * @project sbm
 * @package SbmCommun/Model/Db/Service/TableGateway
 * @filesource TableGatewayAffectations.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 4 avr. 2018
 * @version 2018-2.4.0
 */
namespace SbmCommun\Model\Db\Service\TableGateway;

class TableGatewayAffectations extends AbstractSbmTableGateway
{

    protected function init()
    {
        $this->table_name = 'affectations';
        $this->type = 'table';
        $this->data_object_alias = 'Sbm\Db\ObjectData\Affectation';
    }
}