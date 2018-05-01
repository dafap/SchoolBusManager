#Table affectations

##Structure

    +----------------+-------------+------+-----+---------+----------------+
    | Field          | Type        | Null | Key | Default | Extra          |
    +----------------+-------------+------+-----+---------+----------------+
    | doclabelId     | int(11)     | NO   | PRI | NULL    | auto_increment |
    | documentId     | int(11)     | NO   | MUL | NULL    |                |
    | margin_left    | float       | NO   |     | 0       |                |
    | margin_top     | float       | NO   |     | 8       |                |
    | x_space        | float       | NO   |     | 0       |                |
    | y_space        | float       | NO   |     | 0       |                |
    | label_width    | float       | NO   |     | 105     |                |
    | label_height   | float       | NO   |     | 35      |                |
    | cols_number    | int(11)     | NO   |     | 2       |                |
    | rows_number    | int(11)     | NO   |     | 8       |                |
    | padding_top    | float       | NO   |     | 3       |                |
    | padding_right  | float       | NO   |     | 3       |                |
    | padding_bottom | float       | NO   |     | 3       |                |
    | padding_left   | float       | NO   |     | 3       |                |
    | border         | varchar(4)  | NO   |     |         |                |
    | border_dash    | varchar(4)  | NO   |     | 0       |                |
    | border_width   | float       | NO   |     | 0.3     |                |
    | border_color   | varchar(20) | NO   |     | 000000  |                |
    +----------------+-------------+------+-----+---------+----------------+

##Indexes

    +------------+------------+--------------+-------------+-----------+-------------+------+------------+
    | Non_unique | Key_name   | Seq_in_index | Column_name | Collation | Cardinality | Null | Index_type |
    +------------+------------+--------------+-------------+-----------+-------------+------+------------+
    |          0 | PRIMARY    |            1 | doclabelId  | A         |           5 |      | BTREE      |
    |          1 | documentId |            1 | documentId  | A         |           5 |      | BTREE      |
    +------------+------------+--------------+-------------+-----------+-------------+------+------------+

##Foreign keys

    +------------------------+-----------------+----------------------|-----------------+---------+---------+
    | Key_constraint         | ForeignKey name | Référence table      | Référence field | Delete  | Update  |
    +------------------------+-----------------+----------------------|-----------------+---------+---------+
    | sbm_s_doclabels_ibfk_1 | documentId      | sbm_s_documents      | documentId      | Cascade | Cascade |
    +------------------------+-----------------+----------------------|-----------------+---------+---------+

##Triggers

_Pas de trigger._

