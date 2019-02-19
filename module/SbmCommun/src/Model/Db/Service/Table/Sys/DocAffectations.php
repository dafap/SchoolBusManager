<?php
/**
 * Gestion de la table `docaffectations`
 *
 *
 * @project sbm
 * @package SbmCommun/Model/Service/Table/Sys
 * @filesource DocAffectations.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 26 oct 2018
 * @version 2019-2.5.0
 */
namespace SbmCommun\Model\Db\Service\Table\Sys;

use SbmCommun\Model\Db\Service\Table\AbstractSbmTable;
use SbmCommun\Model\Db\Service\Table\Exception;

class DocAffectations extends AbstractSbmTable
{

    /**
     * Initialisation de la classe
     */
    protected function init()
    {
        $this->table_name = 'docaffectations';
        $this->table_type = 'system';
        $this->table_gateway_alias = 'Sbm\Db\SysTableGateway\DocAffectations';
        $this->id_name = 'docaffectationId';
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
        $resultset = $this->fetchAll($where, 'route');
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