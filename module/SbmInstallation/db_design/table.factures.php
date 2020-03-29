<?php
/**
 * Structure de la table des `factures`
 *
 * Les factures sont numérotées séquentiellement à partir de 1 chaque année civile.
 * Elles sont relatives à une année scolaire et sont rattachées à un responsable.
 *
 * @project sbm
 * @package SbmInstallation/db_design
 * @filesource table.factures.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 29 mars 2020
 * @version 2020-2.6.0
 */
use SbmBase\Model\StdLib;

return [
    'name' => 'factures',
    'type' => 'table',
    'drop' => false,
    'edit_entity' => false,
    'add_data' => false,
    'structure' => [
        'fields' => [
            'exercice' => 'int(11) NOT NULL',
            'numero' => 'int(11) NOT NULL',
            'millesime' => 'int(4) NOT NULL',
            'responsableId' => 'int(11) NOT NULL',
            'date' => 'date NOT NULL',
            'montant' => 'decimal(11,2) NOT NULL',
            'signature' => 'varchar(40) NOT NULL',
            'content' => 'blob NOT NULL'
        ],
        'primary_key' => [
            'exercice',
            'numero'
        ],
        'keys' => [
            'FACTURES_millesime' => [
                'unique' => false,
                'fields' => [
                    'millesime'
                ]
            ],
            'FACTURES_responsableId' => [
                'unique' => false,
                'fields' => [
                    'responsableId'
                ]
            ],
            'FACTURES_signature' => [
                'unique' => false,
                'fields' => [
                    'signature'
                ]
            ]
        ],
        'foreign key' => [
            [
                'key' => 'responsableId',
                'references' => [
                    'table' => 'responsables',
                    'fields' => [
                        'responsableId'
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
        'data.factures.php')
];