<?php
/**
 * Structure de la table des `eleves`
 *
 * Découpage en `eleves`, `scolarites` et `responsables`
 * 
 * @project sbm
 * @package module/SbmInstallation/config/db_design
 * @filesource table.eleves.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 10 oct. 2014
 * @version 2014-1
 */
return array(
    'name' => 'eleves',
    'type' => 'table',
    'drop' => true,
    'edit_entity' => true,
    'add_data' => true,
    'structure' => array(
        'fields' => array(
            'eleveId' => 'int(11) NOT NULL AUTO_INCREMENT',
            'selection' => 'tinyint(1) NOT NULL DEFAULT "0"',
            'dateCreation' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'dateModification' => 'datetime NOT NULL DEFAULT "1900-01-01 00:00:00"',
            'nom' => 'varchar(30) NOT NULL',
            'nomSA' => 'varchar(30) NOT NULL',
            'prenom' => 'varchar(30) NOT NULL',
            'prenomSA' => 'varchar(30) NOT NULL',
            'dateN' => 'date NOT NULL',
            'numero' => 'int(11) NOT NULL DEFAULT "-1"',
            'responsable1Id' => 'int(11) UNSIGNED NOT NULL DEFAULT "0"',
            'responsable2Id' => 'int(11) UNSIGNED NOT NULL DEFAULT "0"',
            'responsableFId' => 'int(11) UNSIGNED NOT NULL DEFAULT "0"',
            'note' => 'text'
        ),
        'primary_key' => array(
            'eleveId'
        ),
        'engine' => 'InnoDB',
        'charset' => 'utf8',
        'collate' => 'utf8_unicode_ci'
    ),
    'data' => include __DIR__ . '/data/data.eleves.php'
); 