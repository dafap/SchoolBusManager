<?php
/**
 * Structure de la vue `transporteurs`
 *
 *
 * @project sbm
 * @package module/SbmInstallation/config/db_design
 * @filesource vue.transporteurs.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 5 févr. 2014
 * @version 2014-1
 */

return array(
    'name' => 'transporteurs',
    'type' => 'vue',
    'drop' => true, // si true, un DROP TABLE IF EXISTS sera fait avant la création
    'edit_entity' => true,
    'structure'=> array(
        'fields' => array(
            array('field' => 'transporteurId',),
            array('field' => 'nom',),
            array('field' => 'adresse1',),
            array('field' => 'adresse2',),
            array('field' => 'codePostal',),
            array('field' => 'communeId',),
            array('field' => 'telephone',),
            array('field' => 'fax',),
            array('field' => 'email',),
            array('field' => 'siret',),
            array('field' => 'naf',),
            array('field' => 'rib_titulaire',),
            array('field' => 'rib_domiciliation',),
            array('field' => 'rib_bic',),
            array('field' => 'rib_iban',),
        ),
        'from' => array(
            'table' => 'transporteurs', // obligatoire mais peut être une vue
            'type' => 'table', // optionnel, 'table' par défaut
            'alias' => 'tra', // optionnel
        ),
        'join' => array(
            array(
                'table' => 'communes', // obligatoire mais peut être une vue
                'type' => 'table', // optionnel, 'table' par défaut
                'alias' => 'com', // optionnel
                'relation' => 'com.communeId = tra.communeId', // obligatoire
                'fields' => array(
                    array('field' => 'nom', 'alias' => 'commune',),
                ),
            ),
        ),
    ),
);