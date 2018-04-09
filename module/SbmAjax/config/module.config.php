<?php
/**
 * Fichier de configuration du module
 * 
 * Les arguments passés par get sont de la forme key1:value1/key2:value2...
 *
 * @project sbm
 * @package SbmAjax/config
 * @filesource module.config.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 3 avr. 2018
 * @version 2018-2.4.0
 */
use SbmAjax\Controller\Service;

$controllers = [
    \SbmAjax\Controller\AdminController::ROUTE => Service\AdminControllerFactory::class,
    \SbmAjax\Controller\EleveController::ROUTE => Service\EleveControllerFactory::class,
    \SbmAjax\Controller\TransportController::ROUTE => Service\TransportControllerFactory::class,
    \SbmAjax\Controller\FinanceController::ROUTE => Service\FinanceControllerFactory::class,
    \SbmAjax\Controller\ParentController::ROUTE => Service\ParentControllerFactory::class
];
$routes = [];
foreach ($controllers as $key => $value) {
    $routes[$key] = [
        'type' => 'segment',
        'options' => [
            'route' => "/$key" . '[/:action][/:args]',
            'constraints' => [
                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                'args' => '[a-zA-Z][a-zA-Z0-9_-]*:[a-zA-Z0-9_%\-\+]+(/[a-zA-Z][a-zA-Z0-9_-]*:[a-zA-Z0-9_%\-\+]+)*'
            ],
            'defaults' => [
                'module' => 'SbmAjax',
                'controller' => $key,
                'action' => 'index'
            ]
        ],
        'may_terminate' => false
    ];
}
return [
    'acl' => [
        'resources' => [
            \SbmAjax\Controller\AdminController::ROUTE => [
                'allow' => [
                    'roles' => [
                        'admin'
                    ]
                ]
            ],
            \SbmAjax\Controller\EleveController::ROUTE => [
                'allow' => [
                    'roles' => [
                        'parent'
                    ]
                ]
            ],
            \SbmAjax\Controller\FinanceController::ROUTE => [
                'allow' => [
                    'roles' => [
                        'gestion'
                    ]
                ]
            ],
            \SbmAjax\Controller\TransportController::ROUTE => [
                'allow' => [
                    'roles' => [
                        'gestion'
                    ]
                ]
            ],
            \SbmAjax\Controller\ParentController::ROUTE => [
                'allow' => [
                    'roles' => [
                        'parent'
                    ]
                ]
            ]
        ]
    ],
    'controllers' => [
        // de préférence dans ce module, commencer les noms par sbmajax (pour des routes commençant par ajax) et les laisser en minuscules
        'factories' => $controllers
    ],
    'router' => [
        'routes' => $routes
    ],
    'view_manager' => [
        'template_map' => [],
        'template_path_stack' => [
            __DIR__ . '/../view'
        ]
    ]
]
// 'strategies' => [
// 'ViewJsonStrategy'
// ]

;
