<?php
/**
 * Configuration du module
 *
 * @project sbm
 * @package SbmPdf
 * @filesource module.config.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 1 juil. 2015
 * @version 2015-2
 */
return array(
    'tcpdf' => array(),
    'acl' => array(
        'resources' => array(
            'sbmpdf' => array(
                'allow' => array(
                    'roles' => array(
                        'admin',
                        'sadmin'
                    )
                )
            )
        )
    ),
    'liste' => array(
        'paginator' => array(
            'nb_pdf' => 1
        )
    ),
    'service_manager' => array(
        'invokables' => array(
            'RenderPdfService' => 'SbmPdf\Service\RenderPdfService',
            'PdfListener' => 'SbmPdf\Listener\PdfListener'
        ),
        'factories' => array(
            'ListeRoutes' => 'SbmPdf\Service\ListeRoutesService'
        )
    ),
    'controllers' => array(
        'invokables' => array(
            'SbmPdf\Controller\Document' => 'SbmPdf\Controller\DocumentController'
        )
    ),
    'router' => array(
        'routes' => array(
            'sbmpdf' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/pdf[/:action[/page/:page][/id/:id]]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'page' => '[0-9]+',
                        'id' => '[0-9]+'
                    ),
                    'defaults' => array(
                        'module' => 'SbmPdf',
                        'controller' => 'SbmPdf\Controller\Document',
                        'action' => 'pdf-liste'
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