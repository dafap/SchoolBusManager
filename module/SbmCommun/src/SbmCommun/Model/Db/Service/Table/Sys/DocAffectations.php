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
 * @date 19 sept 2014
 * @version 2014-1
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

    public function getConfig($documentId)
    {
        $where = "documentId = $documentId";
        $resultset = $this->fetchAll($where, 'route');
        if (! $resultset->count()) {
            throw new Exception(sprintf(_("Could not find rows '%s' in table %s"), $where, $this->table_name));
        }
        $result = array();
        foreach ($resultset as $row) {
            $result[] = $row->getArrayCopy();
        }
        return $result;
    }
}