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
 * @date 9 sept. 2020
 * @version 2020-2.6.0
 */
use SbmPortail\Controller;

return [
    'acl' => [
        'resources' => [
            'sbmportail' => [
                'allow' => [
                    'roles' => [
                        'gestion'
                    ]
                ],
                'actions' => [
                    'index' => [
                        'allow' => [
                            'roles' => [
                                'transporteur',
                                'etablissement',
                                'commune',
                                'secretariat'
                            ]
                        ]
                    ],
                    'retour' => [
                        'allow' => [
                            'roles' => [
                                'guest'
                            ]
                        ]
                    ],
                    'org-index' => [
                        'allow' => [
                            'roles' => [
                                'secretariat'
                            ]
                        ]
                    ],
                    'org-eleves' => [
                        'allow' => [
                            'roles' => [
                                'secretariat'
                            ]
                        ]
                    ],
                    'org-pdf' => [
                        'allow' => [
                            'roles' => [
                                'secretariat'
                            ]
                        ]
                    ],
                    'org-eleves-download' => [
                        'allow' => [
                            'roles' => [
                                'secretariat'
                            ]
                        ]
                    ],
                    'org-circuits' => [
                        'allow' => [
                            'roles' => [
                                'secretariat'
                            ]
                        ]
                    ],
                    'com-index' => [
                        'allow' => [
                            'roles' => [
                                'commune'
                            ]
                        ]
                    ],
                    'com-eleves' => [
                        'allow' => [
                            'roles' => [
                                'commune'
                            ]
                        ]
                    ],
                    'com-eleves-download' => [
                        'allow' => [
                            'roles' => [
                                'commune'
                            ]
                        ]
                    ],
                    'com-pdf' => [
                        'allow' => [
                            'roles' => [
                                'commune'
                            ]
                        ]
                    ],
                    'com-carte-etablissements' => [
                        'allow' => [
                            'roles' => [
                                'commune'
                            ]
                        ]
                    ],
                    'com-carte-stations' => [
                        'allow' => [
                            'roles' => [
                                'commune'
                            ]
                        ]
                    ],
                    'com-circuits' => [
                        'allow' => [
                            'roles' => [
                                'commune'
                            ]
                        ]
                    ],
                    'et-index' => [
                        'allow' => [
                            'roles' => [
                                'etablissement'
                            ]
                        ]
                    ],
                    'tr-index' => [
                        'allow' => [
                            'roles' => [
                                'transporteur'
                            ]
                        ]
                    ],
                    'tr-eleves' => [
                        'allow' => [
                            'roles' => [
                                'transporteur',
                                'etablissement'
                            ]
                        ]
                    ],
                    'tr-pdf' => [
                        'allow' => [
                            'roles' => [
                                'transporteur',
                                'etablissement'
                            ]
                        ]
                    ],
                    'tr-circuits' => [
                        'allow' => [
                            'roles' => [
                                'transporteur',
                                'etablissement'
                            ]
                        ]
                    ],
                    'tr-circuit-group' => [
                        'allow' => [
                            'roles' => [
                                'transporteur',
                                'etablissement'
                            ]
                        ]
                    ],
                    'tr-carte-etablissements' => [
                        'allow' => [
                            'roles' => [
                                'transporteur',
                                'etablissement',
                                'secretariat'
                            ]
                        ]
                    ],
                    'tr-carte-stations' => [
                        'allow' => [
                            'roles' => [
                                'transporteur',
                                'etablissement',
                                'secretariat'
                            ]
                        ]
                    ],
                    'tr-extraction-telephones' => [
                        'allow' => [
                            'roles' => [
                                'transporteur',
                                'etablissement'
                            ]
                        ]
                    ],
                    'tr-eleves-download' => [
                        'allow' => [
                            'roles' => [
                                'transporteur',
                                'etablissement'
                            ]
                        ]
                    ],
                    'modif-compte' => [
                        'allow' => [
                            'roles' => [
                                'transporteur',
                                'etablissement',
                                'commune',
                                'secretariat'
                            ]
                        ]
                    ],
                    'localisation' => [
                        'allow' => [
                            'roles' => [
                                'transporteur',
                                'etablissement',
                                'commune',
                                'secretariat'
                            ]
                        ]
                    ],
                    'mdp-change' => [
                        'allow' => [
                            'roles' => [
                                'transporteur',
                                'etablissement',
                                'commune',
                                'secretariat'
                            ]
                        ]
                    ],
                    'email-change' => [
                        'allow' => [
                            'roles' => [
                                'transporteur',
                                'etablissement',
                                'commune',
                                'secretariat'
                            ]
                        ]
                    ],
                    'message' => [
                        'allow' => [
                            'roles' => [
                                'transporteur',
                                'etablissement',
                                'commune',
                                'secretariat'
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ],
    'controllers' => [
        'factories' => [
            Controller\IndexController::class => Controller\Service\IndexControllerFactory::class
        ]
    ],
    'router' => [
        'routes' => [
            'sbmportail' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/portail[/:action[/:page][/:id][/id/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'page' => '[0-9]+',
                        'id' => '[a-zA-Z0-9][a-zA-Z0-9_-]*'
                    ],
                    'defaults' => [
                        'module' => 'SbmPortail',
                        'controller' => Controller\IndexController::class,
                        'action' => 'index'
                    ]
                ],
                'may_terminate' => true
            ]
        ]
    ],
    'db_manager' => [
        'factories' => [
            'Sbm\Portail\Commune\Query' => \SbmPortail\Model\Db\Service\Query\Commune::class,
            'Sbm\Portail\Secretariat\Query' => \SbmPortail\Model\Db\Service\Query\Secretariat::class,
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