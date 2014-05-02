<?php
/**
 * Structure de la table des `stations`
 *
 *
 * @project sbm
 * @package module/SbmInstallation/config/db_design
 * @filesource table.stations.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 5 févr. 2014
 * @version 2014-1
 */

return array(
    'name' => 'stations',
    'drop' => false,
    'edit_entity' => false,
    'add_data' => false,
    'type' => 'table',
    'structure'=> array(
        'fields' => array(
            'stationId' => 'int(11) NOT NULL AUTO_INCREMENT',
            'communeId' => 'varchar(6) NOT NULL',
            'nom' => 'varchar(45) NOT NULL',
            'aliasCG' => 'varchar(45) NOT NULL DEFAULT ""',
            'codeCG' => 'int(11) NOT NULL DEFAULT "0"',
            'longitude' => 'varchar(20) NOT NULL DEFAULT ""',
            'latitude' => 'varchar(20) NOT NULL DEFAULT ""',
            'visible' => 'tinyint(1) NOT NULL DEFAULT "1"',
            'ouverte' => 'tinyint(1) NOT NULL DEFAULT  "1"',
        ),
        'primary_key' => array('stationId',),
        'engine' => 'MyISAM',
        'charset' => 'utf8',
        'collate' => 'utf8_unicode_ci',
    ),
    'data' => include __DIR__ . '/data/data.stations.php',
);