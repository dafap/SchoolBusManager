<?php
/**
 * Structure de la table des `rpi-communes`
 *
 * @project sbm
 * @package SbmInstallation/db_design
 * @filesource table.rpi-communes.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 9 avr. 2018
 * @version 2018-2.4.0
 */
return [
    'name' => 'rpi-communes',
    'type' => 'table',
    'drop' => true,
    'edit_entity' => true,
    'add_data' => true,
    'structure' => [
        'fields' => [
            'rpiId' => 'int(11) NOT NULL',
            'communeId' => 'char(8) NOT NULL'
        ],
        'primary_key' => [
            'rpiId',
            'communeId'
        ],
        'foreign key' => [
            [
                'key' => 'rpiId',
                'references' => [
                    'table' => 'rpi',
                    'fields' => [
                        'rpiId'
                    ],
                    'on' => [
                        'update' => 'CASCADE',
                        'delete' => 'CASCADE'
                    ]
                ]
            ],
            [
                'key' => 'communeId',
                'references' => [
                    'table' => 'communes',
                    'fields' => [
                        'communeId'
                    ],
                    'on' => [
                        'update' => 'CASCADE',
                        'delete' => 'CASCADE'
                    ]
                ]
            ]
        ],
        'engine' => 'InnoDb',
        'charset' => 'utf8',
        'collate' => 'utf8_unicode_ci'
    ],
    'data' => __DIR__ . '/data/data.rpi-communes.php'
];