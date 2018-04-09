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
 * @date 29 août 2016
 * @version 2016-2.2.0
 */
use ModulesTests\Bootstrap;

error_reporting(E_ALL | E_STRICT);

include_once __DIR__ . DIRECTORY_SEPARATOR . 'ModulesTests/Bootstrap.php';
Bootstrap::chroot();
Bootstrap::init();
ob_start();