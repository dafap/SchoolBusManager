<?php
/**
 * Test unitaire
 *
 * Se référer à l'article de Samsonasik :
 * @link https://samsonasik.wordpress.com/2013/11/19/zendframework-2-centralize-phpunit-test/
 *
 * @project sbm
 * @package tests
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 31 juill. 2016
 * @version 2016-2.1.10
 */

use ModulesTests\ServiceManagerGrabber;
use Zend\Mvc\Application;

error_reporting(E_ALL | E_STRICT);

$cwd = __DIR__;
chdir(dirname(__DIR__));

// Assume we use composer
$loader = require_once  './vendor/autoload.php';
$loader->add("ModulesTests\\", $cwd);
$loader->register();
$config = require_once './config/application.config.php';

Application::init($config);
ServiceManagerGrabber::setServiceConfig($config);
ob_start();