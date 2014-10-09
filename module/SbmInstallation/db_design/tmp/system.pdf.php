<?php
/**
 * Description d'un document
 *
 *
 * @project sbm
 * @package 
 * @filesource 
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 9 août 2014
 * @version 2014-1
 */

//$k_path_images = str_replace('\\', '/', dirname(__FILE__).'/../../../public/img/');

return array(
    'name' => 'pdf',
    'type' => 'system',
    'drop' => true,
    'edit_entity' => true,
    'add_data' => true,
    'structure' => array(
        'fields' => array(
            'pdfId' => 'int(5) NOT NULL AUTO_INCREMENT',
            'nom' => 'varchar(18) NOT NULL',
            'title' => 'varchar(32) NULL DEFAULT NULL',
            'recordSource' => 'varchar(255) NOT NULL',
            'recordSourceType' => 'varchar(6) NOT NULL DEFAULT "table"',
            'filter' => 'varchar(255) NULL DEFAULT NULL',
            'filterOnLoad' => 'tinyint(1) NOT NULL DEFAULT "0"',
            'orderBy' => 'varchar(255) NULL DEFAULT NULL',
            'orderByOnLoad' => 'tinyint(1) NOT NULL DEFAULT "1"',
            'url_path_images' => 'varchar(64) NOT NULL DEFAULT "/public/img/"',
            'pdf_header_logo' => 'varchar(255) NULL DEFAULT "sbm-logo.gif"',
            'pdf_header_logo_width' => 'int(11) NULL DEFAULT "15"',
            'pdf_page_format' => 'varchar(30) NOT NULL DEFAULT "A4"',
            'pdf_page_orientation' => 'varchar(1) NOT NULL DEFAULT "P"',
            'pdf_header_string' => 'varchar(128) NULL DEFAULT NULL',
            'pdf_margin_header' => 'int(11) NOT NULL DEFAULT "5"',
            'pdf_margin_footer' => 'int(11) NOT NULL DEFAULT "10"',
            'pdf_margin_top' => 'int(11) NOT NULL DEFAULT "27"',
            'pdf_margin_bottom' => 'int(11) NOT NULL DEFAULT "25"',
            'pdf_margin_left' => 'int(11) NOT NULL DEFAULT "15"',
            'pdf_margin_right' => 'int(11) NOT NULL DEFAULT "15"',
            'pdf_font_name_main' => 'varchar(64) NOT NULL DEFAULT "helvetica"',
            'pdf_font_size_main' => 'int(11) NOT NULL DEFAULT "10"',
            'pdf_font_name_data' => 'varchar(64) NOT NULL DEFAULT "helvetica"',
            'pdf_font_size_data' => 'int(11) NOT NULL DEFAULT "8"',
            'pdf_font_monospaced' => 'varchar(64) NOT NULL DEFAULT "courier"'
        ),
        'primary_key' => array(
            'pdfId'
        ),
        'engine' => 'MyISAM',
        'charset' => 'utf8',
        'collate' => 'utf8_unicode_ci'
    )
);