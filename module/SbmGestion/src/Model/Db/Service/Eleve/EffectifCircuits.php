<?php
/**
 * Calcul des effectifs des élèves transportés par circuit
 *
 * Calcul spécial qui n'est pas rattaché à un AbstractEffectifTypex mais dérive
 * directement de AbstractEffectif
 *
 * @project sbm
 * @package SbmGestion/src/Model/Db/Service/Eleve
 * @filesource EffectifCircuits.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 2 mars 2020
 * @version 2020-2.6.0
 */
namespace SbmGestion\Model\Db\Service\Eleve;

use SbmBase\Model\StdLib;
use SbmGestion\Model\Db\Service\EffectifInterface;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Where;

class EffectifCircuits extends AbstractEffectif implements EffectifInterface
{

    public function init(bool $sanspreinscrits = false)
    {
        $this->structure = [];

        $rowset = $this->requete(1, $this->getConditions($sanspreinscrits));
        foreach ($rowset as $row) {
            $this->structure[$row['column']][1] = $row['effectif'];
        }
        $rowset = $this->requete(2, $this->getConditions($sanspreinscrits));
        foreach ($rowset as $row) {
            $this->structure[$row['column']][2] = $row['effectif'];
        }
        // total
        foreach ($this->structure as &$value) {
            $value = array_sum($value);
        }
        return $this->structure;
    }

    public function transportes($circuitId)
    {
        return StdLib::getParam($circuitId, $this->structure, 0);
    }

    public function getEffectifColumns()
    {
        return [
            'transportes' => "nombre d'élèves transportés"
        ];
    }

    public function getIdColumn()
    {
        return 'circuitId';
    }

    /**
     *
     * @param int $rang
     *            prend la valeur 1 ou 2 afin de faire la laison de l'enregistrement du
     *            circuit sur les couples (station1Id, circuit1Id) ou (station2Id,
     *            circuit2Id)
     * @param array $conditions
     *
     * @return \Zend\Db\Adapter\Driver\ResultInterface
     */
    private function requete($rang, $conditions)
    {
        $where = new Where();
        $where->equalTo('c.millesime', $this->millesime);

        $on = sprintf(
            implode(' AND ',
                [
                    'c.millesime = a.millesime',
                    'c.ligneId = a.ligne%1$dId',
                    'c.sens = a.sensligne%1$d',
                    'c.moment = a.moment',
                    'c.ordre = a.ordreligne%1$d',
                    'c.stationId = a.station%1$dId'
                ]), $rang);

        $select = $this->sql->select()
            ->from([
            'c' => $this->tableNames['circuits']
        ])
            ->join([
            'a' => $this->tableNames['affectations']
        ], $on, [
            'effectif' => new Expression('count(*)')
        ])
            ->join([
            's' => $this->tableNames['scolarites']
        ], 's.millesime = a.millesime AND s.eleveId = a.eleveId', [])
            ->columns([
            'column' => $this->getIdColumn()
        ])
            ->where($this->arrayToWhere($where, $conditions))
            ->group('circuitId');

        $statement = $this->sql->prepareStatementForSqlObject($select);
        return $statement->execute();
    }

    /**
     * Conditions adaptées à l'algorithme mis en oeuvre mais limité à une correspondance
     * :<ul> <li>Pour une liaison sans correspondance, on regarde le couple (a.service1Id,
     * a.station1Id)</li> <li>Pour une liaison avec correspondance, la correspondance est
     * remarquée par la présence d'un a.service2Id. Dans ce cas, la station2Id du
     * service1Id devient la station1Id du service2Id. On doit donc considérer le couple
     * (a.service2Id, a.station2Id). On ne doit donc pas regarder les enregistrements
     * éventuels ayant correspondance = 2</li></ul>
     *
     *
     * @param bool $sanspreinscrits
     *
     * @return array
     */
    private function getConditions(bool $sanspreinscrits)
    {
        // getFiltreDemande car on compte les élèves transportés mais sur tous les
        // services, y
        // compris sur les correspondances
        $conditions = $this->getFiltreDemandes($sanspreinscrits);
        // On ne prend que sur la correspondance 1 pour utiliser les couples
        // (a.service1Id, a.station1Id) et éventuellement (a.service2Id, a.station2Id)
        $conditions['a.correspondance'] = 1;
        return $conditions;
    }
}