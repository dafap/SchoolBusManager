<?php
/**
 * Structure de la table des `paybox`
 *
 * @project sbm
 * @package SbmInstallation/db_design
 * @filesource table.paybox.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 23 avr. 2020
 * @version 2020-2.6.0
 */
use SbmBase\Model\StdLib;

return [
    'name' => 'paybox',
    'drop' => false,
    'edit_entity' => false,
    'add_data' => false,
    'type' => 'table',
    'structure' => [
        'fields' => [
            'payboxId' => 'int(11) NOT NULL AUTO_INCREMENT',
            'selection' => 'tinyint(1) NOT NULL DEFAULT "0"',
            'responsableId' => 'int(11) NOT NULL',
            'exercice' => 'int(4) NOT NULL',
            'numero'=>'int(11) NOT NULL',
            'auto' => 'varchar(16) NOT NULL DEFAULT ""',
            'montant' => 'int(11) NOT NULL DEFAULT "0"',
            'ref' => 'varchar(250) NOT NULL',
            'idtrans' => 'varchar(10) NOT NULL',
            'datetrans' => 'varchar(8) NOT NULL',
            'heuretrans' => 'varchar(8) NOT NULL',
            'carte' => 'varchar(20) NOT NULL',
            'bin6' => 'char(6) NOT NULL',
            'bin2' => 'char(2) NOT NULL',
            'pays' => 'char(3) NOT NULL',
            'ip' => 'char(3) NOT NULL'
        ],
        'primary_key' => [
            'payboxId'
        ],
        'keys' => [
            'PAYBOX_date_id' => [
                'unique' => false,
                'fields' => [
                    'datetrans',
                    'heuretrans'
                ]
            ],
            'PAYBOX_responsable' => [
                'unique' => false,
                'fields' => [
                    'responsableId'
                ]
            ],
            'PAYBOX_facture' => [
                'unique' => false,
                'fields' => [
                    'exercice',
                    'numero'
                ]
            ]
        ],
        'engine' => 'InnoDb',
        'charset' => 'utf8mb4',
        'collate' => 'utf8mb4_unicode_ci'
    ],
    'data' => StdLib::concatPath(StdLib::findParentPath(__DIR__, 'data/data'),
        'data.paybox.php')
];