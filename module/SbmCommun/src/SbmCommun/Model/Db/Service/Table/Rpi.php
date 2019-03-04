<?php
/**
 * Gestion de la table `rpi`
 * (à déclarer dans module.config.php)
 * 
 * @project sbm
 * @package SbmCommun/Model/Db/Table
 * @filesource Rpi.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 25 fév. 2019
 * @version 2019-2.4.8
 */
namespace SbmCommun\Model\Db\Service\Table;

use SbmCommun\Model\Strategy\Niveau as NiveauStrategy;

class Rpi extends AbstractSbmTable
{

    /**
     * Initialisation du service
     */
    protected function init()
    {
        $this->table_name = 'rpi';
        $this->table_type = 'table';
        $this->table_gateway_alias = 'Sbm\Db\TableGateway\Rpi';
        $this->id_name = 'rpiId';
        $this->strategies['niveau'] = new NiveauStrategy();
    }
    
    /**
     * Coche ou décoche la sélection
     *
     * @param int $rpiId
     * @param bool $selection
     */
    public function setSelection($rpiId, $selection)
    {
        $oData = $this->getObjData();
        $oData->exchangeArray(
            [
                'rpiId' => $rpiId,
                'selection' => $selection
            ]);
        parent::saveRecord($oData);
    }
}