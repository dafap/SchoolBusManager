<?php
/**
 * Structure de la vue `services`
 *
 * Version pour TRANSDEV ALBERTVILLE
 *
 * @project sbm
 * @package SbmInstallation/db_design
 * @filesource vue.services.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 26 mars 2020
 * @version 2020-2.6.0
 */
use Zend\Db\Sql\Join;

return [
    'name' => 'services',
    'type' => 'vue',
    'drop' => true, // si true, un DROP TABLE IF EXISTS sera fait avant la création
    'edit_entity' => true,
    'structure' => [
        'fields' => [
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
                'field' => 'transporteurId'
            ],
            [
                'field' => 'selection'
            ],
            [
                'field' => 'actif'
            ],
            [
                'field' => 'visible'
            ],
            [
                'field' => 'semaine'
            ],
            [
                'field' => 'rang'
            ],
            [
                'field' => 'type'
            ],
            [
                'field' => 'nbPlaces'
            ],
            [
                'field' => 'alias'
            ],
            [
                'field' => 'commentaire'
            ]
        ],
        'from' => [
            'table' => 'services', // obligatoire mais peut être une vue
            'type' => 'table', // optionnel, 'table' par défaut
            'alias' => 'ser'
        ],
        'join' => [
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
                        'field' => 'extremite1'
                    ],
                    [
                        'field' => 'extremite2'
                    ],
                    [
                        'field' => 'via'
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
                        'field' => 'marche'
                    ],
                    [
                        'field' => 'lot'
                    ],
                    [
                        'field' => 'libelle'
                    ],
                    [
                        'field' => 'complement'
                    ],
                    [
                        'field' => 'dateDebut'
                    ],
                    [
                        'field' => 'dateFin'
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
                        'alias' => 'lotSelectionne'
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
                        'alias' => 'titulaire'
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
            'ordre'
        ]
    ]
];