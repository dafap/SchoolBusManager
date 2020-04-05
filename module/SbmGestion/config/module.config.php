<?php
/**
 * Module SbmGestion
 *
 * @project sbm
 * @package module/SbmGestion/config
 * @filesource module.config.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr]
 * @date 5 avr. 2020
 * @version 2020-2.6.0
 */
use SbmGestion\Controller;
use SbmGestion\Form;
use SbmGestion\Controller\Service;
use SbmGestion\Model\Cartes;
use SbmGestion\Model\Db\Service as SbmGestionDbService;
use SbmGestion\Model\Photos;
use SbmGestion\Model\View\Helper as ViewHelper;

return [
    'acl' => [
        'resources' => [
            'sbmgestion' => [
                'allow' => [
                    'roles' => [
                        'gestion'
                    ]
                ]
            ]
        ]
    ],
    'paginator' => [
        'count_per_page' => [
            'nb_circuits' => 15,
            'nb_classes' => 15,
            'nb_communes' => 15,
            'nb_eleves' => 10,
            'nb_etablissements' => 15,
            'nb_organismes' => 15,
            'nb_paiements' => 10,
            'nb_responsables' => 15,
            'nb_services' => 15,
            'nb_stations' => 15,
            'nb_tarifs' => 15,
            'nb_transporteurs' => 15
        ]
    ],
    'db_manager' => [
        'factories' => [
            'Sbm\Db\Simulation\Prepare' => SbmGestionDbService\Simulation\Prepare::class,
            'Sbm\Db\Circuit\Liste' => SbmGestionDbService\Circuit\Liste::class,
            'Sbm\Db\Eleve\Liste' => SbmGestionDbService\Eleve\Liste::class,
            'Sbm\Db\Eleve\Effectif' => SbmGestionDbService\Eleve\Effectif::class,
            'Sbm\Db\Eleve\EffectifCircuits' => SbmGestionDbService\Eleve\EffectifCircuits::class,
            'Sbm\Db\Eleve\EffectifClasses' => SbmGestionDbService\Eleve\EffectifClasses::class,
            'Sbm\Db\Eleve\EffectifCommunes' => SbmGestionDbService\Eleve\EffectifCommunes::class,
            'Sbm\Db\Eleve\EffectifEtablissements' => SbmGestionDbService\Eleve\EffectifEtablissements::class,
            'Sbm\Db\Eleve\EffectifEtablissementsServices' => SbmGestionDbService\Eleve\EffectifEtablissementsServices::class,
            'Sbm\Db\Eleve\EffectifLots' => SbmGestionDbService\Eleve\EffectifLots::class,
            'Sbm\Db\Eleve\EffectifLotsServices' => SbmGestionDbService\Eleve\EffectifLotsServices::class,
            'Sbm\Db\Eleve\EffectifOrganismes' => SbmGestionDbService\Eleve\EffectifOrganismes::class,
            'Sbm\Db\Eleve\EffectifServices' => SbmGestionDbService\Eleve\EffectifServices::class,
            'Sbm\Db\Eleve\EffectifServicesEtablissements' => SbmGestionDbService\Eleve\EffectifServicesEtablissements::class,
            'Sbm\Db\Eleve\EffectifStations' => SbmGestionDbService\Eleve\EffectifStations::class,
            'Sbm\Db\Eleve\EffectifStationsServices' => SbmGestionDbService\Eleve\EffectifStationsServices::class,
            'Sbm\Db\Eleve\EffectifTarifs' => SbmGestionDbService\Eleve\EffectifTarifs::class,
            'Sbm\Db\Eleve\EffectifTransporteurs' => SbmGestionDbService\Eleve\EffectifTransporteurs::class,
            'Sbm\Db\Eleve\EffectifTransporteursServices' => SbmGestionDbService\Eleve\EffectifTransporteursServices::class,
            'Sbm\Db\Service\EffectifLots' => SbmGestionDbService\Service\EffectifLots::class,
            'Sbm\Db\Service\EffectifTransporteurs' => SbmGestionDbService\Service\EffectifTransporteurs::class,
            Cartes\Cartes::class => Cartes\CartesFactory::class,
            Photos\Photos::class => Photos\PhotosFactory::class
        ]
    ],
    'form_manager' => [
        'invokables' => [
            Form\Eleve\AddElevePhase1::class => Form\Eleve\AddElevePhase1::class,
            Form\Eleve\AddElevePhase2::class => Form\Eleve\AddElevePhase2::class,
            Form\Eleve\EditForm::class => Form\Eleve\EditForm::class,
            Form\Eleve\PriseEnChargePaiement::class => Form\Eleve\PriseEnChargePaiement::class,
            Form\Finances\BordereauRemiseValeurChoix::class => Form\Finances\BordereauRemiseValeurChoix::class,
            Form\Finances\BordereauRemiseValeurCreer::class => Form\Finances\BordereauRemiseValeurCreer::class,
            Form\Finances\FinancePaiementSuppr::class => Form\Finances\FinancePaiementSuppr::class,
            Form\EtablissementServiceSuppr::class => Form\EtablissementServiceSuppr::class,
            Form\ModifHoraires::class => Form\ModifHoraires::class,
            Form\SelectionCartes::class => Form\SelectionCartes::class,
            Form\SelectionPhotos::class => Form\SelectionPhotos::class,
            Form\Simulation::class => Form\Simulation::class
        ]
    ],
    'controllers' => [
        'invokables' => [
            Controller\ConfigController::class => Controller\ConfigController::class
        ],
        'factories' => [
            Controller\AnneeScolaireController::class => Service\AnneeScolaireControllerFactory::class,
            Controller\EleveController::class => Service\EleveControllerFactory::class,
            Controller\EleveGestionController::class => Service\EleveGestionControllerFactory::class,
            Controller\FinanceController::class => Service\FinanceControllerFactory::class,
            Controller\IndexController::class => Service\IndexControllerFactory::class,
            Controller\StatistiquesController::class => Service\StatistiquesControllerFactory::class,
            Controller\TransportController::class => Service\TransportControllerFactory::class
        ]
    ],
    'router' => [
        'routes' => [
            'sbmgestion' => [
                'type' => 'literal',
                'options' => [
                    'route' => '/gestion',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action' => 'index'
                    ]
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'eleve' => [ // gestion des élèves
                        'type' => 'segment',
                        'options' => [
                            'route' => '/eleve[/:action[/page/:page][/id/:id]]',
                            'constraints' => [
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'page' => '[0-9]+',
                                'id' => '[0-9]+'
                            ],
                            'defaults' => [
                                'module' => 'SbmGestion',
                                'controller' => Controller\EleveController::class,
                                'action' => 'index'
                            ]
                        ]
                    ],
                    'gestioneleve' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => '/gestioneleve[/:action[/:page][/:id]]',
                            'constraints' => [
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'page' => '[0-9]+',
                                'id' => '[0-9]+'
                            ],
                            'defaults' => [
                                'module' => 'SbmGestion',
                                'controller' => Controller\EleveGestionController::class,
                                'action' => 'index'
                            ]
                        ]
                    ],
                    'finance' => [ // gestion financière
                        'type' => 'segment',
                        'options' => [
                            'route' => '/finance[/:action[/page/:page][/id/:id]]',
                            'constraints' => [
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'page' => '[0-9]+',
                                'id' => '[a-zA-Z0-9]+'
                            ],
                            'defaults' => [
                                'module' => 'SbmGestion',
                                'controller' => Controller\FinanceController::class,
                                'action' => 'index'
                            ]
                        ]
                    ],
                    'transport' => [ // gestion des données du réseau de transport
                        'type' => 'segment',
                        'options' => [
                            'route' => '/transport[/:action[/page/:page][/pr/:pr][/id/:id]]',
                            'constraints' => [
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'page' => '[0-9]+',
                                'pr' => '[0-9]+',
                                'id' => '[0-9]+'
                            ],
                            'defaults' => [
                                'module' => 'SbmGestion',
                                'controller' => Controller\TransportController::class,
                                'action' => 'index'
                            ]
                        ]
                    ],
                    'anneescolaire' => [ // gestion de l'année scolaire
                        'type' => 'segment',
                        'options' => [
                            'route' => '/anneescolaire[/:action][/:millesime][/:id]',
                            'constraints' => [
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'millesime' => '[0-9]{4}',
                                'id' => '[0-9]+'
                            ],
                            'defaults' => [
                                'module' => 'SbmGestion',
                                'controller' => Controller\AnneeScolaireController::class,
                                'action' => 'index'
                            ]
                        ]
                    ],
                    'statistiques' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => '/statistiques[/:action[/id/:id]]',
                            'constraints' => [
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id' => '[0-9]+'
                            ],
                            'defaults' => [
                                'module' => 'SbmGestion',
                                'controller' => Controller\StatistiquesController::class,
                                'action' => 'index'
                            ]
                        ]
                    ],
                    'config' => [ // gestion de la configuration et des paramètres
                        'type' => 'segment',
                        'options' => [
                            'route' => '/config[/:action]',
                            'constraints' => [
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*'
                            ],
                            'defaults' => [
                                'module' => 'SbmGestion',
                                'controller' => Controller\ConfigController::class,
                                'action' => 'index'
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ],
    'view_helpers' => [
        'invokables' => [
            'designationService' => ViewHelper\DesignationService::class
        ],
        'factories' => [
            'menuRapports' => ViewHelper\MenuRapports::class,
            'printServices' => ViewHelper\Services::class,
            'printStations' => ViewHelper\Stations::class
        ]
    ],
    'view_manager' => [
        'template_map' => [],
        'template_path_stack' => [
            __DIR__ . '/../view'
        ],
        'strategies' => [
            'ViewJsonStrategy'
        ]
    ]
];