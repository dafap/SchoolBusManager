<?php
/**
 * Injection des objets dans IndexController
 *
 * Compatibilité ZF3
 * 
 * @project sbm
 * @package SbmPortail/Controller/Service
 * @filesource IndexControllerFactory.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 8 oct. 2018
 * @version 2019-2.5.0
 */
namespace SbmPortail\Controller\Service;

use SbmPortail\Controller\IndexController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class IndexControllerFactory implements FactoryInterface
{

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $sm = $serviceLocator->getServiceLocator();
        $config_controller = [
            'RenderPdfService' => $sm->get('RenderPdfService'),
            'db_manager' => $sm->get('Sbm\DbManager'),
            'authenticate' => $sm->get('SbmAuthentification\Authentication')
        ];
        return new IndexController($config_controller);
    }
}