<?php
/**
 * Permet d'enregistrer le formulaire Login dans le service_manager
 *
 * Compatibilité ZF3
 * 
 * @project sbm
 * @package SbmFront/Form/Service
 * @filesource LoginFactory.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 7 avr. 2016
 * @version 2016-2
 */
namespace SbmFront\Form\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use SbmFront\Form\Login;

class LoginFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $db = $serviceLocator->get('Sbm\DbManager');
        $canonic_name = $db->getCanonicName('users', 'table');
        $db_adapter = $db->getDbAdapter();
        return new Login($canonic_name, $db_adapter);
    }
}