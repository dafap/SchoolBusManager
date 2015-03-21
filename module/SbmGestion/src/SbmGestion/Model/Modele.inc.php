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
                'nature' => 'VACA',
                'rang' => 1,
                'libelle' => 'Vacances de Toussaint',
                'description' => 'Vacances de Toussaint %as%',
                'exercice' => 0
            ),
            array(
                'ordinal' => 3,
                'nature' => 'VACA',
                'rang' => 2,
                'libelle' => 'Vacances de Noël',
                'description' => 'Vacances de Noël %as%',
                'exercice' => 0
            ),
            array(
                'ordinal' => 4,
                'nature' => 'VACA',
                'rang' => 3,
                'libelle' => 'Vacances d\'hiver',
                'description' => 'Vacances d\'hiver %as%',
                'exercice' => 0
            ),
            array(
                'ordinal' => 5,
                'nature' => 'VACA',
                'rang' => 4,
                'libelle' => 'Vacances de printemps',
                'description' => 'Vacances de printemps %as%',
                'exercice' => 0
            ),
            array(
                'ordinal' => 6,
                'nature' => 'PER',
                'rang' => 1,
                'libelle' => 'T1',
                'description' => '1er trimestre %as%',
                'exercice' => '%ex1%'
            ),
            array(
                'ordinal' => 7,
                'nature' => 'PER',
                'rang' => 2,
                'libelle' => 'T2',
                'description' => '2ème trimestre %as%',
                'exercice' => '%ex2%'
            ),
            array(
                'ordinal' => 8,
                'nature' => 'PER',
                'rang' => 3,
                'libelle' => 'T3',
                'description' => '3ème trimestre %as%',
                'exercice' => '%ex2%'
            ),
            array(
                'ordinal' => 9,
                'nature' => 'FACA',
                'rang' => 1,
                'libelle' => 'Facturation annuelle',
                'description' => '%libelle% %as%',
                'exercice' => '%ex1%'
            ),
            array(
                'ordinal' => 10,
                'nature' => 'FACT',
                'rang' => 1,
                'libelle' => 'Facturation T1',
                'description' => '%libelle% %as%',
                'exercice' => '%ex1%'
            ),
            array(
                'ordinal' => 11,
                'nature' => 'FACT',
                'rang' => 2,
                'libelle' => 'Facturation T2',
                'description' => '%libelle% %as%',
                'exercice' => '%ex2%'
            ),
            array(
                'ordinal' => 12,
                'nature' => 'FACT',
                'rang' => 3,
                'libelle' => 'Facturation T3',
                'description' => '%libelle% %as%',
                'exercice' => '%ex2%'
            ),
            array(
                'ordinal' => 13,
                'nature' => 'PREA',
                'rang' => 1,
                'libelle' => 'Prélèvement annuel',
                'description' => '%libelle% %as%',
                'exercice' => '%ex1%'
            ),
            array(
                'ordinal' => 14,
                'nature' => 'PREL',
                'rang' => 1,
                'libelle' => 'Prélèvement T1',
                'description' => '%libelle% %as%',
                'exercice' => '%ex1%'
            ),
            array(
                'ordinal' => 15,
                'nature' => 'PREL',
                'rang' => 2,
                'libelle' => 'Prélèvement T2',
                'description' => '%libelle% %as%',
                'exercice' => '%ex2%'
            ),
            array(
                'ordinal' => 16,
                'nature' => 'PREL',
                'rang' => 3,
                'libelle' => 'Prélèvement T3',
                'description' => '%libelle% %as%',
                'exercice' => '%ex2%'
            ),
            array(
                'ordinal' => 17,
                'nature' => 'RAP',
                'rang' => 1,
                'libelle' => 'Lettre de rappel 1',
                'description' => '%libelle% %as%',
                'exercice' => '%ex1%'
            ),
            array(
                'ordinal' => 18,
                'nature' => 'RAP',
                'rang' => 2,
                'libelle' => 'Lettre de rappel 2',
                'description' => '%libelle% %as%',
                'exercice' => '%ex2%'
            ),
            array(
                'ordinal' => 19,
                'nature' => 'RAP',
                'rang' => 3,
                'libelle' => 'Lettre de rappel 3',
                'description' => '%libelle% %as%',
                'exercice' => '%ex2%'
            ),
            array(
                'ordinal' => 20,
                'nature' => 'INS',
                'rang' => 1,
                'libelle' => 'Période d\'inscription',
                'description' => '%libelle% %as%',
                'exercice' => 0
            )
        )
    )
); 