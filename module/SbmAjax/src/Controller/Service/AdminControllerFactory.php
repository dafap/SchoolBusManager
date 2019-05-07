<?php
/**
 * Injection des objets dans AdminController du module SbmAjax
 *
 * Préparation pour compatibilité avec ZF3
 *
 * @project sbm
 * @package SbmAjax/Controller/Service
 * @filesource AdminControllerFactory.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 29 avr. 2019
 * @version 2019-2.5.0
 */
namespace SbmAjax\Controller\Service;

use SbmAjax\Controller\AdminController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class AdminControllerFactory implements FactoryInterface
{

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $sm = $serviceLocator->getServiceLocator();
        $viewHelperManager = $sm->get('ViewHelperManager');
        $config_controller = [
            'db_manager' => $sm->get('Sbm\DbManager'),
            'viewHelperManager' => $viewHelperManager
        ];
        return new AdminController($config_controller);
    }
}