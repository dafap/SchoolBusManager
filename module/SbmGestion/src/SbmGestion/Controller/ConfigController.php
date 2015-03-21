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
}