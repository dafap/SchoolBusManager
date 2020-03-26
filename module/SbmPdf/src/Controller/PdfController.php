<?php
/**
 * Controller principal du module SbmPdf
 *
 * Gestion des la création et de la modification des documents pdf
 *
 * @project sbm
 * @package SbmPdf/Controller
 * @filesource PdfController.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 26 mars 2020
 * @version 2020-2.6.0
 */
namespace SbmPdf\Controller;

use SbmBase\Model\Session;
use SbmBase\Model\StdLib;
use SbmCommun\Form\ButtonForm;
use SbmCommun\Model\Mvc\Controller\AbstractActionController;
use Zend\Http\PhpEnvironment\Response;
use Zend\View\Model\ViewModel;

class PdfController extends AbstractActionController
{

    /**
     * Dresse la liste des documents pdf
     *
     * @return \Zend\Http\PhpEnvironment\Response|\Zend\View\Model\ViewModel
     */
    public function pdfListeAction()
    {
        $getTemplateList = function () {
            $form = $this->pdf_manager->get('FormDocumentPdf');
            $element = $form->get('page_templateId');
            return $element->getValueOptions();
        };
        $args = $this->initListe(
            [
                [
                    'name' => 'documentId',
                    'type' => 'text',
                    'attributes' => [
                        'class' => 'sbm-width-5c'
                    ],
                    'options' => [
                        'label' => 'Id'
                    ]
                ],
                [
                    'name' => 'name',
                    'type' => 'text',
                    'attributes' => [
                        'class' => 'sbm-width-30c'
                    ],
                    'options' => [
                        'label' => 'Nom'
                    ]
                ],
                [
                    'name' => 'page_templateId',
                    'type' => 'Zend\Form\Element\Select',
                    'attributes' => [
                        'class' => 'sbm-width-45c'
                    ],
                    'options' => [
                        'label' => 'Modèle',
                        'empty_option' => 'Tous',
                        'value_options' => $getTemplateList()
                    ]
                ]
            ], null, [
                'page_templateId'
            ]);
        if ($args instanceof Response)
            return $args;

        return new ViewModel(
            [
                'paginator' => $this->db_manager->get('Sbm\Db\System\Documents')->paginator(
                    $args['where']),
                'page' => $this->params('page', 1),
                'count_per_page' => StdLib::getParam('nb_pdf',
                    $this->pdf_manager->get('paginator'), 10),
                'criteres_form' => $args['form']
            ]);
    }

    /**
     * Affiche et traite le formulaire d'ajout d'un document. Le formulaire est initialisé
     * par les valeurs par défaut.
     *
     * @return \Zend\Http\PhpEnvironment\Response|\Zend\Http\Response|\Zend\View\Model\ViewModel
     */
    public function pdfAjoutAction()
    {
        $currentPage = $this->params('page', 1);
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            $args = Session::get('post', false, $this->getSessionNamespace());
            if ($args === false) {
                $this->flashMessenger()->addErrorMessage("Action interdite.");
                return $this->redirect()->toRoute('sbmpdf',
                    [
                        'action' => 'pdf-liste',
                        'page' => $currentPage
                    ]);
            }
            $isPost = false;
        } else {
            $args = $prg;
            if (array_key_exists('cancel', $args)) {
                $this->flashMessenger()->addWarningMessage(
                    "Aucun enregistrement n'a été ajouté.");
                return $this->redirect()->toRoute('sbmpdf',
                    [
                        'action' => 'pdf-liste',
                        'page' => $currentPage
                    ]);
            }
            $isPost = array_key_exists('submit', $args);
            unset($args['submit']);
            Session::set('post', $args, $this->getSessionNamespace());
        }
        $tDocuments = $this->db_manager->get('Sbm\Db\System\Documents');
        $form = $this->pdf_manager->get('FormDocumentPdf');
        $form->setValueOptions('TrecordSource', $this->db_manager->getTableAliasList());
        $form->setMaxLength($this->db_manager->getMaxLengthArray('documents', 'system'));
        $form->bind($tDocuments->getObjData());
        if ($isPost) {
            $form->setData($args);
            if ($form->isValid()) {
                $oData = $form->getData();
                $tDocuments->saveRecord($oData);
                $documentId = $oData->documentId;
                if (empty($documentId)) {
                    $documentId = $tDocuments->getTableGateway()->getLastInsertValue();
                }
                $this->flashMessenger()->addSuccessMessage(
                    "Un nouvel enregistrement a été ajouté.");
                // création des sections dans doctables, de la fiche étiquette ou de la
                // fiche texte
                switch ($oData->disposition) {
                    case 'Tabulaire':
                        $tDoctables = $this->db_manager->get('Sbm\Db\System\DocTables');
                        $oDoctable = $tDoctables->getObjData();
                        $oDoctable->setMaxLengthArray(
                            $this->db_manager->getMaxLengthArray('doctables', 'system'));
                        $defaults = include (__DIR__ .
                            '/../Model/default/doctables.inc.php');
                        foreach ([
                            'thead',
                            'tbody',
                            'tfoot'
                        ] as $section) {
                            $defaults[$section]['documentId'] = $documentId;
                            $oDoctable->exchangeArray($defaults[$section]);
                            $oDoctable->section = $section;
                            $tDoctables->saveRecord($oDoctable);
                        }
                        break;
                    case 'Etiquette':
                        $tDoclabels = $this->db_manager->get('Sbm\Db\System\DocLabels');
                        $oDoclabel = $tDoclabels->getObjData();
                        $defaults = include (__DIR__ .
                            '/../Model/default/doclabels.inc.php');
                        $defaults['documentId'] = $documentId;
                        $oDoclabel->exchangeArray($defaults);
                        $tDoclabels->saveRecord($oDoclabel);
                        break;
                    default: // Texte
                        break;
                }
                // création des colonnes du tableau

                // retour à la liste des documents pdf
                return $this->redirect()->toRoute('sbmpdf',
                    [
                        'action' => 'pdf-liste',
                        'page' => $currentPage
                    ]);
            }
        } else {
            // initialisation du formulaire
            $defaults = array_merge(
                $this->db_manager->getColumnDefaults('documents', 'system'),
                include (__DIR__ . '/../Model/default/documents.inc.php'));
            $form->setData($defaults);
        }
        $view = new ViewModel(
            [
                'form' => $form->prepare(),
                'page' => $currentPage,
                'documentId' => null
            ]);
        $view->setTemplate('sbm-pdf/pdf/pdf-edit.phtml');
        return $view;
    }

    /**
     * Affiche et traite le formulaire de modification d'un document.
     *
     * @return \Zend\Http\PhpEnvironment\Response|\Zend\Http\Response|\Zend\View\Model\ViewModel
     */
    public function pdfEditAction()
    {
        $currentPage = $this->params('page', 1);
        $form = $this->pdf_manager->get('FormDocumentPdf');
        $form->setValueOptions('TrecordSource', $this->db_manager->getTableAliasList());
        $params = [
            'data' => [
                'table' => 'documents',
                'type' => 'system',
                'alias' => 'Sbm\Db\System\Documents',
                'id' => 'documentId'
            ],
            'form' => $form
        ];
        $r = $this->editData($params, function ($post) {
            return $post;
        });
        if ($r instanceof Response) {
            return $r;
        } else {
            switch ($r->getStatus()) {
                case 'error':
                case 'warning':
                case 'success':
                    return $this->redirect()->toRoute('sbmpdf',
                        [
                            'action' => 'pdf-liste',
                            'page' => $currentPage
                        ]);
                    break;
                default:
                    return new ViewModel(
                        [
                            'form' => $form->prepare(),
                            'page' => $currentPage,
                            'documentId' => $r->getResult()
                        ]);
                    break;
            }
        }
    }

    /**
     * Affiche et traite le formulaire d'ajout d'un document. Le formulaire est initialisé
     * par les valeurs du formulaire de la ligne cliquée (documentId en post au moment de
     * l'appel).
     *
     * @return \Zend\Http\PhpEnvironment\Response|\Zend\Http\Response|\Zend\View\Model\ViewModel
     */
    public function pdfDupliquerAction()
    {
        $currentPage = $this->params('page', 1);
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            $args = Session::get('post', false, $this->getSessionNamespace());
            if ($args === false) {
                $this->flashMessenger()->addErrorMessage("Action interdite.");
                return $this->redirect()->toRoute('sbmpdf',
                    [
                        'action' => 'pdf-liste',
                        'page' => $currentPage
                    ]);
            }
            $isPost = false;
        } else {
            $args = $prg;
            if (array_key_exists('cancel', $args)) {
                $this->flashMessenger()->addWarningMessage(
                    "Aucun enregistrement n'a été ajouté.");
                return $this->redirect()->toRoute('sbmpdf',
                    [
                        'action' => 'pdf-liste',
                        'page' => $currentPage
                    ]);
            }
            $isPost = array_key_exists('submit', $args);
            unset($args['submit']);
            Session::set('post', $args, $this->getSessionNamespace());
        }
        $tDocuments = $this->db_manager->get('Sbm\Db\System\Documents');
        $form = $this->pdf_manager->get('FormDocumentPdf');
        $form->setValueOptions('TrecordSource', $this->db_manager->getTableAliasList());
        $form->setMaxLength($this->db_manager->getMaxLengthArray('documents', 'system'));
        $form->bind($tDocuments->getObjData());
        if ($isPost) {
            $form->setData($args);
            if ($form->isValid()) {
                $oData = $form->getData();
                $tDocuments->saveRecord($oData);
                $documentId = $oData->documentId;
                if (empty($documentId)) {
                    $documentId = $tDocuments->getTableGateway()->getLastInsertValue();
                }
                $this->flashMessenger()->addSuccessMessage(
                    "Un nouvel enregistrement a été ajouté.");
                // création des sections dans doctables, de la fiche étiquette ou de la
                // fiche texte
                switch ($oData->disposition) {
                    case 'Tabulaire':
                        $tDoctables = $this->db_manager->get('Sbm\Db\System\DocTables');
                        $oDoctable = $tDoctables->getObjData();
                        $defaults = include (__DIR__ .
                            '/../Model/default/doctables.inc.php');
                        foreach ([
                            'thead',
                            'tbody',
                            'tfoot'
                        ] as $section) {
                            $defaults[$section]['documentId'] = $documentId;
                            $oDoctable->exchangeArray($defaults[$section]);
                            $oDoctable->section = $section;
                            $tDoctables->saveRecord($oDoctable);
                        }
                        break;
                    case 'Etiquette':
                        $tDoclabels = $this->db_manager->get('Sbm\Db\System\DocLabels');
                        $oDoclabel = $tDoclabels->getObjData();
                        $defaults = include (__DIR__ .
                            '/../Model/default/doclabels.inc.php');
                        $defaults['documentId'] = $documentId;
                        $oDoclabel->exchangeArray($defaults);
                        $tDoclabels->saveRecord($oDoclabel);
                        break;
                    default: // Texte
                        break;
                }
                // création des colonnes du tableau

                // retour à la liste des documents pdf
                return $this->redirect()->toRoute('sbmpdf',
                    [
                        'action' => 'pdf-liste',
                        'page' => $currentPage
                    ]);
            }
        } else {
            // initialisation du formulaire
            $documentId = isset($args['documentId']) ? $args['documentId'] : - 1;
            if ($documentId > 0) {
                $data = $this->db_manager->get('Sbm\Db\System\Documents')
                    ->getRecord($documentId)
                    ->getArrayCopy();
                unset($data['documentId']);
                $form->setData($data);
            } else {
                $defaults = array_merge(
                    $this->db_manager->getColumnDefaults('documents', 'system'),
                    include (__DIR__ . '/../Model/default/documents.inc.php'));
                $form->setData($defaults);
            }
        }
        $view = new ViewModel(
            [
                'form' => $form->prepare(),
                'page' => $currentPage,
                'documentId' => null
            ]);
        $view->setTemplate('sbm-pdf/pdf/pdf-edit.phtml');
        return $view;
    }

    public function pdfSupprAction()
    {
        $currentPage = $this->params('page', 1);
        $form = new ButtonForm([
            'id' => null
        ],
            [
                'supproui' => [
                    'class' => 'confirm',
                    'value' => 'Confirmer'
                ],
                'supprnon' => [
                    'class' => 'confirm',
                    'value' => 'Abandonner'
                ]
            ]);
        $params = [
            'data' => [
                'alias' => 'Sbm\Db\System\Documents',
                'id' => 'documentId'
            ],
            'form' => $form
        ];

        try {
            $r = $this->supprData($params,
                function ($id, $tableClasses) {
                    return [
                        'id' => $id,
                        'data' => $tableClasses->getRecord($id)
                    ];
                });
        } catch (\Zend\Db\Adapter\Exception\InvalidQueryException $e) {
            $this->flashMessenger()->addWarningMessage(
                'Impossible de supprimer ce document parce que certains élèves y sont inscrits.');
            return $this->redirect()->toRoute('sbmpdf',
                [
                    'action' => 'pdf-liste',
                    'page' => $currentPage
                ]);
        }

        if ($r instanceof Response) {
            return $r;
        } else {
            switch ($r->getStatus()) {
                case 'error':
                case 'warning':
                case 'success':
                    return $this->redirect()->toRoute('sbmpdf',
                        [
                            'action' => 'pdf-liste',
                            'page' => $currentPage
                        ]);
                    break;
                default:
                    return new ViewModel(
                        [
                            'form' => $form->prepare(),
                            'page' => $currentPage,
                            'data' => StdLib::getParam('data', $r->getResult()),
                            'documentId' => StdLib::getParam('id', $r->getResult())
                        ]);
                    break;
            }
        }
    }

    public function pdfApercuAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            $this->flashMessenger()->addInfoMessage('Retour à la liste des documents');
            return $this->redirect()->toRoute('sbmpdf');
        }
        $documentId = $prg['documentId'];
        $call_pdf = $this->RenderPdfService;
        $call_pdf->setParam('documentId', $documentId)->renderPdf();
    }

    public function pdfGroupAction()
    {
        ;
    }

    public function pdfPdfAction()
    {
        $criteresObject = '\SbmCommun\Model\Db\ObjectData\Criteres';
        $criteresForm = '\SbmCommun\Form\CriteresForm';
        $documentId = null;
        $retour = [
            'route' => 'sbmpdf',
            'action' => 'pdf-liste'
        ];
        return $this->documentPdf($criteresObject, $criteresForm, $documentId, $retour);
    }

    public function pdfTexteAction()
    {
        return new ViewModel();
    }

    // ====================================================================================================================================

    /**
     * Présente les 3 sections d'un tableau d'un document pdf Reçoit des données en post,
     * dont les données obligatoires suivantes : - documentId - name - ordinal_table -
     * recordSource
     *
     * @return \Zend\Http\PhpEnvironment\Response|\Zend\View\Model\ViewModel
     */
    public function tableListeAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            $args = Session::get('post', false, $this->getSessionNamespace());
            if ($args === false) {
                return $this->redirect()->toRoute('sbmpdf');
            }
        } else {
            $args = $prg;
            if (array_key_exists('disposition', $args)) {
                $args['ordinal_table'] = 1;
                unset($args['disposition']);
                Session::set('post', $args, $this->getSessionNamespace());
            }
        }
        return new ViewModel(
            [
                'data' => $this->db_manager->get('Sbm\Db\System\DocTables')->getConfig(
                    $args['documentId'], $args['ordinal_table']),
                'page' => $this->params('page', 1),
                'document' => $args
            ]);
    }

    /**
     * Reçoit des données en post, dont les données obligatoires suivantes : - doctableId
     * - documentId - name - ordinal_table - recordSource
     *
     * @return \Zend\Http\PhpEnvironment\Response|\Zend\Http\Response|\Zend\View\Model\ViewModel
     */
    public function tableEditAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            $args = Session::get('post', false, $this->getSessionNamespace());
            if ($args === false) {
                return $this->redirect()->toRoute('sbmpdf');
            }
        } else {
            $args = $prg;
            if (array_key_exists('cancel', $args)) {
                return $this->redirect()->toRoute('sbmpdf',
                    [
                        'action' => 'table-liste',
                        'page' => $this->params('page', 1)
                    ]);
            } elseif (array_key_exists('modifier', $args)) {
                $args['ordinal_table'] = 1;
                unset($args['modifier']);
                Session::set('post', $args, $this->getSessionNamespace());
            }
        }
        $tDocTables = $this->db_manager->get('Sbm\Db\System\DocTables');
        $form = $this->pdf_manager->get('FormDocTable');
        $form->bind($tDocTables->getObjData());
        if (array_key_exists('submit', $args)) {
            $section = $args['section'];
            $form->setData($args);
            // die(var_dump($args));
            if ($form->isValid()) {
                $tDocTables->saveRecord($form->getData());
                $this->flashMessenger()->addSuccessMessage('Section enregistrée.');
                return $this->redirect()->toRoute('sbmpdf',
                    [
                        'action' => 'table-liste',
                        'page' => $this->params('page', 1)
                    ]);
            }
        } else {
            $data = $tDocTables->getRecord($args['doctableId']);
            $form->setData($data->getArrayCopy());
            $section = $data->section;
        }

        return new ViewModel(
            [
                'section' => [
                    'doctableId' => $args['doctableId'],
                    'documentId' => $args['documentId'],
                    'name' => $args['name'],
                    'ordinal_table' => $args['ordinal_table'],
                    'recordSource' => $args['recordSource'],
                    'section' => $section
                ],
                'form' => $form,
                'page' => $this->params('page', 1)
            ]);
    }

    // ===================================================================================================

    /**
     * Reçoit par post les paramètres suivants : - documentId - name - ordinal_table -
     * recordSource
     *
     * @return \Zend\Http\PhpEnvironment\Response|\Zend\Http\Response|\Zend\View\Model\ViewModel
     */
    public function colonneListeAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false || array_key_exists('retour', $prg)) {
            $args = Session::get('post', false, $this->getSessionNamespace());
            if ($args === false) {
                return $this->redirect()->toRoute('sbmpdf');
            }
        } else {
            $args = $prg;
            if (array_key_exists('colonnes', $args)) {
                unset($args['colonnes']);
                Session::set('post', $args, $this->getSessionNamespace());
            }
        }
        try {
            $data = $this->db_manager->get('Sbm\Db\System\DocTables\Columns')->getConfig(
                $args['documentId'], $args['ordinal_table']);
        } catch (\SbmCommun\Model\Db\Service\Table\Exception\ExceptionInterface $e) {
            $data = [];
        }
        return new ViewModel(
            [
                'data' => $data,
                'page' => $this->params('page', 1),
                'document' => $args
            ]);
    }

    public function colonneEditAction()
    {
        $currentPage = $this->params('page', 1);
        $pdf_manager = $this->pdf_manager;
        $form = $pdf_manager->get('FormDocColumn');
        $params = [
            'data' => [
                'table' => 'doccolumns',
                'type' => 'system',
                'alias' => 'Sbm\Db\System\DocTables\Columns',
                'id' => 'doccolumnId'
            ],
            'form' => $form
        ];
        $r = $this->editData($params, function ($post) {
            return $post;
        },
            function ($post) use ($pdf_manager, $form) {
                $columns = $pdf_manager->get(\SbmPdf\Model\Columns::class)
                    ->setRecordSource($post['documentId']);
                $form->setValueOptions('tbody', $columns->getListeForSelect());
                $form->setData(
                    [
                        'name' => $post['name'],
                        'recordSource' => $post['recordSource']
                    ]);
            });
        if ($r instanceof Response) {
            return $r;
        } else {
            switch ($r->getStatus()) {
                case 'error':
                case 'warning':
                case 'success':
                    return $this->redirect()->toRoute('sbmpdf',
                        [
                            'action' => 'colonne-liste',
                            'page' => $currentPage
                        ]);
                    break;
                default:
                    return new ViewModel(
                        [
                            'form' => $form->prepare(),
                            'page' => $currentPage,
                            'document' => $r->getResult()
                        ]);
                    break;
            }
        }
    }

    public function colonneAjoutAction()
    {
        $currentPage = $this->params('page', 1);
        $pdf_manager = $this->pdf_manager;
        $form = $pdf_manager->get('FormDocColumn');
        $params = [
            'data' => [
                'table' => 'doccolumns',
                'type' => 'system',
                'alias' => 'Sbm\Db\System\DocTables\Columns'
            ],
            // 'id' => 'doccolumnId'
            'form' => $form
        ];
        $r = $this->addData($params, function ($post) {
            return $post;
        },
            function ($post) use ($pdf_manager, $form) {
                $columns = $pdf_manager->get(\SbmPdf\Model\Columns::class)
                    ->setRecordSource($post['documentId']);
                $form->setValueOptions('tbody', $columns->getListeForSelect());
            });
        switch ($r) {
            case $r instanceof Response:
                return $r;
                break;
            case 'error':
            case 'warning':
            case 'success':
                return $this->redirect()->toRoute('sbmpdf',
                    [
                        'action' => 'colonne-liste',
                        'page' => $currentPage
                    ]);
                break;
            default:
                $form->setData(
                    [
                        'documentId' => $r['documentId'],
                        'name' => $r['name'],
                        'recordSource' => $r['recordSource'],
                        'ordinal_position' => $r['new_position']
                    ]);
                $view = new ViewModel(
                    [
                        'form' => $form->prepare(),
                        'page' => $currentPage,
                        'document' => $r
                    ]);
                $view->setTemplate('sbm-pdf/pdf/colonne-edit.phtml');
                return $view;
                break;
        }
    }

    public function colonneDupliquerAction()
    {
        $currentPage = $this->params('page', 1);
        $pdf_manager = $this->pdf_manager;
        $form = $pdf_manager->get('FormDocColumn');
        $params = [
            'data' => [
                'table' => 'doccolumns',
                'type' => 'system',
                'alias' => 'Sbm\Db\System\DocTables\Columns'
            ],
            // 'id' => 'doccolumnId'
            'form' => $form
        ];
        $r = $this->addData($params, function ($post) {
            return $post;
        },
            function ($post) use ($pdf_manager, $form) {
                $columns = $pdf_manager->get(\SbmPdf\Model\Columns::class)
                    ->setRecordSource($post['documentId']);
                $form->setValueOptions('tbody', $columns->getListeForSelect());
                $form->setData(
                    [
                        'name' => $post['name'],
                        'recordSource' => $post['recordSource']
                    ]);
            });
        switch ($r) {
            case $r instanceof Response:
                return $r;
                break;
            case 'error':
            case 'warning':
            case 'success':
                return $this->redirect()->toRoute('sbmpdf',
                    [
                        'action' => 'colonne-liste',
                        'page' => $currentPage
                    ]);
                break;
            default:
                $tDocColumns = $this->db_manager->get('Sbm\Db\System\DocTables\Columns');
                $acolonne = $tDocColumns->getRecord($r['doccolumnId'])->getArrayCopy();
                $acolonne['ordinal_position'] = $r['new_position'];
                unset($r['ordinal_position']);
                unset($acolonne['doccolumnId']);
                $form->setData($acolonne);
                $view = new ViewModel(
                    [
                        'form' => $form->prepare(),
                        'page' => $currentPage,
                        'document' => $r
                    ]);
                $view->setTemplate('sbm-pdf/pdf/colonne-edit.phtml');
                return $view;
                break;
        }
    }

    public function colonneSupprAction()
    {
        $currentPage = $this->params('page', 1);
        $form = new ButtonForm([
            'id' => null
        ],
            [
                'supproui' => [
                    'class' => 'confirm',
                    'value' => 'Confirmer'
                ],
                'supprnon' => [
                    'class' => 'confirm',
                    'value' => 'Abandonner'
                ]
            ]);
        $params = [
            'data' => [
                'alias' => 'Sbm\Db\System\DocTables\Columns',
                'id' => 'doccolumnId'
            ],
            'form' => $form
        ];
        try {
            $r = $this->supprData($params,
                function ($id, $tDocColumns) {
                    return [
                        'id' => $id,
                        'data' => $tDocColumns->getRecord($id)
                    ];
                });
        } catch (\Zend\Db\Adapter\Exception\InvalidQueryException $e) {
            $this->flashMessenger()->addWarningMessage(
                'Impossible de supprimer cette colonne.');
            return $this->redirect()->toRoute('sbmpdf',
                [
                    'action' => 'colonne-liste',
                    'page' => $currentPage
                ]);
        }

        if ($r instanceof Response) {
            return $r;
        } else {
            switch ($r->getStatus()) {
                case 'error':
                case 'warning':
                case 'success':
                    return $this->redirect()->toRoute('sbmpdf',
                        [
                            'action' => 'colonne-liste',
                            'page' => $currentPage
                        ]);
                    break;
                default:
                    return new ViewModel(
                        [
                            'form' => $form->prepare(),
                            'page' => $currentPage,
                            'data' => StdLib::getParam('data', $r->getResult()),
                            'doccolumnId' => StdLib::getParam('id', $r->getResult())
                        ]);
                    break;
            }
        }
    }

    // ============================================================================================
    public function texteFormatAction()
    {
        ;
    }

    // ============================================================================================
    /**
     * Reçoit par post les paramètres suivants : - disposition (uniquement au moment de
     * l'appel) - documentId - name - recordSource
     *
     * @return \Zend\Http\PhpEnvironment\Response|\Zend\Http\Response|\Zend\View\Model\ViewModel
     */
    public function etiquetteFormatAction()
    {
        $currentPage = $this->params('page', 1);
        $form = $this->pdf_manager->get('FormDocLabel');
        $params = [
            'data' => [
                'table' => 'doclabels',
                'type' => 'system',
                'alias' => 'Sbm\Db\System\DocLabels',
                'id' => 'documentId'
            ],
            'form' => $form
        ];
        $r = $this->editData($params, function ($post) {
            return $post;
        },
            function ($post) use ($form) {
                $form->setData(
                    [
                        'name' => $post['name'],
                        'recordSource' => $post['recordSource']
                    ]);
            });
        if ($r instanceof Response) {
            return $r;
        } else {
            switch ($r->getStatus()) {
                case 'error':
                case 'warning':
                case 'success':
                    return $this->redirect()->toRoute('sbmpdf',
                        [
                            'action' => 'pdf-liste',
                            'page' => $currentPage
                        ]);
                    break;
                default:
                    return new ViewModel(
                        [
                            'form' => $form->prepare(),
                            'page' => $currentPage,
                            'document' => $r->getResult()
                        ]);
                    break;
            }
        }
    }

    // ============================================================================================

    /**
     * Reçoit par post les paramètres suivants : - documentId - name - recordSource
     *
     * @return \Zend\Http\PhpEnvironment\Response|\Zend\Http\Response|\Zend\View\Model\ViewModel
     */
    public function fieldListeAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false || array_key_exists('retour', $prg)) {
            $args = Session::get('post', false, $this->getSessionNamespace());
            if ($args === false) {
                return $this->redirect()->toRoute('sbmpdf');
            }
        } else {
            $args = $prg;
            if (array_key_exists('champs', $args)) {
                unset($args['champs']);
                Session::set('post', $args, $this->getSessionNamespace());
            }
        }
        try {
            $data = $this->db_manager->get('Sbm\Db\System\DocFields')->getConfig(
                $args['documentId']);
        } catch (\SbmCommun\Model\Db\Service\Table\Exception\ExceptionInterface $e) {
            $data = [];
        }
        return new ViewModel(
            [
                'data' => $data,
                'page' => $this->params('page', 1),
                'document' => $args
            ]);
    }

    public function fieldEditAction()
    {
        $currentPage = $this->params('page', 1);
        $pdf_manager = $this->pdf_manager;
        $form = $pdf_manager->get('FormDocField');
        $params = [
            'data' => [
                'table' => 'docfields',
                'type' => 'system',
                'alias' => 'Sbm\Db\System\DocFields',
                'id' => 'docfieldId'
            ],
            'form' => $form
        ];
        $r = $this->editData($params, function ($post) {
            return $post;
        },
            function ($post) use ($pdf_manager, $form) {
                $columns = $pdf_manager->get(\SbmPdf\Model\Columns::class)
                    ->setRecordSource($post['documentId']);
                $form->setValueOptions('fieldname', $columns->getListeForSelect());
                $form->setData(
                    [
                        'name' => $post['name'],
                        'recordSource' => $post['recordSource']
                    ]);
            });
        if ($r instanceof Response) {
            return $r;
        } else {
            switch ($r->getStatus()) {
                case 'error':
                case 'warning':
                case 'success':
                    return $this->redirect()->toRoute('sbmpdf',
                        [
                            'action' => 'field-liste',
                            'page' => $currentPage
                        ]);
                    break;
                default:
                    return new ViewModel(
                        [
                            'form' => $form->prepare(),
                            'page' => $currentPage,
                            'document' => $r->getResult()
                        ]);
                    break;
            }
        }
    }

    public function fieldAjoutAction()
    {
        $currentPage = $this->params('page', 1);
        $pdf_manager = $this->pdf_manager;
        $form = $pdf_manager->get('FormDocField');
        $params = [
            'data' => [
                'table' => 'docfields',
                'type' => 'system',
                'alias' => 'Sbm\Db\System\DocFields'
            ],
            // 'id' => 'docfieldId'
            'form' => $form
        ];
        $r = $this->addData($params, function ($post) {
            return $post;
        },
            function ($post) use ($pdf_manager, $form) {
                $columns = $pdf_manager->get(\SbmPdf\Model\Columns::class)
                    ->setRecordSource($post['documentId']);
                $form->setValueOptions('fieldname', $columns->getListeForSelect());
            });
        switch ($r) {
            case $r instanceof Response:
                return $r;
                break;
            case 'error':
            case 'warning':
            case 'success':
                return $this->redirect()->toRoute('sbmpdf',
                    [
                        'action' => 'field-liste',
                        'page' => $currentPage
                    ]);
                break;
            default:
                $form->setData(
                    [
                        'documentId' => $r['documentId'],
                        'name' => $r['name'],
                        'recordSource' => $r['recordSource'],
                        'ordinal_position' => array_key_exists('new_position', $r) ? $r['new_position'] : 1
                    ]);
                $view = new ViewModel(
                    [
                        'form' => $form->prepare(),
                        'page' => $currentPage,
                        'document' => $r
                    ]);
                $view->setTemplate('sbm-pdf/pdf/field-edit.phtml');
                return $view;
                break;
        }
    }

    public function fieldDupliquerAction()
    {
        $currentPage = $this->params('page', 1);
        $pdf_manager = $this->pdf_manager;
        $form = $pdf_manager->get('FormDocField');
        $params = [
            'data' => [
                'table' => 'docfields',
                'type' => 'system',
                'alias' => 'Sbm\Db\System\DocFields'
            ],
            // 'id' => 'docfieldId'
            'form' => $form
        ];
        $r = $this->addData($params, function ($post) {
            return $post;
        },
            function ($post) use ($pdf_manager, $form) {
                $columns = $pdf_manager->get(\SbmPdf\Model\Columns::class)
                    ->setRecordSource($post['documentId']);
                $form->setValueOptions('fieldname', $columns->getListeForSelect());
                $form->setData(
                    [
                        'name' => $post['name'],
                        'recordSource' => $post['recordSource']
                    ]);
            });
        switch ($r) {
            case $r instanceof Response:
                return $r;
                break;
            case 'error':
            case 'warning':
            case 'success':
                return $this->redirect()->toRoute('sbmpdf',
                    [
                        'action' => 'field-liste',
                        'page' => $currentPage
                    ]);
                break;
            default:
                $acolonne = $this->db_manager->get('Sbm\Db\System\DocFields')
                    ->getRecord($r['docfieldId'])
                    ->getArrayCopy();
                $acolonne['ordinal_position'] = $r['new_position'];
                unset($r['ordinal_position']);
                unset($acolonne['docfieldId']);
                $form->setData($acolonne);
                $view = new ViewModel(
                    [
                        'form' => $form->prepare(),
                        'page' => $currentPage,
                        'document' => $r
                    ]);
                $view->setTemplate('sbm-pdf/pdf/field-edit.phtml');
                return $view;
                break;
        }
    }

    public function fieldSupprAction()
    {
        $currentPage = $this->params('page', 1);
        $form = new ButtonForm([
            'id' => null
        ],
            [
                'supproui' => [
                    'class' => 'confirm',
                    'value' => 'Confirmer'
                ],
                'supprnon' => [
                    'class' => 'confirm',
                    'value' => 'Abandonner'
                ]
            ]);
        $params = [
            'data' => [
                'alias' => 'Sbm\Db\System\DocFields',
                'id' => 'docfieldId'
            ],
            'form' => $form
        ];
        try {
            $r = $this->supprData($params,
                function ($id, $tDocFields) {
                    return [
                        'id' => $id,
                        'data' => $tDocFields->getRecord($id)
                    ];
                });
        } catch (\Zend\Db\Adapter\Exception\InvalidQueryException $e) {
            $this->flashMessenger()->addWarningMessage(
                'Impossible de supprimer ce champ.');
            return $this->redirect()->toRoute('sbmpdf',
                [
                    'action' => 'field-liste',
                    'page' => $currentPage
                ]);
        }

        if ($r instanceof Response) {
            return $r;
        } else {
            switch ($r->getStatus()) {
                case 'error':
                case 'warning':
                case 'success':
                    return $this->redirect()->toRoute('sbmpdf',
                        [
                            'action' => 'field-liste',
                            'page' => $currentPage
                        ]);
                    break;
                default:
                    return new ViewModel(
                        [
                            'form' => $form->prepare(),
                            'page' => $currentPage,
                            'data' => StdLib::getParam('data', $r->getResult()),
                            'docfieldId' => StdLib::getParam('id', $r->getResult())
                        ]);
                    break;
            }
        }
    }

    public function affectationListeAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false || array_key_exists('retour', $prg)) {
            $args = Session::get('post', false, $this->getSessionNamespace());
            if ($args === false) {
                return $this->redirect()->toRoute('sbmpdf');
            }
        } else {
            $args = $prg;
            if (array_key_exists('affecter', $args)) {
                unset($args['affecter']);
                Session::set('post', $args, $this->getSessionNamespace());
            }
        }
        try {
            $data = $this->db_manager->get('Sbm\Db\System\DocAffectations')->getConfig(
                $args['documentId']);
        } catch (\SbmCommun\Model\Db\Service\Table\Exception\ExceptionInterface $e) {
            $data = [];
        }
        return new ViewModel(
            [
                'data' => $data,
                'page' => $this->params('page', 1),
                'document' => $args
            ]);
    }

    public function affectationAjoutAction()
    {
        $currentPage = $this->params('page', 1);
        $pdf_manager = $this->pdf_manager;
        $form = $pdf_manager->get('FormDocAffectation');
        $params = [
            'data' => [
                'table' => 'docaffectations',
                'type' => 'system',
                'alias' => 'Sbm\Db\System\DocAffectations'
            ],
            // 'id' => 'docaffectationId'
            'form' => $form
        ];
        $r = $this->addData($params, function ($post) {
            return $post;
        },
            function ($post) use ($pdf_manager, $form) {
                $form->setValueOptions('route', $pdf_manager->get('ListeRoutes'));
            });
        switch ($r) {
            case $r instanceof Response:
                return $r;
                break;
            case 'error':
            case 'warning':
            case 'success':
                return $this->redirect()->toRoute('sbmpdf',
                    [
                        'action' => 'affectation-liste',
                        'page' => $currentPage
                    ]);
                break;
            default:
                $form->setData(
                    [
                        'documentId' => $r['documentId'],
                        'name' => $r['name'],
                        'recordSource' => $r['recordSource']
                    ]);
                // 'ordinal_position' => $r['new_position']

                $view = new ViewModel(
                    [
                        'form' => $form->prepare(),
                        'page' => $currentPage,
                        'document' => $r
                    ]);
                $view->setTemplate('sbm-pdf/pdf/affectation-edit.phtml');
                return $view;
                break;
        }
    }

    public function affectationEditAction()
    {
        $currentPage = $this->params('page', 1);
        $routes = $this->pdf_manager->get('ListeRoutes');
        $form = $this->pdf_manager->get('FormDocAffectation');
        $params = [
            'data' => [
                'table' => 'docaffectations',
                'type' => 'system',
                'alias' => 'Sbm\Db\System\DocAffectations',
                'id' => 'docaffectationId'
            ],
            'form' => $form
        ];

        $r = $this->editData($params, function ($post) {
            return $post;
        },
            function ($post) use ($routes, $form) {
                $form->setValueOptions('route', $routes);
            });
        if ($r instanceof Response) {
            return $r;
        } else {
            switch ($r->getStatus()) {
                case 'error':
                case 'warning':
                case 'success':
                    return $this->redirect()->toRoute('sbmpdf',
                        [
                            'action' => 'affectation-liste',
                            'page' => $currentPage
                        ]);
                    break;
                default:
                    return new ViewModel(
                        [
                            'form' => $form->prepare(),
                            'page' => $currentPage,
                            'tarifId' => $r->getResult()
                        ]);
                    break;
            }
        }
    }

    public function affectationSupprAction()
    {
        $currentPage = $this->params('page', 1);
        $form = new ButtonForm([
            'id' => null
        ],
            [
                'supproui' => [
                    'class' => 'confirm',
                    'value' => 'Confirmer'
                ],
                'supprnon' => [
                    'class' => 'confirm',
                    'value' => 'Abandonner'
                ]
            ]);
        $params = [
            'data' => [
                'alias' => 'Sbm\Db\System\DocAffectations',
                'id' => 'docaffectationId'
            ],
            'form' => $form
        ];
        try {
            $r = $this->supprData($params,
                function ($id, $tDocAffectations) {
                    return [
                        'id' => $id,
                        'data' => $tDocAffectations->getRecord($id)
                    ];
                });
        } catch (\Zend\Db\Adapter\Exception\InvalidQueryException $e) {
            $this->flashMessenger()->addWarningMessage(
                'Impossible de supprimer cette colonne.');
            return $this->redirect()->toRoute('sbmpdf',
                [
                    'action' => 'affectation-liste',
                    'page' => $currentPage
                ]);
        }

        if ($r instanceof Response) {
            return $r;
        } else {
            switch ($r->getStatus()) {
                case 'error':
                case 'warning':
                case 'success':
                    return $this->redirect()->toRoute('sbmpdf',
                        [
                            'action' => 'affectation-liste',
                            'page' => $currentPage
                        ]);
                    break;
                default:
                    return new ViewModel(
                        [
                            'form' => $form->prepare(),
                            'page' => $currentPage,
                            'data' => StdLib::getParam('data', $r->getResult()),
                            'docaffectationId' => StdLib::getParam('id', $r->getResult())
                        ]);
                    break;
            }
        }
    }
}
