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
 * @date 24 fév. 2019
 * @version 2019-2.5.0
 */
use SbmBase\Model\StdLib;

ini_set('memory_limit', '-1');

return [
    'name' => 'communes',
    'type' => 'table',
    'drop' => false,
    'edit_entity' => false, // si false, on ne touche pas à la structure dans
                             // Create::createOrAlterEntity() - true par défaut
    'add_data' => false, // si false, on ne fait rien dans Create::addData() - true par défaut ;
                          // sans effet sur une vue
    'structure' => [
        'fields' => [
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
            'inscriptionenligne' => 'tinyint(1) NOT NULL DEFAULT "0"',
            'paiementenligne' => 'tinyint(1) NOT NULL DEFAULT "0"',
            'selection' => 'tinyint(1) NOT NULL DEFAULT "0"',
            'population' => 'mediumint(8) UNSIGNED NOT NULL DEFAULT "0"'
        ],
        'primary_key' => [
            'communeId'
        ],
        'engine' => 'InnoDb',
        'charset' => 'utf8',
        'collate' => 'utf8_unicode_ci'
    ],
    'data' => StdLib::concatPath(StdLib::findParentPath(__DIR__, 'data/data'),
        'data.communes.php')
];