<?php
/**
 * Structure de la vue `etablissements`
 *
 * Version pour TRANSDEV ALBERTVILLE
 *
 * @project sbm
 * @package SbmInstallation/db_design
 * @filesource vue.etablissements.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 28 fév. 2020
 * @version 2020-2.6.0
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
                'expression' => [
                    'value' => 'CASE niveau WHEN 1 THEN "maternelle" WHEN 2 THEN "élémentaire" WHEN 3 THEN "maternelle + élémentaire" WHEN 4 THEN "collège" WHEN 8 THEN "lycée" ELSE "autre" END',
                    'type' => 'varchar(24)'
                ],
                'alias' => 'niveauEnToutesLettres'
            ],
            [
                'field' => 'statut'
            ],
            [
                'expression' => [
                    'value' => 'CASE statut WHEN 1 THEN "Public" ELSE "Privé" END',
                    'type' => 'varchar(6)'
                ],
                'alias' => 'statutEnToutesLettres'
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
        'order' => [
            'commune',
            'niveau',
            'nom'
        ]
    ]
];