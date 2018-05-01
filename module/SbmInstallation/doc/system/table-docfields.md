#Table affectations

##Structure

    +----------------------+---------------------+------+-----+----------+----------------+
    | Field                | Type                | Null | Key | Default  | Extra          |
    +----------------------+---------------------+------+-----+----------+----------------+
    | docfieldId           | int(11)             | NO   | PRI | NULL     | auto_increment |
    | documentId           | int(11)             | NO   | MUL | NULL     |                |
    | ordinal_position     | int(11)             | NO   |     | 1        |                |
    | filter               | text                | NO   |     | NULL     |                |
    | fieldname            | varchar(255)        | NO   |     | NULL     |                |
    | fieldname_width      | float               | NO   |     | 0        |                |
    | fieldname_align      | varchar(8)          | NO   |     | standard |                |
    | fieldname_stretch    | tinyint(1) unsigned | NO   |     | 0        |                |
    | fieldname_completion | tinyint(3)          | NO   |     | 0        |                |
    | fieldname_precision  | tinyint(3)          | NO   |     | -1       |                |
    | is_date              | tinyint(1) unsigned | NO   |     | 0        |                |
    | format               | varchar(255)        | YES  |     | NULL     |                |
    | label                | text                | YES  |     | NULL     |                |
    | label_space          | float               | NO   |     | 3        |                |
    | label_width          | float               | NO   |     | 0        |                |
    | label_align          | varchar(8)          | NO   |     | standard |                |
    | label_stretch        | tinyint(1) unsigned | NO   |     | 0        |                |
    | style                | varchar(6)          | NO   |     | main     |                |
    | height               | float               | NO   |     | 7        |                |
    +----------------------+---------------------+------+-----+----------+----------------+

##Indexes

    +------------+------------+--------------+-------------+-----------+-------------+------+------------+
    | Non_unique | Key_name   | Seq_in_index | Column_name | Collation | Cardinality | Null | Index_type |
    +------------+------------+--------------+-------------+-----------+-------------+------+------------+
    |          0 | PRIMARY    |            1 | docfieldId  | A         |          53 |      | BTREE      |
    |          1 | documentId |            1 | documentId  | A         |           5 |      | BTREE      |
    +------------+------------+--------------+-------------+-----------+-------------+------+------------+

##Foreign keys

    +------------------------+-----------------+----------------------|-----------------+---------+---------+
    | Key_constraint         | ForeignKey name | Référence table      | Référence field | Delete  | Update  |
    +------------------------+-----------------+----------------------|-----------------+---------+---------+
    | sbm_s_docfields_ibfk_1 | documentId      | sbm_s_documents      | documentId      | Cascade | Cascade |
    +------------------------+-----------------+----------------------|-----------------+---------+---------+

##Triggers

_Pas de trigger._

