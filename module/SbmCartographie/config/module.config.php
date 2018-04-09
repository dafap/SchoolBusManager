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
 * @date 3 avr. 2018
 * @version 2018-2.4.0
 */
use SbmCartographie\Controller;
use SbmCartographie\Model\Service\CartographieManager;
use SbmCartographie\GoogleMaps\DistanceMatrix;
use SbmCartographie\GoogleMaps\Service\DistanceMatrixFactory;
use SbmCartographie\GoogleMaps\Geocoder;
use SbmCartographie\GoogleMaps\Service\GeocoderFactory;
use SbmCartographie\Model\Projection;
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
            DistanceMatrix::class => DistanceMatrixFactory::class,
            Geocoder::class => GeocoderFactory::class,
            Projection::class => ProjectionFactory::class
        ],
        'services' => [
            'google_api' => [
                'directions' => 'https://maps.googleapis.com/maps/api/directions/json?origin=%s&destination=%s&alternatives=true&sensor=false',
                'distancematrix' => 'https://maps.googleapis.com/maps/api/distancematrix/json?origins=%s&destinations=%s&mode=car&language=fr-FR&sensor=false',
                'geocoder' => 'https://maps.googleapis.com/maps/api/geocode/json?address=%s',
                'reversegeocoder' => 'https://maps.googleapis.com/maps/api/geocode/json?latlng=%s,%s&sensor=true'
            ]
        ]
    ],
    'service_manager' => [
        'factories' => [
            'Sbm\CartographieManager' => CartographieManager::class
        ]
    ],
    'controllers' => [
        'factories' => [
            Controller\CarteController::class => Controller\Service\CarteControllerFactory::class
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
                        'controller' => Controller\CarteController::class,
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