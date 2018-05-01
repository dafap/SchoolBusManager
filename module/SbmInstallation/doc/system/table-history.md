#Table affectations

##Structure

    +------------+-------------+------+-----+---------+-------+
    | Field      | Type        | Null | Key | Default | Extra |
    +------------+-------------+------+-----+---------+-------+
    | table_name | varchar(32) | YES  | MUL | NULL    |       |
    | action     | char(6)     | NO   |     | NULL    |       |
    | id_name    | varchar(64) | YES  |     | NULL    |       |
    | id_int     | int(11)     | NO   |     | 0       |       |
    | id_txt     | varchar(25) | YES  |     | NULL    |       |
    | dt         | datetime    | NO   |     | NULL    |       |
    | log        | text        | YES  |     | NULL    |       |
    +------------+-------------+------+-----+---------+-------+

##Indexes

    +------------+------------------------+--------------+-------------+-----------+-------------+------+------------+
    | Non_unique | Key_name               | Seq_in_index | Column_name | Collation | Cardinality | Null | Index_type |
    +------------+------------------------+--------------+-------------+-----------+-------------+------+------------+
    |          1 | HISTORY_Table          |            1 | table_name  | A         |           6 | YES  | BTREE      |
    |          1 | HISTORY_Table          |            2 | dt          | A         |        5523 |      | BTREE      |
    |          1 | HISTORY_Table_IndexInt |            1 | table_name  | A         |           6 | YES  | BTREE      |
    |          1 | HISTORY_Table_IndexInt |            2 | id_name     | A         |           6 | YES  | BTREE      |
    |          1 | HISTORY_Table_IndexInt |            3 | id_int      | A         |        1578 |      | BTREE      |
    |          1 | HISTORY_Table_IndexInt |            4 | dt          | A         |       22090 |      | BTREE      |
    |          1 | HISTORY_Table_IndexTxt |            1 | table_name  | A         |           6 | YES  | BTREE      |
    |          1 | HISTORY_Table_IndexTxt |            2 | id_name     | A         |           6 | YES  | BTREE      |
    |          1 | HISTORY_Table_IndexTxt |            3 | id_txt      | A         |       22090 | YES  | BTREE      |
    |          1 | HISTORY_Table_IndexTxt |            4 | dt          | A         |       22090 |      | BTREE      |
    +------------+------------------------+--------------+-------------+-----------+-------------+------+------------+

##Foreign keys

_Pas de foreign key._

##Triggers

_Pas de trigger._

