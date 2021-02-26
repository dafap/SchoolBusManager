<?php
/**
 * Analyse les droits d'un utilisateur (etablissement ou gr_etablissement)
 *
 * @project sbm
 * @package SbmPortail/src/Model/User
 * @filesource Etablissement.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 12 nov. 2020
 * @version 2020-2.6.1
 */
namespace SbmPortail\Model\User;

use SbmAuthentification\Model\CategoriesInterface;
use SbmBase\Model\Session;
use SbmBase\Model\StdLib;
use SbmCommun\Model\Db\Service\DbManager;

class Etablissement
{
    use \SbmCommun\Model\Traits\DebugTrait;

    /**
     * Tableau de la forme [etablissementId => nom-localite] listant les établissements
     * auxquels l'utilisateur à droit
     *
     * @var array
     */
    private $arrayEtablissements;

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
     * @var \SbmPortail\Model\Db\Service\Query\Etablissement
     */
    private $query;

    /**
     *
     * @var DbManager
     */
    private $db_manager;

    /**
     * On note que la propriété $query est correctement initialisée par un
     * setEtablissementId() ce qui restreint aux établissements concernés lorsqu'on
     * utilise la méthode getQuery().
     *
     * @param int $categorieId
     * @param int $userId
     * @param \SbmCommun\Model\Db\Service\DbManager $db_manager
     * @throws \SbmCommun\Model\Db\Service\Table\Exception\ExceptionInterface
     */
    public function __construct(int $categorieId, int $userId, DbManager $db_manager)
    {
        $this->categorieId = $categorieId;
        $this->userId = $userId;
        $this->db_manager = $db_manager;
        $this->millesime = Session::get('millesime');
        $this->arrayEtablissements = [];
        $this->query = $this->db_manager->get('Sbm\Portail\Etablissement\Query');
        try {
            $userEtablissementId = $this->db_manager->get(
                'Sbm\Db\Table\UsersEtablissements')->getEtablissementId($userId);
            switch ($categorieId) {
                case CategoriesInterface::ETABLISSEMENT_ID:
                    $this->arrayEtablissements = $this->query->setEtablissementId(
                        [
                            $userEtablissementId
                        ])->getArrayEtablissements();
                    break;
                case CategoriesInterface::GR_ETABLISSEMENTS_ID:
                    $this->arrayEtablissements = $this->query->setEtablissementId(
                        $this->db_manager->get('Sbm\Db\Table\RpiEtablissements')
                            ->getEtablissementIds($userEtablissementId))
                        ->getArrayEtablissements();
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
        foreach ($this->arrayEtablissements as $etablissementId => $nomEtablissement) {
            $resultNbEnregistres = $statEleve->getNbEnregistresByMillesime(
                $this->millesime, 'etablissement', $etablissementId);
            $nbDpEnregistres = $nbInternesEnregistres = 0;
            foreach ($resultNbEnregistres as $result) {
                if ($result['regimeId']) {
                    $nbInternesEnregistres = $result['effectif'];
                } else {
                    $nbDpEnregistres = $result['effectif'];
                }
            }
            $data[$etablissementId] = [
                'etablissement' => $nomEtablissement,
                'elevesDpEnregistres' => $nbDpEnregistres,
                'elevesIntEnregistres' => $nbInternesEnregistres,
                'elevesEnregistres' => $nbDpEnregistres + $nbInternesEnregistres,
                'elevesInscrits' => current(
                    $statEleve->getNbInscritsByMillesime($this->millesime, 'etablissement',
                        $etablissementId))['effectif'],
                'elevesInscritsRayes' => current(
                    $statEleve->getNbRayesByMillesime($this->millesime, true,
                        'etablissement', $etablissementId))['effectif'],
                /*'elevesPreinscrits' => current(
                    $statEleve->getNbPreinscritsByMillesime($this->millesime,
                        'etablissement', $etablissementId))['effectif'],
                'elevesPreinscritsRayes' => current(
                    $statEleve->getNbRayesByMillesime($this->millesime, false,
                        'etablissement', $etablissementId))['effectif'],
                'elevesFamilleAcceuil' => current(
                    $statEleve->getNbFamilleAccueilByMillesime($this->millesime,
                        'etablissement', $etablissementId))['effectif'],*/
                'elevesGardeAlternee' => current(
                    $statEleve->getNbGardeAlterneeByMillesime($this->millesime,
                        'etablissement', $etablissementId))['effectif'],
                'elevesMoins1km' => current(
                    $statEleve->getNbMoins1KmByMillesime($this->millesime, 'etablissement',
                        $etablissementId))['effectif'],
                'elevesDe1A3km' => current(
                    $statEleve->getNbDe1A3KmByMillesime($this->millesime, 'etablissement',
                        $etablissementId))['effectif'],
                'eleves3kmEtPlus' => current(
                    $statEleve->getNb3kmEtPlusByMillesime($this->millesime,
                        'etablissement', $etablissementId))['effectif']
            ];
        }
        return $data;
    }

    public function listeDesNoms(): string
    {
        return implode(' ou ', array_values($this->arrayEtablissements));
    }

    public function getNbEtablissements(): int
    {
        return count($this->arrayEtablissements);
    }

    public function getEtablissementIds()
    {
        return array_keys($this->arrayEtablissements);
    }

    public function getArrayEtablissements()
    {
        return $this->arrayEtablissements;
    }

    public function getQuery()
    {
        return $this->query;
    }
}