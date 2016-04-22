<?php
/**
 * Configuration du module SbmPortail
 *
 * Définition des routes et des acl
 * 
 * @project sbm
 * @package SbmPortail/config
 * @filesource module.config.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 30 juil. 2015
 * @version 2015-1
 */
use SbmPortail\Controller;

return array(
    'acl' => array(
        'resources' => array(
            'sbmportail' => array(
                'allow' => array(
                    'roles' => array(
                        'transporteur', 'etablissement', 'secretariat', 'gestion'
                    )
                )
            )
        )
    ),
    'controllers' => array(
        'factories' => array(
            Controller\IndexController::class => Controller\Service\IndexControllerFactory::class
        )
    ),
    'router' => array(
        'routes' => array(
            'sbmportail' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/portail[/:action[/:page][/:id]]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'page' => '[0-9]+',
                        'id' => '[a-zA-Z0-9][a-zA-Z0-9_-]*'
                    ),
                    'defaults' => array(
                        'module' => 'SbmPortail',
                        'controller' => Controller\IndexController::class,
                        'action' => 'index'
                    )
                ),
                'may_terminate' => true
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