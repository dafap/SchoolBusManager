<?php
/**
 * Classe contenant les méthodes servant à préparer une simulation
 *
 * (doit être déclarée dans db_manager parmi les factories)
 *
 * @project sbm
 * @package SbmGestion/Model/Db/Service/Simulation
 * @filesource Prepare.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 16 juin 2021
 * @version 2021-2.5.11
 */
namespace SbmGestion\Model\Db\Service\Simulation;

use SbmCommun\Model\Db\Service\DbManager;
use SbmCommun\Model\Db\Exception as DbException;
use SbmCommun\Model\Db\Service\Table\Exception as DbTableException;
use SbmCommun\Millau\CalculDroits;
use SbmCommun\Model\Strategy\Niveau;
use Zend\Db\Sql\Where;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class Prepare implements FactoryInterface
{

    /**
     * Service manager
     *
     * @var \Zend\ServiceManager\ServiceLocatorInterface
     */
    private $db_manager;

    /**
     * Classe permettant de mettre à jour les distances
     *
     * @var \SbmCommun\Millau\CalculDroits
     */
    private $majDistances;

    /**
     * Table des changements de classe
     *
     * @var int[]
     */
    private $classeIds = [];

    /**
     * Liste des eleveId à reprendre pour les affectations. Cette liste est remplie par la
     * méthode duplicateScolarites()
     *
     * @var int[]
     */
    private $eleveIds = [];

    /**
     *
     * @var \SbmCommun\Model\Strategy\Niveau
     */
    private $strategyNiveau;

    /**
     * Niveau d'une classe suivante initialisé par la méthode niveauClasseHasChanged
     *
     * @var int
     */
    private $niveauClasse;

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        if (! ($serviceLocator instanceof DbManager)) {
            $message = 'DbManager attendu. On a reçu un %s.';
            throw new DbException\ExceptionNoDbManager(
                sprintf($message, gettype($serviceLocator)));
        }
        $this->db_manager = $serviceLocator;
        $this->strategyNiveau = new Niveau();
        return $this;
    }

    /**
     * Initialise la méthode de mise à jour des distances
     *
     * @param callable $majDistances
     *
     * @return self
     */
    public function setMajDistances($majDistances)
    {
        $this->majDistances = $majDistances;
        return $this;
    }

    /**
     * Initialise la propriété classeIds. Appelée par la méthode duplicateScolarites()
     */
    private function setClasseIds()
    {
        if (empty($this->classeIds)) {
            $where = new Where();
            $where->isNotNull('suivantId');
            $resultset = $this->db_manager->get('Sbm\Db\Table\Classes')->fetchAll($where);
            foreach ($resultset as $classe) {
                $this->classeIds[$classe->classeId] = $classe->suivantId;
            }
        }
    }

    /**
     * Cette méthode duplique les circuits du millesime source dans le millésime cible à
     * condition que le millésime cible soit vide.
     *
     *
     * @param int $source
     *            millésime source
     * @param int $cible
     *            millésime cible
     * @return \SbmGestion\Model\Db\Service\Simulation\Prepare
     */
    public function duplicateCircuits($source, $cible)
    {
        $table = $this->db_manager->get('Sbm\Db\Table\Circuits');
        if ($table->isEmptyMillesime($cible)) {
            $resultset = $table->fetchAll([
                'millesime' => $source
            ]);
            foreach ($resultset as $circuit) {
                $circuit->circuitId = null;
                $circuit->millesime = $cible;
                $table->saveRecord($circuit);
            }
        }
        return $this;
    }

    /**
     * Cette méthode est appelée par la méthode duplicateEleves(). Elle duplique la
     * scolarité des élèves à reprendre à condition que les scolarites du millésime cible
     * soit vide. Cette méthode construit le tableau enregistré dans la propriété eleveIds
     * des eleveId à prendre en compte dans la reprise des affectations (lorsqu'il n'y a
     * pas de changement d'établissement).
     *
     * @param int $source
     *            millésime source
     * @param int $cible
     *            millésime cible
     */
    private function duplicateScolarites($source, $cible)
    {
        $tscolarites = $this->db_manager->get('Sbm\Db\Table\Scolarites');
        if ($tscolarites->isEmptyMillesime($cible)) {
            $this->eleveIds = [];
            $this->setClasseIds();
            $where = new Where();
            $where->equalTo('millesime', $source)->in('classeId',
                array_keys($this->classeIds));
            $resultset = $tscolarites->fetchAll($where);
            $this->majDistances->setMillesime($cible);
            foreach ($resultset as $scolarite) {
                // anciennes données de scolarité
                $classeOrigineId = $scolarite->classeId;
                $etablissementOrigineId = $scolarite->etablissementId;
                // nouvelles données de scolarité
                $scolarite->millesime = $cible;
                $scolarite->classeId = $this->classeIds[$classeOrigineId];
                try {
                    // cas particulier : pas de changement de niveau ou établissement
                    // en RPI ou correspondance précisée dans la table
                    // simulation-etablissements
                    $scolarite->etablissementId = $this->nouvelEtablissementId(
                        $etablissementOrigineId, $classeOrigineId, $scolarite->classeId);
                } catch (DbException\ExceptionInterface $e) {
                    // cas général
                    if ($this->niveauClasse == Niveau::CODE_NIVEAU_PREMIER_CYCLE) {
                        // secteur scolaire collège (table des secteurs scolaire de clg
                        // pu)
                        $secteur = new SectorisationCollege($this->db_manager,
                            $scolarite->eleveId);
                        $scolarite->etablissementId = $secteur->getEtablissementId();
                    } elseif ($this->niveauClasse == Niveau::CODE_NIVEAU_SECOND_CYCLE) {
                        // secteur scolaire lycée (ici, un seul lycée marqué secteur
                        // scolaire)
                        $secteur = new SectorisationLycee($this->db_manager);
                        $scolarite->etablissementId = $secteur->getEtablissementId();
                    }
                }
                try {
                    $tscolarites->saveRecord($scolarite);
                } catch (\Exception $e) {
                    $msg = $e->getMessage() .
                        sprintf("\n%s - eleveId: %d\n", __METHOD__, $scolarite->eleveId);
                    throw new Exception($msg, 0, $e);
                }
                if ($etablissementOrigineId == $scolarite->etablissementId) {
                    $this->eleveIds[] = $scolarite->eleveId;
                } else {
                    // le calcul des distances doit se faire après l'enregistrement
                    $this->majDistances->majDistancesDistrict($scolarite->eleveId, false);
                }
            }
            // réinitialise le millesime en session
            $this->majDistances->setMillesime();
        }
    }

    /**
     * Renvoie le bon etablissementId en fonction de la nouvelle classe.
     * Les règles sont : - etablissement d'origine en RPI - pas de changement de niveau -
     * règle inscrite dans la table 'simulation-etablissements' Ne traite pas le cas des
     * secteurs scolaires de collège et de lycée qui dépendent de la commune de résidence.
     * Dans ce cas une DbTableException est lancée et on retrouvera le niveau de la classe
     * suivante dans la propriété niveauClasse.
     *
     * @param string $etablissementOrigineId
     * @param int $classeOrigineId
     * @param int $classeSuivanteId
     *
     * @return string : le bon etablissementId
     * @throws \SbmGestion\Model\Db\Service\Simulation\Exception
     * @throws \SbmCommun\Model\Db\Service\Table\Exception\RuntimeException (par
     *         getEtablissementSuivantByTable)
     */
    private function nouvelEtablissementId($etablissementOrigineId, $classeOrigineId,
        $classeSuivanteId)
    {
        try {
            // on traite en premier le cas des RPI
            return $this->etablissementEnRpi($etablissementOrigineId, $classeSuivanteId);
        } catch (Exception $e) {
            // c'est un établissement en RPI mais le RPI est mal configuré
            throw $e;
        } catch (DbTableException\ExceptionInterface $e) {
            // ce n'est pas un établissement en RPI
            // y a-t-il un changement de niveau
            if ($this->niveauClasseHasChanged($classeOrigineId, $classeSuivanteId)) {
                // si le niveau de l'établissement origine est supérieur ou égal au niveau
                // de la nouvelle classe il n'y a pas de raison de changer d'établissement
                // exemple : niveau établissement = 3 et niveau de la classe = 2
                $tEtablissements = $this->db_manager->get('Sbm\Db\Table\Etablissements');
                $etablissement = $tEtablissements->getRecord($etablissementOrigineId);
                if ($this->strategyNiveau->extract($etablissement->niveau) >=
                    $this->niveauClasse) {
                    return $etablissementOrigineId;
                }
                // il y a changement d'établissement. On va consulter la table
                // 'simulation-etablissements'. Si l'établissement origine n'est
                // pas dans la table une exception est lancée.
                return $this->getEtablissementSuivantByTable($etablissementOrigineId);
            } else {
                // il n'y a pas de raison de changer d'établissement
                return $etablissementOrigineId;
            }
        }
        ;
    }

    /**
     * Renvoie l'identifiant de l'établissement suivant lorsque la règle de correspondance
     * est inscrite dans la table simulation-etablissements. Sinon, lance une DbException.
     *
     * @param string $etablissementOrigineId
     *
     * @return string
     *
     * @throws \SbmCommun\Model\Db\Service\Table\Exception\RuntimeException (par
     *         getRecord())
     */
    private function getEtablissementSuivantByTable($etablissementOrigineId)
    {
        $tSimulationEtablissements = $this->db_manager->get(
            'Sbm\Db\Table\SimulationEtablissements');
        $simulationEtablissement = $tSimulationEtablissements->getRecord(
            $etablissementOrigineId);
        return $simulationEtablissement->suivantId;
    }

    /**
     * Indique si le niveau de classe a changé et affecte la propriété niveauClasse du
     * niveau de la classe suivante.
     *
     * @param int $classeOrigineId
     * @param int $classeSuivanteId
     *
     * @return boolean
     */
    private function niveauClasseHasChanged($classeOrigineId, $classeSuivanteId)
    {
        $tclasses = $this->db_manager->get('Sbm\Db\Table\Classes');
        $classeOrigine = $tclasses->getRecord($classeOrigineId);
        $classeSuivante = $tclasses->getRecord($classeSuivanteId);
        $this->niveauClasse = $this->strategyNiveau->extract($classeSuivante->niveau);
        return $this->strategyNiveau->extract($classeOrigine->niveau) !=
            $this->niveauClasse;
    }

    /**
     * Renvoie le bon etablissementId en fonction de la nouvelle classe lorsque
     * l'établissement d'origine est en RPI. Si l'établissement n'est pas en RPI, une
     * DbException est lancée.
     *
     * @param string $etablissementId
     *            ancien etablissementId
     * @param int $classeId
     *            nouvelle classeId
     * @return string : le bon etablissementId (ou l'ancien si non concerné par un RPI)
     * @throws \SbmGestion\Model\Db\Service\Simulation\Exception pour une mauvaise
     *         configuration du RPI
     * @throws \SbmCommun\Model\Db\Service\Table\Exception\RuntimeException pour un
     *         établissement qui n'est pas en RPI
     */
    private function etablissementEnRpi($etablissementId, $classeId)
    {
        $trpietablissements = $this->db_manager->get('Sbm\Db\Table\RpiEtablissements');
        $rpiId = $trpietablissements->getRpiId($etablissementId);
        $aetablissements = $trpietablissements->getEtablissements($rpiId);
        $aetablissementId = [];
        foreach ($aetablissements as $etablissement) {
            $aetablissementId[] = $etablissement['etablissementId'];
        }
        $trpiclasses = $this->db_manager->get('Sbm\Db\Table\RpiClasses');
        $where = new Where();
        $where->in('etablissementId', $aetablissementId)->equalTo('classeId', $classeId);
        $result = $trpiclasses->fetchAll($where);
        if ($result->count() == 1) {
            return $result->current()->etablissementId;
        } else {
            $tclasses = $this->db_manager->get('Sbm\Db\Table\Classes');
            $classe = $tclasses->getRecord($classeId);
            if ($this->strategyNiveau->extract($classe->niveau) >=
                Niveau::CODE_NIVEAU_PREMIER_CYCLE) {
                throw new DbException\OutOfBoundsException('Non concerné');
            }
            $trpi = $this->db_manager->get('Sbm\Db\Table\Rpi');
            $rpi = $trpi->getRecord($rpiId);
            $msg = sprintf('Mauvais paramétrage du RPI %s. ', $rpi->libelle);
            if ($result->count() > 1) {
                $msg .= sprintf('La classe %s est dans plusieurs établissements du RPI.',
                    $classe->nom);
            } else {
                $msg .= sprintf('La classe %s est dans aucun établissement du RPI.',
                    $classe->nom);
            }
            throw new Exception($msg);
        }
    }

    /**
     * Cette méthode cette méthode duplique les informations dans les tables scolarites et
     * affectations à condition que les affectations du millésime cible soit vide.
     *
     * @param int $source
     *            millésime source
     * @param int $cible
     *            millésime cible
     * @return \SbmGestion\Model\Db\Service\Simulation\Prepare
     */
    public function duplicateEleves($source, $cible)
    {
        if (! $this->majDistances instanceof CalculDroits) {
            throw new Exception(
                __METHOD__ .
                ' Avant de lancer cette méthode il faut initialiser la classe ' .
                CalculDroits::class . ' par la méthode `setMajDistances`.');
        }
        $table = $this->db_manager->get('Sbm\Db\Table\Affectations');
        if ($table->isEmptyMillesime($cible)) {
            $this->duplicateScolarites($source, $cible);
            $where = new Where();
            $where->equalTo('millesime', $source)->in('eleveId', $this->eleveIds);
            $resultset = $table->fetchAll($where);
            foreach ($resultset as $affectation) {
                $affectation->millesime = $cible;
                $table->saveRecord($affectation);
            }
        }
        return $this;
    }
}