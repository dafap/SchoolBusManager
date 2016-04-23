<?php
/**
 * Module de paiement en ligne
 *
 * Définit les classes correspondantes aux moyens de paiement bancaire choisis par l'organisateur
 * 
 * @project sbm
 * @package SbmPaiement
 * @filesource Module.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 30 mars 2015
 * @version 2015-1
 */
namespace SbmPaiement;

use ZfcBase\Module\AbstractModule;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\EventManager\EventInterface;
use Zend\Mvc\MvcEvent;
use SbmCommun\Model\StdLib;

class Module extends AbstractModule implements AutoloaderProviderInterface, ConfigProviderInterface
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
        $sm = $e->getApplication()->getServiceManager();
        $eventManager = $e->getTarget()->getEventManager();
        // appel du formulaire de paiement d'une plateforme
        $eventManager->attach(new Listener\AppelPlateforme());
        // mise à jour des tables paiements et scolarites à la suite d'un paiement par CB sur une plateforme
        $eventManager->attach($sm->get(Listener\PaiementOK::class));
        $eventManager->attach($sm->get(Listener\ScolariteOK::class));
    }
}