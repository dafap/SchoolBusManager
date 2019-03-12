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
 * @date 7 mars 2019
 * @version 2019-2.5.0
 */
namespace SbmCommun\Model\Db\Service\Table;

use SbmCommun\Model\Strategy\Semaine as SemaineStrategy;
use Zend\Db\Sql\Expression;

class Circuits extends AbstractSbmTable implements EffectifInterface
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
        $this->strategies['semaine'] = new SemaineStrategy();
    }

    public function getSemaine()
    {
        return [
            SemaineStrategy::CODE_SEMAINE_LUNDI => 'lun',
            SemaineStrategy::CODE_SEMAINE_MARDI => 'mar',
            SemaineStrategy::CODE_SEMAINE_MERCREDI => 'mer',
            SemaineStrategy::CODE_SEMAINE_JEUDI => 'jeu',
            SemaineStrategy::CODE_SEMAINE_VENDREDI => 'ven',
            SemaineStrategy::CODE_SEMAINE_SAMEDI => 'sam',
            SemaineStrategy::CODE_SEMAINE_DIMANCHE => 'dim'
        ];
    }

    public function setSelection($circuitId, $selection)
    {
        $oData = clone $this->getObjData();
        $oData->exchangeArray([
            'circuitId' => $circuitId,
            'selection' => $selection
        ]);
        parent::saveRecord($oData);
    }

    public function getCircuit($millesime, $serviceId, $stationId)
    {
        return $this->getRecord(
            [
                'millesime' => $millesime,
                'serviceId' => $serviceId,
                'stationId' => $stationId
            ]);
    }

    /**
     * Renvoie vrai si la table ne contient pas de données pour ce millésime.
     *
     * @param int $millesime
     *
     * @return boolean
     */
    public function isEmptyMillesime($millesime)
    {
        $resultset = $this->fetchAll([
            'millesime' => $millesime
        ]);
        return $resultset->count() == 0;
    }

    /**
     * Supprime tous les enregistrements concernant le millesime indiqué.
     *
     * @param int $millesime
     *
     * @return int
     */
    public function viderMillesime($millesime)
    {
        return $this->table_gateway->delete([
            'millesime' => $millesime
        ]);
    }

    /**
     * Renvoie le dernier millesime utilisé dans la table des circuits
     *
     * @return int
     */
    public function getDernierMillesime()
    {
        $select = $this->getTableGateway()
            ->getSql()
            ->select();
        $select->columns([
            'millesime' => new Expression('max(millesime)')
        ]);
        $resultset = $this->getTableGateway()->selectWith($select);
        $row = $resultset->current();
        return $row->millesime;
    }
}

