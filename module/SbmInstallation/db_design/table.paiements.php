<?php
/**
 * Structure de la table des `paiements`
 *
 *
 * @project sbm
 * @package SbmInstallation/db_design
 * @filesource table.paiements.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 17 mai 2019
 * @version 2019-2.5.0
 */

/**
 * *************************************************************************
 * structure de la table 'paiements' *
 * Table MyISAM encodée utf8 *
 * Description des champs *
 * - paiementId est un auto-incrément *
 * - dateDepot et datePaiement sont des datetime *
 * - dateValeur est une date utilisé pour savoir quand on doit déposer les valeurs (chèques ou
 * espèces) *
 * - responsableId référence le responsable qui paie *
 * - anneeScolaire est l'année scolaire en cours (varchar) *
 * - exercice est l'exercice budgétaire (int)
 * - montant est un décimal a 2 décimales
 * - codeModePaiement et codeCaisse sont des références dans la table système des libelles
 * - banque, titulaire et reference sont des varchar(30) destinés à recevoir les informations
 * nécessaires pour les paiements par chèque
 * *************************************************************************
 */
use SbmBase\Model\StdLib;

return [
    'name' => 'paiements',
    'drop' => false,
    'edit_entity' => false,
    'add_data' => false,
    'type' => 'table',
    'structure' => [
        'fields' => [
            'paiementId' => 'int(11) NOT NULL AUTO_INCREMENT',
            'selection' => 'tinyint(1) NOT NULL DEFAULT "0"',
            'dateBordereau' => 'datetime DEFAULT NULL',
            'dateDepot' => 'datetime DEFAULT NULL',
            'datePaiement' => 'datetime NOT NULL',
            'dateValeur' => 'date DEFAULT NULL',
            'responsableId' => 'int(11) NOT NULL',
            'anneeScolaire' => 'varchar(9) NOT NULL',
            'exercice' => 'smallint(4) NOT NULL',
            'montant' => 'decimal(11,2) NOT NULL',
            'codeModeDePaiement' => 'int(11) NOT NULL',
            'codeCaisse' => 'int(11) NOT NULL',
            'banque' => 'varchar(30) NOT NULL DEFAULT ""',
            'titulaire' => 'varchar(30) NOT NULL DEFAULT ""',
            'reference' => 'varchar(30) NOT NULL DEFAULT ""',
            'justificatif' => 'varchar(30) NOT NULL DEFAULT ""',
            'note' => 'text NULL'
        ],
        'primary_key' => [
            'paiementId'
        ],
        'keys' => [
            'PAIEMENTS_date_reference' => [
                'unique' => true,
                'fields' => [
                    'datePaiement',
                    'reference'
                ]
            ]
        ],
        'engine' => 'InnoDb',
        'charset' => 'utf8',
        'collate' => 'utf8_unicode_ci'
    ],
    'triggers' => [
        'paiements_bi_history' => [
            'moment' => 'BEFORE',
            'evenement' => 'INSERT',
            'definition' => <<<EOT
INSERT INTO %system(history)% (table_name, action, id_name, id_int, dt, log)
VALUES ('%table(paiements)%', 'insert', 'paiementId', NEW.paiementId, NOW(), CONCAT(IFNULL(NEW.dateDepot, ''), '|', NEW.datePaiement, '|', IFNULL(NEW.dateValeur, ''), '|', NEW.responsableId, '|', NEW.anneeScolaire, '|', NEW.exercice, '|', NEW.montant, '|', NEW.codeModeDePaiement, '|', NEW.codeCaisse, '|', NEW.banque, '|', NEW.titulaire, '|', NEW.reference))
EOT

        ],
        'paiements_bu_history' => [
            'moment' => 'BEFORE',
            'evenement' => 'UPDATE',
            'definition' => <<<EOT
INSERT INTO %system(history)% (table_name, action, id_name, id_int, dt, log)
VALUES ('%table(paiements)%', 'update', 'paiementId', OLD.paiementId, NOW(), CONCAT(IFNULL(OLD.dateDepot, ''), '|', OLD.datePaiement, '|', IFNULL(OLD.dateValeur, ''), '|', OLD.responsableId, '|', OLD.anneeScolaire, '|', OLD.exercice, '|', OLD.montant, '|', OLD.codeModeDePaiement, '|', OLD.codeCaisse, '|', OLD.banque, '|', OLD.titulaire, '|', OLD.reference, '|', IFNULL(NEW.note, '')))
EOT

        ],
        'paiements_bd_history' => [
            'moment' => 'BEFORE',
            'evenement' => 'DELETE',
            'definition' => <<<EOT
INSERT INTO %system(history)% (table_name, action, id_name, id_int, dt, log)
VALUES ('%table(paiements)%', 'delete', 'paiementId', OLD.paiementId, NOW(), CONCAT(IFNULL(OLD.dateDepot, ''), '|', OLD.datePaiement, '|', IFNULL(OLD.dateValeur, ''), '|', OLD.responsableId, '|', OLD.anneeScolaire, '|', OLD.exercice, '|', OLD.montant, '|', OLD.codeModeDePaiement, '|', OLD.codeCaisse, '|', OLD.banque, '|', OLD.titulaire, '|', OLD.reference, '|', IFNULL(OLD.note, '')))
EOT

        ]
    ],
    'data' => StdLib::concatPath(StdLib::findParentPath(__DIR__, 'data/data'),
        'data.paiements.php')
];
