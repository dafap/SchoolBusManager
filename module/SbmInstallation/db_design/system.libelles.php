<?php
/**
 * Description de la table de libelles de l'application
 *
 * Ces libelles sont codés dans les tables de l'application.
 * Le champ `nature` désigne la nature de l'information (notation CamelCase)
 * Le champ `code`indique le code du libellé. 
 * La clé primaire est constituée des champs `nature` et `code`
 * Le champ `libelle` contient le libellé. Il peut être modifié.
 * Le champ `ouvert`est un booléen indiquant si ce libellé est utilisé dans cette application. C'est le seul élément à paramétrer. 
 * Les listes déroulantes ne présentent que les libellés ouverts.
 * 
 * @project sbm
 * @package SbmInstallation/db_design
 * @filesource system.libelles.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 24 fév. 2019
 * @version 2019-2.5.0
 */
use SbmBase\Model\StdLib;

return [
    'name' => 'libelles',
    'type' => 'system',
    'drop' => false,
    'edit_entity' => false,
    'add_data' => false,
    'structure' => [
        'fields' => [
            'nature' => 'varchar(20) NOT NULL',
            'code' => 'int(11) NOT NULL DEFAULT "1"',
            'libelle' => 'text NOT NULL',
            'ouvert' => 'tinyint(1) NOT NULL DEFAULT "1"'
        ],
        'primary_key' => [
            'nature',
            'code'
        ],
        'engine' => 'MyISAM',
        'charset' => 'utf8',
        'collate' => 'utf8_unicode_ci'
    ],
    'data' => StdLib::concatPath(StdLib::findParentPath(__DIR__, 'data/data'),
        'data.system.libelles.php')
];