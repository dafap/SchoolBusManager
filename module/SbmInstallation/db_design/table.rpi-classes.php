<?php
/**
 * Structure de la table des `rpi-classes`
 *
 * @project sbm
 * @package SbmInstallation/db_design
 * @filesource table.rpi-classes.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 9 avr. 2018
 * @version 2018-2.4.0
 */
return [
    'name' => 'rpi-classes',
    'type' => 'table',
    'drop' => true,
    'edit_entity' => true,
    'add_data' => true,
    'structure' => [
        'fields' => [
            'classeId' => 'int(11) NOT NULL',
            'etablissementId' => 'char(8) NOT NULL'
        ],
        'primary_key' => [
            'classeId',
            'etablissementId'
        ],
        'foreign key' => [
            [
                'key' => 'classeId',
                'references' => [
                    'table' => 'classes',
                    'fields' => [
                        'classeId'
                    ],
                    'on' => [
                        'update' => 'CASCADE',
                        'delete' => 'CASCADE'
                    ]
                ]
            ],
            [
                'key' => 'etablissementId',
                'references' => [
                    'table' => 'etablissements',
                    'fields' => [
                        'etablissementId'
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
    'data' => __DIR__ . '/data/data.rpi-classes.php'
];