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
 * @date 15 fév. 2020
 * @version 2020-2.6.0
 */
return [
    'name' => 'stations',
    'type' => 'vue',
    'drop' => true, // si true, un DROP TABLE IF EXISTS sera fait avant la création
    'edit_entity' => true,
    'structure' => [
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
                        'alias' => 'lacommune' // commune en maj avec LE, LA ou LES, apostrophes et tirets
                    ],
                    [
                        'field' => 'alias_laposte',
                        'alias' => 'laposte'
                    ],
                    [
                        'field' => 'codePostal'
                    ]
                ]
            ]
        ],
        'order' => [
            'commune',
            'nom'
        ]
    ]
];