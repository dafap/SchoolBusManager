<?php
/**
 * Structure de la table des `appels`
 *
 * Il s'agit des appels à la plateforme de paiement pour essayer de payer.
 * Cette table établit la liaison entre le payeur et les élèves concernés.
 * 
 * @project sbm
 * @package SbmInstallation/db_design
 * @filesource table.appels.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 18 mai 2015
 * @version 2015-1
 */
return array(
    'name' => 'appels',
    'type' => 'table',
    'drop' => false,
    'edit_entity' => false,
    'add_data' => false,
    'structure' => array(
        'fields' => array(
            'appelId' => 'int(11) NOT NULL AUTO_INCREMENT',
            'referenceId' => 'varchar(20) NOT NULL',
            'responsableId' => 'int(11) NOT NULL',
            'eleveId' => 'int(11) NOT NULL'
        ),
        'primary_key' => array(
            'appelId'
        ),
        'foreign key' => array(
            array(
                'key' => 'responsableId',
                'references' => array(
                    'table' => 'responsables',
                    'fields' => array(
                        'responsableId'
                    ),
                    'on' => array(
                        'update' => 'CASCADE',
                        'delete' => 'RESTRICT'
                    )
                )
            ),
            array(
                'key' => 'eleveId',
                'references' => array(
                    'table' => 'eleves',
                    'fields' => array(
                        'eleveId'
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

    'data' => __DIR__ . '/data/data.appels.php'
);