#Table affectations

##Structure

    +--------------------------+--------------+------+-----+------------------+----------------+
    | Field                    | Type         | Null | Key | Default          | Extra          |
    +--------------------------+--------------+------+-----+------------------+----------------+
    | documentId               | int(11)      | NO   | PRI | NULL             | auto_increment |
    | type                     | char(3)      | NO   |     | pdf              |                |
    | disposition              | varchar(12)  | NO   |     | Tabulaire        |                |
    | name                     | varchar(32)  | NO   |     | NULL             |                |
    | out_mode                 | varchar(2)   | NO   |     | I                |                |
    | out_name                 | varchar(32)  | YES  |     | document-sbm.pdf |                |
    | recordSource             | text         | NO   |     | NULL             |                |
    | recordSourceType         | char(1)      | NO   |     | T                |                |
    | filter                   | text         | YES  |     | NULL             |                |
    | orderBy                  | varchar(255) | YES  |     | NULL             |                |
    | url_path_images          | varchar(64)  | NO   |     | /public/img/     |                |
    | image_blank              | varchar(255) | NO   |     | _blank.png       |                |
    | docheader                | tinyint(1)   | NO   |     | 0                |                |
    | docfooter                | tinyint(1)   | NO   |     | 0                |                |
    | pageheader               | tinyint(1)   | NO   |     | 0                |                |
    | pagefooter               | tinyint(1)   | NO   |     | 0                |                |
    | creator                  | varchar(255) | NO   |     | SchoolBusManager |                |
    | author                   | varchar(255) | NO   |     |                  |                |
    | title                    | varchar(255) | NO   |     |                  |                |
    | subject                  | varchar(255) | NO   |     |                  |                |
    | keywords                 | varchar(255) | NO   |     |                  |                |
    | docheader_subtitle       | text         | YES  |     | NULL             |                |
    | docheader_page_distincte | tinyint(1)   | NO   |     | 1                |                |
    | docheader_margin         | int(11)      | NO   |     | 20               |                |
    | docheader_pageheader     | tinyint(1)   | NO   |     | 0                |                |
    | docheader_pagefooter     | tinyint(1)   | NO   |     | 0                |                |
    | docheader_templateId     | int(11)      | NO   |     | 1                |                |
    | docfooter_title          | varchar(255) | NO   |     |                  |                |
    | docfooter_string         | text         | YES  |     | NULL             |                |
    | docfooter_page_distincte | tinyint(1)   | NO   |     | 1                |                |
    | docfooter_insecable      | tinyint(1)   | NO   |     | 1                |                |
    | docfooter_margin         | int(11)      | NO   |     | 20               |                |
    | docfooter_pageheader     | tinyint(1)   | NO   |     | 0                |                |
    | docfooter_pagefooter     | tinyint(1)   | NO   |     | 0                |                |
    | docfooter_templateId     | int(11)      | NO   |     | 1                |                |
    | pageheader_templateId    | int(11)      | NO   |     | 1                |                |
    | pageheader_title         | varchar(255) | NO   |     |                  |                |
    | pageheader_string        | text         | YES  |     | NULL             |                |
    | pageheader_logo_visible  | tinyint(1)   | NO   |     | 1                |                |
    | pageheader_logo          | varchar(255) | NO   |     | sbm-logo.gif     |                |
    | pageheader_logo_width    | int(11)      | NO   |     | 15               |                |
    | pageheader_margin        | int(11)      | NO   |     | 5                |                |
    | pageheader_font_family   | varchar(64)  | NO   |     | helvetica        |                |
    | pageheader_font_style    | char(2)      | NO   |     |                  |                |
    | pageheader_font_size     | int(11)      | NO   |     | 11               |                |
    | pageheader_text_color    | varchar(20)  | NO   |     | 000000           |                |
    | pageheader_line_color    | varchar(20)  | NO   |     | 000000           |                |
    | pagefooter_templateId    | int(11)      | NO   |     | 1                |                |
    | pagefooter_margin        | int(11)      | NO   |     | 10               |                |
    | pagefooter_string        | text         | YES  |     | NULL             |                |
    | pagefooter_font_family   | varchar(64)  | NO   |     | helvetica        |                |
    | pagefooter_font_style    | char(2)      | NO   |     |                  |                |
    | pagefooter_font_size     | int(11)      | NO   |     | 11               |                |
    | pagefooter_text_color    | varchar(20)  | NO   |     | 000000           |                |
    | pagefooter_line_color    | varchar(20)  | NO   |     | 000000           |                |
    | page_templateId          | int(11)      | NO   |     | 1                |                |
    | page_format              | varchar(30)  | NO   |     | A4               |                |
    | page_orientation         | varchar(1)   | NO   |     | P                |                |
    | page_margin_top          | int(11)      | NO   |     | 27               |                |
    | page_margin_bottom       | int(11)      | NO   |     | 25               |                |
    | page_margin_left         | int(11)      | NO   |     | 15               |                |
    | page_margin_right        | int(11)      | NO   |     | 15               |                |
    | main_font_family         | varchar(64)  | NO   |     | helvetica        |                |
    | main_font_style          | char(2)      | NO   |     |                  |                |
    | main_font_size           | int(11)      | NO   |     | 11               |                |
    | data_font_family         | varchar(64)  | NO   |     | helvetica        |                |
    | data_font_style          | char(2)      | NO   |     |                  |                |
    | data_font_size           | int(11)      | NO   |     | 8                |                |
    | titre1_font_family       | varchar(64)  | NO   |     | helvetica        |                |
    | titre1_font_style        | char(2)      | NO   |     |                  |                |
    | titre1_font_size         | int(11)      | NO   |     | 14               |                |
    | titre1_text_color        | varchar(20)  | NO   |     | 000000           |                |
    | titre1_line              | tinyint(1)   | NO   |     | 0                |                |
    | titre1_line_color        | varchar(20)  | NO   |     | 000000           |                |
    | titre2_font_family       | varchar(64)  | NO   |     | helvetica        |                |
    | titre2_font_style        | char(2)      | NO   |     |                  |                |
    | titre2_font_size         | int(11)      | NO   |     | 13               |                |
    | titre2_text_color        | varchar(20)  | NO   |     | 000000           |                |
    | titre2_line              | tinyint(1)   | NO   |     | 0                |                |
    | titre2_line_color        | varchar(20)  | NO   |     | 000000           |                |
    | titre3_font_family       | varchar(64)  | NO   |     | helvetica        |                |
    | titre3_font_style        | char(2)      | NO   |     |                  |                |
    | titre3_font_size         | int(11)      | NO   |     | 12               |                |
    | titre3_text_color        | varchar(20)  | NO   |     | 000000           |                |
    | titre3_line              | tinyint(1)   | NO   |     | 0                |                |
    | titre3_line_color        | varchar(20)  | NO   |     | 000000           |                |
    | titre4_font_family       | varchar(64)  | NO   |     | helvetica        |                |
    | titre4_font_style        | char(2)      | NO   |     |                  |                |
    | titre4_font_size         | int(11)      | NO   |     | 11               |                |
    | titre4_text_color        | varchar(20)  | NO   |     | 000000           |                |
    | titre4_line              | tinyint(1)   | NO   |     | 0                |                |
    | titre4_line_color        | varchar(20)  | NO   |     | 000000           |                |
    | default_font_monospaced  | varchar(64)  | NO   |     | courier          |                |
    +--------------------------+--------------+------+-----+------------------+----------------+

##Indexes

    +------------+----------+--------------+-------------+-----------+-------------+------+------------+
    | Non_unique | Key_name | Seq_in_index | Column_name | Collation | Cardinality | Null | Index_type |
    +------------+----------+--------------+-------------+-----------+-------------+------+------------+
    |          0 | PRIMARY  |            1 | documentId  | A         |          41 |      | BTREE      |
    +------------+----------+--------------+-------------+-----------+-------------+------+------------+

##Foreign keys

_Pas de foreign key._

##Triggers

_Pas de trigger._

