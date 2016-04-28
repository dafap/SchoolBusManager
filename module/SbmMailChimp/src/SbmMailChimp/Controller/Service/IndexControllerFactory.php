<?php
/**
 * Injection des objets dans IndexController
 *
 * Préparation pour compatibilité avec ZF3
 * 
 * @project sbm
 * @package SbmMailChimp/Controller/Service
 * @filesource IndexControllerFactory.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 23 avr. 2016
 * @version 2016-2.1
 */
namespace SbmMailChimp\Controller\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use SbmMailChimp\Controller\IndexController;
use SbmCommun\Model\StdLib;

class IndexControllerFactory implements FactoryInterface
{

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $sm = $serviceLocator->getServiceLocator();
        $config_application = $sm->get('config');
        $config_controller = [
            'db_manager' => $sm->get('Sbm\DbManager'),
            'client' => StdLib::getParamR([
                'sbm',
                'client'
            ], $config_application),
            'mail_config' => StdLib::getParamR([
                'sbm',
                'mail'
            ], $config_application),
            'authenticate' => $sm->get('Dafap\Authenticate'),
            'mailchimp_key' => StdLib::getParamR([
                'sbm',
                'mailchimp',
                'key'
            ], $config_application, '')
        ];
        return new IndexController($config_controller);
    }
}
 