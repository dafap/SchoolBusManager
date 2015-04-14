<?php
/**
 * Structure de la table des `secteurs-scolaires-clg-pu`
 *
 * Description longue du fichier s'il y en a une
 * 
 * @project sbm
 * @package SbmInstallation/db_design
 * @filesource table.secteurs-scolaires-clg-pu.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 13 avr. 2015
 * @version 2015-1
 */

return array(
    'name' => 'secteurs-scolaires-clg-pu',
    'type' => 'table',
    'drop' => false,
    'edit_entity' => false,
    'add_data' => false,
    'structure' => array(
        'fields' => array(
            'communeId' => 'varchar(6) NOT NULL',
            'etablissementId' => 'char(8) NOT NULL',
        ),
        'primary_key' => array(
            'communeId',
            'etablissementId'            
        ),
        'foreign key' => array(
            array(
                'key' => 'etablissementId',
                'references' => array(
                    'table' => 'etablissements',
                    'fields' => array(
                        'etablissementId'
                    ),
                    'on' => array(
                        'update' => 'CASCADE',
                        'delete' => 'RESTRICT'
                    )
                )
            ),
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
        'engine' => 'InnoDb',
        'charset' => 'utf8',
        'collate' => 'utf8_unicode_ci'
    ),
    'data' => __DIR__ . '/data/data.secteurs-scolaires-clg-pu.php'
);