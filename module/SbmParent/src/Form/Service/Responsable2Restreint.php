<?php
/**
 * Injecte les options nécessaires dans le formulaire Responsable2
 *
 * @project sbm
 * @package SbmParent/src/Form/Service
 * @filesource Responsable2Restreint.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 4 mars 2019
 * @version 2019-2.5.0
 */
namespace SbmParent\Form\Service;

use SbmParent\Form\Responsable2;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class Responsable2Restreint implements FactoryInterface
{

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new Responsable2('responsable2',
            [
                'hassbmservicesms' => $serviceLocator->has('sbmservicesms'),
                'complet' => false
            ]);
    }
}