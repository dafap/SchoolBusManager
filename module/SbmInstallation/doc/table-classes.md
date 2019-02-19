#Table classes

##Structure

    +-----------+---------------------+------+-----+---------+----------------+
    | Field     | Type                | Null | Key | Default | Extra          |
    +-----------+---------------------+------+-----+---------+----------------+
    | classeId  | int(11)             | NO   | PRI | NULL    | auto_increment |
    | nom       | varchar(30)         | NO   |     | NULL    |                |
    | aliasCG   | varchar(30)         | YES  |     | NULL    |                |
    | niveau    | tinyint(3) unsigned | NO   |     | 255     |                |
    | suivantId | int(11)             | YES  |     | NULL    |                |
    | selection | tinyint(1)          | NO   |     | 0       |                |
    +-----------+---------------------+------+-----+---------+----------------+

##Indexes

    +------------+----------+--------------+-------------+-----------+-------------+------+------------+
    | Non_unique | Key_name | Seq_in_index | Column_name | Collation | Cardinality | Null | Index_type |
    +------------+----------+--------------+-------------+-----------+-------------+------+------------+
    |          0 | PRIMARY  |            1 | classeId    | A         |          26 |      | BTREE      |
    +------------+----------+--------------+-------------+-----------+-------------+------+------------+

##Foreign keys

_Pas de foreign key._

##Triggers

_Pas de trigger._

