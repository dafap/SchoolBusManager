<?php
/**
 * Classe de calcul des effectifs
 *
 * Cette classe dérive de AbstractEffectif et sera dérivée pour les Lots et les
 * Transporteurs.
 *
 * @project sbm
 * @package SbmGestion/src/Model/Db/Service/Eleve
 * @filesource AbstractEffectifType4.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 6 août 2021
 * @version 2021-2.5.14
 */
namespace SbmGestion\Model\Db\Service\Eleve;

use Zend\Db\Sql\Literal;
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
        ], 'a.service1Id=ser.serviceId',
            [
                $this->getIdColumn(),
                'effectif' => new Literal('count(*)')
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
            ->columns([
            'millesime',
            'eleveId',
            'trajet',
            'jours',
            'sens',
            'service1Id'
        ])
            ->where([
            'millesime' => $this->millesime,
            'correspondance' => 2
        ]);

        $jointure = [
            'a.millesime=correspondances.millesime',
            'a.eleveId=correspondances.eleveId',
            'a.trajet=correspondances.trajet',
            'a.jours=correspondances.jours',
            'a.sens=correspondances.sens',
            'a.service2Id=correspondances.service1Id'
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
        ], 'a.service2Id=ser.serviceId',
            [
                $this->getIdColumn(),
                'effectif' => new Literal('count(*)')
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
