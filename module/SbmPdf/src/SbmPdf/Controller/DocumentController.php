<?php
/**
 * Controller principal du module DafapTcpdf
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
use SbmCommun\Model\StdLib;
use SbmCommun\Form\ButtonForm;

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
        $db = $this->getServiceLocator()->get('Sbm\Db\DbLib');
        $form = new FormDocumentPdf($this->getServiceLocator());
        $form->setValueOptions('recordSource', $db->getTableAliasList());
        $params = array(
            'data' => array(
                'table' => 'documents',
                'type' => 'system',
                'alias' => 'Sbm\Db\System\Documents'
            ),
            'form' => $form
        );
        $r = $this->addData($params);
        switch ($r) {
            case $r instanceof Response:
                return $r;
                break;
            case 'error':
            case 'warning':
            case 'success':
                return $this->redirect()->toRoute('sbmpdf', array(
                'action' => 'pdf-liste',
                'page' => $currentPage
                ));
                break;
            default:
                $view = new ViewModel(array(
                    'form' => $form->prepare(),
                    'page' => $currentPage,
                    'documentId' => null
                ));
                $view->setTemplate('sbm-pdf/document/pdf-edit.phtml');
                return $view;
                break;
        }
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
        $form->setValueOptions('recordSource', $db->getTableAliasList());
        $params = array(
            'data' => array(
                'table' => 'documents',
                'type' => 'system',
                'alias' => 'Sbm\Db\System\Documents',
                'id' => 'documentId'
            ),
            'form' => $form
        );
        $r = $this->editData($params);
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
        $db = $this->getServiceLocator()->get('Sbm\Db\DbLib');
        $form = new FormDocumentPdf($this->getServiceLocator());
        $form->setValueOptions('recordSource', $db->getTableAliasList());
        $params = array(
            'data' => array(
                'table' => 'documents',
                'type' => 'system',
                'alias' => 'Sbm\Db\System\Documents'
            ),
            'form' => $form
        );
        // $r renvoie un Zend\Http\PhpEnvironment\Response ou une chaine compte-rendu ou documentId ou -1 (si le formulaire n'a pas validé)
        $r = $this->addData($params, function ($post) {
            return isset($post['documentId']) ? $post['documentId'] : - 1;
        });
        switch ($r) {
            case $r instanceof Response:
                return $r;
                break;
            case 'error':
            case 'warning':
            case 'success':
                return $this->redirect()->toRoute('sbmpdf', array(
                'action' => 'pdf-liste',
                'page' => $currentPage
                ));
                break;
            default:
                // $r est soit -1, soit $documentId
                if ($r > 0) {
                    $data = $this->getServiceLocator()
                    ->get('Sbm\Db\System\Documents')
                    ->getRecord($r)
                    ->getArrayCopy();
                    $data['documentId'] = null;
                    $form->setData($data);
                }
                $view = new ViewModel(array(
                    'form' => $form->prepare(),
                    'page' => $currentPage,
                    'documentId' => null
                ));
                $view->setTemplate('sbm-pdf/document/pdf-edit.phtml');
                return $view;
                break;
        }
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
}
