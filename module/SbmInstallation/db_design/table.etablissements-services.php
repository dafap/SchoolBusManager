<?php
/**
 * Structure de la table des `etablissementsServices`
 *
 *
 * @project sbm
 * @package SbmInstallation/db_design
 * @filesource table.etablissements-services.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 27 fév. 2020
 * @version 2020-2.6.0
 */
use SbmBase\Model\StdLib;

return [
    'name' => 'etablissements-services',
    'type' => 'table',
    'drop' => false,
    'edit_entity' => false,
    'add_data' => false,
    'structure' => [
        'fields' => [
            'etablissementId' => 'char(8) NOT NULL',
            'millesime' => 'int(11) NOT NULL',
            'ligneId' => 'varchar(5) NOT NULL',
            'sens' => 'tinyint(3) UNSIGNED NOT NULL DEFAULT "1"',
            'moment' => 'tinyint(3) UNSIGNED NOT NULL DEFAULT "1"',
            'ordre' => 'tinyint(3) UNSIGNED NOT NULL DEFAULT "1"',
            'stationId' => 'int(11) NOT NULL'
        ],
        'primary_key' => [
            'etablissementId',
            'millesime',
            'ligneId',
            'sens',
            'moment',
            'ordre'
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
                'key' => [
                    'millesime',
                    'ligneId',
                    'sens',
                    'moment',
                    'ordre'
                ],
                'references' => [
                    'table' => 'services',
                    'fields' => [
                        'millesime',
                        'ligneId',
                        'sens',
                        'moment',
                        'ordre'
                    ],
                    'on' => [
                        'update' => 'CASCADE',
                        'delete' => 'RESTRICT'
                    ]
                ]
            ],
            [
                'key' => 'stationId',
                'references' => [
                    'table' => 'stations',
                    'fields' => [
                        'stationId'
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
        'data.etablissements-services.php')
];