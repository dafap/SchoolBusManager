<?php
/**
 * Structure de la table des `appels`
 *
 * Il s'agit des appels à la plateforme de paiement pour essayer de payer.
 * Cette table établit la liaison entre le payeur et les élèves concernés.
 * 
 * @project sbm
 * @package SbmInstallation/db_design
 * @filesource table.appels.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 7 avr. 2018
 * @version 2018-2.4.0
 */
return [
    'name' => 'appels',
    'type' => 'table',
    'drop' => false,
    'edit_entity' => false,
    'add_data' => false,
    'structure' => [
        'fields' => [
            'appelId' => 'int(11) NOT NULL AUTO_INCREMENT',
            'referenceId' => 'varchar(20) NOT NULL',
            'responsableId' => 'int(11) NOT NULL',
            'eleveId' => 'int(11) NOT NULL'
        ],
        'primary_key' => [
            'appelId'
        ],
        'foreign key' => [
            [
                'key' => 'responsableId',
                'references' => [
                    'table' => 'responsables',
                    'fields' => [
                        'responsableId'
                    ],
                    'on' => [
                        'update' => 'CASCADE',
                        'delete' => 'RESTRICT'
                    ]
                ]
            ],
            [
                'key' => 'eleveId',
                'references' => [
                    'table' => 'eleves',
                    'fields' => [
                        'eleveId'
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
    
    'data' => __DIR__ . '/data/data.appels.php'
];