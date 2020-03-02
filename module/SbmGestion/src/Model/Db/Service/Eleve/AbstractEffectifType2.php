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
 * @date 2 mars 2020
 * @version 2020-2.6.0
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
     * @param string|array $indexId
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
            ->columns($this->getColumns($indexId))
            ->join([
            's' => $this->tableNames['scolarites']
        ], 's.millesime=a.millesime AND s.eleveId=a.eleveId', [])
            ->where($this->arrayToWhere($where, $conditions))
            ->group($group);

        $statement = $this->sql->prepareStatementForSqlObject($select);
        return $statement->execute();
    }

    /**
     *
     * @param mixed $keys
     * @return \Zend\Db\Sql\Expression[]|string[]
     */
    private function getColumns($keys)
    {
        if (is_array($keys)) {
            $array = [
                'effectif' => new Expression('count(*)')
            ];
            $n = 1;
            foreach ($keys as $k) {
                $array['column' . $n ++] = $k;
            }
            return $array;
        } else {
            return [
                'column' => $keys,
                'effectif' => new Expression('count(*)')
            ];
        }
    }

    /**
     * On ne doit pas éliminer les correspondances car on compte par circuit ou par
     * station.
     *
     * @param bool $sanspreinscrits
     * @return array
     */
    protected function getConditions(bool $sanspreinscrits)
    {
        return $this->getFiltreDemandes($sanspreinscrits);
    }

    /**
     * On compte les affectations pour un millesime correspondant à un index2Id lorsqu'il
     * n'a pas déjà été compté en index1Id et correspondance=2
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
        $where = new Where();
        $where->equalTo('a.millesime', $this->millesime)->isNull(
            'correspondances.millesime');

        $select = $this->sql->select()
            ->from([
            'a' => $this->tableNames['affectations']
        ])
            ->columns($this->getColumns($this->getKeys($index)))
            ->join([
            'correspondances' => $select1
        ], implode(' And ', $this->getJointureAffectationsCorrespondances($index)), [],
            Select::JOIN_LEFT)
            ->join([
            's' => $this->tableNames['scolarites']
        ], 's.millesime=a.millesime AND s.eleveId=a.eleveId', [])
            ->where($this->arrayToWhere($where, $conditions))
            ->group($this->getGroup($group));

        $statement = $this->sql->prepareStatementForSqlObject($select);
        return $statement->execute();
    }

    /**
     * Ce prototype est écrit pour un $index donné sous la forme d'une chaine. Il doit
     * être surchargé si $index est un tableau
     *
     * @param string $index
     *            méthode à surcharger si c'est un array
     * @return string[]
     */
    protected function getJointureAffectationsCorrespondances($index)
    {
        return [
            'a.millesime = correspondances.millesime',
            'a.eleveId = correspondances.eleveId',
            'a.trajet = correspondances.trajet',
            'a.jours = correspondances.jours',
            'a.moment = correspondances.moment',
            'a.' . $index . '2Id = correspondances.' . $index . '1Id'
        ];
    }

    /**
     * Ce prototype est écrit pour un $index donné sous la forme d'une chaine. Il doit
     * être surchargé si $index est un tableau
     *
     * @param string $index
     *            méthode à surcharger si c'est un array
     * @return string|array
     */
    protected function getKeys($index)
    {
        return $index . '2Id';
    }

    protected function getGroup($group)
    {
        if (is_array($group)) {
            foreach ($group as &$k) {
                $k = "a.$k";
            }
            return $group;
        } else {
            $k = "a.$group";
            return $k;
        }
    }
}
