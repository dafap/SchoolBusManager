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
 * @date 24 fév. 2019
 * @version 2019-2.5.0
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
            'millesime' => 'int(4) NOT NULL DEFAULT "0"',
            'eleveId' => 'int(11) NOT NULL DEFAULT "0"',
            'trajet' => 'tinyint(1) NOT NULL DEFAULT "1"', // 1 pour le responsable 1, 2 pour le
                                                            // responsable 2
            'jours' => 'tinyint(2) NOT NULL DEFAULT "31"', // 27 semaine, 4 mercredi, 32 samedi -
                                                            // indiquer 63 pour semaine complète ou
                                                            // 59 pour semaine + samedi
            'sens' => 'tinyint(1) NOT NULL DEFAULT "3"', // 1 pour aller / 2 pour retour / 3 pour
                                                          // aller-retour
            'correspondance' => 'tinyint(1) NOT NULL DEFAULT "1"', // de 1 à n à partir du
                                                                    // domicile
            'selection' => 'tinyint(1) NOT NULL DEFAULT "0"',
            'responsableId' => 'int(11) NOT NULL',
            'station1Id' => 'int(11) NOT NULL',
            'service1Id' => 'varchar(11) NOT NULL',
            'station2Id' => 'int(11) DEFAULT NULL', // point de correspondance
            'service2Id' => 'varchar(11) DEFAULT NULL'
        ], // service suivant
        'primary_key' => [
            'millesime',
            'eleveId',
            'trajet',
            'jours',
            'sens',
            'correspondance'
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
                'key' => 'service1Id',
                'references' => [
                    'table' => 'services',
                    'fields' => [
                        'serviceId'
                    ],
                    'on' => [
                        'update' => 'CASCADE',
                        'delete' => 'RESTRICT'
                    ]
                ]
            ]
        ],
        'engine' => 'InnoDB',
        'charset' => 'utf8',
        'collate' => 'utf8_unicode_ci'
    ],
    'triggers' => [
        'affectations_bi_history' => [
            'moment' => 'BEFORE',
            'evenement' => 'INSERT',
            'definition' => <<<EOT
INSERT INTO %system(history)% (table_name, action, id_name, id_txt, dt, log)
VALUES ('%table(affectations)%', 'insert', CONCAT_WS('|', 'millesime', 'eleveId', 'trajet', 'jours', 'sens', 'correspondance'), CONCAT_WS('|', NEW.millesime, NEW.eleveId, NEW.trajet, NEW.jours, NEW.sens, NEW.correspondance), NOW(), CONCAT_WS('|', NEW.selection, NEW.responsableId, NEW.station1Id, NEW.service1Id, NEW.station2Id, NEW.service2Id))
EOT

        ],
        'affectations_bu_history' => [
            'moment' => 'BEFORE',
            'evenement' => 'UPDATE',
            'definition' => <<<EOT
INSERT INTO %system(history)% (table_name, action, id_name, id_txt, dt, log)
VALUES ('%table(affectations)%', 'update', CONCAT_WS('|', 'millesime', 'eleveId', 'trajet', 'jours', 'sens', 'correspondance'), CONCAT_WS('|', OLD.millesime, OLD.eleveId, OLD.trajet, OLD.jours, OLD.sens, OLD.correspondance), NOW(), CONCAT_WS('|', OLD.selection, OLD.responsableId, OLD.station1Id, OLD.service1Id, OLD.station2Id, OLD.service2Id))
EOT

        ],
        'affectations_bd_history' => [
            'moment' => 'BEFORE',
            'evenement' => 'DELETE',
            'definition' => <<<EOT
INSERT INTO %system(history)% (table_name, action, id_name, id_txt, dt, log)
VALUES ('%table(affectations)%', 'delete', CONCAT_WS('|', 'millesime', 'eleveId', 'trajet', 'jours', 'sens', 'correspondance'), CONCAT_WS('|', OLD.millesime, OLD.eleveId, OLD.trajet, OLD.jours, OLD.sens, OLD.correspondance), NOW(), CONCAT_WS('|', OLD.selection, OLD.responsableId, OLD.station1Id, OLD.service1Id, OLD.station2Id, OLD.service2Id))
EOT

        ]
    ],
    'data' => StdLib::concatPath(StdLib::findParentPath(__DIR__, 'data/data'),
        'data.affectations.php')
];