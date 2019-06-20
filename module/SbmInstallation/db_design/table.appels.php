<?php
/**
 * Structure de la table des `appels`
 *
 * Il s'agit des appels à la plateforme de paiement pour essayer de payer.
 * Cette table établit la liaison entre le payeur et les élèves concernés.
 *
 * `referenceId` est la concaténation de vads_trans_id (formaté sur 6 caractères) avec
 * vads_trans_date (formaté AAAAMMJJHHMMSS, heure du serveur de SystemPay)
 *
 * @project sbm
 * @package SbmInstallation/db_design
 * @filesource table.appels.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 19 juin 2019
 * @version 2019-2.5.0
 */
use SbmBase\Model\StdLib;

return [
    'name' => 'appels',
    'type' => 'table',
    'drop' => false,
    'edit_entity' => false,
    'add_data' => false,
    'structure' => [
        'fields' => [
            'appelId' => 'int(11) NOT NULL AUTO_INCREMENT',
            'notified' => 'int(1) NOT NULL DEFAULT "0"',
            'idOp' => 'char(36) NOT NULL',
            'refdet' => 'varchar(30) NOT NULL',
            'responsableId' => 'int(11) NOT NULL',
            'eleveId' => 'int(11) NOT NULL'
        ],
        'primary_key' => [
            'appelId'
        ],
        'keys' => [
            'APPELS_idOp' => [
                'unique' => true,
                'fields' => [
                    'idOp'
                ]
            ],
            'APPELS_refdetNotified' => [
                'unique' => false,
                'fields' => [
                    'notified',
                    'refdet'
                ]
            ]
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
    'data' => StdLib::concatPath(StdLib::findParentPath(__DIR__, 'data/data'),
        'data.appels.php')
];