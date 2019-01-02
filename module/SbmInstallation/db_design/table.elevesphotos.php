<?php
/**
 * Structure de la table des `elevesphotos`
 *
 * Liaison 1<->1 avec `eleves`
 * 
 * @project sbm
 * @package SbmInstallation/db_design
 * @filesource table.elevesphotos.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 27 déc. 2018
 * @version 2018-2.4.6
 */
return [
    'name' => 'elevesphotos',
    'type' => 'table',
    'drop' => true,
    'edit_entity' => true,
    'add_data' => false,
    'structure' => [
        'fields' => [
            'eleveId' => 'int(11) NOT NULL',
            'selection' => 'tinyint(1) NOT NULL DEFAULT "0"',
            'dateCreation' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'dateModification' => 'datetime NOT NULL DEFAULT "1900-01-01 00:00:00"',
            'type' => 'varchar(25) NOT NULL DEFAULT "JPEG"',
            'description' => 'varchar(100) NULL',
            'photo' => 'blob NOT NULL'
        ], // gid CCDA
        'primary_key' => [
            'eleveId'
        ],
        // 'keys' => ['responsable1Id' => ['fields' => ['responsable1Id'))),
        'foreign key' => [
            [
                'key' => 'eleveId',
                'references' => [
                    'table' => 'eleves',
                    'fields' => [
                        'eleveId'
                    ],
                    'on' => [
                        'update' => 'CASCADE',
                        'delete' => 'CASCADE'
                    ]
                ]
            ]
        ],
        'engine' => 'InnoDB',
        'charset' => 'utf8',
        'collate' => 'utf8_unicode_ci'
    ],
    // 'data' => ['after' => ['eleves'],'include' => __DIR__ . '/data/data.elevesphotos.php']
    'data' => __DIR__ . '/data/data.elevesphotos.php'
]; 