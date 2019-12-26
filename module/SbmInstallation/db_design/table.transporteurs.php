<?php
/**
 * Structure de la table des `transporteurs`
 *
 *
 * @project sbm
 * @package SbmInstallation/db_design
 * @filesource table.transporteurs.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 21 déc. 2019
 * @version 2019-2.5.4
 */
use SbmBase\Model\StdLib;

return [
    'name' => 'transporteurs',
    'type' => 'table',
    'drop' => false,
    'edit_entity' => false,
    'add_data' => false,
    'structure' => [
        'fields' => [
            'transporteurId' => 'int(11) NOT NULL AUTO_INCREMENT',
            'selection' => 'tinyint(1) NOT NULL DEFAULT "0"',
            'nom' => 'varchar(30) NOT NULL',
            'adresse1' => 'varchar(38) NOT NULL DEFAULT ""',
            'adresse2' => 'varchar(38) NOT NULL DEFAULT ""',
            'codePostal' => 'varchar(5) NOT NULL',
            'communeId' => 'varchar(6) NOT NULL',
            'telephone' => 'varchar(10) NOT NULL DEFAULT ""',
            'fax' => 'varchar(10) NOT NULL DEFAULT ""',
            'email' => 'varchar(80) NOT NULL DEFAULT ""',
            'siret' => 'varchar(14) NOT NULL DEFAULT ""',
            'naf' => 'varchar(5) NOT NULL DEFAULT ""',
            'tvaIntraCommunautaire' => 'varchar(13) NOT NULL DEFAULT ""',
            'rib_titulaire' => 'varchar(32) NOT NULL DEFAULT ""',
            'rib_domiciliation' => 'varchar(24) NOT NULL DEFAULT ""',
            'rib_bic' => 'varchar(11) NOT NULL DEFAULT ""',
            'rib_iban' => 'varchar(34) NOT NULL DEFAULT ""'
        ],
        'primary_key' => [
            'transporteurId'
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
        'engine' => 'InnoDB',
        'charset' => 'utf8mb4',
        'collate' => 'utf8mb4_unicode_ci'
    ],
    'data' => StdLib::concatPath(StdLib::findParentPath(__DIR__, 'data/data'),
        'data.transporteurs.php')
];