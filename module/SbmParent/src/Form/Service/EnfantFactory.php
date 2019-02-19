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
 * @date 4 oct. 2018
 * @version 2019-2.5.0
 */
namespace SbmParent\Form\Service;

use SbmParent\Form\Enfant;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class EnfantFactory implements FactoryInterface
{

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $db_manager = $serviceLocator->get('Sbm\DbManager');
        return new Enfant($db_manager);
    }
}