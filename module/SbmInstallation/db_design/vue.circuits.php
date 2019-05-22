<?php
/**
 * Structure de la vue `circuits`
 *
 * @project sbm
 * @package SbmInstallation/db_design
 * @filesource vue.circuits.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 22 mai 2019
 * @version 2019-2.5.0
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
                'field' => 'z1'
            ],
            [
                'field' => 'm2'
            ],
            [
                'field' => 's2'
            ],
            [
                'field' => 'z2'
            ],
            [
                'field' => 'm3'
            ],
            [
                'field' => 's3'
            ],
            [
                'field' => 'z3'
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
                'field' => 'emplacement'
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
                        'field' => 'alias',
                        'alias' => 'serv_alias'
                    ],
                    [
                        'field' => 'aliasTr',
                        'alias' => 'serv_aliasTr'
                    ],
                    [
                        'field' => 'aliasCG',
                        'alias' => 'serv_aliasCG'
                    ],
                    [
                        'field' => 'nom',
                        'alias' => 'service'
                    ],
                    [
                        'field' => 'horaire1'
                    ],
                    [
                        'field' => 'horaire2'
                    ],
                    [
                        'field' => 'horaire3'
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
                'table' => 'lots',
                'type' => 'table',
                'alias' => 'lot',
                'relation' => 'ser.lotId=lot.lotId',
                'fields' => [
                    [
                        'field' => 'lotId'
                    ],
                    [
                        'field' => 'marche',
                        'alias' => 'lot_marche'
                    ],
                    [
                        'field' => 'lot',
                        'alias' => 'lot_lot'
                    ],
                    [
                        'field' => 'libelle',
                        'alias' => 'lot_libelle'
                    ],
                    [
                        'field' => 'transporteurId',
                        'alias' => 'lot_transporteurId'
                    ],
                    [
                        'field' => 'dateDebut',
                        'alias' => 'lot_dateDebut'
                    ],
                    [
                        'field' => 'dateFin',
                        'alias' => 'lot_dateFin'
                    ],
                    [
                        'field' => 'actif',
                        'alias' => 'lot_actif'
                    ],
                    [
                        'field' => 'selection',
                        'alias' => 'lot_selection'
                    ]
                ]
            ],
            [
                'table' => 'transporteurs',
                'type' => 'table',
                'alias' => 'tit',
                'relation' => 'tit.transporteurId = lot.transporteurId',
                'fields' => [
                    [
                        'field' => 'nom',
                        'alias' => 'lot_transporteur'
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
            'serviceid',
            'm1'
        ]
    ]
];