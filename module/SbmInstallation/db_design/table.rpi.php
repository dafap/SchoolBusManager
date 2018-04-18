<?php
/**
 * Structure de la table des `rpi`
 *
 *
 * @project sbm
 * @package SbmInstallation/db_design
 * @filesource table.rpi.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 9 avr. 2018
 * @version 2018-2.4.0
 */

/**
 * *************************************************************************
 * structure de la table 'classes' 
 * Table MyISAM encodée utf8 
 * Description des champs 
 * - rpiId est un auto-incrément 
 * - nom est un texte de 10 c maxi 
 * - libelle est le nom detaillé 
 * - niveau indique quels niveaux sont concernés 
 * Les niveaux sont établis en composant par "Et binaire" les valeurs : 
 * - 1 pour maternelle 
 * - 2 pour élémentaire 
 * - 3 pour maternelle et élémentaire (primaire)
 * *************************************************************************
 */
return [
    'name' => 'rpi',
    'drop' => true,
    'edit_entity' => true,
    'add_data' => true,
    'type' => 'table',
    'structure' => [
        'fields' => [
            'rpiId' => 'int(11) NOT NULL AUTO_INCREMENT',
            'nom' => 'varchar(30) NOT NULL',
            'libelle' => 'varchar(50) NULL DEFAULT NULL',
            'niveau' => 'tinyint(3) UNSIGNED NOT NULL DEFAULT "3"',
            'selection' => 'tinyint(1) NOT NULL DEFAULT "0"'
        ],
        'primary_key' => [
            'rpiId'
        ],
        'engine' => 'InnoDb',
        'charset' => 'utf8',
        'collate' => 'utf8_unicode_ci'
    ],
    'data' => __DIR__ . '/data/data.rpi.php'
];