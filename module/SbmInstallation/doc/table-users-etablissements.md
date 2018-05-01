#Table users-etablissements

##Structure

    +-----------------+---------+------+-----+---------+-------+
    | Field           | Type    | Null | Key | Default | Extra |
    +-----------------+---------+------+-----+---------+-------+
    | userId          | int(11) | NO   | PRI | NULL    |       |
    | etablissementId | char(8) | NO   | PRI | NULL    |       |
    +-----------------+---------+------+-----+---------+-------+

##Indexes

    +------------+-----------------+--------------+-----------------+-----------+-------------+------+------------+
    | Non_unique | Key_name        | Seq_in_index | Column_name     | Collation | Cardinality | Null | Index_type |
    +------------+-----------------+--------------+-----------------+-----------+-------------+------+------------+
    |          0 | PRIMARY         |            1 | userId          | A         |           0 |      | BTREE      |
    |          0 | PRIMARY         |            2 | etablissementId | A         |           0 |      | BTREE      |
    |          1 | etablissementId |            1 | etablissementId | A         |           0 |      | BTREE      |
    +------------+-----------------+--------------+-----------------+-----------+-------------+------+------------+

##Foreign keys

    +-----------------------------------+-----------------+----------------------|-----------------+---------+---------+
    | Key_constraint                    | ForeignKey name | Référence table      | Référence field | Delete  | Update  |
    +-----------------------------------+-----------------+----------------------|-----------------+---------+---------+
    | sbm_t_users-etablissements_ibfk_1 | userId          | sbm_t_users          | userId          |         | Cascade |
    | sbm_t_users-etablissements_ibfk_2 | etablissementId | sbm_t_etablissements | etablissementId |         | Cascade |
    +-----------------------------------+-----------------+----------------------|-----------------+---------+---------+

##Triggers

_Pas de trigger._

