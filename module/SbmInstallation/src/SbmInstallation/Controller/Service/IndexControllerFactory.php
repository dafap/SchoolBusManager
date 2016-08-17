<?php
/**
 * Injection des objets dans IndexController
 *
 * Préparation pour compatibilité avec ZF3
 * 
 * @project sbm
 * @package SbmInstallation/Controller/Service
 * @filesource IndexControllerFactory.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 17 août 2016
 * @version 2016-2.2.0
 */
namespace SbmInstallation\Controller\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use SbmInstallation\Controller\IndexController;
use SbmBase\Model\StdLib;

class IndexControllerFactory implements FactoryInterface
{

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $sm = $serviceLocator->getServiceLocator();
        $config_application = $sm->get('config');
        $config_controller = [
            'db_manager' => $sm->get('Sbm\DbManager'),
            'db_config' => StdLib::getParam('db', $config_application),
            'db_design' => StdLib::getParam('db_design', $config_application),
            'config_paiement' => StdLib::getParamR([
                'sbm',
                'paiement'
            ], $config_application),
            'error_log' => StdLib::getParamR([
                'php_settings',
                'error_log'
            ], $config_application),
            'mailchimp_key' => StdLib::getParamR([
                'sbm',
                'mailchimp'
            ], $config_application, ''),
            'img' => StdLib::getParamR([
                'sbm',
                'img'
            ], $config_application, [])
        ];
        return new IndexController($config_controller);
    }
}   