<?php
/**
 * Gestion de la vue `eleves`
 * (à déclarer dans module.config.php)
 *
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Model/Db/Service/Table/vue
 * @filesource Eleves.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 18 juil. 2014
 * @version 2014-1
 */
namespace SbmCommun\Model\Db\Service\Table\Vue;

use SbmCommun\Model\Db\Service\Table\Eleves as TableEleves;

class Eleves extends TableEleves
{
    /**
     * Initialisation du transporteur
     */
    protected function init()
    {
        parent::init();
        $this->table_type = 'vue';
        $this->table_gateway_alias = 'Sbm\Db\VueGateway\Eleves';
    }
}
