<?php
/**
 * Structure de la table des `systempay`
 *
 * @project sbm
 * @package SbmInstallation/db_design
 * @filesource table.systempay.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 24 fév. 2019
 * @version 2019-2.5.0
 */
use SbmBase\Model\StdLib;

return [
    'name' => 'systempay',
    'drop' => false,
    'edit_entity' => false,
    'add_data' => false,
    'type' => 'table',
    'structure' => [
        'fields' => [
            'systempayId' => 'int(11) NOT NULL AUTO_INCREMENT',
            'selection' => 'tinyint(1) NOT NULL DEFAULT "0"',
            'vads_ctx_mode' => 'varchar(10) NOT NULL',
            'vads_operation_type' => 'varchar(6) NOT NULL',
            'vads_trans_date' => 'char(14) NOT NULL',
            'vads_trans_id' => 'char(6) NOT NULL',
            'vads_trans_status' => 'varchar(32) NOT NULL',
            'vads_result' => 'tinyint(2) UNSIGNED NOT NULL DEFAULT "0"',
            'vads_extra_result' => 'tinyint(3) UNSIGNED NOT NULL DEFAULT "0"',
            'vads_auth_result' => 'tinyint(3) UNSIGNED NOT NULL DEFAULT "255"',
            'vads_auth_number' => 'char(6) NOT NULL DEFAULT "      "',
            'vads_cust_email' => 'char(150)',
            'vads_cust_id' => 'int(11) NOT NULL',
            'vads_cust_last_name' => 'varchar(30) NOT NULL',
            'vads_cust_name' => 'varchar(30) NOT NULL',
            'vads_order_id' => 'varchar(32) NOT NULL',
            'ref_eleveIds' => 'varchar(255)',
            'vads_payment_certificate' => 'varchar(40) NOT NULL DEFAULT ""',
            'vads_payment_config' => 'varchar(255)',
            'vads_payment_error' => 'tinyint UNSIGNED NOT NULL DEFAULT "0"',
            'vads_sequence_number' => 'tinyint UNSIGNED NOT NULL DEFAULT "1"',
            'vads_capture_delay' => 'tinyint UNSIGNED NOT NULL DEFAULT "0"',
            'vads_amount' => 'int(11) NOT NULL DEFAULT "0"',
            'vads_currency' => 'char(3) DEFAULT "978"',
            'vads_threeds_enrolled' => 'char(1) DEFAULT "U"',
            'vads_threeds_status' => 'char(1) DEFAULT "U"',
            'vads_card_brand' => 'varchar(127)',
            'vads_card_country' => 'char(2)',
            'vads_card_number' => 'varchar(36)',
            'vads_expiry_month' => 'char(2)',
            'vads_expiry_year' => 'char(4)',
            'vads_bank_code' => 'char(5)',
            'vads_bank_product' => 'varchar(3)'
        ],
        'primary_key' => [
            'systempayId'
        ],
        'keys' => [
            'SYSTEMPAY_date_id' => [
                'unique' => true,
                'fields' => [
                    'vads_trans_date',
                    'vads_trans_id'
                ]
            ],
            'SYSTEMPAY_cust_id' => [
                'fields' => [
                    'vads_cust_id'
                ]
            ]
        ],
        'engine' => 'InnoDb',
        'charset' => 'utf8',
        'collate' => 'utf8_unicode_ci'
    ],
    'data' => StdLib::concatPath(StdLib::findParentPath(__DIR__, 'data/data'),
        'data.systempay.php')
];

