<?php
/**
 * Injection des objets dans PortailController
 *
 * @project sbm
 * @package SbmPortail/src/Controller/Service
 * @filesource PortailControllerFactory.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 24 sept. 2020
 * @version 2020-2.6.0
 */
namespace SbmPortail\Controller\Service;

use SbmPortail\Controller\PortailController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class PortailControllerFactory implements FactoryInterface
{

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $sm = $serviceLocator->getServiceLocator();
        $config_controller = [
            'categorieId' => $sm->get('SbmAuthentification\Authentication')->by('email')->getCategorieId()
        ];
        return new PortailController($config_controller);
    }
}