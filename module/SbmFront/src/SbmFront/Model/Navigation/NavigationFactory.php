<?php
/**
 * Test menu dynamique
 *
 * Description longue du fichier s'il y en a une
 * 
 * @project sbm
 * @package SbmFront/Model/Navigation
 * @filesource NavigationFactory.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 9 août 2015
 * @version 2015-1
 */
namespace SbmFront\Model\Navigation;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class NavigationFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $navigation =  new Navigation();
        return $navigation->createService($serviceLocator);
    }
}