<?php
/**
 * Structure de la table des `rpi-etablissements`
 *
 * @project sbm
 * @package SbmInstallation/db_design
 * @filesource table.simulation-etablissements.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 23 août 2018
 * @version 2018-2.4.3
 */
return [
    'name' => 'simulation-etablissements',
    'type' => 'table',
    'drop' => true,
    'edit_entity' => true,
    'add_data' => true,
    'structure' => [
        'fields' => [
            'origineId' => 'char(8) NOT NULL',
            'suivantId' => 'char(8) NOT NULL'
        ],
        'primary_key' => [
            'origineId'
        ],
        'foreign key' => [
            [
                'key' => 'origineId',
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
            ],
            [
                'key' => 'suivantId',
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
    'data' => __DIR__ . '/data/data.simulation-etablissements.php'
];