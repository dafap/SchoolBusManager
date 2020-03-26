<?php
/**
 * Structure de la vue `circuits`
 *
 * Version pour TRANSDEV ALBERTVILLE
 *
 * @project sbm
 * @package SbmInstallation/db_design
 * @filesource vue.circuits.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 26 mars 2020
 * @version 2020-2.6.0
 */
use Zend\Db\Sql\Join;

return [
    'name' => 'circuits',
    'type' => 'vue',
    'drop' => true,
    'edit_entity' => true,
    'structure' => [
        'fields' => [
            [
                'field' => 'circuitId'
            ],
            [
                'field' => 'millesime'
            ],
            [
                'field' => 'ligneId'
            ],
            [
                'field' => 'sens'
            ],
            [
                'field' => 'moment'
            ],
            [
                'field' => 'ordre'
            ],
            [
                'field' => 'stationId'
            ],
            [
                'field' => 'passage'
            ],
            [
                'field' => 'selection'
            ],
            [
                'field' => 'visible'
            ],
            [
                'field' => 'ouvert'
            ],
            [
                'field' => 'semaine'
            ],
            [
                'field' => 'horaireA'
            ],
            [
                'field' => 'horaireD'
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
                'field' => 'correspondance'
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
                'relation' => 'ser.millesime = cir.millesime AND ser.ligneId = cir.ligneId AND ser.sens = cir.sens AND ser.moment = cir.moment AND ser.ordre = cir.ordre',
                'fields' => [
                    [
                        'field' => 'transporteurId'
                    ],
                    [
                        'field' => 'selection',
                        'alias' => 'serviceSelectionne'
                    ],
                    [
                        'field' => 'actif',
                        'alias' => 'serviceActif'
                    ],
                    [
                        'field' => 'visible',
                        'alias' => 'serviceVisible'
                    ],
                    [
                        'field' => 'semaine',
                        'alias' => 'serviceSemaine'
                    ],
                    [
                        'field' => 'rang',
                        'alias' => 'serviceRang'
                    ],
                    [
                        'field' => 'type',
                        'alias' => 'serviceType'
                    ],
                    [
                        'field' => 'nbPlaces',
                        'alias' => 'serviceNbPlaces'
                    ],
                    [
                        'field' => 'alias',
                        'alias' => 'serviceAlias'
                    ],
                    [
                        'field' => 'commentaire',
                        'alias' => 'serviceCommentaire'
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
                    ],
                    [
                        'field' => 'alias',
                        'alias' => 'lacommuneStation'
                    ],
                    [
                        'field' => 'alias_laposte',
                        'alias' => 'laposteStation'
                    ],
                    [
                        'field' => 'codePostal',
                        'alias' => 'codePostalStation'
                    ]
                ]
            ],
            [
                'table' => 'transporteurs', // obligatoire mais peut être une vue
                'type' => 'table', // optionnel, 'table' par défaut
                'alias' => 'tra', // optionnel
                'relation' => 'tra.transporteurId = ser.transporteurId', // obligatoire
                'fields' => [
                    [
                        'field' => 'nom',
                        'alias' => 'transporteur'
                    ]
                ]
            ],
            [
                'table' => 'communes', // obligatoire mais peut être une vue
                'type' => 'table', // optionnel, 'table' par défaut
                'alias' => 'ctra', // optionnel
                'relation' => 'ctra.communeId = tra.communeId', // obligatoire
                'fields' => [
                    [
                        'field' => 'nom',
                        'alias' => 'communeTransporteur'
                    ],
                    [
                        'field' => 'alias',
                        'alias' => 'lacommuneTransporteur'
                    ],
                    [
                        'field' => 'alias_laposte',
                        'alias' => 'laposteTransporteur'
                    ]
                ]
            ],
            [
                'table' => 'lignes',
                'type' => 'table',
                'alias' => 'lig',
                'relation' => 'lig.millesime = ser.millesime AND lig.ligneId = ser.ligneId',
                'fields' => [
                    [
                        'field' => 'operateur'
                    ],
                    [
                        'field' => 'lotId'
                    ],
                    [
                        'field' => 'extremite1',
                        'alias' => 'ligneExtremite1'
                    ],
                    [
                        'field' => 'extremite2',
                        'alias' => 'ligneExtremite2'
                    ],
                    [
                        'field' => 'via',
                        'alias' => 'ligneVia'
                    ],
                    [
                        'field' => 'internes',
                        'alias' => 'ligneInternes'
                    ],
                    [
                        'field' => 'actif',
                        'alias' => 'ligneOuverte'
                    ],
                    [
                        'field' => 'selection',
                        'alias' => 'ligneSelectionnee'
                    ],
                    [
                        'field' => 'commentaire',
                        'alias' => 'ligneCommentaire'
                    ]
                ]
            ],
            [
                'table' => 'lots', // obligatoire mais peut être une vue
                'type' => 'table', // optionnel, 'table' par défaut
                'alias' => 'lots', // optionnel
                'relation' => 'lig.lotId = lots.lotId', // obligatoire
                'jointure' => Join::JOIN_LEFT,
                'fields' => [
                    [
                        'field' => 'marche',
                        'alias' => 'lotMarche'
                    ],
                    [
                        'field' => 'lot'
                    ],
                    [
                        'field' => 'libelle',
                        'alias' => 'lotLibelle'
                    ],
                    [
                        'field' => 'complement',
                        'alias' => 'lotComplement'
                    ],
                    [
                        'field' => 'dateDebut',
                        'alias' => 'lotDateDebut'
                    ],
                    [
                        'field' => 'dateFin',
                        'alias'=>'lotDateFin'
                    ],
                    [
                        'field' => 'commentaire',
                        'alias' => 'lotCommentaire'
                    ],
                    [
                        'field' => 'actif',
                        'alias' => 'lotActif'
                    ],
                    [
                        'field' => 'selection',
                        'alias' => 'lotSelection'
                    ]
                ]
            ],
            [
                'table' => 'transporteurs', // obligatoire mais peut être une vue
                'type' => 'table', // optionnel, 'table' par défaut
                'alias' => 'tit', // optionnel
                'relation' => 'tit.transporteurId = lots.transporteurId', // obligatoire
                'jointure' => Join::JOIN_LEFT,
                'fields' => [
                    [
                        'field' => 'nom',
                        'alias' => 'lotTitulaire'
                    ]
                ]
            ],
            [
                'table' => 'communes', // obligatoire mais peut être une vue
                'type' => 'table', // optionnel, 'table' par défaut
                'alias' => 'ctit', // optionnel
                'relation' => 'ctit.communeId = tit.communeId', // obligatoire
                'jointure' => Join::JOIN_LEFT,
                'fields' => [
                    [
                        'field' => 'nom',
                        'alias' => 'communeTitulaire'
                    ],
                    [
                        'field' => 'alias',
                        'alias' => 'lacommuneTitulaire'
                    ],
                    [
                        'field' => 'alias_laposte',
                        'alias' => 'laposteTitulaire'
                    ]
                ]
            ]
        ],
        'order' => [
            'millesime',
            'ligneId',
            'sens',
            'moment',
            'ordre',
            'horaireA'
        ]
    ]
];