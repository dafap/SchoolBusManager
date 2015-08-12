<?php
/**
 * Structure de la table des `usersEtablissements`
 *
 * @project sbm
 * @package SbmInstallation/db_design
 * @filesource table.users-etablissements.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 31 juil. 2015
 * @version 2015-1
 */
return array(
    'name' => 'users-etablissements',
    'type' => 'table',
    'drop' => false,
    'edit_entity' => false,
    'add_data' => false,
    'structure' => array(
        'fields' => array(
            'userId' => 'int(11) NOT NULL',
            'etablissementId' => 'char(8) NOT NULL'
        ),
        'primary_key' => array(
            'userId',
            'etablissementId'
        ),
        'foreign key' => array(
            array(
                'key' => 'userId',
                'references' => array(
                    'table' => 'users',
                    'fields' => array(
                        'userId'
                    ),
                    'on' => array(
                        'update' => 'CASCADE',
                        'delete' => 'RESTRICT'
                    )
                )
            ),
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
            )
        ),
        'engine' => 'InnoDb',
        'charset' => 'utf8',
        'collate' => 'utf8_unicode_ci'
    ),   
    'data' => __DIR__ . '/data/data.users-etablissements.php'
);