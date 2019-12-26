<?php
/**
 * Structure de la table des `rpi-etablissements`
 *
 * @project sbm
 * @package SbmInstallation/db_design
 * @filesource table.rpi-etablissements.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 21 déc. 2019
 * @version 2019-2.5.4
 */
use SbmBase\Model\StdLib;

return [
    'name' => 'rpi-etablissements',
    'type' => 'table',
    'drop' => false,
    'edit_entity' => false,
    'add_data' => false,
    'structure' => [
        'fields' => [
            'rpiId' => 'int(11) NOT NULL',
            'etablissementId' => 'char(8) NOT NULL'
        ],
        'primary_key' => [
            'rpiId',
            'etablissementId'
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
        'charset' => 'utf8mb4',
        'collate' => 'utf8mb4_unicode_ci'
    ],
    'data' => StdLib::concatPath(StdLib::findParentPath(__DIR__, 'data/data'),
        'data.rpi-etablissements.php')
];