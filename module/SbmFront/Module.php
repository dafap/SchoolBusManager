<?php
namespace SbmFront;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use ZfcBase\Module\AbstractModule;
use Zend\ModuleManager\Feature\LocatorRegisteredInterface;
use Zend\EventManager\EventInterface;
use SbmFront\Listener\SendListener;

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
}
