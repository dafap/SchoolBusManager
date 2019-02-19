<?php
/**
 * Structure de la vue `services`
 *
 *
 * @project sbm
 * @package SbmInstallation/db_design
 * @filesource vue.services.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 11 fév. 2019
 * @version 2019-2.5.0
 */
return [
    'name' => 'services',
    'type' => 'vue',
    'drop' => true, // si true, un DROP TABLE IF EXISTS sera fait avant la création
    'edit_entity' => true,
    'structure' => [
        'fields' => [
            [
                'field' => 'serviceId'
            ],
            [
                'field' => 'selection'
            ],
            [
                'field' => 'nom'
            ],
            [
                'field' => 'aliasCG'
            ],
            [
                'field' => 'transporteurId'
            ],
            [
                'field' => 'nbPlaces'
            ],
            [
                'field' => 'surEtatCG'
            ],
            [
                'field' => 'operateur'
            ],
            [
                'field' => 'kmAVide'
            ],
            [
                'field' => 'kmEnCharge'
            ],
            [
                'field' => 'natureCarte'
            ]
        ],
        'from' => [
            'table' => 'services', // obligatoire mais peut être une vue
            'type' => 'table', // optionnel, 'table' par défaut
            'alias' => 'ser'
        ], // optionnel
        'join' => [
            [
                'table' => 'transporteurs', // obligatoire mais peut être une vue
                'type' => 'table', // optionnel, 'table' par défaut
                'alias' => 'tra', // optionnel
                'relation' => 'tra.transporteurId = ser.transporteurId', // obligatoire
                'fields' => [
                    [
                        'field' => 'nom',
                        'alias' => 'transporteur'
                    ]
                ]
            ],
            [
                'table' => 'communes', // obligatoire mais peut être une vue
                'type' => 'table', // optionnel, 'table' par défaut
                'alias' => 'com', // optionnel
                'relation' => 'com.communeId = tra.communeId', // obligatoire
                'fields' => [
                    [
                        'field' => 'nom',
                        'alias' => 'communeTransporteur'
                    ]
                ]
            ]
        ],
        'order' => [
            'serviceId'
        ]
    ]
];