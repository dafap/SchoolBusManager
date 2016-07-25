<?php
/**
 * Service rendant la configuration de mail dans SBM
 *
 * @project sbm
 * @package DafapMail/Model/Service
 * @filesource ConfigServiceFactory.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 26 juil. 2016
 * @version 2016-2.1.10
 */

namespace DafapMail\Model\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Factory responsable de retrouver la configuration mail de Sbm.
 *
 */
class ConfigServiceFactory implements FactoryInterface
{
    /**
     * (non-PHPdoc)
     * @see \Zend\ServiceManager\FactoryInterface::createService()
     * 
     * @return array
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('Config');
        return $config['sbm']['mail'];
    }
}