<?php
/**
 * Description courte du fichier
 *
 * Description longue du fichier s'il y en a une
 * 
 * @project project_name
 * @package package_name
 * @filesource table.staffectations.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 12 oct. 2014
 * @version 2014-1
 */
return array(
    'name' => 'affectations',
    'type' => 'table',
    'drop' => false,
    'edit_entity' => false,
    'add_data' => false,
    'structure' => array(
        'fields' => array(
            'eleveId' => 'int(11) NOT NULL DEFAULT "0"',
            'millesime' => 'int(4) NOT NULL DEFAULT "0"',
            'trajet' => 'tinyint(1) NOT NULL DEFAULT "1"', // 1 pour le responsable 1, 2 pour le responsable 2
            'jours' => 'tinyint(1) NOT NULL DEFAULT "1"', // semaine, mercredi ou samedi
            'correspondance' => 'tinyint(1) NOT NULL DEFAULT "1"', // de 1 à n à partir du domicile
            'sens' => 'tinyint(1) NOT NULL DEFAULT "1"', // 1 pour aller / 2 pour retour
            'responsableId' => 'int(11) NOT NULL',
            'stationId' => 'int(11) NOT NULL',
            'serviceId' => 'varchar(11) NOT NULL'
        ),
        'primary_key' => array(
            'millesime',  'eleveId', 'trajet', 'jours', 'correspondance'
        ),
        'engine' => 'InnoDB',
        'charset' => 'utf8',
        'collate' => 'utf8_unicode_ci'
    ),
    'data' => include __DIR__ . '/data/data.affectations.php'
); 