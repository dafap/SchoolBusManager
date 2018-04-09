<?php
/**
 * Structure de la vue `classes`
 * 
 * @project sbm
 * @package SbmInstallation/db_design
 * @filesource vue.classes.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 7 avr. 2018
 * @version 2018-2.4.0
 */
return [
    'name' => 'classes',
    'type' => 'vue',
    'drop' => true,
    'edit_entity' => true,
    'structure' => [
        'fields' => [
            [
                'field' => 'classeId'
            ],
            [
                'field' => 'selection'
            ],
            [
                'field' => 'nom'
            ],
            [
                'field' => 'aliasCG'
            ],
            [
                'field' => 'niveau'
            ],
            [
                'field' => 'suivantId'
            ]
        ],
        'from' => [
            'table' => 'classes', // obligatoire mais peut être une vue
            'type' => 'table', // optionnel, 'table' par défaut
            'alias' => 'cla'
        ], // optionnel
        'join' => [
            [
                'table' => 'classes', // obligatoire mais peut être une vue
                'type' => 'table', // optionnel, 'table' par défaut
                'alias' => 'sui', // optionnel
                'relation' => 'sui.classeId = cla.suivantId', // obligatoire
                'fields' => [
                    [
                        'field' => 'nom',
                        'alias' => 'suivant'
                    ]
                ],
                'jointure' => \Zend\Db\Sql\Select::JOIN_LEFT
            ]
        ],
        'order' => 'nom'
    ]
]; 