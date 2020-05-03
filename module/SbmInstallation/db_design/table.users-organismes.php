<?php
/**
 * Structure de la table `users-organismes`
 *
 * @project sbm
 * @package SbmInstallation/db_design
 * @filesource table.users-organismes.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 3 mai 2020
 * @version 2020-2.6.0
 */
use SbmBase\Model\StdLib;

return [
    'name' => 'users-organismes',
    'type' => 'table',
    'drop' => false,
    'edit_entity' => false,
    'add_data' => false,
    'structure' => [
        'fields' => [
            'userId' => 'int(11) NOT NULL',
            'organismeId' => 'int(11) NOT NULL'
        ],
        'primary_key' => [
            'userId',
            'organismeId'
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
                'key' => 'organismeId',
                'references' => [
                    'table' => 'organismes',
                    'fields' => [
                        'organismeId'
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
        'data.users-organismes.php')
];