<?php
/**
 * Classe Bootstap permettant d'initialiser phpUnit pour le projet
 * en créant un serviceManager initialisé par la config de l'application
 * et des modules.
 * 
 * Usage dans le tests\bootstrap.php des tests unitaires :
 *   use ModulesTests\Bootstrap;
 *   error_reporting(E_ALL | E_STRICT);
 *   include_once __DIR__ . DIRECTORY_SEPARATOR . 'ModulesTests/Bootstrap.php';
 *   Bootstrap::chroot();
 *   Bootstrap::init();
 *   ob_start();
 * 
 * Pour que l'initialisation soit correcte, chroot() doit être exécutée avant init().
 * L'instruction ob_start() est nécessaire lorsqu'on utilise des sessions ou des cookies.
 * 
 * @project sbm
 * @package ModulesTests
 * @filesource Bootstrap.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 29 août 2016
 * @version 2016-2.2.0
 */
namespace ModulesTests;

use Zend\Loader\AutoloaderFactory;
use Zend\Mvc\Service\ServiceManagerConfig;
use Zend\ServiceManager\ServiceManager;
use RuntimeException;

class Bootstrap
{

    protected static $serviceManager;

    protected static $vendorPath;

    public static function init()
    {
        $zf2ModulePaths[] = static::findParentPath('module');
        if ((static::$vendorPath = static::findParentPath('vendor'))) {
            $zf2ModulePaths[] = static::$vendorPath;
        }
        
        static::initAutoloader();
        
        // use ModuleManager to load this module and it's dependencies
        $application_config = include static::findParentPath('config') . DIRECTORY_SEPARATOR . 'application.config.php';
        $smConfig = isset($application_config['service_manager']) ? $application_config['service_manager'] : [];
        $config = [
            'module_listener_options' => [
                'module_paths' => $zf2ModulePaths,
                'config_glob_paths' => [
                    'config/autoload/{,*.}{global,local}.php'
                ]
            ],
            'modules' => $application_config['modules']
        ];
        
        $serviceManager = new ServiceManager(new ServiceManagerConfig($smConfig));
        $serviceManager->setService('ApplicationConfig', $application_config);
        // Load modules
        $serviceManager->get('ModuleManager')->loadModules();
        static::$serviceManager = $serviceManager;
    }

    public static function chroot()
    {
        $rootPath = dirname(static::findParentPath('module'));
        chdir($rootPath);
    }

    public static function getServiceManager()
    {
        return static::$serviceManager;
    }

    protected static function initAutoloader()
    {
        if (! static::$vendorPath) {
            static::$vendorPath = static::findParentPath('vendor');
        }
        
        $inc = static::$vendorPath . DIRECTORY_SEPARATOR . 'autoload.php';
        if (file_exists($inc)) {
            include $inc;
        }
        
        if (! class_exists('Zend\Loader\AutoloaderFactory')) {
            throw new RuntimeException('Unable to load ZF2. Run `php composer.phar install`');
        }
        
        AutoloaderFactory::factory([
            'Zend\Loader\StandardAutoloader' => [
                'autoregister_zf' => true,
                'namespaces' => [
                    __NAMESPACE__ => __DIR__
                ]
            ]
        ]);
    }

    protected static function findParentPath($path)
    {
        $dir = __DIR__;
        $previousDir = '.';
        while (! is_dir($dir . DIRECTORY_SEPARATOR . $path)) {
            $dir = dirname($dir);
            if ($previousDir === $dir) {
                return false;
            }
            $previousDir = $dir;
        }
        return $dir . DIRECTORY_SEPARATOR . $path;
    }
}