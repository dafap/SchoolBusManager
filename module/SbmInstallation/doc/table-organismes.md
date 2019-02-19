#Table organismes

##Structure

    +-----------------------+-------------+------+-----+---------+----------------+
    | Field                 | Type        | Null | Key | Default | Extra          |
    +-----------------------+-------------+------+-----+---------+----------------+
    | organismeId           | int(11)     | NO   | PRI | NULL    | auto_increment |
    | selection             | tinyint(1)  | NO   |     | 0       |                |
    | nom                   | varchar(30) | NO   |     | NULL    |                |
    | adresse1              | varchar(38) | NO   |     |         |                |
    | adresse2              | varchar(38) | NO   |     |         |                |
    | codePostal            | varchar(5)  | NO   |     | NULL    |                |
    | communeId             | varchar(6)  | NO   | MUL | NULL    |                |
    | telephone             | varchar(10) | NO   |     |         |                |
    | fax                   | varchar(10) | NO   |     |         |                |
    | email                 | varchar(80) | NO   |     |         |                |
    | siret                 | varchar(14) | NO   |     |         |                |
    | naf                   | varchar(5)  | NO   |     |         |                |
    | tvaIntraCommunautaire | varchar(13) | NO   |     |         |                |
    +-----------------------+-------------+------+-----+---------+----------------+

##Indexes

    +------------+-----------+--------------+-------------+-----------+-------------+------+------------+
    | Non_unique | Key_name  | Seq_in_index | Column_name | Collation | Cardinality | Null | Index_type |
    +------------+-----------+--------------+-------------+-----------+-------------+------+------------+
    |          0 | PRIMARY   |            1 | organismeId | A         |           5 |      | BTREE      |
    |          1 | communeId |            1 | communeId   | A         |           3 |      | BTREE      |
    +------------+-----------+--------------+-------------+-----------+-------------+------+------------+

##Foreign keys

    +----------------------------+-----------------+----------------------|-----------------+---------+---------+
    | Key_constraint             | ForeignKey name | Référence table      | Référence field | Delete  | Update  |
    +----------------------------+-----------------+----------------------|-----------------+---------+---------+
    | sbm_t_organismes_ibfk_1    | communeId       | sbm_t_communes       | communeId       |         | Cascade |
    +----------------------------+-----------------+----------------------|-----------------+---------+---------+

##Triggers

_Pas de trigger._

