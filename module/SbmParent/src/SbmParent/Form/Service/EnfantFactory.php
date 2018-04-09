<?php
/**
 * Permet d'enregistrer le formulaire Enfant dans le service_manager
 *
 * Compatibilité ZF3
 * 
 * @project sbm
 * @package SbmParent/Form/Service
 * @filesource EnfantFactory.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 5 avr. 2018
 * @version 2018-2.4.0
 */
namespace SbmParent\Form\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use SbmParent\Form\Enfant;

class EnfantFactory implements FactoryInterface
{

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $db_manager = $serviceLocator->get('Sbm\DbManager');
        return new Enfant($db_manager);
    }
}