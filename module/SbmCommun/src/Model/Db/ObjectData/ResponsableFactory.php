<?php
/**
 * Injection de 'hassbmcleversms' dans la classe ObjectData\Responsable
 *
 * @project sbm
 * @package module/SbmCommun/src/Model/Db/ObjectData
 * @filesource ResponsableFactory.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 3 mars 2019
 * @version 2019-2.4.5
 */
namespace SbmCommun\Model\Db\ObjectData;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ResponsableFactory implements FactoryInterface
{

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        if (! $serviceLocator instanceof \SbmCommun\Model\Db\Service\DbManager) {
            throw new Exception\InvalidArgumentException(
                'On a reçu un mauvais service manager.');
        }
        return new Responsable($serviceLocator->has('sbmservicesms'));
    }
}