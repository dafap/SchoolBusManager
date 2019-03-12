<?php
/**
 * Structure de la vue `etablissements-services`
 *
 * @project sbm
 * @package SbmInstallation/db_design
 * @filesource vue.etablissements-services.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 13 mars 2019
 * @version 2019-2.4.8
 */
return [
    'name' => 'etablissements-services',
    'type' => 'vue',
    'drop' => true, // si true, un DROP TABLE IF EXISTS sera fait avant la création
    'edit_entity' => true,
    'structure' => [
        'fields' => [
            [
                'field' => 'etablissementId'
            ],
            [
                'field' => 'serviceId'
            ],
            [
                'field' => 'stationId'
            ]
        ],
        'from' => [
            'table' => 'etablissements-services', // obligatoire mais peut être une vue
            'type' => 'table', // optionnel, 'table' par défaut
            'alias' => 'rel'
        ],
        'join' => [
            [
                'table' => 'etablissements', // obligatoire mais peut être une vue
                'type' => 'table', // optionnel, 'table' par défaut
                'alias' => 'eta',
                'relation' => 'rel.etablissementId = eta.etablissementId', // obligatoire
                'fields' => [
                    [
                        'field' => 'nom',
                        'alias' => 'etab_nom'
                    ],
                    [
                        'field' => 'alias',
                        'alias' => 'etab_alias'
                    ],
                    [
                        'field' => 'aliasCG',
                        'alias' => 'etab_aliasCG'
                    ],
                    [
                        'field' => 'adresse1',
                        'alias' => 'etab_adresse1'
                    ],
                    [
                        'field' => 'adresse2',
                        'alias' => 'etab_adresse2'
                    ],
                    [
                        'field' => 'codePostal',
                        'alias' => 'etab_codePostal'
                    ],
                    [
                        'field' => 'communeId',
                        'alias' => 'etab_communeId'
                    ],
                    [
                        'field' => 'niveau',
                        'alias' => 'etab_niveau'
                    ],
                    [
                        'expression' => [
                            'value' => 'CASE niveau WHEN 1 THEN "maternelle" WHEN 2 THEN "élémentaire" WHEN 3 THEN "maternelle + élémentaire" WHEN 4 THEN "collège" WHEN 8 THEN "lycée" ELSE "autre" END',
                            'type' => 'varchar(24)'
                        ],
                        'alias' => 'niveauEnToutesLettres'
                    ],
                    [
                        'field' => 'statut',
                        'alias' => 'etab_statut'
                    ],
                    [
                        'expression' => [
                            'value' => 'CASE statut WHEN 1 THEN "Public" ELSE "Privé" END',
                            'type' => 'varchar(6)'
                        ],
                        'alias' => 'statutEnToutesLettres'
                    ],
                    [
                        'field' => 'visible',
                        'alias' => 'etab_visible'
                    ],
                    [
                        'field' => 'desservie',
                        'alias' => 'etab_desservie'
                    ],
                    [
                        'field' => 'regrPeda',
                        'alias' => 'etab_regrPeda'
                    ],
                    [
                        'field' => 'rattacheA',
                        'alias' => 'etab_rattacheA'
                    ],
                    [
                        'field' => 'telephone',
                        'alias' => 'etab_telephone'
                    ],
                    [
                        'field' => 'fax',
                        'alias' => 'etab_fax'
                    ],
                    [
                        'field' => 'email',
                        'alias' => 'etab_email'
                    ],
                    [
                        'field' => 'directeur',
                        'alias' => 'etab_directeur'
                    ],
                    [
                        'field' => 'jOuverture',
                        'alias' => 'etab_jOuverture'
                    ],
                    [
                        'field' => 'hMatin',
                        'alias' => 'etab_hMatin'
                    ],
                    [
                        'field' => 'hMidi',
                        'alias' => 'etab_hMidi'
                    ],
                    [
                        'field' => 'hAMidi',
                        'alias' => 'etab_hAMidi'
                    ],
                    [
                        'field' => 'hSoir',
                        'alias' => 'etab_hSoir'
                    ],
                    [
                        'field' => 'hGarderieOMatin',
                        'alias' => 'etab_hGarderieOMatin'
                    ],
                    [
                        'field' => 'hGarderieFMidi',
                        'alias' => 'etab_hGarderieFMidi'
                    ],
                    [
                        'field' => 'hGarderieFSoir',
                        'alias' => 'etab_hGarderieFSoir'
                    ],
                    [
                        'field' => 'x',
                        'alias' => 'etab_x'
                    ],
                    [
                        'field' => 'y',
                        'alias' => 'etab_y'
                    ]
                ]
            ],
            [
                'table' => 'communes',
                'type' => 'table',
                'alias' => 'com1',
                'relation' => 'com1.communeId = eta.communeId',
                'fields' => [
                    [
                        'field' => 'nom',
                        'alias' => 'etab_commune'
                    ]
                ]
            ],
            [
                'table' => 'services', // obligatoire mais peut être une vue
                'type' => 'table', // optionnel, 'table' par défaut
                'alias' => 'ser', // optionnel
                'relation' => 'rel.serviceId = ser.serviceId', // obligatoire
                'fields' => [
                    [
                        'field' => 'nom',
                        'alias' => 'serv_nom'
                    ],
                    [
                        'field' => 'aliasCG',
                        'alias' => 'serv_aliasCG'
                    ],
                    [
                        'field' => 'transporteurId',
                        'alias' => 'serv_transporteurId'
                    ],
                    [
                        'field' => 'nbPlaces',
                        'alias' => 'serv_nbPlaces'
                    ],
                    [
                        'field' => 'surEtatCG',
                        'alias' => 'serv_surEtatCG'
                    ],
                    [
                        'field' => 'operateur',
                        'alias' => 'serv_operateur'
                    ],
                    [
                        'field' => 'kmAVide',
                        'alias' => 'serv_kmAVide'
                    ],
                    [
                        'field' => 'kmEnCharge',
                        'alias' => 'serv_kmEnCharge'
                    ],
                    [
                        'field' => 'natureCarte',
                        'alias' => 'serv_natureCarte'
                    ],
                    [
                        'field' => 'selection',
                        'alias' => 'serv_selection'
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
                        'alias' => 'serv_transporteur'
                    ]
                ]
            ],
            [
                'table' => 'communes', // obligatoire mais peut être une vue
                'type' => 'table', // optionnel, 'table' par défaut
                'alias' => 'com2', // optionnel
                'relation' => 'com2.communeId = tra.communeId', // obligatoire
                'fields' => [
                    [
                        'field' => 'nom',
                        'alias' => 'serv_communeTransporteur'
                    ]
                ]
            ],
            [
                'table' => 'stations',
                'type' => 'table',
                'alias' => 'sta',
                'relation' => 'rel.stationId = sta.stationId',
                'fields' => [
                    [
                        'field' => 'nom',
                        'alias' => 'sta_nom'
                    ],
                    [
                        'field' => 'ouverte',
                        'alias' => 'sta_ouverte'
                    ],
                    [
                        'field' => 'visible',
                        'alias' => 'sta_visible'
                    ],
                    [
                        'field' => 'selection',
                        'alias' => 'sta_selection'
                    ],
                    [
                        'field' => 'x',
                        'alias' => 'sta_x'
                    ],
                    [
                        'field' => 'y',
                        'alias' => 'sta_y'
                    ]
                ]
            ],
            [
                'table' => 'communes',
                'type' => 'table',
                'alias' => 'comsta',
                'relation' => 'comsta.communeId = sta.communeId',
                'fields' => [
                    [
                        'field' => 'nom',
                        'alias' => 'sta_commune'
                    ]
                ]
            ],
            [
                'table' => 'circuits',
                'type' => 'table',
                'alias' => 'cir',
                'relation' => 'cir.serviceId = rel.serviceId AND cir.stationId = rel.stationId',
                'fields' => [
                    
                    [
                        'field' => 'circuitId'
                    ],
                    [
                        'field' => 'selection',
                        'alias' => 'cir_selection'
                    ],
                    [
                        'field' => 'millesime',
                        'alias' => 'cir_millesime'
                    ],
                    [
                        'field' => 'semaine',
                        'alias' => 'cir_semaine'
                    ],
                    [
                        'field' => 'm1',
                        'alias' => 'cir_m1'
                    ],
                    [
                        'field' => 's1',
                        'alias' => 'cir_s1'
                    ],
                    [
                        'field' => 'm2',
                        'alias' => 'cir_m2'
                    ],
                    [
                        'field' => 's2',
                        'alias' => 'cir_s2'
                    ],
                    [
                        'field' => 'm3',
                        'alias' => 'cir_m3'
                    ],
                    [
                        'field' => 's3',
                        'alias' => 'cir_s3'
                    ],
                    [
                        'field' => 'distance',
                        'alias' => 'cir_distance'
                    ],
                    [
                        'field' => 'montee',
                        'alias' => 'cir_montee'
                    ],
                    [
                        'field' => 'descente',
                        'alias' => 'cir_descente'
                    ],
                    [
                        'field' => 'typeArret',
                        'alias' => 'cir_typeArret'
                    ],
                    [
                        'field' => 'commentaire1',
                        'alias' => 'cir_commentaire1'
                    ],
                    [
                        'field' => 'commentaire2',
                        'alias' => 'cir_commentaire2'
                    ]
                ]
            ]
        ],
        'order' => [
            'etablissementId',
            'serviceId'
        ]
    ]
];