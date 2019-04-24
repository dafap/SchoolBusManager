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
 * @date 25 mars 2019
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
                'field' => 'alias'
            ],
            [
                'field' => 'aliasTr'
            ],
            [
                'field' => 'aliasCG'
            ],
            [
                'field' => 'lotId'
            ],
            [
                'field' => 'selection'
            ],
            [
                'field' => 'nom'
            ],
            [
                'field' => 'horaire1'
            ],
            [
                'field' => 'horaire2'
            ],
            [
                'field' => 'horaire3'
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
        ],
        'join' => [
            [
                'table' => 'lots',
                'type' => 'table',
                'alias' => 'lots',
                'relation' => 'lots.lotId=ser.lotId',
                'fields' => [
                    [
                        'field' => 'marche'
                    ],
                    [
                        'field' => 'lot'
                    ],
                    [
                        'field' => 'libelle'
                    ],
                    [
                        'field' => 'complement'
                    ],
                    [
                        'field' => 'dateDebut'
                    ],
                    [
                        'field' => 'dateFin'
                    ],
                    [
                        'field' => 'transporteurId',
                        'alias' => 'titulaireId'
                    ],
                    [
                        'field' => 'commentaire'
                    ]
                ]
            ],
            [
                'table' => 'transporteurs', // obligatoire mais peut être une vue
                'type' => 'table', // optionnel, 'table' par défaut
                'alias' => 'tit', // optionnel
                'relation' => 'tit.transporteurId = lots.transporteurId', // obligatoire
                'fields' => [
                    [
                        'field' => 'nom',
                        'alias' => 'titulaire'
                    ]
                ]
            ],
            [
                'table' => 'communes', // obligatoire mais peut être une vue
                'type' => 'table', // optionnel, 'table' par défaut
                'alias' => 'ctit', // optionnel
                'relation' => 'ctit.communeId = tit.communeId', // obligatoire
                'fields' => [
                    [
                        'field' => 'nom',
                        'alias' => 'communeTitulaire'
                    ]
                ]
            ],
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
                'alias' => 'ctra', // optionnel
                'relation' => 'ctra.communeId = tra.communeId', // obligatoire
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