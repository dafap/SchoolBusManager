<?php
/**
 * Structure de la table des `services`
 *
 *
 * @project sbm
 * @package module/SbmInstallation/config/db_design
 * @filesource table.services.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 5 févr. 2014
 * @version 2014-1
 */

return array(
    'name' => 'services',
    'drop' => false,
    'edit_entity' => false,
    'add_data' => false,
    'type' => 'table',
    'structure'=> array(
        'fields' => array(
            'serviceId' => 'varchar(11) NOT NULL',
            'nom' => 'varchar(45) NOT NULL',
            'aliasCG' => 'varchar(15) NOT NULL DEFAULT ""',
            'transporteurId' => 'int(11) NOT NULL DEFAULT "0"',
            'nbPlaces' => 'tinyint(3) unsigned NOT NULL DEFAULT "0"',
            'surEtatCG' => 'tinyint(1) NOT NULL DEFAULT "1"',
        ),
        'primary_key' => array('serviceId',),
        'engine' => 'MyISAM',
        'charset' => 'utf8',
        'collate' => 'utf8_unicode_ci',
    ),
    'data' => include __DIR__ . '/data/data.services.php',
);