<?php
/**
 * Gestion de la table `lots`
 * (à déclarer dans module.config.php)
 *
 * @project sbm
 * @package SbmCommun/src/Model/Db/Service/Table
 * @filesource Lots.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 24 mars 2019
 * @version 2019-2.5.0
 */
namespace SbmCommun\Model\Db\Service\Table;

class Lots extends AbstractSbmTable implements EffectifInterface
{

    /**
     * Initialisation du service
     */
    protected function init()
    {
        $this->table_name = 'lots';
        $this->table_type = 'table';
        $this->table_gateway_alias = 'Sbm\Db\TableGateway\Lots';
        $this->id_name = 'lotId';
    }

    /**
     * Coche ou décoche la sélection
     *
     * @param int $lotId
     * @param bool $selection
     */
    public function setSelection($lotId, $selection)
    {
        $oData = $this->getObjData();
        $oData->exchangeArray([
            'lotId' => $lotId,
            'selection' => $selection
        ]);
        parent::saveRecord($oData);
    }
}