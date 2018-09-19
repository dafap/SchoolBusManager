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
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr]
 * @date 18 sept. 2018
 * @version 2018-2.4.5
 */
use SbmPortail\Controller;

return [
    'acl' => [
        'resources' => [
            'sbmportail' => [
                'allow' => [
                    'roles' => [
                        'transporteur',
                        'etablissement',
                        'secretariat',
                        'gestion'
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
                    'route' => '/portail[/:action[/:page][/:id]]',
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
    'view_manager' => [
        'template_map' => [],
        'template_path_stack' => [
            __DIR__ . '/../view'
        ]
    ]
];