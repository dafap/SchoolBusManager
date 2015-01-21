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
    'annee-scolaire' => array(
        'modele' => array(
            array(
                'ordinal' => 1,
                'nature' => 'AS',
                'rang' => 1,
                'libelle' => '%as%',
                'description' => 'Année scolaire %as%',
                'exercice' => 0
            ),
            array(
                'ordinal' => 2,
                'nature' => 'VACA',
                'rang' => 1,
                'libelle' => 'Vacances de Toussaint',
                'description' => 'Vacances de Toussaint %as%',
                'exercice' => 0
            ),
            array(
                'ordinal' => 3,
                'nature' => 'VACA',
                'rang' => 2,
                'libelle' => 'Vacances de Noël',
                'description' => 'Vacances de Noël %as%',
                'exercice' => 0
            ),
            array(
                'ordinal' => 4,
                'nature' => 'VACA',
                'rang' => 3,
                'libelle' => 'Vacances d\'hiver',
                'description' => 'Vacances d\'hiver %as%',
                'exercice' => 0
            ),
            array(
                'ordinal' => 5,
                'nature' => 'VACA',
                'rang' => 4,
                'libelle' => 'Vacances de printemps',
                'description' => 'Vacances de printemps %as%',
                'exercice' => 0
            ),
            array(
                'ordinal' => 6,
                'nature' => 'PER',
                'rang' => 1,
                'libelle' => 'T1',
                'description' => '1er trimestre %as%',
                'exercice' => '%ex1%'
            ),
            array(
                'ordinal' => 7,
                'nature' => 'PER',
                'rang' => 2,
                'libelle' => 'T2',
                'description' => '2ème trimestre %as%',
                'exercice' => '%ex2%'
            ),
            array(
                'ordinal' => 8,
                'nature' => 'PER',
                'rang' => 3,
                'libelle' => 'T3',
                'description' => '3ème trimestre %as%',
                'exercice' => '%ex2%'
            ),
            array(
                'ordinal' => 9,
                'nature' => 'FACA',
                'rang' => 1,
                'libelle' => 'Facturation annuelle',
                'description' => '%libelle% %as%',
                'exercice' => '%ex1%'
            ),
            array(
                'ordinal' => 10,
                'nature' => 'FACT',
                'rang' => 1,
                'libelle' => 'Facturation T1',
                'description' => '%libelle% %as%',
                'exercice' => '%ex1%'
            ),
            array(
                'ordinal' => 11,
                'nature' => 'FACT',
                'rang' => 2,
                'libelle' => 'Facturation T2',
                'description' => '%libelle% %as%',
                'exercice' => '%ex2%'
            ),
            array(
                'ordinal' => 12,
                'nature' => 'FACT',
                'rang' => 3,
                'libelle' => 'Facturation T3',
                'description' => '%libelle% %as%',
                'exercice' => '%ex2%'
            ),
            array(
                'ordinal' => 13,
                'nature' => 'PREA',
                'rang' => 1,
                'libelle' => 'Prélèvement annuel',
                'description' => '%libelle% %as%',
                'exercice' => '%ex1%'
            ),
            array(
                'ordinal' => 14,
                'nature' => 'PREL',
                'rang' => 1,
                'libelle' => 'Prélèvement T1',
                'description' => '%libelle% %as%',
                'exercice' => '%ex1%'
            ),
            array(
                'ordinal' => 15,
                'nature' => 'PREL',
                'rang' => 2,
                'libelle' => 'Prélèvement T2',
                'description' => '%libelle% %as%',
                'exercice' => '%ex2%'
            ),
            array(
                'ordinal' => 16,
                'nature' => 'PREL',
                'rang' => 3,
                'libelle' => 'Prélèvement T3',
                'description' => '%libelle% %as%',
                'exercice' => '%ex2%'
            ),
            array(
                'ordinal' => 17,
                'nature' => 'RAP',
                'rang' => 1,
                'libelle' => 'Lettre de rappel 1',
                'description' => '%libelle% %as%',
                'exercice' => '%ex1%'
            ),
            array(
                'ordinal' => 18,
                'nature' => 'RAP',
                'rang' => 2,
                'libelle' => 'Lettre de rappel 2',
                'description' => '%libelle% %as%',
                'exercice' => '%ex2%'
            ),
            array(
                'ordinal' => 19,
                'nature' => 'RAP',
                'rang' => 3,
                'libelle' => 'Lettre de rappel 3',
                'description' => '%libelle% %as%',
                'exercice' => '%ex2%'
            )
        )
    ),
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
            'nb_transporteur_pagination' => 8
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
                            'route' => '/anneescolaire[/:action][/:millesime][/:id]',
                            'constraints' => array(
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'millesime' => '[0-9]{4}',
                                'id' => '[0-9]+'
                            ),
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