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
use Zend\Db\Sql\Where;
use SbmCommun\Model\Db\ObjectData\Criteres as ObjectDataCriteres;
use SbmCommun\Form\CriteresForm;
use SbmAdmin\Form\DocumentPdf as FormDocumentPdf;
use SbmAdmin\Form\Libelle as FormLibelle;
use SbmCommun\Form\ButtonForm;


class IndexController extends AbstractActionController
{

    public function indexAction()
    {
        return new ViewModel();
    }

    public function libelleListeAction()
    {
        $currentPage = $this->params('page', 1);
        $system_libelles = $this->getServiceLocator()->get('Sbm\Db\System\Libelles');
        
        $config = $this->getServiceLocator()->get('Config');
        $nb_libelles_pagination = $config['liste']['paginator']['nb_libelles_pagination'];
        
        $criteres_form = new CriteresForm('libelles');
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
            'paginator' => $system_libelles->paginator($criteres_obj->getWhere()),
            'page' => $currentPage,
            'nb_libelles_pagination' => $nb_libelles_pagination,
            'criteres_form' => $criteres_form
        ));
    }

    public function libelleAjoutAction()
    {
        $currentPage = $this->params('page', 1);
        $tableLibelles = $this->getServiceLocator()->get('Sbm\Db\System\Libelles');
        $db = $this->getServiceLocator()->get('Sbm\Db\DbLib');
        
        $form = new FormLibelle();
        $form->setMaxLength($db->getMaxLengthArray('libelles', 'system'));        
        $form->bind($tableLibelles->getObjData());
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($request->getPost('cancel', false)) {
                $this->flashMessenger()->addWarningMessage("Les données n'ont pas été enregistrées.");
                return $this->redirect()->toRoute('sbmadmin', array(
                    'action' => 'libelle-liste',
                    'page' => $currentPage
                ));
            }
            $form->setData($request->getPost());
            if ($form->isValid()) { // controle le csrf
                if ($tableLibelles->saveRecordAvecControle($form->getData())) {
                    $this->flashMessenger()->addSuccessMessage("Les données ont été enregistrées.");
                    return $this->redirect()->toRoute('sbmadmin', array(
                        'action' => 'libelle-liste',
                        'page' => $currentPage
                    ));
                } else {
                    $this->flashMessenger()->addWarningMessage("Risque de doublons. Les données n'ont pas été enregistrées.");
                }
            }
        }
        return new ViewModel(array(
            'form' => $form,
            'page' => $currentPage
        ));
    }

    public function libelleEditAction()
    {
        $currentPage = $this->params('page', 1);
        $id = $this->params('id', - 1);
        $tableLibelles = $this->getServiceLocator()->get('Sbm\Db\System\Libelles');
        if ($id == - 1 || !$tableLibelles->getObjData()->isValidId($id)) {
            return $this->redirect()->toRoute('sbmadmin', array(
                'action' => 'libelle-liste',
                'page' => $currentPage
            ));
        }
        
        $db = $this->getServiceLocator()->get('Sbm\Db\DbLib');
        
        $form = new FormLibelle();
        $form->setMaxLength($db->getMaxLengthArray('libelles', 'system'));        
        $form->bind($tableLibelles->getObjData());
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($request->getPost('cancel', false)) {
                $this->flashMessenger()->addWarningMessage("L'enregistrement n'a pas été modifié.");
                return $this->redirect()->toRoute('sbmadmin', array(
                    'action' => 'libelle-liste',
                    'page' => $currentPage
                ));
            }
            $form->setData($request->getPost());
            if ($form->isValid()) { // controle le csrf
                if ($tableLibelles->saveRecordAvecControle($form->getData(), true, $id)) {
                    $this->flashMessenger()->addSuccessMessage("Les modifications ont été enregistrées.");
                    return $this->redirect()->toRoute('sbmadmin', array(
                        'action' => 'libelle-liste',
                        'page' => $currentPage
                    ));
                } else {
                    $this->flashMessenger()->addWarningMessage("Risque de doublons. Les données n'ont pas été enregistrées.");
                }
            }
        } else {
            list ($nature, $code) = explode('|', $id);
            $form->setData(array_merge($tableLibelles->getRecord(array(
                'nature' => $nature,
                'code' => $code
            ))
                ->getArrayCopy(), array(
                'id' => $id
            )));
        }
        return new ViewModel(array(
            'form' => $form,
            'page' => $currentPage,
            'id' => $id
        ));
    }

    public function libelleSupprAction()
    {
        $currentPage = $this->params('page', 1);
        $id_get = $this->params('id', - 1); // GET
        $tableLibelles = $this->getServiceLocator()->get('Sbm\Db\System\Libelles');
        if ($id_get == - 1 || !$tableLibelles->getObjData()->isValidId($id_get)) {
            return $this->redirect()->toRoute('sbmadmin', array(
                'action' => 'libelle-liste',
                'page' => $currentPage
            ));
        } 
        $form = new ButtonForm(array(
            'supproui' => array(
                'class' => 'confirm',
                'value' => 'Confirmer'
            ),
            'supprnon' => array(
                'class' => 'confirm',
                'value' => 'Abandonner'
            )
        ), array(
            'id' => $id_get
        ));
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($request->getPost('supproui', false)) { // confirmation
                $id_post = $this->params()->fromPost('id', false); // POST
                if ($id_post && $id_get == $id_post) {
                    list($nature, $code) = explode('|', $id_post);
                    $tableLibelles->deleteRecord(array('nature' => $nature, 'code' => $code));
                    $this->flashMessenger()->addSuccessMessage("L'enregistrement a été supprimé.");
                } else {
                    $this->flashMessenger()->addErrorMessage("Pas d'enregistrement à supprimer.");
                }
            } else { // abandon
                $this->flashMessenger()->addWarningMessage("L'enregistrement n'a pas été supprimé.");
            }
            return $this->redirect()->toRoute('sbmadmin', array(
                'action' => 'libelle-liste',
                'page' => $currentPage
            ));
        } else {
            if ($id_get) {
                $form->setData(array(
                    'id' => $id_get
                ));
            } else {
                $this->flashMessenger()->addErrorMessage("Pas d'enregistrement à supprimer.");
                return $this->redirect()->toRoute('sbmadmin', array(
                    'action' => 'libelle-liste',
                    'page' => $currentPage
                ));
            }
        }

        list($nature, $code) = explode('|', $id_get);
        return new ViewModel(array(
            'data' => $tableLibelles->getRecord(array('nature' => $nature, 'code' => $code)),
            'form' => $form,
            'page' => $currentPage,
            'id' => $id_get
        ));
    }

    public function libelleGroupAction()
    {
        $currentPage = $this->params('page', 1);
        $id_get = $this->params('id', - 1); // GET
        $tableLibelles = $this->getServiceLocator()->get('Sbm\Db\System\Libelles');
        if ($id_get == - 1 || !$tableLibelles->getObjData()->isValidId($id_get)) {
            return $this->redirect()->toRoute('sbmadmin', array(
                'action' => 'libelle-liste',
                'page' => $currentPage
            ));
        }
        
        $config = $this->getServiceLocator()->get('Config');
        $nb_libelles_pagination = $config['liste']['paginator']['nb_libelles_pagination'];
        
        list($nature, $code) = \explode('|', $id_get);
        $where = new Where();
        $where->expression('nature = ?', $nature);
        
        return new ViewModel(array(
            'paginator' => $tableLibelles->paginator($where),
            'page' => $currentPage,
            'nb_libelles_pagination' => $nb_libelles_pagination,
            'nature' => $nature
        ));
    }

    public function libellePdfAction()
    {
        $currentPage = $this->params('page', 1);
        
        $criteres_form = new CriteresForm('libelles');
        $criteres_obj = new ObjectDataCriteres($criteres_form->getElementNames());
        $session = new SessionContainer(str_replace('pdf', 'liste', $this->getSessionNamespace()));
        if (isset($session->criteres)) {
            $criteres_obj->exchangeArray($session->criteres);
        }
        
        $call_pdf = $this->getServiceLocator()->get('RenderPdfService');
        $call_pdf->setParam('documentId', 9)
        ->setParam('recordSource', 'Sbm\Db\System\Libelles')
        ->setParam('where', $criteres_obj->getWhere())
        ->setParam('orderBy', array('nature', 'code'))
        ->renderPdf();
        
        $this->flashMessenger()->addSuccessMessage("Création d'un pdf.");
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
                return $this->redirect()->toRoute('sbmadmin', array(
                    'action' => 'pdf-liste',
                    'page' => $currentPage
                ));
            }
            $form->setData($request->getPost());
            if ($form->isValid()) { // controle le csrf
                $systemDocument->saveRecord($form->getData());
                $this->flashMessenger()->addSuccessMessage("Les modifications ont été enregistrées.");
                return $this->redirect()->toRoute('sbmadmin', array(
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

    public function pdfPdfAction()
    {
        ;
    }

    public function pdfTexteAction()
    {
        return new ViewModel();
    }
}