<?php
/**
 * Gestion de la table `classes`
 * (à déclarer dans module.config.php)
 *
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Model/Db/Table
 * @filesource Classes.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 7 mars 2019
 * @version 2019-2.5.0
 */
namespace SbmCommun\Model\Db\Service\Table;

use SbmCommun\Model\Strategy\Niveau as NiveauStrategy;

class Classes extends AbstractSbmTable implements EffectifInterface
{

    /**
     * Initialisation de la classe
     */
    protected function init()
    {
        $this->table_name = 'classes';
        $this->table_type = 'table';
        $this->table_gateway_alias = 'Sbm\Db\TableGateway\Classes';
        $this->id_name = 'classeId';
        $this->strategies['niveau'] = new NiveauStrategy();
    }

    public function getNiveaux()
    {
        return [
            NiveauStrategy::CODE_NIVEAU_MATERNELLE => 'maternelle',
            NiveauStrategy::CODE_NIVEAU_ELEMENTAIRE => 'élémentaire',
            NiveauStrategy::CODE_NIVEAU_PREMIER_CYCLE => 'premier cycle',
            NiveauStrategy::CODE_NIVEAU_SECOND_CYCLE => 'second cycle',
            NiveauStrategy::CODE_NIVEAU_POST_BAC => 'post bac',
            NiveauStrategy::CODE_NIVEAU_SUPERIEUR => 'ens. supérieur',
            NiveauStrategy::CODE_NIVEAU_AUTRE => 'autres'
        ];
    }

    public function setSelection($classeId, $selection)
    {
        $oData = $this->getObjData();
        $oData->exchangeArray([
            'classeId' => $classeId,
            'selection' => $selection
        ]);
        parent::saveRecord($oData);
    }
}

