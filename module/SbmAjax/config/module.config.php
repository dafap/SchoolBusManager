<?php
/**
 * Fichier de configuration du module
 * 
 * Les arguments passés par get sont de la forme key1:value1/key2:value2...
 *
 * @project sbm
 * @package SbmAjax/config
 * @filesource module.config.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 5 mai 2015
 * @version 2015-1
 */
$controllers = array(
    \SbmAjax\Controller\EleveController::ROUTE => 'SbmAjax\Controller\EleveController',
    \SbmAjax\Controller\TransportController::ROUTE => 'SbmAjax\Controller\TransportController',
    \SbmAjax\Controller\FinanceController::ROUTE => 'SbmAjax\Controller\FinanceController',
    \SbmAjax\Controller\ParentController::ROUTE => 'SbmAjax\Controller\ParentController'
    
);
$routes = array();
foreach ($controllers as $key => $value) {
    $routes[$key] = array(
        'type' => 'segment',
        'options' => array(
            'route' => "/$key" . '[/:action][/:args]',
            'constraints' => array(
                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                'args' => '[a-zA-Z][a-zA-Z0-9_-]*:[a-zA-Z0-9_\-\+]+(/[a-zA-Z][a-zA-Z0-9_-]*:[a-zA-Z0-9_\-\+]+)*'
            ),
            'defaults' => array(
                'module' => 'SbmAjax',
                'controller' => $key,
                'action' => 'index'
            )
        ),
        'may_terminate' => false
    );
}
return array(
    'acl' => array(
        'resources' => array(
            \SbmAjax\Controller\EleveController::ROUTE => array(
                'allow' => array(
                    'roles' => array('parent')
                )
            ),
            \SbmAjax\Controller\FinanceController::ROUTE => array(
                'allow' => array(
                    'roles' => array('gestion')
                )
            ),
            \SbmAjax\Controller\TransportController::ROUTE => array(
                'allow' => array(
                    'roles' => array('gestion')
                )
            ),
            \SbmAjax\Controller\ParentController::ROUTE => array(
                'allow' => array(
                    'roles' => array('parent')
                )
            )
        ),
    ),
    'controllers' => array(
        // de préférence dans ce module, commencer les noms par jax (pour des routes commençant par ajax) et les laisser en minuscules
        'invokables' => $controllers
    ),
    'router' => array(
        'routes' => $routes
    ),
    'view_manager' => array(
        'template_map' => array(),
        'template_path_stack' => array(
            __DIR__ . '/../view'
        ),
        //'strategies' => array(
        //    'ViewJsonStrategy'
        //)
    )
);
