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
    'acl' => array(
        'resources' => array(
            'sbminstall' => array(
                'allow' => array(
                    'roles' => array('sadmin')
                )
            )
        ),
    ),
    'service_manager' => array(
        'invokables' => array(
            'SbmInstallation\DumpTables' => 'SbmInstallation\Model\DumpTables'
        )
    ),
    'controllers' => array(
        'invokables' => array(
            'SbmInstallation\Controller\Index' => 'SbmInstallation\Controller\IndexController'
        )
    ),
    'router' => array(
        'routes' => array(
            'sbminstall' => array(
                
                // 'type' => 'literal',
                'type' => 'segment',
                'options' => array(
                    
                    // 'route' => '/install',
                    'route' => '/install[/:action]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*'
                    ),
                    'defaults' => array(
                        'module' => 'SbmInstallation',
                        'controller' => 'SbmInstallation\Controller\Index',
                        'action' => 'index'
                    )
                ),
                'may_terminate' => true
            )
            // 'child_routes' => array(
            // 'create-tables' => array(
            // 'type' => 'literal',
            // 'options' => array(
            // 'route' => '/create-tables',
            // 'defaults' => array(
            // 'module' => 'SbmInstallation',
            // 'controller' => 'SbmInstallation\Controller\Index',
            // 'action' => 'create'
            // )
            // )
            // )
            // )
            
        )
    ),
    'view_manager' => array(
        'template_map' => array(),
        'template_path_stack' => array(
            __DIR__ . '/../view'
        )
    )
);  
