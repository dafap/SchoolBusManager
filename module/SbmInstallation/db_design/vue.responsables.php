<?php
/**
 * Structure de la vue `responsables`
 *
 *
 * @project sbm
 * @package SbmInstallation/db_design
 * @filesource vue.eleve.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 18 juil. 2014
 * @version 2014-1
 */
return array(
    'name' => 'responsables',
    'drop' => true, // si true, un DROP TABLE IF EXISTS sera fait avant la création
    'edit_entity' => true,
    'type' => 'vue',
    'structure' => array(
        'fields' => array(
            array(
                'field' => 'responsableId'
            ),
            array(
                'field' => 'selection'
            ),
            array(
                'field' => 'dateCreation'
            ),
            array(
                'field' => 'dateModification'
            ),
            array(
                'field' => 'nature'
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
                'field' => 'titre2'
            ),
            array(
                'field' => 'nom2'
            ),
            array(
                'field' => 'nom2SA'
            ),
            array(
                'field' => 'prenom2'
            ),
            array(
                'field' => 'prenom2SA'
            ),
            array(
                'field' => 'adresseL1'
            ),
            array(
                'field' => 'adresseL2'
            ),
            array(
                'field' => 'codePostal'
            ),
            array(
                'field' => 'communeId'
            ),
            array(
                'field' => 'ancienAdresseL1'
            ),
            array(
                'field' => 'ancienAdresseL2'
            ),
            array(
                'field' => 'ancienCodePostal'
            ),
            array(
                'field' => 'ancienCommuneId'
            ),
            array(
                'field' => 'email'
            ),
            array(
                'field' => 'telephoneF'
            ),
            array(
                'field' => 'telephoneP'
            ),
            array(
                'field' => 'telephoneT'
            ),
            array(
                'field' => 'etiquette'
            ),
            array(
                'field' => 'demenagement'
            ),
            array(
                'field' => 'dateDemenagement'
            ),
            array(
                'field' => 'facture',
            ),
            array(
                'field' => 'grilleTarif'
            ),
            array(
                'field' => 'ribTit'
            ),
            array(
                'field' => 'ribDom'
            ),
            array(
                'field' => 'iban'
            ),
            array(
                'field' => 'bic'
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
                'relation' => 'res.responsableId = ele.responsable1Id Or res.responsableId = ele.responsable2Id Or res.responsableId = ele.responsableFId', // obligatoire
                'fields' => array(
                    array(
                        'expression' => array(
                            'value' => 'count(ele.eleveId)',
                            'type' => 'bigint(21)'
                        ),
                        'alias' => 'nbEleves'
                    )
                ),
                'jointure' => \Zend\Db\Sql\Select::JOIN_LEFT
            )
        ),
        'group' => array(
            array(
                'table' => 'res',
                'field' => 'responsableId'
            )
        ),
        'order' => array('nomSA', 'prenomSA', 'commune')
    )
);