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
 * Remarque: à partir de la version 2021-2.5.11 on réduit la liste aux natures AS et INS
 * car il n'y a plus de permanence pour la remise des cartes.
 * Si nécessaire, voir les versions précédentes sur GITHUB pour revenir à la gestion des
 * dates de permanences.
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
    ]
];