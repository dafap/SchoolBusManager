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
 * @date 27 sept. 2018
 * @version 2019-2.5.0
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
    }

    /**
     * (non-PHPdoc)
     *
     * @see \SbmCommun\Model\Db\Service\Table\AbstractSbmTable::setStrategies()
     */
    protected function setStrategies()
    {
        $this->hydrator->addStrategy('niveau', new NiveauStrategy());
    }

    public static function getNiveaux()
    {
        return [
            NiveauStrategy::CODE_NIVEAU_MATERNELLE => 'maternelle',
            NiveauStrategy::CODE_NIVEAU_ELEMENTAIRE => 'élémentaire'
        ];
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
        $oData->exchangeArray([
            'rpiId' => $rpiId,
            'selection' => $selection
        ]);
        parent::saveRecord($oData);
    }
}