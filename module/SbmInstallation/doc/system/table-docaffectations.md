#Table docaffectations

##Structure

    +------------------+--------------+------+-----+---------+----------------+
    | Field            | Type         | Null | Key | Default | Extra          |
    +------------------+--------------+------+-----+---------+----------------+
    | docaffectationId | int(11)      | NO   | PRI | NULL    | auto_increment |
    | documentId       | int(11)      | NO   | MUL | NULL    |                |
    | route            | varchar(255) | NO   |     | NULL    |                |
    | libelle          | varchar(255) | NO   |     | NULL    |                |
    | ordinal_position | int(11)      | NO   |     | NULL    |                |
    +------------------+--------------+------+-----+---------+----------------+

##Indexes

    +------------+------------+--------------+------------------+-----------+-------------+------+------------+
    | Non_unique | Key_name   | Seq_in_index | Column_name      | Collation | Cardinality | Null | Index_type |
    +------------+------------+--------------+------------------+-----------+-------------+------+------------+
    |          0 | PRIMARY    |            1 | docaffectationId | A         |          43 |      | BTREE      |
    |          1 | documentId |            1 | documentId       | A         |          30 |      | BTREE      |
    +------------+------------+--------------+------------------+-----------+-------------+------+------------+

##Foreign keys

    +------------------------------+-----------------+----------------------|-----------------+---------+---------+
    | Key_constraint               | ForeignKey name | Référence table      | Référence field | Delete  | Update  |
    +------------------------------+-----------------+----------------------|-----------------+---------+---------+
    | sbm_s_docaffectations_ibfk_1 | documentId      | sbm_s_documents      | documentId      | Cascade | Cascade |
    +------------------------------+-----------------+----------------------|-----------------+---------+---------+

##Triggers

_Pas de trigger._
