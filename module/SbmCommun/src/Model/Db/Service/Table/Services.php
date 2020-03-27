<?php
/**
 * Gestion de la table `services`
 * (à déclarer dans module.config.php)
 *
 * Version pour TRANSDEV ALBERTVILLE
 *
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Model/Db/Table
 * @filesource Services.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 27 mars 2020
 * @version 2020-2.6.0
 */
namespace SbmCommun\Model\Db\Service\Table;

// use SbmCommun\Model\Strategy\NatureCarte as NatureCarteStrategy;
use SbmCommun\Model\Strategy\Semaine as SemaineStrategy;

class Services extends AbstractSbmTable implements EffectifInterface
{
    use OutilsMillesimeTrait;

    /**
     * Initialisation du service
     */
    protected function init()
    {
        $this->table_name = 'services';
        $this->table_type = 'table';
        $this->table_gateway_alias = 'Sbm\Db\TableGateway\Services';
        $this->id_name = [
            'millesime',
            'ligneId',
            'sens',
            'moment',
            'ordre'
        ];
        $this->strategies['semaine'] = new SemaineStrategy();
    }

    public function getNatureCartes()
    {
        // return $this->strategies['natureCarte']->getNatureCartes();
        return [];
    }

    /**
     * Change l'état de la colonne selection pour le service indiqué.
     *
     * @param int $millesime
     * @param string $ligneId
     * @param int $sens
     * @param int $moment
     * @param int $ordre
     * @param int $selection
     */
    public function setSelection(int $millesime, string $ligneId, int $sens, int $moment,
        int $ordre, int $selection)
    {
        $oData = $this->getObjData();
        $oData->exchangeArray(
            [
                'millesime' => $millesime,
                'ligneId' => $ligneId,
                'sens' => $sens,
                'moment' => $moment,
                'ordre' => $ordre,
                'selection' => $selection
            ]);
        parent::saveRecord($oData);
    }
}

