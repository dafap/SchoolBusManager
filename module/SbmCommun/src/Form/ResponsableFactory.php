<?php
/**
 * Injecte les options nécessaires dans le formulaire Responsable
 *
 * @project sbm
 * @package module/SbmCommun/src/Form
 * @filesource ResponsableFactory.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 14 juin 2020
 * @version 2020-2.5.7
 */
namespace SbmCommun\Form;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ResponsableFactory implements FactoryInterface
{

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new Responsable($serviceLocator->get('Sbm\DbManager'),
            [
                'verrouille' => false,
                'hassbmservicesms' => $serviceLocator->has('sbmservicesms')
            ]);
    }
}