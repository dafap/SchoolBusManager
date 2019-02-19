#Table affectations

##Structure

    +------------------------+---------------------+------+-----+---------+----------------+
    | Field                  | Type                | Null | Key | Default | Extra          |
    +------------------------+---------------------+------+-----+---------+----------------+
    | doctableId             | int(11)             | NO   | PRI | NULL    | auto_increment |
    | documentId             | int(11)             | NO   | MUL | 1       |                |
    | ordinal_table          | int(11)             | NO   |     | 1       |                |
    | section                | char(5)             | YES  |     | NULL    |                |
    | description            | varchar(255)        | NO   |     | NULL    |                |
    | visible                | tinyint(1)          | NO   |     | 1       |                |
    | width                  | varchar(4)          | YES  |     | NULL    |                |
    | row_height             | int(11)             | NO   |     | 6       |                |
    | cell_border            | varchar(4)          | NO   |     | 1       |                |
    | cell_align             | char(1)             | NO   |     | L       |                |
    | cell_link              | varchar(128)        | NO   |     |         |                |
    | cell_stretch           | tinyint(1) unsigned | NO   |     | 0       |                |
    | cell_ignore_min_height | tinyint(1) unsigned | NO   |     | 0       |                |
    | cell_calign            | char(1)             | NO   |     | T       |                |
    | cell_valign            | char(1)             | NO   |     | M       |                |
    | draw_color             | varchar(20)         | NO   |     | black   |                |
    | line_width             | float(2,1)          | NO   |     | 0.1     |                |
    | fill_color             | varchar(20)         | NO   |     | E0EBFF  |                |
    | text_color             | varchar(20)         | NO   |     | black   |                |
    | font_style             | char(2)             | NO   |     |         |                |
    +------------------------+---------------------+------+-----+---------+----------------+

##Indexes

    +------------+------------+--------------+-------------+-----------+-------------+------+------------+
    | Non_unique | Key_name   | Seq_in_index | Column_name | Collation | Cardinality | Null | Index_type |
    +------------+------------+--------------+-------------+-----------+-------------+------+------------+
    |          0 | PRIMARY    |            1 | doctableId  | A         |         105 |      | BTREE      |
    |          1 | documentId |            1 | documentId  | A         |          35 |      | BTREE      |
    +------------+------------+--------------+-------------+-----------+-------------+------+------------+

##Foreign keys

    +------------------------+-----------------+----------------------|-----------------+---------+---------+
    | Key_constraint         | ForeignKey name | Référence table      | Référence field | Delete  | Update  |
    +------------------------+-----------------+----------------------|-----------------+---------+---------+
    | sbm_s_doctables_ibfk_1 | documentId      | sbm_s_documents      | documentId      | Cascade | Cascade |
    +------------------------+-----------------+----------------------|-----------------+---------+---------+

##Triggers

_Pas de trigger._

