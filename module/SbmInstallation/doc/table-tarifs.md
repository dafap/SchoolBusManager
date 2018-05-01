#Table tarifs

##Structure

    +-----------+---------------+------+-----+---------+----------------+
    | Field     | Type          | Null | Key | Default | Extra          |
    +-----------+---------------+------+-----+---------+----------------+
    | tarifId   | int(11)       | NO   | PRI | NULL    | auto_increment |
    | selection | tinyint(1)    | NO   |     | 0       |                |
    | montant   | decimal(10,2) | NO   |     | 0.00    |                |
    | nom       | varchar(48)   | NO   |     | NULL    |                |
    | rythme    | int(4)        | NO   |     | 1       |                |
    | grille    | int(4)        | NO   |     | 1       |                |
    | mode      | int(4)        | NO   |     | 3       |                |
    +-----------+---------------+------+-----+---------+----------------+

##Indexes

    +------------+----------+--------------+-------------+-----------+-------------+------+------------+
    | Non_unique | Key_name | Seq_in_index | Column_name | Collation | Cardinality | Null | Index_type |
    +------------+----------+--------------+-------------+-----------+-------------+------+------------+
    |          0 | PRIMARY  |            1 | tarifId     | A         |           3 |      | BTREE      |
    +------------+----------+--------------+-------------+-----------+-------------+------+------------+

##Foreign keys

_Pas de foreign key._

##Triggers

_Pas de trigger._

