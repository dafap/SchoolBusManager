<?php
/**
 * Module SbmCommun qui regroupe les classes, stratégies, filtres, formulaires communs aus
 * - paramétrages
 * - données générales
 * - structures d'exportation et d'importation
 *
 * @project sbm
 * @package module/SbmCommun
 * @filesource Module.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 22 janv. 2014
 * @version 2014-1
 */
namespace SbmCommun;

use Zend\Mvc\MvcEvent;
use Zend\Mvc\ModuleRouteListener;
use Zend\ModuleManager\Feature\BootstrapListenerInterface;
use Zend\EventManager\EventInterface;
use ZfcBase\Module\AbstractModule;
use Zend\View\Helper\Doctype;
use DafapSession\Model\Session;

class Module extends AbstractModule implements BootstrapListenerInterface
{

    public function getDir()
    {
        return __DIR__;
    }

    public function getNamespace()
    {
        return __NAMESPACE__;
    }
    
    public function onBootstrap(EventInterface $e)
    {
        $doctypeHelper = new Doctype();
        $doctypeHelper('HTML5');
        $tCalendar = $e->getApplication()->getServiceManager()->get('Sbm\Db\System\Calendar'); 
        for ($millesime = Session::get('millesime', false); !$millesime; $millesime = Session::get('millesime', false)) {
            Session::set('millesime', $tCalendar->getDefaultMillesime());
        }
        Session::set('as_libelle', $tCalendar->getAnneeScolaire($millesime));
        
        //$eventManager = $e->getApplication()->getEventManager();
        //$moduleRouteListener = new ModuleRouteListener();
        //$moduleRouteListener->attach($eventManager);
        //$serviceManager = $e->getApplication()->getServiceManager();
        //$eventManager->attach($serviceManager->get('SbmCommun\FlashMessenger'));
    }
}
