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
 * @date 12 févr. 2014
 * @version 2014-1
 */
namespace SbmGestion\Controller;

use SbmCommun\Model\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use DafapSession\Model\Session;
use Zend\Http\PhpEnvironment\Response;

class IndexController extends AbstractActionController
{

    /**
     * Affectation du millesime de travail.
     * S'il n'y en a pas en session, il prend le dernier millesime valide et le met en session.
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
        $statEleve = $this->getServiceLocator()->get('Sbm\Statistiques\Eleve');
        $statResponsable = $this->getServiceLocator()->get('Sbm\Statistiques\Responsable');
        $statPaiement = $this->getServiceLocator()->get('Sbm\Statistiques\Paiement');
        $millesime = Session::get('millesime');
        return new ViewModel(array(
            'elevesEnregistres' => current($statEleve->getNbEnregistresByMillesime($millesime))['effectif'],
            'elevesInscrits' => current($statEleve->getNbInscritsByMillesime($millesime))['effectif'],
            'elevesPreinscrits' => current($statEleve->getNbPreinscritsByMillesime($millesime))['effectif'],
            'elevesRayes' => current($statEleve->getNbRayesByMillesime($millesime))['effectif'],
            'elevesFamilleAcceuil' => current($statEleve->getNbFamilleAccueilByMillesime($millesime))['effectif'],
            'elevesGardeAlternee' => current($statEleve->getNbGardeAlterneeByMillesime($millesime))['effectif'],
            'elevesMoins1km' => current($statEleve->getNbMoins1KmByMillesime($millesime))['effectif'],
            'elevesDe1A3km' => current($statEleve->getNbDe1A3KmByMillesime($millesime))['effectif'],
            'eleves3kmEtPlus' => current($statEleve->getNb3kmEtPlusByMillesime($millesime))['effectif'],
            'responsablesEnregistres' => current($statResponsable->getNbEnregistres())['effectif'],
            'responsablesAvecEnfant' => current($statResponsable->getNbAvecEnfant())['effectif'],
            'responsablesSansEnfant' => current($statResponsable->getNbSansEnfant())['effectif'],
            'responsablesHorsZone' => current($statResponsable->getNbCommuneNonMembre())['effectif'],
            'responsablesDemenagement' => current($statResponsable->getNbDemenagement())['effectif'],
            'paiements' => $statPaiement->getSumByAsMode($millesime)
        ));
    }
}