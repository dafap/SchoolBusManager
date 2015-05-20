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
        $rEtat = $tCalendar->etatDuSite();
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
            'etat' => $rEtat['etat'],
            'msg' => $rEtat['msg']
        ));
    }

    public function testAction()
    {
        $mailTemplate = new MailTemplate('nouveau-compte');
        // die(var_dump($mailTemplate));
        die($mailTemplate->render('nouveau-compte', array(
            'titre' => 'M.',
            'nom' => 'TARTEMPION',
            'prenom' => 'MAurice',
            'url_confirme' => $this->url()
                ->fromRoute('login', array(
                'action' => 'confirm',
                'id' => 'a123fe45'
            ), array(
                'force_canonical' => true
            )),
            'url_annule' => $this->url()
                ->fromRoute('login', array(
                'action' => 'annuler',
                'id' => 'a123fe45'
            ), array(
                'force_canonical' => true
            ))
        )));
        return $view;
    }
}