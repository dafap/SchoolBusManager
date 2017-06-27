<?php
/**
 * Structure de la table des `eleves`
 *
 * Découpage en `eleves`, `scolarites`, `affectations` et `responsables`
 * 
 * @project sbm
 * @package SbmInstallation/db_design
 * @filesource table.eleves.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 27 juin 2017
 * @version 2017-2.3.3
 */
return array(
    'name' => 'eleves',
    'type' => 'table',
    'drop' => false,
    'edit_entity' => false,
    'add_data' => false,
    'structure' => array(
        'fields' => array(
            'eleveId' => 'int(11) NOT NULL AUTO_INCREMENT',
            'selection' => 'tinyint(1) NOT NULL DEFAULT "0"',
            'mailchimp' => 'tinyint(1) NOT NULL DEFAULT "1"',
            'dateCreation' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'dateModification' => 'datetime NOT NULL DEFAULT "1900-01-01 00:00:00"',
            'nom' => 'varchar(30) NOT NULL',
            'nomSA' => 'varchar(30) NOT NULL',
            'prenom' => 'varchar(30) NOT NULL',
            'prenomSA' => 'varchar(30) NOT NULL',
            'dateN' => 'date NOT NULL',
            'numero' => 'int(11) NOT NULL',
            'responsable1Id' => 'int(11) NOT NULL DEFAULT "0"',
            'x1' => 'decimal(18,10) NOT NULL DEFAULT "0"', 
            'y1' => 'decimal(18,10) NOT NULL DEFAULT "0"', 
            'geopt1' => 'GEOMETRY',
            'responsable2Id' => 'int(11) DEFAULT NULL',
            'x2' => 'decimal(18,10) DEFAULT NULL',
            'y2' => 'decimal(18,10) DEFAULT NULL',
            'geopt2' => 'GEOMETRY',
            'responsableFId' => 'int(11) DEFAULT NULL',
            'note' => 'text NULL',
            'id_ccda' => 'int(11)' // gid CCDA
        ),
        'primary_key' => array(
            'eleveId'
        ),
        //'keys' => array('responsable1Id' => array('fields' => array('responsable1Id'))),
        'foreign key' => array(
            array(
                'key' => 'responsable1Id',
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
            )
        ),
        'engine' => 'InnoDB',
        'charset' => 'utf8',
        'collate' => 'utf8_unicode_ci'
    ),
    'triggers' => array(
        'eleves_bi_history' => array(
            'moment' => 'BEFORE',
            'evenement' => 'INSERT',
            'definition' => <<<EOT
INSERT INTO %system(history)% (table_name, action, id_name, id_int, dt, log)
VALUES ('%table(eleves)%', 'insert', 'eleveId', NEW.eleveId, NOW(), CONCAT(NEW.selection, '|', NEW.dateCreation, '|', NEW.dateModification, '|', NEW.nom, '|', NEW.nomSA, '|', NEW.prenom, '|', NEW.prenomSA, '|', NEW.dateN, '|', NEW.numero, '|', NEW.responsable1Id, '|', IFNULL(NEW.responsable2Id,''), '|', IFNULL(NEW.responsableFId,'')))
EOT

        ),
        'eleves_bu_history' => array(
            'moment' => 'BEFORE',
            'evenement' => 'UPDATE',
            'definition' => <<<EOT
INSERT INTO %system(history)% (table_name, action, id_name, id_int, dt, log)
VALUES ('%table(eleves)%', 'update', 'eleveId', OLD.eleveId, NOW(), CONCAT(OLD.selection, '|', OLD.dateCreation, '|', OLD.dateModification, '|', OLD.nom, '|', OLD.nomSA, '|', OLD.prenom, '|', OLD.prenomSA, '|', OLD.dateN, '|', OLD.numero, '|', OLD.responsable1Id, '|', IFNULL(OLD.responsable2Id,''), '|', IFNULL(OLD.responsableFId,'')))
EOT

        ),
        'eleves_bd_history' => array(
            'moment' => 'BEFORE',
            'evenement' => 'DELETE',
            'definition' => <<<EOT
INSERT INTO %system(history)% (table_name, action, id_name, id_int, dt, log)
VALUES ('%table(eleves)%', 'delete', 'eleveId', OLD.eleveId, NOW(), CONCAT(OLD.selection, '|', OLD.dateCreation, '|', OLD.dateModification, '|', OLD.nom, '|', OLD.nomSA, '|', OLD.prenom, '|', OLD.prenomSA, '|', OLD.dateN, '|', OLD.numero, '|', OLD.responsable1Id, '|', IFNULL(OLD.responsable2Id,''), '|', IFNULL(OLD.responsableFId,'')))
EOT

        )
    ),
    //'data' => array('after' => array('responsables'),'include' => __DIR__ . '/data/data.eleves.php')
    'data' => __DIR__ . '/data/data.eleves.php'
); 