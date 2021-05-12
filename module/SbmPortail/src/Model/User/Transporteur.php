<?php
/**
 * Analyse les droits d'un utilisateur (transporteur ou gr_transporteur)
 *
 * @project sbm
 * @package SbmPortail/src/Model/User
 * @filesource Transporteur.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 12 mars 2021
 * @version 2021-2.6.1
 */
namespace SbmPortail\Model\User;

use SbmAuthentification\Model\CategoriesInterface;
use SbmBase\Model\Session;
use SbmBase\Model\StdLib;
use SbmCommun\Model\Db\Service\DbManager;
use SbmPortail\Model\Db\Service\Query\Transporteur as QueryObject;
use ArrayObject;

class Transporteur
{
    use \SbmCommun\Model\Traits\DebugTrait;

    /**
     * Tableau de la forme [transporteurId => nom] listant les transporteurs auxquels
     * l'utilisateur à droit
     *
     * @var array
     */
    private $arrayTransporteurs;

    /**
     *
     * @var int
     */
    private $categorieId;

    /**
     *
     * @var int
     */
    private $userId;

    /**
     *
     * @var int
     */
    private $millesime;

    /**
     *
     * @var bool
     */
    private $sansimpayes;

    /**
     *
     * @var DbManager
     */
    private $db_manager;

    /**
     *
     * @var QueryObject
     */
    private $query;

    /**
     * Données à renvoyer.
     * La clé du tableau est un transporteurId.
     * Chaque donnée est un tableau dont les clés sont 'statistiques', 'voyages' et
     * 'services'
     * La donnée associée à 'statistiques' est le tableau des statistiques (voir la
     * méthode tableauStatistique)
     * La donnée associée à 'voyages' est un entier (voir la méthode voyages)
     * La donnée associée à 'services' est un tableau (voir la méthode services)
     *
     * @var array
     */
    private $data;

    public function __construct(int $categorieId, int $userId, DbManager $db_manager,
        bool $sansimpayes)
    {
        $this->categorieId = $categorieId;
        $this->userId = $userId;
        $this->db_manager = $db_manager;
        $this->sansimpayes = $sansimpayes;
        $this->query = $db_manager->get('Sbm\Portail\Transporteur\Query')->setSansImpayes(
            $sansimpayes);
        $this->millesime = Session::get('millesime');
        $this->arrayTransporteurs = [];
        $this->data = [];
        try {
            $userTransporteurId = $this->db_manager->get(
                'Sbm\Db\Table\UsersTransporteurs')->getTransporteurId($userId);
            switch ($categorieId) {
                case CategoriesInterface::TRANSPORTEUR_ID:
                    $this->arrayTransporteurs[$userTransporteurId] = $this->db_manager->get(
                        'Sbm\Db\Table\Transporteurs')->getRecord($userTransporteurId)->nom;
                    break;
                case CategoriesInterface::GR_TRANSPORTEURS_ID:
                    $arrayTransporteurId = $this->db_manager->get('Sbm\Db\Table\Lots')->getTransporteurIds(
                        $userTransporteurId);
                    foreach ($arrayTransporteurId as $transporteurId) {
                        $this->arrayTransporteurs[$transporteurId] = $this->db_manager->get(
                            'Sbm\Db\Table\Transporteurs')->getRecord($transporteurId)->nom;
                    }
                    break;
            }
            $this->query->setTransporteurId($this->getTransporteurIds());
            $this->voyages();
            $this->services();
            $this->tableauStatistique();
        } catch (\SbmCommun\Model\Db\Service\Table\Exception\ExceptionInterface $e) {
            $this->debugInitLog(StdLib::findParentPath(__DIR__, 'data/logs'),
                'sbm_error.log');
            $this->debugLog($e->getMessage());
            $this->debugLog($e->getTrace());
            throw $e;
        }
    }

    public function getData()
    {
        return $this->data;
    }

    /**
     * Prépare le tableau des services par transporteur
     */
    private function services()
    {
        if (! $this->db_manager->has('Sbm\Db\Eleve\EffectifTransporteursServices')) {
            foreach ($this->getTransporteurIds() as $transporteurId) {
                $this->data[$transporteurId]['services'] = null;
            }
            return;
        }
        $oEffectif = $this->db_manager->get('Sbm\Db\Eleve\EffectifTransporteursServices');
        foreach ($this->getTransporteurIds() as $transporteurId) {
            $resultset = $this->db_manager->get('Sbm\Db\Table\Services')->fetchAll(
                [
                    'millesime' => session::get('millesime'),
                    'transporteurId' => $transporteurId
                ]);
            $oEffectif->setCaractereConditionnel($transporteurId)->init(
                $this->sansimpayes);
            foreach ($resultset as $service) {
                $this->data[$transporteurId]['services'][] = new ArrayObject(
                    [
                        'ligneId' => $service->ligneId,
                        'sens' => $service->sens,
                        'moment' => $service->moment,
                        'ordre' => $service->ordre,
                        'designation' => $service->designation(),
                        'serviceId' => $service->getEncodeServiceId(),
                        'effectif' => $oEffectif->transportes($service->ligneId,
                            $service->sens, $service->moment, $service->ordre)
                    ], \ArrayObject::ARRAY_AS_PROPS);
            }
        }
    }

    /**
     * Calcule le tableau des voyages par transporteur
     */
    private function voyages()
    {
        if (! $this->db_manager->has('Sbm\Db\Eleve\EffectifTransporteurs')) {
            foreach ($this->getTransporteurIds() as $transporteurId) {
                $this->data[$transporteurId]['voyages'] = '';
            }
            return;
        }
        $oEffectif = $this->db_manager->get('Sbm\Db\Eleve\EffectifTransporteurs');
        $oEffectif->init($this->sansimpayes);
        foreach ($this->getTransporteurIds() as $transporteurId) {
            $this->data[$transporteurId]['voyages'] = $oEffectif->transportes(
                $transporteurId);
        }
    }

    /**
     * Calcule le tableau des statistiques par transporteur
     */
    private function tableauStatistique()
    {
        $statEleve = $this->db_manager->get('Sbm\Statistiques\Eleve')->setSansImpayes(
            $this->sansimpayes);
        foreach ($this->arrayTransporteurs as $transporteurId => $nom) {
            $resultNbEnregistres = $statEleve->getNbEnregistresByMillesime(
                $this->millesime, 'transporteur', $transporteurId);
            $nbDpEnregistres = $nbInternesEnregistres = 0;
            foreach ($resultNbEnregistres as $result) {
                if ($result['regimeId']) {
                    $nbInternesEnregistres = $result['effectif'];
                } else {
                    $nbDpEnregistres = $result['effectif'];
                }
            }
            $this->data[$transporteurId]['statistiques'] = [
                'transporteur' => $nom,
                'elevesDpEnregistres' => $nbDpEnregistres,
                'elevesIntEnregistres' => $nbInternesEnregistres,
                'elevesEnregistres' => $nbDpEnregistres + $nbInternesEnregistres,
                /*'elevesInscrits' => current(
                    $statEleve->getNbInscritsByMillesime($this->millesime, 'transporteur',
                        $transporteurId))['effectif'],*/
                'elevesInscritsRayes' => current(
                    $statEleve->getNbRayesByMillesime($this->millesime, 'transporteur',
                        $transporteurId, true))['effectif'],
                'elevesPreinscrits' => current(
                    $statEleve->getNbPreinscritsByMillesime($this->millesime,
                        'transporteur', $transporteurId))['effectif'],
                /*'elevesPreinscritsRayes' => current(
                    $statEleve->getNbRayesByMillesime($this->millesime, 'transporteur',
                        $transporteurId, false))['effectif'],
                'elevesFamilleAcceuil' => current(
                    $statEleve->getNbFamilleAccueilByMillesime($this->millesime,
                        'transporteur', $transporteurId))['effectif'],*/
                'elevesGardeAlternee' => current(
                    $statEleve->getNbGardeAlterneeByMillesime($this->millesime,
                        'transporteur', $transporteurId))['effectif']
                /*
             * 'elevesMoins1km' => current(
             * $statEleve->getNbMoins1KmByMillesime($this->millesime, 'transporteur',
             * $transporteurId))['effectif'],
             * 'elevesDe1A3km' => current(
             * $statEleve->getNbDe1A3KmByMillesime($this->millesime, 'transporteur',
             * $transporteurId))['effectif'],
             * 'eleves3kmEtPlus' => current(
             * $statEleve->getNb3kmEtPlusByMillesime($this->millesime, 'transporteur',
             * $transporteurId))['effectif']
             */
            ];
        }
    }

    /**
     * Noms des transporteurs pour ce user
     *
     * @return string
     */
    public function listeDesNoms(): string
    {
        return implode(' ou ', array_values($this->arrayTransporteurs));
    }

    /**
     * Nombre de transporteurs pour ce user
     *
     * @return int
     */
    public function getNbTransporteurs(): int
    {
        return count($this->arrayTransporteurs);
    }

    /**
     * Tableau indexé des transporteurId pour ce user
     *
     * @return array
     */
    public function getTransporteurIds()
    {
        return array_keys($this->arrayTransporteurs);
    }

    /**
     * Tableau associatif transporteurId => nom pour ce user
     *
     * @return array
     */
    public function getArrayTransporteurs()
    {
        return $this->arrayTransporteurs;
    }

    /**
     * Objet query donnant accès aux données pour ce user
     *
     * @return \SbmPortail\Model\Db\Service\Query\Transporteur
     */
    public function getQuery(): QueryObject
    {
        return $this->query;
    }
}