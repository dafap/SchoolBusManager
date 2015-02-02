<?php
/**
 * Gestion de la vue `paiements`
 *
 * (à déclarer dans module.config.php)
 * 
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Model/Db/Service/Table/Vue
 * @filesource Paiements.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 23 janv. 2015
 * @version 2015-1
 */
namespace SbmCommun\Model\Db\Service\Table\Vue;

use SbmCommun\Model\Db\Service\Table\Paiements as TablePaiements;

class Paiements extends TablePaiements
{
    /**
     * Initialisation de la station
     */
    protected function init()
    {
        parent::init();
        $this->table_type = 'vue';
        $this->table_gateway_alias = 'Sbm\Db\VueGateway\Paiements';
    }
}