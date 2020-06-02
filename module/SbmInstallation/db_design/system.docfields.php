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
 * @date 14 mai 2020
 * @version 2020-2.6.0
 */
use SbmBase\Model\StdLib;

return [
    'name' => 'docfields',
    'type' => 'system',
    'drop' => false,
    'edit_entity' => false,
    'add_data' => false,
    'structure' => [
        'fields' => [
            'docfieldId' => 'int(11) NOT NULL AUTO_INCREMENT',
            'documentId' => 'int(11) NOT NULL',
            'sublabel' => 'int(11) NOT NULL DEFAULT "0"',
            'ordinal_position' => 'int(11) NOT NULL DEFAULT "1"',
            'filter' => 'text NULL',
            'fieldname' => 'varchar(255) NOT NULL',
            'fieldname_width' => 'float NOT NULL DEFAULT "0"', // 0 pour ne pas imposer
                                                                // de largeur
            'fieldname_align' => 'varchar(8) NOT NULL DEFAULT "standard"', // si
                                                                            // fieldname_width
                                                                            // alors L, C,
                                                                            // R, J
            'fieldname_stretch' => 'tinyint(1) UNSIGNED NOT NULL DEFAULT "0"', // si
                                                                                // fieldname_width
                                                                                // alors
                                                                                // de 0 à
                                                                                // 4
            'fieldname_completion' => 'tinyint(3) NOT NULL DEFAULT "0"', // si
                                                                          // fieldname_width
                                                                          // alors
                                                                          // complétion à
                                                                          // gauche par
                                                                          // des espaces
                                                                          // ou zéros.
                                                                          // Indique le
                                                                          // nombre de
                                                                          // chiffres à
                                                                          // obtenir. 0
                                                                          // <=> pas de
                                                                          // complétion
            'fieldname_precision' => 'tinyint(3) NOT NULL DEFAULT "-1"', // précision
                                                                          // pour les
                                                                          // colonnes
                                                                          // numériques.
                                                                          // -1 pour ne
                                                                          // pas
                                                                          // appliquer la
                                                                          // précision
            'nature' => 'tinyint(1) UNSIGNED NOT NULL DEFAULT "0"', // 0 ou 1 pour date
                                                                     // ou 2 pour
                                                                     // photo
            'format' => 'varchar(255)', // format de date : on vérifie s'il y a un h
                                         // (date J/M/A
                                         // H:m:s) ou non (date J/M/A) ou format de
                                         // sprintf si non
                                         // date
            'label' => 'text NULL', // le texte à imprimer avant la valeur du data
            'label_space' => 'float NOT NULL DEFAULT "3"', // espacement entre le label
                                                            // et la
                                                            // donnée (en unité du
                                                            // document, en
                                                            // général le mm)
            'label_width' => 'float NOT NULL DEFAULT "0"', // 0 pour ne pas imposer de
                                                            // largeur
            'label_align' => 'varchar(8) NOT NULL DEFAULT "standard"', // si label_width
                                                                        // alors L,
                                                                        // C, R, J
            'label_stretch' => 'tinyint(1) UNSIGNED NOT NULL DEFAULT "0"', // si
                                                                            // label_width
                                                                            // alors
                                                                            // de 0 à 4
            'style' => 'varchar(6) NOT NULL DEFAULT "main"', // main, data, titre1,
                                                              // titre2, titre3
                                                              // ou titre4
            'height' => 'float NOT NULL DEFAULT "7"', // hauteur des cellules d'écriture
                                                       // du field
                                                       // et du label
            'photo_x' => 'int(11) NULL DEFAULT NULL',
            'photo_y' => 'int(11) NULL DEFAULT NULL',
            'photo_w' => 'int(11) NOT NULL DEFAULT "0"',
            'photo_h' => 'int(11) NOT NULL DEFAULT "0"',
            'photo_type' => 'varchar(4) NOT NULL DEFAULT "JPEG"',
            'photo_align' => 'char(1) NULL DEFAULT NULL',
            'photo_resize' => 'tinyint NOT NULL DEFAULT 1'
        ],
        'primary_key' => [
            'docfieldId'
        ],
        'foreign key' => [
            [
                'key' => 'documentId',
                'references' => [
                    'table' => 'documents',
                    'fields' => [
                        'documentId'
                    ],
                    'on' => [
                        'update' => 'CASCADE',
                        'delete' => 'CASCADE'
                    ]
                ]
            ],
            [
                'key' => [
                    'documentId',
                    'sublabel'
                ],
                'references' => [
                    'table' => 'doclabels',
                    'fields' => [
                        'documentId',
                        'sublabel'
                    ],
                    'on' => [
                        'update' => 'CASCADE',
                        'delete' => 'CASCADE'
                    ]
                ]
            ]
        ],
        'engine' => 'InnoDb',
        'charset' => 'utf8mb4',
        'collate' => 'utf8mb4_unicode_ci'
    ],
    'data' => StdLib::concatPath(StdLib::findParentPath(__DIR__, 'data/data'),
        'data.system.docfields.php')
];