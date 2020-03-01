<?php
/**
 * Structure de la vue `transporteurs`
 *
 * Version pour TRANSDEV ALBERTVILLE
 *
 * @project sbm
 * @package SbmInstallation/db_design
 * @filesource vue.transporteurs.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 28 fév. 2020
 * @version 2020-2.6.0
 */
return [
    'name' => 'transporteurs',
    'type' => 'vue',
    'drop' => true, // si true, un DROP TABLE IF EXISTS sera fait avant la création
    'edit_entity' => true,
    'structure' => [
        'fields' => [
            [
                'field' => 'transporteurId'
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
            ],
            [
                'field' => 'rib_titulaire'
            ],
            [
                'field' => 'rib_domiciliation'
            ],
            [
                'field' => 'rib_bic'
            ],
            [
                'field' => 'rib_iban'
            ]
        ],
        'from' => [
            'table' => 'transporteurs', // obligatoire mais peut être une vue
            'type' => 'table', // optionnel, 'table' par défaut
            'alias' => 'tra'
        ], // optionnel
        'join' => [
            [
                'table' => 'communes', // obligatoire mais peut être une vue
                'type' => 'table', // optionnel, 'table' par défaut
                'alias' => 'com', // optionnel
                'relation' => 'com.communeId = tra.communeId', // obligatoire
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