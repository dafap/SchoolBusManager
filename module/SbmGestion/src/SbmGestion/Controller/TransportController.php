<?php
/**
 * Controller principal du module SbmGestion
 * Gestion des données du réseau de transport
 *
 * @project sbm
 * @package module/SbmGestion/src/SbmGestion/Controller
 * @filesource TransportController.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 15 juin 2017
 * @version 2017-2.3.3
 */
namespace SbmGestion\Controller;

use Zend\View\Model\ViewModel;
use Zend\Session\Container as SessionContainer;
use Zend\Http\PhpEnvironment\Response;
use Zend\Db\Sql\Where;
use SbmBase\Model\Session;
use SbmBase\Model\StdLib;
use SbmCommun\Model\Mvc\Controller\AbstractActionController;
use SbmCommun\Form\ButtonForm;
use SbmCommun\Form\LatLng;
use SbmCommun\Form\Circuit as FormCircuit;
use SbmCommun\Form\Classe as FormClasse;
use SbmCommun\Form\Commune as FormCommune;
use SbmCommun\Form\Etablissement as FormEtablissement;
use SbmCommun\Form\EtablissementService as FormEtablissementService;
use SbmCommun\Form\Service as FormService;
use SbmCommun\Form\Station as FormStation;
use SbmCommun\Form\Transporteur as FormTransporteur;
use SbmCommun\Form\CriteresForm;
use SbmCommun\Model\Db\ObjectData\Criteres as ObjectDataCriteres;
use SbmCommun\Model\Strategy\Niveau;
use SbmCommun\Model\Strategy\Semaine;
use SbmCommun\Model\Mvc\Controller\EditResponse;
use SbmCartographie\Model\Point;
use SbmGestion\Form\EtablissementServiceSuppr as FormEtablissementServiceSuppr;
use SbmGestion\Form\SbmGestion\Form;
use SbmGestion\Model\Db\Filtre\Eleve\Filtre as FiltreEleve;

class TransportController extends AbstractActionController
{

    public function indexAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        }
        $this->redirectToOrigin()->reset(); // on s'assure que la pile des retours est vide
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
        $args = $this->initListe('circuits', function ($config, $form) {
            $form->setValueOptions('stationId', $config['db_manager']->get('Sbm\Db\Select\Stations')
                ->ouvertes());
            $form->setValueOptions('serviceId', $config['db_manager']->get('Sbm\Db\Select\Services'));
        }, array(
            'serviceId',
            'stationId'
        ));
        if ($args instanceof Response)
            return $args;
        
        $args['where']->equalTo('millesime', $this->getFromSession('millesime'));
        $auth = $this->authenticate->by('email');
        return new ViewModel(array(
            'paginator' => $this->db_manager->get('Sbm\Db\Vue\Circuits')->paginator($args['where']),
            't_nb_inscrits' => $this->db_manager->get('Sbm\Db\Eleve\Effectif')->byCircuit(),
            'page' => $this->params('page', 1),
            'count_per_page' => $this->getPaginatorCountPerPage('nb_circuits', 10),
            'criteres_form' => $args['form'],
            'admin' => $auth->getCategorieId() > 253
        ));
    }

    /**
     * Modification d'une fiche de circuit
     * (avec validation des données du formulaire)
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function circuitEditAction()
    {
        $currentPage = $this->params('page', 1);
        $form = new FormCircuit();
        $form->setValueOptions('serviceId', $this->db_manager->get('Sbm\Db\Select\Services'))
            ->setValueOptions('stationId', $this->db_manager->get('Sbm\Db\Select\Stations')
            ->ouvertes())
            ->setValueOptions('semaine', Semaine::getJours());
        $params = array(
            'data' => array(
                'table' => 'circuits',
                'type' => 'table',
                'alias' => 'Sbm\Db\Table\Circuits',
                'id' => 'circuitId'
            ),
            'form' => $form
        );
        
        try {
            $r = $this->editData($this->db_manager, $params);
        } catch (\Zend\Db\Adapter\Exception\InvalidQueryException $e) {
            if (stripos($e->getMessage(), '23000 - 1062 - Duplicate entry') !== false) {
                $this->flashMessenger()->addWarningMessage('Impossible ! Cet arrêt est déjà sur ce circuit.');
                $r = new EditResponse('warning', array());
                ;
            } else {
                throw new \Zend\Db\Adapter\Exception\InvalidQueryException($e->getMessage(), $e->getCode(), $e->getPrevious());
            }
        }
        if ($r instanceof Response) {
            return $r;
        } else {
            switch ($r->getStatus()) {
                case 'error':
                case 'warning':
                case 'success':
                    return $this->redirect()->toRoute('sbmgestion/transport', array(
                        'action' => 'circuit-liste',
                        'page' => $currentPage
                    ));
                    break;
                default:
                    return new ViewModel(array(
                        'form' => $form->prepare(),
                        'page' => $currentPage,
                        'circuitId' => $r->getResult()
                    ));
                    break;
            }
        }
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
                'alias' => 'Sbm\Db\Table\Circuits',
                'id' => 'circuitId'
            ),
            'form' => $form
        );
        $vue_circuits = $this->db_manager->get('Sbm\Db\Vue\Circuits');
        $r = $this->supprData($this->db_manager, $params, function ($id, $tableCircuits) use($vue_circuits) {
            return array(
                'id' => $id,
                'data' => $vue_circuits->getRecord($id)
            );
        });
        if ($r instanceof Response) {
            return $r;
        } else {
            switch ($r->getStatus()) {
                case 'error':
                case 'warning':
                case 'success':
                    return $this->redirect()->toRoute('sbmgestion/transport', array(
                        'action' => 'circuit-liste',
                        'page' => $currentPage
                    ));
                    break;
                default:
                    return new ViewModel(array(
                        'form' => $form->prepare(),
                        'page' => $currentPage,
                        'data' => StdLib::getParam('data', $r->getResult()),
                        'circuitId' => StdLib::getParam('id', $r->getResult())
                    ));
                    break;
            }
        }
    }

    /**
     * Ajout d'une nouvelle fiche de circuit
     * (avec validation des données du formulaire)
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function circuitAjoutAction()
    {
        $currentPage = $this->params('page', 1);
        $form = new FormCircuit();
        $form->setValueOptions('serviceId', $this->db_manager->get('Sbm\Db\Select\Services'))
            ->setValueOptions('stationId', $this->db_manager->get('Sbm\Db\Select\Stations')
            ->ouvertes())
            ->setValueOptions('semaine', Semaine::getJours());
        $params = array(
            'data' => array(
                'table' => 'circuits',
                'type' => 'table',
                'alias' => 'Sbm\Db\Table\Circuits'
            ),
            'form' => $form
        );
        try {
            $r = $this->addData($this->db_manager, $params);
        } catch (\Zend\Db\Adapter\Exception\InvalidQueryException $e) {
            if (stripos($e->getMessage(), '23000 - 1062 - Duplicate entry') !== false) {
                $this->flashMessenger()->addWarningMessage('Impossible ! Cet arrêt est déjà sur ce circuit.');
                $r = 'warning';
            } else {
                throw new \Zend\Db\Adapter\Exception\InvalidQueryException($e->getMessage(), $e->getCode(), $e->getPrevious());
            }
        }
        switch ($r) {
            case $r instanceof Response:
                return $r;
                break;
            case 'error':
            case 'warning':
            case 'success':
                return $this->redirect()->toRoute('sbmgestion/transport', array(
                    'action' => 'circuit-liste',
                    'page' => $currentPage
                ));
                break;
            default:
                return new ViewModel(array(
                    'form' => $form->prepare(),
                    'page' => $currentPage,
                    'circuitId' => null
                ));
                break;
        }
    }

    /**
     * Décoche toutes les fiches marquées sélectionnées
     */
    public function circuitSelectionAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        }
        $args = (array) $prg;
        if (array_key_exists('cancel', $args)) {
            return $this->redirect()->toRoute('sbmgestion/transport', [
                'action' => 'circuit-liste',
                'page' => $this->params('page', 1)
            ]);
        }
        $form = new ButtonForm([], [
            'confirmer' => [
                'class' => 'confirm',
                'value' => 'Confirmer',
                'title' => 'Désélectionner toutes les arrêts des circuits.'
            ],
            'cancel' => [
                'class' => 'confirm',
                'value' => 'Abandonner'
            ]
        ], 'Confirmation', true);
        $tcircuits = $this->db_manager->get('Sbm\Db\Table\Circuits');
        if (array_key_exists('confirmer', $args)) {
            $form->setData($args);
            if ($form->isValid()) {
                $tcircuits->clearSelection();
                $this->flashMessenger()->addSuccessMessage('Toutes les arrêts sont désélectionnées.');
                return $this->redirect()->toRoute('sbmgestion/transport', [
                    'action' => 'circuit-liste',
                    'page' => $this->params('page', 1)
                ]);
            }
        }
        $where = new Where();
        $where->equalTo('selection', 1);
        $view = new ViewModel([
            'form' => $form,
            'nbSelection' => $tcircuits->fetchAll($where)->count()
        ]);
        $view->setTemplate('sbm-gestion/transport/all-selection.phtml');
        return $view;
    }

    public function circuitModifHorairesAction()
    {
        $currentPage = $this->params('page', 1);
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        }
        $args = (array) $prg;
        if (array_key_exists('cancel', $args)) {
            return $this->redirect()->toRoute('sbmgestion/transport', [
                'action' => 'circuit-liste',
                'page' => $currentPage
            ]);
        }
        $form = new \SbmGestion\Form\ModifHoraires();
        if (array_key_exists('submit', $args)) {
            $form->setData($args);
            if ($form->isValid()) {
                $modifHoraires = new \SbmGestion\Model\ModifHoraires($form->getData(), $this->db_manager->get('Sbm\Db\Table\Circuits'));
                if ($modifHoraires->run()) {
                    $this->flashMessenger()->addSuccessMessage('Les horaires ont été modifiés.');
                } else {
                    $this->flashMessenger()->addErrorMessage('Une erreur est survenue lors de la modification des horaires.');
                }
                return $this->redirect()->toRoute('sbmgestion/transport', [
                    'action' => 'circuit-liste',
                    'page' => $currentPage
                ]);
            }
        } else {
            $form->initData();
        }
        return new ViewModel([
            'form' => $form,
            'page' => $currentPage
        ]);
    }

    /**
     * Reçoit la paramètre circuitId en post
     * Renvoie la liste des élèves inscrits pour un circuit donné
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function circuitGroupAction()
    {
        $currentPage = $this->params('page', 1);
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            $args = $this->getFromSession('post', array(), $this->getSessionNamespace());
        } else {
            $args = $prg;
            $this->setToSession('post', $args, $this->getSessionNamespace());
        }
        $circuitId = StdLib::getParam('circuitId', $args, - 1);
        if ($circuitId == - 1) {
            $this->flashMessenger()->addErrorMessage('Action interdite.');
            return $this->redirect()->toRoute('sbmgestion/transport', array(
                'action' => 'circuit-liste',
                'page' => $currentPage
            ));
        }
        $circuit = $this->db_manager->get('Sbm\Db\Vue\Circuits')->getRecord($circuitId);
        return new ViewModel(array(
            'data' => $this->db_manager->get('Sbm\Db\Eleve\Liste')->query($this->getFromSession('millesime'), FiltreEleve::byCircuit($circuit->serviceId, $circuit->stationId, false), array(
                'nom',
                'prenom'
            )),
            'circuit' => $circuit,
            'page' => $currentPage,
            'circuitId' => $circuitId
        ));
    }

    /**
     * Lors de la création d'une nouvelle année scolaire, la table des circuits pour ce millesime est vide.
     * Cette action reprend les circuits de l'année précédente.
     */
    public function circuitDupliquerAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        }
        $dernierMillesime = $this->db_manager->get('Sbm\Db\System\Calendar')->getDernierMillesime();
        if ($dernierMillesime != Session::get('millesime')) {
            $this->flashMessenger()->addInfoMessage('La génération des circuits d\'une nouvelle année ne peut se faire que si cette année est active.');
            return $this->redirect()->toRoute('sbmgestion/transport', array(
                'action' => 'circuit-liste',
                'page' => 1
            ));
        }
        $millesime = $dernierMillesime - 1;
        $tCircuits = $this->db_manager->get('Sbm\Db\Table\Circuits');
        $where = new Where();
        $where->equalTo('millesime', $dernierMillesime);
        $resultset = $tCircuits->fetchAll($where);
        if ($resultset->count()) {
            $this->flashMessenger()->addErrorMessage('Impossible de générer les circuits. Il existe déjà des circuits pour cette année scolaire.');
            return $this->redirect()->toRoute('sbmgestion/transport', array(
                'action' => 'circuit-liste',
                'page' => 1
            ));
        }
        unset($where);
        $where = new Where();
        $where->equalTo('millesime', $millesime);
        $resultset = $tCircuits->fetchAll($where);
        foreach ($resultset as $row) {
            $row->circuitId = null;
            $row->millesime = $dernierMillesime;
            $tCircuits->saveRecord($row);
        }
        $this->flashMessenger()->addSuccessMessage('Les circuits de la dernière année scolaire sont générés.');
        return $this->redirect()->toRoute('sbmgestion/transport', array(
            'action' => 'circuit-liste',
            'page' => 1
        ));
    }

    /**
     * envoie un evenement contenant les paramètres de création d'un document pdf
     * (le listener SbmPdf\Listener\PdfListener lancera la création du pdf)
     * Il n'y a pas de vue associée à cette action puisque la response html est créée par \TCPDF
     */
    public function circuitPdfAction()
    {
        $criteresObject = 'SbmCommun\Model\Db\ObjectData\Criteres';
        $criteresForm = array(
            'SbmCommun\Form\CriteresForm',
            'circuits'
        );
        $documentId = null;
        $retour = array(
            'route' => 'sbmgestion/transport',
            'action' => 'circuit-liste'
        );
        return $this->documentPdf($criteresObject, $criteresForm, $documentId, $retour);
    }

    /**
     * lance la création d'une liste d'élève avec comme filtre le circuitId reçu en post
     */
    public function circuitGroupPdfAction()
    {
        $db_manager = $this->db_manager;
        $criteresObject = array(
            'SbmCommun\Model\Db\ObjectData\Criteres',
            null,
            function ($where, $args) use($db_manager) {
                $circuitId = StdLib::getParam('circuitId', $args, - 1);
                $ocircuit = $db_manager->get('Sbm\Db\Table\Circuits')->getRecord($circuitId);
                $serviceId = $ocircuit->serviceId;
                $stationId = $ocircuit->stationId;
                $where = new Where();
                $where->nest()
                    ->nest()
                    ->equalTo('station1Id', $stationId)
                    ->equalTo('service1Id', $serviceId)
                    ->unnest()->OR->nest()
                    ->equalTo('station2Id', $stationId)
                    ->equalTo('service2Id', $serviceId)
                    ->unnest()
                    ->unnest();
                return $where;
            }
        );
        $criteresForm = 'SbmCommun\Form\CriteresForm';
        $documentId = null;
        $retour = array(
            'route' => 'sbmgestion/transport',
            'action' => 'circuit-group'
        );
        return $this->documentPdf($criteresObject, $criteresForm, $documentId, $retour);
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
        $args = $this->initListe('classes');
        if ($args instanceof Response)
            return $args;
        
        return new ViewModel(array(
            'paginator' => $this->db_manager->get('Sbm\Db\Vue\Classes')->paginator($args['where']),
            't_nb_inscrits' => $this->db_manager->get('Sbm\Db\Eleve\Effectif')->byClasse(),
            'page' => $this->params('page', 1),
            'count_per_page' => $this->getPaginatorCountPerPage('nb_classes', 15),
            'criteres_form' => $args['form']
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
        $form = new FormClasse();
        $form->setValueOptions('niveau', Niveau::getNiveaux())->setValueOptions('suivantId', $this->db_manager->get('Sbm\Db\Select\Classes'));
        $params = array(
            'data' => array(
                'table' => 'classes',
                'type' => 'table',
                'alias' => 'Sbm\Db\Table\Classes',
                'id' => 'classeId'
            ),
            'form' => $form
        );
        
        $r = $this->editData($this->db_manager, $params);
        if ($r instanceof Response) {
            return $r;
        } else {
            switch ($r->getStatus()) {
                case 'error':
                case 'warning':
                case 'success':
                    return $this->redirect()->toRoute('sbmgestion/transport', array(
                        'action' => 'classe-liste',
                        'page' => $currentPage
                    ));
                    break;
                default:
                    return new ViewModel(array(
                        'form' => $form->prepare(),
                        'page' => $currentPage,
                        'classeId' => $r->getResult()
                    ));
                    break;
            }
        }
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
                'alias' => 'Sbm\Db\Table\Classes',
                'id' => 'classeId'
            ),
            'form' => $form
        );
        
        try {
            $r = $this->supprData($this->db_manager, $params, function ($id, $tableClasses) {
                return array(
                    'id' => $id,
                    'data' => $tableClasses->getRecord($id)
                );
            });
        } catch (\Zend\Db\Adapter\Exception\InvalidQueryException $e) {
            $this->flashMessenger()->addWarningMessage('Impossible de supprimer cette classe parce que certains élèves y sont inscrits.');
            return $this->redirect()->toRoute('sbmgestion/transport', array(
                'action' => 'classe-liste',
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
                    return $this->redirect()->toRoute('sbmgestion/transport', array(
                        'action' => 'classe-liste',
                        'page' => $currentPage
                    ));
                    break;
                default:
                    return new ViewModel(array(
                        'form' => $form->prepare(),
                        'page' => $currentPage,
                        'data' => StdLib::getParam('data', $r->getResult()),
                        'classeId' => StdLib::getParam('id', $r->getResult())
                    ));
                    break;
            }
        }
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
        $form = new FormClasse();
        $form->setValueOptions('niveau', Niveau::getNiveaux())->setValueOptions('suivantId', $this->db_manager->get('Sbm\Db\Select\Classes'));
        $params = array(
            'data' => array(
                'table' => 'classes',
                'type' => 'table',
                'alias' => 'Sbm\Db\Table\Classes'
            ),
            'form' => $form
        );
        $r = $this->addData($this->db_manager, $params);
        switch ($r) {
            case $r instanceof Response:
                return $r;
                break;
            case 'error':
            case 'warning':
            case 'success':
                return $this->redirect()->toRoute('sbmgestion/transport', array(
                    'action' => 'classe-liste',
                    'page' => $currentPage
                ));
                break;
            default:
                return new ViewModel(array(
                    'form' => $form->prepare(),
                    'page' => $currentPage,
                    'classeId' => null
                ));
                break;
        }
    }

    /**
     * renvoie la liste des élèves inscrits pour une classe donnée
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function classeGroupAction()
    {
        $pageRetour = $this->params('id', - 1);
        $currentPage = $this->params('page', 1);
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            $args = $this->getFromSession('post', array(), $this->getSessionNamespace());
        } else {
            $args = $prg;
            $this->setToSession('post', $args, $this->getSessionNamespace());
        }
        if ($pageRetour == - 1) {
            $pageRetour = $this->getFromSession('pageRetour', 1, $this->getSessionNamespace());
        } else {
            $this->setToSession('pageRetour', $pageRetour, $this->getSessionNamespace());
        }
        $classeId = StdLib::getParam('classeId', $args, - 1);
        if ($classeId == - 1) {
            $this->flashMessenger()->addErrorMessage('Action interdite.');
            return $this->redirect()->toRoute('sbmgestion/transport', array(
                'action' => 'classe-liste',
                'page' => $pageRetour
            ));
        }
        return new ViewModel(array(
            'paginator' => $this->db_manager->get('Sbm\Db\Eleve\Liste')->paginator($this->getFromSession('millesime'), FiltreEleve::byClasse($classeId), array(
                'nom',
                'prenom'
            )),
            'count_per_page' => $this->getPaginatorCountPerPage('nb_eleves', 15),
            'classe' => $this->db_manager->get('Sbm\Db\Table\Classes')->getRecord($classeId),
            'page' => $currentPage,
            'pageRetour' => $pageRetour,
            'classeId' => $classeId
        ));
    }

    /**
     * envoie un evenement contenant les paramètres de création d'un document pdf
     * (le listener SbmPdf\Listener\PdfListener lancera la création du pdf)
     * Il n'y a pas de vue associée à cette action puisque la response html est créée par \TCPDF
     */
    public function classePdfAction()
    {
        $criteresObject = 'SbmCommun\Model\Db\ObjectData\Criteres';
        $criteresForm = array(
            'SbmCommun\Form\CriteresForm',
            'classes'
        );
        $documentId = null;
        $retour = array(
            'route' => 'sbmgestion/transport',
            'action' => 'classe-liste'
        );
        return $this->documentPdf($criteresObject, $criteresForm, $documentId, $retour);
    }

    /**
     * lance la création d'une liste d'élève avec comme filtre le classeId reçu en post
     */
    public function classeGroupPdfAction()
    {
        $criteresObject = array(
            'SbmCommun\Model\Db\ObjectData\Criteres',
            null,
            function ($where, $args) {
                $classeId = StdLib::getParam('classeId', $args, - 1);
                $where = new Where();
                $where->equalTo('classeId', $classeId);
                return $where;
            }
        );
        $criteresForm = 'SbmCommun\Form\CriteresForm';
        $documentId = null;
        $retour = array(
            'route' => 'sbmgestion/transport',
            'action' => 'classe-group'
        );
        return $this->documentPdf($criteresObject, $criteresForm, $documentId, $retour);
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
        // die(var_dump($this->getFromSession('post', 'vide', $this->getSessionNamespace())));
        $args = $this->initListe('communes');
        
        if ($args instanceof Response)
            return $args;
            // die(var_dump($args['form']));
        return new ViewModel(array(
            'paginator' => $this->db_manager->get('Sbm\Db\Table\Communes')->paginator($args['where']),
            't_nb_inscrits' => $this->db_manager->get('Sbm\Db\Eleve\Effectif')->byCommune(),
            'page' => $this->params('page', 1),
            'count_per_page' => $this->getPaginatorCountPerPage('nb_communes', 20),
            'criteres_form' => $args['form']
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
        $form = new FormCommune();
        $form->modifFormForEdit();
        $params = array(
            'data' => array(
                'table' => 'communes',
                'type' => 'table',
                'alias' => 'Sbm\Db\Table\Communes',
                'id' => 'communeId'
            ),
            'form' => $form
        );
        
        $r = $this->editData($this->db_manager, $params);
        if ($r instanceof Response) {
            return $r;
        } else {
            switch ($r->getStatus()) {
                case 'error':
                case 'warning':
                case 'success':
                    return $this->redirect()->toRoute('sbmgestion/transport', array(
                        'action' => 'commune-liste',
                        'page' => $currentPage
                    ));
                    break;
                default:
                    return new ViewModel(array(
                        'form' => $form->prepare(),
                        'page' => $currentPage,
                        'communeId' => $r->getResult()
                    ));
                    break;
            }
        }
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
                'alias' => 'Sbm\Db\Table\Communes',
                'id' => 'communeId'
            ),
            'form' => $form
        );
        
        try {
            $r = $this->supprData($this->db_manager, $params, function ($id, $tableCommunes) {
                return array(
                    'id' => $id,
                    'data' => $tableCommunes->getRecord($id)
                );
            });
        } catch (\Zend\Db\Adapter\Exception\InvalidQueryException $e) {
            $this->flashMessenger()->addWarningMessage('Impossible de supprimer cette commune car un enregistrement l\'utilise.');
            return $this->redirect()->toRoute('sbmgestion/transport', array(
                'action' => 'commune-liste',
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
                    return $this->redirect()->toRoute('sbmgestion/transport', array(
                        'action' => 'commune-liste',
                        'page' => $currentPage
                    ));
                    break;
                default:
                    return new ViewModel(array(
                        'form' => $form->prepare(),
                        'page' => $currentPage,
                        'data' => StdLib::getParam('data', $r->getResult()),
                        'communeId' => StdLib::getParam('id', $r->getResult())
                    ));
                    break;
            }
        }
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
        $form = new FormCommune();
        $params = array(
            'data' => array(
                'table' => 'communes',
                'type' => 'table',
                'alias' => 'Sbm\Db\Table\Communes'
            ),
            'form' => $form
        );
        $r = $this->addData($this->db_manager, $params);
        switch ($r) {
            case $r instanceof Response:
                return $r;
                break;
            case 'error':
            case 'warning':
            case 'success':
                return $this->redirect()->toRoute('sbmgestion/transport', array(
                    'action' => 'commune-liste',
                    'page' => $currentPage
                ));
                break;
            default:
                return new ViewModel(array(
                    'form' => $form->prepare(),
                    'page' => $currentPage,
                    'communeId' => null
                ));
                break;
        }
    }

    /**
     * renvoie la liste des élèves inscrits pour une commune donnée
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function communeGroupAction()
    {
        $pageRetour = $this->params('id', - 1);
        $currentPage = $this->params('page', 1);
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            $args = $this->getFromSession('post', array(), $this->getSessionNamespace());
        } else {
            $args = $prg;
            $this->setToSession('post', $args, $this->getSessionNamespace());
        }
        if ($pageRetour == - 1) {
            $pageRetour = $this->getFromSession('pageRetour', 1, $this->getSessionNamespace());
        } else {
            $this->setToSession('pageRetour', $pageRetour, $this->getSessionNamespace());
        }
        $communeId = StdLib::getParam('communeId', $args, - 1);
        if ($communeId == - 1) {
            $this->flashMessenger()->addErrorMessage('Action interdite.');
            return $this->redirect()->toRoute('sbmgestion/transport', array(
                'action' => 'commune-liste',
                'page' => $pageRetour
            ));
        }
        return new ViewModel(array(
            'paginator' => $this->db_manager->get('Sbm\Db\Eleve\Liste')->paginator($this->getFromSession('millesime'), FiltreEleve::byCommune($communeId), array(
                'nom',
                'prenom'
            )),
            'count_per_page' => $this->getPaginatorCountPerPage('nb_eleves', 15),
            'commune' => $this->db_manager->get('Sbm\Db\Table\Communes')->getRecord($communeId),
            'page' => $currentPage,
            'pageRetour' => $pageRetour,
            'communeId' => $communeId
        ));
    }

    /**
     * envoie un evenement contenant les paramètres de création d'un document pdf
     * (le listener SbmPdf\Listener\PdfListener lancera la création du pdf)
     * Il n'y a pas de vue associée à cette action puisque la response html est créée par \TCPDF
     */
    public function communePdfAction()
    {
        $criteresObject = 'SbmCommun\Model\Db\ObjectData\Criteres';
        $criteresForm = array(
            'SbmCommun\Form\CriteresForm',
            'communes'
        );
        $documentId = null;
        $retour = array(
            'route' => 'sbmgestion/transport',
            'action' => 'commune-liste'
        );
        return $this->documentPdf($criteresObject, $criteresForm, $documentId, $retour);
    }

    /**
     * lance la création d'une liste d'élève avec comme filtre le communeId reçu en post
     */
    public function communeGroupPdfAction()
    {
        $criteresObject = array(
            'SbmCommun\Model\Db\ObjectData\Criteres',
            null,
            function ($where, $args) {
                $communeId = StdLib::getParam('communeId', $args, - 1);
                $where = new Where();
                $where->equalTo('communeId', $communeId);
                return $where;
            }
        );
        $criteresForm = 'SbmCommun\Form\CriteresForm';
        $documentId = null;
        $retour = array(
            'route' => 'sbmgestion/transport',
            'action' => 'commune-group'
        );
        return $this->documentPdf($criteresObject, $criteresForm, $documentId, $retour);
    }

    /**
     * =============================================== ETABLISSEMENTS ==================================================
     */
    /**
     * Critère de sélection commun aux établissements et aux stations.
     * La localisation géographique est dans un rectangle défini dans la config (voir config/autoload/sbm.local.php)
     * (paramètres dans cartes - etablissements - valide)
     *
     * @return string
     */
    private function critereLocalisation()
    {
        $projection = $this->cartographie_manager->get('SbmCarto\Projection');
        $rangeX = $projection->getRangeX();
        $rangeY = $projection->getRangeY();
        $pasLocalisaton = 'Not((x Between %d And %d) And (y Between %d And %d))';
        return sprintf($pasLocalisaton, $rangeX['etablissements'][0], $rangeX['etablissements'][1], $rangeY['etablissements'][0], $rangeY['etablissements'][1]);
    }

    /**
     * Liste des etablissements
     * (avec pagination)
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function etablissementListeAction()
    {
        $args = $this->initListe('etablissements', null, array(), array(
            'localisation' => 'Literal:' . $this->critereLocalisation()
        ));
        if ($args instanceof Response)
            return $args;
        
        return new ViewModel(array(
            'paginator' => $this->db_manager->get('Sbm\Db\Vue\Etablissements')->paginator($args['where']),
            't_nb_inscrits' => $this->db_manager->get('Sbm\Db\Eleve\Effectif')->byEtablissement(),
            'page' => $this->params('page', 1),
            'count_per_page' => $this->getPaginatorCountPerPage('nb_etablissements', 10),
            'criteres_form' => $args['form'],
            'projection' => $this->cartographie_manager->get('SbmCarto\Projection')
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
        $form = new FormEtablissement();
        $form->modifFormForEdit()
            ->setValueOptions('jOuverture', Semaine::getJours())
            ->setValueOptions('niveau', Niveau::getNiveaux())
            ->setValueOptions('rattacheA', $this->db_manager->get('Sbm\Db\Select\Etablissements')
            ->visibles())
            ->setValueOptions('communeId', $this->db_manager->get('Sbm\Db\Select\Communes')
            ->desservies());
        $params = array(
            'data' => array(
                'table' => 'etablissements',
                'type' => 'table',
                'alias' => 'Sbm\Db\Table\Etablissements',
                'id' => 'etablissementId'
            ),
            'form' => $form
        );
        
        $r = $this->editData($this->db_manager, $params);
        if ($r instanceof Response) {
            return $r;
        } else {
            switch ($r->getStatus()) {
                case 'error':
                case 'warning':
                case 'success':
                    return $this->redirect()->toRoute('sbmgestion/transport', array(
                        'action' => 'etablissement-liste',
                        'page' => $currentPage
                    ));
                    break;
                default:
                    return new ViewModel(array(
                        'form' => $form->prepare(),
                        'page' => $currentPage,
                        'etablissementId' => $r->getResult()
                    ));
                    break;
            }
        }
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
                'alias' => 'Sbm\Db\Table\Etablissements',
                'id' => 'etablissementId'
            ),
            'form' => $form
        );
        $vueEtablissement = $this->db_manager->get('Sbm\Db\Vue\Etablissements');
        try {
            $r = $this->supprData($this->db_manager, $params, function ($id, $tableEtablissements) use($vueEtablissement) {
                return array(
                    'id' => $id,
                    'data' => $vueEtablissement->getRecord($id)
                );
            });
        } catch (\Zend\Db\Adapter\Exception\InvalidQueryException $e) {
            $this->flashMessenger()->addWarningMessage('Impossible de supprimer cet établissement car un enregistrement l\'utilise.');
            return $this->redirect()->toRoute('sbmgestion/transport', array(
                'action' => 'etablissement-liste',
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
                    return $this->redirect()->toRoute('sbmgestion/transport', array(
                        'action' => 'etablissement-liste',
                        'page' => $currentPage
                    ));
                    break;
                default:
                    return new ViewModel(array(
                        'form' => $form->prepare(),
                        'page' => $currentPage,
                        'data' => StdLib::getParam('data', $r->getResult()),
                        'etablissementId' => StdLib::getParam('id', $r->getResult())
                    ));
                    break;
            }
        }
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
        $form = new FormEtablissement();
        $form->setValueOptions('jOuverture', Semaine::getJours())
            ->setValueOptions('niveau', Niveau::getNiveaux())
            ->setValueOptions('rattacheA', $this->db_manager->get('Sbm\Db\Select\Etablissements')
            ->visibles())
            ->setValueOptions('communeId', $this->db_manager->get('Sbm\Db\Select\Communes')
            ->desservies());
        $params = array(
            'data' => array(
                'table' => 'etablissements',
                'type' => 'table',
                'alias' => 'Sbm\Db\Table\Etablissements'
            ),
            'form' => $form
        );
        $r = $this->addData($this->db_manager, $params);
        switch ($r) {
            case $r instanceof Response:
                return $r;
                break;
            case 'error':
            case 'warning':
                return $this->redirect()->toRoute('sbmgestion/transport', array(
                    'action' => 'etablissement-liste',
                    'page' => $currentPage
                ));
                break;
            case 'success':
                $viewmodel = $this->etablissementLocalisationAction($form->getData()->etablissementId, $currentPage);
                $viewmodel->setTemplate('sbm-gestion/transport/etablissement-localisation.phtml');
                return $viewmodel;
                break;
            default:
                return new ViewModel(array(
                    'form' => $form->prepare(),
                    'page' => $currentPage,
                    'etablissementId' => null
                ));
                break;
        }
    }

    /**
     * renvoie la liste des élèves inscrits pour un etablissement donné
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function etablissementGroupAction()
    {
        $pageRetour = $this->params('id', - 1);
        $currentPage = $this->params('page', 1);
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            $args = $this->getFromSession('post', array(), $this->getSessionNamespace());
        } else {
            $args = $prg;
            $this->setToSession('post', $args, $this->getSessionNamespace());
        }
        if ($pageRetour == - 1) {
            $pageRetour = $this->getFromSession('pageRetour', 1, $this->getSessionNamespace());
        } else {
            $this->setToSession('pageRetour', $pageRetour, $this->getSessionNamespace());
        }
        $etablissementId = StdLib::getParam('etablissementId', $args, - 1);
        if ($etablissementId == - 1) {
            $this->flashMessenger()->addErrorMessage('Action interdite.');
            return $this->redirect()->toRoute('sbmgestion/transport', array(
                'action' => 'etablissement-liste',
                'page' => $pageRetour
            ));
        }
        return new ViewModel(array(
            'paginator' => $this->db_manager->get('Sbm\Db\Eleve\Liste')->paginator($this->getFromSession('millesime'), FiltreEleve::byEtablissement($etablissementId), array(
                'nom',
                'prenom'
            )),
            'count_per_page' => $this->getPaginatorCountPerPage('nb_eleves', 15),
            'etablissement' => $this->db_manager->get('Sbm\Db\Vue\Etablissements')->getRecord($etablissementId),
            'page' => $currentPage,
            'pageRetour' => $pageRetour,
            'etablissementId' => $etablissementId
        ));
    }

    /**
     * envoie un evenement contenant les paramètres de création d'un document pdf
     * (le listener SbmPdf\Listener\PdfListener lancera la création du pdf)
     * Il n'y a pas de vue associée à cette action puisque la response html est créée par \TCPDF
     */
    public function etablissementPdfAction()
    {
        $criteresObject = array(
            'SbmCommun\Model\Db\ObjectData\Criteres',
            array(
                'expressions' => array(
                    'localisation' => 'Literal:' . $this->critereLocalisation()
                )
            ),
            function ($where, $args) {
                return $where->equalTo('millesime', Session::get('millesime'));
            }
        );
        $criteresForm = array(
            'SbmCommun\Form\CriteresForm',
            'etablissements'
        );
        $documentId = null;
        $retour = array(
            'route' => 'sbmgestion/transport',
            'action' => 'etablissement-liste'
        );
        return $this->documentPdf($criteresObject, $criteresForm, $documentId, $retour);
    }

    /**
     * lance la création d'une liste d'élève avec comme filtre le etablissementId reçu en post
     */
    public function etablissementGroupPdfAction()
    {
        $criteresObject = array(
            'SbmCommun\Model\Db\ObjectData\Criteres',
            null,
            function ($where, $args) {
                $etablissementId = StdLib::getParam('etablissementId', $args, - 1);
                $where = new Where();
                $where->equalTo('etablissementId', $etablissementId);
                return $where;
            }
        );
        $criteresForm = 'SbmCommun\Form\CriteresForm';
        $documentId = null;
        $retour = array(
            'route' => 'sbmgestion/transport',
            'action' => 'etablissement-group'
        );
        return $this->documentPdf($criteresObject, $criteresForm, $documentId, $retour);
    }

    /**
     * lance la création d'une liste se services desservant l'établissementId reçu en post
     */
    public function etablissementServicePdfAction()
    {
        $criteresObject = array(
            'SbmCommun\Model\Db\ObjectData\Criteres',
            null,
            function ($where, $args) {
                $where->equalTo('etablissementId', StdLib::getParam('etablissementId', $args, - 1));
                return $where;
            }
        );
        $criteresForm = 'SbmCommun\Form\CriteresForm';
        $documentId = null;
        $retour = array(
            'route' => 'sbmgestion/transport',
            'action' => 'etablissement-service'
        );
        return $this->documentPdf($criteresObject, $criteresForm, $documentId, $retour);
    }

    /**
     * lance la création d'une liste d'élève avec comme filtre le couple (etablissementId, serviceId) reçu en post
     */
    public function etablissementServiceGroupPdfAction()
    {
        $criteresObject = array(
            'SbmCommun\Model\Db\ObjectData\Criteres',
            null,
            function ($where, $args) {
                $etablissementId = StdLib::getParam('etablissementId', $args, - 1);
                $serviceId = StdLib::getParam('serviceId', $args, - 1);
                $where = new Where();
                $where->equalTo('millesime', Session::get('millesime'))
                    ->equalTo('etablissementId', $etablissementId)
                    ->equalTo('serviceId', $serviceId);
                return $where;
            }
        );
        $criteresForm = 'SbmCommun\Form\CriteresForm';
        $documentId = null;
        $retour = array(
            'route' => 'sbmgestion/transport',
            'action' => 'etablissement-service-group'
        );
        return $this->documentPdf($criteresObject, $criteresForm, $documentId, $retour);
    }

    /**
     * Localisation d'un établissement sur la carte et enregistrement de ses coordonnées
     */
    public function etablissementLocalisationAction($etablissementId = null, $currentPage = 1)
    {
        if (is_null($etablissementId)) {
            $currentPage = $this->params('page', 1);
            $prg = $this->prg();
            if ($prg instanceof Response) {
                return $prg;
            } elseif ($prg === false) {
                $this->flashMessenger()->addWarningMessage('Recommencez.');
                return $this->redirect()->toRoute('sbmgestion/transport', array(
                    'action' => 'etablissement-liste',
                    'page' => $currentPage
                ));
            } else {
                $args = $prg;
                if (array_key_exists('cancel', $args)) {
                    $this->flashMessenger()->addWarningMessage('Localisation abandonnée.');
                    return $this->redirect()->toRoute('sbmgestion/transport', array(
                        'action' => 'etablissement-liste',
                        'page' => $currentPage
                    ));
                }
                if (! array_key_exists('etablissementId', $args)) {
                    $this->flashMessenger()->addErrorMessage('Action  interdite');
                    return $this->redirect()->toRoute('login', array(
                        'action' => 'logout'
                    ));
                }
            }
            $etablissementId = $args['etablissementId'];
        } else {
            $args = [];
        }
        $d2etab = $this->cartographie_manager->get('SbmCarto\DistanceEtablissements');
        $tEtablissements = $this->db_manager->get('Sbm\Db\Table\Etablissements');
        $configCarte = StdLib::getParam('parent', $this->cartographie_manager->get('cartes'));
        $form = new LatLng(array(
            'etablissementId' => array(
                'id' => 'etablissementId'
            )
        ), array(
            'submit' => array(
                'class' => 'button default submit left-95px',
                'value' => 'Enregistrer la localisation'
            ),
            'cancel' => array(
                'class' => 'button default cancel left-10px',
                'value' => 'Abandonner'
            )
        ), $configCarte['valide']);
        $form->setAttribute('action', $this->url()
            ->fromRoute('sbmgestion/transport', array(
            'action' => 'etablissement-localisation',
            'page' => $currentPage
        )));
        if (array_key_exists('submit', $args)) {
            $form->setData($args);
            if ($form->isValid()) {
                // transforme les coordonnées
                $pt = new Point($args['lng'], $args['lat'], 0, 'degré');
                $point = $d2etab->getProjection()->gRGF93versXYZ($pt);
                // enregistre dans la fiche etablissement
                $oData = $tEtablissements->getObjData();
                $oData->exchangeArray(array(
                    'etablissementId' => $etablissementId,
                    'x' => $point->getX(),
                    'y' => $point->getY()
                ));
                $tEtablissements->saveRecord($oData);
                $this->flashMessenger()->addSuccessMessage('La localisation de l\'établissement est enregistrée.');
                $this->flashMessenger()->addWarningMessage('Attention ! Les distances des domiciles des élèves à l\'établissement n\'ont pas été mises à jour.');
                return $this->redirect()->toRoute('sbmgestion/transport', array(
                    'action' => 'etablissement-liste',
                    'page' => $currentPage
                ));
            }
        }
        $etablissement = $tEtablissements->getRecord($etablissementId);
        $description = '<b>' . $etablissement->nom . "</b>\n";
        $commune = $this->db_manager->get('Sbm\Db\table\Communes')->getRecord($etablissement->communeId);
        if ($etablissement->x == 0.0 && $etablissement->y == 0.0) {
            // essayer de localiser par l'adresse avant de présenter la carte
            $array = $this->cartographie_manager->get('SbmCarto\Geocoder')->geocode($etablissement->adresse1, $etablissement->codePostal, $commune->nom);
            $pt = new Point($array['lng'], $array['lat'], 0, 'degré');
            $description .= $array['adresse'];
        } else {
            $point = new Point($etablissement->x, $etablissement->y);
            $pt = $d2etab->getProjection()->xyzVersgRGF93($point);
            $description .= trim(implode("\n", array(
                $etablissement->adresse1,
                $etablissement->adresse2
            )), "\n");
            $description .= "\n" . $etablissement->codePostal . ' ' . $commune->nom;
        }
        $description = str_replace("\n", "", nl2br($description));
        $form->setData(array(
            'etablissementId' => $etablissementId,
            'lat' => $pt->getLatitude(),
            'lng' => $pt->getLongitude()
        ));
        $tEtablissements = $this->db_manager->get('Sbm\Db\Vue\Etablissements');
        $ptEtablissements = array();
        foreach ($tEtablissements->fetchAll() as $autreEtablissement) {
            if ($autreEtablissement->etablissementId != $etablissementId) {
                $pt = new Point($autreEtablissement->x, $autreEtablissement->y);
                $pt->setAttribute('etablissement', $autreEtablissement);
                $ptEtablissements[] = $d2etab->getProjection()->xyzVersgRGF93($pt);
            }
        }
        return new ViewModel(array(
            // 'pt' => $pt,
            'form' => $form->prepare(),
            'description' => $description,
            'etablissement' => array(
                $etablissement->nom,
                nl2br(trim(implode("\n", array(
                    $etablissement->adresse1,
                    $etablissement->adresse2
                )))),
                $etablissement->codePostal . ' ' . $commune->nom
            ),
            'ptEtablissements' => $ptEtablissements,
            'url_api' => $this->cartographie_manager->get('google_api')['js'],
            'config' => $configCarte
        ));
    }

    /**
     * ========================================== ETABLISSEMENTS-SERVICES ========================================
     */
    
    /**
     * renvoie la liste des élèves inscrits pour un etablissement donné
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function etablissementServiceAction()
    {
        $prg = $this->prg();
        $cancel = false;
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            $etablissementId = $this->getFromSession('etablissementId', false, $this->getSessionNamespace());
        } else {
            $args = $prg;
            if (StdLib::getParam('op', $args, '') == 'retour') {
                $etablissementId = null;
                $cancel = true;
            } else {
                $etablissementId = StdLib::getParam('etablissementId', $args, - 1);
                $this->setToSession('etablissementId', $etablissementId, $this->getSessionNamespace());
            }
        }
        $currentPage = $this->params('page', 1);
        $pageRetour = $this->params('id', - 1);
        if ($pageRetour == - 1) {
            $pageRetour = $this->getFromSession('pageRetour', 1, $this->getSessionNamespace());
        } else {
            $this->setToSession('pageRetour', $pageRetour, $this->getSessionNamespace());
        }
        if ($etablissementId == - 1) {
            $this->flashMessenger()->addErrorMessage('Action interdite.');
            $cancel = true;
        }
        if ($cancel) {
            return $this->redirect()->toRoute('sbmgestion/transport', array(
                'action' => 'etablissement-liste',
                'page' => $pageRetour
            ));
        }
        $table = $this->db_manager->get('Sbm\Db\Vue\EtablissementsServices');
        $where = new Where();
        $where->equalTo('etablissementId', $etablissementId)->equalTo('cir_millesime', Session::get('millesime'));
        return new ViewModel(array(
            'etablissement' => $this->db_manager->get('Sbm\Db\Vue\Etablissements')->getRecord($etablissementId),
            'paginator' => $table->paginator($where),
            'count_per_page' => 15,
            't_nb_inscrits' => $this->db_manager->get('Sbm\Db\Eleve\Effectif')->byServiceGivenEtablissement($etablissementId),
            'page' => $currentPage,
            'etablissementId' => $etablissementId
        ));
    }

    /**
     * renvoie la liste des élèves inscrits pour un service donné
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function serviceEtablissementAction()
    {
        $prg = $this->prg();
        $cancel = false;
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            $serviceId = $this->getFromSession('serviceId', false, $this->getSessionNamespace());
        } else {
            $args = $prg;
            if (StdLib::getParam('op', $args, '') == 'retour') {
                $serviceId = null;
                $cancel = true;
            } else {
                $serviceId = StdLib::getParam('serviceId', $args, - 1);
                $this->setToSession('serviceId', $serviceId, $this->getSessionNamespace());
            }
        }
        $currentPage = $this->params('page', 1);
        $pageRetour = $this->params('id', - 1);
        if ($pageRetour == - 1) {
            $pageRetour = $this->getFromSession('pageRetour', 1, $this->getSessionNamespace());
        } else {
            $this->setToSession('pageRetour', $pageRetour, $this->getSessionNamespace());
        }
        if ($serviceId == - 1) {
            $this->flashMessenger()->addErrorMessage('Action interdite.');
            $cancel = true;
        }
        if ($cancel) {
            return $this->redirect()->toRoute('sbmgestion/transport', array(
                'action' => 'service-liste',
                'page' => $pageRetour
            ));
        }
        $table = $this->db_manager->get('Sbm\Db\Vue\EtablissementsServices');
        return new ViewModel(array(
            'service' => $this->db_manager->get('Sbm\Db\Vue\Services')->getRecord($serviceId),
            'data' => $table->fetchAll(array(
                'serviceId' => $serviceId,
                'cir_millesime' => Session::get('millesime')
            )),
            't_nb_inscrits' => $this->db_manager->get('Sbm\Db\Eleve\Effectif')->byEtablissementGivenService($serviceId),
            'page' => $currentPage,
            'pageRetour' => $pageRetour,
            'serviceId' => $serviceId
        ));
    }

    /**
     * Ajout d'un lien etablissement - service
     * (la validation porte sur un champ csrf)
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function etablissementServiceAjoutAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            $args = $this->getFromSession('post', array(), $this->getSessionNamespace());
            if (StdLib::getParam('origine', $args, false) === false) {
                $this->flashMessenger()->addErrorMessage('Action interdite');
                return $this->redirect()->toRoute('sbmgestion/transport'); // on n'est pas capable de savoir d'où l'on vient
            }
        } else {
            $args = $prg;
            $this->setToSession('post', $args, $this->getSessionNamespace());
        }
        $currentPage = $this->params('page', 1);
        $origine = StdLib::getParam('origine', $args, 'index');
        if (! is_null(StdLib::getParam('cancel', $args))) {
            $this->flashMessenger()->addWarningMessage('Abandon de la création d\'une relation entre un service et un établissement.');
            return $this->redirect()->toRoute('sbmgestion/transport', array(
                'action' => $origine,
                'page' => $currentPage
            ));
        }
        $etablissementId = StdLib::getParam('etablissementId', $args, null);
        $serviceId = StdLib::getParam('serviceId', $args, null);
        $isPost = ! is_null(StdLib::getParam('submit', $args));
        $form = new FormEtablissementService($origine == 'etablissement-service' ? 'service' : 'etablissement');
        if ($origine == 'etablissement-service') {
            $service = null;
            $etablissement = $this->db_manager->get('Sbm\Db\Vue\Etablissements')->getRecord($etablissementId);
            $form->setValueOptions('serviceId', $this->db_manager->get('Sbm\Db\Select\Services'));
        } else {
            $etablissement = null;
            $service = $this->db_manager->get('Sbm\Db\Vue\Services')->getRecord($serviceId);
            $form->setValueOptions('etablissementId', $this->db_manager->get('Sbm\Db\Select\Etablissements')
                ->desservis());
        }
        $table = $this->db_manager->get('Sbm\Db\Table\EtablissementsServices');
        $form->bind($table->getObjData());
        if ($isPost) {
            $form->setData($args);
            if ($form->isValid()) {
                $table->saveRecord($form->getData());
                $this->flashMessenger()->addSuccessMessage("Une relation entre un service et un établissement a été crée.");
                return $this->redirect()->toRoute('sbmgestion/transport', array(
                    'action' => $origine,
                    'page' => $currentPage
                ));
            }
        } else {
            $form->setData(array(
                'etablissementId' => $etablissementId,
                'serviceId' => $serviceId,
                'origine' => $origine
            ));
        }
        if (! empty($serviceId)) {
            $form->setValueOptions('stationId', $this->db_manager->get('Sbm\Db\Select\Stations')
                ->surcircuit($serviceId, Session::get('millesime')));
        }
        return new ViewModel(array(
            'origine' => $origine,
            'form' => $form->prepare(),
            'page' => $currentPage,
            'etablissementId' => $etablissementId,
            'serviceId' => $serviceId,
            'etablissement' => $etablissement,
            'service' => $service
        ));
    }

    /**
     * Suppression d'une relation établissement-service avec confirmation
     * A l'appel, les variables suivantes sont récupérées : $etablissementId, $serviceId, $origine, $op, $supprimer
     * Lors de l'annulation, on a : $etablissementId, $serviceId, $origine, $op, $supprnon
     * Lors de la validation on a : $etablissementId, $serviceId, $origine, $op, $supproui
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function etablissementServiceSupprAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            $args = array();
        } else {
            $args = $prg;
        }
        $origine = StdLib::getParam('origine', $args, 'index');
        $etablissementId = StdLib::getParam('etablissementId', $args, false);
        $serviceId = StdLib::getParam('serviceId', $args, false);
        $cancel = StdLib::getParam('cancel', $args, false);
        if ($origine == 'index' || $etablissementId === false || $serviceId == false) {
            $this->flashMessenger()->addErrorMessage("Action interdite.");
            return $this->redirect()->toRoute('sbmgestion/transport', array(
                'action' => 'index',
                'page' => $this->params('page', 1)
            ));
        } elseif ($cancel) {
            $this->flashMessenger()->addWarningMessage("L'enregistrement n'a pas été supprimé.");
            return $this->redirect()->toRoute('sbmgestion/transport', array(
                'action' => $origine,
                'page' => $this->params('page', 1)
            ));
        }
        $form = new FormEtablissementServiceSuppr();
        $form->setAttribute('action', $this->url()
            ->fromRoute('sbmgestion/transport', array(
            'action' => 'etablissement-service-suppr',
            'page' => $this->params('page', 1)
        )));
        $table = $this->db_manager->get('Sbm\Db\Table\EtablissementsServices');
        if (array_key_exists('submit', $args)) { // suppression confirmée
            $form->setData($args);
            if ($form->isValid()) {
                $table->deleteRecord(array(
                    'etablissementId' => $etablissementId,
                    'serviceId' => $serviceId
                ));
                $this->flashMessenger()->addSuccessMessage("L'enregistrement a été supprimé.");
                return $this->redirect()->toRoute('sbmgestion/transport', array(
                    'action' => $origine,
                    'page' => $this->params('page', 1)
                ));
            }
        } else {
            $form->setData(array(
                'etablissementId' => $etablissementId,
                'serviceId' => $serviceId,
                'origine' => $origine
            ));
        }
        return new ViewModel(array(
            'etablissementId' => $etablissementId,
            'serviceId' => $serviceId,
            'origine' => $origine,
            'etablissement' => $this->db_manager->get('Sbm\Db\Vue\Etablissements')->getRecord($etablissementId),
            'service' => $this->db_manager->get('Sbm\Db\Vue\Services')->getRecord($serviceId),
            'form' => $form->prepare()
        ));
    }

    /**
     * renvoie la liste des élèves inscrits pour un etablissement donné et un service donné
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function etablissementServiceGroupAction()
    {
        $pageRetour = $this->params('id', - 1);
        $currentPage = $this->params('page', 1);
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            $args = $this->getFromSession('post', array(), $this->getSessionNamespace());
        } else {
            $args = $prg;
            $this->setToSession('post', $args, $this->getSessionNamespace());
        }
        if ($pageRetour == - 1) {
            $pageRetour = $this->getFromSession('pageRetour', 1, $this->getSessionNamespace());
        } else {
            $this->setToSession('pageRetour', $pageRetour, $this->getSessionNamespace());
        }
        $etablissementId = StdLib::getParam('etablissementId', $args, - 1);
        $serviceId = StdLib::getParam('serviceId', $args, - 1);
        if ($etablissementId == - 1 || $serviceId == - 1) {
            $this->flashMessenger()->addErrorMessage('Action interdite.');
            return $this->redirect()->toRoute('sbmgestion/transport', array(
                'action' => 'etablissement-liste',
                'page' => $pageRetour
            ));
        }
        $viewModel = new ViewModel(array(
            'h1' => 'Groupe des élèves d\'un établissement inscrits sur un service',
            'paginator' => $this->db_manager->get('Sbm\Db\Eleve\Liste')->paginatorByEtablissementService($this->getFromSession('millesime'), $etablissementId, $serviceId, array(
                'nom',
                'prenom'
            )),
            'count_per_page' => $this->getPaginatorCountPerPage('nb_eleves', 15),
            'etablissement' => $this->db_manager->get('Sbm\Db\Vue\Etablissements')->getRecord($etablissementId),
            'service' => $this->db_manager->get('Sbm\Db\Vue\Services')->getRecord($serviceId),
            'page' => $currentPage,
            'pageRetour' => $pageRetour,
            'etablissementId' => $etablissementId,
            'serviceId' => $serviceId,
            'origine' => StdLib::getParam('origine', $args, 'etablissement-service')
        ));
        $viewModel->setTemplate('sbm-gestion/transport/service-group.phtml');
        return $viewModel;
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
        $args = $this->initListe('services', function ($config, $form) {
            $form->setValueOptions('transporteurId', $config['db_manager']->get('Sbm\Db\Select\Transporteurs'));
        }, array(
            'transporteurId'
        ));
        if ($args instanceof Response)
            return $args;
        
        return new ViewModel(array(
            'paginator' => $this->db_manager->get('Sbm\Db\Vue\Services')->paginator($args['where']),
            'page' => $this->params('page', 1),
            'count_per_page' => $this->getPaginatorCountPerPage('nb_services', 15),
            'criteres_form' => $args['form'],
            't_nb_inscrits' => $this->db_manager->get('Sbm\Db\Eleve\Effectif')->byService()
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
        $form = new FormService();
        $form->modifFormForEdit()->setValueOptions('transporteurId', $this->db_manager->get('Sbm\Db\Select\Transporteurs'));
        $params = array(
            'data' => array(
                'table' => 'services',
                'type' => 'table',
                'alias' => 'Sbm\Db\Table\Services',
                'id' => 'serviceId'
            ),
            'form' => $form
        );
        
        $r = $this->editData($this->db_manager, $params);
        if ($r instanceof Response) {
            return $r;
        } else {
            switch ($r->getStatus()) {
                case 'error':
                case 'warning':
                case 'success':
                    return $this->redirect()->toRoute('sbmgestion/transport', array(
                        'action' => 'service-liste',
                        'page' => $currentPage
                    ));
                    break;
                default:
                    return new ViewModel(array(
                        'form' => $form->prepare(),
                        'page' => $currentPage,
                        'serviceId' => $r->getResult()
                    ));
                    break;
            }
        }
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
        $form = new ButtonForm(array(
            'id' => null,
            'origine' => null
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
                'alias' => 'Sbm\Db\Table\Services',
                'id' => 'serviceId'
            ),
            'form' => $form
        );
        $vueServices = $this->db_manager->get('Sbm\Db\Vue\Services');
        try {
            $r = $this->supprData($this->db_manager, $params, function ($id, $tableServices) use($vueServices) {
                return array(
                    'id' => $id,
                    'data' => $vueServices->getRecord($id)
                );
            });
        } catch (\Zend\Db\Adapter\Exception\InvalidQueryException $e) {
            $this->flashMessenger()->addWarningMessage('Impossible de supprimer ce service car un enregistrement l\'utilise.');
            return $this->redirect()->toRoute('sbmgestion/transport', array(
                'action' => 'service-liste',
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
                    return $this->redirect()->toRoute('sbmgestion/transport', array(
                        'action' => 'service-liste',
                        'page' => $currentPage
                    ));
                    break;
                default:
                    return new ViewModel(array(
                        'form' => $form->prepare(),
                        'page' => $currentPage,
                        'data' => StdLib::getParam('data', $r->getResult()),
                        'serviceId' => StdLib::getParam('id', $r->getResult())
                    ));
                    break;
            }
        }
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
        $form = new FormService();
        $form->setValueOptions('transporteurId', $this->db_manager->get('Sbm\Db\Select\Transporteurs'));
        $params = array(
            'data' => array(
                'table' => 'services',
                'type' => 'table',
                'alias' => 'Sbm\Db\Table\Services'
            ),
            'form' => $form
        );
        $r = $this->addData($this->db_manager, $params);
        switch ($r) {
            case $r instanceof Response:
                return $r;
                break;
            case 'error':
            case 'warning':
            case 'success':
                return $this->redirect()->toRoute('sbmgestion/transport', array(
                    'action' => 'service-liste',
                    'page' => $currentPage
                ));
                break;
            default:
                return new ViewModel(array(
                    'form' => $form->prepare(),
                    'page' => $currentPage,
                    'serviceId' => null
                ));
                break;
        }
    }

    /**
     * renvoie la liste des élèves inscrits pour un service donné
     * Reçoit en get :
     * - id : pageRetour
     * - page : page du paginateur interne
     * Reçoit en post :
     * - serviceId
     * - origine
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function serviceGroupAction()
    {
        $pageRetour = $this->params('id', - 1);
        $currentPage = $this->params('page', 1);
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            $args = $this->getFromSession('post', array(), $this->getSessionNamespace());
        } else {
            $args = $prg;
            $this->setToSession('post', $args, $this->getSessionNamespace());
        }
        if ($pageRetour == - 1) {
            $pageRetour = $this->getFromSession('pageRetour', 1, $this->getSessionNamespace());
        } else {
            $this->setToSession('pageRetour', $pageRetour, $this->getSessionNamespace());
        }
        $serviceId = StdLib::getParam('serviceId', $args, - 1);
        if ($serviceId == - 1) {
            $this->flashMessenger()->addErrorMessage('Action interdite.');
            return $this->redirect()->toRoute('sbmgestion/transport', array(
                'action' => 'service-liste',
                'page' => $pageRetour
            ));
        }
        
        return new ViewModel(array(
            'h1' => 'Groupe des élèves inscrits sur un service',
            'paginator' => $this->db_manager->get('Sbm\Db\Eleve\Liste')->paginator($this->getFromSession('millesime'), FiltreEleve::byService($serviceId), array(
                'nom',
                'prenom'
            )),
            'count_per_page' => $this->getPaginatorCountPerPage('nb_eleves', 15),
            'service' => $this->db_manager->get('Sbm\Db\Vue\Services')->getRecord($serviceId),
            'page' => $currentPage,
            'pageRetour' => $pageRetour,
            'serviceId' => $serviceId,
            'origine' => StdLib::getParam('origine', $args, 'service-liste')
        ));
    }

    /**
     * envoie un evenement contenant les paramètres de création d'un document pdf
     * (le listener SbmPdf\Listener\PdfListener lancera la création du pdf)
     * Il n'y a pas de vue associée à cette action puisque la response html est créée par \TCPDF
     */
    public function servicePdfAction()
    {
        $criteresObject = 'SbmCommun\Model\Db\ObjectData\Criteres';
        $criteresForm = array(
            'SbmCommun\Form\CriteresForm',
            'services'
        );
        $documentId = null;
        $retour = array(
            'route' => 'sbmgestion/transport',
            'action' => 'service-liste'
        );
        return $this->documentPdf($criteresObject, $criteresForm, $documentId, $retour);
    }

    /**
     * lance la création d'une liste d'établissements desservis le serviceId reçu en post
     */
    public function serviceEtablissementPdfAction()
    {
        $criteresObject = array(
            'SbmCommun\Model\Db\ObjectData\Criteres',
            null,
            function ($where, $args) {
                $where->equalTo('serviceId', StdLib::getParam('serviceId', $args, - 1));
                return $where;
            }
        );
        $criteresForm = 'SbmCommun\Form\CriteresForm';
        $documentId = null;
        $retour = array(
            'route' => 'sbmgestion/transport',
            'action' => 'service-etablissement'
        );
        return $this->documentPdf($criteresObject, $criteresForm, $documentId, $retour);
    }

    /**
     * lance la création d'une liste d'élève avec comme filtre le serviceId reçu en post
     */
    public function serviceGroupPdfAction()
    {
        $criteresObject = array(
            'SbmCommun\Model\Db\ObjectData\Criteres',
            null,
            function ($where, $args) {
                $serviceId = StdLib::getParam('serviceId', $args, - 1);
                $where = new Where();
                $where->equalTo('millesime', Session::get('millesime'))->equalTo('serviceId', $serviceId);
                return $where;
            }
        );
        $criteresForm = 'SbmCommun\Form\CriteresForm';
        $documentId = null;
        $retour = array(
            'route' => 'sbmgestion/transport',
            'action' => 'service-group'
        );
        return $this->documentPdf($criteresObject, $criteresForm, $documentId, $retour);
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
        $args = $this->initListe('stations', function ($config, $form) {
            $form->setValueOptions('communeId', $config['db_manager']->get('Sbm\Db\Select\Communes')
                ->desservies());
        }, array(
            'communeId'
        ), array(
            'localisation' => 'Literal:' . $this->critereLocalisation()
        ));
        if ($args instanceof Response)
            return $args;
        
        return new ViewModel(array(
            'paginator' => $this->db_manager->get('Sbm\Db\Vue\Stations')->paginator($args['where']),
            't_nb_inscrits' => $this->db_manager->get('Sbm\Db\Eleve\Effectif')->byStation(),
            'page' => $this->params('page', 1),
            'count_per_page' => $this->getPaginatorCountPerPage('nb_stations', 10),
            'criteres_form' => $args['form'],
            'projection' => $this->cartographie_manager->get('SbmCarto\Projection')
        ));
    }

    /**
     * Liste des stations non desservies
     * (sans pagination)
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function stationsNonDesserviesAction()
    {
        $currentPage = $this->params('page', 1);
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        }
        
        return new ViewModel(array(
            'data' => $this->db_manager->get('Sbm\Db\Circuit\Liste')->stationsNonDesservies(),
            't_nb_inscrits' => $this->db_manager->get('Sbm\Db\Eleve\Effectif')->byStation(),
            'page' => $currentPage
        ));
    }

    /**
     * Demande l'envoi d'un document contenant les stations non desservies
     */
    public function stationsNonDesserviesPdfAction()
    {
        $criteresObject = array(
            'SbmCommun\Model\Db\ObjectData\Criteres'
        );
        $criteresForm = array(
            'SbmCommun\Form\CriteresForm'
        );
        $documentId = null;
        $retour = array(
            'route' => 'sbmgestion/transport',
            'action' => 'stations-non-desservies'
        );
        return $this->documentPdf($criteresObject, $criteresForm, $documentId, $retour);
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
        $request = $this->getRequest();
        if ($request->isPost()) {
            $origine = $request->getPost('origine', false);
            if ($origine) {
                $this->redirectToOrigin()->setBack($origine);
            }
        }
        $form = new FormStation();
        $form->setValueOptions('communeId', $this->db_manager->get('Sbm\Db\Select\Communes')
            ->desservies());
        $params = array(
            'data' => array(
                'table' => 'stations',
                'type' => 'table',
                'alias' => 'Sbm\Db\Table\Stations',
                'id' => 'stationId'
            ),
            'form' => $form
        );
        
        $r = $this->editData($this->db_manager, $params);
        if ($r instanceof Response) {
            return $r;
        } else {
            switch ($r->getStatus()) {
                case 'error':
                case 'warning':
                case 'success':
                    return $this->redirectToOrigin()->back();
                    break;
                default:
                    $form->add(array(
                        'name' => 'origine',
                        'type' => 'hidden',
                        'attributes' => array(
                            'value' => StdLib::getParam('origine', $r->getPost())
                        )
                    ));
                    return new ViewModel(array(
                        'form' => $form->prepare(),
                        'page' => $currentPage,
                        'stationId' => $r->getResult()
                    ));
                    break;
            }
        }
    }

    /**
     * Suppression d'une fiche avec confirmation
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function stationSupprAction()
    {
        $currentPage = $this->params('page', 1);
        $request = $this->getRequest();
        if ($request->isPost()) {
            $origine = $request->getPost('origine', false);
            if ($origine) {
                $this->redirectToOrigin()->setBack($origine);
            }
        }
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
                'alias' => 'Sbm\Db\Table\Stations',
                'id' => 'stationId'
            ),
            'form' => $form
        );
        $vueStations = $this->db_manager->get('Sbm\Db\Vue\Stations');
        try {
            $r = $this->supprData($this->db_manager, $params, function ($id, $tableStations) use($vueStations) {
                return array(
                    'id' => $id,
                    'data' => $vueStations->getRecord($id)
                );
            });
        } catch (\Zend\Db\Adapter\Exception\InvalidQueryException $e) {
            $this->flashMessenger()->addWarningMessage('Impossible de supprimer cette station car un enregistrement l\'utilise.');
            try {
                return $this->redirectToOrigin()->back();
            } catch (\SbmCommun\Model\Mvc\Controller\Plugin\Exception $e) {
                return $this->redirect()->toRoute('sbmgestion/transport', array(
                    'action' => 'station-liste',
                    'page' => $currentPage
                ));
            }
        }
        
        if ($r instanceof Response) {
            return $r;
        } else {
            switch ($r->getStatus()) {
                case 'error':
                case 'warning':
                case 'success':
                    try {
                        return $this->redirectToOrigin()->back();
                    } catch (\SbmCommun\Model\Mvc\Controller\Plugin\Exception $e) {
                        return $this->redirect()->toRoute('sbmgestion/transport', array(
                            'action' => StdLib::getParam('origine', $r->getPost()),
                            'page' => $currentPage
                        ));
                    }
                    break;
                default:
                    return new ViewModel(array(
                        'form' => $form->prepare(),
                        'page' => $currentPage,
                        'data' => StdLib::getParam('data', $r->getResult()),
                        'stationId' => StdLib::getParam('id', $r->getResult())
                    ));
                    break;
            }
        }
    }

    /**
     * Montre la carte des stations.
     * Un clic dans la carte permet de placer la nouvelle station.
     * On enregistre la position.
     * Le formulaire prérempli est présenté avec la commune, l'adresse (N° + rue)
     * et les coordonnées X et Y
     * On peut alors changer le nom de la station avant d'enregistrer la fiche.
     *
     * @return \Zend\Http\PhpEnvironment\Response|\Zend\Http\Response|\Zend\View\Model\ViewModel
     */
    public function stationAjoutAction()
    {
        $currentPage = $this->params('page', 1);
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            $cancel = true;
            $args = array();
        } else {
            $args = (array) $prg;
            $isPost1 = array_key_exists('phase', $args);
            $isPost2 = array_key_exists('csrf', $args);
            $cancel = StdLib::getParam('cancel', $args, false);
            // unset($args['submit']);
            // unset($args['cancel']);
        }
        if ($cancel) {
            $this->flashMessenger()->addWarningMessage("Abandon de la création d'une nouvelle station.");
            return $this->redirect()->toRoute('sbmgestion/transport', array(
                'action' => 'station-liste',
                'page' => $currentPage
            ));
        }
        $table = $this->db_manager->get('Sbm\Db\Table\Stations');
        // même configuration de carte que pour les etablissements
        $configCarte = StdLib::getParam('parent', $this->cartographie_manager->get('cartes'));
        $d2etab = $this->cartographie_manager->get('SbmCarto\DistanceEtablissements');
        $formCarte = new LatLng(array(
            'phase' => 1,
            'lat' => array(
                'id' => 'lat'
            ),
            'lng' => array(
                'id' => 'lng'
            )
        ), array(
            'submit' => array(
                'class' => 'button default submit left-95px',
                'value' => 'Enregistrer la localisation'
            ),
            'cancel' => array(
                'class' => 'button default cancel left-10px',
                'value' => 'Abandonner'
            )
        ), $configCarte['valide']);
        if ($isPost1 || $isPost2) {
            $form = new FormStation();
            $form->setValueOptions('communeId', $this->db_manager->get('Sbm\Db\Select\Communes')
                ->desservies())
                ->setMaxLength($this->db_manager->getMaxLengthArray('stations', 'table'));
            
            $form->bind($table->getObjData());
            if ($isPost1) {
                $formCarte->setData($args);
                if (! $formCarte->isValid()) {
                    $this->flashMessenger()->addWarningMessage("La nouvelle station n'est pas dans la zone autorisée.");
                    return $this->redirect()->toRoute('sbmgestion/transport', array(
                        'action' => 'station-liste',
                        'page' => $currentPage
                    ));
                }
                // transforme les coordonnées
                $pt = new Point($args['lng'], $args['lat'], 0, 'degré');
                $point = $d2etab->getProjection()->gRGF93versXYZ($pt);
                // initialise le formulaire de la station
                $geocode = $this->cartographie_manager->get('SbmCarto\Geocoder');
                $lieu = $geocode->reverseGeocoding($args['lat'], $args['lng']);
                $form->setData(array(
                    'communeId' => $this->db_manager->get('Sbm\Db\Table\Communes')
                        ->getCommuneId($lieu['commune']),
                    'nom' => implode(' ', array(
                        $lieu['numero'],
                        $lieu['rue'],
                        $lieu['lieu-dit']
                    )),
                    'x' => $point->getX(),
                    'y' => $point->getY()
                ));
            } elseif ($isPost2) {
                $form->setData($args);
                if ($form->isValid()) {
                    $table->saveRecord($form->getData());
                    $this->flashMessenger()->addSuccessMessage("Un nouvel enregistrement a été ajouté.");
                    return $this->redirect()->toRoute('sbmgestion/transport', array(
                        'action' => 'station-liste',
                        'page' => $currentPage
                    ));
                }
            } else {
                $defauts = $this->db_manager->getColumnDefaults('stations', 'table');
                unset($defauts['x'], $defauts['y']);
                $form->setData($defauts);
            }
            $view = new ViewModel(array(
                'form' => $form->prepare(),
                'page' => $currentPage,
                'stationId' => null
            ));
            $view->setTemplate('sbm-gestion/transport/station-ajout.phtml');
        } else {
            $formCarte->setAttribute('action', $this->url()
                ->fromRoute('sbmgestion/transport', array(
                'action' => 'station-ajout',
                'page' => $this->params('page', $currentPage)
            )));
            $tStations = $this->db_manager->get('Sbm\Db\Vue\Stations');
            $ptStations = array();
            foreach ($tStations->fetchAll() as $station) {
                $pt = new Point($station->x, $station->y);
                $pt->setAttribute('station', $station);
                $ptStations[] = $d2etab->getProjection()->xyzVersgRGF93($pt);
            }
            $view = new ViewModel(array(
                'form' => $formCarte->prepare(),
                'description' => '<b>Nouvelle station</b>',
                'station' => array(
                    'Création d\'une nouvelle station'
                ),
                'ptStations' => $ptStations,
                'url_api' => $this->cartographie_manager->get('google_api')['js'],
                'config' => $configCarte
            ));
            $view->setTemplate('sbm-gestion/transport/station-localisation.phtml');
        }
        return $view;
    }

    /**
     * renvoie la liste des élèves inscrits pour une station donnée
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function stationGroupAction()
    {
        $currentPage = $this->params('page', 1);
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            $args = $this->getFromSession('post', array(), $this->getSessionNamespace());
        } else {
            $args = $prg;
            $this->setToSession('post', $args, $this->getSessionNamespace());
        }
        $stationId = StdLib::getParam('stationId', $args, - 1);
        if ($stationId == - 1) {
            $this->flashMessenger()->addErrorMessage('Action interdite.');
            return $this->redirect()->toRoute('sbmgestion/transport', array(
                'action' => 'station-liste',
                'page' => $currentPage
            ));
        }
        
        return new ViewModel(array(
            'data' => $this->db_manager->get('Sbm\Db\Eleve\Liste')->query($this->getFromSession('millesime'), FiltreEleve::byStation($stationId), array(
                'nom',
                'prenom'
            )),
            // 'paginator' => $table_eleves->paginator(),
            'station' => $this->db_manager->get('Sbm\Db\Vue\Stations')->getRecord($stationId),
            'page' => $currentPage,
            'stationId' => $stationId,
            'origine' => StdLib::getParam('origine', $args)
        ));
    }

    /**
     * renvoie la liste des services d'une station
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function stationServiceAction()
    {
        $currentPage = $this->params('page', 1);
        $pageRetour = $this->params('id', - 1);
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            $args = $this->getFromSession('post', array(), $this->getSessionNamespace());
        } else {
            $args = $prg;
            $this->setToSession('post', $args, $this->getSessionNamespace());
        }
        if ($pageRetour == - 1) {
            $pageRetour = $this->getFromSession('pageRetour', 1, $this->getSessionNamespace());
        } else {
            $this->setToSession('pageRetour', $pageRetour, $this->getSessionNamespace());
        }
        $stationId = StdLib::getParam('stationId', $args, - 1);
        if ($stationId == - 1) {
            $circuitId = StdLib::getParam('circuitId', $args, - 1);
            $circuit = $this->db_manager->get('Sbm\Db\Table\Circuits')->getRecord($circuitId);
            if (! empty($circuit)) {
                $stationId = $circuit->stationId;
            }
        }
        if ($stationId == - 1) {
            $this->flashMessenger()->addErrorMessage('Action interdite.');
            return $this->redirect()->toRoute('sbmgestion/transport', array(
                'action' => 'station-liste',
                'page' => $pageRetour
            ));
        }
        
        return new ViewModel(array(
            'data' => $this->db_manager->get('Sbm\Db\Circuit\Liste')->byStation($stationId),
            // 'paginator' => $table_eleves->paginator(),
            't_nb_inscrits' => $this->db_manager->get('Sbm\Db\Eleve\Effectif')->byServiceGivenStation($stationId),
            'station' => $this->db_manager->get('Sbm\Db\Vue\Stations')->getRecord($stationId),
            'page' => $currentPage,
            'pageRetour' => $pageRetour,
            'stationId' => $stationId
        ));
    }

    public function stationServiceGroupAction()
    {
        $currentPage = $this->params('page', 1);
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            $args = $this->getFromSession('post', array(), $this->getSessionNamespace());
        } else {
            $args = $prg;
            $this->setToSession('post', $args, $this->getSessionNamespace());
        }
        $stationId = StdLib::getParam('stationId', $args, - 1);
        $serviceId = StdLib::getParam('serviceId', $args, false);
        $millesime = $this->getFromSession('millesime');
        if ($stationId == - 1 || ! $serviceId) {
            $this->flashMessenger()->addErrorMessage('Action interdite.');
            return $this->redirect()->toRoute('sbmgestion/transport', array(
                'action' => StdLib::getParam('origine', $args, 'station-service'),
                'page' => $currentPage
            ));
        }
        $circuit = $this->db_manager->get('Sbm\Db\Vue\Circuits')->getRecord(array(
            'stationId' => $stationId,
            'serviceId' => $serviceId,
            'millesime' => $millesime
        ));
        $view = new ViewModel(array(
            'data' => $this->db_manager->get('Sbm\Db\Eleve\Liste')->query($millesime, FiltreEleve::byCircuit($serviceId, $stationId, false), array(
                'nom',
                'prenom'
            )),
            'circuit' => $circuit,
            'page' => $currentPage,
            'circuitId' => $circuit->circuitId,
            'origine' => StdLib::getParam('origine', $args, 'station-service')
        ));
        $view->setTemplate('sbm-gestion/transport/circuit-group.phtml');
        return $view;
    }

    /**
     * envoie un evenement contenant les paramètres de création d'un document pdf
     * (le listener SbmPdf\Listener\PdfListener lancera la création du pdf)
     * Il n'y a pas de vue associée à cette action puisque la response html est créée par \TCPDF
     */
    public function stationPdfAction()
    {
        $criteresObject = array(
            'SbmCommun\Model\Db\ObjectData\Criteres',
            array(
                'strict' => array(
                    'communeId'
                ),
                'expressions' => array(
                    'localisation' => 'Literal:' . $this->critereLocalisation()
                )
            )
        );
        $criteresForm = array(
            'SbmCommun\Form\CriteresForm',
            'stations'
        );
        $documentId = null;
        $retour = array(
            'route' => 'sbmgestion/transport',
            'action' => 'station-liste'
        );
        return $this->documentPdf($criteresObject, $criteresForm, $documentId, $retour);
    }

    /**
     * Demande le document contenant lea services passant par une station
     *
     * @return Ambigous <\Zend\Http\PhpEnvironment\Response, \Zend\Http\Response>
     */
    public function stationServicePdfAction()
    {
        $criteresObject = array(
            'SbmCommun\Model\Db\ObjectData\Criteres',
            array(),
            function ($where, $args) {
                $stationId = StdLib::getParam('stationId', $args);
                $where = new Where();
                return $where->equalTo('stationId', $stationId);
            }
        );
        $criteresForm = 'SbmCommun\Form\CriteresForm';
        $documentId = null;
        $retour = array(
            'route' => 'sbmgestion/transport',
            'action' => 'station-service'
        );
        return $this->documentPdf($criteresObject, $criteresForm, $documentId, $retour);
    }

    /**
     * lance la création d'une liste d'élève avec comme filtre le circuitId reçu en post
     */
    public function stationGroupPdfAction()
    {
        $criteresObject = array(
            'SbmCommun\Model\Db\ObjectData\Criteres',
            null,
            function ($where, $args) {
                $stationId = StdLib::getParam('stationId', $args, - 1);
                $where = new Where();
                $where->nest()->equalTo('station1Id', $stationId)->OR->equalTo('station2Id', $stationId)->unnest();
                return $where;
            }
        );
        $criteresForm = 'SbmCommun\Form\CriteresForm';
        $documentId = null;
        $retour = array(
            'route' => 'sbmgestion/transport',
            'action' => 'station-group'
        );
        return $this->documentPdf($criteresObject, $criteresForm, $documentId, $retour);
    }

    /**
     * Localisation d'une station sur la carte et enregistrement de ses coordonnées
     * Toutes les stations sont affichées.
     *
     * La station à localiser est repérée par un bulet rouge.
     */
    public function stationLocalisationAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            $this->flashMessenger()->addWarningMessage('Recommencez.');
            return $this->redirect()->toRoute('sbmgestion/transport', array(
                'action' => 'station-liste',
                'page' => $this->params('page', 1)
            ));
        } else {
            $args = $prg;
            if (array_key_exists('cancel', $args)) {
                $this->flashMessenger()->addWarningMessage('Localisation abandonnée.');
                return $this->redirect()->toRoute('sbmgestion/transport', array(
                    'action' => 'station-liste',
                    'page' => $this->params('page', 1)
                ));
            }
            if (! array_key_exists('stationId', $args)) {
                $this->flashMessenger()->addErrorMessage('Action  interdite');
                return $this->redirect()->toRoute('login', array(
                    'action' => 'logout'
                ));
            }
        }
        $d2etab = $this->cartographie_manager->get('SbmCarto\DistanceEtablissements');
        $stationId = $args['stationId'];
        $tStations = $this->db_manager->get('Sbm\Db\Table\Stations');
        // même configuration de carte que pour les etablissements
        $configCarte = StdLib::getParam('parent', $this->cartographie_manager->get('cartes'));
        $form = new LatLng(array(
            'stationId' => array(
                'id' => 'stationId'
            ),
            'lat' => array(
                'id' => 'lat'
            ),
            'lng' => array(
                'id' => 'lng'
            )
        ), array(
            'submit' => array(
                'class' => 'button default submit left-95px',
                'value' => 'Enregistrer la localisation'
            ),
            'cancel' => array(
                'class' => 'button default cancel left-10px',
                'value' => 'Abandonner'
            )
        ), $configCarte['valide']);
        $form->setAttribute('action', $this->url()
            ->fromRoute('sbmgestion/transport', array(
            'action' => 'station-localisation',
            'page' => $this->params('page', 1)
        )));
        if (array_key_exists('submit', $args)) {
            $form->setData($args);
            if ($form->isValid()) {
                // transforme les coordonnées
                $pt = new Point($args['lng'], $args['lat'], 0, 'degré');
                $point = $d2etab->getProjection()->gRGF93versXYZ($pt);
                // enregistre dans la fiche station
                $oData = $tStations->getObjData();
                $oData->exchangeArray(array(
                    'stationId' => $stationId,
                    'x' => $point->getX(),
                    'y' => $point->getY()
                ));
                $tStations->saveRecord($oData);
                $this->flashMessenger()->addSuccessMessage('La localisation de la station est enregistrée.');
                // $this->flashMessenger()->addWarningMessage('Attention ! Les distances des domiciles des élèves à l\'établissement n\'ont pas été mises à jour.');
                return $this->redirect()->toRoute('sbmgestion/transport', array(
                    'action' => 'station-liste',
                    'page' => $this->params('page', 1)
                ));
            }
        }
        $station = $tStations->getRecord($stationId);
        $commune = $this->db_manager->get('Sbm\Db\table\Communes')->getRecord($station->communeId);
        $description = '<b>' . $station->nom . '</b></br>' . $commune->codePostal . ' ' . $commune->nom;
        if ($station->x == 0.0 && $station->y == 0.0) {
            // essayer de localiser par l'adresse avant de présenter la carte
            $array = $this->cartographie_manager->get('SbmCarto\Geocoder')->geocode($station->nom, $commune->codePostal, $commune->nom);
            $pt = new Point($array['lng'], $array['lat'], 0, 'degré');
        } else {
            $point = new Point($station->x, $station->y);
            $pt = $d2etab->getProjection()->xyzVersgRGF93($point);
        }
        $form->setData(array(
            'stationId' => $stationId,
            'lat' => $pt->getLatitude(),
            'lng' => $pt->getLongitude()
        ));
        $tStations = $this->db_manager->get('Sbm\Db\Vue\Stations');
        $ptStations = array();
        foreach ($tStations->fetchAll() as $autreStation) {
            if ($autreStation->stationId != $stationId) {
                $pt = new Point($autreStation->x, $autreStation->y);
                $pt->setAttribute('station', $autreStation);
                $ptStations[] = $d2etab->getProjection()->xyzVersgRGF93($pt);
            }
        }
        return new ViewModel(array(
            // 'pt' => $pt,
            'form' => $form->prepare(),
            'description' => $description,
            'station' => array(
                $station->nom,
                $commune->codePostal . ' ' . $commune->nom
            ),
            'ptStations' => $ptStations,
            'url_api' => $this->cartographie_manager->get('google_api')['js'],
            'config' => $configCarte
        ));
    }

    /**
     * =============================================== TRANSPORTEURS ==================================================
     */
    
    /**
     * Liste des transporteurs
     * (avec pagination)
     *
     * @return ViewModel
     */
    public function transporteurListeAction()
    {
        $args = $this->initListe('transporteurs');
        if ($args instanceof Response)
            return $args;
        
        return new ViewModel(array(
            'paginator' => $this->db_manager->get('Sbm\Db\Vue\Transporteurs')->paginator($args['where']),
            't_nb_inscrits' => $this->db_manager->get('Sbm\Db\Eleve\Effectif')->byTransporteur(),
            'page' => $this->params('page', 1),
            'count_per_page' => $this->getPaginatorCountPerPage('nb_transporteurs', 15),
            'criteres_form' => $args['form']
        ));
    }

    /**
     * Modification d'une fiche de transporteur
     * (la validation porte sur un champ csrf)
     *
     * @return ViewModel
     */
    public function transporteurEditAction()
    {
        $currentPage = $this->params('page', 1);
        $form = new FormTransporteur();
        $form->setValueOptions('communeId', $this->db_manager->get('Sbm\Db\Select\Communes')
            ->visibles());
        $params = array(
            'data' => array(
                'table' => 'transporteurs',
                'type' => 'table',
                'alias' => 'Sbm\Db\Table\Transporteurs',
                'id' => 'transporteurId'
            ),
            'form' => $form
        );
        
        $r = $this->editData($this->db_manager, $params);
        if ($r instanceof Response) {
            return $r;
        } else {
            switch ($r->getStatus()) {
                case 'error':
                case 'warning':
                case 'success':
                    return $this->redirect()->toRoute('sbmgestion/transport', array(
                        'action' => 'transporteur-liste',
                        'page' => $currentPage
                    ));
                    break;
                default:
                    return new ViewModel(array(
                        'form' => $form->prepare(),
                        'page' => $currentPage,
                        'transporteurId' => $r->getResult()
                    ));
                    break;
            }
        }
    }

    /**
     * Suppression d'une fiche avec confirmation
     *
     * @todo : Vérifier qu'il n'y a pas de service attribué avant de supprimer la fiche
     *      
     * @return ViewModel
     */
    public function transporteurSupprAction()
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
                'alias' => 'Sbm\Db\Table\Transporteurs',
                'id' => 'transporteurId'
            ),
            'form' => $form
        );
        $vuetransporteurs = $this->db_manager->get('Sbm\Db\Vue\Transporteurs');
        try {
            $r = $this->supprData($this->db_manager, $params, function ($id, $tabletransporteurs) use($vuetransporteurs) {
                return array(
                    'id' => $id,
                    'data' => $vuetransporteurs->getRecord($id)
                );
            });
        } catch (\Exception $e) {
            $this->flashMessenger()->addWarningMessage('Impossible de supprimer ce transporteur car il a un service.');
            return $this->redirect()->toRoute('sbmgestion/transport', array(
                'action' => 'transporteur-liste',
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
                    return $this->redirect()->toRoute('sbmgestion/transport', array(
                        'action' => 'transporteur-liste',
                        'page' => $currentPage
                    ));
                    break;
                default:
                    return new ViewModel(array(
                        'form' => $form->prepare(),
                        'page' => $currentPage,
                        'data' => StdLib::getParam('data', $r->getResult()),
                        'transporteurId' => StdLib::getParam('id', $r->getResult())
                    ));
                    break;
            }
        }
    }

    /**
     * Ajout d'une nouvelle fiche de transporteur
     * (la validation porte sur un champ csrf)
     *
     * @return ViewModel
     */
    public function transporteurAjoutAction()
    {
        $currentPage = $this->params('page', 1);
        $form = new Formtransporteur();
        $form->setValueOptions('communeId', $this->db_manager->get('Sbm\Db\Select\Communes')
            ->visibles());
        $params = array(
            'data' => array(
                'table' => 'transporteurs',
                'type' => 'table',
                'alias' => 'Sbm\Db\Table\Transporteurs'
            ),
            'form' => $form
        );
        $r = $this->addData($this->db_manager, $params);
        switch ($r) {
            case $r instanceof Response:
                return $r;
                break;
            case 'error':
            case 'warning':
            case 'success':
                return $this->redirect()->toRoute('sbmgestion/transport', array(
                    'action' => 'transporteur-liste',
                    'page' => $currentPage
                ));
                break;
            default:
                return new ViewModel(array(
                    'form' => $form->prepare(),
                    'page' => $currentPage,
                    'transporteurId' => null
                ));
                break;
        }
    }

    /**
     * renvoie la liste des élèves pour un transporteur donné
     *
     * @return ViewModel
     */
    public function transporteurGroupAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            $args = $this->getFromSession('post', array(), $this->getSessionNamespace());
        } else {
            $args = $prg;
            $this->setToSession('post', $args, $this->getSessionNamespace());
        }
        $currentPage = $this->params('page', 1);
        $pageRetour = $this->params('id', - 1);
        if ($pageRetour == - 1) {
            $pageRetour = $this->getFromSession('pageRetour', 1, $this->getSessionNamespace());
        } else {
            $this->setToSession('pageRetour', $pageRetour, $this->getSessionNamespace());
        }
        $transporteurId = StdLib::getParam('transporteurId', $args, - 1);
        if ($transporteurId == - 1) {
            $this->flashMessenger()->addErrorMessage('Action interdite.');
            return $this->redirect()->toRoute('sbmgestion/transport', array(
                'action' => 'transporteur-liste',
                'page' => $pageRetour
            ));
        }
        
        return new ViewModel(array(
            'paginator' => $this->db_manager->get('Sbm\Db\Eleve\Liste')->paginatorByTransporteur($this->getFromSession('millesime'), FiltreEleve::byTransporteur($transporteurId), array(
                'serviceId',
                'nom',
                'prenom'
            )),
            'count_per_page' => $this->getPaginatorCountPerPage('nb_eleves', 15),
            'transporteur' => $this->db_manager->get('Sbm\Db\Table\Transporteurs')->getRecord($transporteurId),
            'page' => $currentPage,
            'pageRetour' => $pageRetour,
            'transporteurId' => $transporteurId
        ));
    }

    /**
     * renvoie la liste des services d'un transporteur
     *
     * @return ViewModel
     */
    public function transporteurServiceAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            $args = $this->getFromSession('post', array(), $this->getSessionNamespace());
        } else {
            $args = $prg;
            $this->setToSession('post', $args, $this->getSessionNamespace());
        }
        $currentPage = $this->params('page', 1);
        $pageRetour = $this->params('id', - 1);
        if ($pageRetour == - 1) {
            $pageRetour = $this->getFromSession('pageRetour', 1, $this->getSessionNamespace());
        } else {
            $this->setToSession('pageRetour', $pageRetour, $this->getSessionNamespace());
        }
        $transporteurId = StdLib::getParam('transporteurId', $args, - 1);
        if ($transporteurId == - 1) {
            $this->flashMessenger()->addErrorMessage('Action interdite.');
            return $this->redirect()->toRoute('sbmgestion/transport', array(
                'action' => 'transporteur-liste',
                'page' => $pageRetour
            ));
        }
        $where = new Where();
        $where->equalTo('transporteurId', $transporteurId);
        return new ViewModel(array(
            'paginator' => $this->db_manager->get('Sbm\Db\Table\Services')->paginator($where, array(
                'serviceId'
            )),
            'count_per_page' => 15,
            't_nb_inscrits' => $this->db_manager->get('Sbm\Db\Eleve\Effectif')->transporteurByService($transporteurId),
            'transporteur' => $this->db_manager->get('Sbm\Db\Table\Transporteurs')->getRecord($transporteurId),
            'page' => $currentPage,
            'pageRetour' => $pageRetour,
            'transporteurId' => $transporteurId
        ));
    }

    /**
     * envoie un evenement contenant les paramètres de création d'un document pdf
     * (le listener SbmPdf\Listener\PdfListener lancera la création du pdf)
     * Il n'y a pas de vue associée à cette action puisque la response html est créée par \TCPDF
     */
    public function transporteurPdfAction()
    {
        $criteresObject = 'SbmCommun\Model\Db\ObjectData\Criteres';
        $criteresForm = array(
            'SbmCommun\Form\CriteresForm',
            'transporteurs'
        );
        $documentId = null;
        $retour = array(
            'route' => 'sbmgestion/transport',
            'action' => 'transporteur-liste'
        );
        return $this->documentPdf($criteresObject, $criteresForm, $documentId, $retour);
    }

    public function transporteurGroupPdfAction()
    {
        $criteresObject = array(
            'SbmCommun\Model\Db\ObjectData\Criteres',
            null,
            function ($where, $args) {
                $transporteurId = StdLib::getParam('transporteurId', $args, - 1);
                $where = new Where();
                $where->equalTo('transporteurId', $transporteurId);
                return $where;
            }
        );
        $criteresForm = 'SbmCommun\Form\CriteresForm';
        $documentId = null;
        $retour = array(
            'route' => 'sbmgestion/transport',
            'action' => 'transporteur-group'
        );
        return $this->documentPdf($criteresObject, $criteresForm, $documentId, $retour);
    }

    /**
     * Demande le document contenant les services d'un transporteur
     *
     * @return Ambigous <\Zend\Http\PhpEnvironment\Response, \Zend\Http\Response>
     */
    public function transporteurServicePdfAction()
    {
        $criteresObject = array(
            'SbmCommun\Model\Db\ObjectData\Criteres',
            array(),
            function ($where, $args) {
                $transporteurId = StdLib::getParam('transporteurId', $args);
                $where = new Where();
                return $where->equalTo('transporteurId', $transporteurId);
            }
        );
        $criteresForm = 'SbmCommun\Form\CriteresForm';
        $documentId = null;
        $retour = array(
            'route' => 'sbmgestion/transport',
            'action' => 'transporteur-service'
        );
        return $this->documentPdf($criteresObject, $criteresForm, $documentId, $retour);
    }
}