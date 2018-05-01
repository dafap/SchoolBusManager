#Table systempay

##Structure

    +--------------------------+---------------------+------+-----+---------+----------------+
    | Field                    | Type                | Null | Key | Default | Extra          |
    +--------------------------+---------------------+------+-----+---------+----------------+
    | systempayId              | int(11)             | NO   | PRI | NULL    | auto_increment |
    | selection                | tinyint(1)          | NO   |     | 0       |                |
    | vads_ctx_mode            | varchar(10)         | NO   |     | NULL    |                |
    | vads_operation_type      | varchar(6)          | NO   |     | NULL    |                |
    | vads_trans_date          | char(14)            | NO   | MUL | NULL    |                |
    | vads_trans_id            | char(6)             | NO   |     | NULL    |                |
    | vads_trans_status        | varchar(32)         | NO   |     | NULL    |                |
    | vads_result              | tinyint(2) unsigned | NO   |     | 0       |                |
    | vads_extra_result        | tinyint(3) unsigned | NO   |     | 0       |                |
    | vads_auth_result         | tinyint(3) unsigned | NO   |     | 255     |                |
    | vads_auth_number         | char(6)             | NO   |     |         |                |
    | vads_cust_email          | char(150)           | YES  |     | NULL    |                |
    | vads_cust_id             | int(11)             | NO   | MUL | NULL    |                |
    | vads_cust_last_name      | varchar(30)         | NO   |     | NULL    |                |
    | vads_cust_name           | varchar(30)         | NO   |     | NULL    |                |
    | vads_order_id            | varchar(32)         | NO   |     | NULL    |                |
    | ref_eleveIds             | varchar(255)        | YES  |     | NULL    |                |
    | vads_payment_certificate | varchar(40)         | NO   |     |         |                |
    | vads_payment_config      | varchar(255)        | YES  |     | NULL    |                |
    | vads_payment_error       | tinyint(3) unsigned | NO   |     | 0       |                |
    | vads_sequence_number     | tinyint(3) unsigned | NO   |     | 1       |                |
    | vads_capture_delay       | tinyint(3) unsigned | NO   |     | 0       |                |
    | vads_amount              | int(11)             | NO   |     | 0       |                |
    | vads_currency            | char(3)             | YES  |     | 978     |                |
    | vads_threeds_enrolled    | char(1)             | YES  |     | U       |                |
    | vads_threeds_status      | char(1)             | YES  |     | U       |                |
    | vads_card_brand          | varchar(127)        | YES  |     | NULL    |                |
    | vads_card_country        | char(2)             | YES  |     | NULL    |                |
    | vads_card_number         | varchar(36)         | YES  |     | NULL    |                |
    | vads_expiry_month        | char(2)             | YES  |     | NULL    |                |
    | vads_expiry_year         | char(4)             | YES  |     | NULL    |                |
    | vads_bank_code           | char(5)             | YES  |     | NULL    |                |
    | vads_bank_product        | varchar(3)          | YES  |     | NULL    |                |
    +--------------------------+---------------------+------+-----+---------+----------------+

##Indexes

    +------------+-------------------+--------------+-----------------+-----------+-------------+------+------------+
    | Non_unique | Key_name          | Seq_in_index | Column_name     | Collation | Cardinality | Null | Index_type |
    +------------+-------------------+--------------+-----------------+-----------+-------------+------+------------+
    |          0 | PRIMARY           |            1 | systempayId     | A         |         336 |      | BTREE      |
    |          0 | SYSTEMPAY_date_id |            1 | vads_trans_date | A         |         336 |      | BTREE      |
    |          0 | SYSTEMPAY_date_id |            2 | vads_trans_id   | A         |         336 |      | BTREE      |
    |          1 | SYSTEMPAY_cust_id |            1 | vads_cust_id    | A         |         180 |      | BTREE      |
    +------------+-------------------+--------------+-----------------+-----------+-------------+------+------------+

##Foreign keys

_Pas de foreign key._

##Triggers

_Pas de trigger._

