<?php
/**
 * Gestion de la table `responsables`
 *
 *
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Model/Db/Table
 * @filesource Responsables.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 18 juil. 2014
 * @version 2014-1
 */
namespace SbmCommun\Model\Db\Service\Table;

use SbmCommun\Model\Db\ObjectData\ObjectDataInterface;
use SbmCommun\Model\Db\ObjectData\Responsable as ObjectDataResponsable;

class Responsables extends AbstractSbmTable
{

    /**
     * Initialisation du transporteur
     */
    protected function init()
    {
        $this->table_name = 'responsables';
        $this->table_type = 'table';
        $this->table_gateway_alias = 'Sbm\Db\TableGateway\Responsables';
        $this->id_name = 'responsableId';
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
                'nomSA',
                'prenomSA',
                'dateCreation'
            ));
        } else {
            // on vérifie si des données ont changé
            if ($this->isUnchanged($obj_data, $old_data))
                return;
            
            if ($old_data->nom != $obj_data->nom) {
                $obj_data->addCalculateField('nomSA');
            }
            if ($old_data->prenom != $obj_data->prenom) {
                $obj_data->addCalculateField('prenomSA');
            }
            if ($old_data->demenagement != $obj_data->demenagement) {
                $obj_data->addCalculateField('demenagement');
            }
            $obj_data->addCalculateField('dateModification');
        }
        
        parent::saveRecord($obj_data);
    }

    private function isUnchanged(ObjectDataResponsable $obj1, ObjectDataResponsable $obj2)
    {
        $data1 = $obj1->getArrayCopy();
        $data2 = $obj2->getArrayCopy();
        $commun1 = array_intersect_key($data1, $data2);
        $commun2 = array_intersect_key($data2, $data1);
        return $commun1 == $commun2;
    }
}