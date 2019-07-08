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
 * @date 22 août 2018
 * @version 2018-2.4.2
 */
namespace SbmAjax\Controller\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use SbmAjax\Controller\AdminController;
use SbmBase\Model\StdLib;

class AdminControllerFactory implements FactoryInterface
{

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $sm = $serviceLocator->getServiceLocator();
        $config_application = $sm->get('config');
        $viewHelperManager = $sm->get('ViewHelperManager');        
        $config_controller = [
            'db_manager' => $sm->get('Sbm\DbManager'),
            'viewHelperManager' => $viewHelperManager
        ];
        return new AdminController($config_controller);
    }
}