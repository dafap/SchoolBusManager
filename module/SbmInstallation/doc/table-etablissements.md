#Table etablissements

##Structure

    +-----------------+---------------------+------+-----+--------------+-------+
    | Field           | Type                | Null | Key | Default      | Extra |
    +-----------------+---------------------+------+-----+--------------+-------+
    | etablissementId | char(8)             | NO   | PRI | NULL         |       |
    | selection       | tinyint(1)          | NO   |     | 0            |       |
    | nom             | varchar(45)         | NO   |     | NULL         |       |
    | alias           | varchar(30)         | YES  |     |              |       |
    | aliasCG         | varchar(50)         | YES  |     |              |       |
    | adresse1        | varchar(38)         | NO   |     |              |       |
    | adresse2        | varchar(38)         | NO   |     |              |       |
    | codePostal      | varchar(5)          | NO   |     | NULL         |       |
    | communeId       | varchar(6)          | NO   | MUL | NULL         |       |
    | niveau          | tinyint(3) unsigned | NO   |     | 255          |       |
    | statut          | tinyint(1)          | NO   |     | 1            |       |
    | visible         | tinyint(1)          | NO   |     | 1            |       |
    | desservie       | tinyint(1)          | NO   |     | 1            |       |
    | regrPeda        | tinyint(1)          | NO   |     | 0            |       |
    | rattacheA       | varchar(8)          | NO   |     |              |       |
    | telephone       | varchar(10)         | NO   |     |              |       |
    | fax             | varchar(10)         | NO   |     |              |       |
    | email           | varchar(80)         | NO   |     |              |       |
    | directeur       | varchar(30)         | NO   |     |              |       |
    | jOuverture      | tinyint(3) unsigned | NO   |     | 127          |       |
    | hMatin          | varchar(5)          | NO   |     |              |       |
    | hMidi           | varchar(5)          | NO   |     |              |       |
    | hAMidi          | varchar(5)          | NO   |     |              |       |
    | hSoir           | varchar(5)          | NO   |     |              |       |
    | hGarderieOMatin | varchar(5)          | NO   |     |              |       |
    | hGarderieFMidi  | varchar(5)          | NO   |     |              |       |
    | hGarderieFSoir  | varchar(5)          | NO   |     |              |       |
    | x               | decimal(18,10)      | NO   |     | 0.0000000000 |       |
    | y               | decimal(18,10)      | NO   |     | 0.0000000000 |       |
    | geopt           | geometry            | YES  |     | NULL         |       |
    +-----------------+---------------------+------+-----+--------------+-------+

##Indexes

    +------------+-----------+--------------+-----------------+-----------+-------------+------+------------+
    | Non_unique | Key_name  | Seq_in_index | Column_name     | Collation | Cardinality | Null | Index_type |
    +------------+-----------+--------------+-----------------+-----------+-------------+------+------------+
    |          0 | PRIMARY   |            1 | etablissementId | A         |          32 |      | BTREE      |
    |          1 | communeId |            1 | communeId       | A         |          11 |      | BTREE      |
    +------------+-----------+--------------+-----------------+-----------+-------------+------+------------+

##Foreign keys

    +-----------------------------+-----------------+----------------------|-----------------+---------+---------+
    | Key_constraint              | ForeignKey name | Référence table      | Référence field | Delete  | Update  |
    +-----------------------------+-----------------+----------------------|-----------------+---------+---------+
    | sbm_t_etablissements_ibfk_1 | communeId       | sbm_t_communes       | communeId       |         | Cascade |
    +-----------------------------+-----------------+----------------------|-----------------+---------+---------+

##Triggers

_Pas de trigger._

