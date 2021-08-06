<?php
/**
 * Calcul des effectifs des élèves transportés par établissement pour un service donné.
 *
 * L'initialisation doit nécessairement se faire par :
 *   $objet->setCaractereConditionnel($serviceId)->init($sanspreinscrits);
 *
 * @project sbm
 * @package SbmGestion/src/Model/Db/Service/Eleve
 * @filesource EffectifServicesEtablissements.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 6 août 2021
 * @version 2021-2.5.14
 */
namespace SbmGestion\Model\Db\Service\Eleve;

use SbmBase\Model\StdLib;
use SbmGestion\Model\Db\Service\EffectifInterface;
use Zend\Db\Sql\Literal;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;

class EffectifServicesEtablissements extends AbstractEffectif implements
    EffectifInterface
{

    /**
     * Caractere conditionnel utilisé pour les requetes
     *
     * @var mixed
     */
    protected $caractere;

    public function init(bool $sanspreinscrits = false)
    {
        $this->structure = [];

        $conditions = $this->getConditions($sanspreinscrits);
        $conditions['service1Id'] = $this->caractere;
        $rowset = $this->requete('service1Id', $conditions, 'etablissementId');
        foreach ($rowset as $row) {
            $this->structure[$row['etablissementId']][1] = $row['effectif'];
        }

        $conditions = $this->getConditions($sanspreinscrits);
        $conditions['a.service2Id'] = $this->caractere;
        $rowset = $this->requetePourCorrespondance('service', $conditions,
            'etablissementId');
        foreach ($rowset as $row) {
            $this->structure[$row['etablissementId']][2] = $row['effectif'];
        }
        // total
        foreach ($this->structure as &$value) {
            $value = array_sum($value);
        }
        return $this->structure;
    }

    public function getIdColumn()
    {
        return 'etablissementId';
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

    public function setCaractereConditionnel($caractere)
    {
        $this->caractere = $caractere;
        return $this;
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
                'effectif' => new Literal('count(*)')
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
        ], 'a.millesime=s.millesime AND a.eleveId=s.eleveId', [])
            ->columns([
            'etablissementId',
            'effectif' => new Literal('count(*)')
        ])
            ->join([
            'correspondances' => $select1
        ], implode(' AND ', $jointure), [], Select::JOIN_LEFT)
            ->where($this->arrayToWhere($where, $conditions))
            ->group($group);

        $statement = $this->sql->prepareStatementForSqlObject($select);
        return $statement->execute();
    }

    /**
     * On ne doit pas éliminer les correspondances quand on compte par circuit ou par
     * station.
     *
     * @param bool $sanspreinscrits
     * @return array
     */
    protected function getConditions(bool $sanspreinscrits)
    {
        return $this->getFiltreDemandes($sanspreinscrits);
    }
}