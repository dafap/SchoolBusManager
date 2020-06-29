<?php
/**
 * Structure de la vue `stations`
 *
 * Version pour TRANSDEV ALBERTVILLE
 *
 * @project sbm
 * @package SbmInstallation/db_design
 * @filesource vue.stations.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 29 juin 2020
 * @version 2020-2.6.0
 */
use Zend\Db\Sql\Select;

return [
    'name' => 'stations',
    'type' => 'vue',
    'drop' => true, // si true, un DROP TABLE IF EXISTS sera fait avant la création
    'edit_entity' => true,
    'structure' => [
        'quantifier' => 'distinct',
        'fields' => [
            [
                'field' => 'stationId'
            ],
            [
                'field' => 'selection'
            ],
            [
                'field' => 'communeId'
            ],
            [
                'field' => 'nom'
            ],
            [
                'field' => 'alias'
            ],
            [
                'field' => 'code'
            ],
            [
                'field' => 'x'
            ],
            [
                'field' => 'y'
            ],
            [
                'field' => 'visible'
            ],
            [
                'field' => 'ouverte'
            ],
            [
                'field' => 'equipement'
            ],
            [
                'field' => 'id_tra'
            ],
            [
                'expression' => [
                    'value' => '(ss1.station1Id IS NOT NULL) OR (ss2.station2Id IS NOT NULL)',
                    'type' => 'tinyint(1)'
                ],
                'alias' => 'jumelle'
            ]
        ],
        'from' => [
            'table' => 'stations', // obligatoire mais peut être une vue
            'type' => 'table', // optionnel, 'table' par défaut
            'alias' => 'sta'
        ], // optionnel
        'join' => [
            [
                'table' => 'communes', // obligatoire mais peut être une vue
                'type' => 'table', // optionnel, 'table' par défaut
                'alias' => 'com', // optionnel
                'relation' => 'com.communeId = sta.communeId', // obligatoire
                'fields' => [
                    [
                        'field' => 'nom',
                        'alias' => 'commune'
                    ],
                    [
                        'field' => 'alias',
                        'alias' => 'lacommune' // commune en maj avec LE, LA ou LES,
                                               // apostrophes et tirets
                    ],
                    [
                        'field' => 'alias_laposte',
                        'alias' => 'laposte'
                    ],
                    [
                        'field' => 'codePostal'
                    ]
                ]
            ],
            [
                'table' => 'stations-stations',
                'type' => 'table',
                'alias' => 'ss1',
                'relation' => 'sta.stationId = ss1.station1Id',
                'fields' => [],
                'jointure' => Select::JOIN_LEFT
            ],
            [
                'table' => 'stations-stations',
                'type' => 'table',
                'alias' => 'ss2',
                'relation' => 'sta.stationId = ss2.station2Id',
                'fields' => [],
                'jointure' => Select::JOIN_LEFT
            ]
        ],
        'order' => [
            'commune',
            'nom'
        ]
    ]
];