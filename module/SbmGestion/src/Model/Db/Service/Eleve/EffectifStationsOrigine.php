<?php
/**
 * Calcul des effectifs des élèves inscrits ou transportés par Station
 *
 * On peut obtenir le résultat total ou le résultat pour le responsable1 ou pour le responsable2
 *
 * @project sbm
 * @package SbmGestion/src/Model/Db/Service/Eleve
 * @filesource EffectifStationsOrigine.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 7 juin 2020
 * @version 2020-2.6.0
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
        $filtre = $this->getFiltreDemandes($sanspreinscrits);
        // demandes R1
        $rowset = $this->requete('stationIdR1', $filtre, false);
        foreach ($rowset as $row) {
            $this->structure['demandes'][$row['column']][1] = $row['effectif'];
        }
        // demandes R2
        $rowset = $this->requete('stationIdR2', $filtre, false);
        foreach ($rowset as $row) {
            $this->structure['demandes'][$row['column']][2] = $row['effectif'];
        }
        // transportes R1
        $rowset = $this->requete('stationIdR1', $filtre, true);
        foreach ($rowset as $row) {
            $this->structure['transportes'][$row['column']][1] = $row['effectif'];
        }
        // transportes R2
        $rowset = $this->requete('stationIdR2', $filtre, true);
        foreach ($rowset as $row) {
            $this->structure['transportes'][$row['column']][2] = $row['effectif'];
        }
        // total
        foreach ($this->structure as &$array)
            foreach ($array as &$value) {
                $value[0] = array_sum($value);
            }
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

    protected function requete($columnId, $conditions, $transportes = false)
    {
        $where = new Where();
        $where->equalTo('s.millesime', $this->millesime);
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
        if ($transportes) {
            $subSelect = $this->sql->select($this->tableNames['affectations'])
                ->quantifier(Select::QUANTIFIER_DISTINCT)
                ->columns([
                'millesime',
                'eleveId',
                'station1Id',
                'moment'
            ]);
            $select->join([
                'a' => $subSelect
            ],
                'a.millesime = s.millesime AND a.eleveId = s.eleveId AND a.station1Id=s.' .
                $columnId, []);
        }
        $statement = $this->sql->prepareStatementForSqlObject($select);
        return $statement->execute();
    }
}