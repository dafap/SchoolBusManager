<?php
/**
 * Configuration du module
 *
 * @project sbm
 * @package SbmPdf
 * @filesource module.config.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr]
 * @date 12 avr. 2016
 * @version 2016-2
 */
use SbmPdf\Service;
use SbmPdf\Model\Service as PdfService;
use SbmPdf\Listener\Service\PdfListenerFactory;
use SbmPdf\Listener\PdfListener;
use SbmPdf\Model\Filter\Service\NomTableFactory;
use SbmPdf\Model\Filter\NomTable;
use SbmPdf\Form\Service\DocumentPdfFactory as FormDocumentPdfFactory;
use SbmPdf\Form\DocTable as FormDocTable;
use SbmPdf\Form\DocColumn as FormDocColumn;
use SbmPdf\Form\DocField as FormDocField;
use SbmPdf\Form\DocLabel as FormDocLabel;
use SbmPdf\Form\DocAffectation as FormDocAffectation;
use SbmPdf\Model\Service\ColumnsFactory;
use SbmPdf\Model\Service\TcpdfFactory;
use SbmPdf\Model\Columns;
use SbmPdf\Model\Tcpdf;
use SbmPdf\Controller\Service\PdfControllerFactory;
use SbmPdf\Controller\Service\DocumentControllerFactory;


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
            'SbmPdf\Controller\Pdf' => PdfControllerFactory::class,
            'SbmPdf\Controller\Document' => DocumentControllerFactory::class
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
                        'controller' => 'SbmPdf\Controller\Pdf',
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
                        'controller' => 'SbmPdf\Controller\Document',
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