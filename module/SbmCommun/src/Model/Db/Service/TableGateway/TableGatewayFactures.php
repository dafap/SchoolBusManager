<?php
/**
 * Service donnant un Tablegateway pour la table Factures
 * (à déclarer dans module.config.php)
 *
 * 
 *
 * @project sbm
 * @package SbmCommun/Model/Db/Service/TableGateway
 * @filesource TableGatewayFactures.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 25 avr. 2019
 * @version 2019-2.5.0
 */
namespace SbmCommun\Model\Db\Service\TableGateway;

class TableGatewayFactures extends AbstractSbmTableGateway
{

    protected function init()
    {
        $this->table_name = 'factures';
        $this->type = 'table';
        $this->data_object_alias = 'Sbm\Db\ObjectData\Facture';
    }
}