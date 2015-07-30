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
return array(
    'acl' => array(
        'resources' => array(
            'sbmportail' => array(
                'allow' => array(
                    'roles' => array(
                        'transporteur', 'gestion'
                    )
                )
            )
        )
    ),
    'controllers' => array(
        'invokables' => array(
            'SbmPortail\Controller\Index' => 'SbmPortail\Controller\IndexController'
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
                        'controller' => 'SbmPortail\Controller\Index',
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