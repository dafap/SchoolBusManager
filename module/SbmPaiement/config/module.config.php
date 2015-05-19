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
                )
            )
        ),
    ),
    'liste' => array(
        'paginator' => array(
            'nb_paiements' => 15
        )
    ),
    'service_manager' => array(
        'invokables' => array(
            'Sbm\AppelPaiement' => 'SbmPaiement\Service\Trigger'
        )
    ),
    'controllers' => array(
        'invokables' => array(
            'SbmPaiement\Controller\Index' => 'SbmPaiement\Controller\IndexController'
        )
    ),
    'router' => array(
        'routes' => array(
            'sbmpaiement' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/paiement[/:action[/:page[/:id]]]',
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