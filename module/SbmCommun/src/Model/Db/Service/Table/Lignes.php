<?php
/**
 * Gestion de la table `lignes`
 * (à déclarer dans module.config.php)
 *
 * Version pour TRANSDEV ALBERTVILLE
 *
 * @project sbm
 * @package SbmCommun/src/Model/Db/Service/Table
 * @filesource Lignes.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 16 fév. 2020
 * @version 2020-2.6.0
 */
namespace SbmCommun\Model\Db\Service\Table;

class Lignes extends AbstractSbmTable implements EffectifInterface
{

    /**
     * Initialisation du service
     */
    protected function init()
    {
        $this->table_name = 'lignes';
        $this->table_type = 'table';
        $this->table_gateway_alias = 'Sbm\Db\TableGateway\Lignes';
        $this->id_name = [
            'millesime',
            'ligneId'
        ];
    }

    /**
     * Coche ou décoche la sélection
     *
     * @param int $millesime
     * @param string $ligneId
     * @param bool $selection
     */
    public function setSelection(int $millesime, string $ligneId, bool $selection)
    {
        $oData = $this->getObjData();
        $oData->exchangeArray(
            [
                'millesime' => $millesime,
                'ligneId' => $ligneId,
                'selection' => $selection
            ]);
        parent::saveRecord($oData);
    }

    /**
     * Soit on passe un tableau des valeurs correspondant à la propriété id_name (millesime, ligneId),
     * soit on passe une chaine contenant ces valeurs séparées par |
     *
     * (non-PHPdoc)
     *
     * @see \SbmCommun\Model\Db\Service\Table\AbstractSbmTable::getRecord()
     */
    public function getRecord($id)
    {
        if (is_array($id)) {
            return parent::getRecord($id);
        } elseif (is_string($id)) {
            list ($nature, $code) = explode('|', $id);
            return parent::getRecord([
                'millesime' => $nature,
                'ligneId' => $code
            ]);
        }
    }
}