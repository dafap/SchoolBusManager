<?php
/**
 * Structure de la table des `circuits`
 *
 *
 * @project sbm
 * @package SbmInstallation/db_design
 * @filesource table.circuits.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 23 déc. 2015
 * @version 2015-1.6.9
 */


return array(
    'name' => 'circuits',
    'drop' => false,
    'edit_entity' => false,
    'add_data' => false,
    'type' => 'table',
    'structure'=> array(
        'fields' => array(
            'circuitId' => 'int(11) NOT NULL AUTO_INCREMENT',
            'selection' => 'tinyint(1) NOT NULL DEFAULT "0"',
            'millesime' => 'int(11) NOT NULL',
            'serviceId' => 'varchar(11) NOT NULL',
            'stationId' => 'int(11) NOT NULL',
            'semaine' => 'tinyint(4) UNSIGNED NOT NULL DEFAULT "31"',
            'm1' => 'time NOT NULL DEFAULT "00:00:00" COMMENT "Aller (4 jours)"',
            's1' => 'time NOT NULL DEFAULT "23:59:59" COMMENT "Retour (4 jours)"',
            'm2' => 'time NOT NULL DEFAULT "00:00:00" COMMENT "Aller (Me)"',
            's2' => 'time NOT NULL DEFAULT "23:59:59" COMMENT "Retour (Me)"',
            'm3' => 'time NOT NULL DEFAULT "00:00:00" COMMENT "Aller (Sa)"',
            's3' => 'time NOT NULL DEFAULT "23:59:59" COMMENT "Retour (Sa)"',
            'distance' => 'decimal(7,3) NOT NULL DEFAULT "0.000"',
            'montee' => 'tinyint(1) UNSIGNED NOT NULL DEFAULT "1"',
            'descente' => 'tinyint(1) UNSIGNED NOT NULL DEFAULT "0"',
            'typeArret' => 'text',
            'commentaire1' => 'text', // aller
            'commentaire2' => 'text', // retour
            'geopt' => 'GEOMETRY'
        ),
        'primary_key' => array('circuitId'),
        'foreign key' => array(
            array(
                'key' => 'serviceId',
                'references' => array(
                    'table' => 'services',
                    'fields' => array(
                        'serviceId'
                    ),
                    'on' => array(
                        'update' => 'CASCADE',
                        'delete' => 'RESTRICT'
                    )
                )
            ),
            array(
                'key' => 'stationId',
                'references' => array(
                    'table' => 'stations',
                    'fields' => array(
                        'stationId'
                    ),
                    'on' => array(
                        'update' => 'CASCADE',
                        'delete' => 'RESTRICT'
                    )
                )
            )
        ),
        'engine' => 'InnoDb',
        'charset' => 'utf8',
        'collate' => 'utf8_unicode_ci',
    ),
    'data' => __DIR__ . '/data/data.circuits.php'
);
