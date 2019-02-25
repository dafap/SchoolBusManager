<?php
/**
 * Gestion de la table système `doclabels`
 * (à déclarer dans module.config.php)
 * 
 * Attention ! La liaison entre les tables `doclabels` et `document` étant de type 0<->1
 * on remplace la clé primaire pas documentId.
 * 
 * @project sbm
 * @package SbmCommun/Model/Service/Table/Sys
 * @filesource DocLabels.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 24 fév. 2019
 * @version 2019-2.5.0
 */
namespace SbmCommun\Model\Db\Service\Table\Sys;

use SbmCommun\Model\Db\Service\Table\AbstractSbmTable;
use SbmCommun\Model\Db\Service\Table\Exception;
use SbmCommun\Model\Strategy\Color;

class DocLabels extends AbstractSbmTable
{

    /**
     * Initialisation de la classe
     */
    protected function init()
    {
        $this->table_name = 'doclabels';
        $this->table_type = 'system';
        $this->table_gateway_alias = 'Sbm\Db\SysTableGateway\DocLabels';
        $this->id_name = 'documentId';
        foreach ($this->getColumnsNames() as $columnName) {
            if (substr($columnName, - 6) == '_color') {
                $this->strategies[$columnName] = new Color();
            }
        }
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
        $resultset = $this->fetchAll($where);
        if (! $resultset->count()) {
            throw new Exception\RuntimeException(
                sprintf(_("Could not find rows '%s' in table %s"), $where,
                    $this->table_name));
        }
        $result = $resultset->current()->getArrayCopy();
        return $result;
    }
}