<?php
/**
 * Structure de la table `zonage`
 *
 * @project sbm
 * @package SbmInstallation/db_design
 * @filesource table.zonage-index.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 10 juin 2020
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
    'name' => 'zonage-index',
    'drop' => true,
    'edit_entity' => true,
    'add_data' => false,
    'type' => 'table',
    'structure' => [
        'fields' => [
            'zonageId' => 'int(11) NOT NULL',
            'communeId' => 'varchar(6) NOT NULL',
            'mot' => 'varchar(45) NOT NULL'
        ],
        'primary_key' => [
            'zonageId','communeId','mot'
        ],
        'foreign key' => [
            [
                'key' => ['zonageId','communeId'],
                'references' => [
                    'table' => 'zonage',
                    'fields' => [
                        'zonageId',
                        'communeId'
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
        'data.zonage-index.php')
];
