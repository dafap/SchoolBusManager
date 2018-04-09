<?php
/**
 * Structure de la vue `paiements`
 *
 * La requête reprend toutes les colonnes de la table `paiements` ainsi que les colonnes `responsable`, `caisse` et `modeDePaiement` 
 * 
 * @project sbm
 * @package package_name
 * @filesource vue.paiements.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 7 avr. 2018
 * @version 2018-2.4.0
 */
return [
    'name' => 'paiements',
    'type' => 'vue',
    'drop' => true,
    'edit_entity' => true,
    'structure' => [
        'fields' => [
            [
                'field' => 'paiementId'
            ],
            [
                'field' => 'selection'
            ],
            [
                'field' => 'dateBordereau'
            ],
            [
                'field' => 'dateDepot'
            ],
            [
                'field' => 'datePaiement'
            ],
            [
                'field' => 'dateValeur'
            ],
            [
                'field' => 'responsableId'
            ],
            [
                'field' => 'anneeScolaire'
            ],
            [
                'field' => 'exercice'
            ],
            [
                'field' => 'montant'
            ],
            [
                'field' => 'codeModeDePaiement'
            ],
            [
                'field' => 'codeCaisse'
            ],
            [
                'field' => 'banque'
            ],
            [
                'field' => 'titulaire'
            ],
            [
                'field' => 'reference'
            ]
        ],
        'from' => [
            'table' => 'paiements', // obligatoire mais peut être une vue
            'type' => 'table', // optionnel, 'table' par défaut
            'alias' => 'pai'
        ], // optionnel
        'join' => [
            [
                'table' => 'responsables',
                'type' => 'table',
                'alias' => 'res',
                'relation' => 'pai.responsableId = res.responsableId',
                'fields' => [
                    [
                        'expression' => [
                            'value' => "CONCAT(res.nom, ' ', res.prenom)",
                            'type' => 'varchar(61)'
                        ],
                        'alias' => 'responsable'
                    ]
                ]
            ],
            [
                'table' => 'libelles-caisses',
                'type' => 'vue',
                'alias' => 'cai',
                'relation' => 'pai.codeCaisse = cai.code',
                'fields' => [
                    [
                        'field' => 'libelle',
                        'alias' => 'caisse'
                    ]
                ]
            ],
            [
                'table' => 'libelles-modes-de-paiement',
                'type' => 'vue',
                'alias' => 'mod',
                'relation' => 'pai.codeModeDePaiement = mod.code',
                'fields' => [
                    [
                        'field' => 'libelle',
                        'alias' => 'modeDePaiement'
                    ]
                ]
            ]
        ]
    ]
];