<?php
/**
 * Initialise l'application à partir de la configuration des modules
 *
 * inspired from https://github.com/doctrine/DoctrineModule/blob/master/tests/DoctrineModuleTest/ServiceManagerTestCase.php
 * thanks to Marco Pivetta and Abdul Malik Ikhsan
 * 
 * @project sbm
 * @package tests/ModulesTests
 * @filesource ServiceManagerGrabber.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 31 juil. 2016
 * @version 2016-2.1.10
 */

//
// 

namespace ModulesTests;

use Zend\ServiceManager\ServiceManager;
use Zend\Mvc\Service\ServiceManagerConfig;

class ServiceManagerGrabber
{
    protected static $serviceConfig = null;
     
    public static function setServiceConfig($config)
    {
        static::$serviceConfig = $config;
    }
     
    public function getServiceManager()
    {
        $configuration = static::$serviceConfig ? : require_once './config/application.config.php';
         
        $smConfig = isset($configuration['service_manager']) ? $configuration['service_manager'] : array();
        $serviceManager = new ServiceManager(new ServiceManagerConfig($smConfig));
        $serviceManager->setService('ApplicationConfig', $configuration);

        $serviceManager->get('ModuleManager')->loadModules();
         
        return $serviceManager;
    }
} 