<?php
/**
 * Permet d'enregistrer le formulaire CreerCompte dans le service_manager
 *
 * Compatibilité ZF3
 * 
 * @project sbm
 * @package SbmFront/Form/Service
 * @filesource CreerCompteFactory.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 18 oct. 2016
 * @version 2016-2.2.1
 */
namespace SbmFront\Form\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use SbmFront\Form\CreerCompte;

class CreerCompteFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $db_manager = $serviceLocator->get('Sbm\DbManager');
        return new CreerCompte($db_manager);
    }
}