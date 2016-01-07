<?php
/**
 * Structure de la vue `classes`
 * 
 * @project sbm
 * @package SbmInstallation/db_design
 * @filesource vue.classes.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 6 janv. 2016
 * @version 2016-1.7.1
 */

return array(
    'name' => 'classes',
    'type' => 'vue',
    'drop' => true, 
    'edit_entity' => true,
    'structure' => array(
        'fields' => array(
            array(
                'field' => 'classeId'
            ),
            array(
                'field' => 'selection'
            ),
            array(
                'field' => 'nom'
            ),
            array(
                'field' => 'aliasCG'
            ),
            array(
                'field' => 'niveau'
            ),
            array(
                'field' => 'suivantId'
            )
        ),
        'from' => array(
            'table' => 'classes', // obligatoire mais peut être une vue
            'type' => 'table', // optionnel, 'table' par défaut
            'alias' => 'cla'
        ) // optionnel
        ,
        'join' => array(
            array(
                'table' => 'classes', // obligatoire mais peut être une vue
                'type' => 'table', // optionnel, 'table' par défaut
                'alias' => 'sui', // optionnel
                'relation' => 'sui.classeId = cla.suivantId', // obligatoire
                'fields' => array(
                    array(
                        'field' => 'nom',
                        'alias' => 'suivant'
                    )
                ),
                'jointure' => \Zend\Db\Sql\Select::JOIN_LEFT
            )
        ),
        'order' => 'nom'
    )
); 