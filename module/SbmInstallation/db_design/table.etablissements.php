<?php
/**
 * Structure de la table des `etablissements`
 *
 *
 * @project sbm
 * @package SbmInstallation/db_design
 * @filesource table.etablissements.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 5 févr. 2014
 * @version 2015-2
 */
return array(
    'name' => 'etablissements',
    'type' => 'table',
    'drop' => false,
    'edit_entity' => false, 
    'add_data' => false, 
    'structure' => array(
        'fields' => array(
            'etablissementId' => 'char(8) NOT NULL',
            'selection' => 'tinyint(1) NOT NULL DEFAULT "0"',
            'nom' => 'varchar(45) NOT NULL',
            'alias' => 'varchar(30) NOT NULL DEFAULT ""',
            'aliasCG' => 'varchar(50) NOT NULL DEFAULT ""',
            'adresse1' => 'varchar(38) NOT NULL DEFAULT ""',
            'adresse2' => 'varchar(38) NOT NULL DEFAULT ""',
            'codePostal' => 'varchar(5) NOT NULL',
            'communeId' => 'varchar(6) NOT NULL',
            'niveau' => 'tinyint(3) unsigned NOT NULL DEFAULT "255"',
            'statut' => 'tinyint(1) NOT NULL DEFAULT "1"',
            'visible' => 'tinyint(1) NOT NULL DEFAULT "1"',
            'desservie' => 'tinyint(1) NOT NULL DEFAULT "1"',
            'regrPeda' => 'tinyint(1) NOT NULL DEFAULT "0"',
            'rattacheA' => 'varchar(8) NOT NULL DEFAULT ""',
            'telephone' => 'varchar(10) NOT NULL DEFAULT ""',
            'fax' => 'varchar(10) NOT NULL DEFAULT ""',
            'email' => 'varchar(80) NOT NULL DEFAULT ""',
            'directeur' => 'varchar(30) NOT NULL DEFAULT ""',
            'jOuverture' => 'tinyint(3) unsigned NOT NULL DEFAULT "127"',
            'hMatin' => 'varchar(5) NOT NULL DEFAULT ""',
            'hMidi' => 'varchar(5) NOT NULL DEFAULT ""',
            'hAMidi' => 'varchar(5) NOT NULL DEFAULT ""',
            'hSoir' => 'varchar(5) NOT NULL DEFAULT ""',
            'hGarderieOMatin' => 'varchar(5) NOT NULL DEFAULT ""',
            'hGarderieFMidi' => 'varchar(5) NOT NULL DEFAULT ""',
            'hGarderieFSoir' => 'varchar(5) NOT NULL DEFAULT ""',
            'x' => 'decimal(18,10) NOT NULL DEFAULT "0.0"',
            'y' => 'decimal(18,10) NOT NULL DEFAULT "0.0"',
            'geopt' => 'GEOMETRY',
        ),
        'primary_key' => array(
            'etablissementId'
        ),
        'foreign key' => array(
            array(
                'key' => 'communeId',
                'references' => array(
                    'table' => 'communes',
                    'fields' => array(
                        'communeId'
                    ),
                    'on' => array(
                        'update' => 'CASCADE',
                        'delete' => 'RESTRICT'
                    )
                )
            )
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
    
    // 'data' => include __DIR__ . '/data/data.etablissements.php'
    // 'data' => array('after' => array('communes'),'include' => __DIR__ . '/data/data.etablissements.php')
    'data' => __DIR__ . '/data/data.etablissements.php'
);