<?php
/**
 * Injection des objets dans ConfigController
 *
 * Préparation pour compatibilité avec ZF3
 * 
 * @project sbm
 * @package SbmParent/Controller/Service
 * @filesource ConfigControllerFactory.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 9 avr. 2016
 * @version 2016-2
 */
namespace SbmParent\Controller\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use SbmParent\Controller\ConfigController;
use SbmCommun\Model\StdLib;
use SbmFront\Model\Responsable\Responsable;

class ConfigControllerFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $sm = $serviceLocator->getServiceLocator();
        $config_application = $sm->get('config');
        $config_controller = [
            'db_manager' => $sm->get('Sbm\DbManager'),
            'form_manager' => $sm->get('Sbm\FormManager'),
            'cartographie_manager' => $sm->get('Sbm\CartographieManager'),
            'authenticate' => $sm->get('Dafap\Authenticate'),
            'responsable' => $sm->get(Responsable::class),
            'client' => StdLib::getParamR([
                'sbm',
                'client'
            ], $config_application),
            'accueil' => StdLib::getParamR([
                'sbm',
                'layout',
                'accueil'
            ], $config_application),
            'paginator_count_per_page' => StdLib::getParamR([
                'paginator',
                'count_per_page'
            ], $config_application)
        ];
        return new ConfigController($config_controller);
    }
}