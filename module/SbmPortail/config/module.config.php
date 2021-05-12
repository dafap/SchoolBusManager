<?php
/**
 * Configuration du module SbmPortail
 *
 * Définition des routes et des acl
 *
 * @project sbm
 * @package SbmPortail/config
 * @filesource module.config.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 11 mai 2021
 * @version 2021-2.6.1
 */
use SbmPortail\Controller;

return [
    'acl' => [
        'resources' => [
            'sbmportail' => [
                'allow' => [
                    'roles' => [
                        'gestion',
                        'commune',
                        'etablissement',
                        'secretariat',
                        'transporteur'
                    ]
                ],
                'child_resources' => [
                    'commune' => [
                        'deny' => [
                            'roles' => [
                                'etablissement',
                                //'secretariat',
                                'transporteur'
                            ]
                        ]
                    ],
                    'etablissement' => [
                        'deny' => [
                            'roles' => [
                                'commune',
                                //'secretariat',
                                'transporteur'
                            ]
                        ]
                    ],
                    'organisateur' => [
                        'deny' => [
                            'roles' => [
                                'commune',
                                'etablissement',
                                'transporteur'
                            ]
                        ]
                    ],
                    'transporteur' => [
                        'deny' => [
                            'roles' => [
                                'commune',
                                'etablissement',
                                //'secretariat'
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ],
    'controllers' => [
        'factories' => [
            Controller\CommuneController::class => Controller\Service\CommuneControllerFactory::class,
            Controller\EtablissementController::class => Controller\Service\EtablissementControllerFactory::class,
            Controller\OrganisateurController::class => Controller\Service\OrganisateurControllerFactory::class,
            Controller\PortailController::class => Controller\Service\PortailControllerFactory::class,
            Controller\TransporteurController::class => Controller\Service\TransporteurControllerFactory::class
        ]
    ],
    'router' => [
        'routes' => [
            'sbmportail' => [
                'type' => 'literal',
                'options' => [
                    'route' => '/portail',
                    'defaults' => [
                        'module' => 'SbmPortail',
                        'controller' => Controller\PortailController::class,
                        'action' => 'dispatch'
                    ]
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'commune' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => '/commune[/:action[/page/:page][/id/:id]]',
                            'constraints' => [
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'page' => '[0-9]+',
                                'id' => '[a-zA-Z0-9][a-zA-Z0-9_-]*'
                            ],
                            'defaults' => [
                                'module' => 'SbmPortail',
                                'controller' => Controller\CommuneController::class,
                                'action' => 'index'
                            ]
                        ]
                    ],
                    'etablissement' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => '/etablissement[/:action[/:page][/id/:id]]',
                            'constraints' => [
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'page' => '[0-9]+',
                                'id' => '[a-zA-Z0-9][a-zA-Z0-9_-]*'
                            ],
                            'defaults' => [
                                'module' => 'SbmPortail',
                                'controller' => Controller\EtablissementController::class,
                                'action' => 'index'
                            ]
                        ]
                    ],
                    'organisateur' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => '/organisateur[/:action[/:page][/id/:id]]',
                            'constraints' => [
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'page' => '[0-9]+',
                                'id' => '[a-zA-Z0-9][a-zA-Z0-9_-]*'
                            ],
                            'defaults' => [
                                'module' => 'SbmPortail',
                                'controller' => Controller\OrganisateurController::class,
                                'action' => 'index'
                            ]
                        ]
                    ],
                    'transporteur' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => '/transporteur[/:action[/:page][/id/:id]]',
                            'constraints' => [
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'page' => '[0-9]+',
                                'id' => '[a-zA-Z0-9][a-zA-Z0-9_-]*'
                            ],
                            'defaults' => [
                                'module' => 'SbmPortail',
                                'controller' => Controller\TransporteurController::class,
                                'action' => 'index'
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ],
    'db_manager' => [
        'factories' => [
            'Sbm\Portail\Commune\Query' => \SbmPortail\Model\Db\Service\Query\Commune::class,
            'Sbm\Portail\Etablissement\Query' => \SbmPortail\Model\Db\Service\Query\Etablissement::class,
            'Sbm\Portail\Organisateur\Query' => \SbmPortail\Model\Db\Service\Query\Organisateur::class,
            'Sbm\Portail\Transporteur\Query' => \SbmPortail\Model\Db\Service\Query\Transporteur::class
        ]
    ],
    'view_manager' => [
        'template_map' => [],
        'template_path_stack' => [
            __DIR__ . '/../view'
        ]
    ]
];