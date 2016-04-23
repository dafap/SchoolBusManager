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
 * @date 6 avr. 2016
 * @version 2016-2
 */
namespace SbmFront\Model\Responsable\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use SbmFront\Model\Responsable\Responsable;

class ResponsableFactory implements FactoryInterface
{

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        //die(var_dump($serviceLocator));
        $authenticate_by = $serviceLocator->get('Dafap\Authenticate')->by();
        $vue_responsable = $serviceLocator->get('Sbm\DbManager')
            ->get('Sbm\Db\Vue\Responsables');
        
        return new Responsable($authenticate_by, $vue_responsable);
    }
}