<?php
/**
 * Structure de la table `usersTransporteurs`
 *
 * @project sbm
 * @package SbmInstallation/db_design
 * @filesource table.users-transporteurs.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 7 avr. 2018
 * @version 2018-2.4.0
 */
return [
    'name' => 'users-transporteurs',
    'type' => 'table',
    'drop' => false,
    'edit_entity' => false,
    'add_data' => false,
    'structure' => [
        'fields' => [
            'userId' => 'int(11) NOT NULL',
            'transporteurId' => 'int(11) NOT NULL'
        ],
        'primary_key' => [
            'userId',
            'transporteurId'
        ],
        'foreign key' => [
            [
                'key' => 'userId',
                'references' => [
                    'table' => 'users',
                    'fields' => [
                        'userId'
                    ],
                    'on' => [
                        'update' => 'CASCADE',
                        'delete' => 'RESTRICT'
                    ]
                ]
            ],
            [
                'key' => 'transporteurId',
                'references' => [
                    'table' => 'transporteurs',
                    'fields' => [
                        'transporteurId'
                    ],
                    'on' => [
                        'update' => 'CASCADE',
                        'delete' => 'RESTRICT'
                    ]
                ]
            ]
        ],
        'engine' => 'InnoDb',
        'charset' => 'utf8',
        'collate' => 'utf8_unicode_ci'
    ],
    'data' => __DIR__ . '/data/data.users-transporteurs.php'
];