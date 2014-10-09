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
use SbmCommun\Form\Classe as FormClasse;
use SbmCommun\Form\Commune as FormCommune;
use SbmCommun\Form\Etablissement as FormEtablissement;
use SbmCommun\Form\Service as FormService;
use SbmCommun\Form\Station as FormStation;
use SbmCommun\Form\Transporteur as FormTransporteur;
use SbmCommun\Form\CriteresForm;
use SbmCommun\Model\Db\ObjectData\Criteres as ObjectDataCriteres;

class TransportController extends AbstractActionController
{

    public function indexAction()
    {
        return new ViewModel();
    }

    /**
     * =============================================== CIRCUITS ==================================================
     */
    
    /**
     * Liste des circuits
     * (avec pagination)
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function circuitListeAction()
    {
        $currentPage = $this->params('page', 1);
        $table_circuits = $this->getCircuitLocator()->get('Sbm\Db\Vue\Circuits');
        
        $criteres_form = new CriteresForm('circuits');
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
        
        $config = $this->getCircuitLocator()->get('Config');
        $nb_circuit_pagination = $config['liste']['paginator']['nb_circuit_pagination'];
        
        return new ViewModel(array(
            'paginator' => $table_circuits->paginator($criteres_obj->getWhere()),
            'page' => $currentPage,
            'nb_circuit_pagination' => $nb_circuit_pagination,
            'criteres_form' => $criteres_form
        ));
    }

    /**
     * Modification d'une fiche de circuit
     * (la validation porte sur un champ csrf)
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function circuitEditAction()
    {
        $currentPage = $this->params('page', 1);
        $circuitId = $this->params('id', - 1);
        if ($circuitId == - 1) {
            $this->redirect()->toRoute('sbmgestion/transport', array(
                'action' => 'circuit-liste',
                'page' => $currentPage
            ));
        }
        $tableCircuits = $this->getCircuitLocator()->get('Sbm\Db\Table\Circuits');
        $db = $this->getServiceLocator()->get('Sbm\Db\DbLib');
        
        $form = new FormCircuit();
        $form->modifFormForEdit();
        $form->setMaxLength($db->getMaxLengthArray('circuits', 'table'));
        $form->bind($tableCircuits->getObjData());
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($request->getPost('cancel', false)) {
                $this->flashMessenger()->addWarningMessage("L'enregistrement n'a pas été modifié.");
                $this->redirect()->toRoute('sbmgestion/transport', array(
                    'action' => 'circuit-liste',
                    'page' => $currentPage
                ));
            }
            $form->setData($request->getPost());
            if ($form->isValid()) { // controle le csrf
                $tableCircuits->saveRecord($form->getData());
                $this->flashMessenger()->addSuccessMessage("Les modifications ont été enregistrées.");
                $this->redirect()->toRoute('sbmgestion/transport', array(
                    'action' => 'circuit-liste',
                    'page' => $currentPage
                ));
            }
        } else {
            $form->setData($tableCircuits->getRecord($circuitId)
                ->getArrayCopy());
        }
        return new ViewModel(array(
            'form' => $form,
            'page' => $currentPage,
            'circuitId' => $circuitId
        ));
    }

    /**
     * Suppression d'une fiche avec confirmation
     *
     * @todo : Vérifier qu'il n'y a pas d'élève inscrit avant de supprimer la fiche
     *      
     * @return \Zend\View\Model\ViewModel
     */
    public function circuitSupprAction()
    {
        $currentPage = $this->params('page', 1);
        $circuitId = $this->params('id', - 1); // GET
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
            'id' => $circuitId
        ));
        $tableCircuits = $this->getCircuitLocator()->get('Sbm\Db\Table\Circuits');
        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($request->getPost('supproui', false)) { // confirmation
                $circuitId = $this->params()->fromPost('id', false); // POST
                if ($circuitId) {
                    $tableCircuits->deleteRecord($circuitId);
                    $this->flashMessenger()->addSuccessMessage("L'enregistrement a été supprimé.");
                } else {
                    $this->flashMessenger()->addErrorMessage("Pas d'enregistrement à supprimer.");
                }
            } else { // abandon
                $this->flashMessenger()->addWarningMessage("L'enregistrement n'a pas été supprimé.");
            }
            $this->redirect()->toRoute('sbmgestion/transport', array(
                'action' => 'circuit-liste',
                'page' => $currentPage
            ));
        } else {
            if ($circuitId) {
                $form->setData(array(
                    'id' => $circuitId
                ));
            } else {
                $this->flashMessenger()->addErrorMessage("Pas d'enregistrement à supprimer.");
                $this->redirect()->toRoute('sbmgestion/transport', array(
                    'action' => 'circuit-liste',
                    'page' => $currentPage
                ));
            }
        }
        
        return new ViewModel(array(
            'data' => $tableCircuits->getRecord($circuitId),
            'form' => $form,
            'page' => $currentPage,
            'circuitId' => $circuitId
        ));
    }

    /**
     * Ajout d'une nouvelle fiche de circuit
     * (la validation porte sur un champ csrf)
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function circuitAjoutAction()
    {
        $currentPage = $this->params('page', 1);
        $circuitId = null;
        $tableCircuits = $this->getCircuitLocator()->get('Sbm\Db\Table\Circuits');
        $db = $this->getServiceLocator()->get('Sbm\Db\DbLib');
        
        $form = new FormCircuit();
        $form->setMaxLength($db->getMaxLengthArray('circuits', 'table'));
        $form->bind($tableCircuits->getObjData());
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($request->getPost('cancel', false)) {
                $this->flashMessenger()->addWarningMessage("L'enregistrement n'a pas été modifié.");
                $this->redirect()->toRoute('sbmgestion/transport', array(
                    'action' => 'circuit-liste',
                    'page' => $currentPage
                ));
            }
            $form->setData($request->getPost());
            if ($form->isValid()) { // controle le csrf
                $tableCircuits->saveRecord($form->getData());
                $this->flashMessenger()->addSuccessMessage("Les modifications ont été enregistrées.");
                $this->redirect()->toRoute('sbmgestion/transport', array(
                    'action' => 'circuit-liste',
                    'page' => $currentPage
                ));
            }
        }
        return new ViewModel(array(
            'form' => $form,
            'page' => $currentPage,
            'circuitId' => $circuitId
        ));
    }

    /**
     * renvoie la liste des élèves inscrits pour un circuit donné
     *
     * @todo : à faire
     *      
     * @return \Zend\View\Model\ViewModel
     */
    public function circuitGroupAction()
    {
        $currentPage = $this->params('page', 1);
        $circuitId = $this->params('id', - 1); // GET
        $tableCircuits = $this->getCircuitLocator()->get('Sbm\Db\Table\Circuits');
        return new ViewModel(array(
            'data' => $tableCircuits->getRecord($circuitId),
            // 'paginator' => $table_eleves->paginator(),
            'page' => $currentPage,
            'circuitId' => $circuitId
        ));
    }

    /**
     * envoie un evenement contenant les paramètres de création d'un document pdf
     * (le listener DafapTcpdf\Listener\PdfListener lancera la création du pdf)
     * Il n'y a pas de vue associée à cette action puisque la response html est créée par \TCPDF
     */
    public function circuitPdfAction()
    {
        $currentPage = $this->params('page', 1);
        
        $criteres_form = new CriteresForm('circuits');
        $criteres_obj = new ObjectDataCriteres($criteres_form->getElementNames());
        $session = new SessionContainer(str_replace('pdf', 'liste', $this->getSessionNamespace()));
        if (isset($session->criteres)) {
            $criteres_obj->exchangeArray($session->criteres);
        }
        
        $call_pdf = $this->getServiceLocator()->get('RenderPdfService');
        $call_pdf->setData(array(
            'sm' => $this->getServiceLocator(),
            'table' => 'Sbm\Db\Vue\Circuits',
            'fields' => array(
                'serviceId',
                'arret',
                'distance',
                'h1',
                'h2'
            ),
            'where' => $criteres_obj->getWhere(),
            'orderBy' => 'nom'
        ))
            ->setHead(array(
            'Code service',
            'Arrêt',
            'Distance',
            'Horaire 1',
            'Horaire 2'
        ))
            ->setPdfConfig(array(
            'title' => 'Liste des circuits',
            'header' => array(
                'title' => 'Liste des circuits',
                'string' => 'éditée par School Bus Manager le ' . date('d/m/Y à H:i')
            )
        ))
            ->setTableConfig(array(
            'tbody' => array(
                'cell' => array(
                    'txt_precision' => array(
                        0,
                        0,
                        3,
                        0,
                        0
                    )
                )
            ),
            'column_widths' => array(
                64,
                30,
                30,
                20,
                36
            )
        ))
            ->renderPdf();
        
        $this->flashMessenger()->addSuccessMessage("Création d'un pdf.");
    }

    /**
     * =============================================== CLASSES ==================================================
     */
    
    /**
     * Liste des classes
     * (avec pagination)
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function classeListeAction()
    {
        $currentPage = $this->params('page', 1);
        $table_classes = $this->getServiceLocator()->get('Sbm\Db\Table\Classes');
        
        $config = $this->getServiceLocator()->get('Config');
        $nb_classe_pagination = $config['liste']['paginator']['nb_classe_pagination'];
        
        $criteres_form = new CriteresForm('classes');
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
            'paginator' => $table_classes->paginator($criteres_obj->getWhere()),
            'page' => $currentPage,
            'nb_classe_pagination' => $nb_classe_pagination,
            'criteres_form' => $criteres_form
        ));
    }

    /**
     * Modification d'une fiche de classe
     * (la validation porte sur un champ csrf)
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function classeEditAction()
    {
        $currentPage = $this->params('page', 1);
        $classeId = $this->params('id', - 1);
        if ($classeId == - 1) {
            $this->redirect()->toRoute('sbmgestion/transport', array(
                'action' => 'classe-liste',
                'page' => $currentPage
            ));
        }
        $tableClasses = $this->getServiceLocator()->get('Sbm\Db\Table\Classes');
        $db = $this->getServiceLocator()->get('Sbm\Db\DbLib');
        
        $form = new FormClasse();
        $form->setValueOptions('niveau', $tableClasses->getNiveaux());
        $form->setMaxLength($db->getMaxLengthArray('classes', 'table'));
        $form->bind($tableClasses->getObjData());
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($request->getPost('cancel', false)) {
                $this->flashMessenger()->addWarningMessage("L'enregistrement n'a pas été modifié.");
                $this->redirect()->toRoute('sbmgestion/transport', array(
                    'action' => 'classe-liste',
                    'page' => $currentPage
                ));
            }
            $form->setData($request->getPost());
            if ($form->isValid()) { // controle le csrf
                $tableClasses->saveRecord($form->getData());
                $this->flashMessenger()->addSuccessMessage("Les modifications ont été enregistrées.");
                $this->redirect()->toRoute('sbmgestion/transport', array(
                    'action' => 'classe-liste',
                    'page' => $currentPage
                ));
            }
        } else {
            $form->setData($tableClasses->getRecord($classeId)
                ->getArrayCopy());
        }
        return new ViewModel(array(
            'form' => $form,
            'page' => $currentPage,
            'classeId' => $classeId
        ));
    }

    /**
     * Suppression d'une fiche avec confirmation
     *
     * @todo : Vérifier qu'il n'y a pas d'élève inscrit avant de supprimer la fiche
     *      
     * @return \Zend\View\Model\ViewModel
     */
    public function classeSupprAction()
    {
        $currentPage = $this->params('page', 1);
        $classeId = $this->params('id', - 1); // GET
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
            'id' => $classeId
        ));
        $tableClasses = $this->getServiceLocator()->get('Sbm\Db\Table\Classes');
        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($request->getPost('supproui', false)) { // confirmation
                $classeId = $this->params()->fromPost('id', false); // POST
                if ($classeId) {
                    $tableClasses->deleteRecord($classeId);
                    $this->flashMessenger()->addSuccessMessage("L'enregistrement a été supprimé.");
                } else {
                    $this->flashMessenger()->addErrorMessage("Pas d'enregistrement à supprimer.");
                }
            } else { // abandon
                $this->flashMessenger()->addWarningMessage("L'enregistrement n'a pas été supprimé.");
            }
            $this->redirect()->toRoute('sbmgestion/transport', array(
                'action' => 'classe-liste',
                'page' => $currentPage
            ));
        } else {
            if ($classeId) {
                $form->setData(array(
                    'id' => $classeId
                ));
            } else {
                $this->flashMessenger()->addErrorMessage("Pas d'enregistrement à supprimer.");
                $this->redirect()->toRoute('sbmgestion/transport', array(
                    'action' => 'classe-liste',
                    'page' => $currentPage
                ));
            }
        }
        
        return new ViewModel(array(
            'data' => $tableClasses->getRecord($classeId),
            'form' => $form,
            'page' => $currentPage,
            'classeId' => $classeId
        ));
    }

    /**
     * Ajout d'une nouvelle fiche de classe
     * (la validation porte sur un champ csrf)
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function classeAjoutAction()
    {
        $currentPage = $this->params('page', 1);
        $classeId = null;
        $tableClasses = $this->getServiceLocator()->get('Sbm\Db\Table\Classes');
        $db = $this->getServiceLocator()->get('Sbm\Db\DbLib');
        
        $form = new FormClasse();
        $form->setValueOptions('niveau', $tableClasses->getNiveaux());
        $form->setMaxLength($db->getMaxLengthArray('classes', 'table'));
        $form->bind($tableClasses->getObjData());
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($request->getPost('cancel', false)) {
                $this->flashMessenger()->addWarningMessage("L'enregistrement n'a pas été modifié.");
                $this->redirect()->toRoute('sbmgestion/transport', array(
                    'action' => 'classe-liste',
                    'page' => $currentPage
                ));
            }
            $form->setData($request->getPost());
            if ($form->isValid()) { // controle le csrf
                $tableClasses->saveRecord($form->getData());
                $this->flashMessenger()->addSuccessMessage("Les modifications ont été enregistrées.");
                $this->redirect()->toRoute('sbmgestion/transport', array(
                    'action' => 'classe-liste',
                    'page' => $currentPage
                ));
            }
        }
        return new ViewModel(array(
            'form' => $form,
            'page' => $currentPage,
            'classeId' => $classeId
        ));
    }

    /**
     * renvoie la liste des élèves inscrits pour une classe donnée
     *
     * @todo : à faire
     *      
     * @return \Zend\View\Model\ViewModel
     */
    public function classeGroupAction()
    {
        $currentPage = $this->params('page', 1);
        $classeId = $this->params('id', - 1); // GET
        $tableClasses = $this->getServiceLocator()->get('Sbm\Db\Table\Classes');
        return new ViewModel(array(
            'data' => $tableClasses->getRecord($classeId),
            // 'paginator' => $table_eleves->paginator(),
            'page' => $currentPage,
            'classeId' => $classeId
        ));
    }

    /**
     * envoie un evenement contenant les paramètres de création d'un document pdf
     * (le listener DafapTcpdf\Listener\PdfListener lancera la création du pdf)
     * Il n'y a pas de vue associée à cette action puisque la response html est créée par \TCPDF
     */
    public function classePdfAction()
    {
        $currentPage = $this->params('page', 1);
        
        $criteres_form = new CriteresForm('classes');
        $criteres_obj = new ObjectDataCriteres($criteres_form->getElementNames());
        $session = new SessionContainer(str_replace('pdf', 'liste', $this->getSessionNamespace()));
        if (isset($session->criteres)) {
            $criteres_obj->exchangeArray($session->criteres);
        }
        
        $call_pdf = $this->getServiceLocator()->get('RenderPdfService');
        $call_pdf->setParam('documentId', 1)
            ->setParam('recordSource', 'Sbm\Db\Table\Classes')
            ->setParam('where', $criteres_obj->getWhere())
            ->setParam('orderBy', 'classeId')
            ->renderPdf();
        
        $this->flashMessenger()->addSuccessMessage("Création d'un pdf.");
    }

    /**
     * =============================================== COMMUNES ==================================================
     */
    
    /**
     * Liste des communes
     * (avec pagination)
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function communeListeAction()
    {
        $currentPage = $this->params('page', 1);
        $table_communes = $this->getServiceLocator()->get('Sbm\Db\Table\Communes');
        
        $config = $this->getServiceLocator()->get('Config');
        $nb_commune_pagination = $config['liste']['paginator']['nb_commune_pagination'];
        
        $criteres_form = new CriteresForm('communes');
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
            'paginator' => $table_communes->paginator($criteres_obj->getWhere()),
            'page' => $currentPage,
            'nb_commune_pagination' => $nb_commune_pagination,
            'criteres_form' => $criteres_form
        ));
    }

    /**
     * Modification d'une fiche de commune
     * (la validation porte sur un champ csrf)
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function communeEditAction()
    {
        $currentPage = $this->params('page', 1);
        $communeId = $this->params('id', - 1);
        if ($communeId == - 1) {
            $this->redirect()->toRoute('sbmgestion/transport', array(
                'action' => 'commune-liste',
                'page' => $currentPage
            ));
        }
        $tableCommunes = $this->getServiceLocator()->get('Sbm\Db\Table\Communes');
        $db = $this->getServiceLocator()->get('Sbm\Db\DbLib');
        
        $form = new FormCommune();
        $form->modifFormForEdit();
        $form->setMaxLength($db->getMaxLengthArray('communes', 'table'));
        $form->bind($tableCommunes->getObjData());
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($request->getPost('cancel', false)) {
                $this->flashMessenger()->addWarningMessage("L'enregistrement n'a pas été modifié.");
                $this->redirect()->toRoute('sbmgestion/transport', array(
                    'action' => 'commune-liste',
                    'page' => $currentPage
                ));
            }
            $form->setData($request->getPost());
            if ($form->isValid()) { // controle le csrf
                $tableCommunes->saveRecord($form->getData());
                $this->flashMessenger()->addSuccessMessage("Les modifications ont été enregistrées.");
                $this->redirect()->toRoute('sbmgestion/transport', array(
                    'action' => 'commune-liste',
                    'page' => $currentPage
                ));
            }
        } else {
            $form->setData($tableCommunes->getRecord($communeId)
                ->getArrayCopy());
        }
        return new ViewModel(array(
            'form' => $form,
            'page' => $currentPage,
            'communeId' => $communeId
        ));
    }

    /**
     * Suppression d'une fiche avec confirmation
     *
     * @todo : Vérifier qu'il n'y a pas d'élève inscrit avant de supprimer la fiche
     *      
     * @return \Zend\View\Model\ViewModel
     */
    public function communeSupprAction()
    {
        $currentPage = $this->params('page', 1);
        $communeId = $this->params('id', - 1); // GET
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
            'id' => $communeId
        ));
        $tableCommunes = $this->getServiceLocator()->get('Sbm\Db\Table\Communes');
        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($request->getPost('supproui', false)) { // confirmation
                $communeId = $this->params()->fromPost('id', false); // POST
                if ($communeId) {
                    $tableCommunes->deleteRecord($communeId);
                    $this->flashMessenger()->addSuccessMessage("L'enregistrement a été supprimé.");
                } else {
                    $this->flashMessenger()->addErrorMessage("Pas d'enregistrement à supprimer.");
                }
            } else { // abandon
                $this->flashMessenger()->addWarningMessage("L'enregistrement n'a pas été supprimé.");
            }
            $this->redirect()->toRoute('sbmgestion/transport', array(
                'action' => 'commune-liste',
                'page' => $currentPage
            ));
        } else {
            if ($communeId) {
                $form->setData(array(
                    'id' => $communeId
                ));
            } else {
                $this->flashMessenger()->addErrorMessage("Pas d'enregistrement à supprimer.");
                $this->redirect()->toRoute('sbmgestion/transport', array(
                    'action' => 'commune-liste',
                    'page' => $currentPage
                ));
            }
        }
        
        return new ViewModel(array(
            'data' => $tableCommunes->getRecord($communeId),
            'form' => $form,
            'page' => $currentPage,
            'communeId' => $communeId
        ));
    }

    /**
     * Ajout d'une nouvelle fiche de commune
     * (la validation porte sur un champ csrf)
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function communeAjoutAction()
    {
        $currentPage = $this->params('page', 1);
        $communeId = null;
        $tableCommunes = $this->getServiceLocator()->get('Sbm\Db\Table\Communes');
        $db = $this->getServiceLocator()->get('Sbm\Db\DbLib');
        
        $form = new FormCommune();
        $form->setMaxLength($db->getMaxLengthArray('communes', 'table'));
        $form->bind($tableCommunes->getObjData());
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($request->getPost('cancel', false)) {
                $this->flashMessenger()->addWarningMessage("L'enregistrement n'a pas été modifié.");
                $this->redirect()->toRoute('sbmgestion/transport', array(
                    'action' => 'commune-liste',
                    'page' => $currentPage
                ));
            }
            $form->setData($request->getPost());
            if ($form->isValid()) { // controle le csrf
                $tableCommunes->saveRecord($form->getData());
                $this->flashMessenger()->addSuccessMessage("Les modifications ont été enregistrées.");
                $this->redirect()->toRoute('sbmgestion/transport', array(
                    'action' => 'commune-liste',
                    'page' => $currentPage
                ));
            }
        }
        return new ViewModel(array(
            'form' => $form,
            'page' => $currentPage,
            'communeId' => $communeId
        ));
    }

    /**
     * renvoie la liste des élèves inscrits pour une commune donnée
     *
     * @todo : à faire
     *      
     * @return \Zend\View\Model\ViewModel
     */
    public function communeGroupAction()
    {
        $currentPage = $this->params('page', 1);
        $communeId = $this->params('id', - 1); // GET
        $tableCommunes = $this->getServiceLocator()->get('Sbm\Db\Table\Communes');
        return new ViewModel(array(
            'data' => $tableCommunes->getRecord($communeId),
            // 'paginator' => $table_eleves->paginator(),
            'page' => $currentPage,
            'communeId' => $communeId
        ));
    }

    /**
     * envoie un evenement contenant les paramètres de création d'un document pdf
     * (le listener DafapTcpdf\Listener\PdfListener lancera la création du pdf)
     * Il n'y a pas de vue associée à cette action puisque la response html est créée par \TCPDF
     */
    public function communePdfAction()
    {
        $currentPage = $this->params('page', 1);
        
        $criteres_form = new CriteresForm('communes');
        $criteres_obj = new ObjectDataCriteres($criteres_form->getElementNames());
        $session = new SessionContainer(str_replace('pdf', 'liste', $this->getSessionNamespace()));
        if (isset($session->criteres)) {
            $criteres_obj->exchangeArray($session->criteres);
        }
        
        $call_pdf = $this->getServiceLocator()->get('RenderPdfService');
        $call_pdf->setParam('documentId', 2)
        ->setParam('recordSource', 'Sbm\Db\Table\Communes')
        ->setParam('where', $criteres_obj->getWhere())
        ->setParam('orderBy', array('departement', 'nom'))
        ->renderPdf();        
        
        $this->flashMessenger()->addSuccessMessage("Création d'un pdf.");
    }

    /**
     * =============================================== ETABLISSEMENTS ==================================================
     */
    
    /**
     * Liste des etablissements
     * (avec pagination)
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function etablissementListeAction()
    {
        $currentPage = $this->params('page', 1);
        $table_etablissements = $this->getServiceLocator()->get('Sbm\Db\Vue\Etablissements');
        
        $config = $this->getServiceLocator()->get('Config');
        $nb_etablissement_pagination = $config['liste']['paginator']['nb_etablissement_pagination'];
        
        $criteres_form = new CriteresForm('etablissements');
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
            'paginator' => $table_etablissements->paginator($criteres_obj->getWhere()),
            'page' => $currentPage,
            'nb_etablissement_pagination' => $nb_etablissement_pagination,
            'criteres_form' => $criteres_form
        ));
    }

    /**
     * Modification d'une fiche d'etablissement
     * (la validation porte sur un champ csrf)
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function etablissementEditAction()
    {
        $currentPage = $this->params('page', 1);
        $etablissementId = $this->params('id', - 1);
        if ($etablissementId == - 1) {
            $this->redirect()->toRoute('sbmgestion/transport', array(
                'action' => 'etablissement-liste',
                'page' => $currentPage
            ));
        }
        $tableEtablissements = $this->getServiceLocator()->get('Sbm\Db\Table\Etablissements');
        $db = $this->getServiceLocator()->get('Sbm\Db\DbLib');
        
        $form = new FormEtablissement();
        $form->modifFormForEdit();
        $form->setValueOptions('jOuverture', $tableEtablissements->getSemaine());
        $form->setValueOptions('niveau', $tableEtablissements->getNiveau());
        $form->setValueOptions('rattacheA', $this->getServiceLocator()
            ->get('Sbm\Db\Select\EtablissementsVisibles'));
        $form->setValueOptions('communeId', $this->getServiceLocator()
            ->get('Sbm\Db\Select\CommunesDesservies'));
        $form->setMaxLength($db->getMaxLengthArray('etablissements', 'table'));
        
        $form->bind($tableEtablissements->getObjData());
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($request->getPost('cancel', false)) {
                $this->flashMessenger()->addWarningMessage("L'enregistrement n'a pas été modifié.");
                $this->redirect()->toRoute('sbmgestion/transport', array(
                    'action' => 'etablissement-liste',
                    'page' => $currentPage
                ));
            }
            $form->setData($request->getPost());
            if ($form->isValid()) { // controle le csrf
                $tableEtablissements->saveRecord($form->getData());
                $this->flashMessenger()->addSuccessMessage("Les modifications ont été enregistrées.");
                $this->redirect()->toRoute('sbmgestion/transport', array(
                    'action' => 'etablissement-liste',
                    'page' => $currentPage
                ));
            }
        } else {
            $form->setData($tableEtablissements->getRecord($etablissementId)
                ->getArrayCopy());
        }
        return new ViewModel(array(
            'form' => $form,
            'page' => $currentPage,
            'etablissementId' => $etablissementId
        ));
    }

    /**
     * Suppression d'une fiche avec confirmation
     *
     * @todo : Vérifier qu'il n'y a pas d'élève inscrit avant de supprimer la fiche
     *      
     * @return \Zend\View\Model\ViewModel
     */
    public function etablissementSupprAction()
    {
        $currentPage = $this->params('page', 1);
        $etablissementId = $this->params('id', - 1); // GET
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
            'id' => $etablissementId
        ));
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($request->getPost('supproui', false)) { // confirmation
                $etablissementId = $this->params()->fromPost('id', false); // POST
                if ($etablissementId) {
                    $tableEtablissements = $this->getServiceLocator()->get('Sbm\Db\Table\Etablissements');
                    $tableEtablissements->deleteRecord($etablissementId);
                    $this->flashMessenger()->addSuccessMessage("L'enregistrement a été supprimé.");
                } else {
                    $this->flashMessenger()->addErrorMessage("Pas d'enregistrement à supprimer.");
                }
            } else { // abandon
                $this->flashMessenger()->addWarningMessage("L'enregistrement n'a pas été supprimé.");
            }
            $this->redirect()->toRoute('sbmgestion/transport', array(
                'action' => 'etablissement-liste',
                'page' => $currentPage
            ));
        } else {
            if ($etablissementId) {
                $form->setData(array(
                    'id' => $etablissementId
                ));
            } else {
                $this->flashMessenger()->addErrorMessage("Pas d'enregistrement à supprimer.");
                $this->redirect()->toRoute('sbmgestion/transport', array(
                    'action' => 'etablissement-liste',
                    'page' => $currentPage
                ));
            }
        }
        
        $tableEtablissements = $this->getServiceLocator()->get('Sbm\Db\Vue\Etablissements');
        return new ViewModel(array(
            'data' => $tableEtablissements->getRecord($etablissementId),
            'form' => $form,
            'page' => $currentPage,
            'etablissementId' => $etablissementId
        ));
    }

    /**
     * Ajout d'une nouvelle fiche d'etablissement
     * (la validation porte sur un champ csrf)
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function etablissementAjoutAction()
    {
        $currentPage = $this->params('page', 1);
        $etablissementId = null;
        $tableEtablissements = $this->getServiceLocator()->get('Sbm\Db\Table\Etablissements');
        $db = $this->getServiceLocator()->get('Sbm\Db\DbLib');
        
        $form = new FormEtablissement();
        $form->setValueOptions('jOuverture', $tableEtablissements->getSemaine());
        $form->setValueOptions('niveau', $tableEtablissements->getNiveau());
        $form->setValueOptions('rattacheA', $this->getServiceLocator()
            ->get('Sbm\Db\Select\EtablissementsVisibles'));
        $form->setValueOptions('communeId', $this->getServiceLocator()
            ->get('Sbm\Db\Select\CommunesDesservies'));
        $form->setMaxLength($db->getMaxLengthArray('etablissements', 'table'));
        
        $form->bind($tableEtablissements->getObjData());
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($request->getPost('cancel', false)) {
                $this->flashMessenger()->addWarningMessage("L'enregistrement n'a pas été modifié.");
                $this->redirect()->toRoute('sbmgestion/transport', array(
                    'action' => 'etablissement-liste',
                    'page' => $currentPage
                ));
            }
            $form->setData($request->getPost());
            if ($form->isValid()) { // controle le csrf
                $tableEtablissements->saveRecord($form->getData());
                $this->flashMessenger()->addSuccessMessage("Les modifications ont été enregistrées.");
                $this->redirect()->toRoute('sbmgestion/transport', array(
                    'action' => 'etablissement-liste',
                    'page' => $currentPage
                ));
            }
        }
        return new ViewModel(array(
            'form' => $form,
            'page' => $currentPage,
            'etablissementId' => $etablissementId
        ));
    }

    /**
     * renvoie la liste des élèves inscrits pour un etablissement donné
     *
     * @todo : à faire
     *      
     * @return \Zend\View\Model\ViewModel
     */
    public function etablissementGroupAction()
    {
        $currentPage = $this->params('page', 1);
        $etablissementId = $this->params('id', - 1); // GET
        $tableEtablissements = $this->getServiceLocator()->get('Sbm\Db\Vue\Etablissements');
        return new ViewModel(array(
            'data' => $tableEtablissements->getRecord($etablissementId),
            // 'paginator' => $table_eleves->paginator(),
            'page' => $currentPage,
            'etablissementId' => $etablissementId
        ));
    }

    /**
     * envoie un evenement contenant les paramètres de création d'un document pdf
     * (le listener DafapTcpdf\Listener\PdfListener lancera la création du pdf)
     * Il n'y a pas de vue associée à cette action puisque la response html est créée par \TCPDF
     */
    public function etablissementPdfAction()
    {
        $currentPage = $this->params('page', 1);
        
        $criteres_form = new CriteresForm('etablissements');
        $criteres_obj = new ObjectDataCriteres($criteres_form->getElementNames());
        $session = new SessionContainer(str_replace('pdf', 'liste', $this->getSessionNamespace()));
        if (isset($session->criteres)) {
            $criteres_obj->exchangeArray($session->criteres);
        }

        $call_pdf = $this->getServiceLocator()->get('RenderPdfService');
        $call_pdf->setParam('documentId', 3)
        ->setParam('recordSource', 'Sbm\Db\Vue\Etablissements')
        ->setParam('where', $criteres_obj->getWhere())
        ->setParam('orderBy', array('commune', 'nom'))
        ->renderPdf();
        
        $this->flashMessenger()->addSuccessMessage("Création d'un pdf.");
    }

    /**
     * =============================================== SERVICES ==================================================
     */
    
    /**
     * Liste des services
     * (avec pagination)
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function serviceListeAction()
    {
        $currentPage = $this->params('page', 1);
        $table_services = $this->getServiceLocator()->get('Sbm\Db\Vue\Services');
        
        $config = $this->getServiceLocator()->get('Config');
        $nb_service_pagination = $config['liste']['paginator']['nb_service_pagination'];
        
        $criteres_form = new CriteresForm('services');
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
            'paginator' => $table_services->paginator($criteres_obj->getWhere()),
            'page' => $currentPage,
            'nb_service_pagination' => $nb_service_pagination,
            'criteres_form' => $criteres_form
        ));
    }

    /**
     * Modification d'une fiche de service
     * (la validation porte sur un champ csrf)
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function serviceEditAction()
    {
        $currentPage = $this->params('page', 1);
        $serviceId = $this->params('id', - 1);
        if ($serviceId == - 1) {
            $this->redirect()->toRoute('sbmgestion/transport', array(
                'action' => 'service-liste',
                'page' => $currentPage
            ));
        }
        $tableServices = $this->getServiceLocator()->get('Sbm\Db\Table\Services');
        $db = $this->getServiceLocator()->get('Sbm\Db\DbLib');
        
        $form = new FormService();
        $form->modifFormForEdit();
        $form->setValueOptions('transporteurId', $this->getServiceLocator()
            ->get('Sbm\Db\Select\Transporteurs'));
        $form->setMaxLength($db->getMaxLengthArray('services', 'table'));
        
        $form->bind($tableServices->getObjData());
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($request->getPost('cancel', false)) {
                $this->flashMessenger()->addWarningMessage("L'enregistrement n'a pas été modifié.");
                $this->redirect()->toRoute('sbmgestion/transport', array(
                    'action' => 'service-liste',
                    'page' => $currentPage
                ));
            }
            $form->setData($request->getPost());
            if ($form->isValid()) { // controle le csrf
                $tableServices->saveRecord($form->getData());
                $this->flashMessenger()->addSuccessMessage("Les modifications ont été enregistrées.");
                $this->redirect()->toRoute('sbmgestion/transport', array(
                    'action' => 'service-liste',
                    'page' => $currentPage
                ));
            }
        } else {
            $form->setData($tableServices->getRecord($serviceId)
                ->getArrayCopy());
        }
        return new ViewModel(array(
            'form' => $form,
            'page' => $currentPage,
            'serviceId' => $serviceId
        ));
    }

    /**
     * Suppression d'une fiche avec confirmation
     *
     * @todo : Vérifier qu'il n'y a pas d'élève inscrit avant de supprimer la fiche
     *      
     * @return \Zend\View\Model\ViewModel
     */
    public function serviceSupprAction()
    {
        $currentPage = $this->params('page', 1);
        $serviceId = $this->params('id', - 1); // GET
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
            'id' => $serviceId
        ));
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($request->getPost('supproui', false)) { // confirmation
                $serviceId = $this->params()->fromPost('id', false); // POST
                if ($serviceId) {
                    $tableServices = $this->getServiceLocator()->get('Sbm\Db\Table\Services');
                    $tableServices->deleteRecord($serviceId);
                    $this->flashMessenger()->addSuccessMessage("L'enregistrement a été supprimé.");
                } else {
                    $this->flashMessenger()->addErrorMessage("Pas d'enregistrement à supprimer.");
                }
            } else { // abandon
                $this->flashMessenger()->addWarningMessage("L'enregistrement n'a pas été supprimé.");
            }
            $this->redirect()->toRoute('sbmgestion/transport', array(
                'action' => 'service-liste',
                'page' => $currentPage
            ));
        } else {
            if ($serviceId) {
                $form->setData(array(
                    'id' => $serviceId
                ));
            } else {
                $this->flashMessenger()->addErrorMessage("Pas d'enregistrement à supprimer.");
                $this->redirect()->toRoute('sbmgestion/transport', array(
                    'action' => 'service-liste',
                    'page' => $currentPage
                ));
            }
        }
        
        $tableServices = $this->getServiceLocator()->get('Sbm\Db\Vue\Services');
        return new ViewModel(array(
            'data' => $tableServices->getRecord($serviceId),
            'form' => $form,
            'page' => $currentPage,
            'serviceId' => $serviceId
        ));
    }

    /**
     * Ajout d'une nouvelle fiche de service
     * (la validation porte sur un champ csrf)
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function serviceAjoutAction()
    {
        $currentPage = $this->params('page', 1);
        $serviceId = null;
        $tableServices = $this->getServiceLocator()->get('Sbm\Db\Table\Services');
        $db = $this->getServiceLocator()->get('Sbm\Db\DbLib');
        
        $form = new FormService();
        $form->setValueOptions('transporteurId', $this->getServiceLocator()
            ->get('Sbm\Db\Select\Transporteurs'));
        $form->setMaxLength($db->getMaxLengthArray('services', 'table'));
        
        $form->bind($tableServices->getObjData());
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($request->getPost('cancel', false)) {
                $this->flashMessenger()->addWarningMessage("L'enregistrement n'a pas été modifié.");
                $this->redirect()->toRoute('sbmgestion/transport', array(
                    'action' => 'service-liste',
                    'page' => $currentPage
                ));
            }
            $form->setData($request->getPost());
            if ($form->isValid()) { // controle le csrf
                $tableServices->saveRecord($form->getData());
                $this->flashMessenger()->addSuccessMessage("Les modifications ont été enregistrées.");
                $this->redirect()->toRoute('sbmgestion/transport', array(
                    'action' => 'service-liste',
                    'page' => $currentPage
                ));
            }
        }
        return new ViewModel(array(
            'form' => $form,
            'page' => $currentPage,
            'serviceId' => $serviceId
        ));
    }

    /**
     * renvoie la liste des élèves inscrits pour un service donné
     *
     * @todo : à faire
     *      
     * @return \Zend\View\Model\ViewModel
     */
    public function serviceGroupAction()
    {
        $currentPage = $this->params('page', 1);
        $serviceId = $this->params('id', - 1); // GET
        $tableServices = $this->getServiceLocator()->get('Sbm\Db\Vue\Services');
        return new ViewModel(array(
            'data' => $tableServices->getRecord($serviceId),
            // 'paginator' => $table_eleves->paginator(),
            'page' => $currentPage,
            'serviceId' => $serviceId
        ));
    }

    /**
     * envoie un evenement contenant les paramètres de création d'un document pdf
     * (le listener DafapTcpdf\Listener\PdfListener lancera la création du pdf)
     * Il n'y a pas de vue associée à cette action puisque la response html est créée par \TCPDF
     */
    public function servicePdfAction()
    {
        $currentPage = $this->params('page', 1);
        
        $criteres_form = new CriteresForm('services');
        $criteres_obj = new ObjectDataCriteres($criteres_form->getElementNames());
        $session = new SessionContainer(str_replace('pdf', 'liste', $this->getSessionNamespace()));
        if (isset($session->criteres)) {
            $criteres_obj->exchangeArray($session->criteres);
        }
        
        $call_pdf = $this->getServiceLocator()->get('RenderPdfService');
        $call_pdf->setParam('documentId', 4)
        ->setParam('recordSource', 'Sbm\Db\Vue\Services')
        ->setParam('where', $criteres_obj->getWhere())
        ->setParam('orderBy', array('serviceId'))
        ->renderPdf();
        
        $this->flashMessenger()->addSuccessMessage("Création d'un pdf.");
    }

    /**
     * =============================================== STATIONS ==================================================
     */
    
    /**
     * Liste des stations
     * (avec pagination)
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function stationListeAction()
    {
        $currentPage = $this->params('page', 1);
        $table_stations = $this->getServiceLocator()->get('Sbm\Db\Vue\Stations');
        
        $config = $this->getServiceLocator()->get('Config');
        $nb_station_pagination = $config['liste']['paginator']['nb_station_pagination'];
        
        $criteres_form = new CriteresForm('stations');
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
            'paginator' => $table_stations->paginator($criteres_obj->getWhere()),
            'page' => $currentPage,
            'nb_station_pagination' => $nb_station_pagination,
            'criteres_form' => $criteres_form
        ));
    }

    /**
     * Modification d'une fiche de station
     * (la validation porte sur un champ csrf)
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function stationEditAction()
    {
        $currentPage = $this->params('page', 1);
        $stationId = $this->params('id', - 1);
        if ($stationId == - 1) {
            $this->redirect()->toRoute('sbmgestion/transport', array(
                'action' => 'station-liste',
                'page' => $currentPage
            ));
        }
        $tableStations = $this->getServiceLocator()->get('Sbm\Db\Table\Stations');
        $db = $this->getServiceLocator()->get('Sbm\Db\DbLib');
        
        $form = new FormStation();
        $form->setValueOptions('communeId', $this->getServiceLocator()
            ->get('Sbm\Db\Select\CommunesDesservies'));
        $form->setMaxLength($db->getMaxLengthArray('stations', 'table'));
        
        $form->bind($tableStations->getObjData());
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($request->getPost('cancel', false)) {
                $this->flashMessenger()->addWarningMessage("L'enregistrement n'a pas été modifié.");
                $this->redirect()->toRoute('sbmgestion/transport', array(
                    'action' => 'station-liste',
                    'page' => $currentPage
                ));
            }
            $form->setData($request->getPost());
            if ($form->isValid()) { // controle le csrf
                $tableStations->saveRecord($form->getData());
                $this->flashMessenger()->addSuccessMessage("Les modifications ont été enregistrées.");
                $this->redirect()->toRoute('sbmgestion/transport', array(
                    'action' => 'station-liste',
                    'page' => $currentPage
                ));
            }
        } else {
            $form->setData($tableStations->getRecord($stationId)
                ->getArrayCopy());
        }
        return new ViewModel(array(
            'form' => $form,
            'page' => $currentPage,
            'stationId' => $stationId
        ));
    }

    /**
     * Suppression d'une fiche avec confirmation
     *
     * @todo : Vérifier qu'il n'y a pas d'élève inscrit avant de supprimer la fiche
     *      
     * @return \Zend\View\Model\ViewModel
     */
    public function stationSupprAction()
    {
        $currentPage = $this->params('page', 1);
        $stationId = $this->params('id', - 1); // GET
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
            'id' => $stationId
        ));
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($request->getPost('supproui', false)) { // confirmation
                $stationId = $this->params()->fromPost('id', false); // POST
                if ($stationId) {
                    $tableStations = $this->getServiceLocator()->get('Sbm\Db\Table\Stations');
                    $tableStations->deleteRecord($stationId);
                    $this->flashMessenger()->addSuccessMessage("L'enregistrement a été supprimé.");
                } else {
                    $this->flashMessenger()->addErrorMessage("Pas d'enregistrement à supprimer.");
                }
            } else { // abandon
                $this->flashMessenger()->addWarningMessage("L'enregistrement n'a pas été supprimé.");
            }
            $this->redirect()->toRoute('sbmgestion/transport', array(
                'action' => 'station-liste',
                'page' => $currentPage
            ));
        } else {
            if ($stationId) {
                $form->setData(array(
                    'id' => $stationId
                ));
            } else {
                $this->flashMessenger()->addErrorMessage("Pas d'enregistrement à supprimer.");
                $this->redirect()->toRoute('sbmgestion/transport', array(
                    'action' => 'station-liste',
                    'page' => $currentPage
                ));
            }
        }
        
        $tableStations = $this->getServiceLocator()->get('Sbm\Db\Vue\Stations');
        return new ViewModel(array(
            'data' => $tableStations->getRecord($stationId),
            'form' => $form,
            'page' => $currentPage,
            'stationId' => $stationId
        ));
    }

    /**
     * Ajout d'une nouvelle fiche de station
     * (la validation porte sur un champ csrf)
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function stationAjoutAction()
    {
        $currentPage = $this->params('page', 1);
        $stationId = null;
        $tableStations = $this->getServiceLocator()->get('Sbm\Db\Table\Stations');
        $db = $this->getServiceLocator()->get('Sbm\Db\DbLib');
        
        $form = new FormStation();
        $form->setValueOptions('communeId', $this->getServiceLocator()
            ->get('Sbm\Db\Select\CommunesDesservies'));
        $form->setMaxLength($db->getMaxLengthArray('stations', 'table'));
        
        $form->bind($tableStations->getObjData());
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($request->getPost('cancel', false)) {
                $this->flashMessenger()->addWarningMessage("L'enregistrement n'a pas été modifié.");
                $this->redirect()->toRoute('sbmgestion/transport', array(
                    'action' => 'station-liste',
                    'page' => $currentPage
                ));
            }
            $form->setData($request->getPost());
            if ($form->isValid()) { // controle le csrf
                $tableStations->saveRecord($form->getData());
                $this->flashMessenger()->addSuccessMessage("Les modifications ont été enregistrées.");
                $this->redirect()->toRoute('sbmgestion/transport', array(
                    'action' => 'station-liste',
                    'page' => $currentPage
                ));
            }
        }
        return new ViewModel(array(
            'form' => $form,
            'page' => $currentPage,
            'stationId' => $stationId
        ));
    }

    /**
     * renvoie la liste des élèves inscrits pour une station donnée
     *
     * @todo : à faire
     *      
     * @return \Zend\View\Model\ViewModel
     */
    public function stationGroupAction()
    {
        $currentPage = $this->params('page', 1);
        $stationId = $this->params('id', - 1); // GET
        $tableStations = $this->getServiceLocator()->get('Sbm\Db\Vue\Stations');
        return new ViewModel(array(
            'data' => $tableStations->getRecord($stationId),
            // 'paginator' => $table_eleves->paginator(),
            'page' => $currentPage,
            'stationId' => $stationId
        ));
    }

    /**
     * envoie un evenement contenant les paramètres de création d'un document pdf
     * (le listener DafapTcpdf\Listener\PdfListener lancera la création du pdf)
     * Il n'y a pas de vue associée à cette action puisque la response html est créée par \TCPDF
     */
    public function stationPdfAction()
    {
        $currentPage = $this->params('page', 1);
        
        $criteres_form = new CriteresForm('stations');
        $criteres_obj = new ObjectDataCriteres($criteres_form->getElementNames());
        $session = new SessionContainer(str_replace('pdf', 'liste', $this->getSessionNamespace()));
        if (isset($session->criteres)) {
            $criteres_obj->exchangeArray($session->criteres);
        }
        
        $call_pdf = $this->getServiceLocator()->get('RenderPdfService');
        $call_pdf->setParam('documentId', 5)
        ->setParam('recordSource', 'Sbm\Db\Vue\Stations')
        ->setParam('where', $criteres_obj->getWhere())
        ->setParam('orderBy', array('commune', 'nom'))
        ->renderPdf();
        
        $this->flashMessenger()->addSuccessMessage("Création d'un pdf.");
    }

    /**
     * =============================================== TRANSPORTEURS ==================================================
     */
    
    /**
     * Liste des transporteurs
     * (avec pagination)
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function transporteurListeAction()
    {
        $currentPage = $this->params('page', 1);
        $table_transporteurs = $this->getServiceLocator()->get('Sbm\Db\Vue\Transporteurs');
        
        $config = $this->getServiceLocator()->get('Config');
        $nb_transporteur_pagination = $config['liste']['paginator']['nb_transporteur_pagination'];
        
        $criteres_form = new CriteresForm('transporteurs');
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
            'paginator' => $table_transporteurs->paginator($criteres_obj->getWhere()),
            'page' => $currentPage,
            'nb_transporteur_pagination' => $nb_transporteur_pagination,
            'criteres_form' => $criteres_form
        ));
    }

    /**
     * Modification d'une fiche de transporteur
     * (la validation porte sur un champ csrf)
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function transporteurEditAction()
    {
        $currentPage = $this->params('page', 1);
        $transporteurId = $this->params('id', - 1);
        if ($transporteurId == - 1) {
            $this->redirect()->toRoute('sbmgestion/transport', array(
                'action' => 'transporteur-liste',
                'page' => $currentPage
            ));
        }
        $tableTransporteurs = $this->getServiceLocator()->get('Sbm\Db\Table\Transporteurs');
        $db = $this->getServiceLocator()->get('Sbm\Db\DbLib');
        
        $form = new FormTransporteur();
        $form->setValueOptions('communeId', $this->getServiceLocator()
            ->get('Sbm\Db\Select\CommunesDesservies'));
        $form->setMaxLength($db->getMaxLengthArray('transporteurs', 'table'));
        
        $form->bind($tableTransporteurs->getObjData());
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($request->getPost('cancel', false)) {
                $this->flashMessenger()->addWarningMessage("L'enregistrement n'a pas été modifié.");
                $this->redirect()->toRoute('sbmgestion/transport', array(
                    'action' => 'transporteur-liste',
                    'page' => $currentPage
                ));
            }
            $form->setData($request->getPost());
            if ($form->isValid()) { // controle le csrf
                $tableTransporteurs->saveRecord($form->getData());
                $this->flashMessenger()->addSuccessMessage("Les modifications ont été enregistrées.");
                $this->redirect()->toRoute('sbmgestion/transport', array(
                    'action' => 'transporteur-liste',
                    'page' => $currentPage
                ));
            }
        } else {
            $form->setData($tableTransporteurs->getRecord($transporteurId)
                ->getArrayCopy());
        }
        return new ViewModel(array(
            'form' => $form,
            'page' => $currentPage,
            'transporteurId' => $transporteurId
        ));
    }

    /**
     * Suppression d'une fiche avec confirmation
     *
     * @todo : Vérifier qu'il n'y a pas de service attribué avant de supprimer la fiche
     *      
     * @return \Zend\View\Model\ViewModel
     */
    public function transporteurSupprAction()
    {
        $currentPage = $this->params('page', 1);
        $transporteurId = $this->params('id', - 1); // GET
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
            'id' => $transporteurId
        ));
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($request->getPost('supproui', false)) { // confirmation
                $transporteurId = $this->params()->fromPost('id', false); // POST
                if ($transporteurId) {
                    $tableTransporteurs = $this->getServiceLocator()->get('Sbm\Db\Table\Transporteurs');
                    $tableTransporteurs->deleteRecord($transporteurId);
                    $this->flashMessenger()->addSuccessMessage("L'enregistrement a été supprimé.");
                } else {
                    $this->flashMessenger()->addErrorMessage("Pas d'enregistrement à supprimer.");
                }
            } else { // abandon
                $this->flashMessenger()->addWarningMessage("L'enregistrement n'a pas été supprimé.");
            }
            $this->redirect()->toRoute('sbmgestion/transport', array(
                'action' => 'transporteur-liste',
                'page' => $currentPage
            ));
        } else {
            if ($transporteurId) {
                $form->setData(array(
                    'id' => $transporteurId
                ));
            } else {
                $this->flashMessenger()->addErrorMessage("Pas d'enregistrement à supprimer.");
                $this->redirect()->toRoute('sbmgestion/transport', array(
                    'action' => 'transporteur-liste',
                    'page' => $currentPage
                ));
            }
        }
        
        $tableTransporteurs = $this->getServiceLocator()->get('Sbm\Db\Vue\Transporteurs');
        return new ViewModel(array(
            'data' => $tableTransporteurs->getRecord($transporteurId),
            'form' => $form,
            'page' => $currentPage,
            'transporteurId' => $transporteurId
        ));
    }

    /**
     * Ajout d'une nouvelle fiche de transporteur
     * (la validation porte sur un champ csrf)
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function transporteurAjoutAction()
    {
        $currentPage = $this->params('page', 1);
        $transporteurId = null;
        $tableTransporteurs = $this->getServiceLocator()->get('Sbm\Db\Table\Transporteurs');
        $db = $this->getServiceLocator()->get('Sbm\Db\DbLib');
        
        $form = new FormTransporteur();
        $form->setValueOptions('communeId', $this->getServiceLocator()
            ->get('Sbm\Db\Select\CommunesDesservies'));
        $form->setMaxLength($db->getMaxLengthArray('transporteurs', 'table'));
        
        $form->bind($tableTransporteurs->getObjData());
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($request->getPost('cancel', false)) {
                $this->flashMessenger()->addWarningMessage("L'enregistrement n'a pas été modifié.");
                $this->redirect()->toRoute('sbmgestion/transport', array(
                    'action' => 'transporteur-liste',
                    'page' => $currentPage
                ));
            }
            $form->setData($request->getPost());
            if ($form->isValid()) { // controle le csrf
                $tableTransporteurs->saveRecord($form->getData());
                $this->flashMessenger()->addSuccessMessage("Les modifications ont été enregistrées.");
                $this->redirect()->toRoute('sbmgestion/transport', array(
                    'action' => 'transporteur-liste',
                    'page' => $currentPage
                ));
            }
        }
        return new ViewModel(array(
            'form' => $form,
            'page' => $currentPage,
            'transporteurId' => $transporteurId
        ));
    }

    /**
     * renvoie la liste des services pour un transporteur donné
     *
     * @todo : à faire
     *      
     * @return \Zend\View\Model\ViewModel
     */
    public function transporteurGroupAction()
    {
        $currentPage = $this->params('page', 1);
        $transporteurId = $this->params('id', - 1); // GET
        $tableTransporteurs = $this->getServiceLocator()->get('Sbm\Db\Vue\Transporteurs');
        return new ViewModel(array(
            'data' => $tableTransporteurs->getRecord($transporteurId),
            // 'paginator' => $table_eleves->paginator(),
            'page' => $currentPage,
            'transporteurId' => $transporteurId
        ));
    }

    /**
     * envoie un evenement contenant les paramètres de création d'un document pdf
     * (le listener DafapTcpdf\Listener\PdfListener lancera la création du pdf)
     * Il n'y a pas de vue associée à cette action puisque la response html est créée par \TCPDF
     */
    public function transporteurPdfAction()
    {
        $currentPage = $this->params('page', 1);
        
        $criteres_form = new CriteresForm('transporteurs');
        $criteres_obj = new ObjectDataCriteres($criteres_form->getElementNames());
        $session = new SessionContainer(str_replace('pdf', 'liste', $this->getSessionNamespace()));
        if (isset($session->criteres)) {
            $criteres_obj->exchangeArray($session->criteres);
        }
        
        $call_pdf = $this->getServiceLocator()->get('RenderPdfService');
        $call_pdf->setParam('documentId', 7)
        ->setParam('recordSource', 'Sbm\Db\Vue\Transporteurs')
        ->setParam('where', $criteres_obj->getWhere())
        ->setParam('orderBy', array('nom'))
        ->renderPdf();
        
        $this->flashMessenger()->addSuccessMessage("Création d'un pdf.");
    }
}