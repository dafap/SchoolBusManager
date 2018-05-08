<?php
/**
 * Injection des objets dans CarteController
 *
 * Compatibilité ZF3
 * 
 * @project sbm
 * @package SbmCartographie/Controller/Service
 * @filesource CarteControllerFactory.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 5 mai 2018
 * @version 2018-2.4.1
 */
namespace SbmCartographie\Controller\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceLocatorInterface;
use SbmCartographie\ConvertSystemGeodetic\Projection\ProjectionInterface;
use SbmCartographie\Controller\CarteController;
use SbmBase\Model\StdLib;

class CarteControllerFactory implements FactoryInterface
{

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $sm = $serviceLocator->getServiceLocator();
        $cm = $sm->get('Sbm\CartographieManager');
        $cartographie = $cm->get('cartographie');
        $projection = str_replace('ProjectionInterface', 
            StdLib::getParam('system', $cartographie), ProjectionInterface::class);
        $nzone = StdLib::getParam('nzone', $cartographie, 0);
        $config_cartes = $cm->get('cartes');
        $google_api = $cm->get('google_api_browser');
        
        return new CarteController(
            [
                'db_manager' => $sm->get('Sbm\DbManager'),
                'projection' => new $projection($nzone),
                'config_cartes' => $config_cartes,
                'url_api' => $google_api['js'],
                'user' => $sm->get('SbmAuthentification\Authentication')
                    ->by()
                    ->getIdentity()
            ]);
    }
}