<?php
/**
 * Calcul des effectifs des élèves transportés par Transporteur
 *
 * Calcul spécial qui n'est pas rattaché à un AbstractEffectifTypex mais dérive
 * directement de AbstractEffectif
 *
 * @project sbm
 * @package SbmGestion/src/Model/Db/Service/Eleve
 * @filesource EffectifTransporteurs.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 8 mars 2019
 * @version 2019-2.5.0
 */
namespace SbmGestion\Model\Db\Service\Eleve;

use SbmBase\Model\StdLib;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;

class EffectifTransporteurs extends AbstractEffectif implements EffectifInterface
{

    public function init(bool $sanspreinscrits = false)
    {
        $this->structure = [];
        // pour compter les transporteurs associés au service1Id dans les affectations
        $rowset = $this->requete($this->getFiltreDemandes($sanspreinscrits));
        foreach ($rowset as $row) {
            $this->structure[$row[$this->getIdColumn()]][1] = $row['effectif'];
        }
        // pour compter les transporteurs associés au service2Id dans les affectations quand c'est
        // nécessaire
        $rowset = $this->requetePourCorrespondance(
            $this->getFiltreDemandes($sanspreinscrits));
        foreach ($rowset as $row) {
            $this->structure[$row[$this->getIdColumn()]][2] = $row['effectif'];
        }
        // total
        foreach ($this->structure as &$value) {
            $value = array_sum($value);
        }
        return $this->structure;
    }

    public function getEffectifColumns()
    {
        return [
            'transportes' => "nombre d'élèves transportés"
        ];
    }

    public function getIdColumn()
    {
        return 'transporteurId';
    }

    /**
     * On compte les affectations pour un millesime correspondant à un service1Id
     *
     * @param array $conditions
     *
     * @return \Zend\Db\Adapter\Driver\ResultInterface
     */
    private function requete(array $conditions)
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
                'effectif' => new Expression('count(*)')
            ])
            ->where($this->arrayToWhere($where, $conditions))
            ->group($this->getIdColumn());

        $statement = $this->sql->prepareStatementForSqlObject($select);
        return $statement->execute();
    }

    /**
     * On compte les affectations pour un millesime correspondant à un service2Id
     * lorsqu'il n'a pas déjà été compté en service1Id et correspondance=2
     */
    private function requetePourCorrespondance(array $conditions)
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

    /**
     *
     * @param int $transporteurId
     *
     * @return mixed|array
     */
    public function transportes($transporteurId)
    {
        return StdLib::getParam($transporteurId, $this->structure, 0);
    }
}