<?php
/**
 * Gestion de la vue `services`
 * (à déclarer dans module.config.php)
 *
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Model/Db/Service/Table/vue
 * @filesource Services.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 4 avr. 2018
 * @version 2018-2.4.0
 */
namespace SbmCommun\Model\Db\Service\Table\Vue;

use SbmCommun\Model\Db\Service\Table\Services as TableServices;

class Services extends TableServices
{

    /**
     * Initialisation du service
     */
    protected function init()
    {
        parent::init();
        $this->table_type = 'vue';
        $this->table_gateway_alias = 'Sbm\Db\VueGateway\Services';
    }
}

