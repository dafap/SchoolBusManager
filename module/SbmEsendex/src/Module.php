<?php
/**
 * Ce module est basé sur l'API Esendex
 *
 * @project sbm
 * @package SbmEsendex/src
 * @filesource Module.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 2 juin 2020
 * @version 2020-2.6.0
 */
namespace SbmEsendex;

use SbmBase\Module\AbstractModule;
use Zend\EventManager\EventInterface;
use Zend\ModuleManager\Feature\BootstrapListenerInterface;
use SbmBase\Model\StdLib;

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
        $viewModel->servicesms_name = StdLib::getParamR([
            'sbm',
            'servicesms',
            'name'
        ], $application->getServicemanager()->get('config'));
        $viewModel->hassbmservicesms = true;
    }
}