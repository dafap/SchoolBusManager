<?php
/**
 * Configuration par défaut des étiquettes
 *
 * @project sbm
 * @package SbmPdf/Model/default
 * @filesource doclabels.inc.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 28 juil. 2015
 * @version 2015-1
 */

 return array(
     'margin_left' => "0", // marge de gauche du la planche (0 mm par défaut)
     'margin_top' => "8", // marge du haut de la planche d'étiquettes (8 mm par défaut)
     'x_space' => "0", // espacement horizontal entre 2 colonnes d'étiquettes (0 mm par défaut)
     'y_space' => "0", // espacement vertical entre 2 rangée d'étiquettes (0 mm par défaut)
     'label_width' => "105", // largeur d'une étiquette
     'label_height' => "35",
     'cols_number' => "2", // nombre de colonnes (2 par défaut)
     'rows_number' => "8", // nombre de rangées d'étiquettes (8 par défaut)
     'padding_top' => "3", // écartement du texte par rapport au haut de l'étiquette (3 mm par défaut)
     'padding_right' => "3", // écartement du texte par rapport au bord droit de l'étiquette (3 mm par défaut)
     'padding_bottom' => "3", // écartement du texte par rapport au bas de l'étiquette (3 mm par défaut)
     'padding_left' => "3", // écartement du texte par rapport au bord gauche de l'étiquette (3 mm par défaut)
     'border' => "0", // 0 pas de bord, 1 cadre, ou combinaison de LTRB
     'border_dash' => "0", // 0 continu; 2 tirets de 2mm séparés de 2mm; "1,2" tirets de 1mm séparés de 2mm
     'border_width' => "0.3", // épaisseur du trait (0.3 mmm par défaut)
     'border_color' => "000000"
 );