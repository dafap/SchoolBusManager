<?php
/**
 * Table système - Affectation des documents aux méthodes des controllers
 *
 *
 * @project sbm
 * @package SbmInstallation/db_design
 * @filesource system.docaffectations.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 17 août 2014
 * @version 2014-1
 */
return array(
    'name' => 'docaffectations',
    'type' => 'system',
    'drop' => false,
    'edit_entity' => false,
    'add_data' => false,
    'structure' => array(
        'fields' => array(
            'docaffectationId' => 'int(11) NOT NULL AUTO_INCREMENT',
            'documentId' => 'int(11) NOT NULL',
            'methodeAction' => 'varchar(255) NOT NULL'
        ),
        'primary_key' => array(
            'docaffectationId'
        ),
        'foreign key' => array(
            array(
                'key' => 'documentId',
                'references' => array(
                    'table' => 'documents',
                    'fields' => array(
                        'documentId'
                    ),
                    'on' => array(
                        'update' => 'CASCADE',
                        'delete' => 'CASCADE'
                    )
                )
            )
        ),
        'engine' => 'InnoDb',
        'charset' => 'utf8',
        'collate' => 'utf8_unicode_ci'
    )
);