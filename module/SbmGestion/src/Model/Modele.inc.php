<?php
/**
 * Liste des fiches à créer pour une nouvelle année scolaire
 *
 * @project sbm
 * @package SbmGestion/src/Model
 * @filesource Modele.inc.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 18 mars 2020
 * @version 2020-2.6.0
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