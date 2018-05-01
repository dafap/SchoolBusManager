#Table services

##Structure

    +----------------+---------------------+------+-----+---------+-------+
    | Field          | Type                | Null | Key | Default | Extra |
    +----------------+---------------------+------+-----+---------+-------+
    | serviceId      | varchar(11)         | NO   | PRI | NULL    |       |
    | selection      | tinyint(1)          | NO   |     | 0       |       |
    | nom            | varchar(45)         | NO   |     | NULL    |       |
    | aliasCG        | varchar(15)         | NO   |     |         |       |
    | transporteurId | int(11)             | NO   | MUL | 0       |       |
    | nbPlaces       | tinyint(3) unsigned | NO   |     | 0       |       |
    | surEtatCG      | tinyint(1)          | NO   |     | 0       |       |
    | operateur      | varchar(4)          | NO   |     | CCDA    |       |
    | kmAVide        | decimal(7,3)        | NO   |     | 0.000   |       |
    | kmEnCharge     | decimal(7,3)        | NO   |     | 0.000   |       |
    | geotrajet      | polygon             | YES  |     | NULL    |       |
    +----------------+---------------------+------+-----+---------+-------+

##Indexes

    +------------+----------------+--------------+----------------+-----------+-------------+------+------------+
    | Non_unique | Key_name       | Seq_in_index | Column_name    | Collation | Cardinality | Null | Index_type |
    +------------+----------------+--------------+----------------+-----------+-------------+------+------------+
    |          0 | PRIMARY        |            1 | serviceId      | A         |          46 |      | BTREE      |
    |          1 | transporteurId |            1 | transporteurId | A         |           8 |      | BTREE      |
    +------------+----------------+--------------+----------------+-----------+-------------+------+------------+

##Foreign keys

    +-----------------------+-----------------+----------------------|-----------------+---------+---------+
    | Key_constraint        | ForeignKey name | Référence table      | Référence field | Delete  | Update  |
    +-----------------------+-----------------+----------------------|-----------------+---------+---------+
    | sbm_t_services_ibfk_1 | transporteurId  | sbm_t_transporteurs  | transporteurId  |         | Cascade |
    +-----------------------+-----------------+----------------------|-----------------+---------+---------+

##Triggers

_Pas de trigger._

