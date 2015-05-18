<?php
/**
 * Service donnant un Tablegateway pour la table Appels
 * (à déclarer dans module.config.php)
 *
 * Il s'agit des appels à la plateforme de paiement pour essayer de payer.
 * Cette table établit la liaison entre le payeur et les élèves concernés.
 * 
 * @project sbm
 * @package SbmCommun/Model/Db/Service/TableGateway
 * @filesource TableGatewayAppels.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 18 mai 2015
 * @version 2015-1
 */
namespace SbmCommun\Model\Db\Service\TableGateway;

class TableGatewayAppels extends AbstractSbmTableGateway
{
    protected function init()
    {
        $this->table_name = 'appels';
        $this->type = 'table';
        $this->data_object_alias = 'Sbm\Db\ObjectData\Appel';
    }
}