#Table scolarites

##Structure

    +------------------+---------------------+------+-----+---------------------+-------+
    | Field            | Type                | Null | Key | Default             | Form  |
    +------------------+---------------------+------+-----+---------------------+-------+
    | millesime        | int(4)              | NO   | PRI | 0                   |   C=  |
    | eleveId          | int(11)             | NO   | PRI | 0                   |   O=h |
    | selection        | tinyint(1)          | NO   |     | 0                   |   N   |
    | dateInscription  | timestamp           | NO   |     | CURRENT_TIMESTAMP   |   N   |
    | dateModification | datetime            | NO   |     | 1900-01-01 00:00:00 |   N   |
    | etablissementId  | char(8)             | NO   | MUL | NULL                |   N=s |
    | classeId         | int(11)             | NO   | MUL | 0                   |   N=s |
    | chez             | varchar(38)         | YES  |     | NULL                |   O   |
    | adresseL1        | varchar(38)         | YES  |     | NULL                |   O   |
    | adresseL2        | varchar(38)         | YES  |     | NULL                |   O   |
    | codePostal       | varchar(5)          | YES  |     | NULL                |   O   |
    | communeId        | varchar(6)          | YES  | MUL | NULL                |   O   |
    | x                | decimal(18,10)      | NO   |     | 0.0000000000        |   O   |
    | y                | decimal(18,10)      | NO   |     | 0.0000000000        |   O   |
    | geopt            | geometry            | YES  |     | NULL                |   N   |
    | distanceR1       | decimal(7,3)        | NO   |     | 0.000               |   O   |
    | distanceR2       | decimal(7,3)        | NO   |     | 0.000               |   O   |
    | dateEtiquette    | datetime            | NO   |     | 1900-01-01 00:00:00 |   N   |
    | dateCarte        | datetime            | NO   |     | 1900-01-01 00:00:00 |   N   |
    | inscrit          | tinyint(1)          | NO   |     | 1                   |   1=  |
    | gratuit          | tinyint(1)          | NO   |     | 0                   |   N   |
    | paiement         | tinyint(1)          | NO   |     | 1                   |   0=  |
    | duplicata        | int(11)             | NO   |     | 0                   |   0=  |
    | fa               | tinyint(1)          | NO   |     | 0                   |   O=  |
    | anneeComplete    | tinyint(1)          | NO   |     | 1                   |   1=r |
    | subventionR1     | tinyint(1)          | NO   |     | 0                   |   0=  |
    | subventionR2     | tinyint(1)          | NO   |     | 0                   |   0=  |
    | demandeR1        | tinyint(1)          | NO   |     | 1                   |   1=  |
    | demandeR2        | tinyint(1)          | NO   |     | 0                   |   C=r |
    | accordR1         | tinyint(1)          | NO   |     | 1                   |   N   |
    | accordR2         | tinyint(1)          | NO   |     | 1                   |   N   |
    | internet         | tinyint(1)          | NO   |     | 1                   |   1=  |
    | district         | tinyint(1)          | NO   |     | 0                   |   O   |
    | derogation       | tinyint(1)          | NO   |     | 0                   |   O=h |
    | dateDebut        | date                | NO   |     | NULL                |   N   |
    | dateFin          | date                | NO   |     | NULL                |   N   |
    | joursTransport   | tinyint(3) unsigned | NO   |     | 127                 |   O=cb|
    | subventionTaux   | int(3)              | NO   |     | 0                   |   O   |
    | tarifId          | int(11)             | NO   | MUL | 0                   |   C=  |
    | organismeId      | int(11)             | NO   |     | 0                   |   O   |
    | regimeId         | tinyint(1)          | NO   |     | 0                   |   O   |
    | motifDerogation  | text                | YES  |     | NULL                |   O=h |
    | motifRefusR1     | text                | YES  |     | NULL                |   N   |
    | motifRefusR2     | text                | YES  |     | NULL                |   N   |
    | commentaire      | text                | NO   |     | NULL                |   N=ta|
    +------------------+---------------------+------+-----+---------------------+-------+

##Indexes

    +------------+-----------------+--------------+-----------------+-----------+-------------+------+------------+
    | Non_unique | Key_name        | Seq_in_index | Column_name     | Collation | Cardinality | Null | Index_type |
    +------------+-----------------+--------------+-----------------+-----------+-------------+------+------------+
    |          0 | PRIMARY         |            1 | millesime       | A         |           4 |      | BTREE      |
    |          0 | PRIMARY         |            2 | eleveId         | A         |        2048 |      | BTREE      |
    |          1 | eleveId         |            1 | eleveId         | A         |         793 |      | BTREE      |
    |          1 | etablissementId |            1 | etablissementId | A         |          21 |      | BTREE      |
    |          1 | classeId        |            1 | classeId        | A         |          25 |      | BTREE      |
    |          1 | communeId       |            1 | communeId       | A         |           3 | YES  | BTREE      |
    |          1 | tarifId         |            1 | tarifId         | A         |           2 |      | BTREE      |
    +------------+-----------------+--------------+-----------------+-----------+-------------+------+------------+
    
##Foreign key
    
    +-------------------------+-----------------+----------------------|-----------------+---------+---------+
    | Key_constraint          | ForeignKey name | Référence table      | Référence field | Delete  | Update  |
    +-------------------------+-----------------+----------------------|-----------------+---------+---------+
    | sbm_t_scolarites_ibfk_1 | eleveId         | sbm_t_eleves         | eleveId         |         | Cascade |
    | sbm_t_scolarites_ibfk_2 | etablissementId | sbm_t_etablissements | etablissementId |         | Cascade |
    | sbm_t_scolarites_ibfk_3 | classeId        | sbm_t_classes        | classeId        |         | Cascade |
    | sbm_t_scolarites_ibfk_4 | communeId       | sbm_t_communes       | communeId       |         | Cascade |
    | sbm_t_scolarites_ibfk_5 | tarifId         | sbm_t_tarifs         | tarifId         |         | Cascade |
    +-------------------------+-----------------+----------------------|-----------------+---------+---------+
    
##Triggers

    +-----------------------+--------+------------------------------------------------------------------------------+
    | Trigger name          | Method | Code                                                                         |
    +-----------------------+--------+------------------------------------------------------------------------------+
    | scolarites_bi_history | INSERT | BEGIN                                                                        |
    |                       |        | INSERT INTO sbm_s_history (table_name, action, id_name, id_txt, dt, log)     |
    |                       |        | VALUES ('sbm_t_scolarites', 'insert', CONCAT_WS('|', 'millesime', 'eleveId'),|
    |                       |        | CONCAT_WS('|', NEW.millesime, NEW.eleveId), NOW(),                           |
    |                       |        | CONCAT_WS('|', NEW.selection, NEW.dateInscription, NEW.dateModification,     |
    |                       |        | NEW.etablissementId, NEW.classeId, NEW.chez, NEW.adresseL1, NEW.adresseL2,   |
    |                       |        | NEW.codePostal, NEW.communeId, NEW.x, NEW.y, NEW.distanceR1, NEW.distanceR2, |
    |                       |        | NEW.dateEtiquette, NEW.dateCarte, NEW.inscrit, NEW.gratuit, NEW.paiement,    |
    |                       |        | NEW.anneeComplete, NEW.subventionR1, NEW.subventionR2, NEW.demandeR1,        |
    |                       |        | NEW.demandeR2, NEW.accordR1, NEW.accordR2, NEW.internet, NEW.district,       |
    |                       |        | NEW.derogation, NEW.dateDebut, NEW.dateFin, NEW.joursTransport,              |
    |                       |        | NEW.subventionTaux, NEW.tarifId, NEW.regimeId, NEW.motifDerogation,          |
    |                       |        | NEW.motifRefusR1, NEW.motifRefusR2, NEW.commentaire));                       |
    |                       |        | END                                                                          |
    +-----------------------+--------+------------------------------------------------------------------------------+
    | scolarites_bu_history | UPDATE | BEGIN                                                                        |
    |                       |        | INSERT INTO sbm_s_history (table_name, action, id_name, id_txt, dt, log)     |
    |                       |        | VALUES ('sbm_t_scolarites', 'update', CONCAT_WS('|', 'millesime', 'eleveId'),|
    |                       |        | CONCAT_WS('|', OLD.millesime, OLD.eleveId), NOW(),                           |
    |                       |        | CONCAT_WS('|', OLD.selection, OLD.dateInscription, OLD.dateModification,     |
    |                       |        | OLD.etablissementId, OLD.classeId, OLD.chez, OLD.adresseL1, OLD.adresseL2,   |
    |                       |        | OLD.codePostal, OLD.communeId, OLD.x, OLD.y, OLD.distanceR1, OLD.distanceR2, |
    |                       |        | OLD.dateEtiquette, OLD.dateCarte, OLD.inscrit, OLD.gratuit, OLD.paiement,    |
    |                       |        | OLD.anneeComplete, OLD.subventionR1, OLD.subventionR2, NEW.demandeR1,        |
    |                       |        | OLD.demandeR2, OLD.accordR1, OLD.accordR2, OLD.internet, OLD.district,       |
    |                       |        | OLD.derogation, OLD.dateDebut, OLD.dateFin, OLD.joursTransport,              |
    |                       |        | OLD.subventionTaux, OLD.tarifId, OLD.regimeId, OLD.motifDerogation,          |
    |                       |        | OLD.motifRefusR1, OLD.motifRefusR2, OLD.commentaire));                       |
    |                       |        | END                                                                          |
    +-----------------------+--------+------------------------------------------------------------------------------+
    | scolarites_bd_history | DELETE | BEGIN                                                                        |
    |                       |        | INSERT INTO sbm_s_history (table_name, action, id_name, id_txt, dt, log)     |
    |                       |        | VALUES ('sbm_t_scolarites', 'delete', CONCAT_WS('|', 'millesime', 'eleveId'),|
    |                       |        | CONCAT_WS('|', OLD.millesime, OLD.eleveId), NOW(),                           |
    |                       |        | CONCAT_WS('|', OLD.selection, OLD.dateInscription, OLD.dateModification,     |
    |                       |        | OLD.etablissementId, OLD.classeId, OLD.chez, OLD.adresseL1, OLD.adresseL2,   |
    |                       |        | OLD.codePostal, OLD.communeId, OLD.x, OLD.y, OLD.distanceR1, OLD.distanceR2, |
    |                       |        | OLD.dateEtiquette, OLD.dateCarte, OLD.inscrit, OLD.gratuit, OLD.paiement,    |
    |                       |        | OLD.anneeComplete, OLD.subventionR1, OLD.subventionR2, NEW.demandeR1,        |
    |                       |        | OLD.demandeR2, OLD.accordR1, OLD.accordR2, OLD.internet, OLD.district,       |
    |                       |        | OLD.derogation, OLD.dateDebut, OLD.dateFin, OLD.joursTransport,              |
    |                       |        | OLD.subventionTaux, OLD.tarifId, OLD.regimeId, OLD.motifDerogation,          |
    |                       |        | OLD.motifRefusR1, OLD.motifRefusR2, OLD.commentaire));                       |
    |                       |        | END                                                                          |
    +-----------------------+--------+------------------------------------------------------------------------------+