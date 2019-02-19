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
 * @date 28 sept. 2018
 * @version 2019-2.5.0
 */
namespace SbmCartographie\Model\Service;

use SbmBase\Model\StdLib;
use SbmCartographie\ConvertSystemGeodetic\Projection\ProjectionInterface;
use SbmCartographie\Model\Projection;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ProjectionFactory implements FactoryInterface
{

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $cartographie = $serviceLocator->get('cartographie');
        $projection = str_replace('ProjectionInterface',
            StdLib::getParam('system', $cartographie), ProjectionInterface::class);
        $nzone = StdLib::getParam('nzone', $cartographie, 0);
        $config_cartes = $serviceLocator->get('cartes');

        return new Projection(new $projection($nzone), $config_cartes);
    }
}