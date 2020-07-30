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
 * @date 30 juil. 2020
 * @version 2020-2.6.0
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
                'field' => 'adresseL3'
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
                'field' => 'ancienAdresseL3'
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
                'field' => 'id_tra'
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
                        'field' => 'alias',
                        'alias' => 'lacommune' // commune en maj avec LE, LA ou LES, apostrophes et tirets
                    ],
                    [
                        'field' => 'alias_laposte',
                        'alias' => 'laposte'
                    ],
                    [
                        'field' => 'inscriptionenligne'
                    ],
                    [
                        'field' => 'paiementenligne'
                    ]
                ]
            ]
        ],
        'order' => [
            'nomSA',
            'prenomSA',
            'commune'
        ]
    ]
];