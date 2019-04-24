<?php
/**
 * Structure de la vue `libelles-modes-de-paiement`
 *
 * Requête donnant la liste des (code, libelle) pour les modes de paiement ouverts (nature = ModeDePaiement ; ouvert = 1)
 * 
 * @project sbm
 * @package SbmInstallation/db_design
 * @filesource vue.libelles-modes-de-paiement.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 7 avr. 2018
 * @version 2018-2.4.0
 */
return [
    'name' => 'libelles-modes-de-paiement',
    'type' => 'vue',
    'drop' => true,
    'edit_entity' => true,
    'structure' => [
        'fields' => [
            [
                'field' => 'code'
            ],
            [
                'field' => 'libelle'
            ]
        ],
        'from' => [
            'table' => 'libelles', // obligatoire mais peut être une vue
            'type' => 'system', // optionnel, 'table' par défaut
            'alias' => 'mode'
        ], // optionnel
        'where' => [
            [
                'literal',
                'nature="ModeDePaiement"'
            ],
            [
                'literal',
                'ouvert=1'
            ]
        ]
    ]
];