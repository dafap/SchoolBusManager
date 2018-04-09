<?php
/**
 * Module principal de l'application
 *
 * Propose la page d'accueil et les méthodes pour se loger
 *
 * @project sbm
 * @package SbmFront
 * @filesource Module.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 4 avr. 2018
 * @version 2018-2.4.0
 */
namespace SbmFront;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use SbmBase\Module\AbstractModule;
use SbmBase\Model\StdLib;

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

    public function onBootstrap(MvcEvent $e)
    {
        $eventManager = $e->getApplication()->getEventManager();
        $config = $e->getApplication()
            ->getServiceManager()
            ->get('config');
        $configLayout = StdLib::getParam('sbm', $config);
        $eventManager->attach(MvcEvent::EVENT_RENDER, 
            function ($e) use($configLayout) {
                $e->getViewModel()
                    ->setVariable('parameter', $configLayout);
            });
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
    }
}
