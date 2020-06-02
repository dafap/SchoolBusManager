<?php
/**
 * Gestion de la table `esendexbatches`
 * (à déclarer dans module.config.php)
 *
 * @project sbm
 * @package SbmEsendex/src/Model/Db/Service/Table
 * @filesource EsendexBatches.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 29 mai 2020
 * @version 2020-2.6.0
 */
namespace SbmEsendex\Model\Db\Service\Table;

use SbmCommun\Model\Db\Service\Table\AbstractSbmTable;

class EsendexBatches extends AbstractSbmTable
{
    protected function init()
    {
        $this->table_name = 'esendexbatches';
        $this->table_type = 'table';
        $this->table_gateway_alias = 'Sbm\Db\TableGateway\EsendexBatches';
        $this->id_name = 'esendexbatchId';
    }
}