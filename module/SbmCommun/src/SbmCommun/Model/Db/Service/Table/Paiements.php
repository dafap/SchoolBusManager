<?php
/**
 * Gestion de la table `paiements`
 * (à déclarer dans module.config.php)
 *
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Model/Db/Table
 * @filesource Paiements.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 20 jan. 2015
 * @version 2015-1
 */
namespace SbmCommun\Model\Db\Service\Table;

use SbmCommun\Model\Db\ObjectData\ObjectDataInterface;
use Zend\Db\Sql\Where;

class Paiements extends AbstractSbmTable
{

    /**
     * Initialisation de la station
     */
    protected function init()
    {
        $this->table_name = 'paiements';
        $this->table_type = 'table';
        $this->table_gateway_alias = 'Sbm\Db\TableGateway\Paiements';
        $this->id_name = 'paiementId';
    }

    public function saveRecord(ObjectDataInterface $obj_data)
    {
        if (empty($obj_data->dateValeur)) {
            $dte = new \DateTime($obj_data->datePaiement);
            $obj_data->dateValeur = $dte->format('Y-m-d');
        }
        parent::saveRecord($obj_data);
    }
    
    public function setSelection($paiementId, $selection)
    {
        $oData = $this->getObjData();
        $oData->exchangeArray(array('paiementId' => $paiementId, 'selection' => $selection));
        parent::saveRecord($oData);
    }

    /**
     * Renvoie le paiementId d'un paiement pour les paramètres indiqués.
     * Renvoie null si le paiement n'est pas enregistré
     *
     * @param int $responsableId            
     * @param string $datePaiement            
     * @param string $reference            
     *
     * @return integer
     */
    public function getPaiementId($responsableId, $datePaiement, $reference)
    {
        $where = new Where();
        $rowset = $this->fetchAll($where->equalTo('responsableId', $responsableId)
            ->equalTo('datePaiement', $datePaiement)
            ->equalTo('reference', $reference));
        if ($rowset && $rowset->current()) {
            return $rowset->current()->paiementId;
        } else {
            return null;
        }
    }
}

