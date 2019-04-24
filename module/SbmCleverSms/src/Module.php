<?php
/**
 * Module de gestion des envois de SMS
 *
 * Ce module est basé sur l'API de Clever SMS
 *
 * @project sbm
 * @package SbmCleverSms/src
 * @filesource Module.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 3 mars 2019
 * @version 2019-2.5.0
 */
namespace SbmCleverSms;

use SbmBase\Module\AbstractModule;
use Zend\EventManager\EventInterface;
use Zend\ModuleManager\Feature\BootstrapListenerInterface;

class Module extends AbstractModule implements BootstrapListenerInterface
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
        $application = $e->getParam('application');
        $application->getServiceManager()->setService('sbmservicesms', true);
        $application->getServicemanager()
            ->get('Sbm\DbManager')
            ->setService('sbmservicesms', true);
        $application->getServicemanager()
            ->get('Sbm\FormManager')
            ->setService('sbmservicesms', true);
        $viewModel = $application->getMvcEvent()->getViewModel();

        $viewModel->hassbmservicesms = true;
    }
}