<?php
/**
 * Structure de la table `users-communes`
 *
 * @project sbm
 * @package SbmInstallation/db_design
 * @filesource table.users-communes.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 15 avr. 2020
 * @version 2020-2.6.0
 */
use SbmBase\Model\StdLib;

return [
    'name' => 'users-communes',
    'type' => 'table',
    'drop' => false,
    'edit_entity' => false,
    'add_data' => false,
    'structure' => [
        'fields' => [
            'userId' => 'int(11) NOT NULL',
            'communeId' => 'varchar(6) NOT NULL'
        ],
        'primary_key' => [
            'userId',
            'communeId'
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
                'key' => 'communeId',
                'references' => [
                    'table' => 'communes',
                    'fields' => [
                        'communeId'
                    ],
                    'on' => [
                        'update' => 'CASCADE',
                        'delete' => 'RESTRICT'
                    ]
                ]
            ]
        ],
        'engine' => 'InnoDb',
        'charset' => 'utf8mb4',
        'collate' => 'utf8mb4_unicode_ci'
    ],
    'data' => StdLib::concatPath(StdLib::findParentPath(__DIR__, 'data/data'),
        'data.users-communes.php')
];