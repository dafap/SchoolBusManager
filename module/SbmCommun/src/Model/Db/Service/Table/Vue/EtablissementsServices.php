<?php
/**
 * Gestion de la vue `etablissements-services`
 * (à déclarer dans module.config.php)
 * 
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Model/Db/Service/Table/vue
 * @filesource EtablissementsServices.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 4 avr. 2018
 * @version 2018-2.4.0
 */
namespace SbmCommun\Model\Db\Service\Table\Vue;

use SbmCommun\Model\Db\Service\Table\EtablissementsServices as TableEtablissementsServices;

class EtablissementsServices extends TableEtablissementsServices
{

    /**
     * Initialisation du service
     */
    protected function init()
    {
        $this->table_name = 'etablissements-services';
        $this->table_type = 'vue';
        $this->table_gateway_alias = 'Sbm\Db\VueGateway\EtablissementsServices';
    }
}

 