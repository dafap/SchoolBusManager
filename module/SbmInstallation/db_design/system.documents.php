<?php
/**
 * Table système - Description d'un document
 *
 *
 * @project sbm
 * @package SbmInstallation/db_design
 * @filesource system.documents.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 18 sept. 2018
 * @version 2018-2.4.5
 */

return array(
    'name' => 'documents',
    'type' => 'system',
    'drop' => false,
    'edit_entity' => false,
    'add_data' => false,
    'structure' => array(
        'fields' => array(
            // document
            'documentId' => 'int(11) NOT NULL AUTO_INCREMENT',
            'type' => 'char(3) NOT NULL DEFAULT "pdf"',
            'disposition' => 'varchar(12) NOT NULL DEFAULT "Tabulaire"', // Tabulaire, Texte, Etiquette
            'name' => 'varchar(32) NOT NULL',
            'out_mode' => 'varchar(2) NOT NULL DEFAULT "I"', // I (inline), D (force download), F (file on server), S (string), FI (F + I), FD (F + D), E (base64 mime multi-part email attachment)
            'out_name' => 'varchar(32) NULL DEFAULT "document-sbm.pdf"',
            'recordSource' => 'text NOT NULL',
            'recordSourceType' => 'char(1) NOT NULL DEFAULT "T"', // prend les valeurs T ou R (pour table ou requête)
            'filter' => 'text NULL',
            'orderBy' => 'varchar(255) NULL DEFAULT NULL',
            // images
            'url_path_images' => 'varchar(64) NOT NULL DEFAULT "/public/img/"',
            'image_blank' => 'varchar(255) NOT NULL DEFAULT "_blank.png"',
            // présence de l'entête (du pied) de document (de page)
            'docheader' => 'tinyint(1) NOT NULL DEFAULT "0"', // Oui (1) ou Non (0)
            'docfooter' => 'tinyint(1) NOT NULL DEFAULT "0"',
            'pageheader' => 'tinyint(1) NOT NULL DEFAULT "0"',
            'pagefooter' => 'tinyint(1) NOT NULL DEFAULT "0"',
            // propriétés du document (qui constitueront la page d'entête du rapport si docheader == 1)
            'creator' => 'varchar(255) NOT NULL DEFAULT "SchoolBusManager"',
            'author' => 'varchar(255) NOT NULL DEFAULT ""',
            'title' => 'varchar(255) NOT NULL DEFAULT ""',
            'subject' => 'varchar(255) NOT NULL DEFAULT ""',
            'keywords' => 'varchar(255) NOT NULL DEFAULT ""',
            'docheader_subtitle' => 'text NULL', // ne fait pas partie des propriétés d'un pdf mais complète la page d'entête du rapport (si présente)
            'docheader_page_distincte' => 'tinyint(1) NOT NULL DEFAULT "1"', // 0 la suite sur la même page ; 1 la suite sur une nouvelle page
            'docheader_margin' => 'int(11) NOT NULL DEFAULT "20"', // marge du bloc docheader si page_distincte == 0
            'docheader_pageheader' => 'tinyint(1) NOT NULL DEFAULT "0"', // 0 pas de pageheader sur la première page ; 1 si oui
            'docheader_pagefooter' => 'tinyint(1) NOT NULL DEFAULT "0"', // 0 pas de pagefooter sur la première page ; 1 si oui
            'docheader_templateId' => 'int(11) NOT NULL DEFAULT "1"',
            // pied de document (si docfooter == 1)
            'docfooter_title' => 'varchar(255) NOT NULL DEFAULT ""',
            'docfooter_string' => 'text NULL',
            'docfooter_page_distincte' => 'tinyint(1) NOT NULL DEFAULT "1"', // 0 la suite sur la même page ; 1 la suite sur une nouvelle page
            'docfooter_insecable'  => 'tinyint(1) NOT NULL DEFAULT "1"', // 0 le footer peut être scindé ; 1 tout le footer sur la même page
            'docfooter_margin' => 'int(11) NOT NULL DEFAULT "20"', // marge du bloc docfooter si page_distincte == 0
            'docfooter_pageheader' => 'tinyint(1) NOT NULL DEFAULT "0"', // 0 pas de pageheader sur la dernière page ; 1 si oui
            'docfooter_pagefooter' => 'tinyint(1) NOT NULL DEFAULT "0"', // 0 pas de pagefooter sur la dernière page ; 1 si oui
            'docfooter_templateId' => 'int(11) NOT NULL DEFAULT "1"',
            // entête de page (si pageheader == 1)
            'pageheader_templateId' => 'int(11) NOT NULL DEFAULT "1"',
            'pageheader_title' => 'varchar(255) NOT NULL DEFAULT ""',
            'pageheader_string' => 'text NULL',
            'pageheader_logo_visible' => 'tinyint(1) NOT NULL DEFAULT "1"', // si 0 on envoie l'image blank
            'pageheader_logo' => 'varchar(255) NOT NULL DEFAULT "sbm-logo.gif"',
            'pageheader_logo_width' => 'int(11) NOT NULL DEFAULT "15"', 
            'pageheader_margin' => 'int(11) NOT NULL DEFAULT "5"',
            'pageheader_font_family' => 'varchar(64) NOT NULL DEFAULT "helvetica"',
            'pageheader_font_style' => 'char(2) NOT NULL DEFAULT ""',
            'pageheader_font_size' => 'int(11) NOT NULL DEFAULT "11"',
            'pageheader_text_color' => 'varchar(20) NOT NULL DEFAULT "000000"',
            'pageheader_line_color' => 'varchar(20) NOT NULL DEFAULT "000000"',
            // pied de page (si pagefooter == 1)
            'pagefooter_templateId' => 'int(11) NOT NULL DEFAULT "1"',
            'pagefooter_margin' => 'int(11) NOT NULL DEFAULT "10"',
            'pagefooter_string' => 'text NULL',
            'pagefooter_font_family' => 'varchar(64) NOT NULL DEFAULT "helvetica"',
            'pagefooter_font_style' => 'char(2) NOT NULL DEFAULT ""',
            'pagefooter_font_size' => 'int(11) NOT NULL DEFAULT "11"',           
            'pagefooter_text_color' => 'varchar(20) NOT NULL DEFAULT "000000"',
            'pagefooter_line_color' => 'varchar(20) NOT NULL DEFAULT "000000"',
            // page
            'page_templateId' => 'int(11) NOT NULL DEFAULT "1"', // identifiant du template (table system template)
            'page_format' => 'varchar(30) NOT NULL DEFAULT "A4"',
            'page_orientation' => 'varchar(1) NOT NULL DEFAULT "P"',
            'page_margin_top' => 'int(11) NOT NULL DEFAULT "27"',
            'page_margin_bottom' => 'int(11) NOT NULL DEFAULT "25"',
            'page_margin_left' => 'int(11) NOT NULL DEFAULT "15"',
            'page_margin_right' => 'int(11) NOT NULL DEFAULT "15"',
            'main_font_family' => 'varchar(64) NOT NULL DEFAULT "helvetica"',
            'main_font_style' => 'char(2) NOT NULL DEFAULT ""',
            'main_font_size' => 'int(11) NOT NULL DEFAULT "11"',
            'data_font_family' => 'varchar(64) NOT NULL DEFAULT "helvetica"',
            'data_font_style' => 'char(2) NOT NULL DEFAULT ""',
            'data_font_size' => 'int(11) NOT NULL DEFAULT "8"',
            'titre1_font_family' => 'varchar(64) NOT NULL DEFAULT "helvetica"',
            'titre1_font_style' => 'char(2) NOT NULL DEFAULT ""',
            'titre1_font_size' => 'int(11) NOT NULL DEFAULT "14"',
            'titre1_text_color' => 'varchar(20) NOT NULL DEFAULT "000000"',
            'titre1_line' => 'tinyint(1) NOT NULL DEFAULT "0"',
            'titre1_line_color' => 'varchar(20) NOT NULL DEFAULT "000000"',
            'titre2_font_family' => 'varchar(64) NOT NULL DEFAULT "helvetica"',
            'titre2_font_style' => 'char(2) NOT NULL DEFAULT ""',
            'titre2_font_size' => 'int(11) NOT NULL DEFAULT "13"',
            'titre2_text_color' => 'varchar(20) NOT NULL DEFAULT "000000"',
            'titre2_line' => 'tinyint(1) NOT NULL DEFAULT "0"',
            'titre2_line_color' => 'varchar(20) NOT NULL DEFAULT "000000"',
            'titre3_font_family' => 'varchar(64) NOT NULL DEFAULT "helvetica"',
            'titre3_font_style' => 'char(2) NOT NULL DEFAULT ""',
            'titre3_font_size' => 'int(11) NOT NULL DEFAULT "12"',
            'titre3_text_color' => 'varchar(20) NOT NULL DEFAULT "000000"',
            'titre3_line' => 'tinyint(1) NOT NULL DEFAULT "0"',
            'titre3_line_color' => 'varchar(20) NOT NULL DEFAULT "000000"',
            'titre4_font_family' => 'varchar(64) NOT NULL DEFAULT "helvetica"',
            'titre4_font_style' => 'char(2) NOT NULL DEFAULT ""',
            'titre4_font_size' => 'int(11) NOT NULL DEFAULT "11"',
            'titre4_text_color' => 'varchar(20) NOT NULL DEFAULT "000000"',
            'titre4_line' => 'tinyint(1) NOT NULL DEFAULT "0"',
            'titre4_line_color' => 'varchar(20) NOT NULL DEFAULT "000000"',
            'default_font_monospaced' => 'varchar(64) NOT NULL DEFAULT "courier"'
        ),
        'primary_key' => array(
            'documentId'
        ),
        'engine' => 'InnoDb',
        'charset' => 'utf8',
        'collate' => 'utf8_unicode_ci'
    ),
    //'data' => include __DIR__ . '/data/data.system.documents.php'
    //'data' => array('after' =>[], 'include' => __DIR__ . '/data/data.documents.php')
    'data' => __DIR__ . '/data/data.system.documents.php'
);