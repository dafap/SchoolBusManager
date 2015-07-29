<?php
/**
 * Controller principal du module SbmPdf
 *
 * Gestion des la création et de la modification des documents pdf
 * 
 * @project sbm
 * @package SbmPdf/Controller
 * @filesource DocumentController.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 1 juil. 2015
 * @version 2015-2
 */
namespace SbmPdf\Controller;

use SbmCommun\Model\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Http\PhpEnvironment\Response;
use SbmPdf\Form\DocumentPdf as FormDocumentPdf;
use SbmPdf\Form\DocTable as FormDocTable;
use SbmPdf\Form\DocColumn as FormDocColumn;
use SbmPdf\Form\DocField as FormDocField;
use SbmPdf\Form\DocLabel as FormDocLabel;
use SbmCommun\Model\StdLib;
use SbmCommun\Form\ButtonForm;
use SbmPdf\Form\SbmPdf\Form;

class DocumentController extends AbstractActionController
{

    /**
     * Dresse la liste des documents pdf
     *
     * @return \Zend\Http\PhpEnvironment\Response|\Zend\View\Model\ViewModel
     */
    public function pdfListeAction()
    {
        $args = $this->initListe('pdf');
        if ($args instanceof Response)
            return $args;
        
        return new ViewModel(array(
            'paginator' => $this->getServiceLocator()
                ->get('Sbm\Db\System\Documents')
                ->paginator($args['where']),
            'page' => $this->params('page', 1),
            'nb_pagination' => $this->getNbPagination('nb_pdf', 10),
            'criteres_form' => $args['form']
        ));
    }

    /**
     * Affiche et traite le formulaire d'ajout d'un document.
     * Le formulaire est initialisé par les valeurs par défaut.
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
            $args = $this->getFromSession('post', false, $this->getSessionNamespace());
            if ($args === false) {
                $this->flashMessenger()->addErrorMessage("Action interdite.");
                return $this->redirect()->toRoute('sbmpdf', array(
                    'action' => 'pdf-liste',
                    'page' => $currentPage
                ));
            }
            $isPost = false;
        } else {
            $args = $prg;
            if (array_key_exists('cancel', $args)) {
                $this->flashMessenger()->addWarningMessage("Aucun enregistrement n'a été ajouté.");
                return $this->redirect()->toRoute('sbmpdf', array(
                    'action' => 'pdf-liste',
                    'page' => $currentPage
                ));
            }
            $isPost = array_key_exists('submit', $args);
            unset($args['submit']);
            $this->setToSession('post', $args, $this->getSessionNamespace());
        }
        $tDocuments = $this->getServiceLocator()->get('Sbm\Db\System\Documents');
        $db = $this->getServiceLocator()->get('Sbm\Db\DbLib');
        $form = new FormDocumentPdf($this->getServiceLocator());
        $form->setValueOptions('TrecordSource', $db->getTableAliasList());
        $form->setMaxLength($db->getMaxLengthArray('documents', 'system'));
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
                $this->flashMessenger()->addSuccessMessage("Un nouvel enregistrement a été ajouté.");
                // création des sections dans doctables, de la fiche étiquette ou de la fiche texte
                switch ($oData->disposition) {
                    case 'Tabulaire':
                        $tDoctables = $this->getServiceLocator()->get('Sbm\Db\System\DocTables');
                        $oDoctable = $tDoctables->getObjData();
                        $defaults = include (__DIR__ . '/../Model/default/doctables.inc.php');
                        foreach (array(
                            'thead',
                            'tbody',
                            'tfoot'
                        ) as $section) {
                            $defaults[$section]['documentId'] = $documentId;
                            $oDoctable->exchangeArray($defaults[$section]);
                            $oDoctable->section = $section;
                            $tDoctables->saveRecord($oDoctable);
                        }
                        break;
                    case 'Etiquette':
                        $tDoclabels = $this->getServiceLocator()->get('Sbm\Db\System\DocLabels');
                        $oDoclabel = $tDoclabels->getObjData();
                        $defaults = include (__DIR__ . '/../Model/default/doclabels.inc.php');                      
                        $defaults['documentId'] = $documentId;
                        $oDoclabel->exchangeArray($defaults);
                        $tDoclabels->saveRecord($oDoclabel);                       
                        break;
                    default: // Texte
                        break;
                }
                // création des colonnes du tableau
                
                // retour à la liste des documents pdf
                return $this->redirect()->toRoute('sbmpdf', array(
                    'action' => 'pdf-liste',
                    'page' => $currentPage
                ));
            }
        } else {
            // initialisation du formulaire
            $defaults = array_merge($db->getColumnDefaults('documents', 'system'), include (__DIR__ . '/../Model/default/documents.inc.php'));
            $form->setData($defaults);
        }
        $view = new ViewModel(array(
            'form' => $form->prepare(),
            'page' => $currentPage,
            'documentId' => null
        ));
        $view->setTemplate('sbm-pdf/document/pdf-edit.phtml');
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
        $db = $this->getServiceLocator()->get('Sbm\Db\DbLib');
        $form = new FormDocumentPdf($this->getServiceLocator());
        $form->setValueOptions('TrecordSource', $db->getTableAliasList());
        $params = array(
            'data' => array(
                'table' => 'documents',
                'type' => 'system',
                'alias' => 'Sbm\Db\System\Documents',
                'id' => 'documentId'
            ),
            'form' => $form
        );
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
                    return $this->redirect()->toRoute('sbmpdf', array(
                        'action' => 'pdf-liste',
                        'page' => $currentPage
                    ));
                    break;
                default:
                    return new ViewModel(array(
                        'form' => $form->prepare(),
                        'page' => $currentPage,
                        'documentId' => $r->getResult()
                    ));
                    break;
            }
        }
    }

    /**
     * Affiche et traite le formulaire d'ajout d'un document.
     *
     * Le formulaire est initialisé par les valeurs du formulaire de la ligne cliquée (documentId en post au moment de l'appel).
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
            $args = $this->getFromSession('post', false, $this->getSessionNamespace());
            if ($args === false) {
                $this->flashMessenger()->addErrorMessage("Action interdite.");
                return $this->redirect()->toRoute('sbmpdf', array(
                    'action' => 'pdf-liste',
                    'page' => $currentPage
                ));
            }
            $isPost = false;
        } else {
            $args = $prg;
            if (array_key_exists('cancel', $args)) {
                $this->flashMessenger()->addWarningMessage("Aucun enregistrement n'a été ajouté.");
                return $this->redirect()->toRoute('sbmpdf', array(
                    'action' => 'pdf-liste',
                    'page' => $currentPage
                ));
            }
            $isPost = array_key_exists('submit', $args);
            unset($args['submit']);
            $this->setToSession('post', $args, $this->getSessionNamespace());
        }
        $tDocuments = $this->getServiceLocator()->get('Sbm\Db\System\Documents');
        $db = $this->getServiceLocator()->get('Sbm\Db\DbLib');
        $form = new FormDocumentPdf($this->getServiceLocator());
        $form->setValueOptions('TrecordSource', $db->getTableAliasList());
        $form->setMaxLength($db->getMaxLengthArray('documents', 'system'));
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
                $this->flashMessenger()->addSuccessMessage("Un nouvel enregistrement a été ajouté.");
                // création des sections dans doctables, de la fiche étiquette ou de la fiche texte
                switch ($oData->disposition) {
                    case 'Tabulaire':
                        $tDoctables = $this->getServiceLocator()->get('Sbm\Db\System\DocTables');
                        $oDoctable = $tDoctables->getObjData();
                        $defaults = include (__DIR__ . '/../Model/default/doctables.inc.php');
                        foreach (array(
                            'thead',
                            'tbody',
                            'tfoot'
                        ) as $section) {
                            $defaults[$section]['documentId'] = $documentId;
                            $oDoctable->exchangeArray($defaults[$section]);
                            $oDoctable->section = $section;
                            $tDoctables->saveRecord($oDoctable);
                        }
                        break;
                    case 'Etiquette':
                        $tDoclabels = $this->getServiceLocator()->get('Sbm\Db\System\DocLabels');
                        $oDoclabel = $tDoclabels->getObjData();
                        $defaults = include (__DIR__ . '/../Model/default/doclabels.inc.php');
                        $defaults['documentId'] = $documentId;
                        $oDoclabel->exchangeArray($defaults);
                        $tDoclabels->saveRecord($oDoclabel);
                        break;
                    default: // Texte
                        break;
                }
                // création des colonnes du tableau
                
                // retour à la liste des documents pdf
                return $this->redirect()->toRoute('sbmpdf', array(
                    'action' => 'pdf-liste',
                    'page' => $currentPage
                ));
            }
        } else {
            // initialisation du formulaire
            $documentId = isset($args['documentId']) ? $args['documentId'] : - 1;
            if ($documentId > 0) {
                $data = $this->getServiceLocator()
                    ->get('Sbm\Db\System\Documents')
                    ->getRecord($documentId)
                    ->getArrayCopy();
                unset($data['documentId']);
                $form->setData($data);
            } else {
                $defaults = array_merge($db->getColumnDefaults('documents', 'system'), include (__DIR__ . '/../Model/default/documents.inc.php'));
                $form->setData($defaults);
            }
        }
        $view = new ViewModel(array(
            'form' => $form->prepare(),
            'page' => $currentPage,
            'documentId' => null
        ));
        $view->setTemplate('sbm-pdf/document/pdf-edit.phtml');
        return $view;
    }

    public function pdfSupprAction()
    {
        $currentPage = $this->params('page', 1);
        $form = new ButtonForm(array(
            'id' => null
        ), array(
            'supproui' => array(
                'class' => 'confirm',
                'value' => 'Confirmer'
            ),
            'supprnon' => array(
                'class' => 'confirm',
                'value' => 'Abandonner'
            )
        ));
        $params = array(
            'data' => array(
                'alias' => 'Sbm\Db\System\Documents',
                'id' => 'documentId'
            ),
            'form' => $form
        );
        
        try {
            $r = $this->supprData($params, function ($id, $tableClasses) {
                return array(
                    'id' => $id,
                    'data' => $tableClasses->getRecord($id)
                );
            });
        } catch (\Zend\Db\Adapter\Exception\InvalidQueryException $e) {
            $this->flashMessenger()->addWarningMessage('Impossible de supprimer ce document parce que certains élèves y sont inscrits.');
            return $this->redirect()->toRoute('sbmpdf', array(
                'action' => 'pdf-liste',
                'page' => $currentPage
            ));
        }
        
        if ($r instanceof Response) {
            return $r;
        } else {
            switch ($r->getStatus()) {
                case 'error':
                case 'warning':
                case 'success':
                    return $this->redirect()->toRoute('sbmpdf', array(
                        'action' => 'pdf-liste',
                        'page' => $currentPage
                    ));
                    break;
                default:
                    return new ViewModel(array(
                        'form' => $form->prepare(),
                        'page' => $currentPage,
                        'data' => StdLib::getParam('data', $r->getResult()),
                        'documentId' => StdLib::getParam('id', $r->getResult())
                    ));
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
        $call_pdf = $this->getServiceLocator()->get('RenderPdfService');
        $call_pdf->setParam('documentId', $documentId)->renderPdf();
    }

    public function pdfGroupAction()
    {
        ;
    }

    public function pdfPdfAction()
    {
        ;
    }

    public function pdfTexteAction()
    {
        return new ViewModel();
    }
    
    // ====================================================================================================================================
    
    /**
     * Présente les 3 sections d'un tableau d'un document pdf
     * Reçoit des données en post, dont les données obligatoires suivantes :
     * - documentId
     * - name
     * - ordinal_table
     * - recordSource
     *
     * @return \Zend\Http\PhpEnvironment\Response|\Zend\View\Model\ViewModel
     */
    public function tableListeAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            $args = $this->getFromSession('post', false, $this->getSessionNamespace());
            if ($args === false) {
                return $this->redirect()->toRoute('sbmpdf');
            }
        } else {
            $args = $prg;
            if (array_key_exists('disposition', $args)) {
                $args['ordinal_table'] = 1;
                unset($args['disposition']);
                $this->setToSession('post', $args, $this->getSessionNamespace());
            }
        }
        return new ViewModel(array(
            'data' => $this->getServiceLocator()
                ->get('Sbm\Db\System\DocTables')
                ->getConfig($args['documentId'], $args['ordinal_table']),
            'page' => $this->params('page', 1),
            'document' => $args
        ));
    }

    /**
     *
     * Reçoit des données en post, dont les données obligatoires suivantes :
     * - doctableId
     * - documentId
     * - name
     * - ordinal_table
     * - recordSource
     *
     * @return \Zend\Http\PhpEnvironment\Response|\Zend\Http\Response|\Zend\View\Model\ViewModel
     */
    public function tableEditAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            $args = $this->getFromSession('post', false, $this->getSessionNamespace());
            if ($args === false) {
                return $this->redirect()->toRoute('sbmpdf');
            }
        } else {
            $args = $prg;
            if (array_key_exists('cancel', $args)) {
                return $this->redirect()->toRoute('sbmpdf', array(
                    'action' => 'table-liste',
                    'page' => $this->params('page', 1)
                ));
            } elseif (array_key_exists('modifier', $args)) {
                $args['ordinal_table'] = 1;
                unset($args['modifier']);
                $this->setToSession('post', $args, $this->getSessionNamespace());
            }
        }
        $tDocTables = $this->getServiceLocator()->get('Sbm\Db\System\DocTables');
        $form = new FormDocTable();
        $form->bind($tDocTables->getObjData());
        if (array_key_exists('submit', $args)) {
            $section = $args['section'];
            $form->setData($args);
            if ($form->isValid()) {
                $tDocTables->saveRecord($form->getData());
                $this->flashMessenger()->addSuccessMessage('Section enregistrée.');
                return $this->redirect()->toRoute('sbmpdf', array(
                    'action' => 'table-liste',
                    'page' => $this->params('page', 1)
                ));
            }
        } else {
            $data = $tDocTables->getRecord($args['doctableId']);
            $form->setData($data->getArrayCopy());
            $section = $data->section;
        }
        
        return new ViewModel(array(
            'section' => array(
                'doctableId' => $args['doctableId'],
                'documentId' => $args['documentId'],
                'name' => $args['name'],
                'ordinal_table' => $args['ordinal_table'],
                'recordSource' => $args['recordSource'],
                'section' => $section
            ),
            'form' => $form,
            'page' => $this->params('page', 1)
        ));
    }
    
    // ===================================================================================================
    
    /**
     * Reçoit par post les paramètres suivants :
     * - documentId
     * - name
     * - ordinal_table
     * - recordSource
     *
     * @return \Zend\Http\PhpEnvironment\Response|\Zend\Http\Response|\Zend\View\Model\ViewModel
     */
    public function colonneListeAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false || array_key_exists('retour', $prg)) {
            $args = $this->getFromSession('post', false, $this->getSessionNamespace());
            if ($args === false) {
                return $this->redirect()->toRoute('sbmpdf');
            }
        } else {
            $args = $prg;
            if (array_key_exists('colonnes', $args)) {
                unset($args['colonnes']);
                $this->setToSession('post', $args, $this->getSessionNamespace());
            }
        }
        try {
            $data = $this->getServiceLocator()
                ->get('Sbm\Db\System\DocTables\Columns')
                ->getConfig($args['documentId'], $args['ordinal_table']);
        } catch (\SbmCommun\Model\Db\Service\Table\Exception $e) {
            $data = array();
        }
        return new ViewModel(array(
            'data' => $data,
            'page' => $this->params('page', 1),
            'document' => $args
        ));
    }

    public function colonneEditAction()
    {
        $currentPage = $this->params('page', 1);
        $sm = $this->getServiceLocator();
        $form = new FormDocColumn($sm);
        $params = array(
            'data' => array(
                'table' => 'doccolumns',
                'type' => 'system',
                'alias' => 'Sbm\Db\System\DocTables\Columns',
                'id' => 'doccolumnId'
            ),
            'form' => $form
        );
        $r = $this->editData($params, function ($post) {
            return $post;
        }, function ($post) use($sm, $form) {
            $columns = new \SbmPdf\Model\Columns($sm, $post['recordSource']);
            $form->setValueOptions('tbody', $columns->getListeForSelect());
            $form->setData(array(
                'name' => $post['name'],
                'recordSource' => $post['recordSource']
            ));
        });
        if ($r instanceof Response) {
            return $r;
        } else {
            switch ($r->getStatus()) {
                case 'error':
                case 'warning':
                case 'success':
                    return $this->redirect()->toRoute('sbmpdf', array(
                        'action' => 'colonne-liste',
                        'page' => $currentPage
                    ));
                    break;
                default:
                    return new ViewModel(array(
                        'form' => $form->prepare(),
                        'page' => $currentPage,
                        'document' => $r->getResult()
                    ));
                    break;
            }
        }
    }

    public function colonneAjoutAction()
    {
        $currentPage = $this->params('page', 1);
        $sm = $this->getServiceLocator();
        $form = new FormDocColumn($sm);
        $params = array(
            'data' => array(
                'table' => 'doccolumns',
                'type' => 'system',
                'alias' => 'Sbm\Db\System\DocTables\Columns'
            ),
            // 'id' => 'doccolumnId'
            'form' => $form
        );
        $r = $this->addData($params, function ($post) {
            return $post;
        }, function ($post) use($sm, $form) {
            $columns = new \SbmPdf\Model\Columns($sm, $post['recordSource']);
            $form->setValueOptions('tbody', $columns->getListeForSelect());
        });
        switch ($r) {
            case $r instanceof Response:
                return $r;
                break;
            case 'error':
            case 'warning':
            case 'success':
                return $this->redirect()->toRoute('sbmpdf', array(
                    'action' => 'colonne-liste',
                    'page' => $currentPage
                ));
                break;
            default:
                $form->setData(array(
                    'documentId' => $r['documentId'],
                    'name' => $r['name'],
                    'recordSource' => $r['recordSource'],
                    'ordinal_position' => $r['new_position']
                ));
                $view = new ViewModel(array(
                    'form' => $form->prepare(),
                    'page' => $currentPage,
                    'document' => $r
                ));
                $view->setTemplate('sbm-pdf/document/colonne-edit.phtml');
                return $view;
                break;
        }
    }

    public function colonneDupliquerAction()
    {
        $currentPage = $this->params('page', 1);
        $sm = $this->getServiceLocator();
        $form = new FormDocColumn($sm);
        $params = array(
            'data' => array(
                'table' => 'doccolumns',
                'type' => 'system',
                'alias' => 'Sbm\Db\System\DocTables\Columns'
            ),
            // 'id' => 'doccolumnId'
            'form' => $form
        );
        $r = $this->addData($params, function ($post) {
            return $post;
        }, function ($post) use($sm, $form) {
            $columns = new \SbmPdf\Model\Columns($sm, $post['recordSource']);
            $form->setValueOptions('tbody', $columns->getListeForSelect());
            $form->setData(array(
                'name' => $post['name'],
                'recordSource' => $post['recordSource']
            ));
        });
        switch ($r) {
            case $r instanceof Response:
                return $r;
                break;
            case 'error':
            case 'warning':
            case 'success':
                return $this->redirect()->toRoute('sbmpdf', array(
                    'action' => 'colonne-liste',
                    'page' => $currentPage
                ));
                break;
            default:
                $tDocColumns = $this->getServiceLocator()->get('Sbm\Db\System\DocTables\Columns');
                $acolonne = $tDocColumns->getRecord($r['doccolumnId'])->getArrayCopy();
                $acolonne['ordinal_position'] = $r['new_position'];
                unset($r['ordinal_position']);
                unset($acolonne['doccolumnId']);
                $form->setData($acolonne);
                $view = new ViewModel(array(
                    'form' => $form->prepare(),
                    'page' => $currentPage,
                    'document' => $r
                ));
                $view->setTemplate('sbm-pdf/document/colonne-edit.phtml');
                return $view;
                break;
        }
    }

    public function colonneSupprAction()
    {
        $currentPage = $this->params('page', 1);
        $form = new ButtonForm(array(
            'id' => null
        ), array(
            'supproui' => array(
                'class' => 'confirm',
                'value' => 'Confirmer'
            ),
            'supprnon' => array(
                'class' => 'confirm',
                'value' => 'Abandonner'
            )
        ));
        $params = array(
            'data' => array(
                'alias' => 'Sbm\Db\System\DocTables\Columns',
                'id' => 'doccolumnId'
            ),
            'form' => $form
        );
        try {
            $r = $this->supprData($params, function ($id, $tDocColumns) {
                return array(
                    'id' => $id,
                    'data' => $tDocColumns->getRecord($id)
                );
            });
        } catch (\Zend\Db\Adapter\Exception\InvalidQueryException $e) {
            $this->flashMessenger()->addWarningMessage('Impossible de supprimer cette colonne.');
            return $this->redirect()->toRoute('sbmpdf', array(
                'action' => 'colonne-liste',
                'page' => $currentPage
            ));
        }
        
        if ($r instanceof Response) {
            return $r;
        } else {
            switch ($r->getStatus()) {
                case 'error':
                case 'warning':
                case 'success':
                    return $this->redirect()->toRoute('sbmpdf', array(
                        'action' => 'colonne-liste',
                        'page' => $currentPage
                    ));
                    break;
                default:
                    return new ViewModel(array(
                        'form' => $form->prepare(),
                        'page' => $currentPage,
                        'data' => StdLib::getParam('data', $r->getResult()),
                        'doccolumnId' => StdLib::getParam('id', $r->getResult())
                    ));
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
     * Reçoit par post les paramètres suivants :
     * - disposition (uniquement au moment de l'appel)
     * - documentId
     * - name
     * - recordSource
     *
     * @return \Zend\Http\PhpEnvironment\Response|\Zend\Http\Response|\Zend\View\Model\ViewModel
     */
    public function etiquetteFormatAction()
    {
        $currentPage = $this->params('page', 1);
        $form = new FormDocLabel();
        $params = array(
            'data' => array(
                'table' => 'doclabels',
                'type' => 'system',
                'alias' => 'Sbm\Db\System\DocLabels',
                'id' => 'documentId'
            ),
            'form' => $form
        );
        $r = $this->editData($params, function ($post) {
            return $post;
        }, function ($post) use($form) {
            $form->setData(array(
                'name' => $post['name'],
                'recordSource' => $post['recordSource']
            ));
        });
        if ($r instanceof Response) {
            return $r;
        } else {
            switch ($r->getStatus()) {
                case 'error':
                case 'warning':
                case 'success':
                    return $this->redirect()->toRoute('sbmpdf', array(
                        'action' => 'pdf-liste',
                        'page' => $currentPage
                    ));
                    break;
                default:
                    return new ViewModel(array(
                        'form' => $form->prepare(),
                        'page' => $currentPage,
                        'document' => $r->getResult()
                    ));
                    break;
            }
        }
    }
    
    // ============================================================================================
    
    /**
     * Reçoit par post les paramètres suivants :
     * - documentId
     * - name
     * - recordSource
     *
     * @return \Zend\Http\PhpEnvironment\Response|\Zend\Http\Response|\Zend\View\Model\ViewModel
     */
    public function fieldListeAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false || array_key_exists('retour', $prg)) {
            $args = $this->getFromSession('post', false, $this->getSessionNamespace());
            if ($args === false) {
                return $this->redirect()->toRoute('sbmpdf');
            }
        } else {
            $args = $prg;
            if (array_key_exists('champs', $args)) {
                unset($args['champs']);
                $this->setToSession('post', $args, $this->getSessionNamespace());
            }
        }
        try {
            $data = $this->getServiceLocator()
                ->get('Sbm\Db\System\DocFields')
                ->getConfig($args['documentId']);
        } catch (\SbmCommun\Model\Db\Service\Table\Exception $e) {
            $data = array();
        }
        return new ViewModel(array(
            'data' => $data,
            'page' => $this->params('page', 1),
            'document' => $args
        ));
    }

    public function fieldEditAction()
    {
        $currentPage = $this->params('page', 1);
        $sm = $this->getServiceLocator();
        $form = new FormDocField($sm);
        $params = array(
            'data' => array(
                'table' => 'docfields',
                'type' => 'system',
                'alias' => 'Sbm\Db\System\DocFields',
                'id' => 'docfieldId'
            ),
            'form' => $form
        );
        $r = $this->editData($params, function ($post) {
            return $post;
        }, function ($post) use($sm, $form) {
            $columns = new \SbmPdf\Model\Columns($sm, $post['recordSource']);
            $form->setValueOptions('fieldname', $columns->getListeForSelect());
            $form->setData(array(
                'name' => $post['name'],
                'recordSource' => $post['recordSource']
            ));
        });
        if ($r instanceof Response) {
            return $r;
        } else {
            switch ($r->getStatus()) {
                case 'error':
                case 'warning':
                case 'success':
                    return $this->redirect()->toRoute('sbmpdf', array(
                        'action' => 'field-liste',
                        'page' => $currentPage
                    ));
                    break;
                default:
                    return new ViewModel(array(
                        'form' => $form->prepare(),
                        'page' => $currentPage,
                        'document' => $r->getResult()
                    ));
                    break;
            }
        }
    }

    public function fieldAjoutAction()
    {
        $currentPage = $this->params('page', 1);
        $sm = $this->getServiceLocator();
        $form = new FormDocField($sm);
        $params = array(
            'data' => array(
                'table' => 'docfields',
                'type' => 'system',
                'alias' => 'Sbm\Db\System\DocFields'
            ),
            // 'id' => 'docfieldId'
            'form' => $form
        );
        $r = $this->addData($params, function ($post) {
            return $post;
        }, function ($post) use($sm, $form) {
            $columns = new \SbmPdf\Model\Columns($sm, $post['recordSource']);
            $form->setValueOptions('fieldname', $columns->getListeForSelect());
        });
        switch ($r) {
            case $r instanceof Response:
                return $r;
                break;
            case 'error':
            case 'warning':
            case 'success':
                return $this->redirect()->toRoute('sbmpdf', array(
                    'action' => 'field-liste',
                    'page' => $currentPage
                ));
                break;
            default:
                $form->setData(array(
                    'documentId' => $r['documentId'],
                    'name' => $r['name'],
                    'recordSource' => $r['recordSource'],
                    'ordinal_position' => array_key_exists('new_position', $r) ? $r['new_position'] : 1
                ));
                $view = new ViewModel(array(
                    'form' => $form->prepare(),
                    'page' => $currentPage,
                    'document' => $r
                ));
                $view->setTemplate('sbm-pdf/document/field-edit.phtml');
                return $view;
                break;
        }
    }

    public function fieldDupliquerAction()
    {
        $currentPage = $this->params('page', 1);
        $sm = $this->getServiceLocator();
        $form = new FormDocField($sm);
        $params = array(
            'data' => array(
                'table' => 'docfields',
                'type' => 'system',
                'alias' => 'Sbm\Db\System\DocFields'
            ),
            // 'id' => 'docfieldId'
            'form' => $form
        );
        $r = $this->addData($params, function ($post) {
            return $post;
        }, function ($post) use($sm, $form) {
            $columns = new \SbmPdf\Model\Columns($sm, $post['recordSource']);
            $form->setValueOptions('fieldname', $columns->getListeForSelect());
            $form->setData(array(
                'name' => $post['name'],
                'recordSource' => $post['recordSource']
            ));
        });
        switch ($r) {
            case $r instanceof Response:
                return $r;
                break;
            case 'error':
            case 'warning':
            case 'success':
                return $this->redirect()->toRoute('sbmpdf', array(
                    'action' => 'field-liste',
                    'page' => $currentPage
                ));
                break;
            default:
                $acolonne = $this->getServiceLocator()
                    ->get('Sbm\Db\System\DocFields')
                    ->getRecord($r['docfieldId'])
                    ->getArrayCopy();
                $acolonne['ordinal_position'] = $r['new_position'];
                unset($r['ordinal_position']);
                unset($acolonne['docfieldId']);
                $form->setData($acolonne);
                $view = new ViewModel(array(
                    'form' => $form->prepare(),
                    'page' => $currentPage,
                    'document' => $r
                ));
                $view->setTemplate('sbm-pdf/document/field-edit.phtml');
                return $view;
                break;
        }
    }

    public function fieldSupprAction()
    {
        $currentPage = $this->params('page', 1);
        $form = new ButtonForm(array(
            'id' => null
        ), array(
            'supproui' => array(
                'class' => 'confirm',
                'value' => 'Confirmer'
            ),
            'supprnon' => array(
                'class' => 'confirm',
                'value' => 'Abandonner'
            )
        ));
        $params = array(
            'data' => array(
                'alias' => 'Sbm\Db\System\DocFields',
                'id' => 'docfieldId'
            ),
            'form' => $form
        );
        try {
            $r = $this->supprData($params, function ($id, $tDocFields) {
                return array(
                    'id' => $id,
                    'data' => $tDocFields->getRecord($id)
                );
            });
        } catch (\Zend\Db\Adapter\Exception\InvalidQueryException $e) {
            $this->flashMessenger()->addWarningMessage('Impossible de supprimer ce champ.');
            return $this->redirect()->toRoute('sbmpdf', array(
                'action' => 'field-liste',
                'page' => $currentPage
            ));
        }
        
        if ($r instanceof Response) {
            return $r;
        } else {
            switch ($r->getStatus()) {
                case 'error':
                case 'warning':
                case 'success':
                    return $this->redirect()->toRoute('sbmpdf', array(
                        'action' => 'field-liste',
                        'page' => $currentPage
                    ));
                    break;
                default:
                    return new ViewModel(array(
                        'form' => $form->prepare(),
                        'page' => $currentPage,
                        'data' => StdLib::getParam('data', $r->getResult()),
                        'docfieldId' => StdLib::getParam('id', $r->getResult())
                    ));
                    break;
            }
        }
    }
}
