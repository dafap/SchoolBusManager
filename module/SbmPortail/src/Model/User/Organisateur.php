<?php
/**
 * Description courte du fichier
 *
 * Description longue du fichier s'il y en a une
 *
 * @project sbm
 * @package SbmPortail
 * @filesource Organisateur.php
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

class Organisateur
{
    use \SbmCommun\Model\Traits\DebugTrait;

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
    }

    /**
     * Renvoie le tableau des statistiques offertes en page d'accueil
     *
     * @return array
     */
    public function tableauStatistique(): array
    {
        $statEleve = $this->db_manager->get('Sbm\Statistiques\Eleve');
        $resultNbEnregistres = $statEleve->getNbEnregistresByMillesime($this->millesime);
        $nbDpEnregistres = $nbInternesEnregistres = 0;
        foreach ($resultNbEnregistres as $result) {
            if ($result['regimeId']) {
                $nbInternesEnregistres = $result['effectif'];
            } else {
                $nbDpEnregistres = $result['effectif'];
            }
        }
        $data = [];
        $data['eleves'] = [
            'elevesDpEnregistres' => $nbDpEnregistres,
            'elevesIntEnregistres' => $nbInternesEnregistres,
            'elevesEnregistres' => $nbDpEnregistres + $nbInternesEnregistres,
            'elevesInscrits' => current(
                $statEleve->getNbInscritsByMillesime($this->millesime))['effectif'],
            'elevesInscritsRayes' => current(
                $statEleve->getNbRayesByMillesime($this->millesime, true))['effectif'],
            'elevesPreinscrits' => current(
                $statEleve->getNbPreinscritsByMillesime($this->millesime))['effectif'],
            'elevesPreinscritsRayes' => current(
                $statEleve->getNbRayesByMillesime($this->millesime, false))['effectif'],
            'elevesFamilleAcceuil' => current(
                $statEleve->getNbFamilleAccueilByMillesime($this->millesime,
                    $communeId))['effectif'],
            'elevesGardeAlternee' => current(
                $statEleve->getNbGardeAlterneeByMillesime($this->millesime))['effectif'],
            'elevesMoins1km' => current(
                $statEleve->getNbMoins1KmByMillesime($this->millesime))['effectif'],
            'elevesDe1A3km' => current(
                $statEleve->getNbDe1A3KmByMillesime($this->millesime))['effectif'],
            'eleves3kmEtPlus' => current(
                $statEleve->getNb3kmEtPlusByMillesime($this->millesime))['effectif']
        ];
        $statResponsable = $this->db_manager->get('Sbm\Statistiques\Responsable');
        $data['responsables'] = [
            'responsablesEnregistres' => current($statResponsable->getNbEnregistres())['effectif'],
            'responsablesAvecEnfant' => current($statResponsable->getNbAvecEnfant())['effectif'],
            'responsablesSansEnfant' => current($statResponsable->getNbSansEnfant())['effectif'],
            'responsablesHorsZone' => current(
                $statResponsable->getNbCommuneNonMembre())['effectif'],
            'responsablesDemenagement' => current(
                $statResponsable->getNbDemenagement())['effectif']
        ];
        return $data;
    }

    /**
     * Renvoie les statistiques par commune
     *
     * @return array
     */
    public function tableauStatistiqueParCommunes(): array
    {
        $statEleve = $this->db_manager->get('Sbm\Statistiques\Eleve');
        $data = [];
        foreach ($arrayCommunes as $communeId => $lacommune) {
            $data[$communeId] = [
                'lacommune' => $lacommune,
                'elevesEnregistres' => current(
                    $statEleve->getNbEnregistresByMillesime($this->millesime, $communeId))['effectif'],
                'elevesInscrits' => current(
                    $statEleve->getNbInscritsByMillesime($this->millesime, $communeId))['effectif'],
                'elevesPreinscrits' => current(
                    $statEleve->getNbPreinscritsByMillesime($this->millesime, $communeId))['effectif'],
                'elevesRayes' => current(
                    $statEleve->getNbRayesByMillesime($this->millesime, true, $communeId))['effectif'],
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
}