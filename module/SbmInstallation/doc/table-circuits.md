#Table circuits

##Structure

    +--------------+---------------------+------+-----+----------+----------------+
    | Field        | Type                | Null | Key | Default  | Extra          |
    +--------------+---------------------+------+-----+----------+----------------+
    | circuitId    | int(11)             | NO   | PRI | NULL     | auto_increment |
    | selection    | tinyint(1)          | NO   |     | 0        |                |
    | millesime    | int(11)             | NO   | MUL | NULL     |                |
    | serviceId    | varchar(11)         | NO   | MUL | NULL     |                |
    | stationId    | int(11)             | NO   | MUL | NULL     |                |
    | passage      | int(11)             | NO   |     | 1        |                |
    | semaine      | tinyint(4) unsigned | NO   |     | 31       |                |
    | m1           | time                | NO   |     | 00:00:00 |                |
    | s1           | time                | NO   |     | 23:59:59 |                |
    | m2           | time                | NO   |     | 00:00:00 |                |
    | s2           | time                | NO   |     | 23:59:59 |                |
    | m3           | time                | NO   |     | 00:00:00 |                |
    | s3           | time                | NO   |     | 23:59:59 |                |
    | distance     | decimal(7,3)        | NO   |     | 0.000    |                |
    | montee       | tinyint(1) unsigned | NO   |     | 1        |                |
    | descente     | tinyint(1) unsigned | NO   |     | 0        |                |
    | typeArret    | text                | YES  |     | NULL     |                |
    | commentaire1 | text                | YES  |     | NULL     |                |
    | commentaire2 | text                | NO   |     | NULL     |                |
    | geopt        | geometry            | YES  |     | NULL     |                |
    +--------------+---------------------+------+-----+----------+----------------+

##Indexes

    +------------+-----------+--------------+-------------+-----------+-------------+------+------------+
    | Non_unique | Key_name  | Seq_in_index | Column_name | Collation | Cardinality | Null | Index_type |
    +------------+-----------+--------------+-------------+-----------+-------------+------+------------+
    |          0 | PRIMARY   |            1 | circuitId   | A         |        1213 |      | BTREE      |
    |          0 | milsersta |            1 | millesime   | A         |           5 |      | BTREE      |
    |          0 | milsersta |            2 | serviceId   | A         |         157 |      | BTREE      |
    |          0 | milsersta |            3 | stationId   | A         |        1213 |      | BTREE      |
    |          0 | milsersta |            4 | passage     | A         |        1213 |      | BTREE      |
    |          1 | serviceId |            1 | serviceId   | A         |          45 |      | BTREE      |
    |          1 | stationId |            1 | stationId   | A         |         214 |      | BTREE      |
    +------------+-----------+--------------+-------------+-----------+-------------+------+------------+

##Foreign keys

    +----------------------------+-----------------+----------------------|-----------------+---------+---------+
    | Key_constraint             | ForeignKey name | Référence table      | Référence field | Delete  | Update  |
    +----------------------------+-----------------+----------------------|-----------------+---------+---------+
    | sbm_t_circuits_ibfk_1      | serviceId       | sbm_t_services       | serviceId       |         | Cascade |
    | sbm_t_circuits_ibfk_2      | stationId       | sbm_t_stations       | stationId       |         | Cascade |
    +----------------------------+-----------------+----------------------|-----------------+---------+---------+

##Triggers

_Pas de trigger._

