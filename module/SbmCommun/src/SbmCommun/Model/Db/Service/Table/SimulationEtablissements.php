<?php
/**
 * Gestion de la table `rpi-etablissements`
 * (à déclarer dans module.config.php)
 * 
 * @project sbm
 * @package SbmCommun/Model/Db/Table
 * @filesource SimulationEtablissements.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 23 août 2018
 * @version 2018-2.4.3
 */
namespace SbmCommun\Model\Db\Service\Table;

class SimulationEtablissements extends AbstractSbmTable
{

    /**
     * Initialisation du service
     */
    protected function init()
    {
        $this->table_name = 'simulation-etablissements';
        $this->table_type = 'table';
        $this->table_gateway_alias = 'Sbm\Db\TableGateway\SimulationEtablissements';
        $this->id_name = 'origineId';
    }
}