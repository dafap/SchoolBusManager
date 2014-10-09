<?php
/**
 * Gestion de la table système `doctables`
 * 
 * (à déclarer dans module.config.php)
 *
 * @project sbm
 * @package SbmCommun/Model/Service/Table/Sys
 * @filesource DocTables.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 19 sept 2014
 * @version 2014-1
 */
namespace SbmCommun\Model\Db\Service\Table\Sys;

use SbmCommun\Model\Db\Service\Table\AbstractSbmTable;
use SbmCommun\Model\Db\Service\Table\Exception;
use Zend\Db\ResultSet\ResultSet;

class DocTables extends AbstractSbmTable
{
    /**
     * Initialisation de la classe
     */
    protected function init()
    {
        $this->table_name = 'doctables';
        $this->table_type = 'system';
        $this->table_gateway_alias = 'Sbm\Db\SysTableGateway\DocTables';
        $this->id_name = 'doctableId';
    }
    
    /**
     * Renvoie un tableau de 3 enregistrements correspondant aux sections 'thead', 'tbody' et 'tfoot' pour un $documentId donné.
     * 
     * @param int $documentId
     *          identifiant du document
     * @param int $ordinal_table 
     *          numéro de la table dans le document
     * 
     * @throws Exception
     *           s'il n'y a pas d'enregistrement pour de $documentId
     * 
     * @param array $documentId
     *           array('thead' => enregistrement, 'tbody' => enregistrement, 'tfoot' => enregistrement) où enregistrement est un tableau
     */
    public function getConfig($documentId, $ordinal_table)
    {
        $where = "documentId = $documentId AND ordinal_table = $ordinal_table";
        $resultset = $this->fetchAll($where);
        if (! $resultset->count()) {
            throw new Exception(sprintf(_("Could not find rows '%s' in table %s"), $where, $this->table_name));
        }
        $result = array('thead' => array(), 'tbody' => array(), 'tfoot' => array());
        foreach ($resultset as $row) {
            $result[$row->section] = $row->getArrayCopy();
        }
        return $result;
    }
}