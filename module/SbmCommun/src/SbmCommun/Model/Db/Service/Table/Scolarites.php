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
use SbmCommun\Model\Strategy\Semaine as SemaineStrategy;
use SbmCommun\Model\Db\ObjectData\Exception as ObjectDataException;

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
        $this->id_name = array(
            'millesime',
            'eleveId'
        );
    }

    /**
     * (non-PHPdoc)
     *
     * @see \SbmCommun\Model\Db\Table\AbstractTable::setStrategies()
     */
    protected function setStrategies()
    {
        $this->hydrator->addStrategy('joursTransport', new SemaineStrategy());
    }

    /**
     * Renvoie true si l'établissement a changé ou si c'est un nouvel enregistrement ou si district == 0
     *
     * (non-PHPdoc)
     * 
     * @see \SbmCommun\Model\Db\Service\Table\AbstractSbmTable::saveRecord()
     */
    public function saveRecord(ObjectDataInterface $obj_data)
    {
        $changeEtab = false;
        try {
            $old_data = $this->getRecord($obj_data->getId());
            $changeEtab = $old_data->district == 0; // pour forcer l'actualisation de district
            $is_new = false;
        } catch (Exception $e) {
            $is_new = true;
        }
        if ($is_new) {
            $obj_data->setCalculateFields(array(
                'dateInscription'
            ));
            $changeEtab = true;
        } else {
            // on vérifie si des données ont changé
            if ($obj_data->isUnchanged($old_data)) {
                return $changeEtab;
            }
            try {
                $changeEtab |= $obj_data->etablissementId != $old_data->etablissementId;
            } catch (ObjectDataException $e) {
                $changeEtab = false;
            }
            $obj_data->addCalculateField('dateModification');
        }
        
        parent::saveRecord($obj_data);
        return $changeEtab;
    }
}