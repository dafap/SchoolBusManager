<?php
/**
 * Module d'envoi de mail
 *
 * @project sbm
 * @package DafapMail
 * @filesource Module.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 15 mai 2015
 * @version 2015-1
 */
namespace DafapMail;

use ZfcBase\Module\AbstractModule;
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
        $eventManager = $e->getTarget()->getEventManager();
        $eventManager->attach(new Model\EnvoiMail());
    }
} 