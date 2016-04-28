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
 * @date 23 avr. 2016
 * @version 2016-2.1
 */
use SbmMailChimp\Controller;
use SbmMailChimp\Model\Db\Service\Users;
return [
    'acl' => [
        'resources' => [
            'sbmmailchimp' => [
                'allow' => [
                    'roles' => [
                        'guest',
                        'parent'
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