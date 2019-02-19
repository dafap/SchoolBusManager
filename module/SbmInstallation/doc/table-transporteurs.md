#Table transporteurs

##Structure

    +-----------------------+-------------+------+-----+---------+----------------+
    | Field                 | Type        | Null | Key | Default | Extra          |
    +-----------------------+-------------+------+-----+---------+----------------+
    | transporteurId        | int(11)     | NO   | PRI | NULL    | auto_increment |
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
    | rib_titulaire         | varchar(32) | NO   |     |         |                |
    | rib_domiciliation     | varchar(24) | NO   |     |         |                |
    | rib_bic               | varchar(11) | NO   |     |         |                |
    | rib_iban              | varchar(34) | NO   |     |         |                |
    +-----------------------+-------------+------+-----+---------+----------------+

##Indexes

    +------------+-----------+--------------+----------------+-----------+-------------+------+------------+
    | Non_unique | Key_name  | Seq_in_index | Column_name    | Collation | Cardinality | Null | Index_type |
    +------------+-----------+--------------+----------------+-----------+-------------+------+------------+
    |          0 | PRIMARY   |            1 | transporteurId | A         |           8 |      | BTREE      |
    |          1 | communeId |            1 | communeId      | A         |           6 |      | BTREE      |
    +------------+-----------+--------------+----------------+-----------+-------------+------+------------+

##Foreign keys

    +----------------------------+-----------------+----------------------|-----------------+---------+---------+
    | Key_constraint             | ForeignKey name | Référence table      | Référence field | Delete  | Update  |
    +----------------------------+-----------------+----------------------|-----------------+---------+---------+
    | sbm_t_transporteurs_ibfk_1 | communeId       | sbm_t_communes       | communeId       |         | Cascade |
    +----------------------------+-----------------+----------------------|-----------------+---------+---------+

##Triggers

_Pas de trigger._

