<?php
/**
 * Classe calculant les itineraires d'un élève
 *
 * En fait, c'est un aiguillage sur une classe implémentant Itineraire\ItineraireInterface
 *
 * @project sbm
 * @package SbmCommun/src/Arlysere
 * @filesource ChercheItineraires.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 27 avr. 2021
 * @version 2021-2.6.1
 */
namespace SbmCommun\Arlysere;

use SbmCommun\Model\Db\Service\DbManager;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ChercheItineraires implements FactoryInterface
{

    /**
     *
     * @var DbManager
     */
    private $db_manager;

    /**
     *
     * @var int
     */
    private $eleveId;

    /**
     *
     * @var string
     */
    private $etablissementId;

    /**
     *
     * @var int
     */
    private $jours;

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
    private $responsableId;

    /**
     *
     * @var int
     */
    private $stationId;

    /**
     *
     * @var int
     */
    private $trajet;

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        if (! ($serviceLocator instanceof DbManager)) {
            $message = 'SbmCommun\Model\Db\Service\DbManager attendu. %s reçu.';
            throw new Exception\RuntimeException(
                sprintf($message, gettype($serviceLocator)));
        }
        $this->db_manager = $serviceLocator;
        return $this;
    }

    /**
     * Les établissements d'enseignement après-bac sont assimilés à des lycées.
     *
     * @throws \SbmCommun\Arlysere\Exception\OutOfBoundsException
     * @return array
     */
    public function run()
    {
        if ($this->regimeId == 1) {
            $oItineraire = $this->db_manager->get(Itineraire\Interne::class);
        } else {
            $niveau = $this->getNiveau();
            if (($niveau & 8) || ($niveau & 16)) {
                $oItineraire = $this->db_manager->get(Itineraire\LyceenDP::class);
            } elseif ($niveau == 4) {
                $oItineraire = $this->db_manager->get(Itineraire\CollegienDP::class);
            } elseif ($niveau > 0 && $niveau < 4) {
                $oItineraire = $this->db_manager->get(Itineraire\Ecolier::class);
            } else {
                throw new Exception\OutOfBoundsException(
                    "Le niveau de cet établissement n'est pas pris en charge par le programme de recherche d'itinéraires.");
            }
        }
        return $oItineraire->setEleveId($this->eleveId)
            ->setEtablissementId($this->etablissementId)
            ->setJours($this->jours)
            ->setResponsableId($this->responsableId)
            ->setStationId($this->stationId)
            ->setTrajet($this->trajet)
            ->run();
    }

    /**
     *
     * @param int $eleveId
     * @return \SbmCommun\Arlysere\ChercheItineraires
     */
    public function setEleveId(int $eleveId)
    {
        $this->eleveId = $eleveId;
        return $this;
    }

    /**
     *
     * @param string $etablissementId
     * @return \SbmCommun\Arlysere\ChercheItineraires
     */
    public function setEtablissementId(string $etablissementId)
    {
        $this->etablissementId = $etablissementId;
        return $this;
    }

    /**
     * Ce sont les jours de transport demandés par le responsable
     *
     * @param array|int $jours
     * @return \SbmCommun\Arlysere\ChercheItineraires
     */
    public function setJours($jours)
    {
        if (is_array($jours)) {
            $strategy = new \SbmCommun\Model\Strategy\Semaine();
            $this->jours = $strategy->extract($jours);
        } else {
            $this->jours = $jours;
        }
        return $this;
    }

    /**
     *
     * @param int $responsableId
     * @return \SbmCommun\Arlysere\ChercheItineraires
     */
    public function setResponsableId(int $responsableId)
    {
        $this->responsableId = $responsableId;
        return $this;
    }

    /**
     * Il s'agit de la station d'origine demandée par le responsable
     *
     * @param int $stationId
     * @return \SbmCommun\Arlysere\ChercheItineraires
     */
    public function setStationId(int $stationId)
    {
        $this->stationId = $stationId;
        return $this;
    }

    /**
     *
     * @param int $trajet
     *            1 pour itinéraires depuis R1 ; 2 pour itinéraires depuis R2
     * @return \SbmCommun\Arlysere\ChercheItineraires
     */
    public function setTrajet(int $trajet)
    {
        $this->trajet = $trajet;
        return $this;
    }

    /**
     *
     * @param int $regimeId
     * @return \SbmCommun\Arlysere\ChercheItineraires
     */
    public function setRegimeId(int $regimeId)
    {
        $this->regimeId = $regimeId;
        return $this;
    }

    /**
     * Niveau d'étude de l'établissement
     *
     * Maternelle = 1
     * Elementaire = 2
     * Primaire = 3 (1 + 2)
     * College = 4
     * Lycée = 8 ou 12 (8 + 4 si classe de 3e) ou 24 ( 8 + 16 si classe BTS) ou 28
     * Après-bac = 16
     *
     * @return int
     */
    private function getNiveau()
    {
        $etablissement = $this->db_manager->get('Sbm\Db\Table\Etablissements')->getRecord(
            $this->etablissementId);
        $strategy = new \SbmCommun\Model\Strategy\Niveau();
        return $strategy->extract($etablissement->niveau);
    }
}