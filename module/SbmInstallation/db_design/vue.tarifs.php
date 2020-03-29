<?php
/**
 * Structure de la vue `tarifs`
 *
 * @project sbm
 * @package SbmInstallation/db_design
 * @filesource vue.tarifs.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 29 mars 2020
 * @version 2020-2.6.0
 */
return [
    'name' => 'tarifs',
    'type' => 'vue',
    'drop' => true,
    'edit_entity' => true,
    'structure' => [
        'fields' => [
            [
                'field' => 'tarifId'
            ],
            [
                'field' => 'selection'
            ],
            [
                'field' => 'montant'
            ],
            [
                'field' => 'nom'
            ],
            [
                'field' => 'duplicata'
            ],
            [
                'field' => 'grille'
            ],
            [
                'field' => 'grille',
                'alias' => 'grilleTarif'
            ],
            [
                'field' => 'reduit'
            ],
            [
                'field' => 'mode'
            ],
            [
                'field' => 'seuil'
            ],
            [
                'field' => 'millesime'
            ]
        ],
        'from' => [
            'table' => 'tarifs', // obligatoire mais peut être une vue
            'type' => 'table' // optionnel, 'table' par défaut
        ],
        'order' => 'duplicata, grilleTarif, reduit, seuil'
    ]
];