<?php
/**
 * Liste des fiches à créer pour une nouvelle année scolaire
 *
 * La numérotation ordinal prend une nouvelle dizaine à chaque changement de nature.
 * Nommeclature :
 * AS   'année scolaire'
 * INS  'inscriptions'
 * VACA 'vacances scolaires'
 * PERM 'permanences'
 *
 * @project sbm
 * @package config/themes/arlysere/config
 * @filesource calendar.config.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 26 déc. 2019
 * @version 2019-2.5.4
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