<?php
/**
 * Calcul des effectifs des élèves transportés par circuit
 *
 * Calcul spécial qui n'est pas rattaché à un AbstractEffectifTypex mais dérive
 * directement de AbstractEffectif.
 * L'interface SpecialEffectifInterface est défini en fin de ce fichier.
 *
 * @project sbm
 * @package SbmGestion/src/Model/Db/Service/Eleve
 * @filesource EffectifCircuits.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 18 avr. 2021
 * @version 2021-2.6.1
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
    use \SbmCommun\Model\Traits\ServiceTrait,\SbmCommun\Model\Traits\ExpressionSqlTrait, \SbmCommun\Model\Traits\DebugTrait;

    private $ligneId;

    private $sens;

    private $moment;

    private $ordre;

    private $sanspreinscrits = false;

    /**
     *
     * @param number $millesime
     */
    public function setMillesime(int $millesime)
    {
        $this->millesime = $millesime;
        return $this;
    }

    /**
     *
     * @param string $ligneId
     */
    public function setLigneId(string $ligneId)
    {
        $this->ligneId = $ligneId;
        return $this;
    }

    /**
     *
     * @param int $sens
     */
    public function setSens(int $sens)
    {
        $this->sens = $sens;
        return $this;
    }

    /**
     *
     * @param int $moment
     */
    public function setMoment(int $moment)
    {
        $this->moment = $moment;
        return $this;
    }

    /**
     *
     * @param int $ordre
     */
    public function setOrdre(int $ordre)
    {
        $this->ordre = $ordre;
        return $this;
    }

    /**
     * Appeler cette méthode avant la méthode init()
     *
     * @param bool $sp
     */
    public function setSanspreinscrits(bool $sp)
    {
        $this->sanspreinscrits = $sp;
        return $this;
    }

    public function init(Where $where = null)
    {
        $this->debugInitLog(StdLib::findParentPath(__DIR__, 'data/tmp'), 'effectifCircuit.log');
        if (empty($where)) {
            $where = new Where();
            $where->equalTo('c.millesime', $this->millesime)
                ->equalTo('c.ligneId', $this->ligneId)
                ->equalTo('c.sens', $this->sens)
                ->equalTo('c.moment', $this->moment)
                ->equalTo('c.ordre', $this->ordre);
        }
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
        if (! array_key_exists($circuitId, $this->structure)) {
            $this->debugLog([__METHOD__ =>['circuitId'=>$circuitId, 'structure' => $this->structure]]);
        }
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
            ->where($conditions)
            ->order(
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
        $this->debugLog([__METHOD__=>$this->getSqlString($select)]);
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
        $select = $this->sql->select()
            ->columns(
            [
                'millesime',
                'ligne1Id',
                'sensligne1',
                'moment',
                'ordreligne1',
                $stationId,
                'effectif' => new Literal('count(aff.eleveId)')
            ])
            ->from([
            'aff' => $this->db_manager->getCanonicName('affectations')
        ])
            ->group(
            [
                'millesime',
                'ligne1Id',
                'moment',
                'sensligne1',
                'ordreligne1',
                $stationId
            ]);
        $condition = new Where();
        $condition->literal('sco.inscrit = 1');
        if ($this->sanspreinscrits) {
            $condition->nest()
                ->nest()
                ->literal('aff.trajet = 1')
                ->literal('sco.paiementR1 = 1')
                ->unnest()->or->nest()
                ->literal('aff.trajet = 2')
                ->literal('sco.reductionR2 = 0')
                ->literal('sco.paiementR2 = 1')
                ->unnest()->or->nest()
                ->literal('aff.trajet = 2')
                ->literal('sco.reductionR2 = 1')
                ->unnest()
                ->unnest();
        }
        $select->join([
            'sco' => $this->db_manager->getCanonicName('scolarites')
        ], 'aff.millesime = sco.millesime AND aff.eleveId = sco.eleveId', [])
            ->where($condition);

        return $select;
    }
}