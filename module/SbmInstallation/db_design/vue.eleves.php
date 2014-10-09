<?php
/**
 * Structure de la vue `eleves`
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
    'name' => 'eleves',
    'drop' => true, // si true, un DROP TABLE IF EXISTS sera fait avant la création
    'type' => 'vue',
    'structure' => array(
        'fields' => array(
            array(
                'field' => 'eleveId'
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
                'field' => 'dateN'
            ),
            array(
                'field' => 'adress1L1'
            ),
            array(
                'field' => 'adress1L2'
            ),
            array(
                'field' => 'codePostal1'
            ),
            array(
                'field' => 'serviceId1',
                'alias' => 'service1'
            ),
            array(
                'field' => 'respId1'
            ),
            array(
                'field' => 'secondeAdresse'
            ),
            array(
                'field' => 'selection'
            )
        ),
        'from' => array(
            'table' => 'eleves', // obligatoire mais peut être une vue
            'type' => 'table', // optionnel, 'table' par défaut
            'alias' => 'ele' // optionnel
                ),
        'join' => array(
            array(
                'table' => 'etablissements', // obligatoire mais peut être une vue
                'type' => 'table', // optionnel, 'table' par défaut
                'alias' => 'eta', // optionnel
                'relation' => 'ele.etablissementId = eta.etablissementId', // obligatoire
                'fields' => array(
                    array(
                        'field' => 'nom',
                        'alias' => 'etablissement'
                    )
                )
            ),
            array(
                'table' => 'tarifs', // obligatoire mais peut être une vue
                'type' => 'table', // optionnel, 'table' par défaut
                'alias' => 'tar', // optionnel
                'relation' => 'ele.tarifId = tar.tarifId', // obligatoire
                'fields' => array(
                    array(
                        'field' => 'nom',
                        'alias' => 'tarif'
                    ),
                    array(
                        'field' => 'montant',
                        'alias' => 'tarifMontant'
                    )
                )
            ),
            array(
                'table' => 'communes', // obligatoire mais peut être une vue
                'type' => 'table', // optionnel, 'table' par défaut
                'alias' => 'com', // optionnel
                'relation' => 'ele.communeId1 = com.communeId', // obligatoire
                'fields' => array(
                    array(
                        'field' => 'nom',
                        'alias' => 'commune1'
                    )
                )
            ),
            array(
                'table' => 'stations', // obligatoire mais peut être une vue
                'type' => 'table', // optionnel, 'table' par défaut
                'alias' => 'sta', // optionnel
                'relation' => 'ele.stationId1 = sta.stationId', // obligatoire
                'fields' => array(
                    array(
                        'field' => 'nom',
                        'alias' => 'station1'
                    )
                )
            ),
            array(
                'table' => 'responsables',
                'type' => 'table',
                'alias' => 'res',
                'relation' => 'ele.respId1 = res.responsableId',
                'fields' => array(
                    array(
                        'field' => 'nom',
                        'alias' => 'nomR1'
                    ),
                    array(
                        'field' => 'prenom',
                        'alias' => 'prenomR1'
                    )
                )
            )
        )
    )
);