<?php
/**
 * Liste des fiches à créer pour une nouvelle année scolaire
 *
 * @project sbm
 * @package SbmGestion/Model
 * @filesource Modele.inc.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 27 juin 2017
 * @version 2017-2.3.4
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
            ],
            [
                'ordinal' => 3,
                'nature' => 'PERM',
                'rang' => 1,
                'libelle' => 'LANGON',
                'description' => 'Langon le',
                'exercice' => 0
            ],
            [
                'ordinal' => 4,
                'nature' => 'PERM',
                'rang' => 2,
                'libelle' => 'CASTETS-EN-DORTHE',
                'description' => 'Castets-en-Dorthe le',
                'exercice' => 0
            ],
            [
                'ordinal' => 5,
                'nature' => 'PERM',
                'rang' => 3,
                'libelle' => 'SAINT-PARDON-DE-CONQUES',
                'description' => 'Castets-en-Dorthe le',
                'exercice' => 0
            ],
            [
                'ordinal' => 6,
                'nature' => 'PERM',
                'rang' => 4,
                'libelle' => 'BIEUJAC',
                'description' => 'Bieujac le',
                'exercice' => 0
            ],
            [
                'ordinal' => 7,
                'nature' => 'PERM',
                'rang' => 5,
                'libelle' => 'FARGUES',
                'description' => 'Fargues le',
                'exercice' => 0
            ],
            [
                'ordinal' => 8,
                'nature' => 'PERM',
                'rang' => 6,
                'libelle' => 'BOMMES',
                'description' => 'Bommes le',
                'exercice' => 0
            ],
            [
                'ordinal' => 9,
                'nature' => 'VACA',
                'rang' => 1,
                'libelle' => 'Vacances de Toussaint',
                'description' => 'Vacances de Toussaint %as%',
                'exercice' => 0
            ],
            [
                'ordinal' => 10,
                'nature' => 'VACA',
                'rang' => 2,
                'libelle' => 'Vacances de Noël',
                'description' => 'Vacances de Noël %as%',
                'exercice' => 0
            ],
            [
                'ordinal' => 11,
                'nature' => 'VACA',
                'rang' => 3,
                'libelle' => 'Vacances d\'hiver',
                'description' => 'Vacances d\'hiver %as%',
                'exercice' => 0
            ],
            [
                'ordinal' => 12,
                'nature' => 'VACA',
                'rang' => 4,
                'libelle' => 'Vacances de printemps',
                'description' => 'Vacances de printemps %as%',
                'exercice' => 0
            ]
        ]
    ]
]; 