<?php
/**
 * Structure de la vue `etablissements`
 *
 *
 * @project sbm
 * @package SbmInstallation/db_design
 * @filesource vue.etablissements.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 5 févr. 2014
 * @version 2014-1
 */

return array(
    'name' => 'etablissements',
    'type' => 'vue',
    'drop' => true, // si true, un DROP TABLE IF EXISTS sera fait avant la création
    'edit_entity' => true,
    'structure'=> array(
        'fields' => array(
            array('field' => 'etablissementId',),
            array('field' => 'nom',),
            array('field' => 'alias',),
            array('field' => 'aliasCG'),
            array('field' => 'adresse1',),
            array('field' => 'adresse2',),
            array('field' => 'codePostal',),
            array('field' => 'communeId',),
            array('field' => 'longitude',),
            array('field' => 'latitude',),
            array('field' => 'niveau',),
            array('field' => 'statut',),
            array('field' => 'visible',),
            array('field' => 'regrPeda',),
            array('field' => 'rattacheA',),
            array('field' => 'telephone',),
            array('field' => 'fax',),
            array('field' => 'email',),
            array('field' => 'directeur',),
            array('field' => 'hMatin',),
            array('field' => 'hMidi',),
            array('field' => 'hAMidi',),
            array('field' => 'hSoir',),
            array('field' => 'jOuverture',),
        ),
        'from' => array(
            'table' => 'etablissements', // obligatoire mais peut être une vue
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