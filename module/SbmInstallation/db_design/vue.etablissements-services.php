<?php
/**
 * Structure de la vue `etablissements-services`
 *
 * @project sbm
 * @package SbmInstallation/db_design
 * @filesource vue.etablissements-services.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 12 mars 2015
 * @version 2015-1
 */
return array(
    'name' => 'etablissements-services',
    'type' => 'vue',
    'drop' => true, // si true, un DROP TABLE IF EXISTS sera fait avant la création
    'edit_entity' => true,
    'structure' => array(
        'fields' => array(
            array(
                'field' => 'etablissementId'
            ),
            array(
                'field' => 'serviceId'
            ),
            array(
                'field' => 'stationId'
            )
        ),
        'from' => array(
            'table' => 'etablissements-services', // obligatoire mais peut être une vue
            'type' => 'table', // optionnel, 'table' par défaut
            'alias' => 'rel'
        ),
        'join' => array(
            array(
                'table' => 'etablissements', // obligatoire mais peut être une vue
                'type' => 'table', // optionnel, 'table' par défaut
                'alias' => 'eta',
                'relation' => 'rel.etablissementId = eta.etablissementId', // obligatoire
                'fields' => array(
                    array(
                        'field' => 'nom',
                        'alias' => 'etab_nom'
                    ),
                    array(
                        'field' => 'alias',
                        'alias' => 'etab_alias'
                    ),
                    array(
                        'field' => 'aliasCG',
                        'alias' => 'etab_aliasCG'
                    ),
                    array(
                        'field' => 'adresse1',
                        'alias' => 'etab_adresse1'
                    ),
                    array(
                        'field' => 'adresse2',
                        'alias' => 'etab_adresse2'
                    ),
                    array(
                        'field' => 'codePostal',
                        'alias' => 'etab_codePostal'
                    ),
                    array(
                        'field' => 'communeId',
                        'alias' => 'etab_communeId'
                    ),
                    array(
                        'field' => 'niveau',
                        'alias' => 'etab_niveau'
                    ),
                    array(
                        'field' => 'statut',
                        'alias' => 'etab_statut'
                    ),
                    array(
                        'field' => 'visible',
                        'alias' => 'etab_visible'
                    ),
                    array(
                        'field' => 'desservie',
                        'alias' => 'etab_desservie'
                    ),
                    array(
                        'field' => 'regrPeda',
                        'alias' => 'etab_regrPeda'
                    ),
                    array(
                        'field' => 'rattacheA',
                        'alias' => 'etab_rattacheA'
                    ),
                    array(
                        'field' => 'telephone',
                        'alias' => 'etab_telephone'
                    ),
                    array(
                        'field' => 'fax',
                        'alias' => 'etab_fax'
                    ),
                    array(
                        'field' => 'email',
                        'alias' => 'etab_email'
                    ),
                    array(
                        'field' => 'directeur',
                        'alias' => 'etab_directeur'
                    ),
                    array(
                        'field' => 'jOuverture',
                        'alias' => 'etab_jOuverture'
                    ),
                    array(
                        'field' => 'hMatin',
                        'alias' => 'etab_hMatin'
                    ),
                    array(
                        'field' => 'hMidi',
                        'alias' => 'etab_hMidi'
                    ),
                    array(
                        'field' => 'hAMidi',
                        'alias' => 'etab_hAMidi'
                    ),
                    array(
                        'field' => 'hSoir',
                        'alias' => 'etab_hSoir'
                    ),
                    array(
                        'field' => 'hGarderieOMatin',
                        'alias' => 'etab_hGarderieOMatin'
                    ),
                    array(
                        'field' => 'hGarderieFMidi',
                        'alias' => 'etab_hGarderieFMidi'
                    ),
                    array(
                        'field' => 'hGarderieFSoir',
                        'alias' => 'etab_hGarderieFSoir'
                    ),
                    array(
                        'field' => 'x',
                        'alias' => 'etab_x'
                    ),
                    array(
                        'field' => 'y',
                        'alias' => 'etab_y'
                    )
                )
            ),
            array(
                'table' => 'communes',
                'type' => 'table',
                'alias' => 'com1',
                'relation' => 'com1.communeId = eta.communeId',
                'fields' => array(
                    array(
                        'field' => 'nom',
                        'alias' => 'etab_commune'
                    )
                )
            ),
            array(
                'table' => 'services', // obligatoire mais peut être une vue
                'type' => 'table', // optionnel, 'table' par défaut
                'alias' => 'ser', // optionnel
                'relation' => 'rel.serviceId = ser.serviceId', // obligatoire
                'fields' => array(
                    array(
                        'field' => 'nom',
                        'alias' => 'serv_nom'
                    ),
                    array(
                        'field' => 'aliasCG',
                        'alias' => 'serv_aliasCG'
                    ),
                    array(
                        'field' => 'transporteurId',
                        'alias' => 'serv_transporteurId'
                    ),
                    array(
                        'field' => 'nbPlaces',
                        'alias' => 'serv_nbPlaces'
                    ),
                    array(
                        'field' => 'surEtatCG',
                        'alias' => 'serv_surEtatCG'
                    ),
                    array(
                        'field' => 'operateur',
                        'alias' => 'serv_operateur'
                    ),
                    array(
                        'field' => 'kmAVide',
                        'alias' => 'serv_kmAVide'
                    ),
                    array(
                        'field' => 'kmEnCharge',
                        'alias' => 'serv_kmEnCharge'
                    ),
                    array(
                        'field' => 'selection',
                        'alias' => 'serv_selection'
                    )
                )
            ),
            array(
                'table' => 'transporteurs', // obligatoire mais peut être une vue
                'type' => 'table', // optionnel, 'table' par défaut
                'alias' => 'tra', // optionnel
                'relation' => 'tra.transporteurId = ser.transporteurId', // obligatoire
                'fields' => array(
                    array(
                        'field' => 'nom',
                        'alias' => 'serv_transporteur'
                    )
                )
            ),
            array(
                'table' => 'communes', // obligatoire mais peut être une vue
                'type' => 'table', // optionnel, 'table' par défaut
                'alias' => 'com2', // optionnel
                'relation' => 'com2.communeId = tra.communeId', // obligatoire
                'fields' => array(
                    array(
                        'field' => 'nom',
                        'alias' => 'serv_communeTransporteur'
                    )
                )
            ),
            array(
                'table' => 'stations',
                'type' => 'table',
                'alias' => 'sta',
                'relation' => 'rel.stationId = sta.stationId',
                'fields' => array(
                    array(
                        'field' => 'nom',
                        'alias' => 'sta_nom'
                    ),
                    array(
                        'field' => 'ouverte',
                        'alias' => 'sta_ouverte'
                    ),
                    array(
                        'field' => 'visible',
                        'alias' => 'sta_visible'
                    ),
                    array(
                        'field' => 'selection',
                        'alias' => 'sta_selection'
                    ),
                    array(
                        'field' => 'x',
                        'alias' => 'sta_x'
                    ),
                    array(
                        'field' => 'y',
                        'alias' => 'sta_y'
                    )
                )
            ),
            array(
                'table' => 'communes',
                'type' => 'table',
                'alias' => 'comsta',
                'relation' => 'comsta.communeId = sta.communeId',
                'fields' => array(
                    array(
                        'field' => 'nom',
                        'alias' => 'sta_commune'
                    )
                )
            ),
            array(
                'table' => 'circuits',
                'type' => 'table',
                'alias' => 'cir',
                'relation' => 'cir.serviceId = rel.serviceId AND cir.stationId = rel.stationId',
                'fields' => array(

                    array(
                        'field' => 'circuitId'
                    ),
                    array(
                        'field' => 'selection',
                        'alias' => 'cir_selection'
                    ),
                    array(
                        'field' => 'millesime',
                        'alias' => 'cir_millesime'
                    ),
                    array(
                        'field' => 'semaine',
                        'alias' => 'cir_semaine'
                    ),
                    array(
                        'field' => 'm1',
                        'alias' => 'cir_m1'
                    ),
                    array(
                        'field' => 's1',
                        'alias' => 'cir_s1'
                    ),
                    array(
                        'field' => 'm2',
                        'alias' => 'cir_m2'
                    ),
                    array(
                        'field' => 's2',
                        'alias' => 'cir_s2'
                    ),
                    array(
                        'field' => 'm3',
                        'alias' => 'cir_m3'
                    ),
                    array(
                        'field' => 's3',
                        'alias' => 'cir_s3'
                    ),
                    array(
                        'field' => 'distance',
                        'alias' => 'cir_distance'
                    ),
                    array(
                        'field' => 'montee',
                        'alias' => 'cir_montee'
                    ),
                    array(
                        'field' => 'descente',
                        'alias' => 'cir_descente'
                    ),
                    array(
                        'field' => 'typeArret',
                        'alias' => 'cir_typeArret'
                    ),
                    array(
                        'field' => 'commentaire1',
                        'alias' => 'cir_commentaire1'
                    ),
                    array(
                        'field' => 'commentaire2',
                        'alias' => 'cir_commentaire2'
                    )
                )
            )
        ),
        'order' => array(
            'etablissementId',
            'serviceId'
        )
    )
);