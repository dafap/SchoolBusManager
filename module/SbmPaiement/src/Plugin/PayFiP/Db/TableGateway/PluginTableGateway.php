<?php
/**
 * Service donnant un Tablegateway pour la table du plugin
 * (déclarée dans /config/autoload/sbm.global.php)
 *
 * @project sbm
 * @package SbmPaiement/Plugin/PayFiP/Db/TableGateway
 * @filesource PluginTableGateway.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 7 mai 2019
 * @version 2019-2.5.0
 */
namespace SbmPaiement\Plugin\PayFiP\Db\TableGateway;

use SbmCommun\Model\Db\Service\TableGateway\AbstractSbmTableGateway;

class PluginTableGateway extends AbstractSbmTableGateway
{

    protected function init()
    {
        $this->table_name = 'payfip';
        $this->type = 'table';
        $this->data_object_alias = 'SbmPaiement\Plugin\ObjectData';
    }
}