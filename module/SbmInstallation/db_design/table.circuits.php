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
 * @date 3 avril 2019
 * @version 2019-2.5.0
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
            'selection' => 'tinyint(1) NOT NULL DEFAULT "0"',
            'millesime' => 'int(11) NOT NULL',
            'serviceId' => 'varchar(11) NOT NULL',
            'stationId' => 'int(11) NOT NULL',
            'passage' => 'int(11) NOT NULL DEFAULT "1"',
            'semaine' => 'tinyint(4) UNSIGNED NOT NULL DEFAULT "31"',
            'm1' => 'time NOT NULL DEFAULT "00:00:00"',
            's1' => 'time NOT NULL DEFAULT "23:59:59"',
            'z1' => 'time NOT NULL DEFAULT "00:00:00"',
            'm2' => 'time NOT NULL DEFAULT "00:00:00"',
            's2' => 'time NOT NULL DEFAULT "00:00:00"',
            'z2' => 'time NOT NULL DEFAULT "00:00:00"',
            'm3' => 'time NOT NULL DEFAULT "00:00:00"',
            's3' => 'time NOT NULL DEFAULT "00:00:00"',
            'z3' => 'time NOT NULL DEFAULT "00:00:00"',
            'distance' => 'decimal(7,3) NOT NULL DEFAULT "0.000"',
            'montee' => 'tinyint(1) UNSIGNED NOT NULL DEFAULT "1"',
            'descente' => 'tinyint(1) UNSIGNED NOT NULL DEFAULT "0"',
            'typeArret' => 'text NULL',
            'commentaire1' => 'text NULL', // aller
            'commentaire2' => 'text NULL', // retour
            'geopt' => 'GEOMETRY'
        ],
        'primary_key' => [
            'circuitId'
        ],
        'keys' => [
            'milserstapas' => [
                'unique' => true,
                'fields' => [
                    'millesime',
                    'serviceId',
                    'stationId',
                    'passage'
                ]
            ]
        ],
        'foreign key' => [
            [
                'key' => 'serviceId',
                'references' => [
                    'table' => 'services',
                    'fields' => [
                        'serviceId'
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
        'charset' => 'utf8',
        'collate' => 'utf8_unicode_ci'
    ],
    'data' => StdLib::concatPath(StdLib::findParentPath(__DIR__, 'data/data'),
        'data.circuits.php')
];
