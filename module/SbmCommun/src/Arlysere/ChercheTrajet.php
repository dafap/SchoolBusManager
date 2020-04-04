<?php
/**
 * Détermination d'un trajet permettant d'aller du domicile à l'établissement ou retour
 *
 * Hérite des propriétés millesime, sql, db_manager de la classe parent
 * Hérite des méthodes publiques addStrategy, createService et getSqlString de la classe parent.
 * Hérite aussi des méthodes protégées getResultSetPrototype, paginator, renderResult
 *
 * @project sbm
 * @package SbmCommun/src/Arlysere
 * @filesource ChercheTrajet.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 16 mars 2020
 * @version 2020-2.6.0
 */
namespace SbmCommun\Arlysere;

use SbmCommun\Model\Db\Service\Query\AbstractQuery;
use SbmCommun\Model\Traits\DebugTrait;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Where;
use Zend\ServiceManager\FactoryInterface;

class ChercheTrajet extends AbstractQuery implements FactoryInterface
{
    use DebugTrait;

    /**
     *
     * @var string
     */
    private $etablissementId;

    /**
     * Identifiant de la station proche du domicile (départ le matin)
     *
     * @var int
     */
    private $stationId;

    /**
     *
     * @var int
     */
    private $eleveId;

    /**
     *
     * @var int
     */
    private $jours;

    /**
     *
     * @var int
     */
    private $trajet;

    /**
     *
     * @var int
     */
    private $responsableId;

    /**
     *
     * @var \SbmCommun\Model\Db\Service\Table\Affectations
     */
    private $tAffectations;

    /**
     *
     * @param number $eleveId
     */
    public function setEleveId(int $eleveId)
    {
        $this->eleveId = $eleveId;
        if (method_exists($this, 'debugLog')) {
            $this->debugLog($this->eleveId);
        }
        return $this;
    }

    /**
     *
     * @param array|int $jours
     */
    public function setJours($jours)
    {
        if (is_array($jours)) {
            $strategy = new \SbmCommun\Model\Strategy\Semaine();
            $this->jours = $strategy->extract($jours);
        } else {
            $this->jours = $jours;
        }
        if (method_exists($this, 'debugLog')) {
            $this->debugLog($this->jours);
        }
        return $this;
    }

    /**
     *
     * @param number $trajet
     */
    public function setTrajet(int $trajet)
    {
        $this->trajet = $trajet;
        if (method_exists($this, 'debugLog')) {
            $this->debugLog($this->trajet);
        }
        return $this;
    }

    /**
     *
     * @param number $responsableId
     */
    public function setResponsableId(int $responsableId)
    {
        $this->responsableId = $responsableId;
        if (method_exists($this, 'debugLog')) {
            $this->debugLog($this->responsableId);
        }
        return $this;
    }

    /**
     *
     * @param string $etablissementId
     */
    public function setEtablissementId(string $etablissementId)
    {
        $this->etablissementId = $etablissementId;
        if (method_exists($this, 'debugLog')) {
            $this->debugLog($this->etablissementId);
        }
        return $this;
    }

    /**
     *
     * @param number $stationId
     */
    public function setStationId(int $stationId)
    {
        $this->stationId = $stationId;
        if (method_exists($this, 'debugLog')) {
            $this->debugLog($this->stationId);
        }
        return $this;
    }

    protected function init()
    {
        $this->tAffectations = $this->db_manager->get('Sbm\Db\Table\Affectations');
        if (method_exists($this, 'debugInitLog')) {
            $this->debugInitLog('/debug', 'debug-cherchetrajet.txt');
            $this->debugClear();
        }
    }

    /**
     * Le tableau renvoyé est de la forme [$moment => boolean] Pour chaque moment on
     * renvoie true si on a trouvé une solution et false sinon.
     *
     * @return boolean[]
     */
    public function run()
    {
        // DEBUG
        $cr = [];
        // ------------
        $trouve = [];
        for ($moment = 1; $moment <= 3; $moment ++) {
            $trouve[$moment] = false;
            // cherche des trajets avec le moins de correspondances possibles
            // (4 maxi)
            for ($i = 1, $trajetsPossibles = []; ! count($trajetsPossibles) && $i <= 4; $i ++) {
                $trajetsPossibles = $this->getTrajets($moment, $i);
            }
            $i --;
            // // DEBUG
            $cr[$this->eleveId][$moment]['nb_circuits'] = $i;
            // $cr[$this->eleveId][$moment]['nb_trajets'] = count($trajetsPossibles);
            // $this->debugLog(__LINE__);
            // ---------
            if (count($trajetsPossibles)) {
                // si on a trouvé des trajets
                // @TODO: contrôle des places disponibles
                $trajet = current($trajetsPossibles);
                // DEBUG
                // $cr[$this->eleveId][$moment]['trajet'] = $trajet;
                // $this->debugLog(__LINE__);
                // --------------
                $oAffectation = $this->tAffectations->getObjData();
                $oAffectation->millesime = $this->millesime;
                $oAffectation->eleveId = $this->eleveId;
                $oAffectation->trajet = $this->trajet;
                $oAffectation->moment = $moment;
                $oAffectation->responsableId = $this->responsableId;
                for ($j = 1; $j <= $i; $j ++) {
                    $oAffectation->jours = $trajet["semaine_$j"];
                    $oAffectation->correspondance = $j;
                    $oAffectation->station1Id = $trajet["station1Id_$j"];
                    $oAffectation->ligne1Id = $trajet["ligne1Id_$j"];
                    $oAffectation->sensligne1 = $trajet["sensligne1_$j"];
                    $oAffectation->ordreligne1 = $trajet["ordreligne1_$j"];
                    $oAffectation->station2Id = $trajet["station2Id_$j"];
                    // DEBUG
                    // $cr[$this->eleveId][$moment]['affectation'][$j] =
                    // $oAffectation->getArrayCopy();
                    $this->debugLog(__LINE__);
                    $this->debugLog($oAffectation);
                    // ---------
                    $this->tAffectations->saveRecord($oAffectation);
                    // DEBUG
                    // $this->debugLog(__LINE__);
                    // ---------------
                }
                $trouve[$moment] = true;
            }
        }
        // DEBUG
        // $this->debugLog($cr);
        // -----------------------
        return $trouve;
    }

    /**
     * Renvoie le Where des requêtes de recherche de trajet
     *
     * @param int $moment
     *            1 pour Matin, 2 pour Midi, 3 pour Soir
     * @param int $nb_cir
     *            nombre de circuits sur le trajet
     * @return \Zend\Db\Sql\Where
     */
    private function getConditions(int $moment, int $nb_cir)
    {
        $where = (new Where())->equalTo('eta.etablissementId', $this->etablissementId)->equalTo(
            'cir1sta1.stationId', $this->stationId);
        // arrivée à l'établissement
        switch ($moment) {
            case 1:
                $left = sprintf('cir%dsta2.horaireA', $nb_cir);
                $right = 'hMatin';
                $where->lessThan($left, $right, Where::TYPE_IDENTIFIER,
                    Where::TYPE_IDENTIFIER);
                break;
            case 2:
                $left = sprintf('cir%dsta2.horaireD', $nb_cir);
                $right = 'hMidi';
                $where->greaterThan($left, $right, Where::TYPE_IDENTIFIER,
                    Where::TYPE_IDENTIFIER);
                break;
            case 3:
                $left = sprintf('cir%dsta2.horaireD', $nb_cir);
                $right = 'hSoir';
                $where->greaterThan($left, $right, Where::TYPE_IDENTIFIER,
                    Where::TYPE_IDENTIFIER);
                break;
        }
        // sur chaque circuit,
        for ($i = 1; $i <= $nb_cir; $i ++) {
            $where->equalTo(sprintf('cir%dsta1.millesime', $i), $this->millesime)
                ->equalTo(sprintf('cir%dsta1.moment', $i), $moment)
                ->literal(sprintf('eta.jOuverture & cir%dsta1.semaine <>0', $i));
            if ($moment == 1) {
                // départ de la station1 avant arrivée en station2
                $left = sprintf('cir%dsta1.horaireD', $i);
                $right = sprintf('cir%dsta2.horaireA', $i);
                $where->lessThan($left, $right, Where::TYPE_IDENTIFIER,
                    Where::TYPE_IDENTIFIER);
            } else {
                // arrivée en station1 après départ de la station2
                $left = sprintf('cir%dsta1.horaireA', $i);
                $right = sprintf('cir%dsta2.horaireD', $i);
                $where->greaterThan($left, $right, Where::TYPE_IDENTIFIER,
                    Where::TYPE_IDENTIFIER);
            }
        }
        // pour chaque correspondance,
        for ($i = 1; $i < $nb_cir; $i ++) {
            if ($moment == 1) {
                // arrivée en cir1sta2 avant départ de cir2sta1
                $left = sprintf('cir%dsta2.horaireA', $i);
                $right = sprintf('cir%dsta1.horaireD', $i + 1);
                $where->lessThan($left, $right, Where::TYPE_IDENTIFIER,
                    Where::TYPE_IDENTIFIER);
            } else {
                // départ de cir1sta2 après arrivée en cir2sta1
                $left = sprintf('cir%dsta2.horaireD', $i);
                $right = sprintf('cir%dsta1.horaireA', $i + 1);
                $where->greaterThan($left, $right, Where::TYPE_IDENTIFIER,
                    Where::TYPE_IDENTIFIER);
            }
        }
        return $where;
    }

    private function jointureEntreCircuits(int $rang_correspondance)
    {
        $modele = 'cir%1$dsta2.millesime=cir%2$dsta1.millesime' .
            ' AND cir%1$dsta2.moment=cir%2$dsta1.moment' .
            ' AND cir%1$dsta2.stationId=cir%2$dsta1.stationId';
        return sprintf($modele, $rang_correspondance, $rang_correspondance + 1);
    }

    private function jointureCircuitCorrespondance(int $rang_correspondance)
    {
        $modele = 'cir%1$dsta1.stationId=cor%2$d.station1Id';
        return sprintf($modele, $rang_correspondance + 1, $rang_correspondance);
    }

    private function jointureCorrespondanceCircuit(int $rang_correspondance)
    {
        $modele = 'cor%1$d.station2Id=cir%1$dsta2.stationId';
        return sprintf($modele, $rang_correspondance);
    }

    private function jointureSurUnCircuit(int $rang_circuit)
    {
        $modele = 'cir%1$dsta1.millesime=cir%1$dsta2.millesime' .
            ' AND cir%1$dsta1.ligneId=cir%1$dsta2.ligneId' .
            ' AND cir%1$dsta1.sens=cir%1$dsta2.sens' .
            ' AND cir%1$dsta1.moment=cir%1$dsta2.moment' .
            ' AND cir%1$dsta1.ordre=cir%1$dsta2.ordre';
        return sprintf($modele, $rang_circuit);
    }

    private function selectCorr(int $n)
    {
        $table1 = $this->db_manager->getCanonicName('stations');
        $table2 = $this->db_manager->getCanonicName('stations-stations');
        $select1 = $this->sql->select()
            ->columns([
            'station1Id',
            'station2Id'
        ])
            ->from($table2);
        $select2 = $this->sql->select()
            ->columns([
            'station2Id',
            'station1Id'
        ])
            ->from($table2)
            ->combine($select1);
        $select3 = $this->sql->select()->from([
            'c' => $select2
        ]);
        $select4 = $this->sql->select()
            ->columns([
            'station1Id' => 'stationId',
            'station2Id' => 'stationId'
        ])
            ->from($table1)
            ->combine($select3);
        return $select4;
    }

    /**
     * Requête déterminant les trajets avec 1 ou plusieurs circuits. Les correspondances
     * ne se font que si la station porte le même identifiant sur les deux circuits. Par
     * défaut, trajet du matin sans correspondance.
     *
     * @param int $moment
     *            1 Matin, 2 Midi, 3 Soir
     * @param int $nb_cir
     *            nombre de circuits
     */
    private function selectTrajet(int $moment = 1, int $nb_cir = 1)
    {
        $select = $this->sql->select()
            ->columns([
            'nom'
        ])
            ->from([
            'eta' => $this->db_manager->getCanonicName('etablissements')
        ])
            ->join(
            [
                'etasta' => $this->db_manager->getCanonicName('etablissements-stations')
            ], 'eta.etablissementId=etasta.etablissementId', []);
        for ($i = $nb_cir; $i >= 1; $i --) {
            $columns1 = [];
            $columns2 = [
                "station2Id_$i" => 'stationId',
                "horaireStation2_$i" => new Expression(
                    sprintf("DATE_FORMAT(cir%dsta2.", $i) .
                    ($moment == 1 ? "horaireA" : "horaireD") . ",'%H:%i')")
            ];
            $columns1 = [
                "sta1cor$i" => 'station1Id',
                "sta2cor$i" => 'station2Id'
            ];
            // $x1=2*$i-1;$x2=2*$i;
            // $columns2=["sta$x2"=>'stationId', "h$x2"=>'horaireA'];
            if ($i == $nb_cir) {
                // sur le dernier circuit, cette station dessert l'établissement
                $select->join(
                    [
                        sprintf('cir%dsta2', $i) => $this->db_manager->getCanonicName(
                            'circuits')
                    ], sprintf('cir%dsta2.stationId = etasta.stationId', $i), $columns2);
            } else {
                // cette station est une station de correspondance
                $select->join([
                    sprintf('cor%d', $i) => $this->selectCorr($i)
                ], $this->jointureCircuitCorrespondance($i), $columns1)
                    ->join(
                    [
                        sprintf('cir%dsta2', $i) => $this->db_manager->getCanonicName(
                            'circuits')
                    ], $this->jointureCorrespondanceCircuit($i), $columns2);
            }
            // la station 1 du circuit 1 dessert le domicile, les autres sont des
            // correspondances
            $columns3 = [
                "semaine_$i" => 'semaine',
                "station1Id_$i" => 'stationId',
                "ligne1Id_$i" => 'ligneId',
                "sensligne1_$i" => 'sens',
                "ordreligne1_$i" => 'ordre',
                "horaireStation1_$i" => new Expression(
                    sprintf("DATE_FORMAT(cir%dsta1.", $i) .
                    ($moment == 1 ? "horaireD" : "horaireA") . ",'%H:%i')")
            ];
            // $columns3=["sta$x1"=>'stationId', "h$x1" => 'horaireD'];
            $select->join(
                [
                    sprintf('cir%dsta1', $i) => $this->db_manager->getCanonicName(
                        'circuits')
                ], $this->jointureSurUnCircuit($i), $columns3);
        }
        $select->where($this->getConditions($moment, $nb_cir));
        //die($this->getSqlString($select));
        return $select;
    }

    /**
     *
     * @param int $moment
     * @param int $nb_cir
     * @return array
     */
    public function getTrajets(int $moment, int $nb_cir)
    {
        return iterator_to_array(
            $this->renderResult($this->selectTrajet($moment, $nb_cir)));
    }
}