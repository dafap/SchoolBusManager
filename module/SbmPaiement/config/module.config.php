<?php
/**
 * Paramètres de configuration du module SbmPaiement
 *
 * Précise le système bancaire utilisé ainsi que ses paramètres.
 * 
 * Pour SP+ il faut indiquer :
 * - vads_ctx_mode (TEST ou PRODUCTION)
 * - vads_site_id (donné au moment de l'adhésion à la plateforme)
 * - certificat pour TEST et pour PRODUCTION (à récupérer sur l'outil de gestion, menu Paramétrage / Boutique/[nom de la boutique] / Certificat
 * 
 * Pour Paybox il faut indiquer :
 * 
 * @project sbm
 * @package SbmPaiement/config
 * @filesource module.config.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 30 mars 2015
 * @version 2015-1
 */
use SbmPaiement\Controller\Service\IndexControllerFactory;
use SbmPaiement\Listener;

if (! defined('MODULE_PAIEMENT_PATH')) {
    define('MODULE_PAIEMENT_PATH', dirname(__DIR__));
    // define('ROOT_PATH', dirname(dirname(MODULE_PATH)));
}
return array(
    'acl' => array(
        'resources' => array(
            'sbmpaiement' => array(
                'allow' => array(
                    'roles' => array('parent')
                ),
                'actions' => array(
                    'notification' => array(
                        'allow' => array(
                            'roles' => array(
                                'guest'
                            )
                        )
                    ),
                    'liste' => array(
                        'allow' => array(
                            'roles' => array(
                                'secretariat'
                            )
                        )
                    ),
                    'voir' => array(
                        'allow' => array(
                            'roles' => array(
                                'secretariat'
                            )
                        )
                    )
                )
            )
        ),
    ),
    'paginator' => array(
        'count_per_page' => array(
            'nb_notifications' => 15
        )
    ),
    'service_manager' => array(
        'invokables' => array(
            'Sbm\AppelPaiement' => 'SbmPaiement\Service\Trigger'
        ),
        'factories' => array(
            Listener\PaiementOK::class => Listener\Service\PaiementOKFactory::class,
            Listener\ScolariteOK::class => Listener\Service\ScolariteOKFactory::class
        )
    ),
    'controllers' => array(
        'factories' => array(
            'SbmPaiement\Controller\Index' => IndexControllerFactory::class
        )
    ),
    'router' => array(
        'routes' => array(
            'sbmpaiement' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/paiement[/:action[/page/:page[/id/:id]]]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'page' => '[0-9]+',
                        'id' => '[0-9]+'
                    ),
                    'defaults' => array(
                        'module' => 'SbmPaiement',
                        'controller' => 'SbmPaiement\Controller\Index',
                        'action' => 'liste'
                    )
                ),
                'may_terminate' => true
            )
        )
    ),
    'sbm' => array(
        'paiement' => array(
            'path_filelog' => realpath(__DIR__ . '/../../../data/logs')
        )
    )
);