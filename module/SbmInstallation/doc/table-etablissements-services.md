#Table etablissements-services

##Structure

    +-----------------+-------------+------+-----+---------+-------+
    | Field           | Type        | Null | Key | Default | Extra |
    +-----------------+-------------+------+-----+---------+-------+
    | etablissementId | char(8)     | NO   | PRI | NULL    |       |
    | serviceId       | varchar(11) | NO   | PRI | NULL    |       |
    | stationId       | int(11)     | NO   |     | NULL    |       |
    +-----------------+-------------+------+-----+---------+-------+

##Indexes

    +------------+-----------+--------------+-----------------+-----------+-------------+------+------------+
    | Non_unique | Key_name  | Seq_in_index | Column_name     | Collation | Cardinality | Null | Index_type |
    +------------+-----------+--------------+-----------------+-----------+-------------+------+------------+
    |          0 | PRIMARY   |            1 | etablissementId | A         |          23 |      | BTREE      |
    |          0 | PRIMARY   |            2 | serviceId       | A         |         160 |      | BTREE      |
    |          1 | serviceId |            1 | serviceId       | A         |          45 |      | BTREE      |
    +------------+-----------+--------------+-----------------+-----------+-------------+------+------------+

##Foreign keys

_Pas de foreign key._

##Triggers

_Pas de trigger._

