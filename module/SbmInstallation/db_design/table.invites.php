<?php
/**
 * Structure de la table des `eleves`
 *
 * Description longue du fichier s'il y en a une
 *
 * @project sbm
 * @package SbmInstallation/db_design
 * @filesource table.invites.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 1 avr. 2021
 * @version 2021-2.6.1
 */
use SbmBase\Model\StdLib;

return [
    'name' => 'invites',
    'type' => 'table',
    'drop' => true,
    'edit_entity' => true,
    'add_data' => false,
    'structure' => [
        'fields' => [
            'inviteId' => 'int(11) NOT NULL AUTO_INCREMENT',
            'millesime' => 'int(4) NOT NULL',
            'dateCreation' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'dateModification' => 'datetime NOT NULL DEFAULT "1900-01-01 00:00:00"',
            'dateDebut' => 'date NOT NULL',
            'dateFin' => 'date NOT NULL',
            'dateCarte' => 'datetime NOT NULL DEFAULT "1900-01-01 00:00:00"',
            'nom' => 'varchar(30) NOT NULL',
            'nomSA' => 'varchar(30) NOT NULL',
            'prenom' => 'varchar(30) NOT NULL',
            'prenomSA' => 'varchar(30) NOT NULL',
            'sexe' => 'tinyint(1) NOT NULL DEFAULT "1"', // 1 ou 2
            'nationalite' => 'char(2) NOT NULL DEFAULT "??"',
            'etablissementId' => 'char(8) NOT NULL',
            'chez' => 'varchar(38) DEFAULT NULL',
            'adresseL1' => 'varchar(38) NOT NULL',
            'adresseL2' => 'varchar(38) DEFAULT NULL',
            'adresseL3' => 'varchar(38) DEFAULT NULL',
            'codePostal' => 'varchar(5) NOT NULL',
            'communeId' => 'varchar(6) NOT NULL',
            'joursTransport' => 'tinyint(3) unsigned NOT NULL DEFAULT "31"',
            'stationId' => 'int(11) NOT NULL',
            'servicesMatin' => 'varchar(30) NOT NULL',
            'servicesMidi' => 'varchar(30) NOT NULL DEFAULT ""',
            'servicesSoir' => 'varchar(30) NOT NULL',
            'servicesMerSoir' => 'varchar(30) NOT NULL DEFAULT ""',
            'demande' => 'tinyint(1) NOT NULL DEFAULT "1"',
            'inscrit' => 'tinyint(1) NOT NULL DEFAULT "1"',
            'gratuit' => 'tinyint(1) NOT NULL DEFAULT "0"',
            'paiement' => 'tinyint(1) NOT NULL DEFAULT "0"',
            'duplicata' => 'int(11) NOT NULL DEFAULT "0"',
            'selection' => 'tinyint(1) NOT NULL DEFAULT "0"',
            'responsableId' => 'int(11) NOT NULL DEFAULT "0"',
            'eleveId' => 'int(11) NOT NULL DEFAULT "0"',
            'organismeId' => 'int(11) NOT NULL DEFAULT "0"',
            'motifDemande' => 'text NULL',
            'motifRefus' => 'text NULL',
            'commentaire' => 'text NULL'
        ],
        'primary_key' => [
            'inviteId'
        ],
        'keys' => [
            'INVITE_etablissement' => [
                'unique' => false,
                'fields' => [
                    'millesime',
                    'etablissementId',
                    'dateDebut',
                    'dateFin',
                ]
            ],
        ],
        'foreign key' => [
            [
                'key' => 'etablissementId',
                'references' => [
                    'table' => 'etablissements',
                    'fields' => [
                        'etablissementId'
                    ],
                    'on' => [
                        'update' => 'CASCADE',
                        'delete' => 'RESTRICT'
                    ]
                ]
            ],
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
            ],
            [
                'key' => 'stationId',
                'references' => [
                    'table' => 'stations',
                    'fields' => [
                        'stationId'
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
        'data.invites.php')
];