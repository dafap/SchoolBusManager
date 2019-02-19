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
 * @date 5 avr. 2018
 * @version 2018-2.4.0
 */
use SbmMail\Form\Mail;
use SbmMail\Model;
use SbmMail\Controller;

return [
    'acl' => [
        'resources' => [
            'SbmMail' => [
                'allow' => [
                    'roles' => [
                        'parent',
                        'transporteur',
                        'etablissement',
                        'secretariat'
                    ]
                ],
                'actions' => [
                    'last-day-changes' => [
                        'allow' => [
                            'roles' => [
                                'guest'
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ],
    'sbm' => [
        'mail' => [
            'transport' => [
                /*
                 * 'mode' => 'smtp', // 'smtp' ou 'sendmail'
                 * 'smtpOptions' => [
                 * 'host' => 'smtp.free.fr',
                 * 'port' => '25', // 25, 587 ou 2525 si connexion TLS ; 465 ou 25025 si connexion SSL
                 * 'connexion_class' => 'plain',
                 * 'connexion_config' => [
                 * 'username' => '',
                 * 'password' => '',
                 * 'from' => ''
                 * ]
                 * ],
                 */
                'transportSsl' => [
                    'use_ssl' => false,
                    'connection_type' => 'tls'
                ]
            ], // ssl | tls
            
            'message' => [
                'type' => 'text/html',
                'html_encoding' => \Zend\Mime\Mime::ENCODING_8BIT,
                'message_encoding' => 'UTF-8'
            ]
        ]
    ],
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
    'controllers' => [
        'factories' => [
            Controller\IndexController::class => Controller\Service\IndexControllerFactory::class
        ]
    ],
    'router' => [
        'routes' => [
            'SbmMail' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/mail[/:action]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*'
                    ],
                    'defaults' => [
                        'module' => 'SbmMail',
                        'controller' => Controller\IndexController::class,
                        'action' => 'index'
                    ]
                ],
                'may_terminate' => true
            ]
        ]
    ],
    'view_manager' => [
        'template_map' => [],
        'template_path_stack' => [
            __DIR__ . '/../view'
        ]
    ]
];
 