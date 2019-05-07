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
 * @date 29 avr. 2019
 * @version 2019-2.5.0
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
            'serviceId' => 'varchar(11) NOT NULL',
            'alias' => 'varchar(15) NOT NULL DEFAULT ""',
            'aliasTr' => 'varchar(15) NOT NULL DEFAULT ""',
            'aliasCG' => 'varchar(15) NOT NULL DEFAULT ""',
            'lotId' => 'int(11) NOT NULL DEFAULT "0"',
            'selection' => 'tinyint(1) NOT NULL DEFAULT "0"',
            'nom' => 'varchar(45) NOT NULL',
            'horaire1'=>'tinyint(4) UNSIGNED NOT NULL DEFAULT "0"',
            'horaire2'=>'tinyint(4) UNSIGNED NOT NULL DEFAULT "0"',
            'horaire3'=>'tinyint(4) UNSIGNED NOT NULL DEFAULT "0"',
            'transporteurId' => 'int(11) NOT NULL DEFAULT "0"',
            'nbPlaces' => 'tinyint(3) unsigned NOT NULL DEFAULT "0"',
            'surEtatCG' => 'tinyint(1) NOT NULL DEFAULT "0"',
            'operateur' => 'varchar(5) NOT NULL DEFAULT "CCMGC"',
            'kmAVide' => 'decimal(7,3) NOT NULL DEFAULT "0"',
            'kmEnCharge' => 'decimal(7,3) NOT NULL DEFAULT "0.000"',
            'natureCarte' => 'tinyint(1) NOT NULL DEFAULT "1"',
            'geotrajet' => 'POLYGON'
        ],
        'primary_key' => [
            'serviceId'
        ],
        'foreign key' => [
            [
                'key' => 'lotId',
                'references' => [
                    'table' => 'lots',
                    'fields' => [
                        'lotId'
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
        'charset' => 'utf8',
        'collate' => 'utf8_unicode_ci'
    ],
    'data' => StdLib::concatPath(StdLib::findParentPath(__DIR__, 'data/data'),
        'data.services.php')
];