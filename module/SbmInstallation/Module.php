<?php
/**
 * Module SbmInstallation pour créer les tables de la base de données, les requêtes enregistrées
 * - création des tables
 * - création des requêtes enregistrées
 * - 
 *
 * @project sbm
 * @package module/SbmInstallation
 * @filesource Module.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 22 janv. 2014
 * @version 2014-1
 */
namespace SbmInstallation;

use Zend\Stdlib\Glob;
use Zend\Config\Factory as ConfigFactory;
use Zend\Mvc\MvcEvent;

class Module
{
    public function onBootstrap(MvcEvent $e)
    {
        set_time_limit(600);   
        ini_set('memory_limit', '-1');
    }
    public function getConfig()
    {
        $config = include __DIR__ . '/config/module.config.php';
        $pattern = __DIR__ . '/config/db_design/*.php';
        foreach (Glob::glob($pattern) as $filename) {
            $value = basename($filename, '.php');
            $config['db_design'][$value] = ConfigFactory::fromFile($filename);
        }
        return $config;
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }
}
