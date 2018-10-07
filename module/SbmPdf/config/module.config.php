<?php
/**
 * Configuration du module
 *
 * @project sbm
 * @package SbmPdf
 * @filesource module.config.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr]
 * @date 7 oct. 2018
 * @version 2018-2.4.5
 */
use SbmPdf\Controller;
use SbmPdf\Service;
use SbmPdf\Form\DocAffectation as FormDocAffectation;
use SbmPdf\Form\DocColumn as FormDocColumn;
use SbmPdf\Form\DocField as FormDocField;
use SbmPdf\Form\DocLabel as FormDocLabel;
use SbmPdf\Form\DocTable as FormDocTable;
use SbmPdf\Form\Service\DocumentPdfFactory as FormDocumentPdfFactory;
use SbmPdf\Listener\PdfListener;
use SbmPdf\Listener\Service\PdfListenerFactory;
use SbmPdf\Model\Columns;
use SbmPdf\Model\Service as PdfService;
use SbmPdf\Model\Tcpdf;
use SbmPdf\Model\Service\ColumnsFactory;
use SbmPdf\Model\Service\TcpdfFactory;

return [
    'tcpdf' => [],
    'acl' => [
        'resources' => [
            'sbmpdf' => [
                'allow' => [
                    'roles' => [
                        'admin',
                        'sadmin'
                    ]
                ]
            ],
            'sbmdocument' => [
                'allow' => [
                    'roles' => [
                        'admin',
                        'sadmin'
                    ]
                ],
                'actions' => [
                    'horaires' => [
                        'allow' => [
                            'roles' => [
                                'parent',
                                'transporteur',
                                'etablissement',
                                'secretariat'
                            ]
                        ]
                    ],
                    'org-pdf' => [
                        'allow' => [
                            'roles' => [
                                'transporteur',
                                'etablissement',
                                'secretariat',
                                'gestion'
                            ]
                        ]
                    ],
                    'index' => [
                        'allow' => [
                            'roles' => [
                                'admin',
                                'sadmin'
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ],
    'pdf_manager' => [
        'invokables' => [
            'FormDocTable' => FormDocTable::class,
            'FormDocColumn' => FormDocColumn::class,
            'FormDocField' => FormDocField::class,
            'FormDocLabel' => FormDocLabel::class,
            'FormDocAffectation' => FormDocAffectation::class
        ],
        'factories' => [
            'ListeRoutes' => PdfService\ListeRoutesService::class,
            'FormDocumentPdf' => FormDocumentPdfFactory::class,
            Columns::class => ColumnsFactory::class,
            Tcpdf::class => TcpdfFactory::class
        ],
        'services' => [
            'paginator' => [
                'nb_pdf' => 1
            ]
        ]
    ],
    'service_manager' => [
        'factories' => [
            'RenderPdfService' => Service\RenderPdfService::class,
            'Sbm\PdfManager' => Service\PdfManagerFactory::class,
            PdfListener::class => PdfListenerFactory::class
        ]
    ],
    'controllers' => [
        'factories' => [
            Controller\PdfController::class => Controller\Service\PdfControllerFactory::class,
            Controller\DocumentController::class => Controller\Service\DocumentControllerFactory::class
        ]
    ],
    'router' => [
        'routes' => [
            'sbmpdf' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/pdf[/:action[/page/:page][/id/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'page' => '[0-9]+',
                        'id' => '[0-9]+'
                    ],
                    'defaults' => [
                        'module' => 'SbmPdf',
                        'controller' => Controller\PdfController::class,
                        'action' => 'pdf-liste'
                    ]
                ],
                'may_terminate' => true
            ],
            'sbmdocument' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/document[/:action[/page/:page][/id/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'page' => '[0-9]+',
                        'id' => '[0-9]+'
                    ],
                    'defaults' => [
                        'module' => 'SbmPdf',
                        'controller' => Controller\DocumentController::class,
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