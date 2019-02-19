<?php
/**
 * Description courte du fichier
 *
 * Description longue du fichier s'il y en a une
 * 
 * @project project_name
 * @package package_name
 * @filesource module.config.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 4 oct. 2018
 * @version 2019-2.5.0
 */
use SbmMailChimp\Controller;
use SbmMailChimp\Model\Db\Service\Users;
return [
    'acl' => [
        'resources' => [
            'sbmmailchimp' => [
                'actions' => [
                    'clean' => [
                        'allow' => [
                            'roles' => [
                                'gestion'
                            ]
                        ]
                    ],
                    'controle' => [
                        'allow' => [
                            'roles' => [
                                'gestion'
                            ]
                        ]
                    ],
                    'creer-field' => [
                        'allow' => [
                            'roles' => [
                                'sadmin'
                            ]
                        ]
                    ],
                    'creer-liste' => [
                        'allow' => [
                            'roles' => [
                                'sadmin'
                            ]
                        ]
                    ],
                    'creer-segment' => [
                        'allow' => [
                            'roles' => [
                                'sadmin'
                            ]
                        ]
                    ],
                    'dupliquer-field' => [
                        'allow' => [
                            'roles' => [
                                'sadmin'
                            ]
                        ]
                    ],
                    'dupliquer-liste' => [
                        'allow' => [
                            'roles' => [
                                'sadmin'
                            ]
                        ]
                    ],
                    'dupliquer-segment' => [
                        'allow' => [
                            'roles' => [
                                'sadmin'
                            ]
                        ]
                    ],
                    'edit-field' => [
                        'allow' => [
                            'roles' => [
                                'sadmin'
                            ]
                        ]
                    ],
                    'edit-liste' => [
                        'allow' => [
                            'roles' => [
                                'admin'
                            ]
                        ]
                    ],
                    'edit-segment' => [
                        'allow' => [
                            'roles' => [
                                'admin'
                            ]
                        ]
                    ],
                    'fields-liste' => [
                        'allow' => [
                            'roles' => [
                                'gestion'
                            ]
                        ]
                    ],
                    'index' => [
                        'allow' => [
                            'roles' => [
                                'gestion'
                            ]
                        ]
                    ],
                    'liste-members' => [
                        'allow' => [
                            'roles' => [
                                'gestion'
                            ]
                        ]
                    ],
                    'populate' => [
                        'allow' => [
                            'roles' => [
                                'gestion'
                            ]
                        ]
                    ],
                    'segment-members' => [
                        'allow' => [
                            'roles' => [
                                'gestion'
                            ]
                        ]
                    ],
                    'segments-liste' => [
                        'allow' => [
                            'roles' => [
                                'gestion'
                            ]
                        ]
                    ],
                    'suppr-field' => [
                        'allow' => [
                            'roles' => [
                                'sadmin'
                            ]
                        ]
                    ],
                    'suppr-liste' => [
                        'allow' => [
                            'roles' => [
                                'sadmin'
                            ]
                        ]
                    ],
                    'suppr-segment' => [
                        'allow' => [
                            'roles' => [
                                'sadmin'
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
            'sbmmailchimp' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/mailchimp[/:action[/:page]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'page' => '[0-9]+'
                    ],
                    'defaults' => [
                        'module' => 'SbmMailChimp',
                        'controller' => Controller\IndexController::class,
                        'action' => 'index',
                        'page' => 1
                    ]
                ],
                'may_terminate' => true
            ]
        ]
    ],
    'db_manager' => [
        'factories' => [
            Users::class => Users::class
        ]
    ],
    'view_manager' => [
        'template_map' => [],
        'template_path_stack' => [
            __DIR__ . '/../view'
        ]
    ]
]; 