#Table stations

##Structure

    +-----------+----------------+------+-----+--------------+----------------+
    | Field     | Type           | Null | Key | Default      | Extra          |
    +-----------+----------------+------+-----+--------------+----------------+
    | stationId | int(11)        | NO   | PRI | NULL         | auto_increment |
    | selection | tinyint(1)     | NO   |     | 0            |                |
    | communeId | varchar(6)     | NO   | MUL | NULL         |                |
    | nom       | varchar(45)    | NO   |     | NULL         |                |
    | aliasCG   | varchar(45)    | NO   |     |              |                |
    | codeCG    | int(11)        | NO   |     | 0            |                |
    | x         | decimal(18,10) | NO   |     | 0.0000000000 |                |
    | y         | decimal(18,10) | NO   |     | 0.0000000000 |                |
    | geopt     | geometry       | YES  |     | NULL         |                |
    | visible   | tinyint(1)     | NO   |     | 1            |                |
    | ouverte   | tinyint(1)     | NO   |     | 1            |                |
    +-----------+----------------+------+-----+--------------+----------------+

##Indexes

    +----------------+------------+-----------+--------------+-------------+-----------+-------------+------+------------+
    | Table          | Non_unique | Key_name  | Seq_in_index | Column_name | Collation | Cardinality | Null | Index_type |
    +----------------+------------+-----------+--------------+-------------+-----------+-------------+------+------------+
    | sbm_t_stations |          0 | PRIMARY   |            1 | stationId   | A         |         228 |      | BTREE      |
    | sbm_t_stations |          1 | communeId |            1 | communeId   | A         |          14 |      | BTREE      |
    +----------------+------------+-----------+--------------+-------------+-----------+-------------+------+------------+

##Foreign keys

    +----------------------------+-----------------+----------------------|-----------------+---------+---------+
    | Key_constraint             | ForeignKey name | Référence table      | Référence field | Delete  | Update  |
    +----------------------------+-----------------+----------------------|-----------------+---------+---------+
    | sbm_t_stations_ibfk_1      | communeId       | sbm_t_communes       | communeId       |         | Cascade |
    +----------------------------+-----------------+----------------------|-----------------+---------+---------+

##Triggers

_Pas de trigger._

