<?php
/**
 * Description courte du fichier
 *
 * Description longue du fichier s'il y en a une
 * 
 * @project project_name
 * @package package_name
 * @filesource IndexController.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 4 févr. 2015
 * @version 2015-1
 */
namespace SbmUser\Controller;

use SbmUser\Form\CreerCompte;
use Zend\View\Model\ViewModel;
use SbmCommun\Model\Mvc\Controller\AbstractActionController;
use SbmCommun\Form\MdpChange;

class IndexController extends AbstractActionController
{

    public function indexAction()
    {
        $auth = $this->getServiceLocator()->get('Sbm\Authenticate');
        return new ViewModel(array(
            'auth' => $auth->getIdentity()
        ));
    }
} 