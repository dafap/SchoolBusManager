<?php
/**
 * Description des champs utilisés dans les tables (table `calendar`)
 *
 * Cette table définit les années scolaires, les périodes de l'année, les périodes de facturation, les périodes de prélèvement, les vacances scolaires
 * 
 * @project sbm
 * @package SbmInstallation/db_design
 * @filesource system.calendar.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 4 nov. 2014
 * @version 2014-1
 */
return array(
    'name' => 'calendar',
    'type' => 'system',
    'drop' => false,
    'edit_entity' => false,
    'add_data' => false,
    'structure' => array(
        'fields' => array(
            'calendarId' => 'int(11) NOT NULL AUTO_INCREMENT',
            'millesime' => 'int(4) NOT NULL',
            'ordinal' => 'tinyint(3) NOT NULL',
            'nature' => 'varchar(4) NOT NULL',
            'rang' => 'tinyint(3) NOT NULL DEFAULT "1"',
            'libelle' => 'varchar(30) NOT NULL',
            'description' => 'varchar(255) NOT NULL',
            'dateDebut' => 'date',
            'dateFin' => 'date',
            'echeance' => 'date',
            'exercice' => 'int(4) NOT NULL DEFAULT "0"'
        ),
        'primary_key' => array(
            'calendarId'
        ),
        'keys' => array(
            'millesime-ordinal' => array(
                'unique' => true,
                'fields' => array(
                    'millesime',
                    'ordinal'
                )
            ),
            'millesime-nature' => array(
                'unique' => true,
                'fields' => array(
                    'millesime',
                    'nature',
                    'rang'
                )
            )
        ),
        'engine' => 'MyISAM',
        'charset' => 'utf8',
        'collate' => 'utf8_unicode_ci'
    ),
    'data' => __DIR__ . '/data/data.system.calendar.php'
);