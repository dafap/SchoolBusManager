#Table communes

##Structure

    +-------------+-----------------------+------+-----+---------+-------+
    | Field       | Type                  | Null | Key | Default | Extra |
    +-------------+-----------------------+------+-----+---------+-------+
    | communeId   | varchar(6)            | NO   | PRI | NULL    |       |
    | nom         | varchar(45)           | NO   |     | NULL    |       |
    | nom_min     | varchar(45)           | NO   |     | NULL    |       |
    | alias       | varchar(30)           | YES  |     | NULL    |       |
    | alias_min   | varchar(30)           | YES  |     | NULL    |       |
    | aliasCG     | varchar(45)           | YES  |     | NULL    |       |
    | codePostal  | varchar(5)            | NO   |     | NULL    |       |
    | departement | varchar(3)            | NO   |     | NULL    |       |
    | canton      | varchar(5)            | NO   |     | NULL    |       |
    | membre      | tinyint(1)            | NO   |     | 0       |       |
    | desservie   | tinyint(1)            | NO   |     | 0       |       |
    | visible     | tinyint(1)            | NO   |     | 0       |       |
    | selection   | tinyint(1)            | NO   |     | 0       |       |
    | population  | mediumint(8) unsigned | NO   |     | 0       |       |
    +-------------+-----------------------+------+-----+---------+-------+

##Indexes

    +------------+----------+--------------+-------------+-----------+-------------+------+------------+
    | Non_unique | Key_name | Seq_in_index | Column_name | Collation | Cardinality | Null | Index_type |
    +------------+----------+--------------+-------------+-----------+-------------+------+------------+
    |          0 | PRIMARY  |            1 | communeId   | A         |       36234 |      | BTREE      |
    +------------+----------+--------------+-------------+-----------+-------------+------+------------+

##Foreign keys

_Pas de foreign key._

##Triggers

_Pas de trigger._

