<?php
/**
 * Description de la table d'historique de l'application
 *
 * Cette table sera remplie par des triggers
 * 
 * @project sbm
 * @package SbmInstallation/db_design
 * @filesource system.history.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 23 oct. 2014
 * @version 2014-1
 */
return array(
    'name' => 'history',
    'type' => 'system',
    'drop' => false,
    'edit_entity' => false,
    'add_data' => false,
    'structure' => array(
        'fields' => array(
            'table_name' => 'varchar(32) DEFAULT NULL',
            'action' => 'char(6) NOT NULL',
            'id_name' => 'varchar(64) DEFAULT NULL',
            'id_int' => 'int(11) NOT NULL DEFAULT 0',
            'id_txt' => 'varchar(25) DEFAULT NULL', // calculé pour affectations
            'dt' => 'datetime NOT NULL',
            'log' => 'text'
        ),
        'keys' => array(
            'HISTORY_Table' => array(
                'unique' => false,
                'fields' => array(
                    'table_name',
                    'dt'
                )
            ),
            'HISTORY_Table_IndexInt' => array(
                'unique' => false,
                'fields' => array(
                    'table_name',
                    'id_name',
                    'id_int',
                    'dt'
                )
            ),
            'HISTORY_Table_IndexTxt' => array(
                'unique' => false,
                'fields' => array(
                    'table_name',
                    'id_name',
                    'id_txt',
                    'dt'
                )
            )
        ),
        'engine' => 'MyISAM',
        'charset' => 'utf8',
        'collate' => 'utf8_unicode_ci'
    )
);