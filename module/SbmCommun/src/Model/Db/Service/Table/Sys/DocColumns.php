<?php
/**
 * Gestion de la table système `doccolumns`
 *
 * (à déclarer dans module.config.php)
 *
 * @project sbm
 * @package SbmCommun/src/Model/Db/Service/Table/AbstractSbmTable
 * @filesource DocColumns.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 7 août 2021
 * @version 2021-2.6.3
 */
namespace SbmCommun\Model\Db\Service\Table\Sys;

use SbmCommun\Model\Db\Service\Table\AbstractSbmTable;
use SbmCommun\Model\Db\Service\Table\Exception;

class DocColumns extends AbstractSbmTable
{

    /**
     * Initialisation de la classe
     */
    protected function init()
    {
        $this->table_name = 'doccolumns';
        $this->table_type = 'system';
        $this->table_gateway_alias = 'Sbm\Db\SysTableGateway\DocColumns';
        $this->id_name = 'doccolumnId';
    }

    /**
     * Renvoie la description des champs des datas de ce document
     *
     * @param int $documentId
     * @param int $ordinal_table
     *
     * @throws \SbmCommun\Model\Db\Service\Table\Exception\RuntimeException
     *
     * @return array
     */
    public function getConfig(int $documentId, int $ordinal_table = 1): array
    {
        $where = "documentId = $documentId AND ordinal_table = $ordinal_table";
        $resultset = $this->fetchAll($where, 'ordinal_position');
        if (! $resultset->count()) {
            throw new Exception\RuntimeException(
                sprintf(_("Could not find rows '%s' in table %s"), $where,
                    $this->table_name));
        }
        $result = [];
        foreach ($resultset as $row) {
            $result[] = $row->getArrayCopy();
        }
        return $result;
    }
}