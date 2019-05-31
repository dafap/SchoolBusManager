<?php
/**
 * Structure de la vue `tarifs`
 *
 * @project sbm
 * @package SbmInstallation/db_design
 * @filesource vue.tarifs.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 31 mai 2019
 * @version 2019-2.5.0
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
                'field' => 'rythme'
            ],
            [
                'field' => 'grille'
            ],
            [
                'field' => 'grille',
                'alias' => 'grilleTarif'
            ],
            [
                'field' => 'mode'
            ],
            [
                'field' => 'seuil'
            ]
        ],
        'from' => [
            'table' => 'tarifs', // obligatoire mais peut être une vue
            'type' => 'table', // optionnel, 'table' par défaut
        ],
        'order' => 'grilleTarif, montant'
    ]
];