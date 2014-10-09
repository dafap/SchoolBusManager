<?php
/**
 * Structure de la table des `responsables`
 *
 *
 * @project sbm
 * @package module/SbmInstallation/config/db_design
 * @filesource table.responsables.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 17 juin 2014
 * @version 2014-1
 */

return array(
    'name' => 'responsables',
    'type' => 'table',
    'drop' => false,
    'edit_entity' => false,
    'add_data' => false,
    'structure'=> array(
        'fields' => array(
            'responsableId' => 'int(11) NOT NULL AUTO_INCREMENT',
            'titre' => 'varchar(4) NOT NULL DEFAULT "M."',
            'nom' => 'varchar(30) NOT NULL',
            'nomSA' => 'varchar(30) NOT NULL',
            'prenom' => 'varchar(30) NOT NULL',
            'prenomSA' => 'varchar(30) NOT NULL',
            'adressL1' => 'varchar(38) NOT NULL',
            'adressL2' => 'varchar(38) NOT NULL',
            'codePostal' => 'varchar(5) NOT NULL',
            'communeId' => 'varchar(6) NOT NULL',
            'telephone' => 'varchar(10) NOT NULL DEFAULT ""',
            'telephoneC' => 'varchar(10) NOT NULL DEFAULT ""',
            'email' => 'varchar(80) NOT NULL DEFAULT ""',
            'ancienAdressL1' => 'varchar(30) DEFAULT NULL',
            'ancienAdressL2' => 'varchar(30) DEFAULT NULL',
            'ancienCodePostal' => 'varchar(5) DEFAULT NULL',
            'ancienCommuneId' => 'varchar(6) DEFAULT NULL',
            'dateDemenagement' => 'date DEFAULT NULL',
            'dateCreation' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'dateModification' => 'datetime NOT NULL DEFAULT "1900-01-01 00:00:00"',
            'demenagement' => 'tinyint(1) UNSIGNED NOT NULL DEFAULT "0"',
            'facture' => 'tinyint(1) UNSIGNED NOT NULL DEFAULT "1"',
            'grilleTarif' => 'int(4) NOT NULL DEFAULT "1"',
            'selection' => 'tinyint(1) UNSIGNED NOT NULL DEFAULT "0"',
        ),
        'primary_key' => array('responsableId',),
        'engine' => 'InnoDB',
        'charset' => 'utf8',
        'collate' => 'utf8_unicode_ci',
    ),
    'data' => include __DIR__ . '/data/data.responsables.php'
);