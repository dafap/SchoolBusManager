<?php
/**
 * Module d'envoi de mail
 *
 * @project sbm
 * @package SbmMail
 * @filesource Module.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 5 avr. 2018
 * @version 2018-2.4.0
 */
namespace SbmMail;

use SbmBase\Module\AbstractModule;
use Zend\EventManager\EventInterface;

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

    public function onBootstrap(EventInterface $e)
    {
        $mailListener = $e->getApplication()
            ->getServiceManager()
            ->get(Model\EnvoiMail::class);
        $eventManager = $e->getTarget()->getEventManager();
        $eventManager->attach($mailListener);
    }
} 