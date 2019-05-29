<?php
/**
 * Structure de la vue `circuits`
 *
 * @project sbm
 * @package SbmInstallation/db_design
 * @filesource vue.circuits.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 29 mai 2019
 * @version 2019-2.4.8
 */
return [
    'name' => 'circuits',
    'type' => 'vue',
    'drop' => true, // si true, un DROP TABLE IF EXISTS sera fait avant la création
    'edit_entity' => true,
    'structure' => [
        'fields' => [
            [
                'field' => 'circuitId'
            ],
            [
                'field' => 'selection'
            ],
            [
                'field' => 'millesime'
            ],
            [
                'field' => 'serviceId'
            ],
            [
                'field' => 'stationId'
            ],
            [
                'field' => 'passage'
            ],
            [
                'field' => 'semaine'
            ],
            [
                'field' => 'm1'
            ],
            [
                'field' => 's1'
            ],
            [
                'field' => 'm2'
            ],
            [
                'field' => 's2'
            ],
            [
                'field' => 'm3'
            ],
            [
                'field' => 's3'
            ],
            [
                'field' => 'distance'
            ],
            [
                'field' => 'montee'
            ],
            [
                'field' => 'descente'
            ],
            [
                'field' => 'typeArret'
            ],
            [
                'field' => 'commentaire1'
            ],
            [
                'field' => 'commentaire2'
            ]
        ],
        'from' => [
            'table' => 'circuits',
            'type' => 'table',
            'alias' => 'cir'
        ],
        'join' => [
            [
                'table' => 'services',
                'type' => 'table',
                'alias' => 'ser',
                'relation' => 'ser.serviceId = cir.serviceId',
                'fields' => [
                    [
                        'field' => 'nom',
                        'alias' => 'service'
                    ],
                    [
                        'field' => 'nbPlaces'
                    ],
                    [
                        'field' => 'operateur'
                    ],
                    [
                        'field' => 'kmAVide'
                    ],
                    [
                        'field' => 'kmEnCharge'
                    ],
                    [
                        'field' => 'transporteurId'
                    ]
                ]
            ],
            [
                'table' => 'transporteurs',
                'type' => 'table',
                'alias' => 'tra',
                'relation' => 'ser.transporteurId = tra.transporteurId',
                'fields' => [
                    [
                        'field' => 'nom',
                        'alias' => 'transporteur'
                    ],
                    [
                        'field' => 'telephone',
                        'alias' => 'telephoneTransporteur'
                    ]
                ]
            ],
            [
                'table' => 'communes',
                'type' => 'table',
                'alias' => 'comtra',
                'relation' => 'comtra.communeId = tra.communeId',
                'fields' => [
                    [
                        'field' => 'nom',
                        'alias' => 'communeTransporteur'
                    ]
                ]
            ],
            [
                'table' => 'stations',
                'type' => 'table',
                'alias' => 'sta',
                'relation' => 'sta.stationId = cir.stationId',
                'fields' => [
                    [
                        'field' => 'nom',
                        'alias' => 'station'
                    ],
                    [
                        'field' => 'ouverte',
                        'alias' => 'stationOuverte'
                    ],
                    [
                        'field' => 'visible',
                        'alias' => 'stationVisible'
                    ]
                ]
            ],
            [
                'table' => 'communes',
                'type' => 'table',
                'alias' => 'comsta',
                'relation' => 'comsta.communeId = sta.communeId',
                'fields' => [
                    [
                        'field' => 'nom',
                        'alias' => 'communeStation'
                    ]
                ]
            ]
        ],
        'order' => [
            'serviceId',
            'm1'
        ]
    ]
];