<?php
/**
 * Paramétrage du module d'envoi de mails
 *
 * Les paramètres relatifs au site et au client sont dans config/autoload/sbm.local.php
 * 
 * @project sbm
 * @package SbmMail/config
 * @filesource module.config.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 18 août 2016
 * @version 2016-2.2.0
 */
use SbmMail\Form\Mail;
use SbmMail\Model;
use SbmMail\Controller;

return array(
    'acl' => array(
        'resources' => array(
            'SbmMail' => array(
                'allow' => array(
                    'roles' => array(
                        'parent',
                        'transporteur',
                        'etablissement',
                        'secretariat'
                    )
                ),
                'actions' => array(
                    'last-day-changes' => array(
                        'allow' => array(
                            'roles' => array(
                                'guest'
                            )
                        )
                    )
                )
            )
        )
    ),
    'sbm' => array(
        'mail' => array(
            'transport' => array(
                /*'mode' => 'smtp', // 'smtp' ou 'sendmail'
                'smtpOptions' => array(
                    'host' => 'smtp.free.fr',
                    'port' => '25', // 25, 587 ou 2525 si connexion TLS ; 465 ou 25025 si connexion SSL
                    'connexion_class' => 'plain',
                    'connexion_config' => array(
                        'username' => '',
                        'password' => '',
                        'from' => ''
                    )
                ),*/
                'transportSsl' => array(
                    'use_ssl' => false,
                    'connection_type' => 'tls'
                )
            ) // ssl | tls

            ,
            'message' => array(
                'type' => 'text/html',
                'html_encoding' => \Zend\Mime\Mime::ENCODING_8BIT,
                'message_encoding' => 'UTF-8'
            )
        )
    ),
    'form_manager' => [
        'invokables' => [
            'SbmMail\MailForm' => Mail::class
        ]
    ],
    'service_manager' => [
        'factories' => [
            Model\EnvoiMail::class => Model\Service\EnvoiMailFactory::class,
            'SbmMail\Config' => Model\Service\ConfigServiceFactory::class
        ]
    ],
    'controllers' => array(
        'factories' => array(
            Controller\IndexController::class => Controller\Service\IndexControllerFactory::class
        )
    ),
    'router' => array(
        'routes' => array(
            'SbmMail' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/mail[/:action]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*'
                    ),
                    'defaults' => array(
                        'module' => 'SbmMail',
                        'controller' => Controller\IndexController::class,
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
 