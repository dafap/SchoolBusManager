<?php
/**
 * Structure de la vue `organismes`
 *
 *
 * @project sbm
 * @package SbmInstallation/db_design
 * @filesource vue.organismes.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 6 oct. 2015
 * @version 2015-1
 */
return array(
    'name' => 'organismes',
    'type' => 'vue',
    'drop' => true, // si true, un DROP TABLE IF EXISTS sera fait avant la création
    'edit_entity' => true,
    'structure' => array(
        'fields' => array(
            array(
                'field' => 'organismeId'
            ),
            array(
                'field' => 'selection'
            ),
            array(
                'field' => 'nom'
            ),
            array(
                'field' => 'adresse1'
            ),
            array(
                'field' => 'adresse2'
            ),
            array(
                'field' => 'codePostal'
            ),
            array(
                'field' => 'communeId'
            ),
            array(
                'field' => 'telephone'
            ),
            array(
                'field' => 'fax'
            ),
            array(
                'field' => 'email'
            ),
            array(
                'field' => 'siret'
            ),
            array(
                'field' => 'naf'
            ),
        ),
        'from' => array(
            'table' => 'organismes', // obligatoire mais peut être une vue
            'type' => 'table', // optionnel, 'table' par défaut
            'alias' => 'org'
        ) // optionnel
,
        'join' => array(
            array(
                'table' => 'communes', // obligatoire mais peut être une vue
                'type' => 'table', // optionnel, 'table' par défaut
                'alias' => 'com', // optionnel
                'relation' => 'com.communeId = org.communeId', // obligatoire
                'fields' => array(
                    array(
                        'field' => 'nom',
                        'alias' => 'commune'
                    )
                )
            )
        ),
        'order' => 'nom'
    )
);