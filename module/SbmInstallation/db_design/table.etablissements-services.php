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
 * @date 7 avr. 2018
 * @version 2018-2.4.0
 */
return [
    'name' => 'etablissements-services',
    'type' => 'table',
    'drop' => false,
    'edit_entity' => false,
    'add_data' => false,
    'structure' => [
        'fields' => [
            'etablissementId' => 'char(8) NOT NULL',
            'serviceId' => 'varchar(11) NOT NULL',
            'stationId' => 'int(11) NOT NULL'
        ],
        'primary_key' => [
            'etablissementId',
            'serviceId'
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
    
    // 'data' => include __DIR__ . '/data/data.services.php',
    // 'data' => ['after' => ['transporteurs'],'include' => __DIR__ . '/data/data.services.php']
    'data' => __DIR__ . '/data/data.etablissements-services.php'
];