<?php
/**
 * Liste des fiches à créer pour une nouvelle année scolaire
 *
 * @project sbm
 * @package SbmGestion/Model
 * @filesource Modele.inc.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 15 juin 2021
 * @version 2021-2.5.11
 */
return [
    'annee-scolaire' => [
        'modele' => [
            [
                'ordinal' => 1,
                'nature' => 'AS',
                'rang' => 1,
                'libelle' => '%as%',
                'description' => 'Année scolaire %as%',
                'exercice' => 0
            ],
            [
                'ordinal' => 2,
                'nature' => 'INS',
                'rang' => 1,
                'libelle' => 'Période d\'inscription',
                'description' => '%libelle% %as%',
                'exercice' => 0
            ]
        ]
    ]
];