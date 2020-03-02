<?php
/**
 * Calcul des effectifs des élèves transportés par établissement pour un service donné.
 *
 * L'initialisation doit nécessairement se faire par :
 *   $objet->setCaractereConditionnel(['ligneId' => $ligneId, 'sens'=>$sens, 'moment'=>$moment,
 *   'ordre'=>$ordre])->init($sanspreinscrits);
 *
 * @project sbm
 * @package SbmGestion/src/Model/Db/Service/Eleve
 * @filesource EffectifServicesEtablissements.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 3 mars 2020
 * @version 2020-2.6.0
 */
namespace SbmGestion\Model\Db\Service\Eleve;

use SbmBase\Model\StdLib;
use SbmGestion\Model\Db\Service\EffectifInterface;
use Zend\Db\Sql\Expression;
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
        $rowset = $this->requete($sanspreinscrits);
        foreach ($rowset as $row) {
            $this->structure[$row['etablissementId']][1] = $row['effectif'];
        }

        $rowset = $this->requetePourCorrespondance($sanspreinscrits);
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
     * @param string $index
     * @param array $conditions
     * @param string|array $group
     *
     * @return \Zend\Db\Adapter\Driver\ResultInterface
     */
    protected function requete($sanspreinscrits)
    {
        $where = new Where();
        $where->equalTo('s.millesime', $this->millesime);
        $conditions = $this->getConditions($sanspreinscrits);
        $conditions['ligne1Id'] = $this->caractere['ligneId'];
        $conditions['sensligne1'] = $this->caractere['sens'];
        $conditions['moment'] = $this->caractere['moment'];
        $conditions['ordreligne1'] = $this->caractere['ordre'];

        $select = $this->sql->select()
            ->from([
            's' => $this->tableNames['scolarites']
        ])
            ->join([
            'a' => $this->tableNames['affectations']
        ], 'a.millesime=s.millesime AND a.eleveId=s.eleveId',
            $this->getColumnsAffectations())
            ->where($this->arrayToWhere($where, $conditions))
            ->group('etablissementId');

        $statement = $this->sql->prepareStatementForSqlObject($select);
        return $statement->execute();
    }

    private function getColumnsAffectations()
    {
        return [
            'ligne1Id',
            'sensligne1',
            'moment',
            'ordreligne1',
            'effectif' => new Expression('count(*)')
        ];
    }

    /**
     *
     * @param bool $sanspreinscrits
     *
     * @return \Zend\Db\Adapter\Driver\ResultInterface
     */
    protected function requetePourCorrespondance($sanspreinscrits)
    {
        $select1 = $this->sql->select()
            ->from($this->tableNames['affectations'])
            ->where([
            'millesime' => $this->millesime,
            'correspondance' => 2
        ]);

        $where = new Where();
        $where->equalTo('s.millesime', $this->millesime)->isNull(
            'correspondances.millesime');

        $conditions = $this->getConditions($sanspreinscrits);
        $conditions['a.ligne2Id'] = $this->caractere['ligneId'];
        $conditions['a.sensligne2'] = $this->caractere['sens'];
        $conditions['a.moment'] = $this->caractere['moment'];
        $conditions['a.ordreligne2'] = $this->caractere['ordre'];

        $select = $this->sql->select()
            ->from([
            's' => $this->tableNames['scolarites']
        ])
            ->join([
            'a' => $this->tableNames['affectations']
        ], 'a.millesime=s.millesime AND a.eleveId=s.eleveId', [])
            ->columns([
            'etablissementId',
            'effectif' => new Expression('count(*)')
        ])
            ->join([
            'correspondances' => $select1
        ], $this->getJointureAffectationsCorrespondances(), [], Select::JOIN_LEFT)
            ->where($this->arrayToWhere($where, $conditions))
            ->group('etablissementId');

        $statement = $this->sql->prepareStatementForSqlObject($select);
        return $statement->execute();
    }

    protected function getJointureAffectationsCorrespondances()
    {
        return implode(' AND ',
            [
                'a.millesime = correspondances.millesime',
                'a.eleveId = correspondances.eleveId',
                'a.trajet = correspondances.trajet',
                'a.jours = correspondances.jours',
                'a.moment = correspondances.moment',
                'a.ligne2Id = correspondances.ligne1Id',
                'a.sensligne2 = correspondances.sensligne1',
                'a.ordreligne2 = correspondances.ordreligne1'
            ]);
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