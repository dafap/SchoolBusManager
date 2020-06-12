<?php
/**
 * Gestion de la table `zonage`
 * (à déclarer dans module.config.php)
 *
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Model/Db/Table
 * @filesource Zonage.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 12 juin 2020
 * @version 2020-2.5.4
 */
namespace SbmCommun\Model\Db\Service\Table;

use SbmCommun\Model\Db\ObjectData\ObjectDataInterface;

class Zonage extends AbstractSbmTable
{

    /**
     * Initialisation du transporteur
     */
    protected function init()
    {
        $this->table_name = 'zonage';
        $this->table_type = 'table';
        $this->table_gateway_alias = 'Sbm\Db\TableGateway\Zonage';
        $this->id_name = 'zonageId';
    }

    /**
     * Enregistre les données en complétant s'il le faut le champ 'nomSA' et renvoie le
     * zonageId
     *
     * {@inheritdoc}
     * @see \SbmCommun\Model\Db\Service\Table\AbstractSbmTable::saveRecord()
     */
    public function saveRecord(ObjectDataInterface $obj_data)
    {
        try {
            $old_data = $this->getRecord($obj_data->getId());
            $is_new = false;
        } catch (\Exception $e) {
            $is_new = true;
        }
        if ($is_new) {
            $obj_data->addCalculateField('nomSA');
        } else {
            if (! $obj_data->isUnchanged($old_data)) {
                if ($old_data->nom != $obj_data->nom) {
                    $obj_data->addCalculateField('nomSA');
                }
            }
        }
        parent::saveRecord($obj_data);
        if ($is_new) {
            return $this->getTableGateway()->getLastInsertValue();
        } else {
            return $obj_data->getId();
        }
    }

    /**
     *
     * @param int $zonageId
     * @param bool $value
     */
    public function setSelection($zonageId, $value)
    {
        $oData = $this->getObjData();
        $oData->exchangeArray([
            'zonageId' => $zonageId,
            'selection' => $value
        ]);
        parent::updateRecord($oData);
    }

    /**
     *
     * @param int $zonageId
     * @param bool $value
     */
    public function setInscriptionEnLigne($zonageId, $value)
    {
        $oData = $this->getObjData();
        $oData->exchangeArray([
            'zonageId' => $zonageId,
            'inscriptionenligne' => $value
        ]);
        parent::updateRecord($oData);
    }

    /**
     *
     * @param int $zonageId
     * @param bool $value
     */
    public function setPaiementEnLigne($zonageId, $value)
    {
        $oData = $this->getObjData();
        $oData->exchangeArray([
            'zonageId' => $zonageId,
            'paiementenligne' => $value
        ]);
        parent::updateRecord($oData);
    }
}

