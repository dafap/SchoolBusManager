<?php
/**
 * Structure de la table des `users`
 *
 * Description longue du fichier s'il y en a une
 * 
 * @project sbm
 * @package module/SbmInstallation/config/db_design
 * @filesource users.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 23 oct. 2014
 * @version 2014-1
 */
return array(
    'name' => 'users',
    'type' => 'table',
    'drop' => false,
    'edit_entity' => false,
    'add_data' => false,
    'structure' => array(
        'fields' => array(
            'userId' => 'int(11) NOT NULL AUTO_INCREMENT',
            'confirme' => 'tinyint(1) UNSIGNED NOT NULL DEFAULT "0"',
            'selection' => 'tinyint(1) UNSIGNED NOT NULL DEFAULT "0"',
            'dateCreation' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'dateModification' => 'datetime NOT NULL DEFAULT "1900-01-01 00:00:00"', // non modifié lors de la mise à jour de dateLogin et adresseIp
            'dateLogin' => 'datetime NOT NULL DEFAULT "1900-01-01 00:00:00"',
            'adresseIp' => 'varchar(16) NOT NULL DEFAULT ""',
            'categorie' => 'tinyint(1) NOT NULL DEFAULT "1"', // 1: parent, 2: gestionnaire, 3: administrateur, 4: superviseur sadmin
            'titre' => 'varchar(20) NOT NULL DEFAULT "M."',
            'nom' => 'varchar(30) NOT NULL',
            'nomSA' => 'varchar(30) NOT NULL',
            'prenom' => 'varchar(30) NOT NULL DEFAULT ""',
            'prenomSA' => 'varchar(30) NOT NULL DEFAULT ""',
            'adresseL1' => 'varchar(38) NOT NULL',
            'adresseL2' => 'varchar(38) NOT NULL DEFAULT ""',
            'codePostal' => 'varchar(5) NOT NULL',
            'communeId' => 'varchar(6) NOT NULL',
            'telephoneF' => 'varchar(10) NOT NULL DEFAULT ""',
            'telephoneP' => 'varchar(10) NOT NULL DEFAULT ""',
            'telephoneT' => 'varchar(10) NOT NULL DEFAULT ""',
            'email' => 'varchar(80) NOT NULL',
            'mdp' => 'varchar(64) NOT NULL', // codage SHA1 du mot de passe
            'temoin' => 'text'
        ),
        'primary_key' => array(
            'userId'
        ),
        'keys' => array(
            'USER_Email' => array(
                'unique' => true,
                'fields' => array(
                    'email'
                )
            )
        ),
        'engine' => 'InnoDB',
        'charset' => 'utf8',
        'collate' => 'utf8_unicode_ci'
    ),
    'triggers' => array(
        'users_bu_history' => array(
            'moment' => 'BEFORE',
            'evenement' => 'UPDATE',
            'definition' => <<<EOT
INSERT INTO %system(history)% (table_name, action, id_name, id_int, dt, log)
VALUES ('%table(users)%', 'update', 'userId', OLD.userId, NOW(), CONCAT(OLD.confirme, '|', OLD.selection, '|', OLD.dateCreation, '|', OLD.dateModification, '|', OLD.dateLogin, '|', OLD.adresseIp, '|', OLD.categorie, '|', OLD.titre, '|', OLD.nom, '|', OLD.nomSA, '|', OLD.prenom, '|', OLD.prenomSA, '|', OLD.adresseL1, '|', OLD.adresseL2, '|', OLD.codePostal, '|', OLD.communeId, '|', OLD.telephoneF, '|', OLD.telephoneP, '|', OLD.telephoneT, '|', OLD.email, '|', OLD.mdp, '|', OLD.temoin))
EOT
        ),
        'users_bd_history' => array(
            'moment' => 'BEFORE',
            'evenement' => 'DELETE',
            'definition' => <<<EOT
INSERT INTO %system(history)% (table_name, action, id_name, id_int, dt, log)
VALUES ('%table(users)%', 'delete', 'userId', OLD.userId, NOW(), CONCAT(OLD.confirme, '|', OLD.selection, '|', OLD.dateCreation, '|', OLD.dateModification, '|', OLD.dateLogin, '|', OLD.adresseIp, '|', OLD.categorie, '|', OLD.titre, '|', OLD.nom, '|', OLD.nomSA, '|', OLD.prenom, '|', OLD.prenomSA, '|', OLD.adresseL1, '|', OLD.adresseL2, '|', OLD.codePostal, '|', OLD.communeId, '|', OLD.telephoneF, '|', OLD.telephoneP, '|', OLD.telephoneT, '|', OLD.email, '|', OLD.mdp, '|', OLD.temoin))
EOT
        )
    ),
    'data' => include __DIR__ . '/data/data.users.php'
);