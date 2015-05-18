<?php
namespace SbmFront;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use ZfcBase\Module\AbstractModule;
use Zend\ModuleManager\Feature\LocatorRegisteredInterface;
use Zend\EventManager\EventInterface;
use SbmFront\Listener\SendListener;
use SbmCommun\Model\StdLib;

class Module extends AbstractModule implements LocatorRegisteredInterface
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
