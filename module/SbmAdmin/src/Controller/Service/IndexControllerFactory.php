<?php
/**
 * Injection des objets dans IndexController du module SbmAdmin
 *
 * Préparation pour compatibilité avec ZF3
 *
 * @project sbm
 * @package SbmAdmin/Controller/Service
 * @filesource IndexControllerFactory.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 18 oct. 2021
 * @version 2021-2.6.4
 */
namespace SbmAdmin\Controller\Service;

use SbmAdmin\Controller\IndexController;
use SbmBase\Model\StdLib;
use SbmCartographie\ConvertSystemGeodetic\Projection\ProjectionInterface;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class IndexControllerFactory implements FactoryInterface
{

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $sm = $serviceLocator->getServiceLocator();
        $cm = $sm->get('Sbm\CartographieManager');
        $cartographie = $cm->get('cartographie');
        $projection = str_replace('ProjectionInterface',
            StdLib::getParam('system', $cartographie), ProjectionInterface::class);
        $nzone = StdLib::getParam('nzone', $cartographie, 0);
        $config_application = $sm->get('config');
        $config_controller = [
            'theme' => $sm->get(\SbmInstallation\Model\Theme::class),
            'db_manager' => $sm->get('Sbm\DbManager'),
            'projection' => new $projection($nzone),
            'form_manager' => $sm->get('Sbm\FormManager'),
            'authenticate' => $sm->get('SbmAuthentification\Authentication'),
            'img' => StdLib::getParamR([
                'sbm',
                'img'
            ], $config_application),
            'client' => StdLib::getParamR([
                'sbm',
                'client'
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