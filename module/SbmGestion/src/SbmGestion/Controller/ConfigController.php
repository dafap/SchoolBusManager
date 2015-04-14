<?php
/**
 * Controller du module SbmGestion permettant de gérer le compte de l'utilisateur et de revenir dans l'espace des gestionnaires
 *
 *
 * @project sbm
 * @package module/SbmGestion/src/SbmGestion/Controller
 * @filesource ConfigController.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 12 févr. 2015
 * @version 2015-1
 */
namespace SbmGestion\Controller;

use SbmCommun\Model\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use SbmCommun\Model\Db\DbLib;


class ConfigController extends AbstractActionController
{
    public function indexAction()
    {
        return new ViewModel();
    }
    
    public function modifCompteAction()
    {
        $retour = $this->url()->fromRoute('sbmgestion');
        return $this->redirectToOrigin()->setBack($retour)->toRoute('login', array('action' => 'modif-compte'));
    }
    
    public function mdpChangeAction()
    {
        $retour = $this->url()->fromRoute('sbmgestion');
        return $this->redirectToOrigin()->setBack($retour)->toRoute('login', array('action' => 'mdp-change'));
    }
    
    public function emailChangeAction()
    {
        $retour = $this->url()->fromRoute('sbmgestion');
        return $this->redirectToOrigin()->setBack($retour)->toRoute('login', array('action' => 'email-change'));
    }
}