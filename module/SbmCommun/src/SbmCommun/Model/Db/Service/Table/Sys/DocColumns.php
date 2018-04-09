<?php
/**
 * Gestion de la table système `doccolumns`
 *
 * (à déclarer dans module.config.php)
 * 
 * @project project_name
 * @package package_name
 * @filesource DocColumns.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 4 avr. 2018
 * @version 2018-2.4.0
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

    public function getConfig($documentId, $ordinal_table)
    {
        $where = "documentId = $documentId AND ordinal_table = $ordinal_table";
        $resultset = $this->fetchAll($where, 'ordinal_position');
        if (! $resultset->count()) {
            throw new Exception(
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