<?php
/**
 * Gestion de la table `circuits`
 * (à déclarer dans module.config.php)
 *
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Model/Db/Table
 * @filesource Circuits.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 17 févr. 2014
 * @version 2014-1
 */
namespace SbmCommun\Model\Db\Service\Table;

use SbmCommun\Model\Strategy\Semaine as SemaineStrategy;

class Circuits extends AbstractSbmTable
{

    /**
     * Initialisation du circuit
     */
    protected function init()
    {
        $this->table_name = 'circuits';
        $this->table_type = 'table';
        $this->table_gateway_alias = 'Sbm\Db\TableGateway\Circuits';
        $this->id_name = 'circuitId';
    }

    /**
     * (non-PHPdoc)
     *
     * @see \SbmCommun\Model\Db\Service\Table\AbstractSbmTable::setStrategies()
     */
    protected function setStrategies()
    {
        $this->hydrator->addStrategy('semaine', new SemaineStrategy());
    }

    public function getSemaine()
    {
        return array(
            SemaineStrategy::CODE_SEMAINE_LUNDI => 'lun',
            SemaineStrategy::CODE_SEMAINE_MARDI => 'mar',
            SemaineStrategy::CODE_SEMAINE_MERCREDI => 'mer',
            SemaineStrategy::CODE_SEMAINE_JEUDI => 'jeu',
            SemaineStrategy::CODE_SEMAINE_VENDREDI => 'ven',
            SemaineStrategy::CODE_SEMAINE_SAMEDI => 'sam',
            SemaineStrategy::CODE_SEMAINE_DIMANCHE => 'dim'
        );
    }

    public function setSelection($circuitId, $selection)
    {
        $oData = clone $this->getObjData();
        $oData->exchangeArray(array(
            'circuitId' => $circuitId,
            'selection' => $selection
        ));
        parent::saveRecord($oData);
    }

    public function getCircuit($millesime, $serviceId, $stationId)
    {
        return $this->getRecord(array('millesime' => $millesime, 'serviceId' => $serviceId, 'stationId' => $stationId));
    }
}

