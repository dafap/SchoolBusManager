<?php
/**
 * Module SbmInstallation
 *
 * @project sbm
 * @package module/SbmInstallation/config
 * @filesource module.config.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 18 sept.2018
 * @version 2018-2.4.5
 */
use SbmBase\Model\StdLib;
use SbmCommun\Model\Image\Image;
use SbmInstallation\Controller;

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
                    ]
                ]
            ]
        ]
    ],
    'sbm' => [
        'img' => [
            'path' => [
                'system' => realpath(StdLib::findParentPath(__DIR__, 'public/img')),
                'tmpuploads' => './data/tmpuploads',
                'url' => '/img/'
            ],
            'cacher' => [ // liste des images utilisées dans SBM qu'il ne faut pas administrer
                '_blank.png',
                'famfamfam-icons.png',
                'favicon.ico',
                'sbm-logo.gif'
            ],
            'administrer' => [ 
                /**
                 * liste des images à administrer
                 * - label : explication de la nature de l'image
                 * - taille : prend les valeurs 
                 *      real  (taille réelle de l'image ; width et height sont ignorés),
                 *      fixe  (taille fixe ; width et height sont en pt)
                 *      scale (taille proportionnelle ; sera ramenée à la taille indiquée en pt)
                 */
                'bandeau-ccda-1.jpg' => [
                    'label' => 'Bandeau de haut de page du site',
                    'taille' => Image::FIXED_SIZE,
                    'width' => 1170,
                    'height' => 195
                ],
                'bas-de-mail-service-gestion.png' => [
                    'label' => 'Bas de mail personnalisé',
                    'taille' => Image::PROPORTIONAL_SIZE,
                    'width' => '427',
                    'height' => '128'
                ],
                'bas-de-mail-transport-scolaire.png' => [
                    'label' => 'Bas de mail impersonnel avec logo TS',
                    'taille' => Image::PROPORTIONAL_SIZE,
                    'width' => '427',
                    'height' => '128'
                ],
                'logocartedroite.jpg' => [
                    'label' => 'Logo transport scolaire',
                    'taille' => Image::PROPORTIONAL_SIZE,
                    'width' => '85',
                    'height' => '48'
                ],
                'logocarteetablissements.png' => [
                    'label' => 'Image de la carte en page d\'accueil',
                    'taille' => Image::FULL_SIZE,
                    'width' => 0,
                    'height' => 0
                ],
                'logocartegauche.jpg' => [
                    'label' => 'Logo de l\'organisateur',
                    'taille' => Image::PROPORTIONAL_SIZE,
                    'width' => '48',
                    'height' => '65'
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
    'view_manager' => [
        'template_map' => [],
        'template_path_stack' => [
            __DIR__ . '/../view'
        ]
    ]
];  
