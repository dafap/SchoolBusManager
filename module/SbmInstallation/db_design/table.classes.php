<?php
/**
 * Structure de la table des `classes`
 *
 *
 * @project sbm
 * @package SbmInstallation/db_design
 * @filesource table.classes.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 22 janv. 2014
 * @version 2014-1
 */


/**
 * *************************************************************************
 * structure de la table 'classes' *
 * Table MyISAM encodée utf8 *
 * Description des champs *
 * - classeId est un auto-incrément *
 * - nom est un texte de 30 c maxi *
 * - aliasCG est le nom utilisé au CG *
 * - niveau indique dans quels établissements cette classe peut se trouver *
 * Les niveaux sont établis en composant par "Et binaire" les valeurs : *
 * 1 pour maternelle *
 * 2 pour élémentaire *
 * 4 pour premier cycle du second degré (collège, segpa ...) *
 * 8 pour second cycle du second degré (lycée, lp ...) *
 * 16 pour classes après bac de lycée et lp (bts, cpge ...) *
 * 32 pour enseignement supérieur (iut, université ...) *
 * 64 pour autres (stagiaires de la formation prof, apprentis ...) *
 * 128 inutilisé *
 * 255 pour tous les niveaux *
 * *************************************************************************
 */
return array(
    'name' => 'classes',
    'drop' => false,
    'edit_entity' => false,
    'add_data' => false,
    'type' => 'table',
    'structure'=> array(
        'fields' => array(
            'classeId' => 'int(11) NOT NULL AUTO_INCREMENT',
            'nom' => 'varchar(30) NOT NULL',
            'aliasCG' => 'varchar(30) NULL DEFAULT NULL',
            'niveau' => 'tinyint(3) UNSIGNED NOT NULL DEFAULT "255"',
        ),
        'primary_key' => array('classeId',),
        'engine' => 'InnoDb',
        'charset' => 'utf8',
        'collate' => 'utf8_unicode_ci',
    ),
    //'data' => include __DIR__ . '/data/data.classes.php',
    //'data' => array('after' => array(), 'include' => __DIR__ . '/data/data.classes.php')
    'data' => __DIR__ . '/data/data.classes.php'
);
