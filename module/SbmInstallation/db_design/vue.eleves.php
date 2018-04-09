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
 * @date 7 avr. 2018
 * @version 2018-2.4.0
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
                        'field' => 'paiement'
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
                    ]
                ]
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
                'alias' => 'com',
                'relation' => 'com.communeId = eta.communeId',
                'fields' => [
                    [
                        'field' => 'nom',
                        'alias' => 'communeEtablissement'
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
                    ]
                ]
            ],
            [
                'table' => 'responsables',
                'type' => 'table',
                'alias' => 'r2',
                'relation' => 'ele.responsable2Id = r2.responsableId', // obligatoire case when r2.responsableId IS NULL then '' else concat(r2.nom, ' ', r2.prenom) end
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
            /*[
                'table' => 'responsables',
                'type' => 'table',
                'alias' => 'rf',
                'relation' => 'ele.responsableFId = rf.responsableId', // obligatoire
                'fields' => [
                    [
                        'expression' => [
                            'value' => "CASE WHEN rf.responsableId IS NULL THEN CONCAT(r1.nom, ' ', r1.prenom) ELSE CONCAT(rf.nom, ' ', rf.prenom) END",
                            'type' => 'varchar(61)'
                        ],
                        'alias' => 'responsableFNomPrenom'
                    ]
                ],
                'jointure' => Select::JOIN_LEFT
            ]*/
        ]
    ]
];