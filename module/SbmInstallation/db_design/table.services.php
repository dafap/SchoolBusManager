<?php
/**
 * Structure de la table des `services`
 *
 *
 * @project sbm
 * @package SbmInstallation/db_design
 * @filesource table.services.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 5 févr. 2014
 * @version 2015-2
 */
return array(
    'name' => 'services',
    'type' => 'table',
    'drop' => false,
    'edit_entity' => false,
    'add_data' => false,
    'structure' => array(
        'fields' => array(
            'serviceId' => 'varchar(11) NOT NULL',
            'nom' => 'varchar(45) NOT NULL',
            'aliasCG' => 'varchar(15) NOT NULL DEFAULT ""',
            'transporteurId' => 'int(11) NOT NULL DEFAULT "0"',
            'nbPlaces' => 'tinyint(3) unsigned NOT NULL DEFAULT "0"',
            'surEtatCG' => 'tinyint(1) NOT NULL DEFAULT "0"',
            'operateur' => 'varchar(4) NOT NULL DEFAULT "CCDA"',
            'kmAVide' => 'decimal(7,3) NOT NULL DEFAULT "0"',
            'kmEnCharge' => 'decimal(7,3) NOT NULL DEFAULT "0.000"',
            'geotrajet' => 'POLYGON'
        ),
        'primary_key' => array(
            'serviceId'
        ),
        'foreign key' => array(
            array(
                'key' => 'transporteurId',
                'references' => array(
                    'table' => 'transporteurs',
                    'fields' => array(
                        'transporteurId'
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
        'collate' => 'utf8_unicode_ci'
    ),
    
    // 'data' => include __DIR__ . '/data/data.services.php',
    // 'data' => array('after' => array('transporteurs'),'include' => __DIR__ . '/data/data.services.php')
    'data' => __DIR__ . '/data/data.services.php'
);