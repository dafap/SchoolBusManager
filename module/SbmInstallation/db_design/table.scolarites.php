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
 * @version 2015-2
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
            'chez' => 'varchar(38) DEFAULT NULL',
            'adresseL1' => 'varchar(38) DEFAULT NULL',
            'adresseL2' => 'varchar(38) DEFAULT NULL',
            'codePostal' => 'varchar(5) DEFAULT NULL',
            'communeId' => 'varchar(6) DEFAULT NULL',
            'x' => 'decimal(18,10) NOT NULL DEFAULT "0"', 
            'y' => 'decimal(18,10) NOT NULL DEFAULT "0"',
            'geopt' => 'GEOMETRY DEFAULT NULL',
            'distanceR1' => 'decimal(7,3) NOT NULL DEFAULT "0.000"',
            'distanceR2' => 'decimal(7,3) NOT NULL DEFAULT "0.000"',
            'dateEtiquette' => 'datetime NOT NULL DEFAULT "1900-01-01 00:00:00"',
            'dateCarte' => 'datetime NOT NULL DEFAULT "1900-01-01 00:00:00"',
            'inscrit' => 'tinyint(1) NOT NULL DEFAULT "1"',
            'gratuit' => 'tinyint(1) NOT NULL DEFAULT "0"',
            'paiement' => 'tinyint(1) NOT NULL DEFAULT "0"',
            'fa' => 'tinyint(1) NOT NULL DEFAULT "0"', // famille d'accueil
            'anneeComplete' => 'tinyint(1) NOT NULL DEFAULT "1"',
            'subventionR1' => 'tinyint(1) NOT NULL DEFAULT "0"',
            'subventionR2' => 'tinyint(1) NOT NULL DEFAULT "0"',
            'demandeR1' => 'tinyint(1) NOT NULL DEFAULT "1"',
            'demandeR2' => 'tinyint(1) NOT NULL DEFAULT "0"',
            'accordR1' => 'tinyint(1) NOT NULL DEFAULT "1"',
            'accordR2' => 'tinyint(1) NOT NULL DEFAULT "1"',
            'internet' => 'tinyint(1) NOT NULL DEFAULT "1"',
            'district' => 'tinyint(1) NOT NULL DEFAULT "0"',
            'derogation' => 'tinyint(1) NOT NULL DEFAULT "0"',
            'dateDebut' => 'date NOT NULL',
            'dateFin' => 'date NOT NULL',
            'joursTransport' => 'tinyint(3) unsigned NOT NULL DEFAULT "127"',
            'subventionTaux' => 'int(3) NOT NULL DEFAULT "0"',
            'tarifId' => 'int(11) NOT NULL DEFAULT "0"',
            'regimeId' => 'tinyint(1) NOT NULL DEFAULT "0"',
            'motifDerogation' => 'text',
            'motifRefusR1' => 'text',
            'motifRefusR2' => 'text',
            'commentaire' => 'text'
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
VALUES ('%table(scolarites)%', 'insert', CONCAT_WS('|', 'millesime', 'eleveId'), CONCAT_WS('|', NEW.millesime, NEW.eleveId), NOW(), CONCAT_WS('|', NEW.selection, NEW.dateInscription, NEW.dateModification, NEW.etablissementId, NEW.classeId, NEW.chez, NEW.adresseL1, NEW.adresseL2, NEW.codePostal, NEW.communeId, NEW.x, NEW.y, NEW.distanceR1, NEW.distanceR2, NEW.dateEtiquette, NEW.dateCarte, NEW.inscrit, NEW.gratuit, NEW.paiement, NEW.anneeComplete, NEW.subventionR1, NEW.subventionR2, NEW.demandeR1, NEW.demandeR2, NEW.accordR1, NEW.accordR2, NEW.internet, NEW.district, NEW.derogation, NEW.dateDebut, NEW.dateFin, NEW.joursTransport, NEW.subventionTaux, NEW.tarifId, NEW.regimeId, NEW.motifDerogation, NEW.motifRefusR1, NEW.motifRefusR2, NEW.commentaire))
EOT

        ),
        'scolarites_bu_history' => array(
            'moment' => 'BEFORE',
            'evenement' => 'UPDATE',
            'definition' => <<<EOT
INSERT INTO %system(history)% (table_name, action, id_name, id_txt, dt, log)
VALUES ('%table(scolarites)%', 'update', CONCAT_WS('|', 'millesime', 'eleveId'), CONCAT_WS('|', OLD.millesime, OLD.eleveId), NOW(), CONCAT_WS('|', OLD.selection, OLD.dateInscription, OLD.dateModification, OLD.etablissementId, OLD.classeId, OLD.chez, OLD.adresseL1, OLD.adresseL2, OLD.codePostal, OLD.communeId, OLD.x, OLD.y, OLD.distanceR1, OLD.distanceR2, OLD.dateEtiquette, OLD.dateCarte, OLD.inscrit, OLD.gratuit, OLD.paiement, OLD.anneeComplete, OLD.subventionR1, OLD.subventionR2, OLD.demandeR1, OLD.demandeR2, OLD.accordR1, OLD.accordR2, OLD.internet, OLD.district, OLD.derogation, OLD.dateDebut, OLD.dateFin, OLD.joursTransport, OLD.subventionTaux, OLD.tarifId, OLD.regimeId, OLD.motifDerogation, OLD.motifRefusR1, OLD.motifRefusR2, OLD.commentaire))
EOT

        ),
        'scolarites_bd_history' => array(
            'moment' => 'BEFORE',
            'evenement' => 'DELETE',
            'definition' => <<<EOT
INSERT INTO %system(history)% (table_name, action, id_name, id_txt, dt, log)
VALUES ('%table(scolarites)%', 'delete', CONCAT_WS('|', 'millesime', 'eleveId'), CONCAT_WS('|', OLD.millesime, OLD.eleveId), NOW(), CONCAT_WS('|', OLD.selection, OLD.dateInscription, OLD.dateModification, OLD.etablissementId, OLD.classeId, OLD.chez, OLD.adresseL1, OLD.adresseL2, OLD.codePostal, OLD.communeId, OLD.x, OLD.y, OLD.distanceR1, OLD.distanceR2, OLD.dateEtiquette, OLD.dateCarte, OLD.inscrit, OLD.gratuit, OLD.paiement, OLD.anneeComplete, OLD.subventionR1, OLD.subventionR2, OLD.demandeR1, OLD.demandeR2, OLD.accordR1, OLD.accordR2, OLD.internet, OLD.district, OLD.derogation, OLD.dateDebut, OLD.dateFin, OLD.joursTransport, OLD.subventionTaux, OLD.tarifId, OLD.regimeId, OLD.motifDerogation, OLD.motifRefusR1, OLD.motifRefusR2, OLD.commentaire))
EOT

        )
    ),
    'data' => __DIR__ . '/data/data.scolarites.php'    
); 