<?php
/**
 * Module SbmGestion
 *
 * @project sbm
 * @package module/SbmGestion/config
 * @filesource module.config.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr]
 * @date 5 fév. 2019
 * @version 2019-2.5.0
 */
use SbmGestion\Controller;
use SbmGestion\Form;
use SbmGestion\Controller\Service;
use SbmGestion\Model\Db\Service as SbmGestionDbService;
use SbmGestion\Model\View\Helper as ViewHelper;
use SbmGestion\Model\Cartes;
use SbmGestion\Model\Photos;

return [
    'acl' => [
        'resources' => [
            'sbmgestion' => [
                'allow' => [
                    'roles' => [
                        'gestion'
                    ]
                ],
                'actions' => [
                    'ouvrir' => [
                        'deny' => [
                            'roles' => [
                                'parent'
                            ]
                        ],
                        'allow' => [
                            'roles' => [
                                'admin'
                            ]
                        ],
                        'redirect_to' => 'sbmgestion/anneescolaire'
                    ],
                    'circuit-dupliquer' => [
                        'deny' => [
                            'roles' => [
                                'parent'
                            ]
                        ],
                        'allow' => [
                            'roles' => [
                                'admin'
                            ]
                        ],
                        'redirect_to' => 'sbmgestion/transport'
                    ]
                ]
            ]
        ]
    ],
    'paginator' => [
        'count_per_page' => [
            'nb_circuits' => 15,
            'nb_classes' => 15,
            'nb_communes' => 15,
            'nb_eleves' => 10,
            'nb_etablissements' => 15,
            'nb_organismes' => 15,
            'nb_paiements' => 10,
            'nb_responsables' => 15,
            'nb_services' => 15,
            'nb_stations' => 15,
            'nb_tarifs' => 15,
            'nb_transporteurs' => 15
        ]
    ],
    'db_manager' => [
        'factories' => [
            'Sbm\Db\Simulation\Prepare' => SbmGestionDbService\Simulation\Prepare::class,
            'Sbm\Db\Circuit\Liste' => SbmGestionDbService\Circuit\Liste::class,
            'Sbm\Db\Eleve\Liste' => SbmGestionDbService\Eleve\Liste::class,
            'Sbm\Db\Eleve\Effectif' => SbmGestionDbService\Eleve\Effectif::class,
            Cartes\Cartes::class => Cartes\CartesFactory::class,
            Photos\Photos::class => Photos\PhotosFactory::class
        ]
    ],
    'form_manager' => [
        'invokables' => [
            Form\Simulation::class => Form\Simulation::class
        ]
    ],
    'controllers' => [
        'invokables' => [
            Controller\ConfigController::class => Controller\ConfigController::class
        ],
        'factories' => [
            Controller\AnneeScolaireController::class => Service\AnneeScolaireControllerFactory::class,
            Controller\EleveController::class => Service\EleveControllerFactory::class,
            Controller\EleveGestionController::class => Service\EleveGestionControllerFactory::class,
            Controller\FinanceController::class => Service\FinanceControllerFactory::class,
            Controller\IndexController::class => Service\IndexControllerFactory::class,
            Controller\StatistiquesController::class => Service\StatistiquesControllerFactory::class,
            Controller\TransportController::class => Service\TransportControllerFactory::class
        ]
    ],
    'router' => [
        'routes' => [
            'sbmgestion' => [
                'type' => 'literal',
                'options' => [
                    'route' => '/gestion',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action' => 'index'
                    ]
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'eleve' => [ // gestion des élèves
                        'type' => 'segment',
                        'options' => [
                            'route' => '/eleve[/:action[/page/:page][/id/:id]]',
                            'constraints' => [
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'page' => '[0-9]+',
                                'id' => '[0-9]+'
                            ],
                            'defaults' => [
                                'module' => 'SbmGestion',
                                'controller' => Controller\EleveController::class,
                                'action' => 'index'
                            ]
                        ]
                    ],
                    'gestioneleve' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => '/gestioneleve[/:action[/:page][/:id]]',
                            'constraints' => [
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'page' => '[0-9]+',
                                'id' => '[0-9]+'
                            ],
                            'defaults' => [
                                'module' => 'SbmGestion',
                                'controller' => Controller\EleveGestionController::class,
                                'action' => 'index'
                            ]
                        ]
                    ],
                    'finance' => [ // gestion financière
                        'type' => 'segment',
                        'options' => [
                            'route' => '/finance[/:action[/page/:page][/id/:id]]',
                            'constraints' => [
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'page' => '[0-9]+',
                                'id' => '[a-zA-Z0-9]+'
                            ],
                            'defaults' => [
                                'module' => 'SbmGestion',
                                'controller' => Controller\FinanceController::class,
                                'action' => 'index'
                            ]
                        ]
                    ],
                    'transport' => [ // gestion des données du réseau de transport
                        'type' => 'segment',
                        'options' => [
                            'route' => '/transport[/:action[/page/:page][/pr/:pr][/id/:id]]',
                            'constraints' => [
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'page' => '[0-9]+',
                                'pr' => '[0-9]+',
                                'id' => '[0-9]+'
                            ],
                            'defaults' => [
                                'module' => 'SbmGestion',
                                'controller' => Controller\TransportController::class,
                                'action' => 'index'
                            ]
                        ]
                    ],
                    'anneescolaire' => [ // gestion de l'année scolaire
                        'type' => 'segment',
                        'options' => [
                            'route' => '/anneescolaire[/:action][/:millesime][/:id]',
                            'constraints' => [
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'millesime' => '[0-9]{4}',
                                'id' => '[0-9]+'
                            ],
                            'defaults' => [
                                'module' => 'SbmGestion',
                                'controller' => Controller\AnneeScolaireController::class,
                                'action' => 'index'
                            ]
                        ]
                    ],
                    'statistiques' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => '/statistiques[/:action[/id/:id]]',
                            'constraints' => [
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id' => '[0-9]+'
                            ],
                            'defaults' => [
                                'module' => 'SbmGestion',
                                'controller' => Controller\StatistiquesController::class,
                                'action' => 'index'
                            ]
                        ]
                    ],
                    'config' => [ // gestion de la configuration et des paramètres
                        'type' => 'segment',
                        'options' => [
                            'route' => '/config[/:action]',
                            'constraints' => [
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*'
                            ],
                            'defaults' => [
                                'module' => 'SbmGestion',
                                'controller' => Controller\ConfigController::class,
                                'action' => 'index'
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ],
    'view_helpers' => [
        'factories' => [
            'menuRapports' => ViewHelper\MenuRapports::class,
            'printServices' => ViewHelper\Services::class,
            'printStations' => ViewHelper\Stations::class
        ]
    ],
    'view_manager' => [
        'template_map' => [],
        'template_path_stack' => [
            __DIR__ . '/../view'
        ],
        'strategies' => [
            'ViewJsonStrategy'
        ]
    ]
];