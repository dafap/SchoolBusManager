<?php
/**
 * Controller principal du module SbmGestion
 *
 *
 * @project sbm
 * @package module/SbmGestion/src/SbmGestion/Controller
 * @filesource IndexController.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 12 févr. 2014
 * @version 2014-1
 */
namespace SbmGestion\Controller;

use Zend\View\Model\ViewModel;
use Zend\Session\Container as SessionContainer;
use SbmCommun\Model\Mvc\Controller\AbstractActionController;
use SbmCommun\Form\ButtonForm;
use SbmCommun\Form\Tarif as FormTarif;
use SbmCommun\Form\CriteresForm;
use SbmCommun\Model\Db\ObjectData\Criteres as ObjectDataCriteres;
use SbmCommun\Model\Db\Service\Table\AbstractSbmTable;

class FinanceController extends AbstractActionController
{

    /**
     * Menu de gestion financière
     *
     * (non-PHPdoc)
     *
     * @see \Zend\Mvc\Controller\AbstractActionController::indexAction()
     */
    public function indexAction()
    {
        return new ViewModel();
    }

    /**
     * Liste des tarifs
     * (avec pagination)
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function tarifListeAction()
    {
        $currentPage = $this->params('page', 1);
        $tableTarifs = $this->getServiceLocator()->get('Sbm\Db\Table\Tarifs');
        
        $criteres_form = new CriteresForm('tarifs');
        $criteres_form->setValueOptions('rythme', $tableTarifs->getRythmes());
        $criteres_form->setValueOptions('grille', $tableTarifs->getGrilles());
        $criteres_form->setValueOptions('mode', $tableTarifs->getModes());
        
        $criteres_obj = new ObjectDataCriteres($criteres_form->getElementNames());
        
        $config = $this->getServiceLocator()->get('Config');
        $nb_tarif_pagination = $config['liste']['paginator']['nb_tarif_pagination'];
        
        // récupère les données du post et met en session
        $session = new SessionContainer($this->getSessionNamespace());
        $request = $this->getRequest();
        if ($request->isPost()) {
            $criteres_form->setData($request->getPost());
            if ($criteres_form->isValid()) {
                $criteres_obj->exchangeArray($criteres_form->getData());
                $session->criteres = $criteres_obj->getArrayCopy();
            } else {
                $criteres_form->reset(); // nécessaire pour remettre en place les control, submit et cancel du formulaire qui peuvent être écrasés par le post
            }
        }
        // récupère les données de la session si le post n'a pas validé
        if (! $criteres_form->hasValidated() && isset($session->criteres)) {
            $criteres_obj->exchangeArray($session->criteres);
            $criteres_form->setData($criteres_obj->getArrayCopy());
        }
        
        return new ViewModel(array(
            'paginator' => $tableTarifs->paginator($criteres_obj->getWhere(), 'nom'),
            'page' => $currentPage,
            'nb_tarif_pagination' => $nb_tarif_pagination,
            'criteres_form' => $criteres_form
        ));
    }

    /**
     * Modification d'une fiche de tarif
     * (la validation porte sur un champ csrf)
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function tarifEditAction()
    {
        $currentPage = $this->params('page', 1);
        $tarifId = $this->params('id', - 1);
        if ($tarifId == - 1) {
            $this->redirect()->toRoute('sbmgestion/finance', array(
                'action' => 'tarif-liste',
                'page' => $currentPage
            ));
        }
        $tableTarifs = $this->getServiceLocator()->get('Sbm\Db\Table\Tarifs');
        $db = $this->getServiceLocator()->get('Sbm\Db\DbLib');
        
        $form = new FormTarif();
        $form->setValueOptions('rythme', array_combine($tableTarifs->getRythmes(), $tableTarifs->getRythmes()));
        $form->setValueOptions('grille', array_combine($tableTarifs->getGrilles(), $tableTarifs->getGrilles()));
        $form->setValueOptions('mode', array_combine($tableTarifs->getModes(), $tableTarifs->getModes()));
        $form->setMaxLength($db->getMaxLengthArray('tarifs', 'table'));
        
        $form->bind($tableTarifs->getObjData());
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($request->getPost('cancel', false)) {
                $this->flashMessenger()->addWarningMessage("L'enregistrement n'a pas été modifié.");
                $this->redirect()->toRoute('sbmgestion/finance', array(
                    'action' => 'tarif-liste',
                    'page' => $currentPage
                ));
            }
            $form->setData($request->getPost());
            if ($form->isValid()) { // controle le csrf
                $tableTarifs->saveRecord($form->getData());
                $this->flashMessenger()->addSuccessMessage("Les modifications ont été enregistrées.");
                $this->redirect()->toRoute('sbmgestion/finance', array(
                    'action' => 'tarif-liste',
                    'page' => $currentPage
                ));
            }
        } else {
            $form->setData($tableTarifs->getRecord($tarifId)
                ->getArrayCopy());
        }
        return new ViewModel(array(
            'form' => $form,
            'page' => $currentPage,
            'tarifId' => $tarifId
        ));
    }

    /**
     * Suppression d'une fiche avec confirmation
     *
     * @todo : Vérifier qu'il n'y a pas d'élève inscrit avant de supprimer la fiche
     *      
     * @return \Zend\View\Model\ViewModel
     */
    public function tarifSupprAction()
    {
        $currentPage = $this->params('page', 1);
        $tarifId = $this->params('id', - 1); // GET
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
            'id' => $tarifId
        ));
        $tableTarifs = $this->getServiceLocator()->get('Sbm\Db\Table\Tarifs');
        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($request->getPost('supproui', false)) { // confirmation
                $tarifId = $this->params()->fromPost('id', false); // POST
                if ($tarifId) {
                    $tableTarifs->deleteRecord($tarifId);
                    $this->flashMessenger()->addSuccessMessage("L'enregistrement a été supprimé.");
                } else {
                    $this->flashMessenger()->addErrorMessage("Pas d'enregistrement à supprimer.");
                }
            } else { // abandon
                $this->flashMessenger()->addWarningMessage("L'enregistrement n'a pas été supprimé.");
            }
            $this->redirect()->toRoute('sbmgestion/finance', array(
                'action' => 'tarif-liste',
                'page' => $currentPage
            ));
        } else {
            if ($tarifId) {
                $form->setData(array(
                    'id' => $tarifId
                ));
            } else {
                $this->flashMessenger()->addErrorMessage("Pas d'enregistrement à supprimer.");
                $this->redirect()->toRoute('sbmgestion/finance', array(
                    'action' => 'tarif-liste',
                    'page' => $currentPage
                ));
            }
        }
        
        return new ViewModel(array(
            'data' => $tableTarifs->getRecord($tarifId),
            'form' => $form,
            'page' => $currentPage,
            'tarifId' => $tarifId
        ));
    }

    /**
     * Ajout d'une nouvelle fiche de tarif
     * (la validation porte sur un champ csrf)
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function tarifAjoutAction()
    {
        $currentPage = $this->params('page', 1);
        $tarifId = null;
        $tableTarifs = $this->getServiceLocator()->get('Sbm\Db\Table\Tarifs');
        $db = $this->getServiceLocator()->get('Sbm\Db\DbLib');
        
        $form = new FormTarif();
        $form->setValueOptions('rythme', array_combine($tableTarifs->getRythmes(), $tableTarifs->getRythmes()));
        $form->setValueOptions('grille', array_combine($tableTarifs->getGrilles(), $tableTarifs->getGrilles()));
        $form->setValueOptions('mode', array_combine($tableTarifs->getModes(), $tableTarifs->getModes()));
        $form->setMaxLength($db->getMaxLengthArray('tarifs', 'table'));
        
        $form->bind($tableTarifs->getObjData());
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($request->getPost('cancel', false)) {
                $this->flashMessenger()->addWarningMessage("L'enregistrement n'a pas été modifié.");
                $this->redirect()->toRoute('sbmgestion/finance', array(
                    'action' => 'tarif-liste',
                    'page' => $currentPage
                ));
            }
            $form->setData($request->getPost());
            if ($form->isValid()) { // controle le csrf
                $tableTarifs->saveRecord($form->getData());
                $this->flashMessenger()->addSuccessMessage("Les modifications ont été enregistrées.");
                $this->redirect()->toRoute('sbmgestion/finance', array(
                    'action' => 'tarif-liste',
                    'page' => $currentPage
                ));
            }
        }
        return new ViewModel(array(
            'form' => $form,
            'page' => $currentPage,
            'tarifId' => $tarifId
        ));
    }

    /**
     * renvoie la liste des élèves inscrits pour un tarif donné
     *
     * @todo : à faire
     *      
     * @return \Zend\View\Model\ViewModel
     */
    public function tarifGroupAction()
    {
        $currentPage = $this->params('page', 1);
        $tarifId = $this->params('id', - 1); // GET
        $tableTarifs = $this->getServiceLocator()->get('Sbm\Db\Table\Tarifs');
        return new ViewModel(array(
            'data' => $tableTarifs->getRecord($tarifId),
            // 'paginator' => $table_eleves->paginator(),
            'page' => $currentPage,
            'tarifId' => $tarifId
        ));
    }

    /**
     * envoie un evenement contenant les paramètres de création d'un document pdf
     * (le listener DafapTcpdf\Listener\PdfListener lancera la création du pdf)
     * Il n'y a pas de vue associée à cette action puisque la response html est créée par \TCPDF
     */
    public function tarifPdfAction()
    {
        $currentPage = $this->params('page', 1);
        
        $criteres_form = new CriteresForm('tarifs');
        $criteres_obj = new ObjectDataCriteres($criteres_form->getElementNames());
        $session = new SessionContainer(str_replace('pdf', 'liste', $this->getSessionNamespace()));
        if (isset($session->criteres)) {
            $criteres_obj->exchangeArray($session->criteres);
        }
        $call_pdf = $this->getServiceLocator()->get('RenderPdfService');
        $call_pdf->setParam('documentId', 6)
        ->setParam('recordSource', 'Sbm\Db\Table\Tarifs')
        ->setParam('where', $criteres_obj->getWhere())
        ->setParam('orderBy', array('grille', 'mode', 'rythme', 'nom'))
        ->renderPdf();
        
        $this->flashMessenger()->addSuccessMessage("Création d'un pdf.");
    }
}