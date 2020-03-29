<?php
/**
 * Structure de la table des `services`
 *
 *
 * @project sbm
 * @package SbmInstallation/db_design
 * @filesource table.services.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 29 mars 2020
 * @version 2020-2.6.0
 */
use SbmBase\Model\StdLib;

return [
    'name' => 'services',
    'type' => 'table',
    'drop' => false,
    'edit_entity' => false,
    'add_data' => false,
    'structure' => [
        'fields' => [
            'millesime' => 'int(4) NOT NULL',
            'ligneId' => 'varchar(5) NOT NULL',
            'sens' => 'tinyint(3) UNSIGNED NOT NULL DEFAULT "1"',
            'moment' => 'tinyint(3) UNSIGNED NOT NULL DEFAULT "1"',
            'ordre' => 'tinyint(3) UNSIGNED NOT NULL DEFAULT "1"',
            'transporteurId' => 'int(11) NOT NULL DEFAULT "0"',
            'selection' => 'tinyint(1) NOT NULL DEFAULT "0"',
            'actif' => 'tinyint(1) NOT NULL DEFAULT "1"',
            'visible' => 'tinyint(1) NOT NULL DEFAULT "1"',
            'semaine' => 'tinyint(3) UNSIGNED NOT NULL DEFAULT "31"',
            'rang' => 'tinyint(3) UNSIGNED NOT NULL DEFAULT "1"',
            'type' => 'tinyint(3) UNSIGNED NOT NULL DEFAULT  "0"',
            'nbPlaces' => 'int(11) unsigned NOT NULL DEFAULT "0"',
            'alias' => 'varchar(45) NOT NULL DEFAULT ""',
            'commentaire' => 'text NULL DEFAULT NULL'
        ],
        'primary_key' => [
            'millesime',
            'ligneId',
            'sens',
            'moment',
            'ordre'
        ],
        'foreign key' => [
            [
                'key' => [
                    'millesime',
                    'ligneId'
                ],
                'references' => [
                    'table' => 'lignes',
                    'fields' => [
                        'millesime',
                        'ligneId'
                    ],
                    'on' => [
                        'update' => 'CASCADE',
                        'delete' => 'RESTRICT'
                    ]
                ]
            ],
            [
                'key' => 'transporteurId',
                'references' => [
                    'table' => 'transporteurs',
                    'fields' => [
                        'transporteurId'
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
        'data.services.php')
];