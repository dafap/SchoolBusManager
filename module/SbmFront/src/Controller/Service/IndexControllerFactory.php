<?php
/**
 * Injection des objets dans IndexController
 *
 * Préparation pour compatibilité avec ZF3
 *
 * @project sbm
 * @package SbmFront/Controller/Service
 * @filesource IndexControllerFactory.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 08 juin 2023
 * @version 2023-2.5.18
 */
namespace SbmFront\Controller\Service;

use SbmBase\Model\StdLib;
use SbmFront\Controller\IndexController;
use SbmFront\Form\Login;
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
            'login_form' => $sm->get(Login::class),
            'client' => StdLib::getParamR([
                'sbm',
                'client'
            ], $config_application),
            'accueil' => StdLib::getParamR([
                'sbm',
                'layout',
                'accueil'
            ], $config_application),
            'front' => StdLib::getParamR([
                'sbm',
                'layout',
                'front'
            ], $config_application),
            'url_ts_organisateur' => StdLib::getParamR([
                'sbm',
                'ts-organisateur'
            ], $config_application),
            'url_ts_region' => StdLib::getParamR([
                'sbm',
                'ts-region'
            ], $config_application)
        ];
        return new IndexController($config_controller);
    }
}