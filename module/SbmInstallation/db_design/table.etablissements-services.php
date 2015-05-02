<?php
/**
 * Structure de la table des `etablissementsServices`
 *
 * 
 * @project rbm
 * @package SbmInstallation/db_design
 * @filesource table.etablissements-services.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 12 mars 2015
 * @version 2015-1
 */

return array(
    'name' => 'etablissements-services',
    'type' => 'table',
    'drop' => false,
    'edit_entity' => false,
    'add_data' => false,
    'structure' => array(
        'fields' => array(
            'etablissementId' => 'char(8) NOT NULL',
            'serviceId' => 'varchar(11) NOT NULL',
        ),
        'primary_key' => array(
            'etablissementId',
            'serviceId'
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
            )
        ),
        'engine' => 'InnoDb',
        'charset' => 'utf8',
        'collate' => 'utf8_unicode_ci'
    ),

    // 'data' => include __DIR__ . '/data/data.services.php',
    // 'data' => array('after' => array('transporteurs'),'include' => __DIR__ . '/data/data.services.php')
    'data' => __DIR__ . '/data/data.etablissements-services.php'
);