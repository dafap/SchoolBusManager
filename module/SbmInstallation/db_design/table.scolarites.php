<?php
/**
 * Structure de la table des `eleves`
 *
 * Découpage en `eleves`, `scolarites` et `responsables`
 * 
 * @project sbm
 * @package SbmInstallation
 * @filesource table.scolarites.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 10 oct. 2014
 * @version 2014-1
 */
return array(
    'name' => 'scolarites',
    'type' => 'table',
    'drop' => false,
    'edit_entity' => false,
    'add_data' => false,
    'structure' => array(
        'fields' => array(
            'eleveId' => 'int(11) NOT NULL DEFAULT "0"',
            'millesime' => 'int(4) NOT NULL DEFAULT "0"',
            'selection' => 'tinyint(1) NOT NULL DEFAULT "0"',
            'dateInscription' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'dateModification' => 'datetime NOT NULL DEFAULT "1900-01-01 00:00:00"',
            'etablissementId' => 'int(11) NOT NULL DEFAULT "0"',
            'classeId' => 'int(11) NOT NULL DEFAULT "0"',
            'adresseL1' => 'varchar(38) NOT NULL',
            'adresseL2' => 'varchar(38) NOT NULL DEFAULT ""',
            'communeId' => 'varchar(5) NOT NULL DEFAULT "00000"',
            'dateEtiquette' => 'datetime NOT NULL DEFAULT "1900-01-01 00:00:00"',
            'dateCarte' => 'datetime NOT NULL DEFAULT "1900-01-01 00:00:00"',
            'inscrit' => 'tinyint(1) NOT NULL DEFAULT "1"',
            'gratuit' => 'tinyint(1) NOT NULL DEFAULT "0"',
            'afacturer' => 'tinyint(1) NOT NULL DEFAULT "1"',
            'anneeComplete' => 'tinyint(1) NOT NULL DEFAULT "1"',
            'subvention' => 'tinyint(1) NOT NULL DEFAULT "1"',
            'derogation' => 'tinyint(1) NOT NULL DEFAULT "0"',
            'dateDebut' => 'date NOT NULL',
            'dateFin' => 'date NOT NULL',
            'subventionTaux' => 'int(3) NOT NULL DEFAULT "0"',
            'derogationMotif' => 'text',
            'tarifId' => 'int(11) NOT NULL DEFAULT "0"',
            'regimeId' => 'tinyint(1) NOT NULL DEFAULT "0"'
        ),
        'primary_key' => array(
            'millesime', 'eleveId'
        ),
        'engine' => 'InnoDB',
        'charset' => 'utf8',
        'collate' => 'utf8_unicode_ci'
    ),
    'data' => include __DIR__ . '/data/data.scolarites.php'
); 