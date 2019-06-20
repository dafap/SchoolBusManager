<?php
/**
 * Gestion de la table `appels`
 * (à déclarer dans module.config.php)
 *
 * Il s'agit des appels à la plateforme de paiement pour essayer de payer.
 * Cette table établit la liaison entre le payeur et les élèves concernés.
 *
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Model/Db/Table
 * @filesource Appels.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 20 juin 2015
 * @version 2015-2.5.0
 */
namespace SbmCommun\Model\Db\Service\Table;

class Appels extends AbstractSbmTable
{

    /**
     * Initialisation du service
     */
    protected function init()
    {
        $this->table_name = 'appels';
        $this->table_type = 'table';
        $this->table_gateway_alias = 'Sbm\Db\TableGateway\Appels';
        $this->id_name = 'appelId';
    }

    public function markNotified($idOp)
    {
        return $this->table_gateway->update([
            'notified' => 1
        ], [
            'idOp' => $idOp
        ]);
    }

    public function saveRecord($obj_data)
    {
        $rowset = $this->fetchAll([
            'refdet' => $obj_data->refdet,
            'notified' => 0
        ]);
        if ($rowset->count()) {
            throw new Exception\RuntimeException('Un appel non notifié existe déjà pour cette dette.');
        }
        return parent::saveRecord($obj_data);
    }
}