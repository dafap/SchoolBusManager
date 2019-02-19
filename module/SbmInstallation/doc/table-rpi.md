#Table rpi

##Structure

    +-----------+---------------------+------+-----+---------+----------------+
    | Field     | Type                | Null | Key | Default | Extra          |
    +-----------+---------------------+------+-----+---------+----------------+
    | rpiId     | int(11)             | NO   | PRI | NULL    | auto_increment |
    | nom       | varchar(30)         | NO   |     | NULL    |                |
    | libelle   | varchar(50)         | YES  |     | NULL    |                |
    | niveau    | tinyint(3) unsigned | NO   |     | 3       |                |
    | selection | tinyint(1)          | NO   |     | 0       |                |
    +-----------+---------------------+------+-----+---------+----------------+

##Indexes

    +------------+----------+--------------+-------------+-----------+-------------+------+------------+
    | Non_unique | Key_name | Seq_in_index | Column_name | Collation | Cardinality | Null | Index_type |
    +------------+----------+--------------+-------------+-----------+-------------+------+------------+
    |          0 | PRIMARY  |            1 | rpiId       | A         |           2 |      | BTREE      |
    +------------+----------+--------------+-------------+-----------+-------------+------+------------+

##Foreign keys

_Pas de foreign key._

##Triggers

_Pas de trigger._

