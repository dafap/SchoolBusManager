<?php
/**
 * Paramètres de configuration du module ConvertGeodetic
 *
 * Compatible ZF3
 * 
 * @project sbm
 * @package ConvertGeodetic/config
 * @filesource module.config.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr]
 * @date 8 avr. 2016
 * @version 2016-2
 */
use SbmCartographie\Controller\Service\CarteControllerFactory;
use SbmCartographie\Model\Service\CartographieManager;
use SbmCartographie\GoogleMaps\DistanceEtablissements;
use SbmCartographie\GoogleMaps\Service\DistanceEtablissementsFactory;
use SbmCartographie\GoogleMaps\Service\GeocoderFactory;
use SbmCartographie\Model\Service\ProjectionFactory;

return [
    'acl' => [
        'resources' => [
            'sbmcarte' => [
                'allow' => [
                    'roles' => [
                        'parent'
                    ]
                ],
                'actions' => [
                    'etablissements' => [
                        'allow' => [
                            'roles' => [
                                'guest'
                            ]
                        ]
                    ],
                    'stations' => [
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
    
    'cartographie_manager' => [
        'factories' => [
            'SbmCarto\DistanceEtablissements' => DistanceEtablissementsFactory::class,
            'SbmCarto\Geocoder' => GeocoderFactory::class,
            'SbmCarto\Projection' => ProjectionFactory::class
        ],
        'services' => [
            'google_api' => [
                'directions' => 'http://maps.googleapis.com/maps/api/directions/json?origin=%s&destination=%s&alternatives=true&sensor=false',
                'distancematrix' => 'http://maps.googleapis.com/maps/api/distancematrix/json?origins=%s&destinations=%s&mode=car&language=fr-FR&sensor=false',
                'geocoder' => 'https://maps.googleapis.com/maps/api/geocode/json?address=%s',
                'reversegeocoder' => 'http://maps.googleapis.com/maps/api/geocode/json?latlng=%s,%s&sensor=true'
            ],
        ]
    ],
    'service_manager' => [
        'factories' => [
            'Sbm\CartographieManager' => CartographieManager::class
        ]
    ],
    'controllers' => [
        'factories' => [
            'SbmCartographie\Controller\Carte' => CarteControllerFactory::class
        ]
    ],
    'router' => [
        'routes' => [
            'sbmcarte' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/carte[/:action]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*'
                    ],
                    'defaults' => [
                        'module' => 'SbmCartographie',
                        'controller' => 'SbmCartographie\Controller\Carte',
                        'action' => 'index'
                    ]
                ]
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