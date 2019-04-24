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
 * @date 24 fév. 2019
 * @version 2019-2.5.0
 */
use SbmBase\Model\StdLib;

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
    'data' => StdLib::concatPath(StdLib::findParentPath(__DIR__, 'data/data'),
        'data.etablissements-services.php')
];