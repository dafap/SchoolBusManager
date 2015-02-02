<?php
/**
 * Gestion de la table `paiements`
 *
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
}

