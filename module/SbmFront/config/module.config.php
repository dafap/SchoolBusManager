<?php
/**
 * Config du module SbmFront
 *
 * @project sbm
 * @package module/SbmFront/config
 * @filesource module.config.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 7 avr. 2016
 * @version 2016-2
 */
use SbmFront\Controller;
use SbmFront\Model\Responsable\Responsable;
use SbmFront\Model\Responsable\Service\ResponsableManager;
use SbmFront\Form\Service\CreerCompteFactory;
use SbmFront\Form\CreerCompte;
use SbmFront\Form\Service\EmailChangeFactory;
use SbmFront\Form\EmailChange;
use SbmFront\Form\Service\LoginFactory;
use SbmFront\Form\Login;
use SbmFront\Form\Service\MdpDemandeFactory;
use SbmFront\Form\MdpDemande;
use SbmFront\Form;


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
                                'parent', 'secretariat', 'transporteur', 'etablissement'
                            )
                        )
                    ),
                    'logout' => array(
                        'allow' => array(
                            'roles' => array(
                                'parent', 'secretariat', 'transporteur', 'etablissement'
                            )
                        )
                    ),
                    'mdp-change' => array(
                        'allow' => array(
                            'roles' => array(
                                'parent', 'secretariat', 'transporteur', 'etablissement'
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
                                'parent', 'secretariat', 'transporteur', 'etablissement'
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
    'form_manager' => [
        'invokables' => [
            Form\MdpChange::class => Form\MdpChange::class,
            Form\MdpFirst::class => Form\MdpFirst::class,
            Form\ModifCompte::class => Form\ModifCompte::class
        ],
        'factories' => [
            CreerCompte::class => CreerCompteFactory::class,
            EmailChange::class => EmailChangeFactory::class,
            Login::class => LoginFactory::class,
            MdpDemande::class => MdpDemandeFactory::class
        ]
    ],
    'service_manager' => [
        'invokables' => [],
        'factories' => [
            SbmFront\Form\Login::class => SbmFront\Form\Service\LoginFactory::class,
            ResponsableManager::class => ResponsableManager::class,           
        ]
    ],
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
                        'controller' => Controller\IndexController::class,
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
                        'controller' => Controller\LoginController::class,
                        'action' => 'login'
                    )
                )
            )
        )
    ),
    'controllers' => array(
        'factories' => array(
            Controller\IndexController::class => Controller\Service\IndexControllerFactory::class,
            Controller\LoginController::class => Controller\Service\LoginControllerFactory::class
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