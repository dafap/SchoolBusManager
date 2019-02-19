#Table users-transporteurs

##Structure

    +----------------+---------+------+-----+---------+-------+
    | Field          | Type    | Null | Key | Default | Extra |
    +----------------+---------+------+-----+---------+-------+
    | userId         | int(11) | NO   | PRI | NULL    |       |
    | transporteurId | int(11) | NO   | PRI | NULL    |       |
    +----------------+---------+------+-----+---------+-------+

##Indexes

    +------------+----------------+--------------+----------------+-----------+-------------+------+------------+
    | Non_unique | Key_name       | Seq_in_index | Column_name    | Collation | Cardinality | Null | Index_type |
    +------------+----------------+--------------+----------------+-----------+-------------+------+------------+
    |          0 | PRIMARY        |            1 | userId         | A         |           8 |      | BTREE      |
    |          0 | PRIMARY        |            2 | transporteurId | A         |           8 |      | BTREE      |
    |          1 | transporteurId |            1 | transporteurId | A         |           4 |      | BTREE      |
    +------------+----------------+--------------+----------------+-----------+-------------+------+------------+

##Foreign keys

    +----------------------------------+-----------------+----------------------|-----------------+---------+---------+
    | Key_constraint                   | ForeignKey name | Référence table      | Référence field | Delete  | Update  |
    +----------------------------------+-----------------+----------------------|-----------------+---------+---------+
    | sbm_t_users-transporteurs_ibfk_1 | userId          | sbm_t_users          | userId          |         | Cascade |
    | sbm_t_users-transporteurs_ibfk_2 | transporteurId  | sbm_t_transporteurs  | transporteurId  |         | Cascade |
    +----------------------------------+-----------------+----------------------|-----------------+---------+---------+

##Triggers

_Pas de trigger._

