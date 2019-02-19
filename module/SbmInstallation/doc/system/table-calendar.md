#Table affectations

##Structure

    +-------------+--------------+------+-----+---------+----------------+
    | Field       | Type         | Null | Key | Default | Extra          |
    +-------------+--------------+------+-----+---------+----------------+
    | calendarId  | int(11)      | NO   | PRI | NULL    | auto_increment |
    | ouvert      | tinyint(1)   | NO   |     | 0       |                |
    | millesime   | int(4)       | NO   | MUL | NULL    |                |
    | ordinal     | tinyint(3)   | NO   |     | NULL    |                |
    | nature      | varchar(4)   | NO   |     | NULL    |                |
    | rang        | tinyint(3)   | NO   |     | 1       |                |
    | libelle     | varchar(64)  | NO   |     | NULL    |                |
    | description | varchar(255) | NO   |     | NULL    |                |
    | dateDebut   | date         | YES  |     | NULL    |                |
    | dateFin     | date         | YES  |     | NULL    |                |
    | echeance    | date         | YES  |     | NULL    |                |
    | exercice    | int(4)       | NO   |     | 0       |                |
    +-------------+--------------+------+-----+---------+----------------+

##Indexes

    +------------+-------------------+--------------+-------------+-----------+-------------+------+------------+
    | Non_unique | Key_name          | Seq_in_index | Column_name | Collation | Cardinality | Null | Index_type |
    +------------+-------------------+--------------+-------------+-----------+-------------+------+------------+
    |          0 | PRIMARY           |            1 | calendarId  | A         |          84 |      | BTREE      |
    |          0 | millesime-ordinal |            1 | millesime   | A         |        NULL |      | BTREE      |
    |          0 | millesime-ordinal |            2 | ordinal     | A         |          84 |      | BTREE      |
    |          0 | millesime-nature  |            1 | millesime   | A         |        NULL |      | BTREE      |
    |          0 | millesime-nature  |            2 | nature      | A         |        NULL |      | BTREE      |
    |          0 | millesime-nature  |            3 | rang        | A         |          84 |      | BTREE      |
    +------------+-------------------+--------------+-------------+-----------+-------------+------+------------+

##Foreign keys

_Pas de foreign key._

##Triggers

_Pas de trigger._