#Table affectations

##Structure

    +--------------------+---------------+------+-----+---------+----------------+
    | Field              | Type          | Null | Key | Default | Extra          |
    +--------------------+---------------+------+-----+---------+----------------+
    | paiementId         | int(11)       | NO   | PRI | NULL    | auto_increment |
    | selection          | tinyint(4)    | NO   |     | 0       |                |
    | dateBordereau      | datetime      | YES  |     | NULL    |                |
    | dateDepot          | datetime      | YES  |     | NULL    |                |
    | datePaiement       | datetime      | NO   | MUL | NULL    |                |
    | dateValeur         | date          | YES  |     | NULL    |                |
    | responsableId      | int(11)       | NO   |     | NULL    |                |
    | anneeScolaire      | varchar(9)    | NO   |     | NULL    |                |
    | exercice           | smallint(4)   | NO   |     | NULL    |                |
    | montant            | decimal(11,2) | NO   |     | NULL    |                |
    | codeModeDePaiement | int(11)       | NO   |     | NULL    |                |
    | codeCaisse         | int(11)       | NO   |     | NULL    |                |
    | banque             | varchar(30)   | NO   |     |         |                |
    | titulaire          | varchar(30)   | NO   |     |         |                |
    | reference          | varchar(30)   | NO   |     |         |                |
    | note               | text          | YES  |     | NULL    |                |
    +--------------------+---------------+------+-----+---------+----------------+

##Indexes

    +------------+--------------------------+--------------+--------------+-----------+-------------+------+------------+
    | Non_unique | Key_name                 | Seq_in_index | Column_name  | Collation | Cardinality | Null | Index_type |
    +------------+--------------------------+--------------+--------------+-----------+-------------+------+------------+
    |          0 | PRIMARY                  |            1 | paiementId   | A         |        1191 |      | BTREE      |
    |          0 | PAIEMENTS_date_reference |            1 | datePaiement | A         |        1180 |      | BTREE      |
    |          0 | PAIEMENTS_date_reference |            2 | reference    | A         |        1191 |      | BTREE      |
    +------------+--------------------------+--------------+--------------+-----------+-------------+------+------------+

##Foreign keys

_Pas de foreign key._

##Triggers

    +----------------------+--------+------------------------------------------------------------------------------+
    | Trigger name         | Method | Code                                                                         |
    +----------------------+--------+------------------------------------------------------------------------------+
    | paiements_bi_history | INSERT | BEGIN                                                                        |
    |                      |        | INSERT INTO sbm_s_history (table_name, action, id_name, id_int, dt, log)     |
    |                      |        | VALUES ('sbm_t_paiements', 'insert', 'paiementId', NEW.paiementId, NOW(),    |
    |                      |        | CONCAT(IFNULL(NEW.dateDepot, ''), '|', NEW.datePaiement, '|',                |
    |                      |        | IFNULL(NEW.dateValeur, ''), '|', NEW.responsableId, '|', NEW.anneeScolaire,  |
    |                      |        | '|', NEW.exercice, '|', NEW.montant, '|', NEW.codeModeDePaiement, '|',       |
    |                      |        | NEW.codeCaisse, '|', NEW.banque, '|', NEW.titulaire, '|', NEW.reference));   |
    |                      |        | END                                                                          |
    +----------------------+--------+------------------------------------------------------------------------------+
    | paiements_bu_history | UPDATE | BEGIN                                                                        |
    |                      |        | INSERT INTO sbm_s_history (table_name, action, id_name, id_int, dt, log)     |
    |                      |        | VALUES ('sbm_t_paiements', 'update', 'paiementId', OLD.paiementId, NOW(),    |
    |                      |        | CONCAT(IFNULL(OLD.dateDepot, ''), '|', OLD.datePaiement, '|',                |
    |                      |        | IFNULL(OLD.dateValeur, ''), '|', OLD.responsableId, '|', OLD.anneeScolaire,  |
    |                      |        | '|', OLD.exercice, '|', OLD.montant, '|', OLD.codeModeDePaiement, '|',       |
    |                      |        | OLD.codeCaisse, '|', OLD.banque, '|', OLD.titulaire, '|', OLD.reference,     |
    |                      |        | '|', IFNULL(NEW.note, '')));                                                 |
    |                      |        | END                                                                          |
    +----------------------+--------+------------------------------------------------------------------------------+
    | paiements_bd_history | DELETE | BEGIN                                                                        |
    |                      |        | INSERT INTO sbm_s_history (table_name, action, id_name, id_int, dt, log)     |
    |                      |        | VALUES ('sbm_t_paiements', 'delete', 'paiementId', OLD.paiementId, NOW(),    |
    |                      |        | CONCAT(IFNULL(OLD.dateDepot, ''), '|', OLD.datePaiement, '|',                |
    |                      |        | IFNULL(OLD.dateValeur, ''), '|', OLD.responsableId, '|', OLD.anneeScolaire,  |
    |                      |        | '|', OLD.exercice, '|', OLD.montant, '|', OLD.codeModeDePaiement, '|',       |
    |                      |        | OLD.codeCaisse, '|', OLD.banque, '|', OLD.titulaire, '|', OLD.reference,     |
    |                      |        | '|', IFNULL(OLD.note, '')));                                                 |
    |                      |        | END                                                                          |
    +----------------------+--------+------------------------------------------------------------------------------+