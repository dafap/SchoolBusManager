<?php
/**
 * Module SbmParent
 *
 * @project sbm
 * @package module/SbmParent/config
 * @filesource module.config.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr]
 * @date 5 avr. 2019
 * @version 2020-2.6.0
 */
use SbmParent\Controller;
use SbmParent\Form;
use SbmParent\Model\Db\Service\Query;
use SbmParent\Model\View\Helper as ViewHelper;

return [
    'acl' => [
        'resources' => [
            'sbmparent' => [
                'allow' => [
                    'roles' => [
                        'parent'
                    ]
                ]
            ],
            'sbmparentconfig' => [
                'allow' => [
                    'roles' => [
                        'parent'
                    ]
                ]
            ]
        ]
    ],
    'db_manager' => [
        'factories' => [
            Query\Eleves::class => Query\Eleves::class
        ]
    ],
    'form_manager' => [
        'invokables' => [],
        'factories' => [
            Form\Enfant::class => Form\Service\EnfantFactory::class,
            Form\ModifAdresse::class => Form\Service\ModifAdresseFactory::class,
            Form\Service\Responsable2Complet::class => Form\Service\Responsable2Complet::class,
            Form\Service\Responsable2Restreint::class => Form\Service\Responsable2Restreint::class
        ]
    ],
    'controllers' => [
        'factories' => [
            Controller\IndexController::class => Controller\Service\IndexControllerFactory::class,
            Controller\ConfigController::class => Controller\Service\ConfigControllerFactory::class
        ]
    ],
    'router' => [
        'routes' => [
            'sbmparent' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/parent[/:action[/:page][/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'page' => '[0-9]+',
                        'id' => '[a-zA-Z0-9][a-zA-Z0-9_-]*'
                    ],
                    'defaults' => [
                        'module' => 'SbmParent',
                        'controller' => Controller\IndexController::class,
                        'action' => 'index'
                    ]
                ],
                'may_terminate' => true
            ],
            'sbmparentconfig' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/parent-config[/:action[/:page][/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'page' => '[0-9]+'
                    ],
                    'defaults' => [
                        'module' => 'SbmParent',
                        'controller' => Controller\ConfigController::class,
                        'action' => 'index'
                    ]
                ],
                'may_terminate' => true
            ]
        ]
    ],'view_helpers' => [
        'invokables' => [
            'printAffectation' => ViewHelper\LigneAffectation::class
        ]
    ],
    'view_manager' => [
        'template_map' => [],
        'template_path_stack' => [
            __DIR__ . '/../view'
        ]
    ]
];