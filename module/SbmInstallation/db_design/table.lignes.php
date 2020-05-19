<?php
/**
 * Structure de la table des `lignes` de marchés
 *
 * Chaque ligne est attribué à un lot (lot de marché).
 * Chaque ligne sera découpée en services (ou courses selon la terminologie des horaires TRA).
 * Chaque service est attribué à un transporteur et n'a qu'un seul horaire.
 *
 * @project sbm
 * @package SbmInstallation/db_design
 * @filesource table.lignes.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 15 mai 2020
 * @version 2020-2.6.0
 */
use SbmBase\Model\StdLib;

return [
    'name' => 'lignes',
    'drop' => false,
    'edit_entity' => false,
    'add_data' => false,
    'type' => 'table',
    'structure' => [
        'fields' => [
            'millesime' => 'int(4) NOT NULL',
            'ligneId' => 'varchar(5) NOT NULL',
            'operateur' => 'varchar(5) NOT NULL DEFAULT "TRA"',
            'natureCarte'=>'TINYINT(3) NOT NULL DEFAULT "1"',
            'lotId' => 'int(11) NULL DEFAULT NULL',
            'extremite1' => 'varchar(45) NOT NULL',
            'extremite2' => 'varchar(45) NOT NULL',
            'via' => 'varchar(45) NOT NULL DEFAULT  ""',
            'internes' => 'tinyint(1) NOT NULL DEFAULT "0"',
            'selection' => 'tinyint(1) NOT NULL DEFAULT "0"',
            'actif' => 'tinyint(1) NOT NULL DEFAULT  "1"',
            'commentaire' => 'text NULL DEFAULT NULL'
        ],
        'primary_key' => [
            'millesime',
            'ligneId'
        ],
        'foreign key' => [
            [
                'key' => 'lotId',
                'references' => [
                    'table' => 'lots',
                    'fields' => [
                        'lotId'
                    ],
                    'on' => [
                        'update' => 'CASCADE',
                        'delete' => 'RESTRICT'
                    ]
                ]
            ]
        ],
        'engine' => 'InnoDb',
        'charset' => 'utf8mb4',
        'collate' => 'utf8mb4_unicode_ci'
    ],
    'data' => StdLib::concatPath(StdLib::findParentPath(__DIR__, 'data/data'),
        'data.lignes.php')
];