<?php
/**
 * Module SbmUser
 *
 * @project sbm
 * @package module/SbmUser/config
 * @filesource module.config.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 22 janv. 2014
 * @version 2014-1
 */

return array(
    'controllers' => array(
        'invokables' => array(
            'SbmUser\Controller\Index' => 'SbmUser\Controller\IndexController',
        )
    ),
    'router' => array(
        'routes' => array(
            'sbmuser' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/user[/:action[/:page][/:id]]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'page' => '[0-9]+'
                    ),
                    'defaults' => array(
                        'module' => 'SbmUser',
                        'controller' => 'SbmUser\Controller\Index',
                        'action' => 'index'
                    )
                ),
                'may_terminate' => true,
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