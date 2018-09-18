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
 * @date 10 sept. 2018
 * @version 2018-2.4.5
 */
namespace SbmCommun\Model\Db\Service\Table\Sys;

use SbmCommun\Model\Db\Service\Table\AbstractSbmTable;
use SbmCommun\Model\Db\Service\Table\Exception;
use SbmCommun\Model\Strategy\Color;

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

    protected function setStrategies()
    {
        foreach ($this->getColumnsNames() as $columnName) {
            if (substr($columnName, - 6) == '_color') {
                $this->hydrator->addStrategy($columnName, new Color());
            }
        }
    }

    /**
     * Renvoie un tableau de 3 enregistrements correspondant aux sections 'thead', 'tbody' et
     * 'tfoot' pour un $documentId donné.
     *
     * @param int $documentId
     *            identifiant du document
     * @param int $ordinal_table
     *            numéro de la table dans le document
     *            
     * @throws Exception s'il n'y a pas d'enregistrement pour de $documentId
     *        
     * @param array $documentId
     *            array('thead' => enregistrement, 'tbody' => enregistrement, 'tfoot' =>
     *            enregistrement) où enregistrement est un tableau
     */
    public function getConfig($documentId, $ordinal_table)
    {
        $where = "documentId = $documentId AND ordinal_table = $ordinal_table";
        $resultset = $this->fetchAll($where);
        if (! $resultset->count()) {
            throw new Exception(
                sprintf(_("Could not find rows '%s' in table %s"), $where,
                    $this->table_name));
        }
        $result = [
            'thead' => [],
            'tbody' => [],
            'tfoot' => []
        ];
        foreach ($resultset as $row) {
            $result[$row->section] = $row->getArrayCopy();
        }
        return $result;
    }
}