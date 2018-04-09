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
 * @date 4 avr. 2018
 * @version 2018-2.4.0
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