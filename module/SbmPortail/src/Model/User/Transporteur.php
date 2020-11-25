<?php
/**
 * Analyse les droits d'un utilisateur (commune ou gr_commune)
 *
 * @project sbm
 * @package SbmPortail/src/Model/User
 * @filesource Commune.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 2 oct. 2020
 * @version 2020-2.6.1
 */
namespace SbmPortail\Model\User;

use SbmAuthentification\Model\CategoriesInterface;
use SbmBase\Model\Session;
use SbmBase\Model\StdLib;
use SbmCommun\Model\Db\Service\DbManager;

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
     * @var DbManager
     */
    private $db_manager;

    public function __construct(int $categorieId, int $userId, DbManager $db_manager)
    {
        $this->categorieId = $categorieId;
        $this->userId = $userId;
        $this->db_manager = $db_manager;
        $this->millesime = Session::get('millesime');
        $this->arrayCommunes = [];
        try {
            $userCommuneId = $this->db_manager->get('Sbm\Db\Table\UsersCommunes')->getCommuneId(
                $userId);
            switch ($categorieId) {
                case CategoriesInterface::COMMUNE_ID:
                    $this->arrayCommunes[$userCommuneId] = $this->db_manager->get(
                        'Sbm\Db\Table\Communes')->getRecord($userCommuneId)->alias;
                    break;
                case CategoriesInterface::GR_COMMUNES_ID:
                    $arrayCommuneId = $this->db_manager->get('Sbm\Db\Table\RpiCommunes')->getCommuneIds(
                        $userCommuneId);
                    foreach ($arrayCommuneId as $communeId) {
                        $this->arrayCommunes[$communeId] = $this->db_manager->get(
                            'Sbm\Db\Table\Communes')->getRecord($communeId)->alias;
                    }
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
        $statEleve = $this->db_manager->get('Sbm\Statistiques\Eleve');
        $data = [];
        foreach ($this->arrayCommunes as $communeId => $lacommune) {
            $resultNbEnregistres = $statEleve->getNbEnregistresByMillesime(
                $this->millesime, $communeId);
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
                    $statEleve->getNbInscritsByMillesime($this->millesime, $communeId))['effectif'],
                'elevesInscritsRayes' => current(
                    $statEleve->getNbRayesByMillesime($this->millesime, true, $communeId))['effectif'],
                'elevesPreinscrits' => current(
                    $statEleve->getNbPreinscritsByMillesime($this->millesime, $communeId))['effectif'],
                'elevesPreinscritsRayes' => current(
                    $statEleve->getNbRayesByMillesime($this->millesime, false, $communeId))['effectif'],
                'elevesFamilleAcceuil' => current(
                    $statEleve->getNbFamilleAccueilByMillesime($this->millesime,
                        $communeId))['effectif'],
                'elevesGardeAlternee' => current(
                    $statEleve->getNbGardeAlterneeByMillesime($this->millesime, $communeId))['effectif'],
                'elevesMoins1km' => current(
                    $statEleve->getNbMoins1KmByMillesime($this->millesime, $communeId))['effectif'],
                'elevesDe1A3km' => current(
                    $statEleve->getNbDe1A3KmByMillesime($this->millesime, $communeId))['effectif'],
                'eleves3kmEtPlus' => current(
                    $statEleve->getNb3kmEtPlusByMillesime($this->millesime, $communeId))['effectif']
            ];
        }
        return $data;
    }

    public function listeDesNoms(): string
    {
        return implode(' ou ', array_values($this->arrayCommunes));
    }

    public function getCommuneIds()
    {
        return array_keys($this->arrayCommunes);
    }

    public function getArrayCommunes()
    {
        return $this->arrayCommunes;
    }
}