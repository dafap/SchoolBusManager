#Table affectations

##Structure

    +---------+-------------+------+-----+---------+-------+
    | Field   | Type        | Null | Key | Default | Extra |
    +---------+-------------+------+-----+---------+-------+
    | nature  | varchar(20) | NO   | PRI | NULL    |       |
    | code    | int(11)     | NO   | PRI | 1       |       |
    | libelle | text        | NO   |     | NULL    |       |
    | ouvert  | tinyint(1)  | NO   |     | 1       |       |
    +---------+-------------+------+-----+---------+-------+

##Indexes

    +------------+----------+--------------+-------------+-----------+-------------+------+------------+
    | Non_unique | Key_name | Seq_in_index | Column_name | Collation | Cardinality | Null | Index_type |
    +------------+----------+--------------+-------------+-----------+-------------+------+------------+
    |          0 | PRIMARY  |            1 | nature      | A         |        NULL |      | BTREE      |
    |          0 | PRIMARY  |            2 | code        | A         |           7 |      | BTREE      |
    +------------+----------+--------------+-------------+-----------+-------------+------+------------+

##Foreign keys

_Pas de foreign key._

##Triggers

_Pas de trigger._

