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
 * @date 12 avr. 2018
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
        $view = new ViewModel(
            [
                'form' => $form->prepare(),
                'communes_membres' => $this->communes_membres,
                'client' => $this->client,
                'accueil' => $this->accueil,
                'as' => Session::get('as')['libelle'],
                'etat' => $tCalendar->etatDuSite(),
                'permanences' => $tCalendar->getPermanences(),
                'url_ts_region' => $this->url_ts_region
            ]);
        //die(var_dump($tCalendar->etatDuSite()['etat']));
        switch ($tCalendar->etatDuSite()['etat']) {
            case 0:
                $view->setTemplate('sbm-front/index/index-avant.phtml');
                break;
            case 1:
                $view->setTemplate('sbm-front/index/index-pendant.phtml');
                break;
            default:
                $view->setTemplate('sbm-front/index/index-apres.phtml');
                break;
        }        
        return $view;
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