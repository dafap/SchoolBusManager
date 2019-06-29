<?php
/**
 * Structure de la table des `payfip`
 *
 * @project sbm
 * @package SbmInstallation/db_design
 * @filesource table.payfip.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 20 juin 2019
 * @version 2019-2.5.0
 */
use SbmBase\Model\StdLib;

return [
    'name' => 'payfip',
    'drop' => false,
    'edit_entity' => false,
    'add_data' => false,
    'type' => 'table',
    'structure' => [
        'fields' => [
            'payfipId' => 'int(11) NOT NULL AUTO_INCREMENT',
            'selection' => 'tinyint(1) NOT NULL DEFAULT "0"',
            'exer' => 'int(4) NOT NULL DEFAULT "0"',
            'montant' => 'int(7) NOT NULL DEFAULT "0"',
            'dattrans' => 'char(8) NOT NULL',
            'heurtrans' => 'char(4) NOT NULL',
            'resultrans' => 'char(1) NOT NULL',
            'numauto' => 'varchar(16) NOT NULL DEFAULT ""',
            'refdet' => 'varchar(30) NOT NULL',
            'titulaire' => 'varchar(45) NOT NULL',
            'mel' => 'varchar(80) NOT NULL',
            'objet' => 'varchar(100) NOT NULL',
            'saisie' => 'char(1) NOT NULL',
            'numcli' => 'char(6) NOT NULL',
            'idOp' => 'char(36) NOT NULL',
        ],
        'primary_key' => [
            'payfipId'
        ],
        'keys' => [
            'PAYFIP_date_id' => [
                'unique' => false,
                'fields' => [
                    'dattrans',
                    'heurtrans'
                ]
            ],
            'PAYFIP_titulaire' => [
                'fields' => [
                    'titulaire'
                ]
            ]
        ],
        'engine' => 'InnoDb',
        'charset' => 'utf8',
        'collate' => 'utf8_unicode_ci'
    ],
    'data' => StdLib::concatPath(StdLib::findParentPath(__DIR__, 'data/data'),
        'data.payfip.php')
];

