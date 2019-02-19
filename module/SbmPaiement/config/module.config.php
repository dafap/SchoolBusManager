<?php
/**
 * Paramètres de configuration du module SbmPaiement
 *
 * Précise le système bancaire utilisé ainsi que ses paramètres.
 * 
 * Pour SP+ il faut indiquer :
 * - vads_ctx_mode (TEST ou PRODUCTION)
 * - vads_site_id (donné au moment de l'adhésion à la plateforme)
 * - certificat pour TEST et pour PRODUCTION (à récupérer sur l'outil de gestion, menu Paramétrage / Boutique/[nom de la boutique] / Certificat
 * 
 * Pour Paybox il faut indiquer :
 * 
 * @project sbm
 * @package SbmPaiement/config
 * @filesource module.config.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 9 oct. 2018
 * @version 2019-2.5.0
 */
use SbmPaiement\Controller;
use SbmPaiement\Listener;
use SbmBase\Model\StdLib;

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
        'invokables' => [
            'Sbm\AppelPaiement' => 'SbmPaiement\Service\Trigger'
        ],
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
            'path_filelog' => StdLib::findParentPath(__DIR__, 'data/logs') //realpath(__DIR__ . '/../../../data/logs')
        ]
    ],
    'csv' => [
        'path' => [
            'tmpuploads' => StdLib::findParentPath(__DIR__, 'data/tmpuploads') //realpath(__DIR__ . '/../../../data/')
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
]
;