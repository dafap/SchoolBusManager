<?php
/**
 * Structure de la table des `etablissements`
 *
 *
 * @project sbm
 * @package module/SbmInstallation/config/db_design
 * @filesource table.etablissements.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 5 févr. 2014
 * @version 2014-1
 */
return array(
    'name' => 'etablissements',
    'type' => 'table',
    'drop' => false,
    'edit_entity' => false, // si false, on ne touche pas à la structure dans Create::createOrAlterEntity() - true par défaut
    'add_data' => false, // si false, on ne fait rien dans Create::addData() - true par défaut ; sans effet sur une vue
    'structure' => array(
        'fields' => array(
            'etablissementId' => 'varchar(8) NOT NULL',
            'nom' => 'varchar(45) NOT NULL',
            'alias' => 'varchar(30) NOT NULL DEFAULT ""',
            'aliasCG' => 'varchar(50) NOT NULL DEFAULT ""',
            'adresse1' => 'varchar(38) NOT NULL DEFAULT ""',
            'adresse2' => 'varchar(38) NOT NULL DEFAULT ""',
            'codePostal' => 'varchar(5) NOT NULL',
            'communeId' => 'varchar(6) NOT NULL',
            'longitude' => 'varchar(20) NOT NULL DEFAULT ""',
            'latitude' => 'varchar(20) NOT NULL DEFAULT ""',
            'niveau' => 'tinyint(3) unsigned NOT NULL DEFAULT "255"',
            'statut' => 'tinyint(1) NOT NULL DEFAULT "1"',
            'visible' => 'tinyint(1) NOT NULL DEFAULT "1"',
            'regrPeda' => 'tinyint(1) NOT NULL DEFAULT "0"',
            'rattacheA' => 'varchar(8) NOT NULL DEFAULT ""',
            'telephone' => 'varchar(10) NOT NULL DEFAULT ""',
            'fax' => 'varchar(10) NOT NULL DEFAULT ""',
            'email' => 'varchar(80) NOT NULL DEFAULT ""',
            'directeur' => 'varchar(30) NOT NULL DEFAULT ""',
            'hMatin' => 'varchar(5) NOT NULL DEFAULT ""',
            'hMidi' => 'varchar(5) NOT NULL DEFAULT ""',
            'hAMidi' => 'varchar(5) NOT NULL DEFAULT ""',
            'hSoir' => 'varchar(5) NOT NULL DEFAULT ""',
            'jOuverture' => 'tinyint(3) unsigned NOT NULL DEFAULT "127"',
        ),
        'primary_key' => array(
            'etablissementId'
        ),
        // 'keys' => array(
        // 'noms' => array('fields' => array('nom',),),
        // 'membres_alpha' => array('fields' => array('membre',),),
        // 'desservies_alpha' => array('fields' => array('desservie',),),
        // ),
        'engine' => 'InnoDB',
        'charset' => 'utf8',
        'collate' => 'utf8_unicode_ci'
    ),
    'data' => include __DIR__ . '/data/data.etablissements.php'
);