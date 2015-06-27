<?php
/**
 * Config du module SbmFront
 *
 * @project sbm
 * @package module/SbmFront/config
 * @filesource module.config.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 22 avr. 2014
 * @version 2014-1
 */
if (! defined('MODULE_PATH')) {
    define('MODULE_PATH', dirname(__DIR__));
    define('ROOT_PATH', dirname(dirname(MODULE_PATH)));
}
if (! defined('APPL_NAME')) {
    define('APPL_NAME', 'School Bus Manager');
}

return array(
    'acl' => array(
        'resources' => array(
            'login' => array(
                'actions' => array(
                    'annuler' => array(
                        'allow' => array(
                            'roles' => array(
                                'guest'
                            )
                        )
                    ),
                    'confirm' => array(
                        'allow' => array(
                            'roles' => array(
                                'guest'
                            )
                        )
                    ),
                    'creer-compte' => array(
                        'allow' => array(
                            'roles' => array(
                                'guest'
                            )
                        )
                    ),
                    'login' => array(
                        'allow' => array(
                            'roles' => array(
                                'guest'
                            )
                        )
                    ),
                    'home-page' => array(
                        'allow' => array(
                            'roles' => array(
                                'guest'
                            )
                        )
                    ),
                    'mdp-demande' => array(
                        'allow' => array(
                            'roles' => array(
                                'guest'
                            )
                        )
                    ),
                    'email-change' => array(
                        'allow' => array(
                            'roles' => array(
                                'parent', 'consultation', 'transporteur', 'etablissement'
                            )
                        )
                    ),
                    'logout' => array(
                        'allow' => array(
                            'roles' => array(
                                'parent', 'consultation', 'transporteur', 'etablissement'
                            )
                        )
                    ),
                    'mdp-change' => array(
                        'allow' => array(
                            'roles' => array(
                                'parent', 'consultation', 'transporteur', 'etablissement'
                            )
                        )
                    ),
                    'mdp-reset' => array(
                        'allow' => array(
                            'roles' => array(
                                'parent'
                            )
                        )
                    ),
                    'modif-compte' => array(
                        'allow' => array(
                            'roles' => array(
                                'parent', 'consultation', 'transporteur', 'etablissement'
                            )
                        )
                    ),
                    'synchro-compte' => array(
                        'allow' => array(
                            'roles' => array(
                                'parent'
                            )
                        )
                    )
                )
            )
        )
    ),
    'service_manager' => array(
        'invokables' => array(),
        'factories' => array()
    ),
    'router' => array(
        'routes' => array(
            'home' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/[:action]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*'
                    ),
                    'defaults' => array(
                        'module' => __NAMESPACE__,
                        'controller' => 'SbmFront\Controller\Index',
                        'action' => 'index'
                    )
                ),
                'may_terminate' => true
            ),
            'login' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/login[/:action[/:id]]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[a-f0-9]{32}'
                    ),
                    'defaults' => array(
                        'module' => __NAMESPACE__,
                        'controller' => 'SbmFront\Controller\Login',
                        'action' => 'login'
                    )
                )
            )
        )
    ),
    'controllers' => array(
        'invokables' => array(
            'SbmFront\Controller\Index' => 'SbmFront\Controller\IndexController',
            'SbmFront\Controller\Login' => 'SbmFront\Controller\LoginController'
        )
    ),
    'view_helpers' => array(
        'factories' => array(
            'bienvenue' => 'SbmFront\Factory\View\Helper\BienvenueFactory'
        )
    ),
    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions' => true,
        'not_found_template' => 'error/404',
        'exception_template' => 'error/index',
        'template_map' => array(
            'layout/layout' => MODULE_PATH . '/view/layout/layout.phtml',
            'layout/header' => MODULE_PATH . '/view/layout/header.phtml',
            'layout/footer' => MODULE_PATH . '/view/layout/footer.phtml',
            'layout/stats' => MODULE_PATH . '/view/layout/stats.phtml',
            'error/404' => MODULE_PATH . '/view/error/404.phtml',
            'error/index' => MODULE_PATH . '/view/error/index.phtml'
        ),
        'template_path_stack' => array(
            MODULE_PATH . DIRECTORY_SEPARATOR . 'view'
        )
    )
);