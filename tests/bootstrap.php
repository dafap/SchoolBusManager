<?php
/**
 * Test unitaire
 *
 *
 * @project zf2-tutorial
 * @package tests
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 8 nov. 2012
 * @version 2012-
 */
chdir(dirname(__DIR__));

include __DIR__ . '/../init_autoloader.php';
Zend\Mvc\Application::init(include 'config/application.config.php');