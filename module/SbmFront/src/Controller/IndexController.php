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
 * @date 28 sept.2018
 * @version 2019-2.5.0
 */
namespace SbmFront\Controller;

use SbmBase\Model\Session;
use SbmCommun\Model\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class IndexController extends AbstractActionController
{

    public function indexAction()
    {
        $form = $this->login_form;
        $form->setAttribute('action',
            $this->url()
                ->fromRoute('login', [
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

    public function horsZoneAction()
    {
        return new ViewModel(
            [
                'accueil' => $this->accueil,
                'client' => $this->client,
                'commune' => $this->params('id')
            ]);
    }

    public function testAction()
    {
        return new ViewModel([]);
    }
}