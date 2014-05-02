<?php
/**
 * Module SbmInstallation
 *
 * @project sbm
 * @package module/SbmInstallation/config
 * @filesource module.config.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 22 janv. 2014
 * @version 2014-1
 */

return array(
    'controllers' => array(
        'invokables' => array(
            'SbmInstallation\Controller\Index' => 'SbmInstallation\Controller\IndexController',
        ),
    ),
    'router' => array(
        'routes' => array(
            'install' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/install[/:action]',
                    'contraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ),
                    'defaults' => array(
                        'controller' => 'SbmInstallation\Controller\Index',
                        'action' => 'index',
                    )
                )
            )
        )
    ),
    'view_manager' => array(
        'template_map' => array(),
        'template_path_stack' => array(
            'install' => __DIR__ . '/../view',
        ),
    ),
);