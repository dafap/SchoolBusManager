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
 * @date 13 avr. 2021
 * @version 2021-2.6.0
 */
namespace SbmCommun\Arlysere;

use SbmBase\Model\StdLib;
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
     * Le primaire a un niveau < 4 ; le secondaire a un niveau >=4
     *
     * @var int
     */
    private $niveau;

    /**
     * DP = 0 ; interne = 1
     *
     * @var int
     */
    private $regimeId;

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

    private function hasDebugger()
    {
        $debug = getenv('APPLICATION_ENV') == 'development';
        return method_exists($this, 'debugLog') && $debug;
    }

    /**
     *
     * @param number $eleveId
     */
    public function setEleveId(int $eleveId)
    {
        $this->eleveId = $eleveId;
        if ($this->hasDebugger()) {
            $this->debugLog($this->eleveId);
        }
        return $this;
    }

    /**
     *
     * @param int $niveau
     * @return \SbmCommun\Arlysere\ChercheTrajet
     */
    public function setNiveau($niveau)
    {
        $this->niveau = $niveau;
        return $this;
    }

    /**
     *
     * @param int $regimeId
     * @return \SbmCommun\Arlysere\ChercheTrajet
     */
    public function setRegime($regimeId)
    {
        $this->regimeId = $regimeId;
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
        if ($this->hasDebugger()) {
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
        if ($this->hasDebugger()) {
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
        if ($this->hasDebugger()) {
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
        if ($this->hasDebugger()) {
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
        if ($this->hasDebugger()) {
            $this->debugLog($this->stationId);
        }
        return $this;
    }

    protected function init()
    {
        $this->tAffectations = $this->db_manager->get('Sbm\Db\Table\Affectations');
        if (method_exists($this, 'debugInitLog')) {
            $this->debugInitLog(StdLib::findParentPath(__DIR__, 'data/logs'),
                'sbm_error.log');
        }
        $this->niveau = -1;
        $this->regimeId = -1;
    }

    /**
     * Le tableau renvoyé est de la forme [$moment => boolean] Pour chaque moment on
     * renvoie true si on a trouvé une solution et false sinon. En mode debuggage le
     * compte-rendu cr est enregistré dans le fichier erreurs.
     *
     * @return boolean[]
     */
    public function run()
    {
        if ($this->hasDebugger()) {
            $cr = [];
        }
        $trouve = [];
        for ($moment = 1; $moment <= 4; $moment ++) {
            $trouve[$moment] = false;
            // cherche des trajets avec le moins de correspondances possibles
            // (4 maxi)
            for ($i = 1, $trajetsPossibles = []; ! count($trajetsPossibles) && $i <= 4; $i ++) {
                $trajetsPossibles = $this->getTrajets($moment, $i);
            }
            $aller = $moment == 1 || $moment == 4 || $moment == 5;
            $i --;
            if ($this->hasDebugger()) {
                $cr[$this->eleveId][$moment]['nb_circuits'] = $i;
                $this->debugLog(sprintf('moment : %d', $moment));
                $this->debugLog($trajetsPossibles);
            }
            if (count($trajetsPossibles)) {
                // si on a trouvé des trajets
                // @TODO: contrôle des places disponibles
                $trajet = current($trajetsPossibles);
                if ($this->hasDebugger()) {
                    $cr[$this->eleveId][$moment]['trajet'] = $trajet;
                }
                $oAffectation = $this->tAffectations->getObjData();
                $oAffectation->millesime = $this->millesime;
                $oAffectation->eleveId = $this->eleveId;
                $oAffectation->trajet = $this->trajet;
                $oAffectation->moment = $moment;
                $oAffectation->responsableId = $this->responsableId;
                for ($j = 1; $j <= $i; $j ++) {
                    $oAffectation->jours = $trajet["semaine_$j"];
                    $oAffectation->correspondance = $j;
                    if ($aller) {
                        $oAffectation->station1Id = $trajet["station1Id_$j"];
                        $oAffectation->station2Id = $trajet["station2Id_$j"];
                    } else {
                        // remettre correctement la montée et la descente au retour
                        $oAffectation->station2Id = $trajet["station1Id_$j"];
                        $oAffectation->station1Id = $trajet["station2Id_$j"];
                    }
                    $oAffectation->ligne1Id = $trajet["ligne1Id_$j"];
                    $oAffectation->sensligne1 = $trajet["sensligne1_$j"];
                    $oAffectation->ordreligne1 = $trajet["ordreligne1_$j"];
                    if ($this->hasDebugger()) {
                        $cr[$this->eleveId][$moment]['affectation'][$j] = $oAffectation->getArrayCopy();
                    }
                    $this->tAffectations->saveRecord($oAffectation);
                }
                $trouve[$moment] = true;
            }
        }
        if ($this->hasDebugger()) {
            $this->debugLog($cr);
        }
        return $trouve;
    }

    /**
     * Renvoie le Where des requêtes de recherche de trajet. L'établissement fréquenté est
     * identifié par la propriété $this->etablissementId. La station d'origine est
     * identifiée par la propriété $this->stationId. Une correspondance cor1 permet
     * d'utiliser une station jumelle de la station d'origine comme point de départ
     * (matin) ou d'arrivée (midi, soir).
     *
     * @param int $moment
     *            1 pour Matin, 2 pour Midi, 3 pour Soir, 4 pour Après-midi, 5 pour
     *            Dimanche soir
     * @param int $nb_cir
     *            nombre de circuits sur le trajet
     * @return \Zend\Db\Sql\Where
     */
    private function getConditions(int $moment, int $nb_cir)
    {
        $aller = $moment == 1 || $moment == 4 || $moment == 5;
        $where = (new Where())->equalTo('eta.etablissementId', $this->etablissementId)->equalTo(
            'cor1.station1Id', $this->stationId);
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
            case 4:
                $left = sprintf('cir%dsta2.horaireA', $nb_cir);
                $right = 'hAMidi';
                $where->lessThan($left, $right, Where::TYPE_IDENTIFIER,
                    Where::TYPE_IDENTIFIER);
                break;
            default: // pas de condition pour le dimanche soir
                break;
        }
        // sur chaque circuit,
        for ($i = 1; $i <= $nb_cir; $i ++) {
            $where->equalTo(sprintf('cir%dsta1.millesime', $i), $this->millesime)
                ->equalTo(sprintf('cir%dsta1.moment', $i), $moment)
                ->literal(sprintf('cir%dsta1.ouvert = 1', $i))
                ->literal(sprintf('cir%dsta2.ouvert = 1', $i))
                ->literal(sprintf('eta.jOuverture & cir%dsta1.semaine <>0', $i));
            if ($aller) {
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
            if ($aller) {
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

    private function jointureCircuitService(int $rang_correspondance)
    {
        $modele = 'cir%1$dsta1.millesime = ser%1$d.millesime' .
            ' AND cir%1$dsta1.ligneId = ser%1$d.ligneId AND cir%1$dsta1.sens=ser%1$d.sens' .
            ' AND cir%1$dsta1.moment=ser%1$d.moment AND cir%1$dsta1.ordre=ser%1$d.ordre';
        return sprintf($modele, $rang_correspondance);
    }

    private function jointureCircuitCorrespondance(int $rang_correspondance)
    {
        $modele = 'cir%1$dsta1.stationId=cor%1$d.station2Id';
        return sprintf($modele, $rang_correspondance);
    }

    private function jointureCorrespondanceCircuit(int $rang_correspondance)
    {
        $modele = 'cor%d.station1Id=cir%dsta2.stationId';
        return sprintf($modele, $rang_correspondance + 1, $rang_correspondance);
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
     *            1 Matin, 2 Midi, 3 Soir, 4 Après-midi, 5 Dimanche soir
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
        $ordre = [
            'etasta.rang'
        ];
        $ordre_corr = [];
        for ($i = $nb_cir; $i >= 1; $i --) {
            // $columns1 = [];
            $aller = $moment == 1 || $moment == 4 || $moment == 5;
            $columns2 = [
                "station2Id_$i" => 'stationId',
                "horaireStation2_$i" => new Expression(
                    sprintf("DATE_FORMAT(cir%dsta2.", $i) .
                    ($aller ? "horaireA" : "horaireD") . ",'%H:%i')")
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
                array_unshift($ordre_corr, sprintf('cir%dsta2.correspondance DESC', $i));
                $select->join(
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
                    ($aller ? "horaireD" : "horaireA") . ",'%H:%i')")
            ];
            // $columns3=["sta$x1"=>'stationId', "h$x1" => 'horaireD'];
            $select->join(
                [
                    sprintf('cir%dsta1', $i) => $this->db_manager->getCanonicName(
                        'circuits')
                ], $this->jointureSurUnCircuit($i), $columns3)
                ->join(
                [
                    sprintf('ser%d', $i) => $this->db_manager->getCanonicName('services')
                ], $this->jointureCircuitService($i), [])
                ->join([
                sprintf('cor%d', $i) => $this->selectCorr($i)
            ], $this->jointureCircuitCorrespondance($i), $columns1);
            $ordre[] = sprintf('ser%d.rang', $i);
        }
        if ($aller) {
            // partir le plus tard possible
            $ordre[] = 'cir1sta1.horaireD DESC';
        } else {
            // arriver le plus tôt possible
            $ordre[] = 'cir1sta1.horaireA';
        }
        foreach ($ordre_corr as $column) {
            $ordre[] = $column;
        }
$this->debugLog([__METHOD__,'moment'=>$moment, 'nb_cir'=>$nb_cir, 'ordre' => $ordre, 'conditions'=>$this->getConditions($moment, $nb_cir)]);
        $select->where($this->getConditions($moment, $nb_cir))
            ->order($ordre);
        if ($this->hasDebugger()) {
            $this->debugLog($this->getSqlString($select));
        }
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