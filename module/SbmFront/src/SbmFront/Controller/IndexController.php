<?php
/**
 * Controleur principal de l'application
 *
 *
 * @project sbm
 * @package module/SbmFront
 * @filesource src/SbmFront/Controller/IndexController.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 21 avr. 2014
 * @version 2014-1
 */
namespace SbmFront\Controller;

use SbmCommun\Model\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use SbmFront\Form\Login;
use SbmCommun\Model\StdLib;
use DafapMail\Model\Template as MailTemplate;
use DafapSession\Model\Session;

class IndexController extends AbstractActionController
{

    public function indexAction()
    {
        $form = new Login($this->getServiceLocator());
        $form->setAttribute('action', $this->url()
            ->fromRoute('login', array(
            'action' => 'login'
        )));
        $tCalendar = $this->getServiceLocator()->get('Sbm\Db\System\Calendar');
        return new ViewModel(array(
            'form' => $form->prepare(),
            'client' => StdLib::getParamR(array(
                'sbm',
                'client'
            ), $this->getServiceLocator()->get('config')),
            'accueil' => StdLib::getParamR(array(
                'sbm',
                'layout',
                'accueil'
            ), $this->getServiceLocator()->get('config')),
            'as' => Session::get('as')['libelle'],
            'etat' => $tCalendar->etatDuSite(),
            'permanences' => $tCalendar->getPermanences()
        ));
    }

    public function testAction()
    {
        die(var_dump(Session::get('millesime'), Session::get('as')));
        return $view;
    }
}