<?php
/**
 * Liste des fiches à créer pour une nouvelle année scolaire
 *
 * @project sbm
 * @package SbmGestion/Model
 * @filesource Modele.inc.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 16 févr. 2015
 * @version 2015-1
 */
return array(
    'annee-scolaire' => array(
        'modele' => array(
            array(
                'ordinal' => 1,
                'nature' => 'AS',
                'rang' => 1,
                'libelle' => '%as%',
                'description' => 'Année scolaire %as%',
                'exercice' => 0
            ),
            array(
                'ordinal' => 2,
                'nature' => 'INS',
                'rang' => 1,
                'libelle' => 'Période d\'inscription',
                'description' => '%libelle% %as%',
                'exercice' => 0
            ),
            array(
                'ordinal' => 3,
                'nature' => 'PERM',
                'rang' => 1,
                'libelle' => 'Permanence pour AUBIN',
                'description' => 'Aubin le',
                'exercice' => 0
            ),
            array(
                'ordinal' => 4,
                'nature' => 'PERM',
                'rang' => 2,
                'libelle' => 'Permanence pour CRANSAC',
                'description' => 'Cransac le',
                'exercice' => 0
            ),
            array(
                'ordinal' => 5,
                'nature' => 'PERM',
                'rang' => 3,
                'libelle' => 'Permanence pour DECAZEVILLE',
                'description' => 'Decazeville le',
                'exercice' => 0
            ),
            array(
                'ordinal' => 6,
                'nature' => 'PERM',
                'rang' => 4,
                'libelle' => 'Permanence pour FIRMI',
                'description' => 'Firmi le',
                'exercice' => 0
            ),
            array(
                'ordinal' => 7,
                'nature' => 'PERM',
                'rang' => 5,
                'libelle' => 'Permanence pour VIVIEZ',
                'description' => 'Viviez le',
                'exercice' => 0
            ),
            array(
                'ordinal' => 8,
                'nature' => 'VACA',
                'rang' => 1,
                'libelle' => 'Vacances de Toussaint',
                'description' => 'Vacances de Toussaint %as%',
                'exercice' => 0
            ),
            array(
                'ordinal' => 9,
                'nature' => 'VACA',
                'rang' => 2,
                'libelle' => 'Vacances de Noël',
                'description' => 'Vacances de Noël %as%',
                'exercice' => 0
            ),
            array(
                'ordinal' => 10,
                'nature' => 'VACA',
                'rang' => 3,
                'libelle' => 'Vacances d\'hiver',
                'description' => 'Vacances d\'hiver %as%',
                'exercice' => 0
            ),
            array(
                'ordinal' => 11,
                'nature' => 'VACA',
                'rang' => 4,
                'libelle' => 'Vacances de printemps',
                'description' => 'Vacances de printemps %as%',
                'exercice' => 0
            ),
        )
    )
); 