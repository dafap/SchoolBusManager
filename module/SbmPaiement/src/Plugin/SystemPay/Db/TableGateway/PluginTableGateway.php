<?php
/**
 * Service donnant un Tablegateway pour la table du plugin
 * (déclarée dans /config/autoload/sbm.global.php)
 * 
 * @project sbm
 * @package SbmPaiement/Plugin/SystemPay/Db/TableGateway
 * @filesource PluginTableGateway.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 5 avr. 2018
 * @version 2018-2.4.0
 */
namespace SbmPaiement\Plugin\SystemPay\Db\TableGateway;

use SbmCommun\Model\Db\Service\TableGateway\AbstractSbmTableGateway;

class PluginTableGateway extends AbstractSbmTableGateway
{

    protected function init()
    {
        $this->table_name = 'systempay';
        $this->type = 'table';
        $this->data_object_alias = 'SbmPaiement\Plugin\ObjectData';
    }
}