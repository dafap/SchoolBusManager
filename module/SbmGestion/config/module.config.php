<?php
/**
 * Module SbmGestion
 *
 * @project sbm
 * @package module/SbmGestion/config
 * @filesource module.config.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 22 janv. 2014
 * @version 2014-1
 */
return array(
    'liste' => array(
        'paginator' => array(
            'nb_circuit_pagination' => 10,
            'nb_classe_pagination' => 8,
            'nb_commune_pagination' => 10,
            'nb_eleve_pagination' => 10,
            'nb_etablissement_pagination' => 5,
            'nb_responsable_pagination' => 10,
            'nb_service_pagination' => 8,
            'nb_station_pagination' => 8,
            'nb_tarif_pagination' => 10,
            'nb_transporteur_pagination' => 8,
        )
    ),
    'controllers' => array(
        'invokables' => array(
            'SbmGestion\Controller\Index' => 'SbmGestion\Controller\IndexController',
            'SbmGestion\Controller\Eleve' => 'SbmGestion\Controller\EleveController',
            'SbmGestion\Controller\Finance' => 'SbmGestion\Controller\FinanceController',
            'SbmGestion\Controller\Transport' => 'SbmGestion\Controller\TransportController',
            'SbmGestion\Controller\AnneeScolaire' => 'SbmGestion\Controller\AnneeScolaireController',
            'SbmGestion\Controller\Simulation' => 'SbmGestion\Controller\SimulationController',
            'SbmGestion\Controller\Config' => 'SbmGestion\Controller\ConfigController'
        )
    ),
    'router' => array(
        'routes' => array(
            'sbmgestion' => array(
                'type' => 'literal',
                'options' => array(
                    'route' => '/gestion',
                    'defaults' => array(
                        'controller' => 'SbmGestion\Controller\Index',
                        'action' => 'index'
                    )
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'eleve' => array( // gestion des élèves
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/eleve[/:action[/:page][/:id]]',
                            'constraints' => array(
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'page' => '[0-9]+'
                            ),
                            'defaults' => array(
                                'module' => 'SbmGestion',
                                'controller' => 'SbmGestion\Controller\Eleve',
                                'action' => 'index'
                            )
                        )
                    ),
                    'finance' => array( // gestion financière
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/finance[/:action[/:page][/:id]]',
                            'constraints' => array(
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'page' => '[0-9]+'
                            ),
                            'defaults' => array(
                                'module' => 'SbmGestion',
                                'controller' => 'SbmGestion\Controller\Finance',
                                'action' => 'index'
                            )
                        )
                    ),
                    'transport' => array( // gestion des données du réseau de transport
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/transport[/:action[/:page][/:id]]',
                            'constraints' => array(
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'page' => '[0-9]+'
                            ),
                            'defaults' => array(
                                'module' => 'SbmGestion',
                                'controller' => 'SbmGestion\Controller\Transport',
                                'action' => 'index'
                            )
                        )
                    ),
                    'anneescolaire' => array( // gestion de l'année scolaire
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/anneescolaire',
                            'defaults' => array(
                                'module' => 'SbmGestion',
                                'controller' => 'SbmGestion\Controller\AnneeScolaire',
                                'action' => 'index'
                            )
                        )
                    ),
                    'simul' => array( // simulation d'une nouvelle organisation
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/simul',
                            'defaults' => array(
                                'module' => 'SbmGestion',
                                'controller' => 'SbmGestion\Controller\Simulation',
                                'action' => 'index'
                            )
                        )
                    ),
                    'config' => array( // gestion de la configuration et des paramètres
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/config',
                            'defaults' => array(
                                'module' => 'SbmGestion',
                                'controller' => 'SbmGestion\Controller\Config',
                                'action' => 'index'
                            )
                        )
                    )
                )
            )
        )
    ),
    'view_manager' => array(
        'template_map' => array(),
        'template_path_stack' => array(
            __DIR__ . '/../view'
        )
    )
);