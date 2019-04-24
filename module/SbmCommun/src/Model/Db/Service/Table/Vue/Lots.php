<?php
/**
 * Gestion de la vue `lots`
 * (à déclarer dans module.config.php)
 *
 * @project sbm
 * @package SbmCommun/src/Model/Db/Service/Table/vue
 * @filesource Lots.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 22 mars 2019
 * @version 2019-2.5.0
 */
namespace SbmCommun\Model\Db\Service\Table\Vue;

use SbmCommun\Model\Db\Service\Table\Lots as TableLots;

class Lots extends TableLots
{

    /**
     * Initialisation du circuit
     */
    protected function init()
    {
        parent::init();
        $this->table_type = 'vue';
        $this->table_gateway_alias = 'Sbm\Db\VueGateway\Lots';
    }
}
