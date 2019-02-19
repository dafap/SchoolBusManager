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
 * @date 28 sept. 2018
 * @version 2019-2.5.0
 */
namespace SbmCartographie\Controller\Service;

use SbmBase\Model\StdLib;
use SbmCartographie\Controller\CarteController;
use SbmCartographie\ConvertSystemGeodetic\Projection\ProjectionInterface;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

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