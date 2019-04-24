<?php
/**
 * Controller principal du module SbmGestion
 *
 *
 * @project sbm
 * @package module/SbmGestion/src/SbmGestion/Controller
 * @filesource IndexController.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 12 sept. 2018
 * @version 2019-2.5.0
 */
namespace SbmGestion\Controller;

use SbmBase\Model\Session;
use SbmCommun\Model\Mvc\Controller\AbstractActionController;
use Zend\Http\PhpEnvironment\Response;
use Zend\View\Model\ViewModel;

class IndexController extends AbstractActionController
{

    /**
     * Affectation du millesime de travail. S'il n'y en a pas en session, il prend le
     * dernier millesime valide et le met en session.
     * (non-PHPdoc)
     *
     * @see \Zend\Mvc\Controller\AbstractActionController::indexAction()
     */
    public function indexAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        }
        $this->redirectToOrigin()->reset(); // on s'assure que la pile des retours est
                                            // vide
        $statEleve = $this->db_manager->get('Sbm\Statistiques\Eleve');
        $statResponsable = $this->db_manager->get('Sbm\Statistiques\Responsable');
        $statPaiement = $this->db_manager->get('Sbm\Statistiques\Paiement');
        $millesime = Session::get('millesime');
        return new ViewModel(
            [
                'elevesEnregistres' => current(
                    $statEleve->getNbEnregistresByMillesime($millesime))['effectif'],
                'elevesInscrits' => current(
                    $statEleve->getNbInscritsByMillesime($millesime))['effectif'],
                'elevesInscritsRayes' => current(
                    $statEleve->getNbRayesByMillesime($millesime, true))['effectif'],
                'elevesPreinscrits' => current(
                    $statEleve->getNbPreinscritsByMillesime($millesime))['effectif'],
                'elevesPreinscritsRayes' => current(
                    $statEleve->getNbRayesByMillesime($millesime, false))['effectif'],
                'elevesFamilleAcceuil' => current(
                    $statEleve->getNbFamilleAccueilByMillesime($millesime))['effectif'],
                'elevesGardeAlternee' => current(
                    $statEleve->getNbGardeAlterneeByMillesime($millesime))['effectif'],
                'elevesMoins1km' => current(
                    $statEleve->getNbMoins1KmByMillesime($millesime))['effectif'],
                'elevesDe1A3km' => current(
                    $statEleve->getNbDe1A3KmByMillesime($millesime))['effectif'],
                'eleves3kmEtPlus' => current(
                    $statEleve->getNb3kmEtPlusByMillesime($millesime))['effectif'],
                'responsablesEnregistres' => current($statResponsable->getNbEnregistres())['effectif'],
                'responsablesAvecEnfant' => current($statResponsable->getNbAvecEnfant())['effectif'],
                'responsablesSansEnfant' => current($statResponsable->getNbSansEnfant())['effectif'],
                'responsablesHorsZone' => current(
                    $statResponsable->getNbCommuneNonMembre())['effectif'],
                'responsablesDemenagement' => current(
                    $statResponsable->getNbDemenagement())['effectif'],
                'paiements' => $statPaiement->getSumByAsMode($millesime)
            ]);
    }
}