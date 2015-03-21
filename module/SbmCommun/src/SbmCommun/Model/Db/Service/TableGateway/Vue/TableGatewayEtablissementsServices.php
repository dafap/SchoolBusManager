<?php
/**
 * Service donnant un Tablegateway pour le table Etablissements
 * (à déclarer dans module.config.php)
 * 
 * @project sbm
 * @package SbmCommun/Model/Db/Service/TableGateway/Vue
 * @filesource TableGatewayEtablissementsServices.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 12 mars 2015
 * @version 2015-1
 */
namespace SbmCommun\Model\Db\Service\TableGateway\Vue;

use SbmCommun\Model\Db\Service\TableGateway\TableGatewayEtablissementsServices as TableGatewayTableEtablissementsServices;

class TableGatewayEtablissementsServices extends TableGatewayTableEtablissementsServices
{
    protected function init()
    {
        $this->table_name = 'etablissements-services';
        $this->type = 'vue';
        $this->data_object_alias = 'Sbm\Db\ObjectData\EtablissementService';
    }
}
 