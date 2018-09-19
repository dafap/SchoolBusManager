<?php
/**
 * Structure de la table des `communes`
 *
 *
 * @project sbm
 * @package SbmInstallation/db_design
 * @filesource table.communes.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 25 janv. 2014
 * @version 2014-1
 */
ini_set('memory_limit', '-1');

return array(
    'name' => 'communes',
    'type' => 'table',
    'drop' => false,
    'edit_entity' => false, // si false, on ne touche pas à la structure dans Create::createOrAlterEntity() - true par défaut
    'add_data' => false, // si false, on ne fait rien dans Create::addData() - true par défaut ; sans effet sur une vue
    'structure' => array(
        'fields' => array(
            'communeId' => 'varchar(6) NOT NULL',
            'nom' => 'varchar(45) NOT NULL',
            'nom_min' => 'varchar(45) NOT NULL',
            'alias' => 'varchar(30) DEFAULT NULL',
            'alias_min' => 'varchar(30) DEFAULT NULL',
            'aliasCG' => 'varchar(45) DEFAULT NULL',
            'codePostal' => 'varchar(5) NOT NULL',
            'departement' => 'varchar(3) NOT NULL',
            'canton' => 'varchar(5) NOT NULL',
            'membre' => 'tinyint(1) NOT NULL DEFAULT "0"',
            'desservie' => 'tinyint(1) NOT NULL DEFAULT "0"',
            'visible' => 'tinyint(1) NOT NULL DEFAULT "0"',
            'selection' => 'tinyint(1) NOT NULL DEFAULT "0"',
            'population' => 'mediumint(8) UNSIGNED NOT NULL DEFAULT "0"',
        ),
        'primary_key' => array('communeId',),
//        'keys' => array(
//            'noms' => array('fields' => array('nom',),),
//            'membres_alpha' => array('fields' => array('membre',),),
//            'desservies_alpha' => array('fields' => array('desservie',),),
//        ),
        'engine' => 'InnoDb',
        'charset' => 'utf8',
        'collate' => 'utf8_unicode_ci',
    ),
    //'data' => include __DIR__ . '/data/data.communes.php',
    //'data' => array('after' =>[], 'include' => __DIR__ . '/data/data.communes.php')
    'data' => __DIR__ . '/data/data.communes.php',
);