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
 * @date 11 fév. 2019
 * @version 2019-2.5.0
 */
namespace SbmMail\Model\Service;

use SbmMail\Model\EnvoiMail;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class EnvoiMailFactory implements FactoryInterface
{

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new EnvoiMail($serviceLocator->get('SbmMail\Config'));
    }
}