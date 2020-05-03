<?php
/**
 * Structure de la vue `organismes`
 *
 *
 * @project sbm
 * @package SbmInstallation/db_design
 * @filesource vue.organismes.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 3 mai 2020
 * @version 2020-2.6.0
 */
return [
    'name' => 'organismes',
    'type' => 'vue',
    'drop' => true, // si true, un DROP TABLE IF EXISTS sera fait avant la création
    'edit_entity' => true,
    'structure' => [
        'fields' => [
            [
                'field' => 'organismeId'
            ],
            [
                'field' => 'selection'
            ],
            [
                'field' => 'nom'
            ],
            [
                'field' => 'adresse1'
            ],
            [
                'field' => 'adresse2'
            ],
            [
                'field' => 'codePostal'
            ],
            [
                'field' => 'communeId'
            ],
            [
                'field' => 'telephone'
            ],
            [
                'field' => 'fax'
            ],
            [
                'field' => 'email'
            ],
            [
                'field' => 'siret'
            ],
            [
                'field' => 'naf'
            ]
        ],
        'from' => [
            'table' => 'organismes', // obligatoire mais peut être une vue
            'type' => 'table', // optionnel, 'table' par défaut
            'alias' => 'org'
        ], // optionnel
        'join' => [
            [
                'table' => 'communes', // obligatoire mais peut être une vue
                'type' => 'table', // optionnel, 'table' par défaut
                'alias' => 'com', // optionnel
                'relation' => 'com.communeId = org.communeId', // obligatoire
                'fields' => [
                    [
                        'field' => 'nom',
                        'alias' => 'commune'
                    ],
                    [
                        'field' => 'alias',
                        'alias' => 'lacommune'
                    ],
                    [
                        'field' => 'alias_laposte',
                        'alias' => 'laposte'
                    ]
                ]
            ]
        ],
        'order' => 'nom'
    ]
];