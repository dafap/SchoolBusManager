<?php
/**
 * Gestion de la table `invites`
 * (à déclarer dans module.config.php)
 *
 * @project sbm
 * @package SbmCommun/src/SbmCommun/Model/Db/Table
 * @filesource Invites.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 14 oct. 2020
 * @version 2020-2.6.1
 */
namespace SbmCommun\Model\Db\Service\Table;

use SbmCommun\Model\Db\ObjectData\ObjectDataInterface;
use SbmCommun\Model\Db\ObjectData\Exception as ExceptionObjectData;

class Invites extends AbstractSbmTable
{

    protected function init()
    {
        $this->table_name = 'invites';
        $this->table_type = 'table';
        $this->table_gateway_alias = 'Sbm\Db\TableGateway\Invites';
        $this->id_name = 'inviteId';
    }

    public function saveRecord(ObjectDataInterface $obj_data)
    {
        try {
            $old_data = $this->getRecord($obj_data->getId());
            if ($obj_data->isUnchanged($old_data)) {
                return false;
            }
            try {
                if ($old_data->nom != $obj_data->nom) {
                    $obj_data->addCalculateField('nomSA');
                }
            } catch (ExceptionObjectData\ExceptionInterface $e) {
            }
            try {
                if ($old_data->prenom != $obj_data->prenom) {
                    $obj_data->addCalculateField('prenomSA');
                }
            } catch (ExceptionObjectData\ExceptionInterface $e) {
            }
            $obj_data->addCalculateField('dateModification');
        } catch (Exception\ExceptionInterface $e) {
            $obj_data->setCalculateFields([
                'nomSA',
                'prenomSA',
                'dateCreation'
            ]);
        }
        //$obj_data->debug_dump(false, true);
        return parent::saveRecord($obj_data);
    }
}