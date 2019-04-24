<?php
/**
 * Structure de la vue `lots`
 *
 * @project sbm
 * @package SbmInstallation/db_design
 * @filesource vue.lots.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 25 mars 2019
 * @version 2019-2.5.0
 */
return [
    'name' => 'lots',
    'type' => 'vue',
    'drop' => true,
    'edit_entity' => true,
    'structure' => [
        'fields' => [
            [
                'field' => 'lotId'
            ],
            [
                'field' => 'selection'
            ],
            [
                'field' => 'actif'
            ],
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
        ],
        'from' => [
            'table' => 'lots', // obligatoire mais peut être une vue
            'type' => 'table', // optionnel, 'table' par défaut
            'alias' => 'lots'
        ], // optionnel
        'join' => [
            [
                'table' => 'transporteurs', // obligatoire mais peut être une vue
                'type' => 'table', // optionnel, 'table' par défaut
                'alias' => 'tra', // optionnel
                'relation' => 'tra.transporteurId = lots.transporteurId', // obligatoire
                'fields' => [
                    [
                        'field' => 'nom',
                        'alias' => 'titulaire'
                    ]
                ],
                'jointure' => \Zend\Db\Sql\Select::JOIN_LEFT
            ]
        ],
        'order' => 'nom'
    ]
];