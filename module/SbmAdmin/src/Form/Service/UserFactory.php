<?php
/**
 * Permet d'enregistrer le formulaire User dans le form_manager
 *
 * Compatibilité ZF3
 * Usage :
 * $form = $sm->get('SbmAdmin\Form\User');
 *
 * @project sbm
 * @package SbmAdmin/Form/Service
 * @filesource UserFactory.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 3 avr. 2018
 * @version 2018-2.4.0
 */
namespace SbmAdmin\Form\Service;

use SbmAdmin\Form\User;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class UserFactory implements FactoryInterface
{

    /**
     * Crée le service en initialisant le db_manager
     * Renvoie le formulaire User
     *
     * (non-PHPdoc)
     *
     * @see \Zend\ServiceManager\FactoryInterface::createService()
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $db_manager = $serviceLocator->get('Sbm\DbManager');
        $canonic_name = $db_manager->getCanonicName('users', 'table');
        $db_adapter = $db_manager->getDbAdapter();
        return new User($canonic_name, $db_adapter);
    }
} 