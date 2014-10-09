<?php
/**
 * Controller principal du module SbmAdmin
 *
 *
 * @project sbm
 * @package module/SbmGestion/src/SbmAdmin/Controller
 * @filesource IndexController.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 11 août 2014
 * @version 2014-1
 */
namespace SbmAdmin\Controller;

use SbmCommun\Model\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container as SessionContainer;
use SbmCommun\Model\Db\ObjectData\Criteres as ObjectDataCriteres;
use SbmCommun\Form\CriteresForm;
use SbmAdmin\Form\DocumentPdf as FormDocumentPdf;

class PdfController extends AbstractActionController
{

    public function indexAction()
    {
        return new ViewModel();
    }

    public function pdfListeAction()
    {
        $currentPage = $this->params('page', 1);
        $system_pdf = $this->getServiceLocator()->get('Sbm\Db\System\Documents');
        
        $config = $this->getServiceLocator()->get('Config');
        $nb_pdf_pagination = $config['liste']['paginator']['nb_pdf_pagination'];
        
        $criteres_form = new CriteresForm('pdf');
        $criteres_obj = new ObjectDataCriteres($criteres_form->getElementNames());
        
        // récupère les données du post et met en session
        $this->session = new SessionContainer($this->getSessionNamespace());
        $request = $this->getRequest();
        if ($request->isPost()) {
            $criteres_form->setData($request->getPost());
            if ($criteres_form->isValid()) {
                $criteres_obj->exchangeArray($criteres_form->getData());
                $this->session->criteres = $criteres_obj->getArrayCopy();
            } else {
                $criteres_form->reset(); // nécessaire pour remettre en place les control, submit et cancel du formulaire qui peuvent être écrasés par le post
            }
        }
        // récupère les données de la session si le post n'a pas validé
        if (! $criteres_form->hasValidated() && isset($this->session->criteres)) {
            $criteres_obj->exchangeArray($this->session->criteres);
            $criteres_form->setData($criteres_obj->getArrayCopy());
        }
        
        return new ViewModel(array(
            'paginator' => $system_pdf->paginator($criteres_obj->getWhere()),
            'page' => $currentPage,
            'nb_pdf_pagination' => $nb_pdf_pagination,
            'criteres_form' => $criteres_form
        ));
    }

    public function pdfPdfAction()
    {
        ;
    }

    public function pdfAjoutAction()
    {
        $currentPage = $this->params('page', 1);
        $documentId = null;
        $systemDocument = $this->getServiceLocator()->get('Sbm\Db\System\Documents');
        $db = $this->getServiceLocator()->get('Sbm\Db\DbLib');
        
        $form = new FormDocumentPdf($this->getServiceLocator());
        $form->setMaxLength($db->getMaxLengthArray('documents', 'system'));
        $form->setValueOptions('recordSource', $db->getTableList());
        
        $form->bind($systemDocument->getObjData());
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($request->getPost('cancel', false)) {
                $this->flashMessenger()->addWarningMessage("L'enregistrement n'a pas été modifié.");
                $this->redirect()->toRoute('sbmadmin/pdf', array(
                    'action' => 'pdf-liste',
                    'page' => $currentPage
                ));
            }
            $form->setData($request->getPost());
            if ($form->isValid()) { // controle le csrf
                $systemDocument->saveRecord($form->getData());
                $this->flashMessenger()->addSuccessMessage("Les modifications ont été enregistrées.");
                $this->redirect()->toRoute('sbmadmin/pdf', array(
                    'action' => 'pdf-liste',
                    'page' => $currentPage
                ));
            }
        } else {
            $dblib = $this->getServiceLocator()->get('Sbm\Db\DbLib');
            $form->setData($dblib->getColumnDefaults('documents', 'system'));
        }
        return new ViewModel(array(
            'form' => $form,
            'page' => $currentPage,
            'documentId' => $documentId
        ));
    }

    public function pdfEditAction()
    {
        ;
    }

    public function pdfDupliquerAction()
    {
        ;
    }

    public function pdfSupprAction()
    {
        ;
    }

    public function pdfGroupAction()
    {
        ;
    }

    public function pdfTexteAction()
    {
        return new ViewModel();
    }
}