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
            'ordinal_position' => 'int(11) NOT NULL DEFAULT "1"',
            'filter' => 'text NOT NULL',
            'fieldname' => 'varchar(255) NOT NULL',
            'fieldname_width' => 'int(11) NOT NULL DEFAULT "0"', // 0 pour ne pas imposer de largeur
            'fieldname_align' => 'varchar(8) NOT NULL DEFAULT "standard"', // si fieldname_width alors L, C, R, J
            'fieldname_stretch' => 'tinyint(1) UNSIGNED NOT NULL DEFAULT "0"', // si fieldname_width alors de 0 à 4
            'fieldname_completion' => 'tinyint(3) NOT NULL DEFAULT "0"', //  si fieldname_width alors complétion à gauche par des espaces ou zéros. Indique le nombre de chiffres à obtenir. 0 <=> pas de complétion
            'fieldname_precision' => 'tinyint(3) NOT NULL DEFAULT "-1"', // précision pour les colonnes numériques. -1 pour ne pas appliquer la précision
            'has_label' => 'tinyint(1) UNSIGNED NOT NULL DEFAULT "0"', // 0 ou 1 ; pas de label par défaut
            'label' => 'varchar(255)', // texte du label
            'label_position' => 'varchar(7) NOT NULL DEFAULT "prepend"', // 'prepend' ou 'append'
            'label_space' => 'int(11) NOT NULL DEFAULR "3"', // espacement entre le label et la donnée (en unité du document, en général le mm)
            'label_width' => 'int(11) NOT NULL DEFAULT "0"', // 0 pour ne pas imposer de largeur
            'label_align' => 'varchar(8) NOT NULL DEFAULT "standard"', // si label_width alors L, C, R, J
            'label_stretch' => 'tinyint(1) UNSIGNED NOT NULL DEFAULT "0"', // si label_width alors de 0 à 4
            'label_completion' => 'tinyint(3) NOT NULL DEFAULT "0"', //  si label_width alors complétion à gauche par des espaces ou zéros. Indique le nombre de chiffres à obtenir. 0 <=> pas de complétion
            'label_precision' => 'tinyint(3) NOT NULL DEFAULT "-1"', // précision pour les colonnes numériques. -1 pour ne pas appliquer la précision
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