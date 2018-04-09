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
 * @date 5 avr. 2018
 * @version 2018-2.4.0
 */
namespace SbmMail\Model\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use SbmMail\Model\EnvoiMail;

class EnvoiMailFactory implements FactoryInterface
{

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new EnvoiMail($serviceLocator->get('SbmMail\Config'));
    }
}