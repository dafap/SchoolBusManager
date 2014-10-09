<?php
/**
 * Structure de la vue `responsables`
 *
 *
 * @project sbm
 * @package module/SbmInstallation/config/db_design
 * @filesource vue.eleve.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 18 juil. 2014
 * @version 2014-1
 */
return array(
    'name' => 'responsables',
    'drop' => true, // si true, un DROP TABLE IF EXISTS sera fait avant la création
    'type' => 'vue',
    'structure' => array(
        'fields' => array(
            array(
                'field' => 'responsableId'
            ),
            array(
                'field' => 'titre'
            ),
            array(
                'field' => 'nom'
            ),
            array(
                'field' => 'nomSA'
            ),
            array(
                'field' => 'prenom'
            ),
            array(
                'field' => 'prenomSA'
            ),
            array(
                'field' => 'adressL1'
            ),
            array(
                'field' => 'adressL2'
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
                'field' => 'telephoneC'
            ),
            array(
                'field' => 'email'
            ),
            array(
                'field' => 'ancienAdressL1'
            ),
            array(
                'field' => 'ancienAdressL2'
            ),
            array(
                'field' => 'ancienCodePostal'
            ),
            array(
                'field' => 'ancienCommuneId'
            ),
            array(
                'field' => 'dateDemenagement'
            ),
            array(
                'field' => 'dateCreation'
            ),
            array(
                'field' => 'dateModification'
            ),
            array(
                'field' => 'demenagement'
            ),
            array(
                'field' => 'facture'
            ),
            array(
                'field' => 'grilleTarif'
            ),
            array(
                'field' => 'selection'
            )
        ),
        'from' => array(
            'table' => 'responsables', // obligatoire mais peut être une vue
            'type' => 'table', // optionnel, 'table' par défaut
            'alias' => 'res' // optionnel
                ),
        'join' => array(
            array(
                'table' => 'communes', // obligatoire mais peut être une vue
                'type' => 'table', // optionnel, 'table' par défaut
                'alias' => 'com', // optionnel
                'relation' => 'res.communeId = com.communeId', // obligatoire
                'fields' => array(
                    array(
                        'field' => 'nom',
                        'alias' => 'commune'
                    )
                )
            ),
            array(
                'table' => 'eleves', // obligatoire mais peut être une vue
                'type' => 'table', // optionnel, 'table' par défaut
                'alias' => 'ele', // optionnel
                'relation' => 'res.responsableId = ele.respId1 OR res.responsableId = ele.respId2 OR res.responsableId = ele.factId ', // obligatoire
                'fields' => array(
                    array(
                        'expression' => array(
                            'value' => 'count(ele.eleveId)',
                            'type' => 'bigint(21)'
                        ),
                        'alias' => 'nbEleves'
                    )
                )
            )
        ),
        'group' => array(
            array(
                'table' => 'res',
                'field' => 'responsableId'
            )
        )
    )
);