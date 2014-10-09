<?php
/**
 * Gestion de la table système `documents`
 *
 * (à déclarer dans module.config.php)
 *
 * @project sbm
 * @package SbmCommun/Model/Service/Table/Sys
 * @filesource Documents.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 11 août 2014
 * @version 2014-1
 */
namespace SbmCommun\Model\Db\Service\Table\Sys;

use SbmCommun\Model\Db\Service\Table\AbstractSbmTable;

class Documents extends AbstractSbmTable
{
    /**
     * Initialisation de la classe
     */
    protected function init()
    {
        $this->table_name = 'documents';
        $this->table_type = 'system';
        $this->table_gateway_alias = 'Sbm\Db\SysTableGateway\Documents';
        $this->id_name = 'documentId';
    }
    
    public function getConfig($documentId)
    {
        return $this->getRecord($documentId)->getArrayCopy();
    }
}