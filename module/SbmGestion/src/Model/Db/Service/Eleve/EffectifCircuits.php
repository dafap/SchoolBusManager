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
 * @date 8 août 2020
 * @version 2020-2.6.0
 */
namespace SbmGestion\Model\Db\Service\Eleve;

use SbmBase\Model\StdLib;
use SbmGestion\Model\Db\Service\EffectifInterface;
use Zend\Db\Sql\Literal;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;

interface SpecialEffectifInterface extends EffectifInterface
{

    public function init(Where $where);
}

class EffectifCircuits extends AbstractEffectif implements SpecialEffectifInterface
{
    use \SbmCommun\Model\Traits\ServiceTrait,\SbmCommun\Model\Traits\ExpressionSqlTrait;

    public function init(Where $where)
    {
        $this->structure = [];
        $rowset = $this->requete($where);
        $effectif_reel = 0;
        $serviceId = '';
        foreach ($rowset as $row) {
            if ($row['serviceId'] != $serviceId) {
                $serviceId = $row['serviceId'];
                $effectif_reel = 0;
            }
            $effectif_reel += $row['montee'] - $row['descente'];
            $this->structure[$row['circuitId']] = [
                'montee' => $row['montee'],
                'descente' => $row['descente'],
                'effectif_reel' => $effectif_reel
            ];
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

    private function requete($conditions)
    {
        $designation = sprintf("CONCAT_WS(' ',%s,%s,%s,%s,%s,%s)", "c.ligneId",
            $this->getSqlMoment('c.moment'), $this->getSqlSens('c.sens'),
            "TIME_FORMAT(c.horaireD, '%H:%i')", $this->getSqlOrdre('c.ordre'),
            $this->getSqlSemaine('c.semaine'));
        $select = $this->sql->select()
            ->columns(
            [
                'circuitId',
                'millesime',
                'ligneId',
                'sens',
                'moment',
                'ordre',
                'stationId',
                'service' => new Literal($designation),
                'serviceId' => new Literal($this->getSqlEncodeServiceId('c'))
            ])
            ->from([
            'c' => $this->db_manager->getCanonicName('circuits')
        ])
            ->join([
            's' => $this->db_manager->getCanonicName('services')
        ], $this->jointureService('c', 's'), [
            'capacite' => 'nbPlaces'
        ])
            ->join([
            'st' => $this->db_manager->getCanonicName('stations')
        ], 'c.stationId=st.stationId', [
            'station' => 'nom'
        ])
            ->join([
            'co' => $this->db_manager->getCanonicName('communes')
        ], 'co.communeId=st.communeId', [
            'commune' => 'alias'
        ])
            ->join([
            'a1' => $this->subselect(1)
        ], $this->jointureCircuit(1), [
            'montee' => new Literal('IFNULL(a1.effectif,0)')
        ], Select::JOIN_LEFT)
            ->join([
            'a2' => $this->subselect(2)
        ], $this->jointureCircuit(2),
            [
                'descente' => new Literal('IFNULL(a2.effectif,0)')
            ], Select::JOIN_LEFT)
            ->
        // ->where($conditions)
        order(
            [
                'c.millesime',
                'c.ligneId',
                'c.sens',
                'c.moment',
                'c.ordre',
                'c.horaireD',
                'c.horaireA',
                'passage'
            ]);
        $statement = $this->sql->prepareStatementForSqlObject($select);
        return $statement->execute();
    }

    /**
     * Jointure entre les tables circuits et subselect (affectations)
     *
     * @param int $md
     *            1 pour montee, 2 pour descente
     * @return string
     */
    private function jointureCircuit(int $md): string
    {
        $array = [
            'a%1$d.millesime=c.millesime',
            'a%1$d.ligne1Id=c.ligneId',
            'a%1$d.sensligne1=c.sens',
            'a%1$d.moment=c.moment',
            'a%1$d.ordreligne1=c.ordre',
            'a%1$d.station%1$dId=c.stationId'
        ];
        return sprintf(implode(' AND ', $array), $md);
    }

    /**
     * Sous-requête de comptage des effectifs
     *
     * @param int $md
     *            1 pour montee, 2 pour descente
     * @return \Zend\Db\Sql\Select
     */
    private function subselect(int $md): Select
    {
        $stationId = sprintf('station%dId', $md);
        return $this->sql->select()
            ->columns(
            [
                'millesime',
                'ligne1Id',
                'sensligne1',
                'moment',
                'ordreligne1',
                $stationId,
                'effectif' => new Literal('count(eleveId)')
            ])
            ->from($this->db_manager->getCanonicName('affectations'))
            ->group(
            [
                'millesime',
                'ligne1Id',
                'moment',
                'sensligne1',
                'ordreligne1',
                $stationId
            ]);
    }
}