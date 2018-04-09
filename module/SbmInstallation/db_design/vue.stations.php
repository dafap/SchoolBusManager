<?php
/**
 * Structure de la vue `stations`
 *
 *
 * @project sbm
 * @package SbmInstallation/db_design
 * @filesource vue.stations.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 7 avr. 2018
 * @version 2018-2.4.0
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
                'field' => 'aliasCG'
            ],
            [
                'field' => 'codeCG'
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