<?php
/**
 * Paramètres de configuration du module ConvertGeodetic
 *
 * 
 * @project sbm
 * @package ConvertGeodetic/config
 * @filesource module.config.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 23 déc. 2015
 * @version 2015-1.6.9
 */
return array(
    'acl' => array(
        'resources' => array(
            'sbmcarte' => array(
                'allow' => array(
                    'roles' => array('parent')
                ),
                'actions' => array(
                    'etablissements' => array(
                        'allow' => array(
                            'roles' => array(
                                'guest'
                            )
                        )
                    ),
                    'stations' => array(
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
    'cartographie' => array(
        'system' => 'Lambert06CC9zones',
        'nzone' => 44
    ),
    'google_api' => array(
        'directions' => 'http://maps.googleapis.com/maps/api/directions/json?origin=%s&destination=%s&alternatives=true&sensor=false',
        'distancematrix' => 'http://maps.googleapis.com/maps/api/distancematrix/json?origins=%s&destinations=%s&mode=car&language=fr-FR&sensor=false',
        'geocoder' => 'https://maps.googleapis.com/maps/api/geocode/json?address=%s',
        'reversegeocoder' => 'http://maps.googleapis.com/maps/api/geocode/json?latlng=%s,%s&sensor=true'
    ),
    'service_manager' => array(
        'invokables' => array(
            'SbmCarto\DistanceEtablissements' => 'SbmCartographie\GoogleMaps\DistanceEtablissements',
            'SbmCarto\Geocoder' => 'SbmCartographie\GoogleMaps\Geocoder',
            'SbmCarto\Projection' => 'SbmCartographie\Model\Projection'
        )
    ),
    'controllers' => array(
        'invokables' => array(
            'SbmCartographie\Controller\Carte' => 'SbmCartographie\Controller\CarteController'
        )
    ),
    'router' => array(
        'routes' => array(
            'sbmcarte' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/carte[/:action]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*'
                    ),
                    'defaults' => array(
                        'module' => 'SbmCartographie',
                        'controller' => 'SbmCartographie\Controller\Carte',
                        'action' => 'index'
                    )
                )
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