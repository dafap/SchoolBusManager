<?php
/**
 * Classe de calcul des effectifs
 *
 * Cette classe dérive de AbstractEffectif et sera dérivée pour les Classes,
 * les Etablissements, les Organismes et les Tarifs
 *
 * @project sbm
 * @package SbmGestion/src/Model/Db/Service/Eleve
 * @filesource AbstractEffectifType1.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 8 mars 2019
 * @version 2019-2.5.0
 */
namespace SbmGestion\Model\Db\Service\Eleve;

use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Where;

abstract class AbstractEffectifType1 extends AbstractEffectif
{

    /**
     * La même méthode pour toutes les classes dérivées.
     *
     * @param bool $sanspreinscrits
     */
    public function init(bool $sanspreinscrits = false)
    {
        $this->structure = [
            'demandes' => [],
            'transportes' => []
        ];
        // calcul des demandes
        $rowset = $this->requete($this->getIdColumn(),
            $this->getFiltreDemandes($sanspreinscrits), $this->getIdColumn(), false);
        foreach ($rowset as $row) {
            $this->structure['demandes'][$row[$this->getIdColumn()]] = $row['effectif'];
        }
        // calcul des transportes
        $rowset = $this->requete($this->getIdColumn(),
            $this->getFiltreTransportes($sanspreinscrits), $this->getIdColumn(), true);
        foreach ($rowset as $row) {
            $this->structure['transportes'][$row[$this->getIdColumn()]] = $row['effectif'];
        }
    }

    /**
     *
     * @param string $columnId
     * @param array $conditions
     *            (@see AbstractQuery::arrayToWhere())
     * @param string|array $group
     * @param boolean $transportes
     *
     * @return \Zend\Db\Adapter\Driver\ResultInterface
     */
    protected function requete($columnId, $conditions, $group, $transportes = false)
    {
        $where = new Where();
        $where->equalTo('s.millesime', $this->millesime);
        $select = $this->sql->select();
        $select->from([
            's' => $this->tableNames['scolarites']
        ])
            ->columns([
            $columnId,
            'effectif' => new Expression('count(*)')
        ])
            ->where($this->arrayToWhere($where, $conditions))
            ->group($group);
        if ($transportes) {
            $select->join([
                'a' => $this->tableNames['affectations']
            ], 'a.millesime = s.millesime AND a.eleveId = s.eleveId', []);
        }
        $statement = $this->sql->prepareStatementForSqlObject($select);
        return $statement->execute();
    }
}