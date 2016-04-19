<?php
/**
 * Module SbmParent
 *
 * @project sbm
 * @package module/SbmParent/config
 * @filesource module.config.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr]
 * @date 6 avr. 2016
 * @version 2016-2
 */
use SbmParent\Controller\Service;
use SbmParent\Form;

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
    'form_manager' => [
        'invokables' => [
            Form\Responsable2Complet::class => Form\Responsable2Complet::class,
            Form\Responsable2Restreint::class => Form\Responsable2Restreint::class
        ],
        'factories' => [
            Form\Enfant::class => Form\Service\EnfantFactory::class
        ]
    ],
    'controllers' => [
        'factories' => [
            'SbmParent\Controller\Index' => Service\IndexControllerFactory::class,
            'SbmParent\Controller\Config' => Service\ConfigControllerFactory::class
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
                        'controller' => 'SbmParent\Controller\Index',
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
                        'controller' => 'SbmParent\Controller\Config',
                        'action' => 'index'
                    ]
                ],
                'may_terminate' => true
            ]
        ]
    ],
    'view_manager' => [
        'template_map' => [],
        'template_path_stack' => [
            __DIR__ . '/../view'
        ]
    ]
];