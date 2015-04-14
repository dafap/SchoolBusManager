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
        $eventManager->attach('route', array(
            $this,
            'checkAuthenticated'
        ));
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

    public function checkAuthenticated(MvcEvent $e)
    {
        if (! $this->inListeBlanche($e)) {
            $auth = $e->getApplication()->getServiceManager()->get('Sbm\Authenticate');
            if (!$auth->hasIdentity()) {
                $e->getRouteMatch()->setParam('controller', 'SbmFront\Controller\Index')->setParam('action', 'index');
            }
        }
    }

    /**
     * Contrôle si l'accès à cette action est autorisée aux anonymes.
     * Les actions autorisées sont listées dans une liste blanche.
     * 
     * @param MvcEvent $e
     */
    public function inListeBlanche(MvcEvent $e)
    {
        // liste des routes ouvertes aux anonymes
        $listeBlanche = array(
            'SbmFront\Controller\Index' => array('index', 'test'),
            'SbmFront\Controller\Login' => array('login', 'logout', 'mdp-demande', 'creer-compte', 'confirm'),
            'SbmPaiement\Controller\Index' => array('notification')
        );
        // contrôle
        $routeMatch = $e->getRouteMatch();
        $controller = $routeMatch->getParam('controller');
        $action = $routeMatch->getParam('action');
        if (\array_key_exists($controller, $listeBlanche)) {
            if (\in_array($action, $listeBlanche[$controller])) {
                return true;
            }
        }
        return false;
    }
}
