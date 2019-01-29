<?php
/**
 * Gestion de la table `elevesphotos`
 * (à déclarer dans module.config.php)
 * 
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Model/Db/Table
 * @filesource ElevesPhotos.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 28 janv. 2019
 * @version 2019-2.4.6
 */
namespace SbmCommun\Model\Db\Service\Table;

use SbmCommun\Model\Db\ObjectData\ObjectDataInterface;
use Zend\Db\Sql\Expression;

class ElevesPhotos extends AbstractSbmTable
{

    /**
     * Initialisation du service
     */
    protected function init()
    {
        $this->table_name = 'elevesphotos';
        $this->table_type = 'table';
        $this->table_gateway_alias = 'Sbm\Db\TableGateway\ElevesPhotos';
        $this->id_name = 'eleveId';
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
            $obj_data->setCalculateFields(
                [
                    'dateCreation'
                ]);
        } else {
            // on vérifie si des données ont changé
            if ($obj_data->isUnchanged($old_data)) {
                return;
            }
            $obj_data->setCalculateFields(
                [
                    'dateModification'
                ]);
        }        
        parent::saveRecord($obj_data);
    }
    
    /**
     * Renvoie la date du dernier lot d'extraction de photos
     */
    public function getLastDateExtraction()
    {
        $select = $this->table_gateway->getSql()
        ->select()
        ->columns(
            [
                'lastDateExtraction' => new Expression('MAX(dateExtraction)')
            ]);
        $rowset = $this->table_gateway->selectWith($select);
        return $rowset->current()->lastDateExtraction;
    }
}