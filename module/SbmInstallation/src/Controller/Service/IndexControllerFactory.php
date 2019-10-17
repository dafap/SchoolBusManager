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
 * @date 17 oct. 2019
 * @version 2019-2.5.2
 */
namespace SbmInstallation\Controller\Service;

use SbmBase\Model\StdLib;
use SbmInstallation\Controller\IndexController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class IndexControllerFactory implements FactoryInterface
{

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $sm = $serviceLocator->getServiceLocator();
        $config_application = $sm->get('config');
        $config_controller = [
            'theme' => $sm->get(\SbmInstallation\Model\Theme::class),
            'db_manager' => $sm->get('Sbm\DbManager'),
            'form_manager' => $sm->get('Sbm\FormManager'),
            'cartographie_manager' => $sm->get('Sbm\CartographieManager'),
            'db_config' => StdLib::getParam('db', $config_application),
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
                'servicemail'
            ], $config_application, ''),
            'img' => StdLib::getParamR([
                'sbm',
                'img'
            ], $config_application, []),
            'hassbmservicesms' => $sm->has('sbmservicesms')
        ];
        if ($sm->has('sbmservicesms')) {
            $config_controller['config_sms'] = StdLib::getParamR([
                'sbm',
                'servicesms'
            ], $config_application);
        }
        return new IndexController($config_controller);
    }
}