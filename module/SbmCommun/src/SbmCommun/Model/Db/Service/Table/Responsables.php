<?php
/**
 * Gestion de la table `responsables`
 * (à déclarer dans module.config.php)
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
     * Initialisation du responsable
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
            if ($obj_data->isUnchanged($old_data))
                return;
            
            if ($old_data->nom != $obj_data->nom) {
                $obj_data->addCalculateField('nomSA');
            }
            if ($old_data->prenom != $obj_data->prenom) {
                $obj_data->addCalculateField('prenomSA');
            }
            /*if ($old_data->demenagement != $obj_data->demenagement) {
                $obj_data->addCalculateField('demenagement');
            }*/
            $obj_data->addCalculateField('dateModification');
        }
        
        parent::saveRecord($obj_data);
    }
    
    /**
     * Renvoie le `nom prénom` du responsable, éventuellement précédé du `titre`
     * 
     * @param int $responsabled
     * référence du respondable
     * @param bool $with_titre
     * indique si le titre doit être mis ou non
     * 
     * @return string
     */
    public function getNomPrenom($responsabled, $with_titre = false)
    {
        $record = $this->getRecord($responsabled);
        return ($with_titre ? $record->titre . ' ' : '') . $record->nom . ' ' . $record->prenom;
    }
}