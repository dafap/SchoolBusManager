<?php
/**
 * Injection des objets dans FinanceController du module SbmAjax
 *
 * Préparation pour compatibilité avec ZF3
 *
 * @project sbm
 * @package SbmAjax/Controller/Service
 * @filesource FinanceControllerFactory.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 29 avr. 2019
 * @version 2019-2.5.0
 */
namespace SbmAjax\Controller\Service;

use SbmAjax\Controller\FinanceController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class FinanceControllerFactory implements FactoryInterface
{

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $sm = $serviceLocator->getServiceLocator();
        $config_controller = [
            'db_manager' => $sm->get('Sbm\DbManager')
        ];
        return new FinanceController($config_controller);
    }
}