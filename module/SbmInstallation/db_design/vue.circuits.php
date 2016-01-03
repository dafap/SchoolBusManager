<?php
/**
 * Structure de la vue `circuits`
 *
 * @project sbm
 * @package SbmInstallation/db_design
 * @filesource vue.circuits.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 24 févr. 2015
 * @version 2015-1
 */
return array(
    'name' => 'circuits',
    'type' => 'vue',
    'drop' => true, // si true, un DROP TABLE IF EXISTS sera fait avant la création
    'edit_entity' => true,
    'structure' => array(
        'fields' => array(
            array(
                'field' => 'circuitId'
            ),
            array(
                'field' => 'selection'
            ),
            array(
                'field' => 'millesime'
            ),
            array(
                'field' => 'serviceId'
            ),
            array(
                'field' => 'stationId'
            ),
            array(
                'field' => 'passage'
            ),
            array(
                'field' => 'semaine'
            ),
            array(
                'field' => 'm1'
            ),
            array(
                'field' => 's1'
            ),
            array(
                'field' => 'm2'
            ),
            array(
                'field' => 's2'
            ),
            array(
                'field' => 'm3'
            ),
            array(
                'field' => 's3'
            ),
            array(
                'field' => 'distance'
            ),
            array(
                'field' => 'montee'
            ),
            array(
                'field' => 'descente'
            ),
            array(
                'field' => 'typeArret'
            ),
            array(
                'field' => 'commentaire1'
            ),
            array(
                'field' => 'commentaire2'
            )
        ),
        'from' => array(
            'table' => 'circuits',
            'type' => 'table',
            'alias' => 'cir'
        ),
        'join' => array(
            array(
                'table' => 'services',
                'type' => 'table',
                'alias' => 'ser',
                'relation' => 'ser.serviceId = cir.serviceId',
                'fields' => array(
                    array(
                        'field' => 'nom',
                        'alias' => 'service'
                    ),
                    array(
                        'field' => 'nbPlaces'
                    ),
                    array(
                        'field' => 'operateur'
                    ),
                    array(
                        'field' => 'kmAVide'
                    ),
                    array(
                        'field' => 'kmEnCharge'
                    ),
                    array(
                        'field' => 'transporteurId'
                    )
                )
            ),
            array(
                'table' => 'transporteurs',
                'type' => 'table',
                'alias' => 'tra',
                'relation' => 'ser.transporteurId = tra.transporteurId',
                'fields' => array(
                    array(
                        'field' => 'nom',
                        'alias' => 'transporteur'
                    ),
                    array(
                        'field' => 'telephone',
                        'alias' => 'telephoneTransporteur'
                    )
                )
            ),
            array(
                'table' => 'communes',
                'type' => 'table',
                'alias' => 'comtra',
                'relation' => 'comtra.communeId = tra.communeId',
                'fields' => array(
                    array(
                        'field' => 'nom',
                        'alias' => 'communeTransporteur'
                    )
                )
            ),
            array(
                'table' => 'stations',
                'type' => 'table',
                'alias' => 'sta',
                'relation' => 'sta.stationId = cir.stationId',
                'fields' => array(
                    array(
                        'field' => 'nom',
                        'alias' => 'station'
                    ),
                    array(
                        'field' => 'ouverte',
                        'alias' => 'stationOuverte'
                    ),
                    array(
                        'field' => 'visible',
                        'alias' => 'stationVisible'
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
                        'alias' => 'communeStation'
                    )
                )
            )
        ),
        'order' => array(
            'serviceid',
            'm1'
        )
    )
);