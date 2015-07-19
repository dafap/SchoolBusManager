<?php
/**
 * Description des champs utilisés dans les documents
 *
 *
 * @project sbm
 * @package SbmInstallation/db_design
 * @filesource system.docfields.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 17 août 2014
 * @version 2014-1
 */
return array(
    'name' => 'docfields',
    'type' => 'system',
    'drop' => false,
    'edit_entity' => false,
    'add_data' => false,
    'structure' => array(
        'fields' => array(
            'docfieldId' => 'int(11) NOT NULL AUTO_INCREMENT',
            'documentId' => 'int(11) NOT NULL',
            'fieldname' => 'varchar(255) NOT NULL',
            'label' => 'varchar(255)', 
        ),
        'primary_key' => array(
            'docfieldId'
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