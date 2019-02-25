<?php
/**
 * Entrée principale de l'application School Bus Manager
 *
 * @project sbm
 * @package public
 * @filesource index.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 20 fév. 2019
 * @version 2019-2.5.0
 */
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
if (php_sapi_name() === 'cli-server' &&
    is_file(__DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))) {
    return false;
}

// Setup autoloading
require 'init_autoloader.php';

// Run the application!
try {
    Zend\Mvc\Application::init(require 'config/application.config.php')->run();
} catch (Exception $e) {
    $ep = $e->getPrevious();
    if ($e instanceof SbmCommun\Model\Db\Exception\RuntimeException ||
        $ep instanceof SbmCommun\Model\Db\Exception\RuntimeException) {
        echo 'Installation de la base de données à faire.';
    } else {
        echo '<pre>';
        echo get_class($e) . "\n" . @get_class($ep) . "\n";
        echo $e->getMessage();
        echo "\n";
        echo $e->getTraceAsString();
        echo '</pre>';
        if ($ep) {
            echo '<pre>';
            echo "\n";
            echo get_class($ep) . "\n";
            echo $ep->getMessage();
            echo "\n";
            echo $ep->getTraceAsString();
            echo '</pre>';
        }
    }
}