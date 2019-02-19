#Table secteurs-scolaires-clg-pu

##Structure

    +-----------------+------------+------+-----+---------+-------+
    | Field           | Type       | Null | Key | Default | Extra |
    +-----------------+------------+------+-----+---------+-------+
    | communeId       | varchar(6) | NO   | PRI | NULL    |       |
    | etablissementId | char(8)    | NO   | PRI | NULL    |       |
    +-----------------+------------+------+-----+---------+-------+

##Indexes

    +------------+-----------------+--------------+-----------------+-----------+-------------+------+------------+
    | Non_unique | Key_name        | Seq_in_index | Column_name     | Collation | Cardinality | Null | Index_type |
    +------------+-----------------+--------------+-----------------+-----------+-------------+------+------------+
    |          0 | PRIMARY         |            1 | communeId       | A         |           5 |      | BTREE      |
    |          0 | PRIMARY         |            2 | etablissementId | A         |          11 |      | BTREE      |
    |          1 | etablissementId |            1 | etablissementId | A         |           4 |      | BTREE      |
    +------------+-----------------+--------------+-----------------+-----------+-------------+------+------------+

##Foreign keys

    +----------------------------------------+-----------------+----------------------|-----------------+---------+---------+
    | Key_constraint                         | ForeignKey name | Référence table      | Référence field | Delete  | Update  |
    +----------------------------------------+-----------------+----------------------|-----------------+---------+---------+
    | sbm_t_secteurs-scolaires-clg-pu_ibfk_1 | etablissementId | sbm_t_etablissements | etablissementId |         | Cascade |
    | sbm_t_secteurs-scolaires-clg-pu_ibfk_2 | communeId       | sbm_t_communes       | communeId       |         | Cascade |
    +----------------------------------------+-----------------+----------------------|-----------------+---------+---------+

##Triggers

_Pas de trigger._

