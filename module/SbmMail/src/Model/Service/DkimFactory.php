<?php
/**
 * Injection dans Dkim du tableau de configuration
 *
 * @project sbm
 * @package
 * @filesource DkimFactory.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 5 juil. 2021
 * @version 2021-2.6.3
 */
namespace SbmMail\Model\Service;

use SbmMail\Model\Dkim;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class DkimFactory implements FactoryInterface
{

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new Dkim($serviceLocator->get('SbmMail\Config')['dkim']);
    }
}