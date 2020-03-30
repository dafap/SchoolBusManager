<?php
/**
 * Structure de la table des `eleves`
 *
 * Découpage en `eleves`, `scolarites`, `affectations` et `responsables`
 * ATTENTION !
 * La valeur par défaut des champs joursTransportR1 et joursTransportR2 doit être une
 * valeur valide dans la strategie Semaine. Aussi on choisit 31 (LMMJV) ou 27 (LM-JV)
 * et surtout pas 127 qui ne conviendrait que si les jours étaient tous autorisés.
 *
 * @project sbm
 * @package SbmInstallation/db_design
 * @filesource table.scolarites.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 30 mars 2020
 * @version 2020-2.6.0
 */
use SbmBase\Model\StdLib;

return [
    'name' => 'scolarites',
    'type' => 'table',
    'drop' => false,
    'edit_entity' => false,
    'add_data' => false,
    'structure' => [
        'fields' => [
            'millesime' => 'int(4) NOT NULL',
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
            'dateEtiquetteR1' => 'datetime NOT NULL DEFAULT "1900-01-01 00:00:00"',
            'dateEtiquetteR2' => 'datetime NOT NULL DEFAULT "1900-01-01 00:00:00"',
            'dateCarteR1' => 'datetime NOT NULL DEFAULT "1900-01-01 00:00:00"',
            'dateCarteR2' => 'datetime NOT NULL DEFAULT "1900-01-01 00:00:00"',
            'inscrit' => 'tinyint(1) NOT NULL DEFAULT "1"',
            'gratuit' => 'tinyint(1) NOT NULL DEFAULT "0"',
            'paiementR1' => 'tinyint(1) NOT NULL DEFAULT "0"',
            'paiementR2' => 'tinyint(1) NOT NULL DEFAULT "0"',
            'duplicataR1' => 'int(11) NOT NULL DEFAULT "0"',
            'duplicataR2' => 'int(11) NOT NULL DEFAULT "0"',
            'fa' => 'tinyint(1) NOT NULL DEFAULT "0"', // famille d'accueil
            'anneeComplete' => 'tinyint(1) NOT NULL DEFAULT "1"',
            'subventionR1' => 'tinyint(1) NOT NULL DEFAULT "0"',
            'subventionR2' => 'tinyint(1) NOT NULL DEFAULT "0"',
            'demandeR1' => 'tinyint(1) NOT NULL DEFAULT "1"',
            'demandeR2' => 'tinyint(1) NOT NULL DEFAULT "0"',
            'dateDemandeR2' => 'datetime NOT NULL DEFAULT "1900-01-01 00:00:00"',
            'stationIdR1' => 'int(11) NOT NULL DEFAULT "0"',
            'stationIdR2' => 'int(11) NOT NULL DEFAULT "0"',
            'accordR1' => 'tinyint(1) NOT NULL DEFAULT "1"',
            'accordR2' => 'tinyint(1) NOT NULL DEFAULT "1"',
            'internet' => 'tinyint(1) NOT NULL DEFAULT "1"',
            'district' => 'tinyint(1) NOT NULL DEFAULT "0"',
            'derogation' => 'tinyint(1) NOT NULL DEFAULT "0"',
            'dateDebut' => 'date NOT NULL',
            'dateFin' => 'date NOT NULL',
            'joursTransportR1' => 'tinyint(3) unsigned NOT NULL DEFAULT "31"',
            'joursTransportR2' => 'tinyint(3) unsigned NOT NULL DEFAULT "31"',
            'subventionTaux' => 'int(3) NOT NULL DEFAULT "0"',
            'grilleTarifR1' => 'int(3) NOT NULL DEFAULT "1"',
            'reductionR1' => 'int(1) NOT NULL DEFAULT "0"',
            'grilleTarifR2' => 'int(3) NOT NULL DEFAULT "4"',
            'reductionR2' => 'int(1) NOT NULL DEFAULT "0"',
            'tarifId' => 'int(11) NOT NULL DEFAULT "0"',
            'organismeId' => 'int(11) NOT NULL DEFAULT "0"',
            'regimeId' => 'tinyint(1) NOT NULL DEFAULT "0"',
            'motifDerogation' => 'text NULL',
            'motifRefusR1' => 'text NULL',
            'motifRefusR2' => 'text NULL',
            'commentaire' => 'text NULL'
        ],
        'primary_key' => [
            'millesime',
            'eleveId'
        ],
        'keys' => [
            'SCOLARITE_grilleTarif' => [
                'unique' => false,
                'fields' => [
                    'grilleTarifR1'
                ]
            ]
        ],
        'foreign key' => [
            [
                'key' => 'eleveId',
                'references' => [
                    'table' => 'eleves',
                    'fields' => [
                        'eleveId'
                    ],
                    'on' => [
                        'update' => 'CASCADE',
                        'delete' => 'RESTRICT'
                    ]
                ]
            ],
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
                'key' => 'classeId',
                'references' => [
                    'table' => 'classes',
                    'fields' => [
                        'classeId'
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
            ]
        ],
        'engine' => 'InnoDB',
        'charset' => 'utf8mb4',
        'collate' => 'utf8mb4_unicode_ci'
    ],
    'triggers' => [
        'scolarites_bi_history' => [
            'moment' => 'BEFORE',
            'evenement' => 'INSERT',
            'definition' => <<<EOT
            INSERT INTO %system(history)% (table_name, action, id_name, id_txt, dt, log)
            VALUES ('%table(scolarites)%', 'insert', CONCAT_WS('|', 'millesime', 'eleveId'), CONCAT_WS('|', NEW.millesime, NEW.eleveId), NOW(), CONCAT_WS('|', NEW.selection, NEW.dateInscription, NEW.dateModification, NEW.etablissementId, NEW.classeId, NEW.chez, NEW.adresseL1, NEW.adresseL2, NEW.codePostal, NEW.communeId, NEW.x, NEW.y, NEW.distanceR1, NEW.distanceR2, NEW.dateEtiquetteR1, NEW.dateEtiquetteR2, NEW.dateCarteR1, NEW.dateCarteR2, NEW.inscrit, NEW.gratuit, NEW.paiementR1, NEW.paiementR2, NEW.duplicataR1, NEW.duplicataR2, NEW.fa, NEW.anneeComplete, NEW.subventionR1, NEW.subventionR2, NEW.demandeR1, NEW.demandeR2, NEW.dateDemandeR2, NEW.stationIdR1, NEW.stationIdR2, NEW.accordR1, NEW.accordR2, NEW.internet, NEW.district, NEW.derogation, NEW.dateDebut, NEW.dateFin, NEW.joursTransportR1, NEW.joursTransportR2, NEW.subventionTaux, NEW.grilleTarifR1, NEW.reductionR1, NEW.grilleTarifR2, NEW.reductionR2, NEW.tarifId, NEW.organismeId, NEW.regimeId, NEW.motifDerogation, NEW.motifRefusR1, NEW.motifRefusR2, NEW.commentaire))
EOT
        ],
        'scolarites_bu_history' => [
            'moment' => 'BEFORE',
            'evenement' => 'UPDATE',
            'definition' => <<<EOT
            INSERT INTO %system(history)% (table_name, action, id_name, id_txt, dt, log)
            VALUES ('%table(scolarites)%', 'update', CONCAT_WS('|', 'millesime', 'eleveId'), CONCAT_WS('|', OLD.millesime, OLD.eleveId), NOW(), CONCAT_WS('|', OLD.selection, OLD.dateInscription, OLD.dateModification, OLD.etablissementId, OLD.classeId, OLD.chez, OLD.adresseL1, OLD.adresseL2, OLD.codePostal, OLD.communeId, OLD.x, OLD.y, OLD.distanceR1, OLD.distanceR2, OLD.dateEtiquetteR1, OLD.dateEtiquetteR2, OLD.dateCarteR1, OLD.dateCarteR2, OLD.inscrit, OLD.gratuit, OLD.paiementR1, OLD.paiementR2, OLD.duplicataR1, OLD.duplicataR2, OLD.fa, OLD.anneeComplete, OLD.subventionR1, OLD.subventionR2, OLD.demandeR1, OLD.demandeR2, OLD.dateDemandeR2, OLD.stationIdR1, OLD.stationIdR2, OLD.accordR1, OLD.accordR2, OLD.internet, OLD.district, OLD.derogation, OLD.dateDebut, OLD.dateFin, OLD.joursTransportR1, OLD.joursTransportR2, OLD.subventionTaux, OLD.grilleTarifR1, OLD.reductionR1, OLD.grilleTarifR2, OLD.reductionR2, OLD.tarifId, OLD.organismeId, OLD.regimeId, OLD.motifDerogation, OLD.motifRefusR1, OLD.motifRefusR2, OLD.commentaire))
EOT
        ],
        'scolarites_bd_history' => [
            'moment' => 'BEFORE',
            'evenement' => 'DELETE',
            'definition' => <<<EOT
            INSERT INTO %system(history)% (table_name, action, id_name, id_txt, dt, log)
            VALUES ('%table(scolarites)%', 'delete', CONCAT_WS('|', 'millesime', 'eleveId'), CONCAT_WS('|', OLD.millesime, OLD.eleveId), NOW(), CONCAT_WS('|', OLD.selection, OLD.dateInscription, OLD.dateModification, OLD.etablissementId, OLD.classeId, OLD.chez, OLD.adresseL1, OLD.adresseL2, OLD.codePostal, OLD.communeId, OLD.x, OLD.y, OLD.distanceR1, OLD.distanceR2, OLD.dateEtiquetteR1, OLD.dateEtiquetteR2, OLD.dateCarteR1, OLD.dateCarteR2, OLD.inscrit, OLD.gratuit, OLD.paiementR1, OLD.paiementR2, OLD.duplicataR1, OLD.duplicataR2, OLD.fa, OLD.anneeComplete, OLD.subventionR1, OLD.subventionR2, OLD.demandeR1, OLD.demandeR2, OLD.dateDemandeR2, OLD.stationIdR1, OLD.stationIdR2, OLD.accordR1, OLD.accordR2, OLD.internet, OLD.district, OLD.derogation, OLD.dateDebut, OLD.dateFin, OLD.joursTransportR1, OLD.joursTransportR2, OLD.subventionTaux, OLD.grilleTarifR1, OLD.reductionR1, OLD.grilleTarifR2, OLD.reductionR2, OLD.tarifId, OLD.organismeId, OLD.regimeId, OLD.motifDerogation, OLD.motifRefusR1, OLD.motifRefusR2, OLD.commentaire))
EOT
        ]
    ],
    'data' => StdLib::concatPath(StdLib::findParentPath(__DIR__, 'data/data'),
        'data.scolarites.php')
];