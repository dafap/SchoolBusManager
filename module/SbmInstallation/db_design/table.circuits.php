<?php
/**
 * Structure de la table des `circuits`
 *
 *
 * @project sbm
 * @package SbmInstallation/db_design
 * @filesource table.circuits.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 29 mars 2020
 * @version 2020-2.6.0
 */
use SbmBase\Model\StdLib;

return [
    'name' => 'circuits',
    'drop' => false,
    'edit_entity' => false,
    'add_data' => false,
    'type' => 'table',
    'structure' => [
        'fields' => [
            'circuitId' => 'int(11) NOT NULL AUTO_INCREMENT',
            'millesime' => 'int(4) NOT NULL',
            'ligneId' => 'varchar(5) NOT NULL',
            'sens' => 'tinyint(3) UNSIGNED NOT NULL DEFAULT "1"',
            'moment' => 'tinyint(3) UNSIGNED NOT NULL DEFAULT "1"',
            'ordre' => 'tinyint(3) UNSIGNED NOT NULL DEFAULT "1"',
            'stationId' => 'int(11) NOT NULL',
            'passage' => 'tinyint(3) UNSIGNED NOT NULL DEFAULT "1"',
            'selection' => 'tinyint(1) NOT NULL DEFAULT "0"',
            'visible' => 'tinyint(1) NOT NULL DEFAULT "1"',
            'ouvert' => 'tinyint(1) NOT NULL DEFAULT "1"',
            'semaine' => 'tinyint(3) UNSIGNED NOT NULL DEFAULT "31"',
            'horaireA' => 'time NOT NULL DEFAULT "00:00:00"',
            'horaireD' => 'time NOT NULL DEFAULT "00:00:00"',
            'distance' => 'decimal(7,3) NOT NULL DEFAULT "0.000"',
            'montee' => 'tinyint(1) NOT NULL DEFAULT "1"',
            'descente' => 'tinyint(1) NOT NULL DEFAULT "0"',
            'correspondance' => 'tinyint(1) DEFAULT "0"',
            'emplacement' => 'varchar(45) NOT NULL DEFAULT ""',
            'typeArret' => 'text NULL',
            'commentaire1' => 'text NULL',
            'commentaire2' => 'text NULL'
        ],
        'primary_key' => [
            'circuitId'
        ],
        'keys' => [
            'milserstapas' => [
                'unique' => true,
                'fields' => [
                    'millesime',
                    'ligneId',
                    'sens',
                    'moment',
                    'ordre',
                    'stationId',
                    'passage'
                ]
            ]
        ],
        'foreign key' => [
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
        'data.circuits.php')
];
