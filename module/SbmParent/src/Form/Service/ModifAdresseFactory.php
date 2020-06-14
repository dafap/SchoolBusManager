<?php
/**
 * Injecte les options nécessaires dans le formulaire ModifAdresse
 *
 * @project sbm
 * @package SbmParent/src/Form/Service
 * @filesource ModifAdresseFactory.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 14 juin 2020
 * @version 2020-2.5.7
 */
namespace SbmParent\Form\Service;

use SbmParent\Form\ModifAdresse;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ModifAdresseFactory implements FactoryInterface
{

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new ModifAdresse($serviceLocator->get('Sbm\DbManager'),
            [
                'hassbmservicesms' => $serviceLocator->has('sbmservicesms')
            ]);
    }
}
