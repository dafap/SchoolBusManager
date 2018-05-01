#Table eleves

##Structure

    +------------------+----------------+------+-----+---------------------+----------------+
    | Field            | Type           | Null | Key | Default             | Extra          |
    +------------------+----------------+------+-----+---------------------+----------------+
    | eleveId          | int(11)        | NO   | PRI | NULL                | auto_increment |
    | selection        | tinyint(1)     | NO   |     | 0                   |                |
    | mailchimp        | tinyint(1)     | NO   |     | 1                   |                |
    | dateCreation     | timestamp      | NO   |     | CURRENT_TIMESTAMP   |                |
    | dateModification | datetime       | NO   |     | 1900-01-01 00:00:00 |                |
    | nom              | varchar(30)    | NO   |     | NULL                |                |
    | nomSA            | varchar(30)    | NO   |     | NULL                |                |
    | prenom           | varchar(30)    | NO   |     | NULL                |                |
    | prenomSA         | varchar(30)    | NO   |     | NULL                |                |
    | dateN            | date           | NO   |     | NULL                |                |
    | numero           | int(11)        | NO   |     | NULL                |                |
    | responsable1Id   | int(11)        | NO   | MUL | 0                   |                |
    | x1               | decimal(18,10) | NO   |     | 0.0000000000        |                |
    | y1               | decimal(18,10) | NO   |     | 0.0000000000        |                |
    | geopt1           | geometry       | YES  |     | NULL                |                |
    | responsable2Id   | int(11)        | YES  |     | NULL                |                |
    | x2               | decimal(18,10) | YES  |     | NULL                |                |
    | y2               | decimal(18,10) | YES  |     | NULL                |                |
    | geopt2           | geometry       | YES  |     | NULL                |                |
    | responsableFId   | int(11)        | YES  |     | NULL                |                |
    | note             | text           | YES  |     | NULL                |                |
    | id_ccda          | int(11)        | YES  |     | NULL                |                |
    +------------------+----------------+------+-----+---------------------+----------------+

##Indexes

    +------------+----------------+--------------+----------------+-----------+-------------+------+------------+
    | Non_unique | Key_name       | Seq_in_index | Column_name    | Collation | Cardinality | Null | Index_type |
    +------------+----------------+--------------+----------------+-----------+-------------+------+------------+
    |          0 | PRIMARY        |            1 | eleveId        | A         |        1011 |      | BTREE      |
    |          1 | responsable1Id |            1 | responsable1Id | A         |         754 |      | BTREE      |
    +------------+----------------+--------------+----------------+-----------+-------------+------+------------+

##Foreign keys

    +--------------------------+-----------------+----------------------|-----------------+---------+---------+
    | Key_constraint           | ForeignKey name | Référence table      | Référence field | Delete  | Update  |
    +--------------------------+-----------------+----------------------|-----------------+---------+---------+
    | sbm_t_eleves_ibfk_1      | responsable1Id  | sbm_t_responsables   | responsableId   |         | Cascade |
    +--------------------------+-----------------+----------------------|-----------------+---------+---------+

##Triggers

    +-------------------+--------+------------------------------------------------------------------------------+
    | Trigger name      | Method | Code                                                                         |
    +-------------------+--------+------------------------------------------------------------------------------+
    | eleves_bi_history | INSERT | BEGIN                                                                        |
    |                   |        | INSERT INTO sbm_s_history (table_name, action, id_name, id_int, dt, log)     |
    |                   |        | VALUES ('sbm_t_eleves', 'insert', 'eleveId', NEW.eleveId, NOW(),             |
    |                   |        | CONCAT(NEW.selection, '|', NEW.dateCreation, '|', NEW.dateModification, '|', |
    |                   |        | NEW.nom, '|', NEW.nomSA, '|', NEW.prenom, '|', NEW.prenomSA, '|', NEW.dateN, |
    |                   |        | '|', NEW.numero, '|', NEW.responsable1Id, '|',                               |
    |                   |        | IFNULL(NEW.responsable2Id,''), '|', IFNULL(NEW.responsableFId,'')));         |
    |                   |        | END                                                                          |
    +-------------------+--------+------------------------------------------------------------------------------+
    | eleves_bu_history | UPDATE | BEGIN                                                                        |
    |                   |        | INSERT INTO sbm_s_history (table_name, action, id_name, id_int, dt, log)     |
    |                   |        | VALUES ('sbm_t_eleves', 'update', 'eleveId', OLD.eleveId, NOW(),             |
    |                   |        | CONCAT(OLD.selection, '|', OLD.dateCreation, '|', OLD.dateModification, '|', |
    |                   |        | OLD.nom, '|', OLD.nomSA, '|', OLD.prenom, '|', OLD.prenomSA, '|', OLD.dateN, |
    |                   |        | '|', OLD.numero, '|', OLD.responsable1Id, '|',                               |
    |                   |        | IFNULL(OLD.responsable2Id,''), '|', IFNULL(OLD.responsableFId,'')));         |
    |                   |        | END                                                                          |
    +-------------------+--------+------------------------------------------------------------------------------+
    | eleves_bd_history | DELETE | BEGIN                                                                        |
    |                   |        | INSERT INTO sbm_s_history (table_name, action, id_name, id_int, dt, log)     |
    |                   |        | VALUES ('sbm_t_eleves', 'delete', 'eleveId', OLD.eleveId, NOW(),             |
    |                   |        | CONCAT(OLD.selection, '|', OLD.dateCreation, '|', OLD.dateModification, '|', |
    |                   |        | OLD.nom, '|', OLD.nomSA, '|', OLD.prenom, '|', OLD.prenomSA, '|', OLD.dateN, |
    |                   |        | '|', OLD.numero, '|', OLD.responsable1Id, '|',                               |
    |                   |        | IFNULL(OLD.responsable2Id,''), '|', IFNULL(OLD.responsableFId,'')));         |
    |                   |        | END                                                                          |
    +-------------------+--------+------------------------------------------------------------------------------+
