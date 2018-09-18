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
 * @date 17 août 2016
 * @version 2016-2.2.0
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
        $eventManager->attach(MvcEvent::EVENT_RENDER, function ($e) use($configLayout) {
            $e->getViewModel()
                ->setVariable('parameter', $configLayout);
        });
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
    }
}
