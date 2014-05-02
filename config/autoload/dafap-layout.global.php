<?php
use SbmGestion\Controller\SimulationController;
/**
 * Déclaration des layouts à utiliser et des paramètres à leur passer
 * 
 * Ce fichier doit être copié sous le nom de manage-layout.global.php dans le dossier config/autoload de l'application
 * 
 * Il doit être complété par la déclaration des layouts et des paramètres à passer à ces layouts
 *
 *
 * @project dafap/ManageLayout
 * @package config
 * @filesource 
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 24 avr. 2014
 * @version 2014-1
 */
return array(
    'view_manager' => array(
        /**
         * Déclarer ici tous les layouts que l'on utilisera sous la forme 'layout/alias'.
         * On devra définir les alias suivants : error, defaults et ensuite layouts particuliers pour des modules ou controllers.
         * Pour ces layouts particuliers, on l'alias devra commencer par une minuscule.
         * (Indiquer le chemin complet pour 'layout/alias' car il ne prend pas en compte les template_path_stack)
         */
        'template_map' => array(
            'layout/error' => __DIR__ . '/../../module/SbmCommun/view/layout/layout.phtml',
            'layout/defaults' => __DIR__ . '/../../module/SbmCommun/view/layout/layout.phtml',
            'layout/sbmFront' => __DIR__ . '/../../module/SbmFront/view/layout/layout.phtml',
        ),
    ),
    'layout_manager' => array(
        /**
         * layout_map :
         * - la clé 'defaults' définie le layout qui s'applique pour tous les modules pour lesquels un layout particulier n'a pas été déclaré
         * - les autres clés portent le nom d'un module (namespace) ou d'un controller (classe) et définissent le layout à utiliser pour ce module
         */
        'layout_map' => array(
            'defaults' => 'layout/defaults',
            'SbmFront' => 'layout/sbmFront',
            'SbmGestion\Controller\SimulationController' => 'layout/sbmFront'
        ),
        'parameter' => array(
            /**
             * Les clés correspondent aux clés déclarées dans view_manager.template_map qui définissent les layouts
             * Le tableau associé à une clé définit les parametres à passer au layout correspondant à cette clé.
             * Dans le layout, ces paramètres seront accessibles dans $this->parameter
             */
            'layout/error' => array(
                'appl_name' => 'School Bus Manager',
                'favicon' => '/img/favicon.ico',
                'css' => array(
                    '/css/style.css',
                    '/css/bootstrap-theme.min.css',
                    '/css/bootstrap.min.css'
                ),
                'js' => array(
                    '/js/bootstrap.min.js',
                    '/js/jquery.min.js',
                    array(
                        'src' => '/js/respond.min.js',
                        'type' => 'text/javascript',
                        'attrs' => array(
                            'conditional' => 'lt IE 9'
                        )
                    ),
                    array(
                        'src' => '/js/html5shiv.js',
                        'type' => 'text/javascript',
                        'attrs' => array(
                            'conditional' => 'lt IE 9'
                        )
                    )
                ),
                'logo_file' => 'img/sbm-logo.gif',
            ),
            'layout/defaults' => array(
                'appl_name' => 'School Bus Manager',
                'favicon' => '/img/favicon.ico',
                'css' => array(
                    '/css/style.css',
                    '/css/bootstrap-theme.min.css',
                    '/css/bootstrap.min.css'
                ),
                'js' => array(
                    '/js/bootstrap.min.js',
                    '/js/jquery.min.js',
                    array(
                        'src' => '/js/respond.min.js',
                        'type' => 'text/javascript',
                        'attrs' => array(
                            'conditional' => 'lt IE 9'
                        )
                    ),
                    array(
                        'src' => '/js/html5shiv.js',
                        'type' => 'text/javascript',
                        'attrs' => array(
                            'conditional' => 'lt IE 9'
                        )
                    )
                ),
                'logo_file' => 'img/sbm-logo.gif',
            ),
            'layout/sbmFront' => array(
                'appl_name' => 'School Bus Manager',
                'favicon' => '/img/favicon.ico',
                'css' => array(
                    '/css/cobas.css'
                ),
                'js' => array(),
                'header' => array(
                    'logo_file' => '/img/logo_cobas.gif',
                    'bandeau_file' => '/img/bandeau_transports_scolaires.jpg'
                ),
                'footer' => array(
                    'client_name' => 'COBAS',
                    'footer_elements' => array(
                        'mentions légales' => 'home\about'
                    )
                )
            )
        )
    )
);