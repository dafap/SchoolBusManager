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
 * @date 11 juin 2020
 * @version 2020-2.5.4
 */
namespace SbmFront\Controller;

use SbmBase\Model\Session;
use SbmCommun\Model\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

/**
 * Dispose des propriétés provenant de IndexControllerFactory : - theme (objet
 * \SbmInstallation\Model\Theme) - db_manager (objet
 * \SbmCommun\Model\Db\Service\DbManager) - login_form (objet \SbmFront\Form\Login) -
 * client - accueil (url de l'organisateur - voir config/autoload/sbm.local.php) -
 * url_ts_region (url du site d'inscription de la région - voir
 * config/autoload/sbm.local.php)
 *
 * @author admin
 */
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
                'communes' => $this->db_manager->get('Sbm\Db\Table\Communes'),
                'calendar' => $tCalendar,
                'theme' => $this->theme,
                'client' => $this->client,
                'accueil' => $this->accueil,
                'millesime' => Session::get('millesime'),
                'as' => Session::get('as')['libelle'],
                'dateDebutAs' => Session::get('as')['dateDebut'],
                'url_ts_organisateur' => $this->url_ts_organisateur,
                'url_ts_region' => $this->url_ts_region
            ]);
        switch ($tCalendar->getEtatDuSite()['etat']) {
            case $tCalendar::ETAT_AVANT:
                $view->setTemplate('sbm-front/index/index-avant.phtml');
                break;
            case $tCalendar::ETAT_PENDANT:
                $view->setTemplate('sbm-front/index/index-pendant.phtml');
                break;
            case $tCalendar::ETAT_APRES:
                $view->setTemplate('sbm-front/index/index-apres.phtml');
                break;
            default:
                $view->setTemplate('sbm-front/index/index-ferme.phtml');
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
}