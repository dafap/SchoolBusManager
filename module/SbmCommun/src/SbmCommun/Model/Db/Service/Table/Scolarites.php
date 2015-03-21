<?php
/**
 * Gestion de la table `scolarites`
 * (à déclarer dans module.config.php)
 *
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Model/Db/Table
 * @filesource Scolarites.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 10 oct. 2014
 * @version 2014-1
 */
namespace SbmCommun\Model\Db\Service\Table;

use SbmCommun\Model\Db\ObjectData\ObjectDataInterface;

class Scolarites extends AbstractSbmTable
{

    /**
     * Initialisation du transporteur
     */
    protected function init()
    {
        $this->table_name = 'scolarites';
        $this->table_type = 'table';
        $this->table_gateway_alias = 'Sbm\Db\TableGateway\Scolarites';
        $this->id_name = array('millesime', 'eleveId');
    }

    public function saveRecord(ObjectDataInterface $obj_data)
    {
        try {
            $old_data = $this->getRecord($obj_data->getId());
            $is_new = false;
        } catch (Exception $e) {
            $is_new = true;
        }
        if ($is_new) {
            $obj_data->setCalculateFields(array(
                'dateInscription'
            ));
        } else {
            // on vérifie si des données ont changé
            if ($obj_data->isUnchanged($old_data))
                return;
            
            $obj_data->addCalculateField('dateModification');
        }
        
        parent::saveRecord($obj_data);
    }
}