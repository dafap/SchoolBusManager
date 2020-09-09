<?php
/**
 * Config du module SbmFront
 *
 * @project sbm
 * @package module/SbmFront/config
 * @filesource module.config.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 9 sept. 2020
 * @version 2020-2.6.0
 */
use SbmFront\Controller;
use SbmFront\Form;
use SbmFront\Form\CreerCompte;
use SbmFront\Form\EmailChange;
use SbmFront\Form\Login;
use SbmFront\Form\MdpDemande;
use SbmFront\Form\Service\CreerCompteFactory;
use SbmFront\Form\Service\EmailChangeFactory;
use SbmFront\Form\Service\LoginFactory;
use SbmFront\Form\Service\MdpDemandeFactory;
use SbmFront\Model\Responsable\Service\ResponsableManager;

if (! defined('MODULE_PATH')) {
    define('MODULE_PATH', dirname(__DIR__));
    define('ROOT_PATH', dirname(dirname(MODULE_PATH)));
}
if (! defined('APPL_NAME')) {
    define('APPL_NAME', 'School Bus Manager');
}

return [
    'acl' => [
        'resources' => [
            'home' => [
                'actions' => [
                    'hors-zone' => [
                        'deny' => [
                            'roles' => [
                                'guest'
                            ]
                        ],
                        'allow' => [
                            'roles' => [
                                'parent'
                            ]
                        ]
                    ]
                ]
            ],
            'login' => [
                'actions' => [
                    'annuler' => [
                        'allow' => [
                            'roles' => [
                                'guest'
                            ]
                        ]
                    ],
                    'confirm' => [
                        'allow' => [
                            'roles' => [
                                'guest'
                            ]
                        ]
                    ],
                    'contact' => [
                        'allow' => [
                            'roles' => [
                                'guest'
                            ]
                        ]
                    ],
                    'creer-compte' => [
                        'allow' => [
                            'roles' => [
                                'guest'
                            ]
                        ]
                    ],
                    'login' => [
                        'allow' => [
                            'roles' => [
                                'guest'
                            ]
                        ]
                    ],
                    'home-page' => [
                        'allow' => [
                            'roles' => [
                                'guest'
                            ]
                        ]
                    ],
                    'mdp-demande' => [
                        'allow' => [
                            'roles' => [
                                'guest'
                            ]
                        ]
                    ],
                    'email-change' => [
                        'allow' => [
                            'roles' => [
                                'parent',
                                'secretariat',
                                'commune',
                                'transporteur',
                                'etablissement'
                            ]
                        ]
                    ],
                    'logout' => [
                        'allow' => [
                            'roles' => [
                                'parent',
                                'secretariat',
                                'commune',
                                'transporteur',
                                'etablissement'
                            ]
                        ]
                    ],
                    'mdp-change' => [
                        'allow' => [
                            'roles' => [
                                'parent',
                                'secretariat',
                                'commune',
                                'transporteur',
                                'etablissement'
                            ]
                        ]
                    ],
                    'mdp-reset' => [
                        'allow' => [
                            'roles' => [
                                'parent'
                            ]
                        ]
                    ],
                    'modif-compte' => [
                        'allow' => [
                            'roles' => [
                                'parent',
                                'secretariat',
                                'commune',
                                'transporteur',
                                'etablissement'
                            ]
                        ]
                    ],
                    'synchro-compte' => [
                        'allow' => [
                            'roles' => [
                                'parent'
                            ]
                        ]
                    ]
                ]
            ],
            'test' => [
                'allow' => [
                    'roles' => [
                        'sadmin'
                    ]
                ]
            ]
        ]
    ],
    'db_manager' => [
        'factories'=>[
            \SbmFront\Factory\Test\Query\Test::class => \SbmFront\Factory\Test\Query\Test::class
        ]
    ],
    'form_manager' => [
        'invokables' => [
            Form\MdpChange::class => Form\MdpChange::class,
            Form\MdpFirst::class => Form\MdpFirst::class,
            Form\ModifCompte::class => Form\ModifCompte::class
        ],
        'factories' => [
            CreerCompte::class => CreerCompteFactory::class,
            EmailChange::class => EmailChangeFactory::class,
            Login::class => LoginFactory::class,
            MdpDemande::class => MdpDemandeFactory::class
        ]
    ],
    'service_manager' => [
        'invokables' => [],
        'factories' => [
            SbmFront\Form\Login::class => SbmFront\Form\Service\LoginFactory::class,
            ResponsableManager::class => ResponsableManager::class
        ]
    ],
    'router' => [
        'routes' => [
            'home' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/[:action[/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[a-zA-Z0-9\-\'\s\%]*'
                    ],
                    'defaults' => [
                        'module' => __NAMESPACE__,
                        'controller' => Controller\IndexController::class,
                        'action' => 'index'
                    ]
                ],
                'may_terminate' => true
            ],
            'login' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/login[/:action[/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[a-f0-9]{32}'
                    ],
                    'defaults' => [
                        'module' => __NAMESPACE__,
                        'controller' => Controller\LoginController::class,
                        'action' => 'login'
                    ]
                ]
            ],
            'test' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/test[/:action[/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[a-f0-9]{32}'
                    ],
                    'defaults' => [
                        'module' => __NAMESPACE__,
                        'controller' => Controller\TestController::class,
                        'action' => 'index'
                    ]
                ]
            ]
        ]
    ],
    'controllers' => [
        'factories' => [
            Controller\IndexController::class => Controller\Service\IndexControllerFactory::class,
            Controller\LoginController::class => Controller\Service\LoginControllerFactory::class,
            Controller\TestController::class => Controller\Service\TestControllerFactory::class
        ]
    ],
    'view_helpers' => [
        'factories' => [
            'bienvenue' => 'SbmFront\Factory\View\Helper\BienvenueFactory'
        ]
    ],
    'view_manager' => [
        'display_not_found_reason' => true,
        'display_exceptions' => true,
        'not_found_template' => 'error/404',
        'exception_template' => 'error/index',
        'template_map' => [
            'layout/layout' => MODULE_PATH . '/view/layout/layout.phtml',
            'layout/header' => MODULE_PATH . '/view/layout/header.phtml',
            'layout/footer' => MODULE_PATH . '/view/layout/footer.phtml',
            'layout/stats' => MODULE_PATH . '/view/layout/stats.phtml',
            'error/404' => MODULE_PATH . '/view/error/404.phtml',
            'error/index' => MODULE_PATH . '/view/error/index.phtml'
        ],
        'template_path_stack' => [
            MODULE_PATH . DIRECTORY_SEPARATOR . 'view'
        ]
    ]
];