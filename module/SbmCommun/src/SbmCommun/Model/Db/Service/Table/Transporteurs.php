<?php
/**
 * Gestion de la table `transporteurs`
 * (à déclarer dans module.config.php)
 *
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Model/Db/Table
 * @filesource Transporteurs.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 17 févr. 2014
 * @version 2014-1
 */
namespace SbmCommun\Model\Db\Service\Table;

class Transporteurs extends AbstractSbmTable
{
    /**
     * Initialisation du transporteur
     */
    protected function init()
    {
        $this->table_name = 'transporteurs';
        $this->table_type = 'table';
        $this->table_gateway_alias = 'Sbm\Db\TableGateway\Transporteurs';
        $this->id_name = 'transporteurId';
    }
    
    public function setSelection($transporteurId, $selection)
    {
        $oData = $this->getObjData();
        $oData->exchangeArray(array(
            'transporteurId' => $transporteurId,
            'selection' => $selection
        ));
        parent::saveRecord($oData);
    }
}

