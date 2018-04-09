<?php
/**
 * Génère un form manager à partir de la configuration 'form_manager' des modules
 *
 * Compatibilité ZF3
 * 
 * @project sbm
 * @package SbmCommun\Model\Service
 * @filesource FormManager.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 4 avr. 2018
 * @version 2018-2.4.0
 */
namespace SbmCommun\Model\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\Config;

class FormManager implements FactoryInterface
{

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $form_manager = new ServiceManager(
            new Config($serviceLocator->get('config')['form_manager']));
        $form_manager->setService('Sbm\DbManager', $serviceLocator->get('Sbm\DbManager'));
        return $form_manager;
    }
}