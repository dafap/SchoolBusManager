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
 * @date 28 sept. 2018
 * @version 2019-2.5.0
 */
namespace SbmFront\Factory\View\Helper;

use SbmFront\View\Helper\Bienvenue;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class BienvenueFactory implements FactoryInterface
{

    /**
     *
     * @param \Zend\View\HelperPluginManager $pluginManager
     *
     * {@inheritdoc}
     */
    public function createService(ServiceLocatorInterface $pluginManager)
    {
        /* @var $authService \SbmAuthentification\Authentication\AuthenticationService */
        $authService = $pluginManager->getServiceLocator()
            ->get('SbmAuthentification\Authentication')
            ->by();

        $viewHelper = new Bienvenue();
        $viewHelper->setAuthService($authService);

        return $viewHelper;
    }
} 