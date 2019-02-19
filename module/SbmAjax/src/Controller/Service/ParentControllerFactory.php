<?php
/**
 * Description courte du fichier
 *
 * Préparation pour compatibilité avec ZF3
 * 
 * @project sbm
 * @package SbmAjax/Controller/Service
 * @filesource ParentControllerFactory.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 28 sept. 2018
 * @version 2019-2.5.0
 */
namespace SbmAjax\Controller\Service;

use SbmAjax\Controller\ParentController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ParentControllerFactory implements FactoryInterface
{

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $sm = $serviceLocator->getServiceLocator();
        $config_controller = [
            'db_manager' => $sm->get('Sbm\DbManager')
        ];
        return new ParentController($config_controller);
    }
}