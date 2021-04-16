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
 * @date 1 avr. 2021
 * @version 2021-2.6.1
 */
namespace SbmFront\View\Helper;

use SbmBase\Model\StdLib;
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
        $sm = $pluginManager->getServiceLocator();
        $authService = $sm->get('SbmAuthentification\Authentication')->by();
        $viewHelper = new Bienvenue();
        $viewHelper->setAuthService($authService);
        if ($authService->hasIdentity()) {
            $categorieId = $authService->getIdentity()['categorieId'];
            $config_application = $sm->get('config');
            $categorie = StdLib::getParamR([
                'acl',
                'roleId',
                $categorieId
            ], $config_application, 'guest');
            $home_route = StdLib::getParamR([
                'acl',
                'redirectTo',
                $categorie
            ], $config_application, '/');
            $viewHelper->setHomeRoute($home_route);
        }
        return $viewHelper;
    }
}