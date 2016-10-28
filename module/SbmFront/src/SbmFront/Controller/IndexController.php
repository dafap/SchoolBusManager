<?php
/**
 * Controleur principal de l'application
 * 
 * Compatible ZF3
 *
 * @project sbm
 * @package module/SbmFront
 * @filesource src/SbmFront/Controller/IndexController.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 28 oct. 2016
 * @version 2016-2
 */
namespace SbmFront\Controller;

use SbmCommun\Model\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use SbmBase\Model\Session;

class IndexController extends AbstractActionController
{

    public function indexAction()
    {
        $form = $this->config['login_form'];
        $form->setAttribute('action', $this->url()
            ->fromRoute('login', array(
            'action' => 'login'
        )));
        $tCalendar = $this->config['db_manager']->get('Sbm\Db\System\Calendar');
        return new ViewModel([
            'form' => $form->prepare(),
            'client' => $this->config['client'],
            'accueil' => $this->config['accueil'],
            'as' => Session::get('as')['libelle'],
            'etat' => $tCalendar->etatDuSite(),
            'permanences' => $tCalendar->getPermanences()
        ]);
    }

    public function testAction()
    {    
        return new ViewModel([]);
    }
}