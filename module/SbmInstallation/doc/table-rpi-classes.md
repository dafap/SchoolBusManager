#Table rpi-classes

##Structure

    +-----------------+---------+------+-----+---------+-------+
    | Field           | Type    | Null | Key | Default | Extra |
    +-----------------+---------+------+-----+---------+-------+
    | classeId        | int(11) | NO   | PRI | NULL    |       |
    | etablissementId | char(8) | NO   | PRI | NULL    |       |
    +-----------------+---------+------+-----+---------+-------+

##Indexes

    +------------+-----------------+--------------+-----------------+-----------+-------------+------+------------+
    | Non_unique | Key_name        | Seq_in_index | Column_name     | Collation | Cardinality | Null | Index_type |
    +------------+-----------------+--------------+-----------------+-----------+-------------+------+------------+
    |          0 | PRIMARY         |            1 | classeId        | A         |           8 |      | BTREE      |
    |          0 | PRIMARY         |            2 | etablissementId | A         |          29 |      | BTREE      |
    |          1 | etablissementId |            1 | etablissementId | A         |           7 |      | BTREE      |
    +------------+-----------------+--------------+-----------------+-----------+-------------+------+------------+

##Foreign keys

    +--------------------------+-----------------+----------------------|-----------------+---------+---------+
    | Key_constraint           | ForeignKey name | Référence table      | Référence field | Delete  | Update  |
    +--------------------------+-----------------+----------------------|-----------------+---------+---------+
    | sbm_t_rpi-classes_ibfk_1 | classeId        | sbm_t_classes        | classeId        | Cascade | Cascade |
    | sbm_t_rpi-classes_ibfk_2 | etablissementId | sbm_t_etablissements | etablissementId | Cascade | Cascade |
    +--------------------------+-----------------+----------------------|-----------------+---------+---------+

##Triggers

_Pas de trigger._

