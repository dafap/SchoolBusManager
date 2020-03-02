<?php
/**
 * Classe de calcul des effectifs
 *
 * Cette classe dérive de AbstractEffectif et sera dérivée pour les Lots et les Transporteurs.
 *
 * @project sbm
 * @package SbmGestion/src/Model/Db/Service/Eleve
 * @filesource AbstractEffectifType4.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 2 mars 2020
 * @version 2020-2.6.0
 */
namespace SbmGestion\Model\Db\Service\Eleve;

use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;

abstract class AbstractEffectifType4 extends AbstractEffectif
{

    /**
     * On compte les affectations pour un millesime qui correspondent à un service1Id
     *
     * @param array $conditions
     *
     * @return \Zend\Db\Adapter\Driver\ResultInterface
     */
    protected function requete($conditions)
    {
        $where = new Where();
        $where->equalTo('a.millesime', $this->millesime);

        $select = $this->sql->select()
            ->from([
            'a' => $this->tableNames['affectations']
        ])
            ->columns([])
            ->join([
            's' => $this->tableNames['scolarites']
        ], 's.millesime=a.millesime AND s.eleveId=a.eleveId', [])
            ->join([
            'ser' => $this->tableNames['services']
        ], $this->getJointureAffectationsServices(1),
            [
                $this->getIdColumn(),
                'effectif' => new Expression('count(*)')
            ])
            ->where($this->arrayToWhere($where, $conditions))
            ->group($this->getIdColumn());

        $statement = $this->sql->prepareStatementForSqlObject($select);
        return $statement->execute();
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
     * On compte les affectations pour un millesime correspondant à un service2Id
     * lorsqu'il n'a pas déjà été compté en service1Id et correspondance=2
     */
    protected function requetePourCorrespondance(array $conditions)
    {
        // pour la jointure, on a besoin de nombreuses colonnes ; je les prends toutes.
        $select1 = $this->sql->select()
            ->from($this->tableNames['affectations'])
            ->columns(
            [
                'millesime',
                'eleveId',
                'trajet',
                'jours',
                'moment',
                'ligne1Id',
                'sensligne1',
                'ordreligne1'
            ])
            ->where([
            'millesime' => $this->millesime,
            'correspondance' => 2
        ]);

        $jointure = [
            'a.millesime = correspondances.millesime',
            'a.eleveId = correspondances.eleveId',
            'a.trajet = correspondances.trajet',
            'a.jours = correspondances.jours',
            'a.moment = correspondances.moment',
            'a.ligne2Id = correspondances.ligne1Id',
            'a.sensligne2 = correspondances.sensligne1',
            'a.ordreligne2 = correspondances.ordreligne1'
        ];
        $where = new Where();
        $where->equalTo('a.millesime', $this->millesime)->isNull(
            'correspondances.millesime');

        $select = $this->sql->select()
            ->from([
            'a' => $this->tableNames['affectations']
        ])
            ->columns([])
            ->join([
            's' => $this->tableNames['scolarites']
        ], 's.millesime=a.millesime AND s.eleveId=a.eleveId', [])
            ->join([
            'ser' => $this->tableNames['services']
        ], $this->getJointureAffectationsServices(2),
            [
                $this->getIdColumn(),
                'effectif' => new Expression('count(*)')
            ])
            ->join([
            'correspondances' => $select1
        ], implode(' And ', $jointure), [], Select::JOIN_LEFT)
            ->where($this->arrayToWhere($where, $conditions))
            ->group($this->getIdColumn());

        $statement = $this->sql->prepareStatementForSqlObject($select);
        return $statement->execute();
    }
}
