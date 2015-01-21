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
 * @date 12 oct. 2014
 * @version 2014-1
 */
return array(
    'name' => 'affectations',
    'type' => 'table',
    'drop' => false,
    'edit_entity' => false,
    'add_data' => false,
    'structure' => array(
        'fields' => array(
            'millesime' => 'int(4) NOT NULL DEFAULT "0"',
            'eleveId' => 'int(11) NOT NULL DEFAULT "0"',
            'trajet' => 'tinyint(1) NOT NULL DEFAULT "1"', // 1 pour le responsable 1, 2 pour le responsable 2
            'jours' => 'tinyint(2) NOT NULL DEFAULT "31"', // 27 semaine, 4 mercredi, 32 samedi - indiquer 63 pour semaine complète ou 59 pour semaine + samedi
            'sens' => 'tinyint(1) NOT NULL DEFAULT "3"', // 1 pour aller / 2 pour retour / 3 pour aller-retour
            'correspondance' => 'tinyint(1) NOT NULL DEFAULT "1"', // de 1 à n à partir du domicile
            'selection' => 'tinyint(1) NOT NULL DEFAULT "0"',
            'responsableId' => 'int(11) NOT NULL',
            'station1Id' => 'int(11) NOT NULL',
            'service1Id' => 'varchar(11) NOT NULL',
            'station2Id' => 'int(11) DEFAULT NULL', // point de correspondance
            'service2Id' => 'varchar(11) DEFAULT NULL'
        ), // service suivant
        'primary_key' => array(
            'millesime',
            'eleveId',
            'trajet',
            'jours',
            'sens',
            'correspondance'
        ),
        'foreign key' => array(
            array(
                'key' => array('millesime','eleveId'),
                'references' => array(
                    'table' => 'scolarites',
                    'fields' => array(
                        'millesime','eleveId'
                    ),
                    'on' => array(
                        'update' => 'CASCADE',
                        'delete' => 'RESTRICT'
                    )
                )
            ),
            array(
                'key' => 'responsableId',
                'references' => array(
                    'table' => 'responsables',
                    'fields' => array(
                        'responsableId'
                    ),
                    'on' => array(
                        'update' => 'CASCADE',
                        'delete' => 'RESTRICT'
                    )
                )
            ),
            array(
                'key' => 'station1Id',
                'references' => array(
                    'table' => 'stations',
                    'fields' => array(
                        'stationId'
                    ),
                    'on' => array(
                        'update' => 'CASCADE',
                        'delete' => 'RESTRICT'
                    )
                )
            ),
            array(
                'key' => 'service1Id',
                'references' => array(
                    'table' => 'services',
                    'fields' => array(
                        'serviceId'
                    ),
                    'on' => array(
                        'update' => 'CASCADE',
                        'delete' => 'RESTRICT'
                    )
                )
            ),
        ),
        'engine' => 'InnoDB',
        'charset' => 'utf8',
        'collate' => 'utf8_unicode_ci'
    ),
    'triggers' => array(
        'affectations_bi_history' => array(
            'moment' => 'BEFORE',
            'evenement' => 'INSERT',
            'definition' => <<<EOT
INSERT INTO %system(history)% (table_name, action, id_name, id_txt, dt, log)
VALUES ('%table(affectations)%', 'insert', CONCAT_WS('|', 'millesime', 'eleveId', 'trajet', 'jours', 'sens', 'correspondance'), CONCAT_WS('|', NEW.millesime, NEW.eleveId, NEW.trajet, NEW.jours, NEW.sens, NEW.correspondance), NOW(), CONCAT_WS('|', NEW.selection, NEW.responsableId, NEW.station1Id, NEW.service1Id, NEW.station2Id, NEW.service2Id))
EOT

        ),
        'affectations_bu_history' => array(
            'moment' => 'BEFORE',
            'evenement' => 'UPDATE',
            'definition' => <<<EOT
INSERT INTO %system(history)% (table_name, action, id_name, id_txt, dt, log)
VALUES ('%table(affectations)%', 'update', CONCAT_WS('|', 'millesime', 'eleveId', 'trajet', 'jours', 'sens', 'correspondance'), CONCAT_WS('|', OLD.millesime, OLD.eleveId, OLD.trajet, OLD.jours, OLD.sens, OLD.correspondance), NOW(), CONCA_WST('|', OLD.selection, OLD.responsableId, OLD.station1Id, OLD.service1Id, OLD.station2Id, OLD.service2Id))
EOT

        ),
        'affectations_bd_history' => array(
            'moment' => 'BEFORE',
            'evenement' => 'DELETE',
            'definition' => <<<EOT
INSERT INTO %system(history)% (table_name, action, id_name, id_txt, dt, log)
VALUES ('%table(affectations)%', 'delete', CONCAT_WS('|', 'millesime', 'eleveId', 'trajet', 'jours', 'sens', 'correspondance'), CONCAT_WS('|', OLD.millesime, OLD.eleveId, OLD.trajet, OLD.jours, OLD.sens, OLD.correspondance), NOW(), CONCAT_WS('|', OLD.selection, OLD.responsableId, OLD.station1Id, OLD.service1Id, OLD.station2Id, OLD.service2Id))
EOT

        )
    ),
    
    // 'data' => include __DIR__ . '/data/data.affectations.php'
    //'data' => array('after' => array('eleves','responsables','stations','services'),'include' => __DIR__ . '/data/data.affectations.php')
    'data' => __DIR__ . '/data/data.affectations.php'
); 