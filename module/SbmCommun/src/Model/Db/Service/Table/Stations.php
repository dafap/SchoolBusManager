<?php
/**
 * Gestion de la table `stations`
 * (à déclarer dans module.config.php)
 *
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Model/Db/Table
 * @filesource Stations.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 10 sept. 2018
 * @version 2018-2.4.5
 */
namespace SbmCommun\Model\Db\Service\Table;

class Stations extends AbstractSbmTable
{

    /**
     * Initialisation de la station
     */
    protected function init()
    {
        $this->table_name = 'stations';
        $this->table_type = 'table';
        $this->table_gateway_alias = 'Sbm\Db\TableGateway\Stations';
        $this->id_name = 'stationId';
    }

    public function setSelection($stationId, $selection)
    {
        $oData = $this->getObjData();
        $oData->exchangeArray([
            'stationId' => $stationId,
            'selection' => $selection
        ]);
        parent::saveRecord($oData);
    }
}

