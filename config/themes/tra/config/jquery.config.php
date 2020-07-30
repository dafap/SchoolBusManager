<?php
/**
 * Fichier de configuration de JQuery
 *
 * Liste des bibliothèques à charger
 *
 * @project sbm
 * @package config/themes/arlysere/config
 * @filesource jquery.config.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 30 juil. 2020
 * @version 2020-2.6.0
 */
return [
    'jquery' => [
        'mode' => 'prepend',
        'js' => [
            0 => 'js/jquery-1.12.4/jquery.min.js'
        ]
    ],
    'jquery-ui' => [
        'mode' => 'append',
        'css' => [
            0 => 'js/jquery-ui-1.12.1.custom/jquery-ui.min.css'
        ],
        'js' => [
            0 => 'js/jquery-ui-1.12.1.custom/jquery-ui.min.js'
        ]
    ],
    'jquery.formatCurrency' => [
        'mode' => 'append',
        'js' => [
            0 => 'js/jquery.formatCurrency-1.4.0.i18n/jquery.formatCurrency-1.4.0.min.js'
        ]
    ],
    'jquery-cookie' => [
        'mode' => 'append',
        'js' => [
            0 => 'js/jquery-cookie.master/jquery.cookie.js'
        ]
    ],
    'jquery-confirm' => [
        'mode' => 'append',
        'css' => [
            0 => 'js/jquery-confirm-v3.3.4/dist/jquery-confirm.min.css'
        ],
        'js' => [
            0 => 'js/jquery-confirm-v3.3.4/dist/jquery-confirm.min.js'
        ]
    ]
];