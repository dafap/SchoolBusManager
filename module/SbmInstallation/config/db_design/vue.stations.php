<?php
/**
 * Structure de la vue `stations`
 *
 *
 * @project sbm
 * @package module/SbmInstallation/config/db_design
 * @filesource vue.stations.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 5 févr. 2014
 * @version 2014-1
 */

return array(
    'name' => 'stations',
    'drop' => true, // si true, un DROP TABLE IF EXISTS sera fait avant la création
    'type' => 'vue',
    'structure'=> array(
        'fields' => array(
            array('field' => 'stationId',),
            array('field' => 'communeId',),
            array('field' => 'nom',),
            array('field' => 'aliasCG'),
            array('field' => 'codeCG',),
            array('field' => 'longitude',),
            array('field' => 'latitude',),
            array('field' => 'visible',),
            array('field' => 'ouverte',),
        ),
        'from' => array(
            'table' => 'stations', // obligatoire mais peut être une vue
            'type' => 'table', // optionnel, 'table' par défaut
            'alias' => 'eta', // optionnel
        ),
        'join' => array(
            array(
                'table' => 'communes', // obligatoire mais peut être une vue
                'type' => 'table', // optionnel, 'table' par défaut
                'alias' => 'com', // optionnel
                'relation' => 'com.communeId = eta.communeId', // obligatoire
                'fields' => array(
                    array('field' => 'nom', 'alias' => 'commune',),
                ),
            ),
        ),
    ),
);