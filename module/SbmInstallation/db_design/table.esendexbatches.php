<?php
/**
 * Structure de la table `esendexbatches`
 *
 * @project sbm
 * @package SbmInstallation/db_design
 * @filesource table.esendexbatches.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 29 mai 2020
 * @version 2020-2.6.0
 */
use SbmBase\Model\StdLib;

return [
    'name' => 'esendexbatches',
    'drop' => false,
    'edit_entity' => false,
    'add_data' => false,
    'type' => 'table',
    'structure' => [
        'fields' => [
            'esendexbatchId' => 'int(11) NOT NULL AUTO_INCREMENT',
            'nb_demandes' => 'int(11) NOT NULL DEFAULT "0"',
            'nb_envois' => 'int(11) NOT NULL DEFAULT "0"',
            'body' => 'text NULL',
            'batchid' => 'varchar(255) NOT NULL',
            'date' => 'datetime NOT NULL DEFAULT "1900-01-01 00:00:00"'
        ],
        'primary_key' => [
            'esendexbatchId'
        ],
        'keys' => [
            'batchiddate' => [
                'unique' => true,
                'fields' => [
                    'batchid',
                    'date'
                ]
            ]
        ],
        'engine' => 'InnoDb',
        'charset' => 'utf8mb4',
        'collate' => 'utf8mb4_unicode_ci'
    ],
    'data' => StdLib::concatPath(StdLib::findParentPath(__DIR__, 'data/data'),
        'data.esendexbatches.php')
];