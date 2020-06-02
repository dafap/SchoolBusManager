<?php
/**
 * Structure de la table `esendextelephones`
 *
 * @project sbm
 * @package SbmInstallation/db_design
 * @filesource table.esendextelephones.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 31 mai 2020
 * @version 2020-2.6.0
 */
use SbmBase\Model\StdLib;

return [
    'name' => 'esendextelephones',
    'drop' => false,
    'edit_entity' => false,
    'add_data' => false,
    'type' => 'table',
    'structure' => [
        'fields' => [
            'esendextelephoneId' => 'int(11) NOT NULL AUTO_INCREMENT',
            'esendexbatchId' => 'int(11) NOT NULL DEFAULT "0"',
            'telephone' => 'varchar(10) NOT NULL DEFAULT ""',
            'destinataire' => 'varchar(255) NOT NULL DEFAULT ""'
        ],
        'primary_key' => [
            'esendextelephoneId'
        ],
        'foreign key' => [
            [
                'key' => 'esendexbatchId',
                'references' => [
                    'table' => 'esendexbatches',
                    'fields' => [
                        'esendexbatchId'
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
        'data.esendextelephones.php')
];