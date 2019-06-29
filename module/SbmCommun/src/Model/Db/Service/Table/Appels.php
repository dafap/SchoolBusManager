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

use SbmCommun\Model\Db\ObjectData\ObjectDataInterface;

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

    /**
     * On interdit d'enregistrer un appel si un enregistrement non notifié existe pour le
     * même élève avec la même refdet. Dans ce cas, on lance une RuntimeException
     * contenant le idOp du premier enregistrement trouvé en message.
     *
     * {@inheritdoc}
     * @see \SbmCommun\Model\Db\Service\Table\AbstractSbmTable::saveRecord()
     */
    public function saveRecord(ObjectDataInterface $obj_data)
    {
        $rowset = $this->fetchAll(
            [
                'refdet' => $obj_data->refdet,
                'eleveId' => $obj_data->eleveId,
                'notified' => 0
            ]);
        if ($rowset->count()) {
            $row = $rowset->current();
            throw new Exception\RuntimeException($row->idOp);
        }
        return parent::saveRecord($obj_data);
    }
}