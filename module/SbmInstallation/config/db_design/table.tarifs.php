<?php
/**
 * Structure de la table des `tarifs`
 *
 *
 * @project sbm
 * @package module/SbmInstallation/config/db_design
 * @filesource table.tarifs.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 5 févr. 2014
 * @version 2014-1
 */

return array(
    'name' => 'tarifs',
    'drop' => false,
    'edit_entity' => false,
    'add_data' => false,
    'type' => 'table',
    'structure'=> array(
        'fields' => array(
            'tarifId' => 'int(11) NOT NULL AUTO_INCREMENT',
            'montant' => 'decimal(10,2) NOT NULL DEFAULT "0.00"',
            'nom' => 'varchar(48) NOT NULL',
            'attributs' => 'int(4) NOT NULL DEFAULT "1041"',
        ),
        'primary_key' => array('tarifId',),
        'engine' => 'MyISAM',
        'charset' => 'utf8',
        'collate' => 'utf8_unicode_ci',
    ),
    'data' => include __DIR__ . '/data/data.tarifs.php',
);