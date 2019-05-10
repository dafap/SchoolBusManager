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
 * @date 10 mai 2019
 * @version 2018-2.5.0
 */
namespace SbmAdmin\Controller\Service;

use SbmAdmin\Controller\IndexController;
use SbmBase\Model\StdLib;
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
            'RenderPdfService' => $sm->get('RenderPdfService'),
            'db_manager' => $sm->get('Sbm\DbManager'),
            'form_manager' => $sm->get('Sbm\FormManager'),
            'authenticate' => $sm->get('SbmAuthentification\Authentication'),
            'paginator_count_per_page' => StdLib::getParamR(
                [
                    'paginator',
                    'count_per_page'
                ], $config_application)
        ];
        return new IndexController($config_controller);
    }
}