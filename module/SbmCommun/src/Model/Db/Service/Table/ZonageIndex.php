<?php
/**
 * Gestion de la table `zonage-index`
 * (à déclarer dans module.config.php)
 *
 * @project sbm
 * @package module/SbmCommun/src/SbmCommun/Model/Db/Table
 * @filesource ZonageIndex.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 11 juin 2020
 * @version 2020-2.5.4
 */
namespace SbmCommun\Model\Db\Service\Table;

class ZonageIndex extends AbstractSbmTable
{


    /**
     * Initialisation du transporteur
     */
    protected function init()
    {
        $this->table_name = 'zonage-index';
        $this->table_type = 'table';
        $this->table_gateway_alias = 'Sbm\Db\TableGateway\ZonageIndex';
        $this->id_name = [
            'zonageId',
            'communeId',
            'mot'
        ];
    }
}

