<?php
/**
 * Structure de la table `zonage`
 *
 * @project sbm
 * @package SbmInstallation/db_design
 * @filesource table.zonage.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 21 déc. 2019
 * @version 2019-2-5-4
 */
/**
 * *************************************************************************
 * structure de la table 'classes'
 * Table MyISAM encodée utf8
 * Description des champs
 * - rpiId est un auto-incrément
 * - nom est un texte de 10 c maxi
 * - libelle est le nom detaillé
 * - niveau indique quels niveaux sont concernés
 * Les niveaux sont établis en composant par "Et binaire" les valeurs :
 * - 1 pour maternelle
 * - 2 pour élémentaire
 * - 3 pour maternelle et élémentaire (primaire)
 * *************************************************************************
 */
use SbmBase\Model\StdLib;

return [
    'name' => 'zonage',
    'drop' => true,
    'edit_entity' => true,
    'add_data' => false,
    'type' => 'table',
    'structure' => [
        'fields' => [
            'zonageId' => 'int(11) NOT NULL AUTO_INCREMENT',
            'communeId' => 'varchar(6) NOT NULL',
            'nom' => 'varchar(45) NOT NULL',
            'nomSA' => 'varchar(45) NOT NULL',
            'inscriptionenligne' => 'tinyint(1) NOT NULL DEFAULT "0"',
            'paiementenligne' => 'tinyint(1) NOT NULL DEFAULT "0"',
            'selection' => 'tinyint(1) NOT NULL DEFAULT "0"'
        ],
        'primary_key' => [
            'zonageId'
        ],
        'keys' => [
            'ZONAGE_NomSA' =>[
                'unique' => true,
                'fields' => [
                    'nomSA'
                ]
            ]
        ],
        'foreign key' => [
            [
                'key' => 'communeId',
                'references' => [
                    'table' => 'communes',
                    'fields' => [
                        'communeId'
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
        'data.zonage.php')
];
