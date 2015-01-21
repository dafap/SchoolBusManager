<?php
/**
 * Structure de la table des `responsables`
 *
 * Découpage en `eleves`, `scolarites` et `responsables`
 * 
 * @project sbm
 * @package SbmInstallation/db_design
 * @filesource table.responsables.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 10 oct. 2014
 * @version 2014-1
 */
return array(
    'name' => 'responsables',
    'type' => 'table',
    'drop' => false,
    'edit_entity' => false,
    'add_data' => false,
    'structure' => array(
        'fields' => array(
            'responsableId' => 'int(11) NOT NULL AUTO_INCREMENT',
            'selection' => 'tinyint(1) UNSIGNED NOT NULL DEFAULT "0"',
            'dateCreation' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'dateModification' => 'datetime NOT NULL DEFAULT "1900-01-01 00:00:00"',
            'nature' => 'tinyint(1) NOT NULL DEFAULT "0"',
            'titre' => 'varchar(20) NOT NULL DEFAULT "M."',
            'nom' => 'varchar(30) NOT NULL',
            'nomSA' => 'varchar(30) NOT NULL',
            'prenom' => 'varchar(30) NOT NULL DEFAULT ""',
            'prenomSA' => 'varchar(30) NOT NULL DEFAULT ""',
            'adresseL1' => 'varchar(38) NOT NULL',
            'adresseL2' => 'varchar(38) NOT NULL DEFAULT ""',
            'codePostal' => 'varchar(5) NOT NULL',
            'communeId' => 'varchar(6) NOT NULL',
            'ancienAdresseL1' => 'varchar(30) NOT NULL DEFAULT ""',
            'ancienAdresseL2' => 'varchar(30) NOT NULL DEFAULT ""',
            'ancienCodePostal' => 'varchar(5) NOT NULL DEFAULT ""',
            'ancienCommuneId' => 'varchar(6) NOT NULL DEFAULT ""',
            'email' => 'varchar(80) NOT NULL DEFAULT ""',
            'telephoneF' => 'varchar(10) NOT NULL DEFAULT ""',
            'telephoneP' => 'varchar(10) NOT NULL DEFAULT ""',
            'telephoneT' => 'varchar(10) NOT NULL DEFAULT ""',
            'etiquette' => 'tinyint(1) UNSIGNED NOT NULL DEFAULT "0"',
            'demenagement' => 'tinyint(1) UNSIGNED NOT NULL DEFAULT "0"',
            'dateDemenagement' => 'date NOT NULL DEFAULT "1900-01-01"',
            'facture' => 'tinyint(1) UNSIGNED NOT NULL DEFAULT "1"',
            'grilleTarif' => 'int(4) NOT NULL DEFAULT "1"',
            'ribTit' => 'varchar(32) NOT NULL DEFAULT ""',
            'ribDom' => 'varchar(24) NOT NULL DEFAULT ""',
            'iban' => 'varchar(34) NOT NULL DEFAULT ""',
            'bic' => 'varchar(11) NOT NULL DEFAULT ""'
        ),
        'primary_key' => array(
            'responsableId'
        ),
        'foreign key' => array(
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
            )
        ),
        'engine' => 'InnoDB',
        'charset' => 'utf8',
        'collate' => 'utf8_unicode_ci'
    ),
    'triggers' => array(
        'responsables_bi_history' => array(
            'moment' => 'BEFORE',
            'evenement' => 'INSERT',
            'definition' => <<<EOT
INSERT INTO %system(history)% (table_name, action, id_name, id_int, dt, log)
VALUES ('%table(responsables)%', 'insert', 'responsableId', NEW.responsableId, NOW(), CONCAT_WS('|', NEW.selection, NEW.dateCreation, NEW.dateModification, NEW.nature, NEW.titre, NEW.nom, NEW.nomSA, NEW.prenom, NEW.prenomSA, NEW.adresseL1, NEW.adresseL2, NEW.codePostal, NEW.communeId, NEW.ancienAdresseL1, NEW.ancienAdresseL2, NEW.ancienCodePostal, NEW.ancienCommuneId, NEW.email, NEW.telephoneF, NEW.telephoneP, NEW.telephoneT, NEW.etiquette, NEW.demenagement, NEW.dateDemenagement, NEW.facture, NEW.grilleTarif, NEW.ribTit, NEW.ribDom, NEW.iban, NEW.bic))
EOT

        ),
        'responsables_bu_history' => array(
            'moment' => 'BEFORE',
            'evenement' => 'UPDATE',
            'definition' => <<<EOT
INSERT INTO %system(history)% (table_name, action, id_name, id_int, dt, log)
VALUES ('%table(responsables)%', 'update', 'responsableId', OLD.responsableId, NOW(), CONCAT_WS('|', OLD.selection, OLD.dateCreation, OLD.dateModification, OLD.nature, OLD.titre, OLD.nom, OLD.nomSA, OLD.prenom, OLD.prenomSA, OLD.adresseL1, OLD.adresseL2, OLD.codePostal, OLD.communeId, OLD.ancienAdresseL1, OLD.ancienAdresseL2, OLD.ancienCodePostal, OLD.ancienCommuneId, OLD.email, OLD.telephoneF, OLD.telephoneP, OLD.telephoneT, OLD.etiquette, OLD.demenagement, OLD.dateDemenagement, OLD.facture, OLD.grilleTarif, OLD.ribTit, OLD.ribDom, OLD.iban, OLD.bic))
EOT

        ),
        'responsables_bd_history' => array(
            'moment' => 'BEFORE',
            'evenement' => 'DELETE',
            'definition' => <<<EOT
INSERT INTO %system(history)% (table_name, action, id_name, id_int, dt, log)
VALUES ('%table(responsables)%', 'delete', 'responsableId', OLD.responsableId, NOW(), CONCAT_WS('|', OLD.selection, OLD.dateCreation, OLD.dateModification, OLD.nature, OLD.titre, OLD.nom, OLD.nomSA, OLD.prenom, OLD.prenomSA, OLD.adresseL1, OLD.adresseL2, OLD.codePostal, OLD.communeId, OLD.ancienAdresseL1, OLD.ancienAdresseL2, OLD.ancienCodePostal, OLD.ancienCommuneId, OLD.email, OLD.telephoneF, OLD.telephoneP, OLD.telephoneT, OLD.etiquette, OLD.demenagement, OLD.dateDemenagement, OLD.facture, OLD.grilleTarif, OLD.ribTit, OLD.ribDom, OLD.iban, OLD.bic))
EOT

        )
    ),
    
    // 'data' => include __DIR__ . '/data/data.responsables.php'
    // 'data' => array('after' => array('communes'),'include' => __DIR__ . '/data/data.responsables.php')
    'data' => __DIR__ . '/data/data.responsables.php'
); 