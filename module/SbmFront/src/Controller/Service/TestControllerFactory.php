<?php
/**
 * Injection des objets dans TestController
 *
 * Ne doit pas être mis en ligne
 *
 * @project sbm
 * @package SbmFront/src/Controller/Service
 * @filesource TestControllerFactory.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 10 mai 2020
 * @version 2020-2.6.0
 */
namespace SbmFront\Controller\Service;

use SbmBase\Model\StdLib;
use SbmFront\Controller\TestController;
use SbmFront\Form\Login;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use SbmCartographie\ConvertSystemGeodetic\Projection\ProjectionInterface;

class TestControllerFactory implements FactoryInterface
{

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $sm = $serviceLocator->getServiceLocator();
        $config_application = $sm->get('config');
        $cm = $sm->get('Sbm\CartographieManager');
        $cartographie = $cm->get('cartographie');
        $projection = str_replace('ProjectionInterface',
            StdLib::getParam('system', $cartographie), ProjectionInterface::class);
        $nzone = StdLib::getParam('nzone', $cartographie, 0);
        $config_cartes = $cm->get('cartes');
        $google_api = $cm->get('google_api_browser');
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
            'url_ts_organisateur' => StdLib::getParamR([
                'sbm',
                'ts-organisateur'
            ], $config_application),
            'url_ts_region' => StdLib::getParamR([
                'sbm',
                'ts-region'
            ], $config_application),
            'projection' => new $projection($nzone),
            'config_cartes' => $config_cartes,
            'url_api' => $google_api['js'],
            'authenticate' => $sm->get('SbmAuthentification\Authentication')
        ];
        return new TestController($config_controller);
    }
}