#Table users

##Structure

    +-------------------+---------------------+------+-----+---------------------+----------------+
    | Field             | Type                | Null | Key | Default             | Extra          |
    +-------------------+---------------------+------+-----+---------------------+----------------+
    | userId            | int(11)             | NO   | PRI | NULL                | auto_increment |
    | token             | varchar(32)         | YES  | UNI | NULL                |                |
    | tokenalive        | tinyint(1) unsigned | NO   |     | 0                   |                |
    | confirme          | tinyint(1) unsigned | NO   |     | 0                   |                |
    | active            | tinyint(1) unsigned | NO   |     | 1                   |                |
    | selection         | tinyint(1) unsigned | NO   |     | 0                   |                |
    | dateCreation      | timestamp           | NO   |     | CURRENT_TIMESTAMP   |                |
    | dateModification  | datetime            | NO   |     | 1900-01-01 00:00:00 |                |
    | dateLastLogin     | datetime            | NO   |     | 1900-01-01 00:00:00 |                |
    | datePreviousLogin | datetime            | NO   |     | 1900-01-01 00:00:00 |                |
    | adresseIp         | varchar(16)         | NO   |     |                     |                |
    | previousIp        | varchar(16)         | NO   |     |                     |                |
    | categorieId       | tinyint(1) unsigned | NO   |     | 1                   |                |
    | titre             | varchar(20)         | NO   |     | M.                  |                |
    | nom               | varchar(30)         | NO   |     | NULL                |                |
    | prenom            | varchar(30)         | NO   |     |                     |                |
    | email             | varchar(80)         | NO   | UNI | NULL                |                |
    | mdp               | varchar(60)         | NO   |     | NULL                |                |
    | gds               | varchar(8)          | NO   |     |                     |                |
    | note              | text                | YES  |     | NULL                |                |
    +-------------------+---------------------+------+-----+---------------------+----------------+

##Indexes

    +------------+------------+--------------+-------------+-----------+-------------+------+------------+
    | Non_unique | Key_name   | Seq_in_index | Column_name | Collation | Cardinality | Null | Index_type |
    +------------+------------+--------------+-------------+-----------+-------------+------+------------+
    |          0 | PRIMARY    |            1 | userId      | A         |         400 |      | BTREE      |
    |          0 | USER_Email |            1 | email       | A         |         400 |      | BTREE      |
    |          0 | USER_Token |            1 | token       | A         |         143 | YES  | BTREE      |
    +------------+------------+--------------+-------------+-----------+-------------+------+------------+

##Foreign keys

_Pas de foreign key._

##Triggers

    +------------------+--------+------------------------------------------------------------------------------+
    | Trigger name     | Method | Code                                                                         |
    +------------------+--------+------------------------------------------------------------------------------+
    | users_bu_history | UPDATE | BEGIN                                                                        |
    |                  |        | INSERT INTO sbm_s_history (table_name, action, id_name, id_int, dt, log)     |
    |                  |        | VALUES ('sbm_t_users', 'update', 'userId', OLD.userId, NOW(),                |
    |                  |        | CONCAT(IFNULL(OLD.token, ''), '|', OLD.tokenalive, '|', OLD.confirme, '|',   |
    |                  |        | OLD.active, '|', OLD.selection, '|', OLD.dateCreation, '|',                  |
    |                  |        | OLD.dateModification, '|', OLD.dateLastLogin, '|', OLD.datePreviousLogin,    |
    |                  |        | '|', OLD.adresseIp, '|', OLD.previousIp, '|', OLD.categorieId, '|',          |
    |                  |        | OLD.titre, '|', OLD.nom, '|', OLD.prenom, '|', OLD.email, '|', OLD.mdp,      |
    |                  |        | '|', OLD.gds, '|', IFNULL(OLD.note, '')));                                   |
    |                  |        | END                                                                          |
    +------------------+--------+------------------------------------------------------------------------------+
    | users_bd_history | DELETE | BEGIN                                                                        |
    |                  |        | INSERT INTO sbm_s_history (table_name, action, id_name, id_int, dt, log)     |
    |                  |        | VALUES ('sbm_t_users', 'delete', 'userId', OLD.userId, NOW(),                |
    |                  |        | CONCAT(IFNULL(OLD.token, ''), '|', OLD.tokenalive, '|', OLD.confirme, '|',   |
    |                  |        | OLD.active, '|', OLD.selection, '|', OLD.dateCreation, '|',                  |
    |                  |        | OLD.dateModification, '|', OLD.dateLastLogin, '|', OLD.datePreviousLogin,    |
    |                  |        | '|', OLD.adresseIp, '|', OLD.previousIp, '|', OLD.categorieId, '|',          |
    |                  |        | OLD.titre, '|', OLD.nom, '|', OLD.prenom, '|', OLD.email, '|', OLD.mdp,      |
    |                  |        | '|', OLD.gds, '|', IFNULL(OLD.note, '')));                                   |
    |                  |        | END                                                                          |
    +------------------+--------+------------------------------------------------------------------------------+