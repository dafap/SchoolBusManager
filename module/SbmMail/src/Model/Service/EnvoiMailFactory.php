<?php
/**
 * Injection dans EnvoiMail du tableau de configuration
 *
 * Compatibilité ZF3
 *
 * @project sbm
 * @package SbmMail/src/Model/Service
 * @filesource EnvoiMailFactory.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 7 août 2021
 * @version 2021-2.6.3
 */
namespace SbmMail\Model\Service;

use SbmMail\Model\EnvoiMail;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class EnvoiMailFactory implements FactoryInterface
{

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new EnvoiMail($serviceLocator->get('SbmMail\Config'),
            $serviceLocator->get('SbmMail\Dkim'));
    }
}