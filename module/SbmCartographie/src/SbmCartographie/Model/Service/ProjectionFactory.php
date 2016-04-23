<?php
/**
 * Permet d'enregistrer l'objet SbmCartographie\Model\Projection dans le service_manager
 *
 * Compatibilité ZF3
 * 
 * @project sbm
 * @package SbmCartographie/Model/Service
 * @filesource ProjectionFactory.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 7 avr. 2016
 * @version 2016-2
 */
namespace SbmCartographie\Model\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use SbmCartographie\ConvertSystemGeodetic\Projection\ProjectionInterface;
use SbmCartographie\Model\Projection;
use SbmCommun\Model\StdLib;

class ProjectionFactory implements FactoryInterface
{

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $cartographie = $serviceLocator->get('cartographie');
        $projection = str_replace('ProjectionInterface', StdLib::getParam('system', $cartographie), ProjectionInterface::class);
        $nzone = StdLib::getParam('nzone', $cartographie, 0);
        $config_cartes = $serviceLocator->get('cartes');
        
        return new Projection(new $projection($nzone), $config_cartes);
    }
}