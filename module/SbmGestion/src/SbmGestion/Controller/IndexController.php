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
 * @date 21 fév. 2025
 * @version 2025-2.4.23
 */
namespace SbmGestion\Controller;

use Zend\View\Model\ViewModel;
use Zend\Http\PhpEnvironment\Response;
use SbmBase\Model\Session;
use SbmCommun\Model\Mvc\Controller\AbstractActionController;

class IndexController extends AbstractActionController
{

    private function effectif($tableau)
    {
        if (is_array($tableau)) {
            $current_element = current($tableau);
            if (is_array($current_element) && array_key_exists('effectif', $current_element)) {
                return $current_element['effectif'];
            }
        }
        return 0;
    }

    /**
     * Affectation du millesime de travail.
     * S'il n'y en a pas en session, il prend le dernier millesime valide et le met en
     * session.
     *
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
                'elevesEnregistres' => $this->effectif(
                    $statEleve->getNbEnregistresByMillesime($millesime)),
                'elevesInscrits' => $this->effectif(
                    $statEleve->getNbInscritsByMillesime($millesime)),
                'elevesInscritsRayes' => $this->effectif(
                    $statEleve->getNbRayesByMillesime($millesime, true)),
                'elevesPreinscrits' => $this->effectif(
                    $statEleve->getNbPreinscritsByMillesime($millesime)),
                'elevesPreinscritsRayes' => $this->effectif(
                    $statEleve->getNbRayesByMillesime($millesime, false)),
                'elevesFamilleAcceuil' => $this->effectif(
                    $statEleve->getNbFamilleAccueilByMillesime($millesime)),
                'elevesGardeAlternee' => $this->effectif(
                    $statEleve->getNbGardeAlterneeByMillesime($millesime)),
                'elevesMoins1km' => $this->effectif(
                    $statEleve->getNbMoins1KmByMillesime($millesime)),
                'elevesDe1A3km' => $this->effectif(
                    $statEleve->getNbDe1A3KmByMillesime($millesime)),
                'eleves3kmEtPlus' => $this->effectif(
                    $statEleve->getNb3kmEtPlusByMillesime($millesime)),
                'responsablesEnregistres' => $this->effectif(
                    $statResponsable->getNbEnregistres()),
                'responsablesAvecEnfant' => $this->effectif(
                    $statResponsable->getNbAvecEnfant()),
                'responsablesSansEnfant' => $this->effectif(
                    $statResponsable->getNbSansEnfant()),
                'responsablesHorsZone' => $this->effectif(
                    $statResponsable->getNbCommuneNonMembre()),
                'responsablesDemenagement' => $this->effectif(
                    $statResponsable->getNbDemenagement()),
                'paiements' => $statPaiement->getSumByAsMode($millesime)
            ]);
    }
}