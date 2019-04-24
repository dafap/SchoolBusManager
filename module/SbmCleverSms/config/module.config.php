<?php
/**
 * Paramètres de configuration du module SbmCleverSms
 *
 * Requêtes basées sur l'API REST de Clever SMS Ligth (v2.1 du 17/08/2015)
 *
 * @project sbm
 * @package SbmCleverSms/config
 * @filesource module.config.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 19 avr. 2019
 * @version 2019-2.5.0
 */
use SbmBase\Model\StdLib;
use SbmCleverSms\Controller;
use SbmCleverSms\Form;
use SbmCleverSms\Model;

return [
    'acl' => [
        'resources' => [
            'sbmservicesms' => [
                'allow' => [
                    'roles' => [
                        'sadmin'
                    ]
                ],
                'actions' => [
                    'account-info' => [
                        'allow' => [
                            'roles' => [
                                'gestion',
                                'admin'
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ],
    'sbm' => [
        'servicesms' => [
            'api_url' => 'http://webserviceslight.clever.fr/api/',
            'path_filelog' => StdLib::findParentPath(__DIR__, 'data/logs')
        ]
    ],
    'service_manager' => [
        'factories' => [
            Model\CurlRequest::class => Model\CurlRequestFactory::class
        ]
    ],
    'db_manager' => [
        'invokables' => [
            'Sbm\Db\ObjectData\CleverSms' => Model\Db\ObjectData\CleverSms::class
        ],
        'factories' => [
            'Sbm\Db\Table\CleverSms' => Model\Db\Service\Table\CleverSms::class,
            'Sbm\Db\TableGateway\CleverSms' => Model\Db\Service\TableGateway\TableGatewayCleverSms::class
        ]
    ],
    'form_manager' => [
        'invokables' => [
            Form\Sms::class => Form\Sms::class
        ]
    ],
    'controllers' => [
        'factories' => [
            Controller\IndexController::class => Controller\Service\IndexControllerFactory::class
        ]
    ],
    'router' => [
        'routes' => [
            'sbmservicesms' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/sms[/:action[/page/:page][/id/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'page' => '[0-9]+',
                        'id' => '[0-9]+'
                    ],
                    'defaults' => [
                        'module' => 'SbmCleverSms',
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