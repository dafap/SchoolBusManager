<?php
/**
 * Module SbmParent
 *
 * @project sbm
 * @package module/SbmParent/config
 * @filesource module.config.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 22 janv. 2014
 * @version 2014-1
 */
return array(
    'acl' => array(
        'resources' => array(
            'sbmparent' => array(
                'allow' => array(
                    'roles' => array(
                        'parent'
                    )
                )
            ),
            'sbmparentconfig' => array(
                'allow' => array(
                    'roles' => array(
                        'parent'
                    )
                )
            )
        )
    ),
    'controllers' => array(
        'invokables' => array(
            'SbmParent\Controller\Index' => 'SbmParent\Controller\IndexController',
            'SbmParent\Controller\Config' => 'SbmParent\Controller\ConfigController'
        )
    ),
    'router' => array(
        'routes' => array(
            'sbmparent' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/parent[/:action[/:page][/:id]]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'page' => '[0-9]+',
                        'id' => '[a-zA-Z0-9][a-zA-Z0-9_-]*'
                    ),
                    'defaults' => array(
                        'module' => 'SbmParent',
                        'controller' => 'SbmParent\Controller\Index',
                        'action' => 'index'
                    )
                ),
                'may_terminate' => true
            ),
            'sbmparentconfig' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/parent-config[/:action[/:page][/:id]]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'page' => '[0-9]+'
                    ),
                    'defaults' => array(
                        'module' => 'SbmParent',
                        'controller' => 'SbmParent\Controller\Config',
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