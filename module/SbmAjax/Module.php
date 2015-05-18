<?php
/**
 * Module SbmAjax
 *
 * Les routes définies dans ce module permettent de passer davantage de paramètres.
 * Le layout est désactivé dans les vues de ce module lorsquel'appel se fait par ajax.
 * 
 * @project sbm
 * @package SbmAjax
 * @filesource Module.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 5 mai 2015
 * @version 2015-1
 */

namespace SbmAjax;

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
    
    /**
     * Si on veut supprimer le layout uniquement pour les requêtes ajax on regarde la méthode isXmlHttpRequest().
     * Ici, je supprime le layout pour toutes les requêtes adressées à ce module.
     * 
     * @see https://samsonasik.wordpress.com/2012/12/02/zend-framework-2-disable-layout-in-specific-module/
     * @param MvcEvent $e
     */
    public function onBootstrap(MvcEvent $e)
    {
        $sharedEvents        = $e->getApplication()->getEventManager()->getSharedManager();
        $sharedEvents->attach(__NAMESPACE__, 'dispatch', function($e) {
            $result = $e->getResult();
            if ($result instanceof \Zend\View\Model\ViewModel) {
                // si on veut supprimer le layout uniquement pour ajax
                //$result->setTerminal($e->getRequest()->isXmlHttpRequest());
                // si on veut supprimer le layout quelque soit le type de requête
                //set true : $result->setTerminal(true);
                $result->setTerminal(true);
            } else {
                throw new \Exception('SbmAjax\Module::onBootstap() n\'a pas reçu un \Zend\View\Model\ViewModel');
            }
        });
    }
}
