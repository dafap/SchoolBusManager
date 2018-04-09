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
 * @date 4 avr. 2018
 * @version 2018-2.4.0
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