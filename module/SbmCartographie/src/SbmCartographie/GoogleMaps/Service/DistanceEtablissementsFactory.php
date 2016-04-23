<?php
/**
 * Permet d'enregistrer l'objet SbmCartographie\GoogleMaps\DistanceEtablissements dans le service_manager
 *
 * Compatibilité ZF3
 * 
 * @project sbm
 * @package SbmCartographie/GoogleMaps/Service
 * @filesource DistanceEtablissementsFactory.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 9 avr. 2016
 * @version 2016-2
 */
namespace SbmCartographie\GoogleMaps\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use SbmCartographie\ConvertSystemGeodetic\Projection\ProjectionInterface;
use SbmCartographie\GoogleMaps\DistanceEtablissements;
use SbmCommun\Model\StdLib;

class DistanceEtablissementsFactory implements FactoryInterface
{

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $db_manager = $serviceLocator->get('Sbm\DbManager');
        $cartographie = $serviceLocator->get('cartographie');
        $projection = str_replace('ProjectionInterface', StdLib::getParam('system', $cartographie), ProjectionInterface::class);
        $nzone = StdLib::getParam('nzone', $cartographie, 0);
        $google_api = $serviceLocator->get('google_api');
        
        return new DistanceEtablissements($db_manager, new $projection($nzone), $google_api);
    }
}