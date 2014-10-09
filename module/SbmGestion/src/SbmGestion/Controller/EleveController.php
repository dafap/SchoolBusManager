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
use SbmCommun\Model\Db\DbLib;
use SbmCommun\Form\CriteresForm;
use SbmCommun\Model\Db\ObjectData\Criteres as ObjectDataCriteres;
use SbmCommun\Form\ButtonForm;
use SbmCommun\Form\Eleve as FormEleve;
use SbmCommun\Form\Responsable as FormResponsable;

class EleveController extends AbstractActionController
{

    public function indexAction()
    {
        return new ViewModel();
    }

    public function eleveListeAction()
    {
        $currentPage = $this->params('page', 1);
        $table_eleves = $this->getServiceLocator()->get('Sbm\Db\Vue\Eleves');
        
        $config = $this->getServiceLocator()->get('Config');
        $nb_eleve_pagination = $config['liste']['paginator']['nb_eleve_pagination'];
        
        $criteres_form = new CriteresForm('eleves');
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
            'paginator' => $table_eleves->paginator($criteres_obj->getWhere()),
            'page' => $currentPage,
            'nb_eleve_pagination' => $nb_eleve_pagination,
            'criteres_form' => $criteres_form
        ));
    }

    public function eleveAjoutAction()
    {
        $currentPage = $this->params('page', 1);
        $eleveId = null;
        $tableEleves = $this->getServiceLocator()->get('Sbm\Db\Table\Eleves');
        $db = $this->getServiceLocator()->get('Sbm\Db\DbLib');
        
        $form = new FormEleve();
        $form->setValueOptions('etablissementId', $this->getServiceLocator()
            ->get('Sbm\Db\Select\EtablissementsVisibles'));
        // $form->setValueOptions('classeId', $this->getServiceLocator()->get('Sbm\Db\Select\Classes'));
        $form->setValueOptions('communeId1', $this->getServiceLocator()
            ->get('Sbm\Db\Select\CommunesDesservies'));
        $form->setMaxLength($db->getMaxLengthArray('eleves', 'table'));
        
        $form->bind($tableEleves->getObjData());
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($request->getPost('cancel', false)) {
                $this->flashMessenger()->addWarningMessage("L'enregistrement n'a pas été modifié.");
                $this->redirect()->toRoute('sbmgestion/eleve', array(
                    'action' => 'eleve-liste',
                    'page' => $currentPage
                ));
            }
            $form->setData($request->getPost());
            if ($form->isValid()) { // controle le csrf
                $tableEleves->saveRecord($form->getData());
                $this->flashMessenger()->addSuccessMessage("Les modifications ont été enregistrées.");
                $this->redirect()->toRoute('sbmgestion/eleve', array(
                    'action' => 'eleve-liste',
                    'page' => $currentPage
                ));
            }
        }
        return new ViewModel(array(
            'form' => $form,
            'page' => $currentPage,
            'eleveId' => $eleveId
        ));
    }

    public function eleveEditAction()
    {
        $currentPage = $this->params('page', 1);
        $eleveId = $this->params('id', - 1);
        if ($eleveId == - 1) {
            $this->redirect()->toRoute('sbmgestion/eleve', array(
                'action' => 'eleve-liste',
                'page' => $currentPage
            ));
        }
        $tableEleves = $this->getServiceLocator()->get('Sbm\Db\Table\Eleves');
        $db = $this->getServiceLocator()->get('Sbm\Db\DbLib');
        
        $form = new FormEleve();
        // $form->modifFormForEdit();
        $form->setMaxLength($db->getMaxLengthArray('eleves', 'table'));
        $form->bind($tableEleves->getObjData());
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($request->getPost('cancel', false)) {
                $this->flashMessenger()->addWarningMessage("L'enregistrement n'a pas été modifié.");
                $this->redirect()->toRoute('sbmgestion/eleve', array(
                    'action' => 'eleve-liste',
                    'page' => $currentPage
                ));
            }
            $form->setData($request->getPost());
            if ($form->isValid()) { // controle le csrf
                $tableEleves->saveRecord($form->getData());
                $this->flashMessenger()->addSuccessMessage("Les modifications ont été enregistrées.");
                $this->redirect()->toRoute('sbmgestion/eleve', array(
                    'action' => 'eleve-liste',
                    'page' => $currentPage
                ));
            }
        } else {
            $form->setData($tableEleves->getRecord($eleveId)
                ->getArrayCopy());
        }
        return new ViewModel(array(
            'form' => $form,
            'page' => $currentPage,
            'eleveId' => $eleveId
        ));
    }

    public function eleveGroupAction()
    {
        $currentPage = $this->params('page', 1);
        $responsableId = $this->params('id', - 1); // GET
        $tableResponsables = $this->getServiceLocator()->get('Sbm\Db\Vue\Responsables');
        return new ViewModel(array(
            'datagroup' => $tableResponsables->getRecord($responsableId),
            // 'paginator' => $table_eleves->paginator(),
            'page' => $currentPage,
            'responsableId' => $responsableId
        ));
    }

    /**
     * envoie un evenement contenant les paramètres de création d'un document pdf
     * (le listener DafapTcpdf\Listener\PdfListener lancera la création du pdf)
     * Il n'y a pas de vue associée à cette action puisque la response html est créée par \TCPDF
     */
    public function elevePdfAction()
    {
        $currentPage = $this->params('page', 1);
        
        $criteres_form = new CriteresForm('eleves');
        $criteres_obj = new ObjectDataCriteres($criteres_form->getElementNames());
        $session = new SessionContainer(str_replace('pdf', 'liste', $this->getSessionNamespace()));
        if (isset($session->criteres)) {
            $criteres_obj->exchangeArray($session->criteres);
        }
        
        $call_pdf = $this->getServiceLocator()->get('RenderPdfService');
        $call_pdf->setData(array(
            'sm' => $this->getServiceLocator(),
            'table' => 'Sbm\Db\Vue\Eleves',
            'fields' => array(
                'nom',
                'prenom',
                'dateN',
                'commune1',
                'station1',
                'service1',
                'tarifMontant',
                array(
                    'name' => 'secondeAdresse',
                    'type' => 'boolean',
                    'values' => array(
                        false => '',
                        true => 'G.A.'
                    )
                )
            ),
            'where' => $criteres_obj->getWhere(),
            'orderBy' => array(
                'nomSA',
                'prenomSA'
            )
        ))
            ->setHead(array(
            'Nom',
            'Prénom',
            'Date n.',
            'Commune 1',
            'Station 1',
            'Service 1',
            'Tarif',
            'G.A.'
        ))
            ->setPdfConfig(array(
            'title' => 'Liste des élèves',
            'header' => array(
                'title' => 'Liste des élèves',
                'string' => 'éditée par School Bus Manager le ' . date('d/m/Y à H:i')
            )
        ))
            ->setTableConfig(array(
            'thead' => array(
                'cell' => array(
                    'stretch' => 1
                )
            ),
            'tbody' => array(
                'cell' => array(
                    'txt_precision' => array(
                        - 1,
                        - 1,
                        0,
                        - 1,
                        0,
                        0
                    ),
                    'stretch' => 1
                )
            ),
            'column_widths' => array(
                25,
                15,
                20,
                55,
                35,
                15,
                10,
                9
            )
        ))
            ->renderPdf();
        
        $this->flashMessenger()->addSuccessMessage("Création d'un pdf.");
    }

    public function eleveSupprAction()
    {
        $currentPage = $this->params('page', 1);
        $eleveId = $this->params('id', - 1); // GET
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
            'id' => $eleveId
        ));
        $tableEleves = $this->getServiceLocator()->get('Sbm\Db\Vue\Eleves');
        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($request->getPost('supproui', false)) { // confirmation
                $eleveId = $this->params()->fromPost('id', false); // POST
                if ($eleveId) {
                    $tableEleves = $this->getServiceLocator()->get('Sbm\Db\Table\Eleves');
                    $tableEleves->deleteRecord($eleveId);
                    $this->flashMessenger()->addSuccessMessage("L'enregistrement a été supprimé.");
                } else {
                    $this->flashMessenger()->addErrorMessage("Pas d'enregistrement à supprimer.");
                }
            } else { // abandon
                $this->flashMessenger()->addWarningMessage("L'enregistrement n'a pas été supprimé.");
            }
            $this->redirect()->toRoute('sbmgestion/eleve', array(
                'action' => 'eleve-liste',
                'page' => $currentPage
            ));
        } else {
            if ($eleveId) {
                $form->setData(array(
                    'id' => $eleveId
                ));
            } else {
                $this->flashMessenger()->addErrorMessage("Pas d'enregistrement à supprimer.");
                $this->redirect()->toRoute('sbmgestion/eleve', array(
                    'action' => 'eleve-liste',
                    'page' => $currentPage
                ));
            }
        }
        
        return new ViewModel(array(
            'data' => $tableEleves->getRecord($eleveId),
            'form' => $form,
            'page' => $currentPage,
            'eleveId' => $eleveId
        ));
    }

    public function responsableListeAction()
    {
        $currentPage = $this->params('page', 1);
        $table_responsables = $this->getServiceLocator()->get('Sbm\Db\Vue\Responsables');
        
        $config = $this->getServiceLocator()->get('Config');
        $nb_responsable_pagination = $config['liste']['paginator']['nb_responsable_pagination'];
        
        $criteres_form = new CriteresForm('responsables');
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
            'paginator' => $table_responsables->paginator($criteres_obj->getWhere()),
            'page' => $currentPage,
            'nb_responsable_pagination' => $nb_responsable_pagination,
            'criteres_form' => $criteres_form
        ));
    }

    public function responsableAjoutAction()
    {
        $currentPage = $this->params('page', 1);
        $responsableId = null;
        $tableResponsables = $this->getServiceLocator()->get('Sbm\Db\Table\Responsables');
        $db = $this->getServiceLocator()->get('Sbm\Db\DbLib');
        
        $form = new FormResponsable();
        $form->setValueOptions('communeId', $this->getServiceLocator()
            ->get('Sbm\Db\Select\CommunesDesservies'));
        $form->setValueOptions('ancienCommuneId', $this->getServiceLocator()
            ->get('Sbm\Db\Select\CommunesDesservies'));
        $form->setMaxLength($db->getMaxLengthArray('responsables', 'table'));
        
        $form->bind($tableResponsables->getObjData());
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($request->getPost('cancel', false)) {
                $this->flashMessenger()->addWarningMessage("L'enregistrement n'a pas été modifié.");
                $this->redirect()->toRoute('sbmgestion/eleve', array(
                    'action' => 'responsable-liste',
                    'page' => $currentPage
                ));
            }
            $form->setData($request->getPost());
            if ($form->isValid()) { // controle le csrf
                $tableResponsables->saveRecord($form->getData());
                $this->flashMessenger()->addSuccessMessage("Les modifications ont été enregistrées.");
                $this->redirect()->toRoute('sbmgestion/eleve', array(
                    'action' => 'responsable-liste',
                    'page' => $currentPage
                ));
            }
        }
        return new ViewModel(array(
            'form' => $form,
            'page' => $currentPage,
            'responsableId' => $responsableId,
            'demenagement' => false
        ));
    }

    public function responsableEditAction()
    {
        $currentPage = $this->params('page', 1);
        $responsableId = $this->params('id', - 1);
        if ($responsableId == - 1) {
            $this->redirect()->toRoute('sbmgestion/eleve', array(
                'action' => 'responsable-liste',
                'page' => $currentPage
            ));
        }
        $tableResponsables = $this->getServiceLocator()->get('Sbm\Db\Table\Responsables');
        $db = $this->getServiceLocator()->get('Sbm\Db\DbLib');
        
        $form = new FormResponsable();
        $form->setValueOptions('communeId', $this->getServiceLocator()
            ->get('Sbm\Db\Select\CommunesDesservies'));
        $form->setValueOptions('ancienCommuneId', $this->getServiceLocator()
            ->get('Sbm\Db\Select\CommunesDesservies'));
        $form->setMaxLength($db->getMaxLengthArray('responsables', 'table'));
        
        $form->bind($tableResponsables->getObjData());
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($request->getPost('cancel', false)) {
                $this->flashMessenger()->addWarningMessage("L'enregistrement n'a pas été modifié.");
                $this->redirect()->toRoute('sbmgestion/eleve', array(
                    'action' => 'responsable-liste',
                    'page' => $currentPage
                ));
            }
            $form->setData($request->getPost());
            if ($form->isValid()) { // controle le csrf
                $tableResponsables->saveRecord($form->getData());
                $this->flashMessenger()->addSuccessMessage("Les modifications ont été enregistrées.");
                $this->redirect()->toRoute('sbmgestion/eleve', array(
                    'action' => 'responsable-liste',
                    'page' => $currentPage
                ));
            }
            $demenagement = $request->getPost('demenagement', false);
        } else {
            $array_data = $tableResponsables->getRecord($responsableId)->getArrayCopy();
            $form->setData($array_data);
            $demenagement = $array_data['demenagement'];
        }
        return new ViewModel(array(
            'form' => $form,
            'page' => $currentPage,
            'responsableId' => $responsableId,
            'demenagement' => $demenagement
        ));
    }

    public function responsableGroupAction()
    {
        $currentPage = $this->params('page', 1);
        $responsableId = $this->params('id', - 1); // GET
        $tableResponsables = $this->getServiceLocator()->get('Sbm\Db\Vue\Responsables');
        $tableEleves = $this->getServiceLocator()->get('Sbm\Db\Table\Eleves');
        $data = array();
        $data['resp1'] = $tableEleves->duResponsable1($responsableId);
        $data['resp2'] = $tableEleves->duResponsable2($responsableId);
        $data['fact'] = $tableEleves->duResponsableFinancier($responsableId);
        return new ViewModel(array(
            'datagroup' => $tableResponsables->getRecord($responsableId),
            'data' => $data,
            'page' => $currentPage,
            'responsableId' => $responsableId
        ));
    }

    public function responsablePdfAction()
    {
        $currentPage = $this->params('page', 1);
        
        $criteres_form = new CriteresForm('responsables');
        $criteres_obj = new ObjectDataCriteres($criteres_form->getElementNames());
        $session = new SessionContainer(str_replace('pdf', 'liste', $this->getSessionNamespace()));
        if (isset($session->criteres)) {
            $criteres_obj->exchangeArray($session->criteres);
        }
        
        $call_pdf = $this->getServiceLocator()->get('RenderPdfService');
        $call_pdf->setData(array(
            // 'sm' => $this->getServiceLocator(),
            'table' => 'Sbm\Db\Vue\Responsables',
            'fields' => array(
                'nom',
                'prenom',
                'adressL1',
                'adressL2',
                'codePostal',
                'commune',
                'email',
                'telephone',
                'telephoneC',
                'nbEleves',
                array(
                    'name' => 'demenagement',
                    'type' => 'boolean',
                    'values' => array(
                        false => '',
                        true => 'D'
                    )
                )
            ),
            'where' => $criteres_obj->getWhere(), // objet Zend\Db\Sql\Where
            'orderBy' => array(
                'nomSA',
                'prenomSA'
            )
        ))
            ->setHead(array(
            'Nom',
            'Prénom',
            'Adresse',
            'Adresse',
            'Code postal',
            'Commune',
            'Email',
            'Telephone',
            'Portable',
            'Nb élèves',
            'Dém.'
        ))
            ->setPdfConfig(array(
            'title' => 'Liste des responsables',
            'header' => array(
                'title' => 'Liste des responsables',
                'string' => 'éditée par School Bus Manager le ' . date('d/m/Y à H:i')
            )
        ))
            ->setTableConfig(array(
            'thead' => array(
                'cell' => array(
                    'stretch' => 1
                ),
                'font' => array('family' => 'helvetica')
            ),
            'tbody' => array(
                'cell' => array(
                    'txt_precision' => array(
                        - 1,
                        - 1,
                        - 1,
                        - 1,
                        - 1,
                        - 1,
                        - 1,
                        - 1,
                        - 1,
                        0,
                        - 1
                    ),
                    'stretch' => 1
                ),
                'font' => array('family' => 'times')
            ),
            /*'column_widths' => '='/*array(
                25,
                15,
                25,
                15,
                10,
                25,
                25,
                10,
                10,
                8,
                5
            )*/
        ))
            ->renderPdf();
        
        $this->flashMessenger()->addSuccessMessage("Création d'un pdf.");
    }

    public function responsableSupprAction()
    {
        $currentPage = $this->params('page', 1);
        $responsableId = $this->params('id', - 1); // GET
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
            'id' => $responsableId
        ));
        $tableResponsables = $this->getServiceLocator()->get('Sbm\Db\Vue\Responsables');
        $tableEleves = $this->getServiceLocator()->get('Sbm\Db\Table\Eleves'); // table en relation avec responsables
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($request->getPost('supproui', false)) { // confirmation
                $responsableId = $this->params()->fromPost('id', false); // POST
                if ($responsableId) {
                    // on supprime les eleves en relation avec ce responsable
                    foreach ($tableEleves->duResponsable($responsableId) as $eleve) {
                        $tableEleves->deleteRecord($eleve->eleveId);
                    }
                    // on supprime le responsable
                    $tableResponsables = $this->getServiceLocator()->get('Sbm\Db\Table\Responsables');
                    $tableResponsables->deleteRecord($responsableId);
                    $this->flashMessenger()->addSuccessMessage("L'enregistrement a été supprimé.");
                } else {
                    $this->flashMessenger()->addErrorMessage("Pas d'enregistrement à supprimer.");
                }
            } else { // abandon
                $this->flashMessenger()->addWarningMessage("L'enregistrement n'a pas été supprimé.");
            }
            $this->redirect()->toRoute('sbmgestion/eleve', array(
                'action' => 'responsable-liste',
                'page' => $currentPage
            ));
        } else {
            if ($responsableId) {
                $form->setData(array(
                    'id' => $responsableId
                ));
            } else {
                $this->flashMessenger()->addErrorMessage("Pas d'enregistrement à supprimer.");
                $this->redirect()->toRoute('sbmgestion/eleve', array(
                    'action' => 'responsable-liste',
                    'page' => $currentPage
                ));
            }
        }
        
        return new ViewModel(array(
            'data' => $tableResponsables->getRecord($responsableId),
            'data_dependantes' => $tableEleves->duResponsable($responsableId),
            'form' => $form,
            'page' => $currentPage,
            'responsableId' => $responsableId
        ));
    }
}