<?php
/**
 * Service donnant un Tablegateway pour le table RpiEtablissements
 * (à déclarer dans module.config.php)
 *
 * @project sbm
 * @package SbmCommun/Model/Db/Service/TableGateway
 * @filesource TableGatewaySimulationEtablissements.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 24 août 2018
 * @version 2018-2.4.3
 */
namespace SbmCommun\Model\Db\Service\TableGateway;

class TableGatewaySimulationEtablissements extends AbstractSbmTableGateway
{

    protected function init()
    {
        $this->table_name = 'simulation-etablissements';
        $this->type = 'table';
        $this->data_object_alias = 'Sbm\Db\ObjectData\SimulationEtablissement';
    }
}