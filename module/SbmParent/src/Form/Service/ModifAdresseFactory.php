<?php
/**
 * Injecte les options nécessaires dans le formulaire ModifAdresse
 *
 * @project sbm
 * @package SbmParent/src/Form/Service
 * @filesource ModifAdresseFactory.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 4 mars 2019
 * @version 2019-2.5.0
 */
namespace SbmParent\Form\Service;

use SbmParent\Form\ModifAdresse;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ModifAdresseFactory implements FactoryInterface
{

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new ModifAdresse('responsable',
            [
                'hassbmservicesms' => $serviceLocator->has('sbmservicesms')
            ]);
    }
}
