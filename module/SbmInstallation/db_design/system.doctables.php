<?php
/**
 * Description des champs utilisés dans les tables (table `doctables`)
 *
 *
 * @project sbm
 * @package SbmInstallation/db_design
 * @filesource system.doctables.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 17 août 2014
 * @version 2014-1
 */
return array(
    'name' => 'doctables',
    'type' => 'system',
    'drop' => false,
    'edit_entity' => false,
    'add_data' => false,
    'structure' => array(
        'fields' => array(
            'doctableId' => 'int(11) NOT NULL AUTO_INCREMENT',
            'documentId' => 'int(11) NOT NULL DEFAULT "1"',
            'ordinal_table' => 'int(11) NOT NULL DEFAULT "1"', // dans le cas où il y aurait plusieurs tables dans le même document
            'section' => 'char(5)', // prend les valeurs thead, tbody ou tfoot
            'description' => 'varchar(255) NOT NULL',
            'visible' => 'tinyint(1) NOT NULL DEFAULT "1"',
            'width' => 'varchar(4)', // null par défaut, prend la valeur auto ou un nombre de 1 à 100 (% de la largeur de la zone d'écriture)
            'row_height' => 'int(11) NOT NULL DEFAULT "6"',
            'cell_border' => 'varchar(4) NOT NULL DEFAULT "1"', // 0, 1, L, R, T, B
            'cell_align' => 'char(1) NOT NULL DEFAULT "L"', // L, C, R, J
            'cell_link' => 'varchar(128) NOT NULL DEFAULT ""',
            'cell_stretch' => 'tinyint(1) UNSIGNED NOT NULL DEFAULT "0"', // de 0 à 4
            'cell_ignore_min_height' => 'tinyint(1) UNSIGNED NOT NULL DEFAULT "0"', // bool
            'cell_calign' => 'char(1) NOT NULL DEFAULT "T"', // T, C, B, A, L, D
            'cell_valign' => 'char(1) NOT NULL DEFAULT "M"', // M, T, B
            'draw_color' => 'varchar(20) NOT NULL DEFAULT "black"',
            'line_width' => 'float(2,1) NOT NULL DEFAULT "0.1"',
            'fill_color' => 'varchar(20) NOT NULL DEFAULT "E0EBFF"',
            'text_color' => 'varchar(20) NOT NULL DEFAULT "black"',
            'font_style' => 'char(2) NOT NULL DEFAULT ""'
        ) // '', B, I, U, D, O ou combinaison de 2 d'entre elles
,
        'primary_key' => array(
            'doctableId'
        ),
        'engine' => 'MyISAM',
        'charset' => 'utf8',
        'collate' => 'utf8_unicode_ci'
    ),
    'data' => include __DIR__ . '/data/data.system.doctables.php'
);