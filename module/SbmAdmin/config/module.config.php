<?php
/**
 * Module SbmAdmin
 *
 * @project sbm
 * @package module/SbmAdmin/config
 * @filesource module.config.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr]
 * @date 16 sept. 2018
 * @version 2018-2.4.5
 */
use SbmAdmin\Controller;
use SbmAdmin\Form;
use SbmAdmin\Model\Db\Service\Libelle\Liste;
use SbmAdmin\Model\Db\Service\Responsable\Responsables;
use SbmAdmin\Model\Db\Service\User\Users;
use SbmAdmin\Model\View\Helper\RpiClasses;
use SbmAdmin\Model\View\Helper\RpiCommunes;
use SbmAdmin\Model\View\Helper\RpiEtablissements;

return [
    'acl' => [
        'resources' => [
            'sbmadmin' => [
                'allow' => [
                    'roles' => [
                        'admin',
                        'sadmin'
                    ]
                ]
            ]
        ]
    ],
    'paginator' => [
        'count_per_page' => [
            'nb_libelles' => 15,
            'nb_secteurs-scolaires' => 20,
            'nb_simulation-etablissements' => 15,
            'nb_users' => 20
        ]
    ],
    'db_manager' => [
        'factories' => [
            Liste::class => Liste::class,
            Responsables::class => Responsables::class,
            Users::class => Users::class
        ]
    ],
    'form_manager' => [
        'invokables' => [
            Form\Libelle::class => Form\Libelle::class
        ],
        'factories' => [
            Form\Export::class => Form\Service\ExportFactory::class,
            Form\User::class => Form\Service\UserFactory::class,
            Form\UserRelation::class => Form\Service\UserRelationFactory::class
        ]
    ],
    'controllers' => [
        'factories' => [
            Controller\IndexController::class => Controller\Service\IndexControllerFactory::class
        ]
    ],
    'router' => [
        'routes' => [
            'sbmadmin' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/admin[/:action[/page/:page][/id/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'page' => '[0-9]+',
                        'id' => '[0-9]+'
                    ],
                    'defaults' => [
                        'module' => 'SbmAdmin',
                        'controller' => Controller\IndexController::class,
                        'action' => 'index'
                    ]
                ],
                'may_terminate' => true
            ]
        ]
    ],
    'view_helpers' => [
        'invokables' => [
            'rpiCommunes' => RpiCommunes::class,
            'rpiEtablissements' => RpiEtablissements::class,
            'rpiClasses' => RpiClasses::class
        ]
    ],
    'view_manager' => [
        'template_map' => [],
        'template_path_stack' => [
            __DIR__ . '/../view'
        ]
    ]
];