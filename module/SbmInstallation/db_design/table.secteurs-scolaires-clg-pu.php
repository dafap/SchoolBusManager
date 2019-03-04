<?php
/**
 * Structure de la table des `secteurs-scolaires-clg-pu`
 * 
 * @project sbm
 * @package SbmInstallation/db_design
 * @filesource table.secteurs-scolaires-clg-pu.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 25 fév. 2019
 * @version 2019-2.4.8
 */
use SbmBase\Model\StdLib;

return [
    'name' => 'secteurs-scolaires-clg-pu',
    'type' => 'table',
    'drop' => false,
    'edit_entity' => false,
    'add_data' => false,
    'structure' => [
        'fields' => [
            'communeId' => 'varchar(6) NOT NULL',
            'etablissementId' => 'char(8) NOT NULL'
        ],
        'primary_key' => [
            'communeId',
            'etablissementId'
        ],
        'foreign key' => [
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
        'charset' => 'utf8',
        'collate' => 'utf8_unicode_ci'
    ],
    'data' => StdLib::concatPath(StdLib::findParentPath(__DIR__, 'data/data'),
        'data.secteurs-scolaires-clg-pu.php')
];