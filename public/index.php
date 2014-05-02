<?php
if (getenv('APPLICATION_ENV') == 'development') {
    // Cette constante est nécessaire pour utiliser ZendDeveloperTools en version PHP < 5.4
    if (PHP_VERSION_ID < 50400) {
        define('REQUEST_MICROTIME', microtime(true));
    }
} 

/**
 * Cela rend notre vie plus facile lorsqu'il s'agit de chemins.
 *
 * Tout est relatif à la racine de l'application maintenant.
 */
chdir(dirname(__DIR__));

// Decline static file requests back to the PHP built-in webserver
if (php_sapi_name() === 'cli-server' && is_file(__DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))) {
    return false;
}

// Setup autoloading
require 'init_autoloader.php';

// Run the application!
Zend\Mvc\Application::init(require 'config/application.config.php')->run();
