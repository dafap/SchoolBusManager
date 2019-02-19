<?php
/**
 * Table système - Affectation des documents aux méthodes des controllers
 *
 *
 * @project sbm
 * @package SbmInstallation/db_design
 * @filesource system.docaffectations.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 7 avr. 2018
 * @version 2018-2.4.0
 */
return [
    'name' => 'docaffectations',
    'type' => 'system',
    'drop' => false,
    'edit_entity' => false,
    'add_data' => false,
    'structure' => [
        'fields' => [
            'docaffectationId' => 'int(11) NOT NULL AUTO_INCREMENT',
            'documentId' => 'int(11) NOT NULL',
            'route' => 'varchar(255) NOT NULL',
            'libelle' => 'varchar(255) NOT NULL',
            'ordinal_position' => 'int(11) NOT NULL'
        ],
        'primary_key' => [
            'docaffectationId'
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
    'data' => __DIR__ . '/data/data.system.docaffectations.php'
];