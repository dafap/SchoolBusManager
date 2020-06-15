<?php
/**
 * Permet d'enregistrer l'objet SbmFront\Responsable dans le service_manager
 *
 * Compatibilité ZF3
 *
 * @project sbm
 * @package SbmFront/Model/Service
 * @filesource ResponsableFactory.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 15 juin 2020
 * @version 2020-2.5.7
 */
namespace SbmFront\Model\Responsable\Service;

use SbmFront\Model\Responsable\Responsable;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ResponsableFactory implements FactoryInterface
{

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        // die(var_dump($serviceLocator));
        $authenticate_by = $serviceLocator->get('SbmAuthentification\Authentication')->by();

        return new Responsable($authenticate_by, $serviceLocator->get('Sbm\DbManager'));
    }
}