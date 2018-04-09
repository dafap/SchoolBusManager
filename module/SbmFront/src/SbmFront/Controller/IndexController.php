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
 * @date 4 avr. 2018
 * @version 2018-2.4.0
 */
namespace SbmFront\Controller;

use SbmCommun\Model\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use SbmBase\Model\Session;

class IndexController extends AbstractActionController
{

    public function indexAction()
    {
        $form = $this->login_form;
        $form->setAttribute('action', 
            $this->url()
                ->fromRoute('login', 
                [
                    'action' => 'login'
                ]));
        $tCalendar = $this->db_manager->get('Sbm\Db\System\Calendar');
        return new ViewModel(
            [
                'form' => $form->prepare(),
                'client' => $this->client,
                'accueil' => $this->accueil,
                'as' => Session::get('as')['libelle'],
                'etat' => $tCalendar->etatDuSite(),
                'permanences' => $tCalendar->getPermanences()
            ]);
    }
    
    /*
     * public function testAction()
     * {
     * $result = null;
     * // $requete = $this->db_manager->get(\SbmFront\Essai\Requete::class);
     * // $result = $requete->test();
     * return new ViewModel([
     * 'args' => $result
     * ]);
     * }
     */
}