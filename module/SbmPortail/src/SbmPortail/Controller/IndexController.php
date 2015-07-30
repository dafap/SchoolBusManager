<?php
/**
 * Controller du portail ouvert aux invités en consultation
 *
 * transporteur, etablissement, secretariat
 * 
 * @project sbm
 * @package SbmPortail/Controller
 * @filesource IndexController.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 30 juil. 2015
 * @version 2015-1
 */
namespace SbmPortail\Controller;

use SbmCommun\Model\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Http\PhpEnvironment\Response;
use DafapSession\Model\Session;
use SbmCommun\Model\StdLib;

class IndexController extends AbstractActionController
{

    public function indexAction()
    {
        $auth = $this->getServiceLocator()
            ->get('Dafap\Authenticate')
            ->by('email');
        if (! $auth->hasIdentity()) {
            return $this->redirect()->toRoute('login', array(
                'action' => 'home-page'
            ));
        }
        switch ($auth->getCategorieId()) {
            case 2:
                return $this->redirect()->toRoute('sbmportail', array(
                    'action' => 'tr-index'
                ));
                break;
            case 3:
                return $this->redirect()->toRoute('sbmportail', array(
                    'action' => 'et-index'
                ));
                break;
            case 200:
            case 253:
            case 254:
            case 255:
                return $this->redirect()->toRoute('sbmportail', array(
                    'action' => 'ccda-index'
                ));
                break;
            default:
                return $this->redirect()->toRoute('login', array(
                    'action' => 'home-page'
                ));
                break;
        }
    }

    public function retourAction()
    {
        try {
            return $this->redirectToOrigin()->back();
        } catch (Exception $e) {
            return $this->redirect()->toRoute('login', array(
                'action' => 'home-page'
            ));
        }
    }

    public function ccdaIndexAction()
    {
        // reprendre le tableau de bord complet du gestionnaire
        return new ViewModel();
    }

    public function etIndexAction()
    {
        return new ViewModel(array(
            'elevesTransportes' => 'xxx',
            'circuits' => array(),
            'transporteurs' => array()
        ));
    }

    public function trIndexAction()
    {
        return new ViewModel(array(
            'elevesATransporter' => 'xxx',
            'nbCircuits' => 'yyy'
        ));
    }

    public function trElevesAction()
    {
        $auth = $this->getServiceLocator()
            ->get('Dafap\Authenticate')
            ->by('email');
        if (! $auth->hasIdentity()) {
            return $this->redirect()->toRoute('login', array(
                'action' => 'home-page'
            ));
        }
        $identity = $auth->getIdentity();
        $email = $identity['email'];
        
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            // ce n'était pas un post. Prendre les paramètres éventuellement dans la session (login ou cas du paginator ou de F5 )
            $this->sbm_isPost = false;
            $args = Session::get('post', array(), $this->getSessionNamespace('tr-eleves'));
        } else {
            // c'était un post ; on le met en session si ce n'est pas un retour ou un cancel
            $args = $prg;
            $retour = StdLib::getParam('op', $args, '') == 'retour';
            if ($retour) {
                // dans ce cas, il s'agit du retour d'une action de type suppr, ajout ou edit. Comme pour un get, on récupère ce qui est en session.
                $this->sbm_isPost = false;
                $args = Session::get('post', array(), $this->getSessionNamespace('tr-eleves'));
            } else {
                if (array_key_exists('cancel', $args)) {
                    try {
                        return $this->redirectToOrigin()->back();
                    } catch (\SbmCommun\Model\Mvc\Controller\Plugin\Exception $e) {
                        return $this->redirect()->toRoute('sbmportail', array(
                            'action' => 'tr-index'
                        ));
                    }
                } elseif (array_key_exists('origine', $args)) {
                    $this->redirectToOrigin()->setBack($args['origine']);
                    unset($args['origine']);
                }
                $this->sbm_isPost = true;
                Session::set('post', $args, $this->getSessionNamespace('tr-eleves'));
            }
        }
        // formulaire des critères de recherche
        $criteres_form = new \SbmPortail\Form\CriteresForm();
        // initialiser le form pour les select ...
        $criteres_form->setValueOptions('etablissementId', $this->getServiceLocator()
            ->get('Sbm\Db\Select\EtablissementsDesservis'))
            ->setValueOptions('classeId', $this->getServiceLocator()
            ->get('Sbm\Db\Select\Classes'))
            ->setValueOptions('serviceId', $this->getServiceLocator()
            ->get('Sbm\Db\Select\Services'))
            ->setValueOptions('stationId', $this->getServiceLocator()
            ->get('Sbm\Db\Select\Stations')
            ->toutes());
        
        // créer un objectData qui contient la méthode getWhere() adhoc
        $criteres_obj = new \SbmPortail\Model\Db\ObjectData\Criteres($criteres_form->getElementNames());
        
        if ($this->sbm_isPost) {
            $criteres_form->setData($args);
            if ($criteres_form->isValid()) {
                $criteres_obj->exchangeArray($criteres_form->getData());
            }
        }
        // récupère les données de la session si le post n'a pas été validé dans le formulaire (pas de post ou invalide)
        if (! $criteres_form->hasValidated() && ! empty($args)) {
            $criteres_obj->exchangeArray($args);
            $criteres_form->setData($criteres_obj->getArrayCopy());
        }
        
        switch ($auth->getCategorieId()) {
            case 2:
                $right = $this->getServiceLocator()
                    ->get('Sbm\Db\Table\transporteurs')
                    ->getTransporteurId($email);
                $where = $criteres_obj->getWhere()
                    ->nest()
                    ->equalTo('ser1.transporteurId', $right)->or->equalTo('ser2.transporteurId', $right)->unnest();
                $paginator = $this->getServiceLocator()
                    ->get('Sbm\Db\Query\AffectationsServicesStations')
                    ->paginatorScolaritesR($where, array(
                    'nom',
                    'prenom'
                ));
                break;
            case 3:
                break;
            case 200:
                break;
            default:
                $paginator = $this->getServiceLocator()
                    ->get('Sbm\Db\Query\ElevesResponsables')
                    ->paginatorScolaritesR2($criteres_obj->getWhere(), array(
                    'nom',
                    'prenom'
                ));
                break;
        }
        
        return new ViewModel(array(
            'paginator' => $paginator,
            'page' => $this->params('page', 1),
            'nb_pagination' => $this->getNbPagination('nb_eleves', 10),
            'criteres_form' => $criteres_form
        ));
    }

    /**
     * envoie un evenement contenant les paramètres de création d'un document pdf
     * (le listener SbmPdf\Listener\PdfListener lancera la création du pdf)
     * Il n'y a pas de vue associée à cette action puisque la response html est créée par \TCPDF
     */
    public function trPdfAction()
    {
        $auth = $this->getServiceLocator()
            ->get('Dafap\Authenticate')
            ->by('email');
        if (! $auth->hasIdentity()) {
            return $this->redirect()->toRoute('login', array(
                'action' => 'home-page'
            ));
        }
        $identity = $auth->getIdentity();
        $email = $identity['email'];
        $currentPage = $this->params('page', 1);
        
        $criteres_form = new \SbmPortail\Form\CriteresForm();
        $criteres_obj = new \SbmPortail\Model\Db\ObjectData\Criteres($criteres_form->getElementNames());
        $criteres = Session::get('post', array(), $this->getSessionNamespace('tr-eleves'));
        if (! empty($criteres)) {
            $criteres_obj->exchangeArray($criteres);
        }
        $filtre = array(
            'criteres' => array(),
            'strict' => array(
                'empty' => array(),
                'not empty' => array()
            )
        );
        $expressions = array();
        switch ($auth->getCategorieId()) {
            case 2:
                $right = $this->getServiceLocator()
                    ->get('Sbm\Db\Table\transporteurs')
                    ->getTransporteurId($email);
                $where = $criteres_obj->getWhere()
                    ->nest()
                    ->equalTo('ser1.transporteurId', $right)->or->equalTo('ser2.transporteurId', $right)->unnest();
                $filtre = $criteres_obj->getCriteres();
                $expressions[] = "(transporteur1Id = $right OR transporteur2Id = $right)";
                $documentId = 'List élèves portail transporteur';
                
                break;
            case 3:
                break;
            case 200:
                break;
            default:
                $where = $criteres_obj->getWhere();
                $criteres = array_merge($criteres, array(
                    'millesime' => $this->getFromSession('millesime')
                ));
                $documentId = 'List élèves portail transporteur';
                break;
        }
        $call_pdf = $this->getServiceLocator()->get('RenderPdfService');
        $call_pdf->setParam('documentId', $documentId);
        if (! empty($filtre['criteres'])) {
            $call_pdf->setParam('where', $where)
                ->setParam('expression', $expressions)
                ->setParam('criteres', $filtre['criteres'])
                ->setParam('strict', $filtre['strict']);
        }
        $call_pdf->renderPdf();
        
        $this->flashMessenger()->addSuccessMessage("Création d'un pdf.");
    }

    public function trCircuitsAction()
    {
        return new ViewModel(array(
            'nb_pagination' => 'xxx',
            'page' => 'yyy'
        ));
    }

    public function trCarteEtablissementsAction()
    {
        $this->redirectToOrigin()->setBack('/portail/tr-index');
        return $this->redirect()->toRoute('sbmcarte', array(
            'action' => 'etablissements'
        ));
    }

    public function trCarteStationsAction()
    {
        $this->redirectToOrigin()->setBack('/portail/tr-index');
        return $this->redirect()->toRoute('sbmcarte', array(
            'action' => 'stations'
        ));
    }
    
    // ===========================================================================================================
    // méthodes du menu Bienvenue
    //
    public function modifCompteAction()
    {
        $retour = $this->url()->fromRoute('sbmportail');
        return $this->redirectToOrigin()
            ->setBack($retour)
            ->toRoute('login', array(
            'action' => 'modif-compte'
        ));
    }

    public function localisationAction()
    {
        $this->flashMessenger()->addWarningMessage('La localisation n\'est pas possible pour votre catégorie d\'utilisateurs.');
        return $this->redirect()->toRoute('sbmportail');
    }

    public function mdpChangeAction()
    {
        $retour = $this->url()->fromRoute('sbmportail');
        return $this->redirectToOrigin()
            ->setBack($retour)
            ->toRoute('login', array(
            'action' => 'mdp-change'
        ));
    }

    public function emailChangeAction()
    {
        $retour = $this->url()->fromRoute('sbmportail');
        return $this->redirectToOrigin()
            ->setBack($retour)
            ->toRoute('login', array(
            'action' => 'email-change'
        ));
    }

    public function messageAction()
    {
        $retour = $this->url()->fromRoute('sbmportail');
        return $this->redirectToOrigin()
            ->setBack($retour)
            ->toRoute('dafapmail');
    }
}