<?php
/**
 * Structure de la table des `stations-stations`
 *
 *
 * @project sbm
 * @package SbmInstallation/db_design
 * @filesource table.stations-stations.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 24 fév. 2021
 * @version 2021-2.6.1
 */
use SbmBase\Model\StdLib;

return [
    'name' => 'stations-stations',
    'type' => 'table',
    'drop' => false,
    'edit_entity' => false,
    'add_data' => false,
    'structure' => [
        'fields' => [
            'station1Id' => 'int(11) NOT NULL DEFAULT "0"',
            'station2Id' => 'int(11) NOT NULL DEFAULT "0"',
            'temps' => 'time DEFAULT NULL'
        ],
        'primary_key' => [
            'station1Id',
            'station2Id'
        ],
        'foreign key' => [
            [
                'key' => 'station1Id',
                'references' => [
                    'table' => 'stations',
                    'fields' => [
                        'stationId'
                    ],
                    'on' => [
                        'update' => 'CASCADE',
                        'delete' => 'CASCADE'
                    ]
                ]
            ],
            [
                'key' => 'station2Id',
                'references' => [
                    'table' => 'stations',
                    'fields' => [
                        'stationId'
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
        'data.stations-stations.php')
];