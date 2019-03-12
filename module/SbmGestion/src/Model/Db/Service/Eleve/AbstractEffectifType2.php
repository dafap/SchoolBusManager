<?php
/**
 * Classe de calcul des effectifs
 *
 * Cette classe dérive de AbstractEffectif et sera dérivée pour les Services et les Stations.
 *
 * @project sbm
 * @package SbmGestion/src/Model/Db/Service/Eleve
 * @filesource AbstractEffectifType2.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 8 mars 2019
 * @version 2019-2.5.0
 */
namespace SbmGestion\Model\Db\Service\Eleve;

use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;

abstract class AbstractEffectifType2 extends AbstractEffectif
{

    /**
     * On compte toutes les affectations pour un millesime correspondant à index1Id
     *
     * @param string $indexId
     * @param Where|\Closure|string|array $conditions
     * @param string|array $group
     *
     * @return \Zend\Db\Adapter\Driver\ResultInterface
     */
    protected function requete($indexId, $conditions, $group)
    {
        $where = new Where();
        $where->equalTo('a.millesime', $this->millesime);

        $select = $this->sql->select()
            ->from([
            'a' => $this->tableNames['affectations']
        ])
            ->join([
            's' => $this->tableNames['scolarites']
        ], 's.millesime=a.millesime AND s.eleveId=a.eleveId', [])
            ->columns([
            'column' => $indexId,
            'effectif' => new Expression('count(*)')
        ])
            ->where($this->arrayToWhere($where, $conditions))
            ->group($group);

        $statement = $this->sql->prepareStatementForSqlObject($select);
        return $statement->execute();
    }

    /**
     * On ne doit pas éliminer les correspondances car on compte par circuit ou par station.
     *
     * @param bool $sanspreinscrits
     * @return array
     */
    protected function getConditions(bool $sanspreinscrits)
    {
        return $this->getFiltreDemandes($sanspreinscrits);
    }

    /**
     * On compte les affectations pour un millesime correspondant à un index2Id
     * lorsqu'il n'a pas déjà été compté en index1Id et correspondance=2
     *
     * @param string $index
     * @param array $conditions
     * @param string|array $group
     *
     * @return \Zend\Db\Adapter\Driver\ResultInterface
     */
    protected function requetePourCorrespondance($index, $conditions, $group)
    {
        $select1 = $this->sql->select()
            ->from($this->tableNames['affectations'])
            ->where([
            'millesime' => $this->millesime,
            'correspondance' => 2
        ]);

        $index2Id = $index . '2Id';
        $index1Id = $index . '1Id';
        $jointure = [
            'a.millesime=correspondances.millesime',
            'a.eleveId=correspondances.eleveId',
            'a.trajet=correspondances.trajet',
            'a.jours=correspondances.jours',
            'a.sens=correspondances.sens',
            "a.$index2Id=correspondances.$index1Id"
        ];
        $where = new Where();
        $where->equalTo('a.millesime', $this->millesime)->isNull(
            'correspondances.millesime');

        $select = $this->sql->select()
            ->from([
            'a' => $this->tableNames['affectations']
        ])
            ->join([
            'correspondances' => $select1
        ], implode(' And ', $jointure), [], Select::JOIN_LEFT)
            ->join([
            's' => $this->tableNames['scolarites']
        ], 's.millesime=a.millesime AND s.eleveId=a.eleveId', [])
            ->columns([
            'column' => $index2Id,
            'effectif' => new Expression('count(*)')
        ])
            ->where($this->arrayToWhere($where, $conditions))
            ->group("a.$group");

        $statement = $this->sql->prepareStatementForSqlObject($select);
        return $statement->execute();
    }
}
