<?php
/**
 * Structure de la table des `usersEtablissements`
 *
 * @project sbm
 * @package SbmInstallation/db_design
 * @filesource table.users-etablissements.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 25 fév. 2019
 * @version 2019-2.4.8
 */
use SbmBase\Model\StdLib;

return [
    'name' => 'users-etablissements',
    'type' => 'table',
    'drop' => false,
    'edit_entity' => false,
    'add_data' => false,
    'structure' => [
        'fields' => [
            'userId' => 'int(11) NOT NULL',
            'etablissementId' => 'char(8) NOT NULL'
        ],
        'primary_key' => [
            'userId',
            'etablissementId'
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
                'key' => 'etablissementId',
                'references' => [
                    'table' => 'etablissements',
                    'fields' => [
                        'etablissementId'
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
    'data' => StdLib::concatPath(StdLib::findParentPath(__DIR__, 'data/data'),
        'data.users-etablissements.php')
];