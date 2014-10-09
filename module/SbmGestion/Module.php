<?php
/**
 * Module SbmGestion pour le service de gestion des transports scolaires
 * - gestion des comptes des utilisateurs
 * - gestion du réseau de transport
 * - gestion des élèves
 * - gestion financière
 * - importations et exporation de données
 *
 * @project sbm
 * @package module/SbmGestion
 * @filesource Module.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 22 janv. 2014
 * @version 2014-1
 */
namespace SbmGestion;

use ZfcBase\Module\AbstractModule;
use Zend\EventManager\EventInterface;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;


class Module extends AbstractModule
{
public function getDir()
    {
        return __DIR__;
    }

    public function getNamespace()
    {
        return __NAMESPACE__;
    }
    
    /*public function onBoostrap(EventInterface $e)
    {
        $eventManager = $e->getApplication()->getEventManager();
        $eventManager->attach(MvcEvent::EVENT_DISPATCH, function($e) {
            $controller = $e->getTarget();
            var_dump(get_class($controller));
            $controller->layout()->flashMessages = array_merge(
               array('success' => $this->flashMessenger()->getSuccessMessages()), 
               array('warning' => $this->flashMessenger()->getWarningMessages()),
               array('error' => $this->flashMessenger()->getErrorMessages()), 
               $this->flashMessenger()->getMessages()
            );
        }, -9500);
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
    }*/
}
