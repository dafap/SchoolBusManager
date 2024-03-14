<?php
/**
 * Renvoie un tableau d'effectifs pour les statistiques
 *
 * @project sbm
 * @package SbmGestion/Model/Db/Service/Eleve
 * @filesource Effectif.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 14 mars 2024
 * @version 2024-2.6.8
 */
namespace SbmGestion\Model\Db\Service\Eleve;

use SbmBase\Model\Session;
use SbmCommun\Model\Db\Exception;
use SbmCommun\Model\Db\Service\DbManager;
use SbmCommun\Model\Traits\ExpressionSqlTrait;
use SbmCommun\Model\Traits\SqlStringTrait;
use SbmGestion\Model\Db\Service\AbstractQuery;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Where;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class Effectif extends AbstractQuery implements FactoryInterface
{
    use ExpressionSqlTrait, SqlStringTrait;

    /**
     *
     * @var \SbmCommun\Model\Db\Service\DbManager
     */
    private $db_manager;

    /**
     *
     * @var integer
     */
    private $millesime;

    /**
     *
     * @var \Zend\Db\Sql\Sql
     */
    private $sql;

    /**
     *
     * @var array
     */
    private $tableName = [];

    public function createService(ServiceLocatorInterface $db_manager)
    {
        if (! ($db_manager instanceof DbManager)) {
            $message = 'SbmCommun\Model\Db\Service\DbManager attendu. %s reçu.';
            throw new Exception\ExceptionNoDbManager(
                sprintf($message, gettype($db_manager)));
        }
        $this->db_manager = $db_manager;
        $this->millesime = Session::get('millesime');
        $this->tableName['affectations'] = $db_manager->getCanonicName('affectations',
            'table');
        $this->tableName['circuits'] = $db_manager->getCanonicName('circuits', 'table');
        $this->tableName['classes'] = $db_manager->getCanonicName('classes', 'table');
        $this->tableName['communes'] = $db_manager->getCanonicName('communes', 'table');
        $this->tableName['eleves'] = $db_manager->getCanonicName('eleves', 'table');
        $this->tableName['etablissements'] = $db_manager->getCanonicName('etablissements',
            'table');
        $this->tableName['responsables'] = $db_manager->getCanonicName('responsables',
            'table');
        $this->tableName['scolarites'] = $db_manager->getCanonicName('scolarites', 'table');
        $this->tableName['services'] = $db_manager->getCanonicName('services', 'table');
        $this->sql = new Sql($db_manager->getDbAdapter());
        return $this;
    }

    /**
     * Statistiques
     */

    /**
     * Renvoie un tableau statistiques pour l'année en cours et pour l'année précédente
     *
     * @return array <br><code>['annee_courante' => [], 'annee_precedente' =>
     *         []]</code><br> où chaque tableau est composé de lignes de la forme :<br>
     *         <code>['nom' => nnn, 'alias' => aaa, 'inscrits' => value, 'internet' =>
     *         value, 'papier' => value, 'transportes' => value]</code> <br>où<ul>
     *         <li><b>nom</b> est le nom de la classe</li> <li><b>alias</b> est
     *         l'aliasCG</li> <li><b>inscrits</b> est le nombre d'inscrits</li>
     *         <li><b>internet</b> est le nombre d'inscrits par internet</li>
     *         <li><b>papier</b> est le nombre d'inscrits par fiche papier</li>
     *         <li><b>transportes</b> est le nombre d'élève transportés</li></ul>
     */
    public function statistiquesParClasse()
    {
        $result = [
            'annee_courante' => [],
            'annee_precedente' => []
        ];
        $statement = $this->sql->prepareStatementForSqlObject(
            $this->selectStatistiquesParClasse($this->millesime));
        $result['annee_courante'] = iterator_to_array($statement->execute());
        $statement = $this->sql->prepareStatementForSqlObject(
            $this->selectStatistiquesParClasse($this->millesime - 1));
        $result['annee_precedente'] = iterator_to_array($statement->execute());
        // die(var_dump($result));
        return $result;
    }

    /**
     * Définit une requête pour les statistiques portant sur un millesime
     *
     * @param int $millesime
     *            Millesime sur lequel porte les calculs
     * @return \Zend\Db\Sql\Select
     */
    private function selectStatistiquesParClasse($millesime)
    {
        // Tous les inscrits
        // SELECT `sbm_t_scolarites`.`classeId`, count(`sbm_t_scolarites`.`eleveId`) AS
        // `inscrits`
        // FROM `sbm_t_scolarites`
        // WHERE inscrit = 1 AND `millesime` = '2015'
        // GROUP BY `sbm_t_scolarites`.`classeId`
        $whereInscrits = new Where();
        $whereInscrits->literal('inscrit = 1')->equalTo('millesime', $millesime);
        $selectInscrits = $this->sql->select($this->tableName['scolarites'])
            ->group([
            'classeId'
        ])
            ->columns([
            'classeId',
            'inscrits' => new Expression('count(eleveId)')
        ])
            ->where($whereInscrits);

        // Inscrits par internet
        // SELECT `sbm_t_scolarites`.`classeId`, count(`sbm_t_scolarites`.`eleveId`) AS
        // `internet`
        // FROM `sbm_t_scolarites`
        // WHERE inscrit = 1 AND internet = 1 AND `millesime` = '2015'
        // GROUP BY `sbm_t_scolarites`.`classeId`
        $whereInternet = new Where();
        $whereInternet->literal('inscrit = 1')
            ->literal('internet = 1')
            ->equalTo('millesime', $millesime);
        $selectInternet = $this->sql->select($this->tableName['scolarites'])
            ->group([
            'classeId'
        ])
            ->columns([
            'classeId',
            'internet' => new Expression('count(eleveId)')
        ])
            ->where($whereInternet);

        // Inscrits par fiche papier
        // SELECT `sbm_t_scolarites`.`classeId`, count(`sbm_t_scolarites`.`eleveId`) AS
        // `papier`
        // FROM `sbm_t_scolarites`
        // WHERE inscrit = 1 AND internet = 0 AND `millesime` = '2015'
        // GROUP BY `sbm_t_scolarites`.`classeId`
        $wherePapier = new Where();
        $wherePapier->literal('inscrit = 1')
            ->literal('internet = 0')
            ->equalTo('millesime', $millesime);
        $selectPapier = $this->sql->select($this->tableName['scolarites'])
            ->group([
            'classeId'
        ])
            ->columns([
            'classeId',
            'papier' => new Expression('count(eleveId)')
        ])
            ->where($wherePapier);

        // SELECT DISTINCT `sbm_t_scolarites`.`classeId`, `sbm_t_scolarites`.`eleveId`
        // FROM `sbm_t_scolarites`
        // INNER JOIN `sbm_t_affectations` ON
        // `sbm_t_affectations`.`millesime`=`sbm_t_scolarites`.`millesime` AND
        // `sbm_t_affectations`.`eleveId`=`sbm_t_scolarites`.`eleveId`
        // WHERE `sbm_t_scolarites`.`millesime` = '2015' AND `sbm_t_scolarites`.`inscrit`
        // = 1
        $whereTransportes = new Where();
        $whereTransportes->equalTo('sco.millesime', $millesime)->literal('inscrit = 1');
        $selectElevesTransportes = $this->sql->select(
            [
                'sco' => $this->tableName['scolarites']
            ])
            ->join([
            'aff' => $this->tableName['affectations']
        ], 'sco.millesime = aff.millesime AND sco.eleveId = aff.eleveId', [])
            ->columns([
            'classeId',
            'eleveId'
        ])
            ->quantifier(Select::QUANTIFIER_DISTINCT)
            ->where($whereTransportes);

        // SELECT `aff`.`classeId`, count(`aff`.`eleveId`) AS `transportes`
        // FROM (requete_precedente) AS `aff`
        // GROUP BY `aff`.`classeId`
        $selectTransportes = $this->sql->select([
            'aff' => $selectElevesTransportes
        ])
            ->group([
            'classeId'
        ])
            ->columns([
            'classeId',
            'transportes' => new Expression('count(eleveId)')
        ]);

        $select = $this->sql->select();
        $select->from([
            'cla' => $this->tableName['classes']
        ])
            ->join([
            'inscrits' => $selectInscrits
        ], 'inscrits.classeId=cla.classeId', [
            'inscrits'
        ], Select::JOIN_LEFT)
            ->join([
            'internet' => $selectInternet
        ], 'internet.classeId=cla.classeId', [
            'internet'
        ], Select::JOIN_LEFT)
            ->join([
            'papier' => $selectPapier
        ], 'papier.classeId=cla.classeId', [
            'papier'
        ], Select::JOIN_LEFT)
            ->join([
            'transportes' => $selectTransportes
        ], 'transportes.classeId=cla.classeId', [
            'transportes'
        ], Select::JOIN_LEFT)
            ->columns([
            'nom',
            'alias' => 'aliasCG'
        ])
            ->order([
            'cla.niveau ASC',
            'cla.nom DESC'
        ]);
        // die($this->getSqlString($select));
        return $select;
    }

    /**
     * Renvoie un tableau statistiques pour l'année en cours et pour l'année précédente
     *
     * @return array <br><code>['annee_courante' => [], 'annee_precedente' =>
     *         []]</code><br> où chaque tableau est composé de lignes de la forme :<br>
     *         <code>['nom' => nnn, 'alias' => aaa, 'inscrits' => value, 'internet' =>
     *         value, 'papier' => value, 'transportes' => value]</code> <br>où<ul>
     *         <li><b>nom</b> est le nom de la classe</li> <li><b>alias</b> est
     *         l'aliasCG</li> <li><b>inscrits</b> est le nombre d'inscrits</li>
     *         <li><b>internet</b> est le nombre d'inscrits par internet</li>
     *         <li><b>papier</b> est le nombre d'inscrits par fiche papier</li>
     *         <li><b>transportes</b> est le nombre d'élève transportés</li></ul>
     */
    public function statistiquesParCommune()
    {
        $result = [
            'annee_courante' => [],
            'annee_precedente' => []
        ];
        $statement = $this->sql->prepareStatementForSqlObject(
            $this->selectStatistiquesParCommune($this->millesime));
        $result['annee_courante'] = iterator_to_array($statement->execute());
        $statement = $this->sql->prepareStatementForSqlObject(
            $this->selectStatistiquesParCommune($this->millesime - 1));
        $result['annee_precedente'] = iterator_to_array($statement->execute());
        // die(var_dump($result));
        return $result;
    }

    private function selectStatistiquesParCommune($millesime)
    {
        // Requête donnant les eleveId sans doublons des élèves transportés pour un
        // millesime donné
        $select_affectations = $this->sql->select($this->tableName['affectations'])
            ->quantifier(Select::QUANTIFIER_DISTINCT)
            ->columns([
            'millesime',
            'eleveId'
        ])
            ->where([
            'millesime' => $millesime
        ]);
        // Requête donnant les élèves inscrits sans doublons d'un millésime donné,
        // avec internet et affectation
        // (affectation est null si pas d'affectation, non null s'il y en a au moins une)
        $select_eleves = $this->sql->select([
            'ele' => $this->tableName['eleves']
        ])
            ->join([
            'sco' => $this->tableName['scolarites']
        ], 'ele.eleveId = sco.eleveId', [
            'internet',
            'demandeR1',
            'demandeR2'
        ])
            ->join([
            'aff' => $select_affectations
        ], 'sco.millesime = aff.millesime AND sco.eleveId = aff.eleveId',
            [
                'affectation' => 'eleveId'
            ], Select::JOIN_LEFT)
            ->columns([
            'eleveId',
            'responsable1Id',
            'responsable2Id'
        ])
            ->where([
            'sco.millesime' => $millesime,
            'inscrit = 1'
        ]);
        // Requête donnant les responsableId avec leur commune
        $select_responsables = $this->sql->select([
            'c' => $this->tableName['communes']
        ])
            ->join([
            'r' => $this->tableName['responsables']
        ], 'c.communeId = r.communeId', [
            'responsableId'
        ])
            ->columns([
            'commune' => 'nom'
        ]);
        // Requête
        $select = $this->sql->select([
            'com' => $this->tableName['communes']
        ])
            ->join(
            [
                'inscrits' => $this->selectPartielParCommune('inscrits', $select_eleves,
                    $select_responsables)
            ], 'com.nom = inscrits.nom', [
                'inscrits'
            ], Select::JOIN_LEFT)
            ->join(
            [
                'internet' => $this->selectPartielParCommune('internet', $select_eleves,
                    $select_responsables, [
                        'internet' => 1
                    ])
            ], 'com.nom = internet.nom', [
                'internet'
            ], Select::JOIN_LEFT)
            ->join(
            [
                'papier' => $this->selectPartielParCommune('papier', $select_eleves,
                    $select_responsables, [
                        'internet' => 0
                    ])
            ], 'com.nom = papier.nom', [
                'papier'
            ], Select::JOIN_LEFT)
            ->join(
            [
                'transportes' => $this->selectPartielParCommune('transportes',
                    $select_eleves, $select_responsables, [
                        'affectation Is Not Null'
                    ])
            ], 'com.nom = transportes.nom', [
                'transportes'
            ], Select::JOIN_LEFT)
            ->columns([
            'nom'
        ])
            ->where([
            'desservie' => 1
        ]);
        // die($this->getSqlString($select));
        return $select;
    }

    /**
     * Le champs de la requête rendue sont le nom de la commune et l'effectif sous le nom
     * demandé.
     *
     * @param string $compteur
     *            Nom du champ renvoyant l'effectif
     * @param Select $select_eleves
     * @param Select $select_responsables
     * @param array $where
     *            Les conditions peuvent être :<ul> <li>internet = 1</li> <li>internet =
     *            0</li> <li>affectation Is Not Null</li></ul>
     * @return \Zend\Db\Sql\Select
     */
    private function selectPartielParCommune($compteur, Select $select_eleves,
        Select $select_responsables, array $where = [])
    {
        // Requête donnant les élèves par commune par le R2
        $select2 = $this->sql->select([
            'res' => $select_responsables
        ])
            ->join([
            'elv' => $select_eleves
        ], 'elv.responsable2Id = res.responsableId', [
            'eleveId'
        ])
            ->columns([
            'commune'
        ])
            ->where(array_merge([
            'demandeR2 > 0'
        ], $where));

        // Requête donnant les élèves par commune par le R1
        $select1 = $this->sql->select([
            'res' => $select_responsables
        ])
            ->join([
            'elv' => $select_eleves
        ], 'elv.responsable1Id = res.responsableId', [
            'eleveId'
        ])
            ->columns([
            'commune'
        ])
            ->where(array_merge([
            'demandeR1 > 0'
        ], $where));

        // union des deux requêtes
        $select1->combine($select2);

        // comptage des élèves
        $select = $this->sql->select([
            'tmp' => $select1
        ])
            ->group('commune')
            ->columns(
            [
                'nom' => 'commune',
                $compteur => new Expression('count(eleveId)')
            ]);
        return $select;
    }

    /**
     * Renvoie un tableau statistiques pour l'année en cours et pour l'année
     * précédente<br>
     *
     *
     * @return array <br><code>['annee_courante' => [], 'annee_precedente' =>
     *         [])</code><br> où chaque tableau est composé de lignes de la forme :<br>
     *         <code>['nom' => nnn, 'inscrits' => value, 'internet' => value, 'papier' =>
     *         value]</code> <br>où<ul> <li><b>nom</b> est le code du circuit</li>
     *         <li><b>inscrits</b> est le nombre d'élèves transportés</li>
     *         <li><b>internet</b> est le nombre d'inscrits par internet</li>
     *         <li><b>papier</b> est le nombre d'inscrits par fiche papier</li><ul>
     */
    public function statistiquesParCircuit()
    {
        $result = [
            'annee_courante' => [],
            'annee_precedente' => []
        ];
        $statement = $this->sql->prepareStatementForSqlObject(
            $this->selectStatistiquesParCircuit($this->millesime));
        $result['annee_courante'] = iterator_to_array($statement->execute());
        $statement = $this->sql->prepareStatementForSqlObject(
            $this->selectStatistiquesParCircuit($this->millesime - 1));
        $result['annee_precedente'] = iterator_to_array($statement->execute());
        // die(var_dump($result));
        return $result;
    }

    private function selectStatistiquesParCircuit($millesime)
    {
        $subSelectBase = $this->sql->select([
            's' => $this->tableName['services']
        ])
            ->columns([
            'ligneId',
            'sens',
            'moment',
            'ordre'
        ])
            ->join([
            'aff' => $this->tableName['affectations']
        ],
            sprintf('(%s) OR (%s)',
                implode(' AND ',
                    [
                        'aff.ligne1Id = s.ligneId',
                        'aff.sensligne1 = s.sens',
                        'aff.moment = s.moment',
                        'aff.ordreligne1 = s.ordre'
                    ]),
                implode(' AND ',
                    [
                        'aff.ligne2Id = s.ligneId',
                        'aff.sensligne2 = s.sens',
                        'aff.moment = s.moment',
                        'aff.ordreligne2 = s.ordre'
                    ])), [
                'eleveId'
            ], Select::JOIN_LEFT)
            ->join([
            'sco' => $this->tableName['scolarites']
        ], 'aff.millesime=sco.millesime AND aff.eleveId=sco.eleveId', [],
            Select::JOIN_LEFT)
            ->quantifier(Select::QUANTIFIER_DISTINCT);
        // inscrits
        $whereInscrits = new Where();
        $whereInscrits->equalTo('sco.millesime', $millesime)->literal('sco.inscrit = 1');
        $subSelectInscrits = clone $subSelectBase;
        $subSelectInscrits->where($whereInscrits);

        // internet
        $whereInternet = clone $whereInscrits;
        $whereInternet->literal('sco.internet = 1');
        $subSelectInternet = clone $subSelectBase;
        $subSelectInternet->where($whereInternet);

        // papier
        $wherePapier = clone $whereInscrits;
        $wherePapier->literal('sco.internet = 0');
        $subSelectPapier = clone $subSelectBase;
        $subSelectPapier->where($wherePapier);

        // requête
        $select = $this->sql->select([
            'ser' => $this->tableName['services']
        ])
            ->join(
            [
                'tmp1' => $this->subSelectCircuitGroup('sub1', 'inscrits',
                    $subSelectInscrits)
            ],
            implode(' AND ',
                [
                    'tmp1.ligneId = ser.ligneId',
                    'tmp1.sens = ser.sens',
                    'tmp1.moment = ser.moment',
                    'tmp1.ordre = ser.ordre'
                ]), [], Select::JOIN_LEFT)
            ->join(
            [
                'tmp2' => $this->subSelectCircuitGroup('sub2', 'internet',
                    $subSelectInternet)
            ],
            implode(' AND ',
                [
                    'tmp2.ligneId = ser.ligneId',
                    'tmp2.sens = ser.sens',
                    'tmp2.moment = ser.moment',
                    'tmp2.ordre = ser.ordre'
                ]), [], Select::JOIN_LEFT)
            ->join(
            [
                'tmp3' => $this->subSelectCircuitGroup('sub3', 'papier', $subSelectPapier)
            ],
            implode(' AND ',
                [
                    'tmp3.ligneId = ser.ligneId',
                    'tmp3.sens = ser.sens',
                    'tmp3.moment = ser.moment',
                    'tmp3.ordre = ser.ordre'
                ]), [], Select::JOIN_LEFT)
            ->columns(
            [
                'nom' => new Expression(
                    $this->getSqlDesignationService('ser.ligneId', 'ser.sens',
                        'ser.moment', 'ser.ordre')),
                'inscrits' => new Expression('COALESCE(inscrits, 0)'),
                'internet' => new Expression('COALESCE(internet, 0)'),
                'papier' => new Expression('COALESCE(papier, 0)')
            ])
            ->order([
            'ser.ligneId',
            'ser.sens',
            'ser.moment',
            'ser.ordre'
        ]);
        // die($this->getSqlString($select));
        return $select;
    }

    /**
     * Renvoie une sous requête de la forme SELECT ligneId, sens, moment, ordre,
     * count($alias.eleveId) AS inscrits FROM $sub AS $alias GROUP BY ligneId, sens,
     * moment, ordre
     *
     * @param string $alias
     *            nom de l'alias (les alias doivent être distincts)
     * @param Select $sub
     *            requête dérivée de $subSelectBase
     * @return \Zend\Db\Sql\Select
     */
    private function subSelectCircuitGroup($alias, $fieldNameCount, Select $sub)
    {
        return $this->sql->select([
            $alias => $sub
        ])
            ->columns(
            [
                'ligneId',
                'sens',
                'moment',
                'ordre',
                $fieldNameCount => new Expression('count(eleveId)')
            ])
            ->group([
            'ligneId',
            'sens',
            'moment',
            'ordre'
        ]);
    }

    /**
     * Renvoie un tableau statistiques pour l'année en cours et pour l'année précédente
     *
     * @return array <br><code>['annee_courante' => [], 'annee_precedente' =>
     *         []]</code><br> où chaque tableau est composé de lignes de la forme :<br>
     *         <code>['nom' => nnn, 'alias' => aaa, 'inscrits' => value, 'internet' =>
     *         value, 'papier' => value, 'transportes' => value]</code> <br>où<ul>
     *         <li><b>nom</b> est le nom de la classe</li> <li><b>alias</b> est
     *         l'aliasCG</li> <li><b>inscrits</b> est le nombre d'inscrits</li>
     *         <li><b>internet</b> est le nombre d'inscrits par internet</li>
     *         <li><b>papier</b> est le nombre d'inscrits par fiche papier</li>
     *         <li><b>transportes</b> est le nombre d'élève transportés</li></ul>
     */
    public function statistiquesParEtablissement()
    {
        $result = [
            'annee_courante' => [],
            'annee_precedente' => []
        ];
        $statement = $this->sql->prepareStatementForSqlObject(
            $this->selectStatistiquesParEtablissement($this->millesime));
        $result['annee_courante'] = iterator_to_array($statement->execute());
        $statement = $this->sql->prepareStatementForSqlObject(
            $this->selectStatistiquesParEtablissement($this->millesime - 1));
        $result['annee_precedente'] = iterator_to_array($statement->execute());
        return $result;
    }

    private function selectStatistiquesParEtablissement($millesime)
    {
        // les inscrits
        $whereInscrits = new Where();
        $whereInscrits->literal('inscrit = 1')->equalTo('millesime', $millesime);
        $selectInscrits = $this->sql->select($this->tableName['scolarites'])
            ->group([
            'etablissementId'
        ])
            ->columns(
            [
                'etablissementId',
                'inscrits' => new Expression('count(eleveId)')
            ])
            ->where($whereInscrits);

        // par internet
        $whereInternet = new Where();
        $whereInternet->literal('inscrit = 1')
            ->literal('internet = 1')
            ->equalTo('millesime', $millesime);
        $selectInternet = $this->sql->select($this->tableName['scolarites'])
            ->group([
            'etablissementId'
        ])
            ->columns(
            [
                'etablissementId',
                'internet' => new Expression('count(eleveId)')
            ])
            ->where($whereInternet);

        // par fiche papier
        $wherePapier = new Where();
        $wherePapier->literal('inscrit = 1')
            ->literal('internet = 0')
            ->equalTo('millesime', $millesime);
        $selectPapier = $this->sql->select($this->tableName['scolarites'])
            ->group([
            'etablissementId'
        ])
            ->columns([
            'etablissementId',
            'papier' => new Expression('count(eleveId)')
        ])
            ->where($wherePapier);

        // transportés
        $whereTransportes = new Where();
        $whereTransportes->equalTo('sco.millesime', $millesime)->equalTo('sco.inscrit', 1);
        $selectElevesTransportes = $this->sql->select(
            [
                'sco' => $this->tableName['scolarites']
            ])
            ->join([
            'aff' => $this->tableName['affectations']
        ], 'sco.millesime = aff.millesime AND sco.eleveId = aff.eleveId', [])
            ->columns([
            'etablissementId',
            'eleveId'
        ])
            ->quantifier(Select::QUANTIFIER_DISTINCT)
            ->where($whereTransportes);
        $selectTransportes = $this->sql->select([
            'aff' => $selectElevesTransportes
        ])
            ->group([
            'etablissementId'
        ])
            ->columns(
            [
                'etablissementId',
                'transportes' => new Expression('count(eleveId)')
            ]);

        // construction de la requête
        $select = $this->sql->select([
            'eta' => $this->tableName['etablissements']
        ])
            ->columns([
            'nom'
        ])
            ->join([
            'com' => $this->tableName['communes']
        ], 'eta.communeId = com.communeId', [
            'commune' => 'nom'
        ])
            ->join([
            'transportes' => $selectTransportes
        ], 'transportes.etablissementId = eta.etablissementId',
            [
                'transportes' => new Expression('COALESCE(transportes, 0)')
            ], Select::JOIN_LEFT)
            ->join([
            'inscrits' => $selectInscrits
        ], 'inscrits.etablissementId = eta.etablissementId',
            [
                'inscrits' => new Expression('COALESCE(inscrits, 0)')
            ], Select::JOIN_LEFT)
            ->join([
            'internet' => $selectInternet
        ], 'internet.etablissementId = eta.etablissementId',
            [
                'internet' => new Expression('COALESCE(internet, 0)')
            ], Select::JOIN_LEFT)
            ->join([
            'papier' => $selectPapier
        ], 'papier.etablissementId = eta.etablissementId',
            [
                'papier' => new Expression('COALESCE(papier, 0)')
            ], Select::JOIN_LEFT)
            ->where([
            'eta.desservie' => 1
        ])
            ->order([
            'com.nom',
            'eta.niveau',
            'eta.nom'
        ]);
        // die($this->getSqlString($select));
        return $select;
    }

    /**
     * Renvoie un tableau statistiques pour l'année en cours et pour l'année précédente
     *
     * @return array <br><code>['annee_courante' => [], 'annee_precedente' =>
     *         []]</code><br> où chaque tableau est composé de lignes de la forme :<br>
     *         <code>['commune' => nnn, 'circuit' => aaa, 'transportes' => value)</code>
     *         <br>où<ul> <li><b>commune</b> est le nom de la commune</li>
     *         <li><b>circuit</b> est le code du service</li> <li><b>transportes</b> est
     *         le nombre d'élève transportés</li></ul>
     */
    public function statistiquesParCommuneCircuit()
    {
        $result = [
            'annee_courante' => [],
            'annee_precedente' => []
        ];
        $statement = $this->sql->prepareStatementForSqlObject(
            $this->selectStatistiquesParCommuneCircuit($this->millesime));
        $result['annee_courante'] = iterator_to_array($statement->execute());
        $statement = $this->sql->prepareStatementForSqlObject(
            $this->selectStatistiquesParCommuneCircuit($this->millesime - 1));
        $result['annee_precedente'] = iterator_to_array($statement->execute());
        // die(var_dump($result));
        return $result;
    }

    /**
     * Renvoie une requête
     *
     * @param int $millesime
     *
     * @return \Zend\Db\Sql\Select
     */
    private function selectStatistiquesParCommuneCircuit($millesime)
    {
        $subSelectBase = $this->sql->select([
            's' => $this->tableName['services']
        ])
            ->columns([
            'ligneId',
            'sens',
            'moment',
            'ordre'
        ])
            ->join([
            'aff' => $this->tableName['affectations']
        ],
            sprintf('(%s) OR (%s)',
                implode(' AND ',
                    [
                        'aff.ligne1Id = s.ligneId',
                        'aff.sensligne1 = s.sens',
                        'aff.moment = s.moment',
                        'aff.ordreligne1 = s.ordre'
                    ]),
                implode(' AND ',
                    [
                        'aff.ligne2Id = s.ligneId',
                        'aff.sensligne2 = s.sens',
                        'aff.moment = s.moment',
                        'aff.ordreligne2 = s.ordre'
                    ])), [
                'eleveId'
            ], Select::JOIN_LEFT)
            ->join([
            'res' => $this->tableName['responsables']
        ], 'res.responsableId=aff.responsableId', [
            'communeId'
        ], Select::JOIN_LEFT)
            ->join([
            'sco' => $this->tableName['scolarites']
        ], 'aff.millesime=sco.millesime AND aff.eleveId=sco.eleveId', [],
            Select::JOIN_LEFT)
            ->quantifier(Select::QUANTIFIER_DISTINCT);
        // inscrits
        $whereInscrits = new Where();
        $whereInscrits->equalTo('sco.millesime', $millesime)->literal('sco.inscrit = 1');
        $subSelectInscrits = clone $subSelectBase;
        $subSelectInscrits->where($whereInscrits);

        // internet
        $whereInternet = clone $whereInscrits;
        $whereInternet->literal('sco.internet = 1');
        $subSelectInternet = clone $subSelectBase;
        $subSelectInternet->where($whereInternet);

        // papier
        $wherePapier = clone $whereInscrits;
        $wherePapier->literal('sco.internet = 0');
        $subSelectPapier = clone $subSelectBase;
        $subSelectPapier->where($wherePapier);
        ;

        // requête
        $select = $this->sql->select([
            'com' => $this->tableName['communes']
        ])
            ->join(
            [
                'tmp1' => $this->subSelectCommuneCircuitGroup('sub1', 'inscrits',
                    $subSelectInscrits)
            ], 'tmp1.communeId=com.communeId', [], Select::JOIN_LEFT)
            ->join(
            [
                'tmp2' => $this->subSelectCommuneCircuitGroup('sub2', 'internet',
                    $subSelectInternet)
            ],
            implode(' AND ',
                [
                    'tmp2.communeId = com.communeId',
                    'tmp2.ligneId = tmp1.ligneId',
                    'tmp2.sens = tmp1.sens',
                    'tmp2.moment = tmp1.moment',
                    'tmp2.ordre = tmp1.ordre'
                ]), [], Select::JOIN_LEFT)
            ->join(
            [
                'tmp3' => $this->subSelectCommuneCircuitGroup('sub3', 'papier',
                    $subSelectPapier)
            ],
            implode(' AND ',
                [
                    'tmp3.communeId = com.communeId',
                    'tmp3.ligneId = tmp1.ligneId',
                    'tmp3.sens = tmp1.sens',
                    'tmp3.moment = tmp1.moment',
                    'tmp3.ordre = tmp1.ordre'
                ]), [], Select::JOIN_LEFT)
            ->columns(
            [
                'commune' => 'nom',
                'circuit' => new Expression(
                    $this->getSqlDesignationService('COALESCE(tmp1.ligneId, "")',
                        'tmp1.sens', 'tmp1.moment', 'tmp1.ordre')),
                'inscrits' => new Expression('COALESCE(tmp1.inscrits, 0)'),
                'internet' => new Expression('COALESCE(tmp2.internet, 0)'),
                'papier' => new Expression('COALESCE(tmp3.papier, 0)')
            ])
            ->where([
            'desservie' => 1
        ])
            ->order(
            [
                'com.nom',
                'tmp1.ligneId',
                'tmp1.sens',
                'tmp1.moment',
                'tmp1.ordre'
            ]);
        // die($this->getSqlString($select));
        return $select;
    }

    private function subSelectCommuneCircuitGroup($alias, $fieldNameCount, Select $sub)
    {
        return $this->sql->select([
            $alias => $sub
        ])
            ->columns(
            [
                'communeId',
                'ligneId',
                'sens',
                'moment',
                'ordre',
                $fieldNameCount => new Expression('count(eleveId)')
            ])
            ->group([
            'communeId',
            'ligneId',
            'sens',
            'moment',
            'ordre'
        ]);
    }

    /**
     * Renvoie un tableau statistiques pour l'année en cours et pour l'année précédente
     *
     * @return array <br><code>['annee_courante' => [], 'annee_precedente' =>
     *         []]</code><br> où chaque tableau est composé de lignes de la forme :<br>
     *         <code>['commune' => nnn, 'circuit' => aaa, 'inscrits' => value, 'internet'
     *         => value, 'papier' => value, 'transportes' => value)</code> <br>où<ul>
     *         <li><b>commune</b> est le nom de la commune</li> <li><b>circuit</b> est le
     *         code du service</li> <li><b>inscrits</b> est le nombre d'inscrits</li>
     *         <li><b>internet</b> est le nombre d'inscrits par internet</li>
     *         <li><b>papier</b> est le nombre d'inscrits par fiche papier</li>
     *         <li><b>transportes</b> est le nombre d'élève transportés</li></ul>
     */
    public function statistiquesParCircuitCommune()
    {
        $result = [
            'annee_courante' => [],
            'annee_precedente' => []
        ];
        $statement = $this->sql->prepareStatementForSqlObject(
            $this->selectStatistiquesParCircuitCommune($this->millesime));
        $result['annee_courante'] = iterator_to_array($statement->execute());
        $statement = $this->sql->prepareStatementForSqlObject(
            $this->selectStatistiquesParCircuitCommune($this->millesime - 1));
        $result['annee_precedente'] = iterator_to_array($statement->execute());
        // die(var_dump($result));
        return $result;
    }

    /**
     * Renvoie une requête SQL .
     *
     * @param int $millesime
     *
     * @return \Zend\Db\Sql\Select
     */
    private function selectStatistiquesParCircuitCommune($millesime)
    {
        $subSelectBase = $this->sql->select([
            's' => $this->tableName['services']
        ])
            ->columns([
            'ligneId',
            'sens',
            'moment',
            'ordre'
        ])
            ->join([
            'aff' => $this->tableName['affectations']
        ],
            sprintf('(%s) OR (%s)',
                implode(' AND ',
                    [
                        'aff.ligne1Id = s.ligneId',
                        'aff.sensligne1 = s.sens',
                        'aff.moment = s.moment',
                        'aff.ordreligne1 = s.ordre'
                    ]),
                implode(' AND ',
                    [
                        'aff.ligne2Id = s.ligneId',
                        'aff.sensligne2 = s.sens',
                        'aff.moment = s.moment',
                        'aff.ordreligne2 = s.ordre'
                    ])), [
                'eleveId'
            ], Select::JOIN_LEFT)
            ->join([
            'res' => $this->tableName['responsables']
        ], 'res.responsableId=aff.responsableId', [], Select::JOIN_LEFT)
            ->join([
            'com' => $this->tableName['communes']
        ], 'res.communeId=com.communeId', [
            'commune' => 'nom'
        ], Select::JOIN_LEFT)
            ->join([
            'sco' => $this->tableName['scolarites']
        ], 'aff.millesime=sco.millesime AND aff.eleveId=sco.eleveId', [],
            Select::JOIN_LEFT)
            ->quantifier(Select::QUANTIFIER_DISTINCT);
        // inscrits
        $whereInscrits = new Where();
        $whereInscrits->equalTo('sco.millesime', $millesime)->literal('sco.inscrit = 1');
        $subSelectInscrits = clone $subSelectBase;
        $subSelectInscrits->where($whereInscrits);

        // internet
        $whereInternet = clone $whereInscrits;
        $whereInternet->literal('sco.internet = 1');
        $subSelectInternet = clone $subSelectBase;
        $subSelectInternet->where($whereInternet);

        // papier
        $wherePapier = clone $whereInscrits;
        $wherePapier->literal('sco.internet = 0');
        $subSelectPapier = clone $subSelectBase;
        $subSelectPapier->where($wherePapier);

        // requête
        $select = $this->sql->select([
            'ser' => $this->tableName['services']
        ])
            ->join(
            [
                'tmp1' => $this->subSelectCircuitCommuneGroup('sub1', 'inscrits',
                    $subSelectInscrits)
            ],
            implode(' AND ',
                [
                    'tmp1.ligneId = ser.ligneId',
                    'tmp1.sens = ser.sens',
                    'tmp1.moment = ser.moment',
                    'tmp1.ordre = ser.ordre'
                ]), [], Select::JOIN_LEFT)
            ->join(
            [
                'tmp2' => $this->subSelectCircuitCommuneGroup('sub2', 'internet',
                    $subSelectInternet)
            ],
            implode(' AND ',
                [
                    'tmp2.ligneId=tmp1.ligneId',
                    'tmp2.sens = ser.sens',
                    'tmp2.moment = ser.moment',
                    'tmp2.ordre = ser.ordre',
                    'tmp2.commune=tmp1.commune'
                ]), [], Select::JOIN_LEFT)
            ->join(
            [
                'tmp3' => $this->subSelectCircuitCommuneGroup('sub3', 'papier',
                    $subSelectPapier)
            ],
            implode(' AND ',
                [
                    'tmp3.ligneId=tmp1.ligneId',
                    'tmp3.sens = ser.sens',
                    'tmp3.moment = ser.moment',
                    'tmp3.ordre = ser.ordre',
                    'tmp3.commune=tmp1.commune'
                ]), [], Select::JOIN_LEFT)
            ->columns(
            [
                'circuit' => new Expression(
                    $this->getSqlDesignationService('ser.ligneId', 'ser.sens',
                        'ser.moment', 'ser.ordre')),
                'commune' => new Expression('COALESCE(tmp1.commune, "")'),
                'inscrits' => new Expression('COALESCE(inscrits, 0)'),
                'internet' => new Expression('COALESCE(internet, 0)'),
                'papier' => new Expression('COALESCE(papier, 0)')
            ])
            ->order(
            [
                'ser.ligneId',
                'ser.sens',
                'ser.moment',
                'ser.ordre',
                'tmp1.commune'
            ]);
        // die($this->getSqlString($select));
        return $select;
    }

    private function subSelectCircuitCommuneGroup($alias, $fieldNameCount, Select $sub)
    {
        return $this->sql->select([
            $alias => $sub
        ])
            ->columns(
            [
                'ligneId',
                'sens',
                'moment',
                'ordre',
                'commune',
                $fieldNameCount => new Expression('count(eleveId)')
            ])
            ->group([
            'ligneId',
            'sens',
            'moment',
            'ordre',
            'commune'
        ]);
    }

    /**
     * Renvoie un tableau statistiques pour l'année en cours et pour l'année précédente
     *
     * @return array <br><code>['annee_courante' => [], 'annee_precedente' =>
     *         []]</code><br> où chaque tableau est composé de lignes de la forme :<br>
     *         <code>['etablissementId' => id, 'etablissement' =>nnn, 'commune' => vvv,
     *         'classe' => ccc, 'inscrits' => value, 'internet' => value, 'papier' =>
     *         value, 'transportes' => value)</code> <br>où<ul> <li><b>etablissementId</b>
     *         est l'id de l'établissement</li> <li><b>etablissement</b> est le nom de
     *         l'établissement</li> <li><b>commune</b> est la commune de
     *         l'établissement</li> <li><b>classe</b> est le nom de la classe</li>
     *         <li><b>inscrits</b> est le nombre d'inscrits</li> <li><b>internet</b> est
     *         le nombre d'inscrits par internet</li> <li><b>papier</b> est le nombre
     *         d'inscrits par fiche papier</li> <li><b>transportes</b> est le nombre
     *         d'élève transportés</li></ul>
     * @return array
     */
    public function statistiquesParEtablissementClasse()
    {
        $statement = $this->sql->prepareStatementForSqlObject(
            $this->selectStatistiquesParEtablissementClasse($this->millesime));
        $annee_courante = iterator_to_array($statement->execute());
        $statement = $this->sql->prepareStatementForSqlObject(
            $this->selectStatistiquesParEtablissementClasse($this->millesime - 1));
        $annee_precedente = iterator_to_array($statement->execute());
        // résultat par alignement des tableaux
        $result = [
            'annee_courante' => [],
            'annee_precedente' => []
        ];
        for ($etablissementId = '', $ic = 0, $ip = 0; $ic < count($annee_courante) ||
            $ip < count($annee_precedente);) {
            if (empty($etablissementId)) {
                // initialisation à l'entrée d'un établissement
                if (isset($annee_courante[$ic]['etablissementId'])) {
                    $etablissementId = $annee_courante[$ic]['etablissementId'];
                } else {
                    $etablissementId = $annee_precedente[$ip]['etablissementId'];
                }
                /*
                 * $total = [ 'annee_courante' => [ 'etablissement' => '', 'commune' =>
                 * 'TOTAL', 'classe' => '', 'inscrits' => 0, 'internet' => 0, 'papier' =>
                 * 0, 'transportes' => 0 ], 'annee_precedente' => [ 'etablissement' => '',
                 * 'commune' => 'TOTAL', 'classe' => '', 'inscrits' => 0, 'internet' => 0,
                 * 'papier' => 0, 'transportes' => 0 ] ];
                 */
            }
            if (isset($annee_courante[$ic]['etablissementId']) &&
                $etablissementId == $annee_courante[$ic]['etablissementId']) {
                if (isset($annee_precedente[$ip]['etablissementId']) &&
                    $etablissementId == $annee_precedente[$ip]['etablissementId']) {
                    // même établissement pour les 2 tableaux
                    if ($annee_courante[$ic]['classe'] == $annee_precedente[$ip]['classe']) {
                        // même classe pour les 2 tableaux
                        // $total['annee_courante']['inscrits'] =
                        // $annee_courante[$ic]['inscrits'];
                        $result['annee_courante'][] = $annee_courante[$ic ++];
                        // $total['annee_precedente']['inscrits'] =
                        // $annee_precedente[$ip]['inscrits'];
                        $result['annee_precedente'][] = $annee_precedente[$ip ++];
                    } elseif ($annee_courante[$ic]['classe'] == '') {
                        // pas de classe dans l'année courante
                        $ic ++;
                        // $result['annee_precedente'][] = $annee_precedente[$ip++];
                    } elseif ($annee_precedente[$ip]['classe'] == '') {
                        // pas de classe dans l'année précédente
                        $ip ++;
                        // $result['annee_courante'][] = $annee_courante[$ic++];
                    } elseif ($annee_courante[$ic]['classe'] <
                        $annee_precedente[$ip]['classe']) {
                        $result['annee_precedente'][] = $annee_precedente[$ip ++];
                    } else {
                        $result['annee_courante'][] = $annee_courante[$ic ++];
                    }
                } else {
                    // l'établissement de l'année précédente a changé
                    // il faut finir l'établissement de l'année en cours
                    $row = $annee_courante[$ic ++];
                    $result['annee_courante'][] = $row;
                    $result['annee_precedente'][] = array_merge($row,
                        [
                            'inscrits' => 0,
                            'internet' => 0,
                            'papier' => 0,
                            'transportes' => 0
                        ]);
                    // $total['annee_courante']['inscrits'] += $row['inscrits'];
                }
            } else {
                if (isset($annee_precedente[$ip]['etablissementId']) &&
                    $etablissementId == $annee_precedente[$ip]['etablissementId']) {
                    // il faut finir l'établissement de l'année précédente
                    $row = $annee_precedente[$ip ++];
                    $result['annee_precedente'][] = $row;
                    $result['annee_courante'][] = array_merge($row,
                        [
                            'incrits' => 0,
                            'internet' => 0,
                            'papier' => 0,
                            'transportes' => 0
                        ]);
                    // $total['annee_precedente']['inscrits'] += $row['inscrits'];
                } else {
                    // $etablissementId n'est plus à jour
                    $etablissementId = '';
                    // $result['annee_precedente'][] = $total['annee_precedente'];
                    // $result['annee_courante'][] = $total['annee_courante'];
                }
            }
        }
        return $result;
    }

    /**
     * Renvoie une requête de la forme : SELECT eta.etablissementId, eta.nom AS
     * etablissement, com.nom AS commune, COALESCE(cla.nom, '') AS classe,
     * COALESCE(tmp1.inscrits, 0) AS inscrits, COALESCE(tmp2.internet, 0) AS internet,
     * COALESCE(tmp3.papier,0) AS papier, COALESCE(tmp4.transportes, 0) AS transportes
     * FROM `sbm_t_etablissements` eta JOIN `sbm_t_communes` com ON
     * eta.communeId=com.communeId LEFT JOIN ( SELECT sub1.etablissementId, sub1.classeId,
     * count(sub1.eleveId) AS inscrits FROM `sbm_t_scolarites` sub1 WHERE
     * sub1.millesime=2015 AND sub1.inscrit=1 GROUP BY etablissementId, classeId ) tmp1 ON
     * tmp1.etablissementId=eta.etablissementId LEFT JOIN ( SELECT sub2.etablissementId,
     * sub2.classeId, count(sub2.eleveId) AS internet FROM `sbm_t_scolarites` sub2 WHERE
     * sub2.millesime=2015 AND sub2.inscrit=1 AND sub2.internet=1 GROUP BY
     * etablissementId, classeId ) tmp2 ON tmp2.etablissementId=eta.etablissementId AND
     * tmp2.classeId=tmp1.classeId LEFT JOIN ( SELECT sub3.etablissementId, sub3.classeId,
     * count(sub3.eleveId) AS papier FROM `sbm_t_scolarites` sub3 WHERE
     * sub3.millesime=2015 AND sub3.inscrit=1 AND sub3.internet=0 GROUP BY
     * etablissementId, classeId ) tmp3 ON tmp3.etablissementId=eta.etablissementId AND
     * tmp3.classeId=tmp1.classeId LEFT JOIN ( SELECT sub4.etablissementId, sub4.classeId,
     * count(sub4.eleveId) AS transportes FROM ( SELECT DISTINCT sco.etablissementId,
     * sco.classeId, sco.eleveId FROM `sbm_t_scolarites` sco JOIN `sbm_t_affectations`aff
     * ON aff.millesime=sco.millesime AND aff.eleveId=sco.eleveId WHERE aff.millesime=2015
     * AND sco.inscrit=1 ) sub4 GROUP BY etablissementId, classeId ) tmp4 ON
     * tmp4.etablissementId=eta.etablissementId AND tmp4.classeId=tmp1.classeId LEFT JOIN
     * `sbm_t_classes` cla ON tmp1.classeId=cla.classeId WHERE eta.desservie = 1 ORDER BY
     * com.nom, eta.niveau, eta.nom, cla.nom DESC
     *
     * @param int $millesime
     *
     * @return \Zend\Db\Sql\Select
     */
    private function selectStatistiquesParEtablissementClasse($millesime)
    {
        // subInscrits
        $subInscrits = $this->sql->select([
            'sub1' => $this->tableName['scolarites']
        ])
            ->columns(
            [
                'etablissementId',
                'classeId',
                'inscrits' => new Expression('count(sub1.eleveId)')
            ])
            ->where([
            'sub1.millesime' => $millesime,
            'inscrit' => 1
        ])
            ->group([
            'etablissementId',
            'classeId'
        ]);

        // subInternet
        $subInternet = $this->sql->select([
            'sub2' => $this->tableName['scolarites']
        ])
            ->columns(
            [
                'etablissementId',
                'classeId',
                'internet' => new Expression('count(sub2.eleveId)')
            ])
            ->where([
            'sub2.millesime' => $millesime,
            'inscrit' => 1,
            'internet' => 1
        ])
            ->group([
            'etablissementId',
            'classeId'
        ]);

        // subPapier
        $subPapier = $this->sql->select([
            'sub3' => $this->tableName['scolarites']
        ])
            ->columns(
            [
                'etablissementId',
                'classeId',
                'papier' => new Expression('count(sub3.eleveId)')
            ])
            ->where([
            'sub3.millesime' => $millesime,
            'inscrit' => 1,
            'internet' => 0
        ])
            ->group([
            'etablissementId',
            'classeId'
        ]);

        // subTransportes
        $subTransportes1 = $this->sql->select([
            'sco' => $this->tableName['scolarites']
        ])
            ->join([
            'aff' => $this->tableName['affectations']
        ], 'aff.millesime=sco.millesime AND aff.eleveId=sco.eleveId', [])
            ->columns([
            'etablissementId',
            'classeId',
            'eleveId'
        ])
            ->quantifier(Select::QUANTIFIER_DISTINCT)
            ->where([
            'sco.millesime' => $millesime,
            'sco.inscrit' => 1
        ]);

        $subTransportes = $this->sql->select([
            'sub4' => $subTransportes1
        ])
            ->columns(
            [
                'etablissementId',
                'classeId',
                'transportes' => new Expression('count(sub4.eleveId)')
            ])
            ->group([
            'etablissementId',
            'classeId'
        ]);

        // construction de la requête
        $select = $this->sql->select([
            'eta' => $this->tableName['etablissements']
        ])
            ->join([
            'com' => $this->tableName['communes']
        ], 'eta.communeId=com.communeId', [
            'commune' => 'nom'
        ])
            ->join([
            'tmp1' => $subInscrits
        ], 'tmp1.etablissementId=eta.etablissementId', [], Select::JOIN_LEFT)
            ->join([
            'tmp2' => $subInternet
        ], 'tmp2.etablissementId=eta.etablissementId AND tmp2.classeId=tmp1.classeId', [],
            Select::JOIN_LEFT)
            ->join([
            'tmp3' => $subPapier
        ], 'tmp3.etablissementId=eta.etablissementId AND tmp3.classeId=tmp1.classeId', [],
            Select::JOIN_LEFT)
            ->join([
            'tmp4' => $subTransportes
        ], 'tmp4.etablissementId=eta.etablissementId AND tmp4.classeId=tmp1.classeId', [],
            Select::JOIN_LEFT)
            ->join([
            'cla' => $this->tableName['classes']
        ], 'tmp1.classeId=cla.classeId', [], Select::JOIN_LEFT)
            ->columns(
            [
                'etablissementId',
                'etablissement' => 'nom',
                'classe' => new Expression("COALESCE(cla.nom, '')"),
                'inscrits' => new Expression("COALESCE(tmp1.inscrits, 0)"),
                'internet' => new Expression("COALESCE(tmp2.internet, 0)"),
                'papier' => new Expression("COALESCE(tmp3.papier,0)"),
                'transportes' => new Expression("COALESCE(tmp4.transportes, 0)")
            ])
            ->where([
            'eta.desservie' => 1
        ])
            ->order([
            'com.nom',
            'eta.niveau',
            'eta.nom',
            'cla.nom DESC'
        ]);
        return $select;
    }

    /**
     * Renvoie un tableau statistiques pour l'année en cours et pour l'année précédente
     *
     * @return array <br><code>['annee_courante' => [], 'annee_precedente' =>
     *         []]</code><br> où chaque tableau est composé de lignes de la forme :<br>
     *         <code>['etablissementId' => id, 'etablissement' =>nnn, 'commune' => vvv,
     *         'classe' => ccc, 'inscrits' => value, 'internet' => value, 'papier' =>
     *         value, 'transportes' => value)</code> <br>où<ul> <li><b>classe</b> est le
     *         nom de la classe</li> <li><b>etablissementId</b> est l'id de
     *         l'établissement</li> <li><b>etablissement</b> est le nom de
     *         l'établissement</li> <li><b>commune</b> est la commune de
     *         l'établissement</li> <li><b>inscrits</b> est le nombre d'inscrits</li>
     *         <li><b>internet</b> est le nombre d'inscrits par internet</li>
     *         <li><b>papier</b> est le nombre d'inscrits par fiche papier</li>
     *         <li><b>transportes</b> est le nombre d'élève transportés</li></ul>
     * @return array
     */
    public function statistiquesParClasseEtablissement()
    {
        $statement = $this->sql->prepareStatementForSqlObject(
            $this->selectStatistiquesParClasseEtablissement($this->millesime));
        $annee_courante = iterator_to_array($statement->execute());
        $statement = $this->sql->prepareStatementForSqlObject(
            $this->selectStatistiquesParClasseEtablissement($this->millesime - 1));
        $annee_precedente = iterator_to_array($statement->execute());
        // résultat par alignement des tableaux
        $result = [
            'annee_courante' => [],
            'annee_precedente' => []
        ];
        for ($classe = '', $ic = 0, $ip = 0; $ic < count($annee_courante) ||
            $ip < count($annee_precedente);) {
            if (empty($classe)) {
                // initialisation à l'entrée d'une classe
                if (isset($annee_courante[$ic]['classe'])) {
                    $classe = $annee_courante[$ic]['classe'];
                } else {
                    $classe = $annee_precedente[$ip]['classe'];
                }
                /*
                 * $total = [ 'annee_courante' => [ 'classe' => '', 'etablissement' => '',
                 * 'commune' => 'TOTAL', 'inscrits' => 0, 'internet' => 0, 'papier' => 0,
                 * 'transportes' => 0 ], 'annee_precedente' => [ 'classe' => '',
                 * 'etablissement' => '', 'commune' => 'TOTAL', 'inscrits' => 0,
                 * 'internet' => 0, 'papier' => 0, 'transportes' => 0 ] ];
                 */
            }
            if (isset($annee_courante[$ic]['classe']) &&
                $classe == $annee_courante[$ic]['classe']) {
                if (isset($annee_precedente[$ip]['classe']) &&
                    $classe == $annee_precedente[$ip]['classe']) {
                    // même classe pour les 2 tableaux
                    if ($annee_courante[$ic]['etablissementId'] ==
                        $annee_precedente[$ip]['etablissementId']) {
                        // même établissement pour les 2 tableaux
                        // $total['annee_courante']['inscrits'] +=
                        // $annee_courante[$ic]['inscrits'];
                        $result['annee_courante'][] = $annee_courante[$ic ++];
                        // $total['annee_precedente']['inscrits'] +=
                        // $annee_precedente[$ip]['inscrits'];
                        $result['annee_precedente'][] = $annee_precedente[$ip ++];
                    } elseif ($annee_courante[$ic]['etablissementId'] == '') {
                        // pas d'établissement dans l'année courante
                        $ic ++;
                        // $result['annee_precedente'][] = $annee_precedente[$ip++];
                    } elseif ($annee_precedente[$ip]['etablissementId'] == '') {
                        // pas d'établissement dans l'année précédente
                        $ip ++;
                        // $result['annee_courante'][] = $annee_courante[$ic++];
                    } elseif ($annee_courante[$ic]['etablissementId'] <
                        $annee_precedente[$ip]['etablissementId']) {
                        $result['annee_precedente'][] = $annee_precedente[$ip ++];
                    } else {
                        $result['annee_courante'][] = $annee_courante[$ic ++];
                    }
                } else {
                    // la classe de l'année précédente a changé
                    // il faut finir la classe de l'année en cours
                    $row = $annee_courante[$ic ++];
                    $result['annee_courante'][] = $row;
                    $result['annee_precedente'][] = array_merge($row,
                        [
                            'inscrits' => 0,
                            'internet' => 0,
                            'papier' => 0,
                            'transportes' => 0
                        ]);
                    // $total['annee_courante']['inscrits'] += $row['inscrits'];
                }
            } else {
                if (isset($annee_precedente[$ip]['classe']) &&
                    $classe == $annee_precedente[$ip]['classe']) {
                    // il faut finir la classe de l'année précédente
                    $row = $annee_precedente[$ip ++];
                    $result['annee_precedente'][] = $row;
                    $result['annee_courante'][] = array_merge($row,
                        [
                            'incrits' => 0,
                            'internet' => 0,
                            'papier' => 0,
                            'transportes' => 0
                        ]);
                    // $total['annee_precedente']['inscrits'] += $row['inscrits'];
                } else {
                    // $classe n'est plus à jour
                    $classe = '';
                    // $result['annee_precedente'][] = $total['annee_precedente'];
                    // $result['annee_courante'][] = $total['annee_courante'];
                }
            }
        }
        return $result;
    }

    /**
     * Renvoie une requête
     *
     * @param int $millesime
     *
     * @return \Zend\Db\Sql\Select
     */
    private function selectStatistiquesParClasseEtablissement($millesime)
    {
        // subInscrits
        $subInscrits = $this->sql->select([
            'sub1' => $this->tableName['scolarites']
        ])
            ->columns(
            [
                'classeId',
                'etablissementId',
                'inscrits' => new Expression('count(sub1.eleveId)')
            ])
            ->where([
            'sub1.millesime' => $millesime,
            'inscrit' => 1
        ])
            ->group([
            'classeId',
            'etablissementId'
        ]);

        // subInternet
        $subInternet = $this->sql->select([
            'sub2' => $this->tableName['scolarites']
        ])
            ->columns(
            [
                'classeId',
                'etablissementId',
                'internet' => new Expression('count(sub2.eleveId)')
            ])
            ->where([
            'sub2.millesime' => $millesime,
            'inscrit' => 1,
            'internet' => 1
        ])
            ->group([
            'classeId',
            'etablissementId'
        ]);

        // subPapier
        $subPapier = $this->sql->select([
            'sub3' => $this->tableName['scolarites']
        ])
            ->columns(
            [
                'classeId',
                'etablissementId',
                'papier' => new Expression('count(sub3.eleveId)')
            ])
            ->where([
            'sub3.millesime' => $millesime,
            'inscrit' => 1,
            'internet' => 0
        ])
            ->group([
            'classeId',
            'etablissementId'
        ]);

        // subTransportes
        $subTransportes1 = $this->sql->select([
            'sco' => $this->tableName['scolarites']
        ])
            ->join([
            'aff' => $this->tableName['affectations']
        ], 'aff.millesime=sco.millesime AND aff.eleveId=sco.eleveId', [])
            ->columns([
            'etablissementId',
            'classeId',
            'eleveId'
        ])
            ->quantifier(Select::QUANTIFIER_DISTINCT)
            ->where([
            'sco.millesime' => $millesime,
            'sco.inscrit' => 1
        ]);

        $subTransportes = $this->sql->select([
            'sub4' => $subTransportes1
        ])
            ->columns(
            [
                'classeId',
                'etablissementId',
                'transportes' => new Expression('count(sub4.eleveId)')
            ])
            ->group([
            'classeId',
            'etablissementId'
        ]);

        // construction de la requête
        $select = $this->sql->select([
            'cla' => $this->tableName['classes']
        ])
            ->join([
            'tmp1' => $subInscrits
        ], 'tmp1.classeId=cla.classeId', [], Select::JOIN_LEFT)
            ->join([
            'tmp2' => $subInternet
        ], 'tmp2.classeId=cla.classeId AND tmp2.etablissementId=tmp1.etablissementId', [],
            Select::JOIN_LEFT)
            ->join([
            'tmp3' => $subPapier
        ], 'tmp3.classeId=cla.classeId AND tmp3.etablissementId=tmp1.etablissementId', [],
            Select::JOIN_LEFT)
            ->join([
            'tmp4' => $subTransportes
        ], 'tmp4.classeId=cla.classeId AND tmp4.etablissementId=tmp1.etablissementId', [],
            Select::JOIN_LEFT)
            ->join([
            'eta' => $this->tableName['etablissements']
        ], 'tmp1.etablissementId=eta.etablissementId', [], Select::JOIN_LEFT)
            ->join([
            'com' => $this->tableName['communes']
        ], 'eta.communeId=com.communeId', [
            'commune' => 'nom'
        ], Select::JOIN_LEFT)
            ->columns(
            [
                'classe' => 'nom',
                'etablissementId' => new Expression("COALESCE(eta.etablissementId, '')"),
                'etablissement' => new Expression("COALESCE(eta.nom, '')"),
                'commune' => new Expression("COALESCE(com.nom, '')"),
                'inscrits' => new Expression("COALESCE(tmp1.inscrits, 0)"),
                'internet' => new Expression("COALESCE(tmp2.internet, 0)"),
                'papier' => new Expression("COALESCE(tmp3.papier,0)"),
                'transportes' => new Expression("COALESCE(tmp4.transportes, 0)")
            ])
            ->where([
            'eta.desservie' => 1
        ])
            ->order([
            'cla.niveau',
            'cla.nom DESC',
            'com.nom',
            'eta.nom'
        ]);
        return $select;
    }
}