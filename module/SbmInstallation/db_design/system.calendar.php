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
 * @date 24 fév. 2019
 * @version 2019-2.5.0
 */
use SbmBase\Model\StdLib;

return [
    'name' => 'calendar',
    'type' => 'system',
    'drop' => false,
    'edit_entity' => false,
    'add_data' => false,
    'structure' => [
        'fields' => [
            'calendarId' => 'int(11) NOT NULL AUTO_INCREMENT',
            'ouvert' => 'tinyint(1) NOT NULL DEFAULT "0"',
            'millesime' => 'int(4) NOT NULL',
            'ordinal' => 'tinyint(3) NOT NULL',
            'nature' => 'varchar(4) NOT NULL',
            'rang' => 'tinyint(3) NOT NULL DEFAULT "1"',
            'libelle' => 'varchar(64) NOT NULL',
            'description' => 'varchar(255) NOT NULL',
            'dateDebut' => 'date',
            'dateFin' => 'date',
            'echeance' => 'date',
            'date1' => 'date',
            'date2' => 'date',
            'date3' => 'date',
            'date4' => 'date',
            'exercice' => 'int(4) NOT NULL DEFAULT "0"'
        ],
        'primary_key' => [
            'calendarId'
        ],
        'keys' => [
            'millesime-ordinal' => [
                'unique' => true,
                'fields' => [
                    'millesime',
                    'ordinal'
                ]
            ],
            'millesime-nature' => [
                'unique' => true,
                'fields' => [
                    'millesime',
                    'nature',
                    'rang'
                ]
            ]
        ],
        'engine' => 'MyISAM',
        'charset' => 'utf8',
        'collate' => 'utf8_unicode_ci'
    ],
    'data' => StdLib::concatPath(StdLib::findParentPath(__DIR__, 'data/data'),
        'data.system.calendar.php')
];