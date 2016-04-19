<?php
/**
 * Module SbmInstallation
 *
 * @project sbm
 * @package module/SbmInstallation/config
 * @filesource module.config.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 10 avr. 2016
 * @version 2016-2
 */
use SbmInstallation\Controller\Service\IndexControllerFactory;
use SbmInstallation\Model\Service;
return [
    'acl' => [
        'resources' => [
            'sbminstall' => [
                'allow' => [
                    'roles' => ['sadmin']
                ]
            ]
        ],
    ],
    'controllers' => [
        'factories' => [
            'SbmInstallation\Controller\Index' => IndexControllerFactory::class
        ]
    ],
    'router' => [
        'routes' => [
            'sbminstall' => [
                
                // 'type' => 'literal',
                'type' => 'segment',
                'options' => [
                    
                    // 'route' => '/install',
                    'route' => '/install[/:action]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*'
                    ],
                    'defaults' => [
                        'module' => 'SbmInstallation',
                        'controller' => 'SbmInstallation\Controller\Index',
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
