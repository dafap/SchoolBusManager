<?php
/**
 * Paramètres de configuration du module SbmPaiement
 *
 * Partie indépendante des plugins utilisés
 *
 * @project sbm
 * @package SbmPaiement/config
 * @filesource module.config.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 16 juin 2019
 * @version 2019-2.5.0
 */
use SbmBase\Model\StdLib;
use SbmPaiement\Controller;
use SbmPaiement\Listener;

if (! defined('MODULE_PAIEMENT_PATH')) {
    define('MODULE_PAIEMENT_PATH', dirname(__DIR__));
    // define('ROOT_PATH', dirname(dirname(MODULE_PATH)));
}
return [
    'acl' => [
        'resources' => [
            'sbmpaiement' => [
                'allow' => [
                    'roles' => [
                        'parent'
                    ]
                ],
                'actions' => [
                    'notification' => [
                        'allow' => [
                            'roles' => [
                                'guest'
                            ]
                        ]
                    ],
                    'liste' => [
                        'allow' => [
                            'roles' => [
                                'secretariat'
                            ]
                        ]
                    ],
                    'voir' => [
                        'allow' => [
                            'roles' => [
                                'secretariat'
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ],
    'paginator' => [
        'count_per_page' => [
            'nb_notifications' => 15
        ]
    ],
    'service_manager' => [
        'factories' => [
            Listener\PaiementOK::class => Listener\Service\PaiementOKFactory::class,
            Listener\ScolariteOK::class => Listener\Service\ScolariteOKFactory::class
        ]
    ],
    'controllers' => [
        'factories' => [
            Controller\IndexController::class => Controller\Service\IndexControllerFactory::class
        ]
    ],
    'router' => [
        'routes' => [
            'sbmpaiement' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/paiement[/:action[/page/:page[/id/:id]]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'page' => '[0-9]+',
                        'id' => '[0-9]+'
                    ],
                    'defaults' => [
                        'module' => 'SbmPaiement',
                        'controller' => Controller\IndexController::class,
                        'action' => 'liste'
                    ]
                ],
                'may_terminate' => true
            ]
        ]
    ],
    'sbm' => [
        'paiement' => [
            'path_filelog' => StdLib::findParentPath(__DIR__, 'data/logs')
        ]
    ],
    'csv' => [
        'path' => [
            'tmpuploads' => StdLib::findParentPath(__DIR__, 'data/tmpuploads')
        ],
        'parameters' => [
            'firstline' => true,
            'separator' => ';',
            'enclosure' => '',
            'escape' => '\\'
        ]
    ],
    'view_manager' => [
        'template_path_stack' => [
            __DIR__ . '/../view'
        ]
    ]
];