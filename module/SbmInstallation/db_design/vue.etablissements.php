<?php
/**
 * Structure de la vue `etablissements`
 *
 *
 * @project sbm
 * @package SbmInstallation/db_design
 * @filesource vue.etablissements.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 7 avr. 2018
 * @version 2018-2.4.0
 */
return [
    'name' => 'etablissements',
    'type' => 'vue',
    'drop' => true, // si true, un DROP TABLE IF EXISTS sera fait avant la création
    'edit_entity' => true,
    'structure' => [
        'fields' => [
            [
                'field' => 'etablissementId'
            ],
            [
                'field' => 'selection'
            ],
            [
                'field' => 'nom'
            ],
            [
                'field' => 'alias'
            ],
            [
                'field' => 'aliasCG'
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
                'field' => 'niveau'
            ],
            [
                'field' => 'statut'
            ],
            [
                'field' => 'visible'
            ],
            [
                'field' => 'desservie'
            ],
            [
                'field' => 'regrPeda'
            ],
            [
                'field' => 'rattacheA'
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
                'field' => 'directeur'
            ],
            [
                'field' => 'jOuverture'
            ],
            [
                'field' => 'hMatin'
            ],
            [
                'field' => 'hMidi'
            ],
            [
                'field' => 'hAMidi'
            ],
            [
                'field' => 'hSoir'
            ],
            [
                'field' => 'hGarderieOMatin'
            ],
            [
                'field' => 'hGarderieFMidi'
            ],
            [
                'field' => 'hGarderieFSoir'
            ],
            [
                'field' => 'x'
            ],
            [
                'field' => 'y'
            ]
        ],
        'from' => [
            'table' => 'etablissements', // obligatoire mais peut être une vue
            'type' => 'table', // optionnel, 'table' par défaut
            'alias' => 'eta'
        ], // optionnel
        'join' => [
            [
                'table' => 'communes', // obligatoire mais peut être une vue
                'type' => 'table', // optionnel, 'table' par défaut
                'alias' => 'com', // optionnel
                'relation' => 'com.communeId = eta.communeId', // obligatoire
                'fields' => [
                    [
                        'field' => 'nom',
                        'alias' => 'commune'
                    ]
                ]
            ]
        ],
        'order' => [
            'commune',
            'niveau',
            'nom'
        ]
    ]
];