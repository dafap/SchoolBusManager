<?php
/**
 * Injection dans EnvoiMail du tableau de configuration
 *
 * Compatibilité ZF3
 * 
 * @project sbm
 * @package package_name
 * @filesource EnvoiMailFactory.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 14 avr. 2016
 * @version 2016-2
 */
namespace DafapMail\Model\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use DafapMail\Model\EnvoiMail;

class EnvoiMailFactory implements FactoryInterface
{

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new EnvoiMail($serviceLocator->get('config')['sbm']['mail']);
    }
}