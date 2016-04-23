<?php
/**
 * Configuration de la table d'un document pdf par défaut
 *
 * @project sbm
 * @package SbmPdf/Model/default
 * @filesource doctables.inc.php
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 1 juil. 2015
 * @version 2015-2
 */
return [
    'thead' => [
        'visible' => true,
        'width' => null,
        'row_height' => 7,
        'cell_border' => 1,
        'cell_align' => 'C',
        'cell_link' => '',
        'cell_stretch' => 0,
        'cell_ignore_min_height' => false,
        'cell_calign' => 'T',
        'cell_valign' => 'M',
        'draw_color' => '800000',
        'line_width' => 0.3,
        'fill_color' => 'red',
        'text_color' => 'white',
        'font_style' => 'B'
    ],
    'tbody' => [
        'visible' => true,
        'width' => 'auto',
        'row_height' => 6,
        'cell_border' => 'LR',
        'cell_align' => 'standard',
        'cell_link' => '',
        'cell_stretch' => 0,
        'cell_ignore_min_height' => false,
        'cell_calign' => 'T',
        'cell_valign' => 'M',
        'draw_color' => '800000',
        'line_width' => 0.3,
        'fill_color' => 'E0EBFF',
        'text_color' => 'black',
        'font_style' => ''
    ],
    'tfoot' => [
        'visible' => true,
        'width' => null,
        'row_height' => 7,
        'cell_border' => 1,
        'cell_align' => 'standard',
        'cell_link' => '',
        'cell_stretch' => 0,
        'cell_ignore_min_height' => false,
        'cell_calign' => 'T',
        'cell_valign' => 'M',
        'draw_color' => '800000',
        'line_width' => 0.3,
        'fill_color' => 'white',
        'text_color' => 'black',
        'font_style' => 'B'
    ]
];