<?php
/**
 * Permet d'enregistrer le formulaire MdpDemande dans le service_manager
 *
 * Compatibilité ZF3
 * 
 * @project sbm
 * @package SbmFront/Form/Service
 * @filesource MdpDemandeFactory.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 18 oct. 2016
 * @version 2016-2.2.1
 */
namespace SbmFront\Form\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use SbmFront\Form\MdpDemande;

class MdpDemandeFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $db_manager = $serviceLocator->get('Sbm\DbManager');
        $canonic_name = $db_manager->getCanonicName('users', 'table');
        $db_adapter = $db_manager->getDbAdapter();
        return new MdpDemande($canonic_name, $db_adapter);
    }
}