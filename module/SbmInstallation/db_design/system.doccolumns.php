<?php
/**
 * Description des champs utilisés dans les tables (table `doccolumns`)
 * 
 * @project sbm
 * @package SbmInstallation/db_design
 * @filesource system.doccolumns.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 7 avr. 2018
 * @version 2018-2.4.0
 */
return [
    'name' => 'doccolumns',
    'type' => 'system',
    'drop' => false,
    'edit_entity' => false,
    'add_data' => false,
    'structure' => [
        'fields' => [
            'doccolumnId' => 'int(11) NOT NULL AUTO_INCREMENT',
            'documentId' => 'int(11) NOT NULL DEFAULT "1"',
            'ordinal_table' => 'int(11) NOT NULL DEFAULT "1"',
            'ordinal_position' => 'int(11) NOT NULL DEFAULT "1"',
            'thead' => 'varchar(255) NOT NULL DEFAULT ""',
            'thead_align' => 'varchar(8) NOT NULL DEFAULT "standard"', // L, C, R, J
            'thead_stretch' => 'tinyint(1) UNSIGNED NOT NULL DEFAULT "0"', // de 0 à 4
            'thead_precision' => 'tinyint(3) NOT NULL DEFAULT "-1"',
            'thead_completion' => 'tinyint(3) NOT NULL DEFAULT "0"',
            'tbody' => 'varchar(255) NOT NULL DEFAULT ""',
            'tbody_align' => 'varchar(8) NOT NULL DEFAULT "standard"', // L, C, R, J
            'tbody_stretch' => 'tinyint(1) UNSIGNED NOT NULL DEFAULT "0"', // de 0 à 4
            'tbody_precision' => 'tinyint(3) NOT NULL DEFAULT "-1"', // précision pour les colonnes numériques. -1 pour ne pas appliquer la précision
            'tbody_completion' => 'tinyint(3) NOT NULL DEFAULT "0"', // complétion à gauche par des espaces ou zéros. Indique le nombre de chiffres à obtenir. 0 <=> pas de complétion
            'tfoot' => 'varchar(255) NOT NULL DEFAULT ""',
            'tfoot_align' => 'varchar(8) NOT NULL DEFAULT "standard"', // L, C, R, J
            'tfoot_stretch' => 'tinyint(1) UNSIGNED NOT NULL DEFAULT "0"', // de 0 à 4
            'tfoot_precision' => 'tinyint(3) NOT NULL DEFAULT "-1"', // précision pour les colonnes numériques. -1 pour ne pas appliquer la précision
            'tfoot_completion' => 'tinyint(3) NOT NULL DEFAULT "0"', // complétion à gauche par des espaces ou zéros. Indique le nombre de chiffres à obtenir. 0 <=> pas de complétion
            'filter' => 'text NULL',
            'width' => 'int(11) NOT NULL DEFAULT "0"', // laisser à 0 pour que Tcpdf calcule la valeur nécessaire
            'truncate' => 'tinyint(1) NOT NULL DEFAULT "0"', // couper à la taille de la colonne (oui 1/non 0)
            'nl' => 'tinyint(1) NOT NULL DEFAULT "0"'
        ], // saut de page après un changement de valeur dans cette colonne (oui 1/non 0)
        'primary_key' => [
            'doccolumnId'
        ],
        'foreign key' => [
            [
                'key' => 'documentId',
                'references' => [
                    'table' => 'documents',
                    'fields' => [
                        'documentId'
                    ],
                    'on' => [
                        'update' => 'CASCADE',
                        'delete' => 'CASCADE'
                    ]
                ]
            ]
        ],
        'engine' => 'InnoDb',
        'charset' => 'utf8',
        'collate' => 'utf8_unicode_ci'
    ],
    // 'data' => include __DIR__ . '/data/data.system.doccolumns.php'
    // 'data' => ['after' => ['documents'], 'include' => __DIR__ . '/data/data.doccolumns.php']
    'data' => __DIR__ . '/data/data.system.doccolumns.php'
];