<?php
/**
 * Structure de la vue `eleves`
 *
 * Découpage en `eleves`, `scolarites`, `affectations` et `responsables`
 *
 * @project sbm
 * @package SbmInstallation/db_design
 * @filesource vue.eleves.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 29 mars 2020
 * @version 2020-2.6.0
 */
use Zend\Db\Sql\Select;

return [
    'name' => 'eleves',
    'type' => 'vue',
    'drop' => true,
    'edit_entity' => true,
    'structure' => [
        'fields' => [
            [
                'field' => 'eleveId'
            ],
            [
                'field' => 'selection'
            ],
            [
                'field' => 'dateCreation'
            ],
            [
                'field' => 'dateModification'
            ],
            [
                'field' => 'nom'
            ],
            [
                'field' => 'nomSA'
            ],
            [
                'field' => 'prenom'
            ],
            [
                'field' => 'prenomSA'
            ],
            [
                'field' => 'dateN'
            ],
            [
                'field' => 'sexe'
            ],
            [
                'field' => 'numero'
            ],
            [
                'field' => 'responsable1Id'
            ],
            [
                'field' => 'x1'
            ],
            [
                'field' => 'y1'
            ],
            [
                'field' => 'responsable2Id'
            ],
            [
                'field' => 'x2'
            ],
            [
                'field' => 'y2'
            ],
            [
                'field' => 'responsableFId'
            ],
            [
                'field' => 'id_tra',
                'alias' => 'id_tra_elv'
            ],
            [
                'field' => 'note'
            ]
        ],
        'from' => [
            'table' => 'eleves',
            'type' => 'table',
            'alias' => 'ele'
        ],
        'join' => [
            [
                'table' => 'scolarites',
                'type' => 'table',
                'alias' => 'sco',
                'relation' => 'sco.eleveId = ele.eleveId',
                'fields' => [
                    [
                        'field' => 'millesime'
                    ],
                    [
                        'field' => 'etablissementId'
                    ],
                    [
                        'field' => 'classeId'
                    ],
                    [
                        'field' => 'inscrit'
                    ],
                    [
                        'field' => 'paiementR1'
                    ],
                    [
                        'field' => 'paiementR2'
                    ],
                    [
                        'field' => 'district'
                    ],
                    [
                        'field' => 'derogation'
                    ],
                    [
                        'field' => 'distanceR1'
                    ],
                    [
                        'field' => 'distanceR2'
                    ],
                    [
                        'field' => 'demandeR1'
                    ],
                    [
                        'field' => 'demandeR2'
                    ],
                    [
                        'field' => 'dateDemandeR2'
                    ],
                    [
                        'field' => 'stationIdR1'
                    ],
                    [
                        'field' => 'stationIdR2'
                    ],
                    [
                        'field' => 'accordR1'
                    ],
                    [
                        'field' => 'accordR2'
                    ],
                    [
                        'field' => 'subventionR1'
                    ],
                    [
                        'field' => 'subventionR2'
                    ],
                    [
                        'field' => 'grilleTarifR1'
                    ],
                    [
                        'field' => 'reductionR1'
                    ],
                    [
                        'field' => 'grilleTarifR2'
                    ],
                    [
                        'field' => 'reductionR2'
                    ],
                    [
                        'field' => 'joursTransportR1'
                    ],
                    [
                        'field' => 'joursTransportR2'
                    ],
                    [
                        'field' => 'dateEtiquetteR1'
                    ],
                    [
                        'field' => 'dateEtiquetteR2'
                    ],
                    [
                        'field' => 'dateCarteR1'
                    ],
                    [
                        'field' => 'dateCarteR2'
                    ],
                    [
                        'field' => 'duplicataR1'
                    ],
                    [
                        'field' => 'duplicataR2'
                    ]
                ]
            ],
            [
                'table' => 'communes', // obligatoire mais peut être une vue
                'type' => 'table', // optionnel, 'table' par défaut
                'alias' => 'com', // optionnel
                'relation' => 'com.communeId = sco.communeId', // obligatoire
                'fields' => [
                    [
                        'field' => 'nom',
                        'alias' => 'communeElv'
                    ],
                    [
                        'field' => 'alias',
                        'alias' => 'lacommuneElv'
                    ],
                    [
                        'field' => 'alias_laposte',
                        'alias' => 'laposteElv'
                    ]
                ],
                'jointure' => Select::JOIN_LEFT
            ],
            [
                'table' => 'etablissements',
                'type' => 'table',
                'alias' => 'eta',
                'relation' => 'sco.etablissementId = eta.etablissementId',
                'fields' => [
                    [
                        'expression' => [
                            'value' => 'CASE WHEN eta.alias IS NULL THEN eta.nom ELSE eta.alias END',
                            'type' => 'varchar(45)'
                        ],
                        'alias' => 'etablissement'
                    ]
                ]
            ],
            [
                'table' => 'communes',
                'type' => 'table',
                'alias' => 'cet',
                'relation' => 'cet.communeId = eta.communeId',
                'fields' => [
                    [
                        'field' => 'nom',
                        'alias' => 'communeEtablissement'
                    ],
                    [
                        'field' => 'alias',
                        'alias' => 'lacommuneEtablissement'
                    ],
                    [
                        'field' => 'alias_laposte',
                        'alias' => 'laposteEtablissement'
                    ]
                ]
            ],
            [
                'table' => 'responsables', // obligatoire mais peut être une vue
                'type' => 'table', // optionnel, 'table' par défaut
                'alias' => 'r1', // optionnel
                'relation' => 'ele.responsable1Id = r1.responsableId', // obligatoire
                'fields' => [
                    [
                        'expression' => [
                            'value' => "CONCAT(r1.nom, ' ', r1.prenom)",
                            'type' => 'varchar(61)'
                        ],
                        'alias' => 'responsable1NomPrenom'
                    ],
                    [
                        'field' => 'id_tra',
                        'alias' => 'id_tra_resp'
                    ]
                ]
            ],
            [
                'table' => 'communes', // obligatoire mais peut être une vue
                'type' => 'table', // optionnel, 'table' par défaut
                'alias' => 'cr1', // optionnel
                'relation' => 'cr1.communeId = r1.communeId', // obligatoire
                'fields' => [
                    [
                        'field' => 'nom',
                        'alias' => 'communeR1'
                    ],
                    [
                        'field' => 'alias',
                        'alias' => 'lacommuneR1'
                    ],
                    [
                        'field' => 'alias_laposte',
                        'alias' => 'laposteR1'
                    ]
                ]
            ],
            [
                'table' => 'responsables',
                'type' => 'table',
                'alias' => 'r2',
                'relation' => 'ele.responsable2Id = r2.responsableId', // obligatoire
                'fields' => [
                    [
                        'expression' => [
                            'value' => "CASE WHEN r2.responsableId IS NULL THEN '' ELSE CONCAT(r2.nom, ' ', r2.prenom) END",
                            'type' => 'varchar(61)'
                        ],
                        'alias' => 'responsable2NomPrenom'
                    ]
                ],
                'jointure' => Select::JOIN_LEFT
            ],
            [
                'table' => 'communes', // obligatoire mais peut être une vue
                'type' => 'table', // optionnel, 'table' par défaut
                'alias' => 'cr2', // optionnel
                'relation' => 'cr2.communeId = r2.communeId', // obligatoire
                'fields' => [
                    [
                        'field' => 'nom',
                        'alias' => 'communeR2'
                    ],
                    [
                        'field' => 'alias',
                        'alias' => 'lacommuneR2'
                    ],
                    [
                        'field' => 'alias_laposte',
                        'alias' => 'laposteR2'
                    ]
                ],
                'jointure' => Select::JOIN_LEFT
            ]
            /*
         * [ 'table' => 'responsables', 'type' => 'table', 'alias' => 'rf', 'relation' =>
         * 'ele.responsableFId = rf.responsableId', // obligatoire 'fields' => [ [
         * 'expression' => [ 'value' => "CASE WHEN rf.responsableId IS NULL THEN
         * CONCAT(r1.nom, ' ', r1.prenom) ELSE CONCAT(rf.nom, ' ', rf.prenom) END", 'type'
         * => 'varchar(61)' ], 'alias' => 'responsableFNomPrenom' ] ], 'jointure' =>
         * Select::JOIN_LEFT ]
         */
        ]
    ]
];