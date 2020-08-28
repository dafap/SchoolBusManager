<?php
/**
 * Structure de la table des `users`
 *
 * @project sbm
 * @package SbmInstallation/db_design
 * @filesource users.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 28 août 2020
 * @version 2020-2.6.0
 */
use SbmBase\Model\StdLib;

return [
    'name' => 'users',
    'type' => 'table',
    'drop' => false,
    'edit_entity' => false,
    'add_data' => false,
    'structure' => [
        'fields' => [
            'userId' => 'int(11) NOT NULL AUTO_INCREMENT',
            'token' => 'varchar(32) DEFAULT NULL', // pour une entrée directe par un lien
                                                    // - usage
                                                    // unique
            'tokenalive' => 'tinyint(1) UNSIGNED NOT NULL DEFAULT "0"', // indique si le
                                                                         // token est
                                                                         // actif. Il ne
                                                                         // l'est pas
                                                                         // par défaut
            'confirme' => 'tinyint(1) UNSIGNED NOT NULL DEFAULT "0"', // indique si
                                                                       // l'email a été
                                                                       // confirmé. Il ne
                                                                       // l'est pas
                                                                       // par défaut
            'active' => 'tinyint(1) UNSIGNED NOT NULL DEFAULT "1"', // compte actif ou
                                                                     // désactivé
            'selection' => 'tinyint(1) UNSIGNED NOT NULL DEFAULT "0"',
            'dateCreation' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'dateModification' => 'datetime NOT NULL DEFAULT "1900-01-01 00:00:00"', // n'est
                                                                                      // modifié
                                                                                      // que
                                                                                      // pour
                                                                                      // titre,
                                                                                      // nom,
                                                                                      // prenom,
                                                                                      // email,
                                                                                      // mdp
            'dateLastLogin' => 'datetime NOT NULL DEFAULT "1900-01-01 00:00:00"',
            'datePreviousLogin' => 'datetime NOT NULL DEFAULT "1900-01-01 00:00:00"',
            'adresseIp' => 'varchar(16) NOT NULL DEFAULT ""',
            'previousIp' => 'varchar(16) NOT NULL DEFAULT ""',
            'categorieId' => 'tinyint(1) UNSIGNED NOT NULL DEFAULT "1"', // SbmAuthentification\Model\CategoriesInterface
            'titre' => 'varchar(20) NOT NULL DEFAULT "M."',
            'nom' => 'varchar(30) NOT NULL',
            'prenom' => 'varchar(30) NOT NULL DEFAULT ""',
            'email' => 'varchar(80) NOT NULL',
            'mdp' => 'varchar(60) NOT NULL DEFAULT ""', // mot de passe crypté par
                                                         // \SbmAuthentification\Model\Mdp::crypteMdp()
            'gds' => 'varchar(8) NOT NULL', // grain de sel - mot aléatoire de 8
                                             // caractères
                                             // enregistré lors de la création, puis
                                             // inchangé
            'note' => 'text NULL'
        ],
        'primary_key' => [
            'userId'
        ],
        'keys' => [
            'USER_Email' => [
                'unique' => true,
                'fields' => [
                    'email'
                ]
            ],
            'USER_Token' => [
                'unique' => true,
                'fields' => [
                    'token'
                ]
            ]
        ],
        'engine' => 'InnoDB',
        'charset' => 'utf8mb4',
        'collate' => 'utf8mb4_unicode_ci'
    ],
    'triggers' => [
        'users_bu_history' => [
            'moment' => 'BEFORE',
            'evenement' => 'UPDATE',
            'definition' => <<<EOT
            INSERT INTO %system(history)% (table_name, action, id_name, id_int, dt, log)
            VALUES ('%table(users)%', 'update', 'userId', OLD.userId, NOW(), CONCAT(IFNULL(OLD.token, ''), '|', OLD.tokenalive, '|', OLD.confirme, '|', OLD.active, '|', OLD.selection, '|', OLD.dateCreation, '|', OLD.dateModification, '|', OLD.dateLastLogin, '|', OLD.datePreviousLogin, '|', OLD.adresseIp, '|', OLD.previousIp, '|', OLD.categorieId, '|', OLD.titre, '|', OLD.nom, '|', OLD.prenom, '|', OLD.email, '|', OLD.mdp, '|', OLD.gds, '|', IFNULL(OLD.note, '')))
            EOT
        ],
        'users_bd_history' => [
            'moment' => 'BEFORE',
            'evenement' => 'DELETE',
            'definition' => <<<EOT
            INSERT INTO %system(history)% (table_name, action, id_name, id_int, dt, log)
            VALUES ('%table(users)%', 'delete', 'userId', OLD.userId, NOW(), CONCAT(IFNULL(OLD.token, ''), '|', OLD.tokenalive, '|', OLD.confirme, '|', OLD.active, '|', OLD.selection, '|', OLD.dateCreation, '|', OLD.dateModification, '|', OLD.dateLastLogin, '|', OLD.datePreviousLogin, '|', OLD.adresseIp, '|', OLD.previousIp, '|', OLD.categorieId, '|', OLD.titre, '|', OLD.nom, '|', OLD.prenom, '|', OLD.email, '|', OLD.mdp, '|', OLD.gds, '|', IFNULL(OLD.note, '')))
            EOT
        ]
    ],

    'data' => StdLib::concatPath(StdLib::findParentPath(__DIR__, 'data/data'),
        'data.users.php')
];