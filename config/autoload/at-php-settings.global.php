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
 * @date 7 avr. 2018
 * @version 2018-2.4.0
 */
if (getenv('APPLICATION_ENV') == 'development') {
    $config = [
        'php_settings' => [
            'display_startup_errors' => true,
            'display_errors' => true,
            'error_log' => realpath(__DIR__ . '/../../data') . '/logs/sbm_error.log',
            'error_reporting' => E_ALL,
            'max_execution_time' => 60,
            'date.timezone' => 'Europe/Paris',
            
            'controllers' => [
                'SbmPaiement\Controller\Index' => [
                    'date.timezone' => 'UTC'
                ],
                'SbmGestion\Controller\Index' => [
                    'memory_limit' => '32M'
                ]
            ],
            
            'routes' => [
                'home' => [
                    'memory_limit' => '128M',
                    'max_execution_time' => '120'
                ]
            ]
        ]
    ];
} else { // config du PHP en production
    $config = [
        'php_settings' => [
            'display_startup_errors' => false,
            'display_errors' => false,
            'error_log' => realpath(__DIR__ . '/../../data') . '/logs/sbm_error.log',
            'error_reporting' => E_ALL,
            'max_execution_time' => 30,
            'date.timezone' => 'Europe/Paris',
            
            'controllers' => [
                'SbmGestion\Controller\Index' => [
                    'memory_limit' => '32M'
                ]
            ],
            
            'routes' => [
                'home' => [
                    'memory_limit' => '32M',
                    'max_execution_time' => '60'
                ]
            ]
        ]
    ];
}

return $config;
;