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
 * @date 26 mars 2015
 * @version 2015-1
 */
return array(
    'acl' => array(
        'resources' => array(
            'sbmcarte' => array(
                'allow' => array(
                    'roles' => array('parent')
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
        'geocoder' => 'https://maps.googleapis.com/maps/api/geocode/json?address=%s'
    ),
    'service_manager' => array(
        'invokables' => array(
            'SbmCarto\DistanceEtablissements' => 'SbmCartographie\GoogleMaps\DistanceEtablissements',
            'SbmCarto\Geocoder' => 'SbmCartographie\GoogleMaps\Geocoder'
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