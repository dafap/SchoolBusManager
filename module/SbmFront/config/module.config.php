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
    'zfcuser' => array(),
    'router' => array(
        'routes' => array(
            'home' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/[:action][/:page]',
                    'defaults' => array(
                        'module' => __NAMESPACE__,
                        'controller' => 'SbmFront\Controller\Index',
                        'action' => 'index'
                    )
                ),
                'may_terminate' => true,
            ),
        )
    ),
    'controllers' => array(
        'invokables' => array(
            'SbmFront\Controller\Index' => 'SbmFront\Controller\IndexController'
        )
    ),
    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions' => true,
        'not_found_template' => 'error/404',
        'exception_template' => 'error/index',
        'template_map' => array(
            'layout/header' => MODULE_PATH . '/view/layout/header.phtml',
            'layout/footer' => MODULE_PATH . '/view/layout/footer.phtml',
            'layout/stats' => MODULE_PATH . '/view/layout/stats.phtml',
            'error/404' => MODULE_PATH . '/view/error/404.phtml',
            'error/index' => MODULE_PATH . '/view/error/index.phtml'
        ),
        'template_path_stack' => array(            
            MODULE_PATH . DIRECTORY_SEPARATOR . 'view'
        )
    ),
)
;