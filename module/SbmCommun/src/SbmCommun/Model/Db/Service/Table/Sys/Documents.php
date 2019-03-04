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
 * @date 25 fév. 2019
 * @version 2019-2.4.8
 */
namespace SbmCommun\Model\Db\Service\Table\Sys;

use SbmCommun\Model\Db\Service\Table\AbstractSbmTable;
use SbmCommun\Model\Strategy\Color;
use Zend\Db\Sql\Where;

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
        foreach ($this->getColumnsNames() as $columnName) {
            if (substr($columnName, - 6) == '_color') {
                $this->strategies[$columnName] = new Color();
            }
        }
    }

    /**
     * Renvoie un tableau associatif contenant la fiche du document demandé
     *
     * @param int|string $documentId
     *            Si $documentId est une chaine non numérique, on cherche le documentId de l'enregistrement ayant pour name cette chaine
     */
    public function getConfig($documentId)
    {
        if (! is_numeric($documentId)) {
            $documentId = $this->getDocumentId($documentId);
        }
        return $this->getRecord($documentId)->getArrayCopy();
    }

    /**
     * Renvoie le documentId de l'enregistrement ayant pour name le $name donné.
     *
     * @param string $name            
     *
     * @return int
     * @throws \SbmCommun\Model\Db\Service\Table\Exception
     */
    public function getDocumentId($name)
    {
        $where = new Where();
        $where->equalTo('name', $name);
        $rowset = $this->fetchAll($where);
        if (! $rowset->count()) {
            throw new \SbmCommun\Model\Db\Service\Table\Exception(
                sprintf('Définir le document %s.', $name));
        }
        return $rowset->current()->documentId;
    }
}