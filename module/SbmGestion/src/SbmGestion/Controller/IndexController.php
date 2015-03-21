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


class IndexController extends AbstractActionController
{
    /**
     * Affectation du millesime de travail. S'il n'y en a pas en session, il prend le dernier millesime valide et le met en session.
     * 
     * (non-PHPdoc)
     * @see \Zend\Mvc\Controller\AbstractActionController::indexAction()
     */
    public function indexAction()
    {
        $auth = $this->getServiceLocator()->get('Sbm\Authenticate');        
        return new ViewModel(array('as_libelle' => Session::get('as_libelle'), 'auth' => $auth->getIdentity()));
    }
}