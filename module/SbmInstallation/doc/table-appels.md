#Table appels

##Structure

    +---------------+-------------+------+-----+---------+----------------+
    | Field         | Type        | Null | Key | Default | Extra          |
    +---------------+-------------+------+-----+---------+----------------+
    | appelId       | int(11)     | NO   | PRI | NULL    | auto_increment |
    | referenceId   | varchar(20) | NO   |     | NULL    |                |
    | responsableId | int(11)     | NO   | MUL | NULL    |                |
    | eleveId       | int(11)     | NO   | MUL | NULL    |                |
    +---------------+-------------+------+-----+---------+----------------+

##Indexes

    +------------+---------------+--------------+---------------+-----------+-------------+------+------------+
    | Non_unique | Key_name      | Seq_in_index | Column_name   | Collation | Cardinality | Null | Index_type |
    +------------+---------------+--------------+---------------+-----------+-------------+------+------------+
    |          0 | PRIMARY       |            1 | appelId       | A         |         679 |      | BTREE      |
    |          1 | responsableId |            1 | responsableId | A         |         207 |      | BTREE      |
    |          1 | eleveId       |            1 | eleveId       | A         |         284 |      | BTREE      |
    +------------+---------------+--------------+---------------+-----------+-------------+------+------------+

##Foreign keys

_Pas de foreign key._

##Triggers

_Pas de trigger._

