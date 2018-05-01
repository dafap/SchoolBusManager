#Table rpi-etablissements

##Structure

    +-----------------+---------+------+-----+---------+-------+
    | Field           | Type    | Null | Key | Default | Extra |
    +-----------------+---------+------+-----+---------+-------+
    | rpiId           | int(11) | NO   | PRI | NULL    |       |
    | etablissementId | char(8) | NO   | PRI | NULL    |       |
    +-----------------+---------+------+-----+---------+-------+

##Indexes

    +------------+-----------------+--------------+-----------------+-----------+-------------+------+------------+
    | Non_unique | Key_name        | Seq_in_index | Column_name     | Collation | Cardinality | Null | Index_type |
    +------------+-----------------+--------------+-----------------+-----------+-------------+------+------------+
    |          0 | PRIMARY         |            1 | rpiId           | A         |           3 |      | BTREE      |
    |          0 | PRIMARY         |            2 | etablissementId | A         |           6 |      | BTREE      |
    |          1 | etablissementId |            1 | etablissementId | A         |           6 |      | BTREE      |
    +------------+-----------------+--------------+-----------------+-----------+-------------+------+------------+

##Foreign keys

    +---------------------------------+-----------------+----------------------|-----------------+---------+---------+
    | Key_constraint                  | ForeignKey name | Référence table      | Référence field | Delete  | Update  |
    +---------------------------------+-----------------+----------------------|-----------------+---------+---------+
    | sbm_t_rpi-etablissements_ibfk_1 | rpiId           | sbm_t_rpi            | rpiId           | Cascade | Cascade |
    | sbm_t_rpi-etablissements_ibfk_2 | etablissementId | sbm_t_etablissements | etablissementId | Cascade | Cascade |
    +---------------------------------+-----------------+----------------------|-----------------+---------+---------+

##Triggers

_Pas de trigger._

