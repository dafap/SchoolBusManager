<?php
/**
 * Service donnant un Tablegateway pour la table `Paiements`
 * (à déclarer dans module.config.php)
 *
 * @project sbm
 * @package SbmCommun/Model/Db/Service/TableGateway
 * @filesource TableGatewayPaiements.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 20 jan. 2015
 * @version 2015-1
 */
namespace SbmCommun\Model\Db\Service\TableGateway;

class TableGatewayPaiements extends AbstractSbmTableGateway
{
    protected function init()
    {
        $this->table_name = 'paiements';
        $this->type = 'table';
        $this->data_object_alias = 'Sbm\Db\ObjectData\Paiement';
    }
}