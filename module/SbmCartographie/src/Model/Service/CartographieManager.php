<?php
/**
 * Génère un form manager donnat accès aux services du module SbmCartographie
 *
 * Compatibilité ZF3
 *
 * @project sbm
 * @package SbmCartographie/Model/Service
 * @filesource CartographieManager.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 18 fév. 2019
 * @version 2019-2.5.0
 */
namespace SbmCartographie\Model\Service;

use Zend\ServiceManager\Config;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceManager;

class CartographieManager implements FactoryInterface
{

    private $cartographie_manager;

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $this->cartographie_manager = new ServiceManager(
            new Config($serviceLocator->get('config')['cartographie_manager']));
        $this->cartographie_manager->setService('Sbm\DbManager',
            $serviceLocator->get('Sbm\DbManager'));
        return $this->cartographie_manager;
    }
}