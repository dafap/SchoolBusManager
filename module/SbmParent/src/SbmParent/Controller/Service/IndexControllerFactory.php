<?php
/**
 * Injection des objets dans IndexController
 *
 * Préparation pour compatibilité avec ZF3
 * 
 * @project sbm
 * @package SbmParent/Controller/Service
 * @filesource IndexControllerFactory.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 5 avr. 2018
 * @version 2018-2.4.0
 */
namespace SbmParent\Controller\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceLocatorInterface;
use SbmParent\Controller\IndexController;
use SbmBase\Model\StdLib;
use SbmCommun\Model\Service as ServicesCommun;
use SbmFront\Model\Responsable\Service\ResponsableManager as Responsable;

class IndexControllerFactory implements FactoryInterface
{

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $sm = $serviceLocator->getServiceLocator();
        $config_application = $sm->get('config');
        $local_sm = new ServiceManager();
        $local_sm->setService('Sbm\DbManager', $sm->get('Sbm\DbManager'))
            ->setService('Sbm\CartographieManager', $sm->get('Sbm\CartographieManager'))
            ->setFactory('SbmPaiement\Plugin\Plateforme', 
            StdLib::getParamR(
                [
                    'service_manager',
                    'factories',
                    'SbmPaiement\Plugin\Plateforme'
                ], $config_application));
        $config_controller = [
            'db_manager' => $sm->get('Sbm\DbManager'),
            'form_manager' => $sm->get('Sbm\FormManager'),
            'authenticate' => $sm->get('SbmAuthentification\Authentication'),
            'responsable' => $sm->get(Responsable::class),
            'local_manager' => $local_sm,
            'client' => StdLib::getParamR(
                [
                    'sbm',
                    'client'
                ], $config_application),
            'accueil' => StdLib::getParamR(
                [
                    'sbm',
                    'layout',
                    'accueil'
                ], $config_application),
            'paginator_count_per_page' => StdLib::getParamR(
                [
                    'paginator',
                    'count_per_page'
                ], $config_application)
        ];
        return new IndexController($config_controller);
    }
}