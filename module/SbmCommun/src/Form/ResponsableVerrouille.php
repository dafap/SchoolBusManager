<?php
/**
 * Injecte les options nécessaires dans le formulaire Responsable
 *
 * @project sbm
 * @package module/SbmCommun/src/Form
 * @filesource ResponsableVerrouille.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 4 mars 2019
 * @version 2019-2.5.0
 */
namespace SbmCommun\Form;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ResponsableVerrouille implements FactoryInterface
{

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new Responsable('responsable',
            [
                'verrouille' => true,
                'hassbmservicesms' => $serviceLocator->has('sbmservicesms')
            ]);
    }
}