<?php
/**
 * Renvoie l'objet requête (Query) et le résultat des statistiques pour un organisateur
 *
 * @project sbm
 * @package SbmPortail/src/Model/User
 * @filesource Organisateur.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 10 mai 2021
 * @version 2021-2.6.1
 */
namespace SbmPortail\Model\User;

use SbmBase\Model\Session;
use SbmCommun\Model\Db\Service\DbManager;
use SbmPortail\Model\Db\Service\Query\Organisateur as QueryObject;

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
     */
    public function __construct(int $categorieId, int $userId, DbManager $db_manager,
        bool $sansimpayes)
    {
        $this->categorieId = $categorieId;
        $this->userId = $userId;
        $this->db_manager = $db_manager;
        $this->sansimpayes = $sansimpayes;
        $this->query = $db_manager->get('Sbm\Portail\Organisateur\Query')->setSansImpayes(
            $this->sansimpayes);
        $this->millesime = Session::get('millesime');
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
                $statEleve->getNbRayesByMillesime($this->millesime, '', null, true))['effectif'],
            'elevesPreinscrits' => current(
                $statEleve->getNbPreinscritsByMillesime($this->millesime))['effectif'],
            'elevesPreinscritsRayes' => current(
                $statEleve->getNbRayesByMillesime($this->millesime, '', null, false))['effectif'],
            'elevesFamilleAcceuil' => current(
                $statEleve->getNbFamilleAccueilByMillesime($this->millesime))['effectif'],
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
            'responsablesHorsZone' => current($statResponsable->getNbCommuneNonMembre())['effectif'],
            'responsablesDemenagement' => current($statResponsable->getNbDemenagement())['effectif']
        ];
        return $data;
    }

    /**
     * Objet query donnant accès aux données pour ce user
     *
     * @return \SbmPortail\Model\Db\Service\Query\Organisateur
     */
    public function getQuery(): QueryObject
    {
        return $this->query;
    }
}