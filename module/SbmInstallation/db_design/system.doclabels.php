<?php
/**
 * Description des champs de la table de configuration des étiquettes
 * 
 * @project sbm
 * @package SbmInstallation
 * @filesource system.doclabels.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 26 juil. 2015
 * @version 2015-1
 */
return array(
    'name' => 'doclabels',
    'type' => 'system',
    'drop' => true,
    'edit_entity' => true,
    'add_data' => true,
    'structure' => array(
        'fields' => array(
            'doclabelId' => 'int(11) NOT NULL AUTO_INCREMENT',
            'documentId' => 'int(11) NOT NULL',
            'margin_left' => 'float NOT NULL DEFAULT "0"', // marge de gauche du la planche (0 mm par défaut)
            'margin_top' => 'float NOT NULL DEFAULT "8"', // marge du haut de la planche d'étiquettes (8 mm par défaut)
            'x_space' => 'float NOT NULL DEFAULT "0"', // espacement horizontal entre 2 colonnes d'étiquettes (0 mm par défaut)
            'y_space' => 'float NOT NULL DEFAULT "0"', // espacement vertical entre 2 rangée d'étiquettes (0 mm par défaut)
            'label_width' => 'float NOT NULL DEFAULT "105"', // largeur d'une étiquette
            'label_height' => 'float NOT NULL DEFAULT "35"',
            'cols_number' => 'int(11) NOT NULL DEFAULT "2"', // nombre de colonnes (2 par défaut)
            'rows_number' => 'int(11) NOT NULL DEFAULT "8"', // nombre de rangées d'étiquettes (8 par défaut)
            'padding_top' => 'float NOT NULL DEFAULT "3"', // écartement du texte par rapport au haut de l'étiquette (3 mm par défaut)
            'padding_right' => 'float NOT NULL DEFAULT "3"', // écartement du texte par rapport au bord droit de l'étiquette (3 mm par défaut)
            'padding_bottom' => 'float NOT NULL DEFAULT "3"', // écartement du texte par rapport au bas de l'étiquette (3 mm par défaut)
            'padding_left' => 'float NOT NULL DEFAULT "3"', // écartement du texte par rapport au bord gauche de l'étiquette (3 mm par défaut)
            'border' => 'varchar(4) NOT NULL DEFAULT ""', // 0 pas de bord, 1 cadre, ou combinaison de LTRB
            'border_dash' => 'varchar(4) NOT NULL DEFAULT "0"', // 0 continu; 2 tirets de 2mm séparés de 2mm; "1,2" tirets de 1mm séparés de 2mm
            'border_width' => 'float NOT NULL DEFAULT "0.3"', // épaisseur du trait (0.3 mmm par défaut)
            'border_color' => 'varchar(20) NOT NULL DEFAULT "000000"'
        ),
        'primary_key' => array(
            'doclabelId'
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
    ),
    'data' => __DIR__ . '/data/data.system.doclabels.php'
);
 