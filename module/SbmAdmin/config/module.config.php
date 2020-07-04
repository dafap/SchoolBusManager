<?php
/**
 * Module SbmAdmin
 *
 * @project sbm
 * @package module/SbmAdmin/config
 * @filesource module.config.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr]
 * @date 3 juil. 2020
 * @version 2018-2.4.16
 */
use SbmAdmin\Form;
use SbmAdmin\Controller;
use SbmAdmin\Model\Db\Service\Responsable\Responsables;
use SbmAdmin\Model\Db\Service\User\Users;
use SbmAdmin\Model\Db\Service\Libelle\Liste;
use SbmAdmin\Model\View\Helper\RpiCommunes;
use SbmAdmin\Model\View\Helper\RpiEtablissements;
use SbmAdmin\Model\View\Helper\RpiClasses;
use SbmCommun\Form\Responsable;

return [
    'acl' => [
        'resources' => [
            'sbmadmin' => [
                'allow' => [
                    'roles' => [
                        'admin',
                        'sadmin'
                    ]
                ],
                'actions' => [
                    'user-ajout' => [
                        'allow' => [
                            'roles' => [
                                'gestion'
                            ]
                        ]
                    ],
                    'user-edit' => [
                        'allow' => [
                            'roles' => [
                                'gestion'
                            ]
                        ]
                    ],
                    'user-etablissement' => [
                        'allow' => [
                            'roles' => [
                                'gestion'
                            ]
                        ]
                    ],
                    'user-link' => [
                        'allow' => [
                            'roles' => [
                                'gestion'
                            ]
                        ]
                    ],
                    'user-liste' => [
                        'allow' => [
                            'roles' => [
                                'gestion'
                            ]
                        ]
                    ],
                    'user-mail' => [
                        'allow' => [
                            'roles' => [
                                'gestion'
                            ]
                        ]
                    ],
                    'user-pdf' => [
                        'allow' => [
                            'roles' => [
                                'gestion'
                            ]
                        ]
                    ],
                    'user-prepare_nouveaux_comptes' => [
                        'allow' => [
                            'roles' => [
                                'gestion'
                            ]
                        ]
                    ],
                    'user-suppr' => [
                        'allow' => [
                            'roles' => [
                                'gestion'
                            ]
                        ]
                    ],
                    'user-transporteur' => [
                        'allow' => [
                            'roles' => [
                                'gestion'
                            ]
                        ]
                    ],
                    'rpi-ajout' => [
                        'allow' => [
                            'roles' => [
                                'gestion'
                            ]
                        ]
                    ],
                    'rpi-edit' => [
                        'allow' => [
                            'roles' => [
                                'gestion'
                            ]
                        ]
                    ],
                    'rpi-liste' => [
                        'allow' => [
                            'roles' => [
                                'gestion'
                            ]
                        ]
                    ],
                    'rpi-pdf' => [
                        'allow' => [
                            'roles' => [
                                'gestion'
                            ]
                        ]
                    ],
                    'rpi-suppr' => [
                        'allow' => [
                            'roles' => [
                                'gestion'
                            ]
                        ]
                    ],
                    'secteur-scolaire-ajout' => [
                        'allow' => [
                            'roles' => [
                                'gestion'
                            ]
                        ]
                    ],
                    'secteur-scolaire-liste' => [
                        'allow' => [
                            'roles' => [
                                'gestion'
                            ]
                        ]
                    ],
                    'secteur-scolaire-pdf' => [
                        'allow' => [
                            'roles' => [
                                'gestion'
                            ]
                        ]
                    ],
                    'secteur-scolaire-suppr' => [
                        'allow' => [
                            'roles' => [
                                'gestion'
                            ]
                        ]
                    ],
                    'simulation-etablissement-ajout' => [
                        'allow' => [
                            'roles' => [
                                'gestion'
                            ]
                        ]
                    ],
                    'simulation-etablissement-liste' => [
                        'allow' => [
                            'roles' => [
                                'gestion'
                            ]
                        ]
                    ],
                    'simulation-etablissement-pdf' => [
                        'allow' => [
                            'roles' => [
                                'gestion'
                            ]
                        ]
                    ],
                    'simulation-etablissement-suppr' => [
                        'allow' => [
                            'roles' => [
                                'gestion'
                            ]
                        ]
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