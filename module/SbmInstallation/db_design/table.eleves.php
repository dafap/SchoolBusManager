<?php
/**
 * Structure de la table des `eleves`
 *
 *
 * @project sbm
 * @package module/SbmInstallation/config/db_design
 * @filesource table.eleves.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 17 juin 2014
 * @version 2014-1
 */

return array(
    'name' => 'eleves',
    'type' => 'table',
    'drop' => false,
    'edit_entity' => false,
    'add_data' => false,
    'structure'=> array(
        'fields' => array(
            'eleveId' => 'int(11) NOT NULL AUTO_INCREMENT',
            'respId1' => 'int(11) UNSIGNED NOT NULL DEFAULT "0"',
            'respId2' => 'int(11) UNSIGNED NOT NULL DEFAULT "0"',
            'factId' => 'int(11) UNSIGNED NOT NULL DEFAULT "0"',
            'classeId' => 'int(8) UNSIGNED NOT NULL DEFAULT "0"',
            'etablissementId' => 'varchar(8) NOT NULL DEFAULT ""',
            'tarifId' => 'int(11) NOT NULL DEFAULT "0"',
            'stationId1' => 'int(11) NOT NULL DEFAULT "0"',
            'stationId1m' => 'int(11) NOT NULL DEFAULT "0"',
            'stationId1s' => 'int(11) NOT NULL DEFAULT "0"',
            'stationId2' => 'int(11) NOT NULL DEFAULT "0"',
            'stationId2m' => 'int(11) NOT NULL DEFAULT "0"',
            'stationId2s' => 'int(11) NOT NULL DEFAULT "0"',
            'serviceId1' => 'varchar(11) NOT NULL DEFAULT ""',
            'serviceId1m' => 'varchar(11) NOT NULL DEFAULT ""',
            'serviceId1s' => 'varchar(11) NOT NULL DEFAULT ""',
            'serviceId2' => 'varchar(11) DEFAULT NULL',
            'serviceId2m' => 'varchar(11) DEFAULT NULL',
            'serviceId2s' => 'varchar(11) DEFAULT NULL',
            'nom' => 'varchar(30) NOT NULL',
            'nomSA' => 'varchar(30) NOT NULL',
            'prenom' => 'varchar(30) NOT NULL',
            'prenomSA' => 'varchar(30) NOT NULL',
            'adress1L1' => 'varchar(38) NOT NULL',
            'adress1L2' => 'varchar(38) NOT NULL',
            'codePostal1' => 'varchar(5) NOT NULL',
            'communeId1' => 'varchar(6) NOT NULL',
            'adress2L1' => 'varchar(38) DEFAULT NULL',
            'adress2L2' => 'varchar(38) DEFAULT NULL',
            'codePostal2' => 'varchar(5) DEFAULT NULL',
            'communeId2' => 'varchar(6) DEFAULT NULL',
            'dateN' => 'date NOT NULL',
            'dateDebut' => 'date NOT NULL',
            'dateFin' => 'date NOT NULL',
            'dateCreation' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'dateModification' => 'datetime NOT NULL DEFAULT "1900-01-01 00:00:00"',
            'dateInscription' => 'datetime NOT NULL DEFAULT "1900-01-01 00:00:00"',
            'dateCarte' => 'datetime NOT NULL DEFAULT "1900-01-01 00:00:00"',
            'regimeId' => 'tinyint(1) UNSIGNED NOT NULL DEFAULT "0"',
            'anComplet' => 'tinyint(1) UNSIGNED NOT NULL DEFAULT "1"',
            'inscrit' => 'tinyint(1) UNSIGNED NOT NULL DEFAULT "1"',
            'selection' => 'tinyint(1) UNSIGNED NOT NULL DEFAULT "0"',
            'secondeAdresse' => 'tinyint(1) UNSIGNED NOT NULL DEFAULT "0"'
        ),
        'primary_key' => array('eleveId',),
        'engine' => 'InnoDB',
        'charset' => 'utf8',
        'collate' => 'utf8_unicode_ci',
    ),
    'data' => include __DIR__ . '/data/data.eleves.php'
);