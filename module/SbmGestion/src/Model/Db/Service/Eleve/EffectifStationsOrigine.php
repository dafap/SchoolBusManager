<?php
/**
 * Calcul des effectifs des élèves inscrits ou transportés par Station
 *
 * On peut obtenir le résultat total ou le résultat pour le responsable1 ou pour le
 * responsable2
 *
 * @project sbm
 * @package SbmGestion/src/Model/Db/Service/Eleve
 * @filesource EffectifStationsOrigine.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 22 août 2021
 * @version 2021-2.6.3
 */
namespace SbmGestion\Model\Db\Service\Eleve;

use SbmBase\Model\StdLib;
use SbmGestion\Model\Db\Service\EffectifInterface;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Literal;

class EffectifStationsOrigine extends AbstractEffectif implements EffectifInterface
{

    public function init(bool $sanspreinscrits = false)
    {
        $this->structure = [];
        // demandes R1
        $filtre = $this->getFiltreDemandes($sanspreinscrits);
        $rowset = $this->requeteDemandes('stationIdR1', $filtre);
        foreach ($rowset as $row) {
            $this->structure['demandes'][$row['column']][1] = $row['effectif'];
        }
        // demandes R2 . Ne compter que les stationIdR2 différentes des stationIdR1
        $filtre['!='] = [
            'stationIdR1',
            'stationIdR2',
            Where::TYPE_IDENTIFIER,
            Where::TYPE_IDENTIFIER
        ];
        $rowset = $this->requeteDemandes('stationIdR2', $filtre);
        foreach ($rowset as $row) {
            $this->structure['demandes'][$row['column']][2] = $row['effectif'];
        }
        // transportes R1
        $rowset = $this->requeteTransportes(1, $sanspreinscrits);
        foreach ($rowset as $row) {
            $this->structure['transportes'][$row['column']][1] = $row['effectif'];
        }
        // transportes R2 . Ne compter que les affectations moment = 1, correspondance = 1
        // où il n'y a pas de trajet = 1
        $rowset = $this->requeteTransportes(2, $sanspreinscrits);
        foreach ($rowset as $row) {
            $this->structure['transportes'][$row['column']][2] = $row['effectif'];
        }
        // total
        foreach ($this->structure as &$array)
            foreach ($array as &$value) {
                $value[0] = array_sum($value);
            }
        // die(var_dump($this->structure));
    }

    /**
     *
     * @param int $stationId
     * @param int $r
     * @return int
     */
    public function demandes(int $stationId, int $r = 0): int
    {
        return StdLib::getParamR([
            'demandes',
            $stationId,
            $r
        ], $this->structure, 0);
    }

    /**
     *
     * @param int $stationId
     * @param int $r
     * @return int
     */
    public function transportes(int $stationId, int $r = 0): int
    {
        return StdLib::getParamR([
            'transportes',
            $stationId,
            $r
        ], $this->structure, 0);
    }

    public function getEffectifColumns()
    {
        return [
            'transportes' => "nombre d'élèves transportés",
            'demandes' => "nombre de demandes"
        ];
    }

    public function getIdColumn()
    {
        return 'stationId';
    }

    private function requeteDemandes($columnId, $conditions)
    {
        $where = new Where();
        $where->equalTo('millesime', $this->millesime);
        $select = $this->sql->select();
        $select->from([
            's' => $this->tableNames['scolarites']
        ])
            ->columns([
            'column' => $columnId,
            'effectif' => new Literal('count(*)')
        ])
            ->where($this->arrayToWhere($where, $conditions))
            ->group($columnId);
        $statement = $this->sql->prepareStatementForSqlObject($select);
        return $statement->execute();
    }

    private function requeteTransportes(int $trajet, bool $sanspreinscrits)
    {
        $where = new Where();
        $where->equalTo('s.millesime', $this->millesime);
        $select = $this->sql->select();
        $select->columns([
            'effectif' => new Literal('count(*)')
        ])
            ->from([
            's' => $this->tableNames['scolarites']
        ])
            ->join([
            'a' => $this->getSubSelect($trajet)
        ], 'a.millesime = s.millesime AND a.eleveId = s.eleveId',
            [
                'column' => 'station1Id'
            ])
            ->where(
            $this->arrayToWhere($where,
                $this->getConditionTransportes($trajet, $sanspreinscrits)))
            ->group('station1Id');
        // if ($transportes) die($this->getSqlString($select));
        $statement = $this->sql->prepareStatementForSqlObject($select);
        return $statement->execute();
    }

    private function getSubSelect($trajet)
    {
        $where1 = new Where();
        $where1->equalTo('millesime', $this->millesime)
            ->literal('trajet = 1')
            ->literal('moment = 1')
            ->literal('correspondance = 1');
        $subselectTrajet1 = $this->sql->select($this->tableNames['affectations'])
            ->quantifier(Select::QUANTIFIER_DISTINCT)
            ->columns([
            'millesime',
            'eleveId',
            'station1Id'
        ])
            ->where($where1);
        if ($trajet == 1) {
            return $subselectTrajet1;
        }
        // pour le trajet 2, on renvoie seulement ceux qui ne sont pas déja en trajet 1
        $where2 = new Where();
        $where2->equalTo('a2.millesime', $this->millesime)
            ->literal('a2.trajet = 2')
            ->literal('a2.moment = 1')
            ->literal('a2.correspondance = 1')
            ->isNull('a1.eleveId');
        return $this->sql->select()
            ->quantifier(Select::QUANTIFIER_DISTINCT)
            ->columns([
            'millesime',
            'eleveId',
            'station1Id'
        ])
            ->from([
            'a2' => $this->tableNames['affectations']
        ])
            ->join([
            'a1' => $subselectTrajet1
        ],
            'a1.millesime = a2.millesime AND a1.eleveId = a2.eleveId AND a1.station1Id = a2.station1Id',
            [], Select::JOIN_LEFT)
            ->where($where2);
    }

    private function getConditionTransportes(int $trajet, bool $sanspreinscrits)
    {
        if ($sanspreinscrits) {
            return [
                'inscrit' => 1,
                [
                    'paiementR1' => 1,
                    'or',
                    'gratuit' => 1
                ],
                [
                    '>' => [
                        'demandeR' . $trajet,
                        0
                    ]
                ]
            ];
        } else {
            return [
                'inscrit' => 1,
                [
                    '>' => [
                        'demandeR' . $trajet,
                        0
                    ]
                ]
            ];
        }
    }
}