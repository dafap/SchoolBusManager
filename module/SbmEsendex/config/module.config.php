<?php
/**
 * Paramètres de configuration du module SbmEsendex
 *
 * Requêtes basées sur l'API
 *
 * @project sbm
 * @package SbmEsendex/config
 * @filesource module.config.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 2 juin 2020
 * @version 2020-2.6.0
 */
use SbmBase\Model\StdLib;
use SbmEsendex\Controller;
use SbmEsendex\Form;
use SbmEsendex\Model;

return [
    'acl' => [
        'resources' => [
            'sbmservicesms' => [
                'allow' => [
                    'roles' => [
                        'gestion'
                    ]
                ]
            ]
        ]
    ],
    'sbm' => [
        'servicesms' => [
            'name' => 'esendex',
            'api_url' => 'https://api.esendex.com/v1.0/messagedispatcher',
            'path_filelog' => StdLib::findParentPath(__DIR__, 'data/logs'),
            'filename' => 'esendex_error.log'
        ]
    ],
    'service_manager' => [
        'factories' => [
            Model\ApiSms::class => Model\ApiSmsFactory::class
        ]
    ],
    'db_manager' => [
        'invokables' => [
            'Sbm\Db\ObjectData\EsendexBatch' => Model\Db\ObjectData\EsendexBatch::class,
            'Sbm\Db\ObjectData\EsendexSms' => Model\Db\ObjectData\EsendexSms::class,
            'Sbm\Db\ObjectData\EsendexTelephone' => Model\Db\ObjectData\EsendexTelephone::class
        ],
        'factories' => [
            'Sbm\Db\Table\EsendexBatches' => Model\Db\Service\Table\EsendexBatches::class,
            'Sbm\Db\Table\EsendexSms' => Model\Db\Service\Table\EsendexSms::class,
            'Sbm\Db\Table\EsendexTelephones' => Model\Db\Service\Table\EsendexTelephones::class,
            'Sbm\Db\TableGateway\EsendexBatches' => Model\Db\Service\TableGateway\TableGatewayEsendexBatches::class,
            'Sbm\Db\TableGateway\EsendexSms' => Model\Db\Service\TableGateway\TableGatewayEsendexSms::class,
            'Sbm\Db\TableGateway\EsendexTelephones' => Model\Db\Service\TableGateway\TableGatewayEsendexTelephones::class
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
                        'module' => 'SbmEsendex',
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