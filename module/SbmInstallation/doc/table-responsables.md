#Table responsables

##Structure

    +------------------+---------------------+------+-----+---------------------+----------------+
    | Field            | Type                | Null | Key | Default             | Extra          |
    +------------------+---------------------+------+-----+---------------------+----------------+
    | responsableId    | int(11)             | NO   | PRI | NULL                | auto_increment |
    | selection        | tinyint(1) unsigned | NO   |     | 0                   |                |
    | dateCreation     | timestamp           | NO   |     | CURRENT_TIMESTAMP   |                |
    | dateModification | datetime            | NO   |     | 1900-01-01 00:00:00 |                |
    | nature           | tinyint(1)          | NO   |     | 0                   |                |
    | titre            | varchar(20)         | NO   |     | M.                  |                |
    | nom              | varchar(30)         | NO   |     | NULL                |                |
    | nomSA            | varchar(30)         | NO   |     | NULL                |                |
    | prenom           | varchar(30)         | NO   |     |                     |                |
    | prenomSA         | varchar(30)         | NO   |     |                     |                |
    | titre2           | varchar(20)         | NO   |     |                     |                |
    | nom2             | varchar(30)         | NO   |     |                     |                |
    | nom2SA           | varchar(30)         | NO   |     |                     |                |
    | prenom2          | varchar(30)         | NO   |     |                     |                |
    | prenom2SA        | varchar(30)         | NO   |     |                     |                |
    | adresseL1        | varchar(38)         | NO   |     | NULL                |                |
    | adresseL2        | varchar(38)         | NO   |     |                     |                |
    | codePostal       | varchar(5)          | NO   |     | NULL                |                |
    | communeId        | varchar(6)          | NO   | MUL | NULL                |                |
    | ancienAdresseL1  | varchar(30)         | NO   |     |                     |                |
    | ancienAdresseL2  | varchar(30)         | NO   |     |                     |                |
    | ancienCodePostal | varchar(5)          | NO   |     |                     |                |
    | ancienCommuneId  | varchar(6)          | NO   |     |                     |                |
    | email            | varchar(80)         | YES  | UNI | NULL                |                |
    | telephoneF       | varchar(10)         | NO   |     |                     |                |
    | telephoneP       | varchar(10)         | NO   |     |                     |                |
    | telephoneT       | varchar(10)         | NO   |     |                     |                |
    | etiquette        | tinyint(1) unsigned | NO   |     | 0                   |                |
    | demenagement     | tinyint(1) unsigned | NO   |     | 0                   |                |
    | dateDemenagement | date                | NO   |     | 1900-01-01          |                |
    | facture          | tinyint(1) unsigned | NO   |     | 1                   |                |
    | grilleTarif      | int(4)              | NO   |     | 1                   |                |
    | ribTit           | varchar(32)         | NO   |     |                     |                |
    | ribDom           | varchar(24)         | NO   |     |                     |                |
    | iban             | varchar(34)         | NO   |     |                     |                |
    | bic              | varchar(11)         | NO   |     |                     |                |
    | x                | decimal(18,10)      | NO   |     | 0.0000000000        |                |
    | y                | decimal(18,10)      | NO   |     | 0.0000000000        |                |
    | userId           | int(11)             | NO   |     | 3                   |                |
    | id_ccda          | int(11)             | YES  |     | NULL                |                |
    | note             | text                | YES  |     | NULL                |                |
    +------------------+---------------------+------+-----+---------------------+----------------+

##Indexes

    +------------+-------------------+--------------+---------------+-----------+-------------+------+------------+
    | Non_unique | Key_name          | Seq_in_index | Column_name   | Collation | Cardinality | Null | Index_type |
    +------------+-------------------+--------------+---------------+-----------+-------------+------+------------+
    |          0 | PRIMARY           |            1 | responsableId | A         |         814 |      | BTREE      |
    |          0 | RESPONSABLE_email |            1 | email         | A         |         640 | YES  | BTREE      |
    |          1 | communeId         |            1 | communeId     | A         |          26 |      | BTREE      |
    +------------+-------------------+--------------+---------------+-----------+-------------+------+------------+

##Foreign keys

    +----------------------------+-----------------+----------------------|-----------------+---------+---------+
    | Key_constraint             | ForeignKey name | Référence table      | Référence field | Delete  | Update  |
    +----------------------------+-----------------+----------------------|-----------------+---------+---------+
    | sbm_t_responsables_ibfk_1  | communeId       | sbm_t_communes       | communeId       |         | Cascade |
    +----------------------------+-----------------+----------------------|-----------------+---------+---------+

##Triggers

    +-------------------------+--------+------------------------------------------------------------------------------+
    | Trigger name            | Method | Code                                                                         |
    +-------------------------+--------+------------------------------------------------------------------------------+
    | responsables_bi_history | INSERT | BEGIN                                                                        |
    |                         |        | INSERT INTO sbm_s_history (table_name, action, id_name, id_int, dt, log)     |
    |                         |        | VALUES ('sbm_t_responsables', 'insert', 'responsableId', NEW.responsableId,  |
    |                         |        | NOW(), CONCAT_WS('|', NEW.selection, NEW.dateCreation, NEW.dateModification, |
    |                         |        | NEW.nature, NEW.titre, NEW.nom, NEW.nomSA, NEW.prenom, NEW.prenomSA,         |
    |                         |        | NEW.adresseL1, NEW.adresseL2, NEW.codePostal, NEW.communeId,                 |
    |                         |        | NEW.ancienAdresseL1, NEW.ancienAdresseL2, NEW.ancienCodePostal,              |
    |                         |        | NEW.ancienCommuneId, NEW.email, NEW.telephoneF, NEW.telephoneP,              |
    |                         |        | NEW.telephoneT, NEW.etiquette, NEW.demenagement, NEW.dateDemenagement,       |
    |                         |        | NEW.facture, NEW.grilleTarif, NEW.ribTit, NEW.ribDom, NEW.iban, NEW.bic,     |
    |                         |        | NEW.x, NEW.y, NEW.userId, NEW.note));                                        |
    |                         |        | END                                                                          |
    +-------------------------+--------+------------------------------------------------------------------------------+
    | responsables_bu_history | UPDATE | BEGIN                                                                        |
    |                         |        | INSERT INTO sbm_s_history (table_name, action, id_name, id_int, dt, log)     |
    |                         |        | VALUES ('sbm_t_responsables', 'update', 'responsableId', OLD.responsableId,  |
    |                         |        | NOW(), CONCAT_WS('|', OLD.selection, OLD.dateCreation, OLD.dateModification, |
    |                         |        | OLD.nature, OLD.titre, OLD.nom, OLD.nomSA, OLD.prenom, OLD.prenomSA,         |
    |                         |        | OLD.adresseL1, OLD.adresseL2, OLD.codePostal, OLD.communeId,                 |
    |                         |        | OLD.ancienAdresseL1, OLD.ancienAdresseL2, OLD.ancienCodePostal,              |
    |                         |        | OLD.ancienCommuneId, OLD.email, OLD.telephoneF, OLD.telephoneP,              |
    |                         |        | OLD.telephoneT, OLD.etiquette, OLD.demenagement, OLD.dateDemenagement,       |
    |                         |        | OLD.facture, OLD.grilleTarif, OLD.ribTit, OLD.ribDom, OLD.iban, OLD.bic,     |
    |                         |        | OLD.x, OLD.y, OLD.userId, OLD.note));                                        |
    |                         |        | END                                                                          |
    +-------------------------+--------+------------------------------------------------------------------------------+
    | responsables_bd_history | DELETE | BEGIN                                                                        |
    |                         |        | INSERT INTO sbm_s_history (table_name, action, id_name, id_int, dt, log)     |
    |                         |        | VALUES ('sbm_t_responsables', 'delete', 'responsableId', OLD.responsableId,  |
    |                         |        | NOW(), CONCAT_WS('|', OLD.selection, OLD.dateCreation, OLD.dateModification, |
    |                         |        | OLD.nature, OLD.titre, OLD.nom, OLD.nomSA, OLD.prenom, OLD.prenomSA,         |
    |                         |        | OLD.adresseL1, OLD.adresseL2, OLD.codePostal, OLD.communeId,                 |
    |                         |        | OLD.ancienAdresseL1, OLD.ancienAdresseL2, OLD.ancienCodePostal,              |
    |                         |        | OLD.ancienCommuneId, OLD.email, OLD.telephoneF, OLD.telephoneP,              |
    |                         |        | OLD.telephoneT, OLD.etiquette, OLD.demenagement, OLD.dateDemenagement,       |
    |                         |        | OLD.facture, OLD.grilleTarif, OLD.ribTit, OLD.ribDom, OLD.iban, OLD.bic,     |
    |                         |        | OLD.x, OLD.y, OLD.userId, OLD.note));                                        |
    |                         |        | END                                                                          |
    +-------------------------+--------+------------------------------------------------------------------------------+