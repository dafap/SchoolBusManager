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
 * @date 9 avr. 2016
 * @version 2016-2
 */
namespace SbmAdmin\Controller\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use SbmAdmin\Controller\IndexController;
use SbmCommun\Model\StdLib;

class IndexControllerFactory implements FactoryInterface
{

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $sm = $serviceLocator->getServiceLocator();
        $config_application = $sm->get('config');
        $config_controller = [
            'RenderPdfService' => $sm->get('RenderPdfService'),
            'db_manager' => $sm->get('Sbm\DbManager'),
            'form_manager' => $sm->get('Sbm\FormManager'),
            'authenticate' => $sm->get('Dafap\Authenticate'),
            'paginator_count_per_page' => StdLib::getParamR([
                'paginator',
                'count_per_page'
            ], $config_application)
        ];
        return new IndexController($config_controller);
    }
}