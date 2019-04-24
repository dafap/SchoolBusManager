<?php
/**
 * Structure de la table des `lots` de marchés
 *
 * Chaque marché est découpé en lots (1 ou plusieurs).
 * Chaque lot est attribué à un transporteur.
 * Chaque lot peut être découpé en services (lignes)
 * Chaque service est attribué à un transporteur qui sera le titulaire du marché ou un sous-traitant.
 *
 * @project sbm
 * @package SbmInstallation/db_design
 * @filesource table.lots.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 25 mars 2019
 * @version 2019-2.5.0
 */
use SbmBase\Model\StdLib;

return [
    'name' => 'lots',
    'drop' => false,
    'edit_entity' => false,
    'add_data' => false,
    'type' => 'table',
    'structure' => [
        'fields' => [
            'lotId' => 'int(11) NOT NULL AUTO_INCREMENT',
            'selection' => 'tinyint(1) NOT NULL DEFAULT "0"',
            'actif' => 'tinyint(1) NOT NULL DEFAULT  "1"',
            'marche' => 'varchar(12) NOT NULL',
            'lot' => 'varchar(12) NOT NULL',
            'libelle' => 'varchar(45) NOT NULL DEFAULT ""',
            'complement' => 'varchar(45) NOT NULL DEFAULT ""',
            'dateDebut' => 'date NOT NULL',
            'dateFin' => 'date NOT NULL',
            'transporteurId' => 'int(11) NULL',
            'commentaire' => 'text NULL'
        ],
        'primary_key' => [
            'lotId'
        ],
        'keys' => [
            'LOT_Marche' => [
                'unique' => true,
                'fields' => [
                    'marche',
                    'lot'
                ]
            ]
        ],
        'foreign key' => [
            [
                'key' => 'transporteurId',
                'references' => [
                    'table' => 'transporteurs',
                    'fields' => [
                        'transporteurId'
                    ],
                    'on' => [
                        'update' => 'CASCADE',
                        'delete' => 'RESTRICT'
                    ]
                ]
            ]
        ],
        'engine' => 'InnoDb',
        'charset' => 'utf8',
        'collate' => 'utf8_unicode_ci'
    ],
    'data' => StdLib::concatPath(StdLib::findParentPath(__DIR__, 'data/data'),
        'data.lots.php')
];