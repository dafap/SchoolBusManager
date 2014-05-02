<?php
/**
 * Configuration des variables PHP par ce fichier
 * (Utilise le module atukai/at-php-settings qui est indiqué dans le composer.phar du projet)
 *
 *
 * @project sbm
 * @package config/autoload
 * @filesource at-php-settings.global.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 14 avr. 2014
 * @version 2014-1
 */
if (getenv('APPLICATION_ENV') == 'development') {
    $config = array(
        'php_settings' => array(
            'display_startup_errors' => true,
            'display_errors' => true,
            'error_log' => realpath(__DIR__ . '/../../data') . '/logs/sbm_error.log',
            'error_reporting' => E_ALL,
            'max_execution_time' => 30,
            'date.timezone' => 'Europe/Paris',
            
            'controllers' => array(
                'SbmGestion\Controller\Index' => array(
                    'memory_limit' => '32M'
                )
            ),
            
            'routes' => array(
                'home' => array(
                    'memory_limit' => '32M',
                    'max_execution_time' => '60'
                )
            )
        )
    );
} else { // config du PHP en production
    $config = array(
        'php_settings' => array(
            'display_startup_errors' => false,
            'display_errors' => false,
            'error_log' => realpath(__DIR__ . '/../../data') . '/logs/sbm_error.log',
            'error_reporting' => E_ALL,
            'max_execution_time' => 30,
            'date.timezone' => 'Europe/Paris',
            
            'controllers' => array(
                'SbmGestion\Controller\Index' => array(
                    'memory_limit' => '32M'
                )
            ),
            
            'routes' => array(
                'home' => array(
                    'memory_limit' => '32M',
                    'max_execution_time' => '60'
                )
            )
        )
    );
}

return $config;
;