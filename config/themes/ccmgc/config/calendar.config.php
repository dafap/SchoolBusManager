<?php
/**
 * Liste des fiches à créer pour une nouvelle année scolaire
 *
 * La numérotation ordinal prend une nouvelle dizaine à chaque changement de nature.
 * La nature PERM devant être la dernière, elle commence à 101. On adaptera la liste
 * des permanences en la réduisant ou en l'allongeant.
 * Pour les permanences, indiquer le code INSEE de la commune dans le champ 'libelle'.
 * La méthode SbmGestion\Controller\AnneeScolaireController::newAction() fera le reste.
 *
 * @project sbm
 * @package SbmGestion/Model
 * @filesource Modele.inc.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 3 mai 2019
 * @version 2019-2.5.0
 */
return [
    [
        'ordinal' => 1,
        'nature' => 'AS',
        'rang' => 1,
        'libelle' => '%as%',
        'description' => 'Année scolaire %as%',
        'exercice' => 0
    ],
    [
        'ordinal' => 11,
        'nature' => 'INS',
        'rang' => 1,
        'libelle' => 'Période d\'inscription',
        'description' => '%libelle% %as%',
        'exercice' => 0
    ],
    [
        'ordinal' => 21,
        'nature' => 'VACA',
        'rang' => 1,
        'libelle' => 'Vacances de Toussaint',
        'description' => 'Vacances de Toussaint %as%',
        'exercice' => 0
    ],
    [
        'ordinal' => 22,
        'nature' => 'VACA',
        'rang' => 2,
        'libelle' => 'Vacances de Noël',
        'description' => 'Vacances de Noël %as%',
        'exercice' => 0
    ],
    [
        'ordinal' => 23,
        'nature' => 'VACA',
        'rang' => 3,
        'libelle' => 'Vacances d\'hiver',
        'description' => 'Vacances d\'hiver %as%',
        'exercice' => 0
    ],
    [
        'ordinal' => 24,
        'nature' => 'VACA',
        'rang' => 4,
        'libelle' => 'Vacances de printemps',
        'description' => 'Vacances de printemps %as%',
        'exercice' => 0
    ],
    [
        'ordinal' => 101,
        'nature' => 'PERM',
        'rang' => 1,
        'libelle' => '12002',
        'description' => '',
        'exercice' => 0
    ],
    [
        'ordinal' => 102,
        'nature' => 'PERM',
        'rang' => 2,
        'libelle' => '12070',
        'description' => '',
        'exercice' => 0
    ],
    [
        'ordinal' => 103,
        'nature' => 'PERM',
        'rang' => 3,
        'libelle' => '12072',
        'description' => '',
        'exercice' => 0
    ],
    [
        'ordinal' => 104,
        'nature' => 'PERM',
        'rang' => 4,
        'libelle' => '12084',
        'description' => '',
        'exercice' => 0
    ],
    [
        'ordinal' => 105,
        'nature' => 'PERM',
        'rang' => 5,
        'libelle' => '12086',
        'description' => '',
        'exercice' => 0
    ],
    [
        'ordinal' => 106,
        'nature' => 'PERM',
        'rang' => 6,
        'libelle' => '12145',
        'description' => '',
        'exercice' => 0
    ],
    [
        'ordinal' => 107,
        'nature' => 'PERM',
        'rang' => 7,
        'libelle' => '12160',
        'description' => '',
        'exercice' => 0
    ],
    [
        'ordinal' => 108,
        'nature' => 'PERM',
        'rang' => 8,
        'libelle' => '12178',
        'description' => '',
        'exercice' => 0
    ],
    [
        'ordinal' => 109,
        'nature' => 'PERM',
        'rang' => 9,
        'libelle' => '12180',
        'description' => '',
        'exercice' => 0
    ],
    [
        'ordinal' => 110,
        'nature' => 'PERM',
        'rang' => 10,
        'libelle' => '12200',
        'description' => '',
        'exercice' => 0
    ],
    [
        'ordinal' => 111,
        'nature' => 'PERM',
        'rang' => 11,
        'libelle' => '12204',
        'description' => '',
        'exercice' => 0
    ],
    [
        'ordinal' => 112,
        'nature' => 'PERM',
        'rang' => 12,
        'libelle' => '48131',
        'description' => '',
        'exercice' => 0
    ],
    [
        'ordinal' => 113,
        'nature' => 'PERM',
        'rang' => 13,
        'libelle' => '12211',
        'description' => '',
        'exercice' => 0
    ],
    [
        'ordinal' => 114,
        'nature' => 'PERM',
        'rang' => 14,
        'libelle' => '12225',
        'description' => '',
        'exercice' => 0
    ],
    [
        'ordinal' => 115,
        'nature' => 'PERM',
        'rang' => 15,
        'libelle' => '12293',
        'description' => '',
        'exercice' => 0
    ]
];