<?php
/**
 * Module SbmAdmin
 *
 * @project sbm
 * @package module/SbmAdmin/config
 * @filesource module.config.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 22 janv. 2014
 * @version 2014-1
 */
return array(
    'acl' => array(
        'resources' => array(
            'sbmadmin' => array(
                'allow' => array(
                    'roles' => array('admin', 'sadmin')
                )
            )
        ),
    ),
    'liste' => array(
        'paginator' => array(
            'nb_libelles' => 15,
            'nb_users' => 20
        )
    ),
    'service_manager' => array(
        'factories' => array(
            'Sbm\Db\Libelle\Liste' => 'SbmAdmin\Model\Db\Service\Libelle\Liste'
        )
    ),
    'controllers' => array(
        'invokables' => array(
            'SbmAdmin\Controller\Index' => 'SbmAdmin\Controller\IndexController',
        )
    ),
    'router' => array(
        'routes' => array(
            'sbmadmin' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/admin[/:action[/:page][/:id]]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'page' => '[0-9]+'
                    ),
                    'defaults' => array(
                        'module' => 'SbmAdmin',
                        'controller' => 'SbmAdmin\Controller\Index',
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