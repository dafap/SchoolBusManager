<?php
/**
 * School Bus Manager
 *
 * Configuration des modules de l'application et des autoloads
 *
 * @project sbm
 * @package config
 * @filesource application.config.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 25 mai 2020
 * @version 2020-2.6.0
 */
return [
    'modules' => [
        'SbmBase',
        'SbmAuthentification',
        'AtPhpSettings',
        'SbmFront',
        'SbmPortail',
        'SbmMail',
        'SbmGestion',
        'SbmAdmin',
        'SbmParent',
        'SbmPaiement',
        'SbmCommun',
        'SbmInstallation',
        'SbmPdf',
        'SbmCartographie',
        'SbmMailChimp',
        'SbmAjax',
        'SbmEsendex'
    ],
    'module_listener_options' => [
        'module_paths' => [
            './module',
            './vendor'
        ],
        'config_glob_paths' => [
            'config/autoload/{,*.}{global,local}.php'
        ]
    ]
];
