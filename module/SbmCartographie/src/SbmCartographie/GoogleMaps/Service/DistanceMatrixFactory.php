<?php
/**
 * Factory permettant d'enregistrer une instance de DistanceMatrix dans le service manager
 *
 * Incompatible avec ZF3 - revoir createService() en remplaçant par __invoke()
 * 
 * @project sbm
 * @package SbmCartographie/GoogleMaps/Service
 * @filesource DistanceMatrixFactory.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 5 mai 2018
 * @version 2018-2.4.1
 */
namespace SbmCartographie\GoogleMaps\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use SbmCartographie\ConvertSystemGeodetic\Projection\ProjectionInterface;
use SbmCartographie\GoogleMaps\DistanceMatrix;
use SbmBase\Model\StdLib;

class DistanceMatrixFactory implements FactoryInterface
{

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $cartographie = $serviceLocator->get('cartographie');
        $projection = str_replace('ProjectionInterface', 
            StdLib::getParam('system', $cartographie), ProjectionInterface::class);
        $nzone = StdLib::getParam('nzone', $cartographie, 0);
        $google_api_distanceMatrix = StdLib::getParam('distancematrix', 
            $serviceLocator->get('google_api_serveur'));
        return new DistanceMatrix(new $projection($nzone), $google_api_distanceMatrix);
    }
}