<?php
/**
 * Gestion de la vue `transporteurs`
 * (à déclarer dans module.config.php)
 *
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Model/Db/Service/Table/vue
 * @filesource Transporteurs.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 17 févr. 2014
 * @version 2014-1
 */
namespace SbmCommun\Model\Db\Service\Table\Vue;

use SbmCommun\Model\Db\Service\Table\Transporteurs as TableTransporteurs;

class Transporteurs extends TableTransporteurs
{
    /**
     * Initialisation du transporteur
     */
    protected function init()
    {
        parent::init();
        $this->table_type = 'vue';
        $this->table_gateway_alias = 'Sbm\Db\VueGateway\Transporteurs';
    }
}

