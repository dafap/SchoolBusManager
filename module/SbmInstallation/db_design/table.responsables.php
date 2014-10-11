<?php
/**
 * Structure de la table des `responsables`
 *
 * Découpage en `eleves`, `scolarites` et `responsables`
 * 
 * @project sbm
 * @package module/SbmInstallation/config/db_design
 * @filesource table.responsables.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 10 oct. 2014
 * @version 2014-1
 */
return array(
    'name' => 'responsables',
    'type' => 'table',
    'drop' => true,
    'edit_entity' => true,
    'add_data' => true,
    'structure'=> array(
        'fields' => array(
            'responsableId' => 'int(11) NOT NULL AUTO_INCREMENT',
            'selection' => 'tinyint(1) UNSIGNED NOT NULL DEFAULT "0"',
            'dateCreation' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'dateModification' => 'datetime NOT NULL DEFAULT "1900-01-01 00:00:00"',
            'nature' => 'tinyint(1) NOT NULL DEFAULT "0"',
            'titre' => 'varchar(20) NOT NULL DEFAULT "M."',
            'nom' => 'varchar(30) NOT NULL',
            'nomSA' => 'varchar(30) NOT NULL',
            'prenom' => 'varchar(30) NOT NULL DEFAULT ""',
            'prenomSA' => 'varchar(30) NOT NULL DEFAULT ""',
            'adresseL1' => 'varchar(38) NOT NULL',
            'adresseL2' => 'varchar(38) NOT NULL DEFAULT ""',
            'codePostal' => 'varchar(5) NOT NULL',
            'communeId' => 'varchar(6) NOT NULL',
            'ancienAdresseL1' => 'varchar(30) DEFAULT NULL',
            'ancienAdresseL2' => 'varchar(30) DEFAULT NULL',
            'ancienCodePostal' => 'varchar(5) DEFAULT NULL',
            'ancienCommuneId' => 'varchar(6) DEFAULT NULL',
            'email' => 'varchar(80) NOT NULL DEFAULT ""',
            'telephoneF' => 'varchar(10) NOT NULL DEFAULT ""',
            'telephoneP' => 'varchar(10) NOT NULL DEFAULT ""',
            'telephoneT' => 'varchar(10) NOT NULL DEFAULT ""',
            'etiquette' => 'tinyint(1) UNSIGNED NOT NULL DEFAULT "0"',
            'demenagement' => 'tinyint(1) UNSIGNED NOT NULL DEFAULT "0"',
            'dateDemenagement' => 'date NOT NULL DEFAULT "1900-01-01"',
            'facture' => 'tinyint(1) UNSIGNED NOT NULL DEFAULT "1"',
            'grilleTarif' => 'int(4) NOT NULL DEFAULT "1"',
            'Rib_tit' => 'varchar(32) DEFAULT NULL',
            'Rib_dom' => 'varchar(24) DEFAULT NULL',
            'Iban' => 'varchar(34) DEFAULT NULL',
            'Bic' => 'varchar(11) DEFAULT NULL'
        ),
        'primary_key' => array('responsableId',),
        'engine' => 'InnoDB',
        'charset' => 'utf8',
        'collate' => 'utf8_unicode_ci',
    ),
    'data' => include __DIR__ . '/data/data.responsables.php'
); 