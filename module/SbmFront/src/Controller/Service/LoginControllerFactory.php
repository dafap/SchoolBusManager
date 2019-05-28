<?php
/**
 * Injection des objets dans LoginController
 *
 *
 * @project sbm
 * @package SbmFront/Controller/Service
 * @filesource LoginControllerFactory.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 28 mai 2019
 * @version 2019-2.5.0
 */
namespace SbmFront\Controller\Service;

use SbmBase\Model\StdLib;
use SbmCartographie\GoogleMaps\DistanceMatrix;
use SbmFront\Controller\LoginController;
use SbmFront\Model\Responsable\Service\ResponsableManager as Responsable;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class LoginControllerFactory implements FactoryInterface
{

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $sm = $serviceLocator->getServiceLocator();
        $cm = $sm->get('Sbm\CartographieManager');
        $config_application = $sm->get('config');
        $config_controller = [
            'theme' => $sm->get(\SbmInstallation\Model\Theme::class),
            'db_manager' => $sm->get('Sbm\DbManager'),
            'form_manager' => $sm->get('Sbm\FormManager'),
            'authenticate' => $sm->get('SbmAuthentification\Authentication'),
            'responsable' => $sm->get(Responsable::class),
            'oDistanceMatrix' => $cm->get(DistanceMatrix::class),
            'config_cartes' => $cm->get('cartes'),
            'mail_config' => StdLib::getParamR([
                'sbm',
                'mail'
            ], $config_application),
            'img' => StdLib::getParamR([
                'sbm',
                'img'
            ], $config_application),
            'client' => StdLib::getParamR([
                'sbm',
                'client'
            ], $config_application),
            'accueil' => StdLib::getParamR([
                'sbm',
                'layout',
                'accueil'
            ], $config_application)
        ];
        return new LoginController($config_controller);
    }
}