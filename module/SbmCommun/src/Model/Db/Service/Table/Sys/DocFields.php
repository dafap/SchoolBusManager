<?php
/**
 * Gestion de la table système `docfields`
 * (à déclarer dans module.config.php)
 *
 * @project sbm
 * @package SbmCommun/Model/Service/Table/Sys
 * @filesource DocFields.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 26 oct 2018
 * @version 2019-2.5.0
 */
namespace SbmCommun\Model\Db\Service\Table\Sys;

use SbmCommun\Model\Db\Service\Table\AbstractSbmTable;
use SbmCommun\Model\Db\Service\Table\Exception;

class DocFields extends AbstractSbmTable
{

    /**
     * Initialisation de la classe
     */
    protected function init()
    {
        $this->table_name = 'docfields';
        $this->table_type = 'system';
        $this->table_gateway_alias = 'Sbm\Db\SysTableGateway\DocFields';
        $this->id_name = 'docfieldId';
    }

    /**
     *
     * @param int $documentId
     *
     * @throws \SbmCommun\Model\Db\Service\Table\Exception\RuntimeException
     *
     * @return array
     */
    public function getConfig($documentId)
    {
        $where = "documentId = $documentId";
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