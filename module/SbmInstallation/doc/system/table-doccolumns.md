#Table affectations

##Structure

    +------------------+---------------------+------+-----+----------+----------------+
    | Field            | Type                | Null | Key | Default  | Extra          |
    +------------------+---------------------+------+-----+----------+----------------+
    | doccolumnId      | int(11)             | NO   | PRI | NULL     | auto_increment |
    | documentId       | int(11)             | NO   | MUL | 1        |                |
    | ordinal_table    | int(11)             | NO   |     | 1        |                |
    | ordinal_position | int(11)             | NO   |     | 1        |                |
    | thead            | varchar(255)        | NO   |     |          |                |
    | thead_align      | varchar(8)          | NO   |     | standard |                |
    | thead_stretch    | tinyint(1) unsigned | NO   |     | 0        |                |
    | thead_precision  | tinyint(3)          | NO   |     | -1       |                |
    | thead_completion | tinyint(3)          | NO   |     | 0        |                |
    | tbody            | varchar(255)        | NO   |     |          |                |
    | tbody_align      | varchar(8)          | NO   |     | standard |                |
    | tbody_stretch    | tinyint(1) unsigned | NO   |     | 0        |                |
    | tbody_precision  | tinyint(3)          | NO   |     | -1       |                |
    | tbody_completion | tinyint(3)          | NO   |     | 0        |                |
    | tfoot            | varchar(255)        | NO   |     |          |                |
    | tfoot_align      | varchar(8)          | NO   |     | standard |                |
    | tfoot_stretch    | tinyint(1) unsigned | NO   |     | 0        |                |
    | tfoot_precision  | tinyint(3)          | NO   |     | -1       |                |
    | tfoot_completion | tinyint(3)          | NO   |     | 0        |                |
    | filter           | text                | NO   |     | NULL     |                |
    | width            | int(11)             | NO   |     | 0        |                |
    | truncate         | tinyint(1)          | NO   |     | 0        |                |
    | nl               | tinyint(1)          | NO   |     | 0        |                |
    +------------------+---------------------+------+-----+----------+----------------+

##Indexes

    +------------+------------+--------------+-------------+-----------+-------------+------+------------+
    | Non_unique | Key_name   | Seq_in_index | Column_name | Collation | Cardinality | Null | Index_type |
    +------------+------------+--------------+-------------+-----------+-------------+------+------------+
    |          0 | PRIMARY    |            1 | doccolumnId | A         |         263 |      | BTREE      |
    |          1 | documentId |            1 | documentId  | A         |          34 |      | BTREE      |
    +------------+------------+--------------+-------------+-----------+-------------+------+------------+

##Foreign keys

    +-------------------------+-----------------+----------------------|-----------------+---------+---------+
    | Key_constraint          | ForeignKey name | Référence table      | Référence field | Delete  | Update  |
    +-------------------------+-----------------+----------------------|-----------------+---------+---------+
    | sbm_s_doccolumns_ibfk_1 | documentId      | sbm_s_documents      | documentId      | Cascade | Cascade |
    +-------------------------+-----------------+----------------------|-----------------+---------+---------+

##Triggers

_Pas de trigger._

