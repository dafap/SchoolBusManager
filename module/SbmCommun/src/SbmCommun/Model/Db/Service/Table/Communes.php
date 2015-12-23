<?php
/**
 * Gestion de la table `communes`
 * (à déclarer dans module.config.php)
 *
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Model/Db/Table
 * @filesource Communes.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 17 févr. 2014
 * @version 2014-1
 */
namespace SbmCommun\Model\Db\Service\Table;

class Communes extends AbstractSbmTable
{
    /**
     * Initialisation de la commune
     */
    protected function init()
    {
        $this->table_name = 'communes';
        $this->table_type = 'table';
        $this->table_gateway_alias = 'Sbm\Db\TableGateway\Communes';
        $this->id_name = 'communeId';
    }
    
    /**
     * Renvoie l'identifiant d'une commune desservie dont on donne le nom
     * 
     * @param string $nom
     * 
     * @return string Code INSEE de la commune
     */
    public function getCommuneId($nom)
    {
        $result = $this->fetchAll(array('nom' => $nom, 'desservie' => 1));
        if (! is_null($result)) {
            return $result->current()->communeId;
        } else {
            return null;
        }
    }
    
    public function getCodePostal($communeId)
    {
        if (!empty($communeId)) {
            try {
                $c = $this->getRecord($communeId);
                return $c->codePostal;
            } catch (Exception $e) {
                // $communeId n'a pas été trouvée
                return '';
            }
        } else {
            return '';
        }
    }
    
    public function setVisible($communeId)
    {
        $oData = $this->getObjData();
        $oData->exchangeArray(array('communeId' => $communeId, 'visible' => 1));
        $this->saveRecord($oData);
    }
    
    public function setSelection($communeId, $selection)
    {
        $oData = $this->getObjData();
        $oData->exchangeArray(array(
            'communeId' => $communeId,
            'selection' => $selection
        ));
        parent::saveRecord($oData);
    }
}

