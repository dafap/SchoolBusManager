<?php
/**
 * Gestion de la vue `etablissements`
 * (à déclarer dans module.config.php)
 *
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Model/Db/Service/Table/vue
 * @filesource Etablissements.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 17 févr. 2014
 * @version 2014-1
 */
namespace SbmCommun\Model\Db\Service\Table\Vue;

use SbmCommun\Model\Db\Service\Table\Etablissements as TableEtablissements;

class Etablissements extends TableEtablissements
{
    /**
     * Initialisation de l'etablissement
     */
    protected function init()
    {
        parent::init();
        $this->table_type = 'vue';
        $this->table_gateway_alias = 'Sbm\Db\VueGateway\Etablissements';
    }
}

