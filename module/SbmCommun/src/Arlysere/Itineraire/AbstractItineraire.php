<?php
/**
 * Base de recherce des itinéraires
 *
 * @project sbm
 * @package SbmCommun/src/Arlysere/Itineraire
 * @filesource AbstractItineraire.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 29 avr. 2021
 * @version 2021-2.6.1
 */
namespace SbmCommun\Arlysere\Itineraire;

// use SbmBase\Model\StdLib;
use SbmCommun\Model\Db\Service\Query\AbstractQuery;
use SbmCommun\Model\Db\Sql\Replace;
use SbmCommun\Model\Db\Sql\Ddl\CreateLike;
use SbmCommun\Model\Db\Sql\Ddl\CreateSelect;
use SbmCommun\Model\Db\Sql\Ddl\DropIfExists;
use Zend\Db\Sql\Literal;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;

abstract class AbstractItineraire extends AbstractQuery implements ItineraireInterface
{

    public const MAX_NB_TRONCONS = 3;

    // Condition horaire avec prise en compte du temps de trajet à pied dans
    // l'enchainement
    protected const COND_HORAIRE_TEMPS = 'SEC_TO_TIME(TIME_TO_SEC(%s) + TIME_TO_SEC(%s)) < %s';

    /**
     * identifant de l'élève dans la table Eleves
     *
     * @var int
     */
    protected $eleveId;

    /**
     * identifiant de l'établissement dans la table Etablissements
     *
     * @var string
     */
    protected $etablissementId;

    /**
     *
     * @var int|array
     */
    protected $jours;

    /**
     * 1 pour Matin, 2 pour Midi, 3 pour Soir, 4 pour Après-midi, 5 pour Dimanche soir
     *
     * @var int
     */
    protected $moment;

    /**
     * Maternelle = 1
     * Elementaire = 2
     * Primaire = 3 (1 + 2)
     * College = 4
     * Lycée = 8 ou 12 (8 + 4 si classe de 3e) ou 24 ( 8 + 16 si classe BTS) ou 28
     * Après-bac = 16
     *
     * @var int
     */
    protected $niveau;

    /**
     * DP = 0 ; interne = 1
     *
     * @var int
     */
    protected $regimeId;

    /**
     * identifiant du responsable dans la table Responsables
     *
     * @var int
     */
    protected $responsableId;

    /**
     * prend les valeurs 'aller' ou 'retour'
     *
     * @var string
     */
    protected $sensItineraire;

    /**
     * identifiant de la station dans la table Stations
     *
     * @var int
     */
    protected $stationId;

    /**
     * Trajet 1 pour le responsable1 ; trajet 2 pour le responsable2
     *
     * @var int
     */
    protected $trajet;

    /**
     * Itinéraire sur lequel il y a de la place
     *
     * @var array
     */
    protected $itineraire;

    /**
     * Meilleur itinéraire en surbook (celui ou le ratio est le plus bas)
     *
     * @var array
     */
    protected $surbook;

    /**
     *
     * @var \SbmCommun\Model\Db\Service\Table\Affectations
     */
    private $tAffectations;

    /**
     * Permet de modifier le rang des services dans les classes dérivées
     */
    abstract protected function rangService();

    /**
     * Renvoie la constante de filtre associée au moment.
     * Exemple: pour moment = 1 renvoie JOURS_MATIN
     *
     * @param int $moment
     * @return int
     */
    abstract protected function getFiltreJours(int $moment = 0): int;

    /**
     * Renvoie le moment codé dans la base de donnée (de 1 à 5)
     *
     * @param int $moment
     * @return int
     */
    abstract protected function getMoment(int $moment = 0): int;

    /**
     * Initialisation commune aux classes dérivées.
     * La méthode init() des classes dérivées doit appeler cette méthode par:
     * parent::init()
     *
     * {@inheritdoc}
     * @see \SbmCommun\Model\Db\Service\Query\AbstractQuery::init()
     */
    protected function init()
    {
        $this->tAffectations = $this->db_manager->get('Sbm\Db\Table\Affectations');
        $this->itineraire = [];
        $this->surbook = [];
    }

    protected function process(array $arrayMoments)
    {
        foreach ($arrayMoments as $moment) {
            $this->setMoment($moment);
            $this->itineraire[$moment] = null;
            $this->surbook[$moment] = new \ArrayObject(
                [
                    'ratio' => 1000, // on devra trouver mieux
                    'itineraire' => null
                ], \ArrayObject::ARRAY_AS_PROPS);
            // cherche des trajets avec le moins de tronçons possibles
            for ($num_tr = 1, $trajetsPossibles = null; ! $this->valid($trajetsPossibles) &&
                $num_tr <= self::MAX_NB_TRONCONS; $num_tr ++) {
                $trajetsPossibles = new \ArrayObject(
                    [
                        'nb_troncons' => $num_tr,
                        'itineraires' => iterator_to_array(
                            $this->renderResult(
                                $this->prepareItineraire($num_tr)
                                    ->getSelect()))
                    ], \ArrayObject::ARRAY_AS_PROPS);
            }
        }
        $this->putAffectations($arrayMoments);
    }

    /**
     *
     * @param number $eleveId
     * @return self
     */
    public function setEleveId($eleveId)
    {
        $this->eleveId = $eleveId;
        return $this;
    }

    /**
     *
     * @param string $etablissementId
     * @return self
     */
    public function setEtablissementId($etablissementId)
    {
        $this->etablissementId = $etablissementId;
        return $this;
    }

    /**
     * Si on donne un tableau il est encodé en entier pour compatibilité avec
     * l'enregistrement dans la base de données
     *
     * @param int|array $jours
     * @return self
     */
    public function setJours($jours)
    {
        $this->jours = $jours;
        return $this;
    }

    /**
     * Maternelle = 1
     * Elementaire = 2
     * Primaire = 3 (1 + 2)
     * College = 4
     * Lycée = 8 ou 12 (8 + 4 si classe de 3e) ou 24 ( 8 + 16 si classe BTS) ou 28
     * Après-bac = 16
     *
     * @param number $niveau
     * @return self
     */
    public function setNiveau(int $niveau)
    {
        $this->niveau = $niveau;
        return $this;
    }

    /**
     * DP = 0 ; interne = 1
     *
     * @param number $regimeId
     * @return self
     */
    public function setRegimeId(int $regimeId)
    {
        $this->regimeId = $regimeId;
        return $this;
    }

    /**
     *
     * @param number $responsableId
     * @return self
     */
    public function setResponsableId(int $responsableId)
    {
        $this->responsableId = $responsableId;
        return $this;
    }

    /**
     *
     * @param int $moment
     */
    public function setMoment(int $moment)
    {
        $this->moment = $moment;
        switch ($moment) {
            case 1:
            case 4:
            case 5:
                $this->sensItineraire = self::ALLER;
                break;
            default:
                $this->sensItineraire = self::RETOUR;
                break;
        }
        return $this;
    }

    /**
     * C'est la station d'origine (proche du domicile ou la station de prise en charge
     * dans Arlysère)
     *
     * @param number $stationId
     * @return self
     */
    public function setStationId(int $stationId)
    {
        $this->stationId = $stationId;
        return $this;
    }

    /**
     * Tranjet 1 pour le responsable 1 ; trajet 2 pour le responsable 2
     *
     * @param number $trajet
     * @return self
     */
    public function setTrajet($trajet)
    {
        $this->trajet = $trajet;
        return $this;
    }

    /**
     *
     * @return \Zend\Db\Sql\Select
     */
    protected function getSelect(): Select
    {
        return $this->select;
    }

    /**
     * Crée le select de la requête.
     * A l'aller on la crée à partir de l'arrivée à l'établissement.
     * Au retour on la crée à partir du départ de l'établissement.
     *
     * Contexte:
     * Il faut initialiser par leurs setters les propriétés suivantes :<ul>
     * <li>moment (qui initialise aussi sensItineraire par son setter)</li>
     * <li>etablissementId (établissement scolaire de cet élève)</li>
     * <li>stationId (station origine pour cet élève et ce responsable)</li>
     * <li>jours (jours de transport demandés pour cet élève)</li>
     * <
     *
     * @param int $nb_troncons
     * @return self
     */
    protected function prepareItineraire(int $nb_troncons)
    {
        $this->select = $this->sql->select()
            ->quantifier(Select::QUANTIFIER_DISTINCT)
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
        if ($this->sensItineraire == self::ALLER) {
            for ($num_tr = $nb_troncons; $num_tr >= 1; $num_tr --) {
                if ($num_tr == $nb_troncons) {
                    $on = "tr$num_tr.descente_stationId = etasta.stationId";
                } else {
                    $on = $this->jointureTroncons($num_tr);
                }
                $this->select->join([
                    "tr$num_tr" => $this->tableTroncon($num_tr)
                ], $on,
                    [
                        $this->prefixeFieldTroncon($num_tr, 'origine_stationId') => 'origine_stationId',
                        $this->prefixeFieldTroncon($num_tr, 'temps') => 'temps',
                        $this->prefixeFieldTroncon($num_tr, 'montee_stationId') => 'montee_stationId',
                        $this->prefixeFieldTroncon($num_tr, 'horaireD') => 'horaireD',
                        $this->prefixeFieldTroncon($num_tr, 'montee_circuitId') => 'montee_circuitId',
                        $this->prefixeFieldTroncon($num_tr, 'millesime') => 'millesime',
                        $this->prefixeFieldTroncon($num_tr, 'ligneId') => 'ligneId',
                        $this->prefixeFieldTroncon($num_tr, 'sens') => 'sens',
                        $this->prefixeFieldTroncon($num_tr, 'moment') => 'moment',
                        $this->prefixeFieldTroncon($num_tr, 'ordre') => 'ordre',
                        $this->prefixeFieldTroncon($num_tr, 'semaine') => 'semaine',
                        $this->prefixeFieldTroncon($num_tr, 'internes') => 'internes',
                        $this->prefixeFieldTroncon($num_tr, 'nbPlaces') => 'nbPlaces',
                        $this->prefixeFieldTroncon($num_tr, 'descente_stationId') => 'descente_stationId',
                        $this->prefixeFieldTroncon($num_tr, 'horaireA') => 'horaireA',
                        $this->prefixeFieldTroncon($num_tr, 'descente_circuitId') => 'descente_circuitId',
                        $this->prefixeFieldTroncon($num_tr, 'correspondance') => 'correspondance',
                        $this->prefixeFieldTroncon($num_tr, 'montee_passage') => 'montee_passage',
                        $this->prefixeFieldTroncon($num_tr, 'descente_passage') => 'descente_passage'
                    ]);
            }
        } else {
            for ($num_tr = 1; $num_tr <= $nb_troncons; $num_tr ++) {
                if ($num_tr == 1) {
                    $on = "tr1.montee_stationId = etasta.stationId";
                } else {
                    $on = $this->jointureTroncons($num_tr);
                }
                $this->select->join([
                    "tr$num_tr" => $this->tableTroncon($num_tr)
                ], $on,
                    [
                        $this->prefixeFieldTroncon($num_tr, 'montee_stationId') => 'montee_stationId',
                        $this->prefixeFieldTroncon($num_tr, 'horaireD') => 'horaireD',
                        $this->prefixeFieldTroncon($num_tr, 'montee_circuitId') => 'montee_circuitId',
                        $this->prefixeFieldTroncon($num_tr, 'millesime') => 'millesime',
                        $this->prefixeFieldTroncon($num_tr, 'ligneId') => 'ligneId',
                        $this->prefixeFieldTroncon($num_tr, 'sens') => 'sens',
                        $this->prefixeFieldTroncon($num_tr, 'moment') => 'moment',
                        $this->prefixeFieldTroncon($num_tr, 'ordre') => 'ordre',
                        $this->prefixeFieldTroncon($num_tr, 'semaine') => 'semaine',
                        $this->prefixeFieldTroncon($num_tr, 'internes') => 'internes',
                        $this->prefixeFieldTroncon($num_tr, 'nbPlaces') => 'nbPlaces',
                        $this->prefixeFieldTroncon($num_tr, 'descente_stationId') => 'descente_stationId',
                        $this->prefixeFieldTroncon($num_tr, 'horaireA') => 'horaireA',
                        $this->prefixeFieldTroncon($num_tr, 'descente_circuitId') => 'descente_circuitId',
                        $this->prefixeFieldTroncon($num_tr, 'correspondance') => 'correspondance',
                        $this->prefixeFieldTroncon($num_tr, 'destination_stationId') => 'destination_stationId',
                        $this->prefixeFieldTroncon($num_tr, 'temps') => 'temps'
                    ]);
            }
        }
        $this->select->order($this->ordre($nb_troncons));
        return $this;
    }

    protected function prefixeFieldTroncon(int $tr_num, string $field_name): string
    {
        return sprintf('tr%d_%s', $tr_num, $field_name);
    }

    private function tableTroncon(int $num_troncon)
    {
        $table_name = "tmp_troncon$num_troncon";
        $dbAdapter = $this->db_manager->getDbAdapter();
        $dropIfExists = new DropIfExists($table_name);
        $dbAdapter->query($dropIfExists->buildSqlString($dbAdapter->getPlatform()),
            $dbAdapter::QUERY_MODE_EXECUTE);
        if ($this->sensItineraire == self::ALLER) {
            $select = $this->selectTronconAller();
        } else {
            $select = $this->selectTronconRetour();
        }
        $createSelect = new CreateSelect($table_name, $select,
            [
                CreateLike::TEMPORARY => true,
                CreateLike::IF_NOT_EXISTS => true
            ]);
        $dbAdapter->query($createSelect->buildSqlString($dbAdapter->getPlatform()),
            $dbAdapter::QUERY_MODE_EXECUTE);
        return $table_name;
    }

    private function selectTronconAller(): Select
    {
        $select = $this->sql->select()
            ->columns(
            [
                'descente_stationId' => 'stationId',
                'horaireA' => 'horaireA',
                'descente_circuitId' => 'circuitId',
                'descente_passage' => 'passage',
                'correspondance' => 'correspondance',
                'semaine' => new Literal(
                    'montee.semaine & ser.semaine & descente.semaine')
            ])
            ->from([
            'descente' => $this->db_manager->getCanonicName('circuits')
        ])
            ->join([
            'ser' => $this->db_manager->getCanonicName('services')
        ],
            implode(' And ',
                [
                    'descente.millesime = ser.millesime',
                    'descente.ligneId = ser.ligneId',
                    'descente.sens = ser.sens',
                    'descente.moment = ser.moment',
                    'descente.ordre = ser.ordre'
                ]),
            [
                'millesime',
                'ligneId',
                'sens',
                'moment',
                'ordre',
                'nbPlaces',
                'rangService' => $this->rangService(),
                'pertinenceJours' => $this->pertinenceJours()
            ])
            ->join([
            'montee' => $this->db_manager->getCanonicName('circuits')
        ],
            implode(' And ',
                [
                    'montee.millesime = ser.millesime',
                    'montee.ligneId = ser.ligneId',
                    'montee.sens = ser.sens',
                    'montee.moment = ser.moment',
                    'montee.ordre = ser.ordre'
                ]),
            [
                'montee_stationId' => 'stationId',
                'horaireD' => 'horaireD',
                'montee_circuitId' => 'circuitId',
                'montee_passage' => 'passage'
            ])
            ->join([
            'staequi' => $this->tableStationsEquivalentes('staequi')
        ], 'staequi.station2Id = montee.stationId',
            [
                'origine_stationId' => 'station1Id',
                'temps' => 'temps'
            ])
            ->join([
            'lig' => $this->db_manager->getCanonicName('lignes')
        ], 'lig.millesime = ser.millesime And lig.ligneId = ser.ligneId', [
            'internes'
        ])
            ->join([
            'monsta' => $this->db_manager->getCanonicName('stations')
        ], 'montee.stationId = monsta.stationId', [])
            ->join([
            'dessta' => $this->db_manager->getCanonicName('stations')
        ], 'descente.stationId = dessta.stationId', [])
            ->where($this->getWhereTroncon());
        // die($this->getSqlString($select));
        return $select;
    }

    private function selectTronconRetour(): Select
    {
        $select = $this->sql->select()
            ->columns(
            [
                'montee_stationId' => 'stationId',
                'horaireD' => 'horaireD',
                'montee_circuitId' => 'circuitId',
                'montee_passage' => 'passage',
                'semaine' => new Literal(
                    'montee.semaine & ser.semaine & descente.semaine')
            ])
            ->from([
            'montee' => $this->db_manager->getCanonicName('circuits')
        ])
            ->join([
            'ser' => $this->db_manager->getCanonicName('services')
        ],
            implode(' And ',
                [
                    'montee.millesime = ser.millesime',
                    'montee.ligneId = ser.ligneId',
                    'montee.sens = ser.sens',
                    'montee.moment = ser.moment',
                    'montee.ordre = ser.ordre'
                ]),
            [
                'millesime',
                'ligneId',
                'sens',
                'moment',
                'ordre',
                'nbPlaces',
                'rangService' => $this->rangService(),
                'pertinenceJours' => $this->pertinenceJours()
            ])
            ->join([
            'descente' => $this->db_manager->getCanonicName('circuits')
        ],
            implode(' And ',
                [
                    'descente.millesime = ser.millesime',
                    'descente.ligneId = ser.ligneId',
                    'descente.sens = ser.sens',
                    'descente.moment = ser.moment',
                    'descente.ordre = ser.ordre'
                ]),
            [
                'descente_stationId' => 'stationId',
                'horaireA' => 'horaireA',
                'descente_circuitId' => 'circuitId',
                'descente_passage' => 'passage',
                'correspondance' => 'correspondance'
            ])
            ->join([
            'staequi' => $this->tableStationsEquivalentes('staequi')
        ], 'staequi.station2Id = descente.stationId',
            [
                'destination_stationId' => 'station1Id',
                'temps' => 'temps'
            ])
            ->join([
            'lig' => $this->db_manager->getCanonicName('lignes')
        ], 'lig.millesime = ser.millesime And lig.ligneId = ser.ligneId', [
            'internes'
        ])
            ->join([
            'monsta' => $this->db_manager->getCanonicName('stations')
        ], 'montee.stationId = monsta.stationId', [])
            ->join([
            'dessta' => $this->db_manager->getCanonicName('stations')
        ], 'descente.stationId = dessta.stationId', [])
            ->where($this->getWhereTroncon());
        return $select;
    }

    /**
     * Renvoie une expression Sql permettant de classer en premier les solutions qui
     * conviennent à tous les jours nécessaires.
     *
     * @return Literal
     */
    protected function pertinenceJours(): Literal
    {
        $str = '';
        $semaine = sprintf('(ser.semaine & %d)', $this->getFiltreJours());
        for ($j = 0; $j < 7; $j ++) {
            $str .= sprintf('+(%1$s & %2$d = %2$d)', $semaine, 1 << $j);
        }
        $str = ltrim($str, '+');
        return new Literal($str);
    }

    protected function getWhereTroncon(): Where
    {
        $filtre = $this->getFiltreJours();
        $where = new Where();
        return $where->equalTo('montee.millesime', $this->millesime)
            ->equalTo('montee.moment', $this->getMoment())
            ->literal('montee.horaireD < descente.horaireA')
            ->literal('montee.ouvert = 1')
            ->literal('monsta.ouverte = 1')
            ->literal('descente.ouvert = 1')
            ->literal('dessta.ouverte = 1')
            ->literal('ser.actif = 1')
            ->literal('lig.actif = 1')
            ->expression('ser.semaine & ? <> 0', $filtre)
            ->expression('montee.semaine & ? <> 0', $filtre)
            ->expression('descente.semaine & ? <> 0', $filtre);
    }

    /**
     * Renvoie une requête donnant les colonnes station1Id, station2Id et temps où se
     * passe une correspondance.
     * Règle générale : station1Id = station2Id
     * Cas particulier : station1Id <> station2Id mais les 2 stations sont jumelles
     *
     * @param string $table_name
     * @return string
     */
    private function tableStationsEquivalentes(string $table_name)
    {
        $table1 = $this->db_manager->getCanonicName('stations');
        $table2 = $this->db_manager->getCanonicName('stations-stations');
        // on prend les stations jumelles (station1Id,station2Id)
        $select1 = new Select();
        $select1->columns([
            'station1Id',
            'station2Id',
            'temps'
        ])->from($table2);
        // on rajoute (UNION) les stations jumelles symétriques (station2Id,station1Id)
        $select2 = new Select();
        $select2->columns([
            'station2Id',
            'station1Id',
            'temps'
        ])
            ->from($table2)
            ->combine($select1);
        // on ajoute (UNION) les stations non jumelles (stationId,stationId)
        $union = new Select();
        // artifice nécessaire pour union de 3 tables
        $union->columns(
            [
                'station1Id' => 'stationId',
                'station2Id' => 'stationId',
                'temps' => new Literal('"00:00:00"')
            ])
            ->from($table1)
            ->combine(new Select([
            'c' => $select2
        ]));
        $table_name = "tmp_$table_name";
        $createLike = new CreateLike($table_name, $table2,
            [
                CreateLike::TEMPORARY => true,
                CreateLike::IF_NOT_EXISTS => true
            ]);
        $dbAdapter = $this->db_manager->getDbAdapter();
        $dbAdapter->query($createLike->buildSqlString($dbAdapter->getPlatform()),
            $dbAdapter::QUERY_MODE_EXECUTE);
        $replace = new Replace($table_name);
        $replace->setUnion($union);
        $dbAdapter->query($this->sql->buildSqlString($replace),
            $dbAdapter::QUERY_MODE_EXECUTE);
        return $table_name;
    }

    /**
     * A l'aller, jointure du tronçon $n au tronçon suivant ($n + 1)
     * Au retour, jointure du tronçon $n au tronçon précédent ($n - 1)
     *
     * @param int $n
     * @return $string
     */
    private function jointureTroncons(int $n): string
    {
        if ($this->sensItineraire == self::ALLER) {
            return sprintf("tr%s.descente_stationId = tr%s.origine_stationId", $n, $n + 1);
        } else {
            return sprintf("tr%s.destination_stationId = tr%s.montee_stationId", $n - 1,
                $n);
        }
    }

    /**
     * Les priorités sont dans l'ordre suivant : <ol>
     * <li>Solutions valables pour le plus de jours demandés (pertinenceJours)</li>
     * <li>Départ sur le service de rang le plus bas (ser1.rang)</li>
     * <li>S’il y a des correspondances, priorité à celles qui ont été repérées
     * (descente%d.correspondance DESC où %d va de 1 à nb_de_tronçons - 1)</li>
     * <li>A l’aller partir le plus tard possible ; au retour arriver le plus tôt possible
     * (montee1.horaireD DESC ou descente%d.horaireA)</li>
     * <li>Station desservant l’établissement de rang le plus bas (etasta.rang)</li>
     * <li>S’il y a des correspondances, choisir les services de correspondance de rangs
     * les plus bas possibles (ser%d.rang où %d va de 2 à nb_de_tronçons)</li>
     * <li>En cas de boucles, ordre de passage le plus petit possible</li></ol>
     *
     * @param int $nb_troncons
     * @return string[]
     */
    protected function ordre(int $nb_troncons)
    {
        $ordre = [];
        for ($tr_num = 1, $pertinenceJours = ''; $tr_num <= $nb_troncons; $tr_num ++) {
            $pertinenceJours .= "+ tr$tr_num.pertinenceJours ";
        }
        // 1 - Solutions valables pour le plus de jours demandés (pertinenceJours)
        $ordre[] = new Literal(trim(ltrim($pertinenceJours, '+')));
        // 2 - Départ sur le service de rang le plus bas (ser1.rang)
        $ordre[] = 'tr1.rangService';
        // 3 - Priorité aux points de correspondance repérés
        for ($tr_num = 1; $tr_num < $nb_troncons; $tr_num ++) {
            $ordre[] = sprintf('tr%d.correspondance DESC', $tr_num);
        }
        if ($this->sensItineraire == self::ALLER) {
            // 4 - partir le plus tard possible
            $ordre[] = 'tr1.horaireD DESC';
        } else {
            // 4 - arriver le plus tôt possible
            $ordre[] = sprintf('tr%d.horaireA', $nb_troncons);
        }
        // 5 - Station desservant l’établissement de rang le plus bas (etasta.rang)
        $ordre[] = 'etasta.rang';
        // 6 - Correspondances de rang le plus bas
        for ($tr_num = 2; $tr_num <= $nb_troncons; $tr_num ++) {
            $ordre[] = sprintf('tr%d.rangService', $tr_num);
        }
        // 7 - Durée de trajet la plus petite (en cas de boucles)
        /*
         * for ($i = 1; $i <= $nb_troncons; $i ++) {
         * $ordre[] = sprintf('tr%d.montee_passage', $i);
         * $ordre[] = sprintf('tr%d.descente_passage', $i);
         * }
         */
        return $ordre;
    }

    /**
     * Renvoie true si un itinéraire proposé a de la place pour cet élève.
     * L'itinéraire est enregistré dans la propriété itineraire.
     * Parallèlement, on garde en mémoire l'itinéraire en surbook de ratio
     * effectif/nbPlaces le plus faible.
     *
     * @param \ArrayObject $oItineraires
     * @return bool
     */
    protected function valid(\ArrayObject $oItineraires = null): bool
    {
        if (empty($oItineraires->itineraires)) {
            $this->itineraire[$this->moment] = null;
            return false;
        }
        $ListeTronconsSansPlace = [];
        $effectifCircuits = $this->db_manager->get('Sbm\Db\Eleve\EffectifCircuits');
        $effectifCircuits->setSanspreinscrits(true)
            ->setMillesime($this->millesime)
            ->setMoment($this->getMoment());
        foreach ($oItineraires->itineraires as $itineraire) {
            $deLaPlace = true;
            $surbook = new \ArrayObject([
                'ratio' => 1,
                'itineraire' => null
            ], \ArrayObject::ARRAY_AS_PROPS);
            for ($num_tr = 1; $num_tr <= $oItineraires->nb_troncons; $num_tr ++) {
                $ratio = $this->chargeSurTroncon($itineraire, $num_tr, $effectifCircuits);
                if ($ratio > 1) {
                    $deLaPlace = false;
                    if ($ratio > $surbook->ratio) {
                        $surbook->ratio = $ratio;
                    }
                }
            }
            if ($deLaPlace) {
                $this->itineraire[$this->moment] = new \ArrayObject(
                    [
                        'nb_troncons' => $oItineraires->nb_troncons,
                        'itineraire' => $itineraire
                    ], \ArrayObject::ARRAY_AS_PROPS);
                return true;
            } else {
                if ($surbook->ratio < $this->surbook[$this->moment]->ratio) {
                    $surbook->itineraire = new \ArrayObject(
                        [
                            'nb_troncons' => $oItineraires->nb_troncons,
                            'itineraire' => $itineraire
                        ], \ArrayObject::ARRAY_AS_PROPS);
                    $this->surbook[$this->moment] = $surbook;
                }
            }
        }
        return false;
    }

    private function chargeSurTroncon($itineraire, $num_tr, $effectifCircuits): float
    {
        $nbPlaces = $itineraire[$this->prefixeFieldTroncon($num_tr, 'nbPlaces')];
        if ($this->sensItineraire == self::ALLER) {
            $nbPlaces += self::TOLERANCE_ALLER;
        } else {
            $nbPlaces += self::TOLERANCE_RETOUR;
        }
        $effectifCircuits->setLigneId(
            $itineraire[$this->prefixeFieldTroncon($num_tr, 'ligneId')])
            ->setSens($itineraire[$this->prefixeFieldTroncon($num_tr, 'sens')])
            ->setOrdre($itineraire[$this->prefixeFieldTroncon($num_tr, 'ordre')])
            ->init();
        return $effectifCircuits->effectifMaxEntre(
            $itineraire[$this->prefixeFieldTroncon($num_tr, 'montee_circuitId')],
            $itineraire[$this->prefixeFieldTroncon($num_tr, 'descente_circuitId')]) /
            $nbPlaces;
    }

    /**
     * Enregistre les solutions trouvées dans la table `affectations`
     */
    protected function putAffectations(array $arrayMoments)
    {
        foreach ($arrayMoments as $moment) {
            $oAffectation = $this->getObjAffectation();
            $oAffectation->moment = $this->getMoment($moment);
            $nb_troncons = 0;
            if (! empty($this->itineraire[$moment])) {
                $nb_troncons = $this->itineraire[$moment]->nb_troncons;
                $itineraire = $this->itineraire[$moment]->itineraire;
            } elseif (is_null($this->surbook[$moment]->itineraire)) {
                continue;
            } else {
                $nb_troncons = $this->surbook[$moment]->itineraire->nb_troncons;
                $itineraire = $this->surbook[$moment]->itineraire->itineraire;
            }
            for ($tr_num = 1; $tr_num <= $nb_troncons; $tr_num ++) {
                $oAffectation->correspondance = $tr_num;
                $oAffectation->jours = $itineraire[$this->prefixeFieldTroncon($tr_num,
                    'semaine')] & $this->getFiltreJours($moment);
                $oAffectation->ligne1Id = $itineraire[$this->prefixeFieldTroncon($tr_num,
                    'ligneId')];
                $oAffectation->sensligne1 = $itineraire[$this->prefixeFieldTroncon(
                    $tr_num, 'sens')];
                $oAffectation->ordreligne1 = $itineraire[$this->prefixeFieldTroncon(
                    $tr_num, 'ordre')];
                $oAffectation->station1Id = $itineraire[$this->prefixeFieldTroncon(
                    $tr_num, 'montee_stationId')];
                $oAffectation->station2Id = $itineraire[$this->prefixeFieldTroncon(
                    $tr_num, 'descente_stationId')];
                $this->tAffectations->saveRecord($oAffectation);
            }
        }
    }

    private function getObjAffectation(): \SbmCommun\Model\Db\ObjectData\ObjectDataInterface
    {
        return $this->tAffectations->getObjData()->exchangeArray(
            [
                'millesime' => $this->millesime,
                'eleveId' => $this->eleveId,
                'trajet' => $this->trajet,
                'responsableId' => $this->responsableId
            ]);
    }
}