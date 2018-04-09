<?php
/**
 * Permet d'enregistrer l'objet SbmCartographie\GoogleMaps\Geocoder dans le service_manager
 *
 * Compatibilité ZF3
 * 
 * @project sbm
 * @package SbmCartographie/GoogleMaps/Service
 * @filesource GeocoderFactory.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 3 avr. 2018
 * @version 2018-2.4.0
 */
namespace SbmCartographie\GoogleMaps\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use SbmCartographie\ConvertSystemGeodetic\Projection\ProjectionInterface;
use SbmCartographie\GoogleMaps\Geocoder;
use SbmBase\Model\StdLib;

class GeocoderFactory implements FactoryInterface
{

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $cartographie = $serviceLocator->get('cartographie');
        $projection = str_replace('ProjectionInterface', 
            StdLib::getParam('system', $cartographie), ProjectionInterface::class);
        $nzone = StdLib::getParam('nzone', $cartographie, 0);
        $google_api = $serviceLocator->get('google_api');
        
        return new Geocoder(new $projection($nzone), $google_api);
    }
}