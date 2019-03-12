<?php
/**
 * Classe de calcul des effectifs
 *
 * Cette classe dérive de AbstractEffectif et sera dérivée pour les EtablissementsServices,
 * les ServicesEtablissements et les StationsServices
 *
 * @project sbm
 * @package SbmGestion/src/Model/Db/Service/Eleve
 * @filesource AbstractEffectifType3.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 11 mars 2019
 * @version 2019-2.5.0
 */
namespace SbmGestion\Model\Db\Service\Eleve;

use SbmBase\Model\StdLib;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;

abstract class AbstractEffectifType3 extends AbstractEffectif
{

    /**
     * Caractere conditionnel utilisé pour les requetes
     *
     * @var mixed
     */
    protected $caractere;

    public function setCaractereConditionnel($caractere)
    {
        $this->caractere = $caractere;
        return $this;
    }

    public function transportes($id)
    {
        return StdLib::getParam($id, $this->structure, 0);
    }

    public function getEffectifColumns()
    {
        return [
            'transportes' => "nombre d'élèves transportés"
        ];
    }

    /**
     *
     * @param string $indexId
     * @param array $conditions
     * @param string|array $group
     *
     * @return \Zend\Db\Adapter\Driver\ResultInterface
     */
    protected function requete($indexId, $conditions, $group)
    {
        $where = new Where();
        $where->equalTo('s.millesime', $this->millesime);

        $select = $this->sql->select()
            ->from([
            's' => $this->tableNames['scolarites']
        ])
            ->join([
            'a' => $this->tableNames['affectations']
        ], 'a.millesime=s.millesime AND a.eleveId=s.eleveId',
            [
                $indexId,
                'effectif' => new Expression('count(*)')
            ])
            ->where($this->arrayToWhere($where, $conditions))
            ->group($group);

        $statement = $this->sql->prepareStatementForSqlObject($select);
        return $statement->execute();
    }

    /**
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
        $where->equalTo('s.millesime', $this->millesime)->isNull(
            'correspondances.millesime');

        $select = $this->sql->select()
            ->from([
            's' => $this->tableNames['scolarites']
        ])
            ->join([
            'a' => $this->tableNames['affectations']
        ], 'a.millesime=s.millesime AND a.eleveId=s.eleveId',
            [
                $index2Id,
                'effectif' => new Expression('count(*)')
            ])
            ->columns([])
            ->join([
            'correspondances' => $select1
        ], implode(' AND ', $jointure), [], Select::JOIN_LEFT)
            ->where($this->arrayToWhere($where, $conditions))
            ->group($group);

        $statement = $this->sql->prepareStatementForSqlObject($select);
        return $statement->execute();
    }

    /**
     * On ne doit pas éliminer les correspondances quand on compte par circuit ou par station.
     *
     * @param bool $sanspreinscrits
     * @return array
     */
    protected function getConditions(bool $sanspreinscrits)
    {
        return $this->getFiltreDemandes($sanspreinscrits);
    }
}
