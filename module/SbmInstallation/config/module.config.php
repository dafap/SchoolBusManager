<?php
/**
 * Module SbmInstallation
 *
 * @project sbm
 * @package module/SbmInstallation/config
 * @filesource module.config.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 2 juin 2020
 * @version 2020-2.6.0
 */
use SbmBase\Model\StdLib;
use SbmCommun\Model\Image\Image;
use SbmInstallation\Controller;
use SbmInstallation\Form;
use SbmInstallation\Model\View\Helper as ViewHelper;

return [
    'acl' => [
        'resources' => [
            'sbminstall' => [
                'allow' => [
                    'roles' => [
                        'sadmin'
                    ]
                ],
                'actions' => [
                    'gestion-images' => [
                        'allow' => [
                            'roles' => [
                                'admin'
                            ]
                        ]
                    ],
                    'upload-image' => [
                        'allow' => [
                            'roles' => [
                                'admin'
                            ]
                        ]
                    ],
                    'edit-css' => [
                        'allow' => [
                            'roles' => [
                                'admin'
                            ]
                        ]
                    ],
                    'edit-clever-sms' => [
                        'allow' => [
                            'roles' => [
                                'admin'
                            ]
                        ]
                    ],
                    'edit-esendex' => [
                        'allow' => [
                            'roles' => [
                                'admin'
                            ]
                        ]
                    ],
                    'edit-client' => [
                        'allow' => [
                            'roles' => [
                                'admin'
                            ]
                        ]
                    ],
                    'edit-page' => [
                        'allow' => [
                            'roles' => [
                                'admin'
                            ]
                        ]
                    ],
                    'edit-sites' => [
                        'allow' => [
                            'roles' => [
                                'admin'
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ],
    'sbm' => [
        'img' => [
            'path' => [
                'system' => StdLib::findParentPath(__DIR__, 'public/img'),
                'tmpuploads' => './data/tmpuploads',
                'url' => '/img/'
            ],
            'log' => [
                'path_filelog' => StdLib::findParentPath(__DIR__, 'data/logs'),
                'filename' => 'photo_error.log'
            ],
            'cacher' => [ // liste des images utilisées dans SBM qu'il ne faut pas
                           // administrer
                '_blank.png',
                'famfamfam-icons.png',
                'favicon.ico',
                'sbm-logo.gif'
            ],
            'administrer' => [
                /**
                 * liste des images à administrer - label : explication de la nature de
                 * l'image - taille : prend les valeurs FULL_SIZE (taille réelle de
                 * l'image ; width et height sont ignorés), FIXED_SIZE (taille fixe ;
                 * width et height sont en pt) PROPORTIONAL_SIZE (taille proportionnelle ;
                 * sera ramenée à la taille indiquée en pt) - width : en pt - height : en
                 * pt
                 */
                'bandeau-sbm.png' => [
                    // utilisé dans CSS .page1 #header #bandeau
                    'label' => 'Bandeau de haut de page du site',
                    'taille' => Image::FIXED_SIZE,
                    'width' => 1170,
                    'height' => 105
                ],
                'bas-de-mail-service-gestion.png' => [
                    // utilisé dans
                    // \SbmGestion\Controller\EleveController::responsableMailAction()
                    // et \SbmMail\Controller\IndexController::lastDayChangesAction()
                    'label' => 'Bas de mail personnalisé service transport',
                    'taille' => Image::PROPORTIONAL_SIZE,
                    'width' => 427,
                    'height' => 128
                ],
                'bas-de-mail-transport-scolaire.png' => [
                    // utilisé dans \SbmFront\Controller\LoginController::mdpDemande(),
                    // creerCompteAction() et
                    // \SbmGestion\Controller\EleveController::responsableLogerAction()
                    'label' => 'Bas de mail impersonnel avec logo TS',
                    'taille' => Image::PROPORTIONAL_SIZE,
                    'width' => 427,
                    'height' => 128
                ],
                'logotransportscolaire.png' => [
                    // utilisé uniquement dans les documents
                    'label' => 'Logo transport scolaire des documents à imprimer',
                    'taille' => Image::PROPORTIONAL_SIZE,
                    'width' => 85,
                    'height' => 48
                ],
                'logocarteetablissements.png' => [
                    // utilisé dans SbmFront/view/sbm-front/index/index-avant.phtml,
                    // index-pendant.phtml et index-après.phtml
                    'label' => 'Image de la carte en page d\'accueil',
                    'taille' => Image::FULL_SIZE,
                    'width' => 0,
                    'height' => 0
                ],
                'logocartegauche.png' => [
                    // utilisé dans \SbmPdf\Model\Tcpdf::templateDocBodyMethod3Picture()
                    'label' => 'Logo carte gauche',
                    'taille' => Image::PROPORTIONAL_SIZE,
                    'width' => 48,
                    'height' => 65
                ],
                'logocartedroite.png' => [
                    // utilisé dans \SbmPdf\Model\Tcpdf::templateDocBodyMethod3Picture()
                    'label' => 'Logo carte droite',
                    'taille' => Image::PROPORTIONAL_SIZE,
                    'width' => 85,
                    'height' => 48
                ]
            ]
        ]
    ],
    'controllers' => [
        'factories' => [
            Controller\IndexController::class => Controller\Service\IndexControllerFactory::class
        ]
    ],
    'router' => [
        'routes' => [
            'sbminstall' => [

                // 'type' => 'literal',
                'type' => 'segment',
                'options' => [

                    // 'route' => '/install',
                    'route' => '/install[/:action]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*'
                    ],
                    'defaults' => [
                        'module' => 'SbmInstallation',
                        'controller' => Controller\IndexController::class,
                        'action' => 'index'
                    ]
                ],
                'may_terminate' => true
            ]
        ]
    ],
    'service_manager' => [
        'invokables' => [
            \SbmInstallation\Model\Theme::class => \SbmInstallation\Model\Theme::class
        ]
    ],
    'form_manager' => [
        'invokables' => [
            Form\DumpTables::class => Form\DumpTables::class,
            Form\FileContent::class => Form\FileContent::class
        ]
    ],
    'view_helpers' => [
        'aliases' => [
            'sbmArrayN2Idx' => ViewHelper\SbmArrayN2Idx::class,
            'sbmarrayN2Idx' => ViewHelper\SbmArrayN2Idx::class,
            'sbmArrayN2Asso' => ViewHelper\SbmArrayN2Asso::class,
            'sbmarrayN2Asso' => ViewHelper\SbmArrayN2Asso::class
        ],
        'invokables' => [
            ViewHelper\SbmArrayN2Idx::class => ViewHelper\SbmArrayN2Idx::class,
            ViewHelper\SbmArrayN2Asso::class => ViewHelper\SbmArrayN2Asso::class
        ]
    ],
    'view_manager' => [
        'template_map' => [],
        'template_path_stack' => [
            __DIR__ . '/../view'
        ]
    ]
];
