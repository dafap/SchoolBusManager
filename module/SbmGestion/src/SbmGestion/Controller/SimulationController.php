<?php
/**
 * Controller principal du module SbmGestion
 * Méthodes utilisées pour réaliser une simulation
 *
 * @project sbm
 * @package module/SbmGestion/src/SbmGestion/Controller
 * @filesource IndexController.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 6 avr. 2018
 * @version 2018-2.4.0
 */
namespace SbmGestion\Controller;

use SbmCommun\Model\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use SbmCommun\Model\Db\DbLib;

class SimulationController extends AbstractActionController
{

    public function indexAction()
    {
        return new ViewModel();
    }
}