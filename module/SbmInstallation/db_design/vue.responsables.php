<?php
/**
 * Structure de la vue `responsables`
 *
 *
 * @project sbm
 * @package SbmInstallation/db_design
 * @filesource vue.eleve.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 2 mars 2019
 * @version 2019-2.5.0
 */
return [
    'name' => 'responsables',
    'drop' => true, // si true, un DROP TABLE IF EXISTS sera fait avant la création
    'edit_entity' => true,
    'type' => 'vue',
    'structure' => [
        'fields' => [
            [
                'field' => 'responsableId'
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
                'field' => 'nature'
            ],
            [
                'field' => 'titre'
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
                'field' => 'titre2'
            ],
            [
                'field' => 'nom2'
            ],
            [
                'field' => 'nom2SA'
            ],
            [
                'field' => 'prenom2'
            ],
            [
                'field' => 'prenom2SA'
            ],
            [
                'field' => 'adresseL1'
            ],
            [
                'field' => 'adresseL2'
            ],
            [
                'field' => 'codePostal'
            ],
            [
                'field' => 'communeId'
            ],
            [
                'field' => 'ancienAdresseL1'
            ],
            [
                'field' => 'ancienAdresseL2'
            ],
            [
                'field' => 'ancienCodePostal'
            ],
            [
                'field' => 'ancienCommuneId'
            ],
            [
                'field' => 'email'
            ],
            [
                'field' => 'telephoneF'
            ],
            [
                'field' => 'telephoneP'
            ],
            [
                'field' => 'telephoneT'
            ],
            [
                'field' => 'smsF'
            ],
            [
                'field' => 'smsP'
            ],
            [
                'field' => 'smsT'
            ],
            [
                'field' => 'etiquette'
            ],
            [
                'field' => 'demenagement'
            ],
            [
                'field' => 'dateDemenagement'
            ],
            [
                'field' => 'facture'
            ],
            [
                'field' => 'grilleTarif'
            ],
            [
                'field' => 'ribTit'
            ],
            [
                'field' => 'ribDom'
            ],
            [
                'field' => 'iban'
            ],
            [
                'field' => 'bic'
            ],
            [
                'field' => 'x'
            ],
            [
                'field' => 'y'
            ],
            [
                'field' => 'userId'
            ],
            [
                'field' => 'note'
            ]
        ],
        'from' => [
            'table' => 'responsables', // obligatoire mais peut être une vue
            'type' => 'table', // optionnel, 'table' par défaut
            'alias' => 'res'
        ], // optionnel
        'join' => [
            [
                'table' => 'communes', // obligatoire mais peut être une vue
                'type' => 'table', // optionnel, 'table' par défaut
                'alias' => 'com', // optionnel
                'relation' => 'res.communeId = com.communeId', // obligatoire
                'fields' => [
                    [
                        'field' => 'nom',
                        'alias' => 'commune'
                    ],
                    [
                        'field' => 'inscriptionenligne'
                    ],
                    [
                        'field' => 'paiementenligne'
                    ]
                ]
            ],
            [
                'table' => 'eleves', // obligatoire mais peut être une vue
                'type' => 'table', // optionnel, 'table' par défaut
                'alias' => 'ele', // optionnel
                'relation' => 'res.responsableId = ele.responsable1Id Or res.responsableId = ele.responsable2Id Or res.responsableId = ele.responsableFId', // obligatoire
                'fields' => [
                    [
                        'expression' => [
                            'value' => 'count(ele.eleveId)',
                            'type' => 'bigint(21)'
                        ],
                        'alias' => 'nbEleves'
                    ]
                ],
                'jointure' => \Zend\Db\Sql\Select::JOIN_LEFT
            ]
        ],
        'group' => [
            [
                'table' => 'res',
                'field' => 'responsableId'
            ]
        ],
        'order' => [
            'nomSA',
            'prenomSA',
            'commune'
        ]
    ]
];