<?php
/**
 * Configuration du module
 *
 * @project sbm
 * @package SbmPdf
 * @filesource module.config.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr]
 * @date 15 février 2021
 * @version 2021-2.6.1
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
use SbmPdf\Model\View\Helper as ViewHelper;
use SbmPdf\Model\Document;
use SbmPdf\Model\Document\Template;
use SbmPdf\Mvc\Controller\Plugin as PluginController;
use SbmPdf\Model\Document\Template\TableHtml;
use SbmPdf\Model\Document\Template\TableComplexe;

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
                    'facture' => [
                        'allow' => [
                            'roles' => [
                                'parent',
                                'secretariat',
                                'gestion'
                            ]
                        ]
                    ],
                    'les-factures' => [
                        'allow' => [
                            'roles' => [
                                'secretariat',
                                'gestion'
                            ]
                        ]
                    ],
                    'horaires' => [
                        'allow' => [
                            'roles' => [
                                'parent',
                                'transporteur',
                                'etablissement',
                                'commune',
                                'secretariat',
                                'gestion'
                            ]
                        ]
                    ],
                    'org-pdf' => [
                        'allow' => [
                            'roles' => [
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
            'FormDocAffectation' => FormDocAffectation::class,
            Template\CartesTransport::PDFMANAGER_ID => Template\CartesTransport::class,
            Template\CopieEcran::PDFMANAGER_ID => Template\CopieEcran::class,
            Template\Etiquettes::PDFMANAGER_ID => Template\Etiquettes::class,
            Template\Horaires::PDFMANAGER_ID => Template\Horaires::class,
            Template\PublipostageHtml::PDFMANAGER_ID => Template\PublipostageHtml::class,
            Template\TableComplexe::PDFMANAGER_ID => Template\TableComplexe::class,
            Template\TableHtml::PDFMANAGER_ID => Template\TableHtml::class,
            Template\TableSimple::PDFMANAGER_ID => Template\TableSimple::class
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
    'controller_plugins' => [
        'invokables' => [
            PluginController\Pdf::PLUGINMANAGER_ID => PluginController\Pdf::class
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
    'view_helpers' => [
        'factories' => [
            'menuRapports' => ViewHelper\MenuRapports::class
        ]
    ],
    'view_manager' => [
        'template_map' => [],
        'template_path_stack' => [
            __DIR__ . '/../view'
        ]
    ]
];