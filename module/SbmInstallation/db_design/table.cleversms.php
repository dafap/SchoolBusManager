<?php
/**
 * Structure de la table `cleversms`
 *
 * @project sbm
 * @package SbmInstallation/db_design
 * @filesource table.cleversms.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 21 déc. 2019
 * @version 2019-2.5.4
 */
use SbmBase\Model\StdLib;

return [
    'name' => 'cleversms',
    'drop' => false,
    'edit_entity' => false,
    'add_data' => false,
    'type' => 'table',
    'structure' => [
        'fields' => [
            'cleversmsId' => 'int(11) NOT NULL AUTO_INCREMENT',
            'reference' => 'varchar(64) NOT NULL DEFAULT ""',
            'nb_recipients' => 'int(11) NOT NULL DEFAULT "0"',
            'units' => 'int(11) NOT NULL DEFAULT "0"',
            'filename' => 'varchar(64) NOT NULL DEFAULT ""',
            'text' => 'text NULL',
            'encoding' => 'tinyint(1) NOT NULL DEFAULT "3"',
            'send_date' => 'datetime NOT NULL DEFAULT "1900-01-01 00:00:00"',
            'mo' => 'int(11) NOT NULL DEFAULT "0"',
            'http_code' => 'int(11) NULL'
        ],
        'primary_key' => [
            'cleversmsId'
        ],
        'keys' => [
            'SMS_reference' => [
                'unique' => true,
                'fields' => [
                    'reference'
                ]
            ]
        ],
        'engine' => 'InnoDb',
        'charset' => 'utf8mb4',
        'collate' => 'utf8mb4_unicode_ci'
    ],
    'data' => StdLib::concatPath(StdLib::findParentPath(__DIR__, 'data/data'),
        'data.cleversms.php')
];