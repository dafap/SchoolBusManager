<?php
/**
 * Structure de la table des `stations`
 *
 *
 * @project sbm
 * @package SbmInstallation/db_design
 * @filesource table.stations.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 15 fév. 2020
 * @version 2020-2.6.0
 */
use SbmBase\Model\StdLib;

return [
    'name' => 'stations',
    'drop' => false,
    'edit_entity' => false,
    'add_data' => false,
    'type' => 'table',
    'structure' => [
        'fields' => [
            'stationId' => 'int(11) NOT NULL AUTO_INCREMENT',
            'selection' => 'tinyint(1) NOT NULL DEFAULT "0"',
            'communeId' => 'varchar(6) NOT NULL',
            'nom' => 'varchar(45) NOT NULL',
            'alias' => 'varchar(45) NOT NULL DEFAULT ""',
            'code' => 'int(11) NOT NULL DEFAULT "0"',
            'x' => 'decimal(18,10) NOT NULL DEFAULT "0.0"',
            'y' => 'decimal(18,10) NOT NULL DEFAULT "0.0"',
            'visible' => 'tinyint(1) NOT NULL DEFAULT "1"',
            'ouverte' => 'tinyint(1) NOT NULL DEFAULT  "1"',
            'equipement' => 'text NULL',
            'id_tra' =>'varchar(14) NULL DEFAULT NULL'
        ],
        'primary_key' => [
            'stationId'
        ],
        'foreign key' => [
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
        'data.stations.php')
];