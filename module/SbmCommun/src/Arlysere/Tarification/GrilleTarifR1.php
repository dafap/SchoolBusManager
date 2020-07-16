<?php
/**
 * Calcul de la réduction et de la grille tarifaire pour un R1
 *
 * Utilisation de la forme :
 * $this->db_manager->get('Sbm\GrilleTarifR1')->appliquerTarif($eleveId);
 *
 * Si on ne veut pas relire les fiches eleve et scolarite, on peut passer les objectData oEleve et oSolarite
 * $this->db_manager->get('Sbm\GrilleTarifR1')->setOEleve($oEleve)->setOScolarite($oScolarite)->appliquerTarif($eleveId);
 *
 * La méthode appliquerTarif() calcule et enregistre si nécessaire la grille tarifaire et la réduction
 * La méthode getObjectDataScolarite() calcule la grile tarifaire et la réduction et renvoie l'object
 * sans l'enregistrer
 * La méthode scolariteChange() renvoie un booléen qui indique si l'objectData Scolarite a changé après
 * le calcul de la grille tarifaire et de la réduction. Mais il n'y a pas d'enregistrement.
 *
 * @project sbm
 * @package SbmCommun/src/Arlysere/Tarification
 * @filesource GrilleTarifR1.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 16 juil. 2020
 * @version 2020-2.6.0
 */
namespace SbmCommun\Arlysere\Tarification;

use SbmBase\Model\Session;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Where;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class GrilleTarifR1 implements FactoryInterface, GrilleTarifInterface
{

    /**
     *
     * @var \SbmCommun\Model\Db\Service\DbManager
     */
    protected $db_manager;

    /**
     *
     * @var int
     */
    private $millesime;

    /**
     * Tableau contenant les clés 'dateDebut', 'dateFin', 'echeance', 'etat',
     * 'inscription'
     *
     * @var array
     */
    protected $etatDuSite;

    /**
     * Indicateur interne qui permet de réduire les lectures dans les tables
     *
     * @var int
     */
    private $eleveId;

    /**
     * Données temporaires pour les calculs
     *
     * @var \SbmCommun\Model\Db\ObjectData\Eleve
     */
    private $oEleve;

    /**
     * Cet objet est initialisé par le contenu de la table scolarites puis ses propriétés
     * grilleTarifRx et reductionRx sont mises à jour par les calculs. Le résultat sera
     * enregistré si on appelle la méthode appliquerTarif() ou renvoyé sans enregistrement
     * si on appelle la méthode getObjectDataScolarite()
     *
     * @var \SbmCommun\Model\Db\ObjectData\Scolarite
     */
    protected $oScolarite;

    /**
     * Indicateur qui permet de savoir si la grille tarifaire ou la réduction ont changé
     * après calcul
     *
     * @var bool
     */
    protected $scolariteChange;

    /**
     * Si l'école est dans une RPI donnant droit à la grille RPI école à école alors la
     * variable prend la valeur self::RPI sinon elle prend la valeur TARIF_ARLYSERE ou
     * HORS_ARLYSERE selon la commune de domicile du responsable
     *
     * @var int|bool
     */
    protected $grilleTarif;

    /**
     *
     * @param \SbmCommun\Model\Db\ObjectData\Eleve $oEleve
     */
    public function setOEleve($oEleve)
    {
        $this->oEleve = $oEleve;
        return $this;
    }

    /**
     *
     * @param \SbmCommun\Model\Db\ObjectData\Scolarite $oScolarite
     */
    public function setOScolarite($oScolarite)
    {
        $this->oScolarite = $oScolarite;
        return $this;
    }

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        if (! ($serviceLocator instanceof \SbmCommun\Model\Db\Service\DbManager)) {
            $message = 'SbmCommun\Model\Db\Service\DbManager attendu. %s reçu.';
            throw new \SbmCommun\Model\Db\Exception\ExceptionNoDbManager(
                sprintf($message, gettype($serviceLocator)));
        }
        $this->db_manager = $serviceLocator;
        $this->etatDuSite = $this->db_manager->get('Sbm\Db\System\Calendar')->getEtatDuSite();
        $this->millesime = Session::get('millesime');
        $this->init();
        return $this;
    }

    /**
     * Calcule la réduction et la grille tarifaire et enregistre le résultat s'il a
     * changé.
     *
     * @param int $eleveId
     * @return int
     */
    public function appliquerTarif(int $eleveId)
    {
        if ($eleveId > 0) {
            $this->calculeGrilleTarif($eleveId)->calculeReduction($eleveId);
            if ($this->scolariteChange && $this->oScolarite->eleveId == $eleveId) {
                return $this->db_manager->get('Sbm\Db\Table\Scolarites')->saveRecord(
                    $this->oScolarite);
            }
        }
    }

    /**
     * Renvoie un ObjectData Scolarite avec la bonne grille et la bonne réduction pour R1
     * sans l'enregistrer dans la table. Attention, la propriété changeScolarite indique
     * si cet enregistrement est nécessaire.
     *
     * @param int $eleveId
     * @return \SbmCommun\Model\Db\ObjectData\Scolarite
     */
    public function getObectDataScolarite(int $eleveId)
    {
        if (! $this->oScolarite || $this->oScolarite->eleveId != $eleveId) {
            $this->calculeGrilleTarif($eleveId)->calculeReduction($eleveId);
        }
        return $this->oScolarite;
    }

    /**
     * Renvoie true si la grille tarifaire ou la réduction pour R1 ont changé. Pas
     * d'enregistrement dans la table. La méthode getObjectDataScolarite() permet de
     * récupérer les données à jour.
     *
     * @param int $eleveId
     * @return bool
     */
    public function scolariteChange(int $eleveId): bool
    {
        if (! $this->oScolarite || $this->oScolarite->eleveId != $eleveId) {
            $this->calculeGrilleTarif($eleveId)->calculeReduction($eleveId);
        }
        return $this->scolariteChange;
    }

    /**
     * Adaptée pour affecter la grille calculée au responsable R1
     *
     * @param int $eleveId
     * @return \SbmCommun\Arlysere\Tarification\GrilleTarifR1
     */
    protected function calculeGrilleTarif(int $eleveId)
    {
        $this->readEleve($eleveId);
        if ($this->oScolarite) {
            $this->scolariteChange |= $this->oScolarite->grilleTarifR1 !=
                $this->grilleTarif;
            $this->oScolarite->grilleTarifR1 = $this->grilleTarif;
        }
        return $this;
    }

    /**
     * Adaptée pour affecter la réduction calculée au responsable R1
     *
     * @param int $eleveId
     * @return \SbmCommun\Arlysere\Tarification\GrilleTarifR1
     */
    protected function calculeReduction(int $eleveId)
    {
        $reduction = $this->periodeReduction($eleveId) ||
            $this->estPremiereInscription($eleveId) || $this->derogationObtenue($eleveId);
        if ($this->oScolarite) {
            $this->scolariteChange |= $this->oScolarite->reductionR1 != $reduction;
            $this->oScolarite->reductionR1 = $reduction;
        }
        return $this;
    }

    /**
     * Indique si le R1 a inscrit l'élève dans les délais
     *
     * @param int $eleveId
     * @return bool
     */
    protected function periodeReduction(int $eleveId): bool
    {
        $this->readEleve($eleveId);
        if ($this->oScolarite) {
            $dateInscription = \DateTime::createFromFormat('Y-m-d H:i:s',
                $this->oScolarite->dateInscription);
            return $dateInscription <= $this->etatDuSite['echeance'];
        } else {
            return false;
        }
    }

    /**
     *
     * @param int $eleveId
     * @return bool
     */
    protected function estPremiereInscription(int $eleveId): bool
    {
        $this->readEleve($eleveId);
        if ($this->oEleve) {
            $dateCreation = \DateTime::createFromFormat('Y-m-d H:i:s',
                $this->oEleve->dateCreation);
            return $dateCreation > $this->etatDuSite['echeance'];
        } else {
            return false;
        }
    }

    /**
     * A fourni une preuve d'inscription dans l'établissement scolaire après la date
     * limite ou de déménagement après la date limite.
     *
     * @param int $eleveId
     * @return bool
     */
    protected function derogationObtenue(int $eleveId): bool
    {
        $this->readEleve($eleveId);
        if ($this->oScolarite) {
            return $this->oScolarite->derogation;
        } else {
            return false;
        }
    }

    protected function readEleve(int $eleveId)
    {
        if ($eleveId != $this->eleveId) {
            $this->eleveId = $eleveId;
            $this->scolariteChange = false;
            try {
                $this->oEleve = $this->db_manager->get('Sbm\Db\Table\Eleves')->getRecord(
                    $eleveId);
            } catch (\Exception $e) {
                $this->init();
                return;
            }
            try {
                $this->oScolarite = $this->db_manager->get('Sbm\Db\Table\Scolarites')->getRecord(
                    [
                        'millesime' => $this->millesime,
                        'eleveid' => $eleveId
                    ]);
            } catch (\Exception $e) {
                $this->init();
                return;
            }
            $this->reglesGrille();
        }
    }

    protected function reglesGrille()
    {
        // rpi école à école
        if ($this->regardeRPI()) {
            $this->grilleTarif = self::RPI;
            return;
        }
        // adresse de la famille ou de l'eleve ou de la station dans Arlysère
        if ($this->regardeAdresseR1() || $this->regardeAdresseEleve() ||
            $this->regardeCommuneStation()) {
            $this->grilleTarif = self::TARIF_ARLYSERE;
        } else {
            $this->grilleTarif = self::HORS_ARLYSERE;
        }
    }

    /**
     * Renvoie true si l'adresse est dans Arlysère
     *
     * @return bool
     */
    private function regardeAdresseEleve(): bool
    {
        try {
            $millesime = Session::get('millesime');
            $sql = new Sql($this->db_manager->getDbAdapter());
            $select = $sql->select()
                ->quantifier(Select::QUANTIFIER_DISTINCT)
                ->columns([
                'membre'
            ])
                ->from(
                [
                    'com' => $this->db_manager->getCanonicName('communes', 'table')
                ])
                ->join(
                [
                    'sco' => $this->db_manager->getCanonicName('scolarites', 'table')
                ], 'com.communeId = sco.communeId', [])
                ->where(
                (new Where())->literal('com.membre = 1')
                    ->equalTo('sco.millesime', $millesime)
                    ->equalTo('sco.eleveId', $this->oEleve->eleveId));
            $statement = $sql->prepareStatementForSqlObject($select);
            $result = $statement->execute();
            return $result->count() > 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Renvoie true si l'adresse est dans Arlysère
     *
     * @return bool
     */
    private function regardeAdresseR1(): bool
    {
        try {
            $sql = new Sql($this->db_manager->getDbAdapter());
            $select = $sql->select()
                ->quantifier(Select::QUANTIFIER_DISTINCT)
                ->columns([
                'membre'
            ])
                ->from(
                [
                    'com' => $this->db_manager->getCanonicName('communes', 'table')
                ])
                ->join(
                [
                    'res' => $this->db_manager->getCanonicName('responsables', 'table')
                ], 'com.communeId = res.communeId', [])
                ->where(
                (new Where())->literal('com.membre = 1')
                    ->equalTo('res.responsableId', $this->oEleve->responsable1Id));
            $statement = $sql->prepareStatementForSqlObject($select);
            $result = $statement->execute();
            return $result->count() > 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Revoie true si la station est dans Arlysère
     *
     * @return bool
     */
    private function regardeCommuneStation(): bool
    {
        try {
            $millesime = Session::get('millesime');
            $sql = new Sql($this->db_manager->getDbAdapter());
            $select = $sql->select()
                ->quantifier(Select::QUANTIFIER_DISTINCT)
                ->columns([])
                ->from(
                [
                    'sco' => $this->db_manager->getCanonicName('scolarites', 'table')
                ])
                ->join(
                [
                    'sta' => $this->db_manager->getCanonicName('stations', 'table')
                ], 'sta.stationId = sco.stationIdR1', [])
                ->join(
                [
                    'com' => $this->db_manager->getCanonicName('communes', 'table')
                ], 'com.communeId = sta.communeId', [
                    'membre'
                ])
                ->where(
                (new Where())->literal('com.membre = 1')
                    ->equalTo('sco.millesime', $millesime)
                    ->equalTo('sco.eleveId', $this->oEleve->eleveId));
            $statement = $sql->prepareStatementForSqlObject($select);
            $result = $statement->execute();
            return $result->count() > 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    private function regardeRPI()
    {
        try {
            $sql = new Sql($this->db_manager->getDbAdapter());
            $select = $sql->select(
                [
                    'rpi' => $this->db_manager->getCanonicName('rpi', 'table')
                ])
                ->columns([
                'grille'
            ])
                ->join(
                [
                    'rpietab' => $this->db_manager->getCanonicName('rpi-etablissements',
                        'table')
                ], 'rpietab.rpiId = rpi.rpiId', [])
                ->where(
                (new Where())->equalTo('rpi.grille', self::RPI)
                    ->equalTo('rpietab.etablissementId',
                    $this->oScolarite->etablissementId));
            $statement = $sql->prepareStatementForSqlObject($select);
            $result = $statement->execute();
            return $result->count() > 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    private function init()
    {
        $this->scolariteChange = false;
        $this->eleveId = 0;
        $this->oEleve = null;
        $this->oScolarite = null;
        $this->grilleTarif = null;
    }
}