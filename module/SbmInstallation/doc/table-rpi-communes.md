#Table rpi-communes

##Structure

    +-----------+---------+------+-----+---------+-------+
    | Field     | Type    | Null | Key | Default | Extra |
    +-----------+---------+------+-----+---------+-------+
    | rpiId     | int(11) | NO   | PRI | NULL    |       |
    | communeId | char(8) | NO   | PRI | NULL    |       |
    +-----------+---------+------+-----+---------+-------+

##Indexes

    +------------+-----------+--------------+-------------+-----------+-------------+------+------------+
    | Non_unique | Key_name  | Seq_in_index | Column_name | Collation | Cardinality | Null | Index_type |
    +------------+-----------+--------------+-------------+-----------+-------------+------+------------+
    |          0 | PRIMARY   |            1 | rpiId       | A         |           4 |      | BTREE      |
    |          0 | PRIMARY   |            2 | communeId   | A         |           9 |      | BTREE      |
    |          1 | communeId |            1 | communeId   | A         |           9 |      | BTREE      |
    +------------+-----------+--------------+-------------+-----------+-------------+------+------------+

##Foreign keys

    +---------------------------+-----------------+----------------------|-----------------+---------+---------+
    | Key_constraint            | ForeignKey name | Référence table      | Référence field | Delete  | Update  |
    +---------------------------+-----------------+----------------------|-----------------+---------+---------+
    | sbm_t_rpi-communes_ibfk_1 | rpiId           | sbm_t_rpi            | rpiId           | Cascade | Cascade |
    | sbm_t_rpi-communes_ibfk_2 | communeId       | sbm_t_communes       | communeId       | Cascade | Cascade |
    +---------------------------+-----------------+----------------------|-----------------+---------+---------+

##Triggers

_Pas de trigger._

