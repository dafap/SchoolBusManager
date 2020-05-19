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
 * @date 18 mai 2020
 * @version 2020-2.6.0
 */
namespace SbmCommun\Model\Db\Service\Table\Sys;

use SbmCommun\Model\Db\Service\Table\AbstractSbmTable;
use SbmCommun\Model\Db\Service\Table\Exception;
use SbmCommun\Model\Strategy\Color;
use Zend\Db\Sql\Literal;

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
        $this->id_name = [
            'documentId',
            'sublabel'
        ];
        foreach ($this->getColumnsNames() as $columnName) {
            if (substr($columnName, - 6) == '_color') {
                $this->strategies[$columnName] = new Color();
            }
        }
    }

    /**
     * Renvoie un tableau structuré. Si $sublabel == -1 on renvoie un tableau composé
     * d'autant de ligne qu'il y a de sublabel dans la table. Sinon on renvoie la ligne
     * correspondant au sublabel demandé. Chaque sublabel est un tableau dont les clés =>
     * valeurs sont les champs de la table.
     *
     * @param int $documentId
     * @param int $sublabel
     * @throws Exception\RuntimeException
     * @return array
     */
    public function getConfig(int $documentId, int $sublabel = - 1): array
    {
        $where = [
            'documentId' => $documentId
        ];
        if ($sublabel >= 0) {
            $where['sublabel'] = $sublabel;
        }
        $resultset = $this->fetchAll($where);
        if (! $resultset->count()) {
            $message = _("Cannot find a description of label (document %d%s) in table %s");
            throw new Exception\RuntimeException(
                sprintf($message, $documentId,
                    $sublabel == - 1 ? '' : " - cadre $sublabel", $this->table_name));
        }
        $result = [];
        foreach ($resultset as $odoclabel) {
            $result[$odoclabel->sublabel] = $odoclabel->getArrayCopy();
        }
        return $result;
    }

    public function nbSublabel(int $documentId): int
    {
        $select = $this->table_gateway->getSql()
            ->select()
            ->columns([
            'nb' => new Literal('count(*)')
        ])
            ->where([
            'documentId' => $documentId
        ]);
        $result = $this->table_gateway->selectWith($select);
        return $result->current()->nb;
    }

    public function getNextSublabel(int $documentId): int
    {
        $select = $this->table_gateway->getSql()
            ->select()
            ->columns([
            'max_sublabel' => new Literal('max(sublabel)')
        ])
            ->where([
            'documentId' => $documentId
        ]);
        $result = $this->table_gateway->selectWith($select);
        if ($result->count()) {
            return $result->current()->max_sublabel + 1;
        } else {
            return 0;
        }
    }
}