<?php
/**
 * Structure de la vue `services`
 *
 *
 * @project sbm
 * @package SbmInstallation/db_design
 * @filesource vue.services.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 5 févr. 2014
 * @version 2014-1
 */

return array(
    'name' => 'services',
    'type' => 'vue',
    'drop' => true, // si true, un DROP TABLE IF EXISTS sera fait avant la création
    'edit_entity' => true,
    'structure'=> array(
        'fields' => array(
            array('field' => 'serviceId',),
            array('field' => 'nom',),
            array('field' => 'aliasCG'),
            array('field' => 'transporteurId',),
            array('field' => 'nbPlaces',),
            array('field' => 'surEtatCG',),
            array('field' => 'operateur'),
            array('field' => 'kmAVide'),
            array('field' => 'kmEnCharge')
        ),
        'from' => array(
            'table' => 'services', // obligatoire mais peut être une vue
            'type' => 'table', // optionnel, 'table' par défaut
            'alias' => 'ser', // optionnel
        ),
        'join' => array(
            array(
                'table' => 'transporteurs', // obligatoire mais peut être une vue
                'type' => 'table', // optionnel, 'table' par défaut
                'alias' => 'tra', // optionnel
                'relation' => 'tra.transporteurId = ser.transporteurId', // obligatoire
                'fields' => array(
                    array('field' => 'nom', 'alias' => 'transporteur',),
                ),
            ),
            array(
                'table' => 'communes', // obligatoire mais peut être une vue
                'type' => 'table', // optionnel, 'table' par défaut
                'alias' => 'com', // optionnel
                'relation' => 'com.communeId = tra.communeId', // obligatoire
                'fields' => array(
                    array('field' => 'nom', 'alias' => 'communeTransporteur',),
                ),
            ),
        ),
        'order' => array('serviceId')
    ),
);