<?php
/**
 * Gestion de la table `services`
 * (à déclarer dans module.config.php)
 *
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Model/Db/Table
 * @filesource Services.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 17 févr. 2014
 * @version 2014-1
 */
namespace SbmCommun\Model\Db\Service\Table;

class Services extends AbstractSbmTable
{
    /**
     * Initialisation du service
     */
    protected function init()
    {
        $this->table_name = 'services';
        $this->table_type = 'table';
        $this->table_gateway_alias = 'Sbm\Db\TableGateway\Services';
        $this->id_name = 'serviceId';
    }
    
    public function setSelection($serviceId, $selection)
    {
        $oData = $this->getObjData();
        $oData->exchangeArray(array(
            'serviceId' => $serviceId,
            'selection' => $selection
        ));
        parent::saveRecord($oData);
    }
}

