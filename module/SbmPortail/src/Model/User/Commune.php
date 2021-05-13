<?php
/**
 * Analyse les droits d'un utilisateur (commune ou gr_commune)
 *
 * @project sbm
 * @package SbmPortail/src/Model/User
 * @filesource Commune.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 6 mai 2021
 * @version 2021-2.6.1
 */
namespace SbmPortail\Model\User;

use SbmAuthentification\Model\CategoriesInterface;
use SbmBase\Model\Session;
use SbmBase\Model\StdLib;
use SbmCommun\Model\Db\Service\DbManager;
use SbmPortail\Model\Db\Service\Query\Commune as QueryObject;

class Commune
{
    use \SbmCommun\Model\Traits\DebugTrait;

    /**
     * Tableau de la forme [communeId => alias] listant les communes auxquelles
     * l'utilisateur à droit
     *
     * @var array
     */
    private $arrayCommunes;

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
     *
     * @param int $categorieId
     * @param int $userId
     * @param \SbmCommun\Model\Db\Service\DbManager $db_manager
     * @param bool $sansimpayes
     * @throws \SbmCommun\Model\Db\Service\Table\Exception\ExceptionInterface
     */
    public function __construct(int $categorieId, int $userId, DbManager $db_manager,
        bool $sansimpayes)
    {
        $this->categorieId = $categorieId;
        $this->userId = $userId;
        $this->db_manager = $db_manager;
        $this->sansimpayes = $sansimpayes;
        $this->query = $db_manager->get('Sbm\Portail\Commune\Query')->setSansImpayes(
            $sansimpayes);
        $this->millesime = Session::get('millesime');
        $this->arrayCommunes = [];
        try {
            $userCommuneId = $this->db_manager->get('Sbm\Db\Table\UsersCommunes')->getCommuneId(
                $userId);
            switch ($categorieId) {
                case CategoriesInterface::COMMUNE_ID:
                    $this->arrayCommunes = $this->query->setCommuneId([
                        $userCommuneId
                    ])->getArrayCommunes();
                    break;
                case CategoriesInterface::GR_COMMUNES_ID:
                    $this->arrayCommunes = $this->query->setCommuneId(
                        $this->db_manager->get('Sbm\Db\Table\RpiCommunes')
                            ->getCommuneIds($userCommuneId))
                        ->getArrayCommunes();
                    break;
            }
        } catch (\SbmCommun\Model\Db\Service\Table\Exception\ExceptionInterface $e) {
            $this->debugInitLog(StdLib::findParentPath(__DIR__, 'data/logs'),
                'sbm_error.log');
            $this->debugLog($e->getMessage());
            $this->debugLog($e->getTrace());
            throw $e;
        }
    }

    /**
     * Renvoie le tableau des statistiques offertes en page d'accueil
     *
     * @return array
     */
    public function tableauStatistique(): array
    {
        $statEleve = $this->db_manager->get('Sbm\Statistiques\Eleve')->setSansImpayes(
            $this->sansimpayes);
        $data = [];
        foreach ($this->arrayCommunes as $communeId => $lacommune) {
            $resultNbEnregistres = $statEleve->getNbEnregistresByMillesime(
                $this->millesime, 'commune', $communeId);
            $nbDpEnregistres = $nbInternesEnregistres = 0;
            foreach ($resultNbEnregistres as $result) {
                if ($result['regimeId']) {
                    $nbInternesEnregistres = $result['effectif'];
                } else {
                    $nbDpEnregistres = $result['effectif'];
                }
            }
            $data[$communeId] = [
                'lacommune' => $lacommune,
                'elevesDpEnregistres' => $nbDpEnregistres,
                'elevesIntEnregistres' => $nbInternesEnregistres,
                'elevesEnregistres' => $nbDpEnregistres + $nbInternesEnregistres,
                'elevesInscrits' => current(
                    $statEleve->getNbInscritsByMillesime($this->millesime, 'commune',
                        $communeId))['effectif'],
                'elevesInscritsRayes' => current(
                    $statEleve->getNbRayesByMillesime($this->millesime, 'commune',
                        $communeId, true))['effectif'],
                'elevesPreinscrits' => current(
                    $statEleve->getNbPreinscritsByMillesime($this->millesime, 'commune',
                        $communeId))['effectif'],
                'elevesPreinscritsRayes' => current(
                    $statEleve->getNbRayesByMillesime($this->millesime, 'commune',
                        $communeId, false))['effectif'],
                'elevesFamilleAcceuil' => current(
                    $statEleve->getNbFamilleAccueilByMillesime($this->millesime, 'commune',
                        $communeId))['effectif'],
                'elevesGardeAlternee' => current(
                    $statEleve->getNbGardeAlterneeByMillesime($this->millesime, 'commune',
                        $communeId))['effectif'],
                'elevesMoins1km' => current(
                    $statEleve->getNbMoins1KmByMillesime($this->millesime, 'commune',
                        $communeId))['effectif'],
                'elevesDe1A3km' => current(
                    $statEleve->getNbDe1A3KmByMillesime($this->millesime, 'commune',
                        $communeId))['effectif'],
                'eleves3kmEtPlus' => current(
                    $statEleve->getNb3kmEtPlusByMillesime($this->millesime, 'commune',
                        $communeId))['effectif']
            ];
        }
        return $data;
    }

    /**
     * Nombre d'établissementId pour ce user
     *
     * @return int
     */
    public function getNbCommunes(): int
    {
        return count($this->arrayCommunes);
    }

    /**
     * Alias des communes
     *
     * @return string
     */
    public function listeDesNoms(): string
    {
        return implode(' ou ', array_values($this->arrayCommunes));
    }

    /**
     * Tableau indexé des communeId pour ce user
     *
     * @return array
     */
    public function getCommuneIds(): array
    {
        return array_keys($this->arrayCommunes);
    }

    /**
     * Tableau associatif des communeId => alias pour ce user
     *
     * @return array
     */
    public function getArrayCommunes()
    {
        return $this->arrayCommunes;
    }

    /**
     * Objet query donnant accès aux données pour ce user
     *
     * @return \SbmPortail\Model\Db\Service\Query\Commune
     */
    public function getQuery(): QueryObject
    {
        return $this->query;
    }
}