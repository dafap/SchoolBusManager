<?php
/**
 * Structure de la table des `affectations`
 *
 * Découpage en `eleves`, `scolarites`, `affectations` et `responsables`
 *
 * @project sbm
 * @package SbmInstallation/db_design
 * @filesource table.staffectations.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 18 juil. 2020
 * @version 2020-2.6.0
 */
use SbmBase\Model\StdLib;

return [
    'name' => 'affectations',
    'type' => 'table',
    'drop' => false,
    'edit_entity' => false,
    'add_data' => false,
    'structure' => [
        'fields' => [
            'millesime' => 'int(4) NOT NULL',
            'eleveId' => 'int(11) NOT NULL DEFAULT "0"',
            'trajet' => 'tinyint(3) UNSIGNED NOT NULL DEFAULT "1"', // 1 pour responsable 1,
                                                            // 2 pour responsable 2
            'jours' => 'tinyint(3) UNSIGNED NOT NULL DEFAULT "31"', // 4 me, 15 lmj, 16 v,
                                                            // 27 lmjv, 31 lmmjv, 64 di
            'moment' => 'tinyint(3) UNSIGNED NOT NULL DEFAULT "1"', // 1 matin, 2 midi, 3 soir
            'correspondance' => 'tinyint(1) NOT NULL DEFAULT "1"', // de 1 à n dans l'ordre chronologique
            'selection' => 'tinyint(1) NOT NULL DEFAULT "0"',
            'responsableId' => 'int(11) NOT NULL',
            'station1Id' => 'int(11) NOT NULL',
            'ligne1Id' => 'varchar(5) NOT NULL',
            'sensligne1' => 'tinyint(3) UNSIGNED NOT NULL DEFAULT "1"',
            'ordreligne1' => 'tinyint(3) UNSIGNED NOT NULL DEFAULT "1"',
            'station2Id' => 'int(11) DEFAULT NULL', // point de correspondance
            'ligne2Id' => 'varchar(5) DEFAULT NULL',
            'sensligne2' => 'tinyint(3) UNSIGNED NOT NULL DEFAULT "1"',
            'ordreligne2' => 'tinyint(3) UNSIGNED NOT NULL DEFAULT "1"'
        ], // service suivant
        'primary_key' => [
            'millesime',
            'eleveId',
            'trajet',
            'jours',
            'moment',
            'correspondance',
            'sensligne1'
        ],
        'foreign key' => [
            [
                'key' => [
                    'millesime',
                    'eleveId'
                ],
                'references' => [
                    'table' => 'scolarites',
                    'fields' => [
                        'millesime',
                        'eleveId'
                    ],
                    'on' => [
                        'update' => 'CASCADE',
                        'delete' => 'RESTRICT'
                    ]
                ]
            ],
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
            ],
            [
                'key' => 'station1Id',
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
            ],
            [
                'key' => [
                    'millesime',
                    'ligne1Id',
                    'sensligne1',
                    'moment',
                    'ordreligne1'
                ],
                'references' => [
                    'table' => 'services',
                    'fields' => [
                        'millesime',
                        'ligneId',
                        'sens',
                        'moment',
                        'ordre'
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
    'triggers' => [
        'affectations_bi_history' => [
            'moment' => 'BEFORE',
            'evenement' => 'INSERT',
            'definition' => <<<EOT
INSERT INTO %system(history)% (table_name, action, id_name, id_txt, dt, log)
VALUES ('%table(affectations)%', 'insert', CONCAT_WS('|', 'millesime', 'eleveId', 'trajet', 'jours', 'moment', 'correspondance'), CONCAT_WS('|', NEW.millesime, NEW.eleveId, NEW.trajet, NEW.jours, NEW.moment, NEW.correspondance), NOW(), CONCAT_WS('|', NEW.selection, NEW.responsableId, NEW.station1Id, NEW.ligne1Id, NEW.sensligne1, NEW.ordreligne1, NEW.station2Id, NEW.ligne2Id, NEW.sensligne2, NEW.ordreligne2))
EOT

        ],
        'affectations_bu_history' => [
            'moment' => 'BEFORE',
            'evenement' => 'UPDATE',
            'definition' => <<<EOT
INSERT INTO %system(history)% (table_name, action, id_name, id_txt, dt, log)
VALUES ('%table(affectations)%', 'update', CONCAT_WS('|', 'millesime', 'eleveId', 'trajet', 'jours', 'moment', 'correspondance'), CONCAT_WS('|', OLD.millesime, OLD.eleveId, OLD.trajet, OLD.jours, OLD.moment, OLD.correspondance), NOW(), CONCAT_WS('|', OLD.selection, OLD.responsableId, OLD.station1Id, OLD.ligne1Id, OLD.sensligne1, OLD.ordreligne1, OLD.station2Id, OLD.ligne2Id, OLD.sensligne2, OLD.ordreligne2))
EOT

        ],
        'affectations_bd_history' => [
            'moment' => 'BEFORE',
            'evenement' => 'DELETE',
            'definition' => <<<EOT
INSERT INTO %system(history)% (table_name, action, id_name, id_txt, dt, log)
VALUES ('%table(affectations)%', 'delete', CONCAT_WS('|', 'millesime', 'eleveId', 'trajet', 'jours', 'moment', 'correspondance'), CONCAT_WS('|', OLD.millesime, OLD.eleveId, OLD.trajet, OLD.jours, OLD.moment, OLD.correspondance), NOW(), CONCAT_WS('|', OLD.selection, OLD.responsableId, OLD.station1Id, OLD.ligne1Id, OLD.sensligne1, OLD.ordreligne1, OLD.station2Id, OLD.ligne2Id, OLD.sensligne2, OLD.ordreligne2))
EOT

        ]
    ],
    'data' => StdLib::concatPath(StdLib::findParentPath(__DIR__, 'data/data'),
        'data.affectations.php')
];