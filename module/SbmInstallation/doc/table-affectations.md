#Table affectations

##Structure

    +----------------+-------------+------+-----+---------+-------+
    | Field          | Type        | Null | Key | Default | Extra |
    +----------------+-------------+------+-----+---------+-------+
    | millesime      | int(4)      | NO   | PRI | 0       |       |
    | eleveId        | int(11)     | NO   | PRI | 0       |       |
    | trajet         | tinyint(1)  | NO   | PRI | 1       |       |
    | jours          | tinyint(2)  | NO   | PRI | 31      |       |
    | sens           | tinyint(1)  | NO   | PRI | 3       |       |
    | correspondance | tinyint(1)  | NO   | PRI | 1       |       |
    | selection      | tinyint(1)  | NO   |     | 0       |       |
    | responsableId  | int(11)     | NO   | MUL | NULL    |       |
    | station1Id     | int(11)     | NO   | MUL | NULL    |       |
    | service1Id     | varchar(11) | NO   | MUL | NULL    |       |
    | station2Id     | int(11)     | YES  |     | NULL    |       |
    | service2Id     | varchar(11) | YES  |     | NULL    |       |
    +----------------+-------------+------+-----+---------+-------+

##Indexes

    +------------+---------------+--------------+----------------+-----------+-------------+------+------------+
    | Non_unique | Key_name      | Seq_in_index | Column_name    | Collation | Cardinality | Null | Index_type |
    +------------+---------------+--------------+----------------+-----------+-------------+------+------------+
    |          0 | PRIMARY       |            1 | millesime      | A         |           4 |      | BTREE      |
    |          0 | PRIMARY       |            2 | eleveId        | A         |        1995 |      | BTREE      |
    |          0 | PRIMARY       |            3 | trajet         | A         |        2043 |      | BTREE      |
    |          0 | PRIMARY       |            4 | jours          | A         |        2043 |      | BTREE      |
    |          0 | PRIMARY       |            5 | sens           | A         |        2043 |      | BTREE      |
    |          0 | PRIMARY       |            6 | correspondance | A         |        2043 |      | BTREE      |
    |          1 | responsableId |            1 | responsableId  | A         |         598 |      | BTREE      |
    |          1 | station1Id    |            1 | station1Id     | A         |         150 |      | BTREE      |
    |          1 | service1Id    |            1 | service1Id     | A         |          30 |      | BTREE      |
    +------------+---------------+--------------+----------------+-----------+-------------+------+------------+

##Foreign keys

    +---------------------------+--------------------+----------------------|--------------------+---------+---------+
    | Key_constraint            | ForeignKey name    | Référence table      | Référence field    | Delete  | Update  |
    +---------------------------+--------------------+----------------------|--------------------+---------+---------+
    | sbm_t_affectations_ibfk_1 | millesime, eleveId | sbm_t_scolarites     | millesime, eleveId |         | Cascade |
    | sbm_t_affectations_ibfk_2 | responsableId      | sbm_t_responsables   | responsableId      |         | Cascade |
    | sbm_t_affectations_ibfk_3 | station1Id         | sbm_t_stations       | stationId          |         | Cascade |
    | sbm_t_affectations_ibfk_4 | service1Id         | sbm_t_services       | serviceId          |         | Cascade |
    +---------------------------+--------------------+----------------------|--------------------+---------+---------+

##Triggers

    +-------------------------+--------+------------------------------------------------------------------------------+
    | Trigger name            | Method | Code                                                                         |
    +-------------------------+--------+------------------------------------------------------------------------------+
    | affectations_bi_history | INSERT | BEGIN                                                                        |
    |                         |        | INSERT INTO sbm_s_history (table_name, action, id_name, id_txt, dt, log)     |
    |                         |        | VALUES ('sbm_t_affectations', 'insert', CONCAT_WS('|', 'millesime',          |
    |                         |        | 'eleveId', 'trajet', 'jours', 'sens', 'correspondance'), CONCAT_WS('|',      |
    |                         |        | NEW.millesime, NEW.eleveId, NEW.trajet, NEW.jours, NEW.sens,                 |
    |                         |        | NEW.correspondance), NOW(), CONCAT_WS('|', NEW.selection, NEW.responsableId, |
    |                         |        | NEW.station1Id, NEW.service1Id, NEW.station2Id, NEW.service2Id));            |
    |                         |        | END                                                                          |
    +-------------------------+--------+------------------------------------------------------------------------------+
    | affectations_bu_history | UPDATE | BEGIN                                                                        |
    |                         |        | INSERT INTO sbm_s_history (table_name, action, id_name, id_txt, dt, log)     |
    |                         |        | VALUES ('sbm_t_affectations', 'update', CONCAT_WS('|', 'millesime',          |
    |                         |        | 'eleveId', 'trajet', 'jours', 'sens', 'correspondance'), CONCAT_WS('|',      |
    |                         |        | OLD.millesime, OLD.eleveId, OLD.trajet, OLD.jours, OLD.sens,                 |
    |                         |        | OLD.correspondance), NOW(), CONCAT_WS('|', OLD.selection, OLD.responsableId, |
    |                         |        | OLD.station1Id, OLD.service1Id, OLD.station2Id, OLD.service2Id));            |
    |                         |        | END                                                                          |
    +-------------------------+--------+------------------------------------------------------------------------------+
    | affectations_bd_history | DELETE | BEGIN                                                                        |
    |                         |        | INSERT INTO sbm_s_history (table_name, action, id_name, id_txt, dt, log)     |
    |                         |        | VALUES ('sbm_t_affectations', 'delete', CONCAT_WS('|', 'millesime',          |
    |                         |        | 'eleveId', 'trajet', 'jours', 'sens', 'correspondance'), CONCAT_WS('|',      |
    |                         |        | OLD.millesime, OLD.eleveId, OLD.trajet, OLD.jours, OLD.sens,                 |
    |                         |        | OLD.correspondance), NOW(), CONCAT_WS('|', OLD.selection, OLD.responsableId, |
    |                         |        | OLD.station1Id, OLD.service1Id, OLD.station2Id, OLD.service2Id));            |
    |                         |        | END                                                                          |
    +-------------------------+--------+------------------------------------------------------------------------------+