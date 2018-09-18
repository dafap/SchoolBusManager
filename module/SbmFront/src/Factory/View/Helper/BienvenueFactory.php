<?php
/**
 * Enregistrement du View Helper Bienvenue en tant que service
 * 
 * 
 * @project sbm
 * @package SbmFront/Factory/View/Helper
 * @filesource BienvenueFactory.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 13 févr. 2015
 * @version 2015-1
 */
namespace SbmFront\Factory\View\Helper;

use Zend\Authentication\AuthenticationService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\View\HelperPluginManager;
use SbmFront\View\Helper\Bienvenue;


class BienvenueFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function createService(ServiceLocatorInterface $pluginManager)
    {
        /* @var $pluginManager HelperPluginManager */
        $serviceManager = $pluginManager->getServiceLocator();

        /* @var $authService AuthenticationService */
        $authService = $serviceManager->get('SbmAuthentification\Authentication')->by();

        $viewHelper = new Bienvenue();
        $viewHelper->setAuthService($authService);

        return $viewHelper;
    }
} 