<?php
/**
 * Structure de la table des `eleves`
 *
 * Découpage en `eleves`, `scolarites`, `affectations` et `responsables`
 * 
 * @project sbm
 * @package SbmInstallation/db_design
 * @filesource table.scolarites.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 10 oct. 2014
 * @version 2014-1
 */
return array(
    'name' => 'scolarites',
    'type' => 'table',
    'drop' => false,
    'edit_entity' => false,
    'add_data' => false,
    'structure' => array(
        'fields' => array(
            'millesime' => 'int(4) NOT NULL DEFAULT "0"',
            'eleveId' => 'int(11) NOT NULL DEFAULT "0"',
            'selection' => 'tinyint(1) NOT NULL DEFAULT "0"',
            'dateInscription' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'dateModification' => 'datetime NOT NULL DEFAULT "1900-01-01 00:00:00"',
            'etablissementId' => 'char(8) NOT NULL',
            'classeId' => 'int(11) NOT NULL DEFAULT "0"',
            'adresseL1' => 'varchar(38) NOT NULL',
            'adresseL2' => 'varchar(38) NOT NULL DEFAULT ""',
            'communeId' => 'varchar(6) NOT NULL',
            'dateEtiquette' => 'datetime NOT NULL DEFAULT "1900-01-01 00:00:00"',
            'dateCarte' => 'datetime NOT NULL DEFAULT "1900-01-01 00:00:00"',
            'inscrit' => 'tinyint(1) NOT NULL DEFAULT "1"',
            'gratuit' => 'tinyint(1) NOT NULL DEFAULT "0"',
            'afacturer' => 'tinyint(1) NOT NULL DEFAULT "1"',
            'anneeComplete' => 'tinyint(1) NOT NULL DEFAULT "1"',
            'subvention' => 'tinyint(1) NOT NULL DEFAULT "1"',
            'derogation' => 'tinyint(1) NOT NULL DEFAULT "0"',
            'dateDebut' => 'date NOT NULL',
            'dateFin' => 'date NOT NULL',
            'subventionTaux' => 'int(3) NOT NULL DEFAULT "0"',
            'tarifId' => 'int(11) NOT NULL DEFAULT "0"',
            'regimeId' => 'tinyint(1) NOT NULL DEFAULT "0"',
            'derogationMotif' => 'text'
        ),
        'primary_key' => array(
            'millesime',
            'eleveId'
        ),
        'foreign key' => array(
            array(
                'key' => 'eleveId',
                'references' => array(
                    'table' => 'eleves',
                    'fields' => array(
                        'eleveId'
                    ),
                    'on' => array(
                        'update' => 'CASCADE',
                        'delete' => 'RESTRICT'
                    )
                )
            ),
            array(
                'key' => 'etablissementId',
                'references' => array(
                    'table' => 'etablissements',
                    'fields' => array(
                        'etablissementId'
                    ),
                    'on' => array(
                        'update' => 'CASCADE',
                        'delete' => 'RESTRICT'
                    )
                )
            ),
            array(
                'key' => 'classeId',
                'references' => array(
                    'table' => 'classes',
                    'fields' => array(
                        'classeId'
                    ),
                    'on' => array(
                        'update' => 'CASCADE',
                        'delete' => 'RESTRICT'
                    )
                )
            ),
            array(
                'key' => 'communeId',
                'references' => array(
                    'table' => 'communes',
                    'fields' => array(
                        'communeId'
                    ),
                    'on' => array(
                        'update' => 'CASCADE',
                        'delete' => 'RESTRICT'
                    )
                )
            ),
            array(
                'key' => 'tarifId',
                'references' => array(
                    'table' => 'tarifs',
                    'fields' => array(
                        'tarifId'
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
        'scolarites_bi_history' => array(
            'moment' => 'BEFORE',
            'evenement' => 'INSERT',
            'definition' => <<<EOT
INSERT INTO %system(history)% (table_name, action, id_name, id_txt, dt, log)
VALUES ('%table(scolarites)%', 'insert', CONCAT_WS('|', 'millesime', 'eleveId'), CONCAT_WS('|', NEW.millesime, NEW.eleveId), NOW(), CONCAT_WS('|', NEW.selection, NEW.dateInscription, NEW.dateModification, NEW.etablissementId, NEW.classeId, NEW.adresseL1, NEW.adresseL2, NEW.communeId, NEW.dateEtiquette, NEW.dateCarte, NEW.inscrit, NEW.gratuit, NEW.afacturer, NEW.anneeComplete, NEW.subvention, NEW.derogation, NEW.dateDebut, NEW.dateFin, NEW.subventionTaux, NEW.tarifId, NEW.regimeId, NEW.derogationMotif))
EOT

        ),
        'scolarites_bu_history' => array(
            'moment' => 'BEFORE',
            'evenement' => 'UPDATE',
            'definition' => <<<EOT
INSERT INTO %system(history)% (table_name, action, id_name, id_txt, dt, log)
VALUES ('%table(scolarites)%', 'update', CONCAT_WS('|', 'millesime', 'eleveId'), CONCAT_WS('|', OLD.millesime, OLD.eleveId), NOW(), CONCAT_WS('|', OLD.selection, OLD.dateInscription, OLD.dateModification, OLD.etablissementId, OLD.classeId, OLD.adresseL1, OLD.adresseL2, OLD.communeId, OLD.dateEtiquette, OLD.dateCarte, OLD.inscrit, OLD.gratuit, OLD.afacturer, OLD.anneeComplete, OLD.subvention, OLD.derogation, OLD.dateDebut, OLD.dateFin, OLD.subventionTaux, OLD.tarifId, OLD.regimeId, OLD.derogationMotif))
EOT

        ),
        'scolarites_bd_history' => array(
            'moment' => 'BEFORE',
            'evenement' => 'DELETE',
            'definition' => <<<EOT
INSERT INTO %system(history)% (table_name, action, id_name, id_txt, dt, log)
VALUES ('%table(scolarites)%', 'delete', CONCAT_WS('|', 'millesime', 'eleveId'), CONCAT_WS('|', OLD.millesime, OLD.eleveId), NOW(), CONCAT_WS('|', OLD.selection, OLD.dateInscription, OLD.dateModification, OLD.etablissementId, OLD.classeId, OLD.adresseL1, OLD.adresseL2, OLD.communeId, OLD.dateEtiquette, OLD.dateCarte, OLD.inscrit, OLD.gratuit, OLD.afacturer, OLD.anneeComplete, OLD.subvention, OLD.derogation, OLD.dateDebut, OLD.dateFin, OLD.subventionTaux, OLD.tarifId, OLD.regimeId, OLD.derogationMotif))
EOT

        )
    ),
    
    // 'data' => include __DIR__ . '/data/data.scolarites.php'
    // 'data' => array('after' => array('eleves','etablissements','classes','communes','tarifs'),'include' => __DIR__ . '/data/data.scolarites.php')
    'data' => __DIR__ . '/data/data.scolarites.php'    
); 