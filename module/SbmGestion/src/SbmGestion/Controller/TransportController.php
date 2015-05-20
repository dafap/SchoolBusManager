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
use Zend\Http\PhpEnvironment\Response;
use SbmCommun\Model\Mvc\Controller\AbstractActionController;
use SbmCommun\Form\ButtonForm;
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
use SbmCommun\Model\StdLib;
use SbmCommun\Model\Strategy\Niveau;
use SbmCommun\Model\Strategy\Semaine;
use SbmGestion\Form\EtablissementServiceSuppr as FormEtablissementServiceSuppr;
use SbmGestion\Form\SbmGestion\Form;

class TransportController extends AbstractActionController
{

    public function indexAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        }
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
        $args = $this->initListe('circuits', function ($sm, $form) {
            $form->setValueOptions('stationId', $sm->get('Sbm\Db\Select\Stations')->ouvertes());
            $form->setValueOptions('serviceId', $sm->get('Sbm\Db\Select\Services'));
        }, array(
            'stationId'
        ));
        if ($args instanceof Response)
            return $args;
        
        $args['where']->equalTo('millesime', $this->getFromSession('millesime'));
        return new ViewModel(array(
            'paginator' => $this->getServiceLocator()
                ->get('Sbm\Db\Vue\Circuits')
                ->paginator($args['where']),
            't_nb_inscrits' => $this->getServiceLocator()
                ->get('Sbm\Db\Eleve\Effectif')
                ->byCircuit(),
            'page' => $this->params('page', 1),
            'nb_pagination' => $this->getNbPagination('nb_circuits', 10),
            'criteres_form' => $args['form']
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
        $form->setValueOptions('serviceId', $this->getServiceLocator()
            ->get('Sbm\Db\Select\Services'))
            ->setValueOptions('stationId', $this->getServiceLocator()
            ->get('Sbm\Db\Select\Stations')->ouvertes())
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
        
        $r = $this->editData($params);
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
        $sm = $this->getServiceLocator();
        $r = $this->supprData($params, function ($id, $tableCircuits) use ($sm){
            return array(
                'id' => $id,
                'data' => $sm->get('Sbm\Db\Vue\Circuits')->getRecord($id)
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
        $form->setValueOptions('serviceId', $this->getServiceLocator()
            ->get('Sbm\Db\Select\Services'))
            ->setValueOptions('stationId', $this->getServiceLocator()
            ->get('Sbm\Db\Select\Stations')->ouvertes())
            ->setValueOptions('semaine', Semaine::getJours());
        $params = array(
            'data' => array(
                'table' => 'circuits',
                'type' => 'table',
                'alias' => 'Sbm\Db\Table\Circuits'
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
     * renvoie la liste des élèves inscrits pour un circuit donné
     *
     * @todo : à faire
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
        $circuit = $this->getServiceLocator()
            ->get('Sbm\Db\Vue\Circuits')
            ->getRecord($circuitId);
        return new ViewModel(array(
            'data' => $this->getServiceLocator()
                ->get('Sbm\Db\Eleve\Liste')
                ->byCircuit($this->getFromSession('millesime'), array(
                array(
                    'service1Id' => $circuit->serviceId,
                    'station1Id' => $circuit->stationId
                ),
                'or',
                array(
                    'service2Id' => $circuit->serviceId,
                    'station2Id' => $circuit->stationId
                )
            ), array(
                'nom',
                'prenom'
            )),
            'circuit' => $circuit,
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
        $args = $this->initListe('classes');
        if ($args instanceof Response)
            return $args;
        
        return new ViewModel(array(
            'paginator' => $this->getServiceLocator()
                ->get('Sbm\Db\Table\Classes')
                ->paginator($args['where']),
            't_nb_inscrits' => $this->getServiceLocator()
                ->get('Sbm\Db\Eleve\Effectif')
                ->byClasse(),
            'page' => $this->params('page', 1),
            'nb_pagination' => $this->getNbPagination('nb_classes', 15),
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
        $form->setValueOptions('niveau', Niveau::getNiveaux());
        $params = array(
            'data' => array(
                'table' => 'classes',
                'type' => 'table',
                'alias' => 'Sbm\Db\Table\Classes',
                'id' => 'classeId'
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
        
        $r = $this->supprData($params, function ($id, $tableClasses) {
            return array(
                'id' => $id,
                'data' => $tableClasses->getRecord($id)
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
        $form->setValueOptions('niveau', Niveau::getNiveaux());
        $params = array(
            'data' => array(
                'table' => 'classes',
                'type' => 'table',
                'alias' => 'Sbm\Db\Table\Classes'
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
        $classeId = StdLib::getParam('classeId', $args, - 1);
        if ($classeId == - 1) {
            $this->flashMessenger()->addErrorMessage('Action interdite.');
            return $this->redirect()->toRoute('sbmgestion/transport', array(
                'action' => 'classe-liste',
                'page' => $currentPage
            ));
        }
        return new ViewModel(array(
            'data' => $this->getServiceLocator()
                ->get('Sbm\Db\Eleve\Liste')
                ->byClasse($this->getFromSession('millesime'), $classeId, array(
                'nom',
                'prenom'
            )),
            'classe' => $this->getServiceLocator()
                ->get('Sbm\Db\Table\Classes')
                ->getRecord($classeId),
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
        $args = $this->initListe('communes');
        if ($args instanceof Response)
            return $args;
        
        return new ViewModel(array(
            'paginator' => $this->getServiceLocator()
                ->get('Sbm\Db\Table\Communes')
                ->paginator($args['where']),
            't_nb_inscrits' => $this->getServiceLocator()
                ->get('Sbm\Db\Eleve\Effectif')
                ->byCommune(),
            'page' => $this->params('page', 1),
            'nb_pagination' => $this->getNbPagination('nb_communes', 20),
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
        
        $r = $this->editData($params);
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
        
        $r = $this->supprData($params, function ($id, $tableCommunes) {
            return array(
                'id' => $id,
                'data' => $tableCommunes->getRecord($id)
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
        $r = $this->addData($params);
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
        $communeId = StdLib::getParam('communeId', $args, - 1);
        if ($communeId == - 1) {
            $this->flashMessenger()->addErrorMessage('Action interdite.');
            return $this->redirect()->toRoute('sbmgestion/transport', array(
                'action' => 'commune-liste',
                'page' => $currentPage
            ));
        }
        return new ViewModel(array(
            'data' => $this->getServiceLocator()
                ->get('Sbm\Db\Eleve\Liste')
                ->byCommune($this->getFromSession('millesime'), $communeId, array(
                'nom',
                'prenom'
            )),
            'commune' => $this->getServiceLocator()
                ->get('Sbm\Db\Table\Communes')
                ->getRecord($communeId),
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
            ->setParam('orderBy', array(
            'departement',
            'nom'
        ))
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
        $args = $this->initListe('etablissements');
        if ($args instanceof Response)
            return $args;
        
        return new ViewModel(array(
            'paginator' => $this->getServiceLocator()
                ->get('Sbm\Db\Vue\Etablissements')
                ->paginator($args['where']),
            't_nb_inscrits' => $this->getServiceLocator()
                ->get('Sbm\Db\Eleve\Effectif')
                ->byEtablissement(),
            'page' => $this->params('page', 1),
            'nb_pagination' => $this->getNbPagination('nb_etablissements', 10),
            'criteres_form' => $args['form']
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
            ->setValueOptions('rattacheA', $this->getServiceLocator()
            ->get('Sbm\Db\Select\EtablissementsVisibles'))
            ->setValueOptions('communeId', $this->getServiceLocator()
            ->get('Sbm\Db\Select\Communes')
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
        
        $r = $this->editData($params);
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
        $vueEtablissement = $this->getServiceLocator()->get('Sbm\Db\Vue\Etablissements');
        $r = $this->supprData($params, function ($id, $tableEtablissements) use($vueEtablissement) {
            return array(
                'id' => $id,
                'data' => $vueEtablissement->getRecord($id)
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
            ->setValueOptions('rattacheA', $this->getServiceLocator()
            ->get('Sbm\Db\Select\EtablissementsVisibles'))
            ->setValueOptions('communeId', $this->getServiceLocator()
            ->get('Sbm\Db\Select\Communes')
            ->desservies());
        $params = array(
            'data' => array(
                'table' => 'etablissements',
                'type' => 'table',
                'alias' => 'Sbm\Db\Table\Etablissements'
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
                return $this->redirect()->toRoute('sbmgestion/transport', array(
                    'action' => 'etablissement-liste',
                    'page' => $currentPage
                ));
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
        $etablissementId = StdLib::getParam('etablissementId', $args, - 1);
        if ($etablissementId == - 1) {
            $this->flashMessenger()->addErrorMessage('Action interdite.');
            return $this->redirect()->toRoute('sbmgestion/transport', array(
                'action' => 'etablissement-liste',
                'page' => $currentPage
            ));
        }
        
        return new ViewModel(array(
            'data' => $this->getServiceLocator()
                ->get('Sbm\Db\Eleve\Liste')
                ->byEtablissement($this->getFromSession('millesime'), $etablissementId, array(
                'nom',
                'prenom'
            )),
            // 'paginator' => $table_eleves->paginator(),
            'etablissement' => $this->getServiceLocator()
                ->get('Sbm\Db\Vue\Etablissements')
                ->getRecord($etablissementId),
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
            ->setParam('orderBy', array(
            'commune',
            'nom'
        ))
            ->renderPdf();
        
        $this->flashMessenger()->addSuccessMessage("Création d'un pdf.");
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
        if ($etablissementId == - 1) {
            $this->flashMessenger()->addErrorMessage('Action interdite.');
            $cancel = true;
        }
        if ($cancel) {
            return $this->redirect()->toRoute('sbmgestion/transport', array(
                'action' => 'etablissement-liste',
                'page' => $currentPage
            ));
        }
        $table = $this->getServiceLocator()->get('Sbm\Db\Vue\EtablissementsServices');
        return new ViewModel(array(
            'etablissement' => $this->getServiceLocator()
                ->get('Sbm\Db\Vue\Etablissements')
                ->getRecord($etablissementId),
            'data' => $table->fetchAll(array(
                'etablissementId' => $etablissementId
            )),
            't_nb_inscrits' => $this->getServiceLocator()
                ->get('Sbm\Db\Eleve\Effectif')
                ->byServiceGivenEtablissement($etablissementId),
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
        if ($serviceId == - 1) {
            $this->flashMessenger()->addErrorMessage('Action interdite.');
            $cancel = true;
        }
        if ($cancel) {
            return $this->redirect()->toRoute('sbmgestion/transport', array(
                'action' => 'service-liste',
                'page' => $currentPage
            ));
        }
        $table = $this->getServiceLocator()->get('Sbm\Db\Vue\EtablissementsServices');
        return new ViewModel(array(
            'service' => $this->getServiceLocator()
                ->get('Sbm\Db\Vue\Services')
                ->getRecord($serviceId),
            'data' => $table->fetchAll(array(
                'serviceId' => $serviceId
            )),
            't_nb_inscrits' => $this->getServiceLocator()
                ->get('Sbm\Db\Eleve\Effectif')
                ->byEtablissementGivenService($serviceId),
            'page' => $currentPage,
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
        $form = new FormEtablissementService(empty($serviceId) ? 'service' : 'etablissement');
        if (empty($serviceId)) {
            $service = null;
            $etablissement = $this->getServiceLocator()
                ->get('Sbm\Db\Vue\Etablissements')
                ->getRecord($etablissementId);
            $form->setValueOptions('serviceId', $this->getServiceLocator()
                ->get('Sbm\Db\Select\Services'));
        } else {
            $etablissement = null;
            $service = $this->getServiceLocator()
                ->get('Sbm\Db\Vue\Services')
                ->getRecord($serviceId);
            $form->setValueOptions('etablissementId', $this->getServiceLocator()
                ->get('Sbm\Db\Select\EtablissementsVisibles'));
        }
        $table = $this->getServiceLocator()->get('Sbm\Db\Table\EtablissementsServices');
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
        $table = $this->getServiceLocator()->get('Sbm\Db\Table\EtablissementsServices');
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
            'etablissement' => $this->getServiceLocator()
                ->get('Sbm\Db\Vue\Etablissements')
                ->getRecord($etablissementId),
            'service' => $this->getServiceLocator()
                ->get('Sbm\Db\Vue\Services')
                ->getRecord($serviceId),
            'form' => $form->prepare()
        ));
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
        $args = $this->initListe('services', function ($sm, $form) {
            $form->setValueOptions('transporteurId', $sm->get('Sbm\Db\Select\Transporteurs'));
        }, array(
            'transporteurId'
        ));
        if ($args instanceof Response)
            return $args;
        
        return new ViewModel(array(
            'paginator' => $this->getServiceLocator()
                ->get('Sbm\Db\Vue\Services')
                ->paginator($args['where']),
            'page' => $this->params('page', 1),
            'nb_pagination' => $this->getNbPagination('nb_services', 15),
            'criteres_form' => $args['form'],
            't_nb_inscrits' => $this->getServiceLocator()
                ->get('Sbm\Db\Eleve\Effectif')
                ->byService()
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
        $form->modifFormForEdit()->setValueOptions('transporteurId', $this->getServiceLocator()
            ->get('Sbm\Db\Select\Transporteurs'));
        $params = array(
            'data' => array(
                'table' => 'services',
                'type' => 'table',
                'alias' => 'Sbm\Db\Table\Services',
                'id' => 'serviceId'
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
        $vueServices = $this->getServiceLocator()->get('Sbm\Db\Vue\Services');
        $r = $this->supprData($params, function ($id, $tableServices) use($vueServices) {
            return array(
                'id' => $id,
                'data' => $vueServices->getRecord($id)
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
        $form->setValueOptions('transporteurId', $this->getServiceLocator()
            ->get('Sbm\Db\Select\Transporteurs'));
        $params = array(
            'data' => array(
                'table' => 'services',
                'type' => 'table',
                'alias' => 'Sbm\Db\Table\Services'
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
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function serviceGroupAction()
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
        $serviceId = StdLib::getParam('serviceId', $args, - 1);
        if ($serviceId == - 1) {
            $this->flashMessenger()->addErrorMessage('Action interdite.');
            return $this->redirect()->toRoute('sbmgestion/transport', array(
                'action' => 'service-liste',
                'page' => $currentPage
            ));
        }
        
        return new ViewModel(array(
            'data' => $this->getServiceLocator()
                ->get('Sbm\Db\Eleve\Liste')
                ->byService($this->getFromSession('millesime'), $serviceId, array(
                'nom',
                'prenom'
            )),
            // 'paginator' => $table_eleves->paginator(),
            'service' => $this->getServiceLocator()
                ->get('Sbm\Db\Vue\Services')
                ->getRecord($serviceId),
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
            ->setParam('orderBy', array(
            'serviceId'
        ))
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
        $args = $this->initListe('stations', function ($sm, $form) {
            $form->setValueOptions('communeId', $sm->get('Sbm\Db\Select\Communes')
                ->desservies());
        }, array(
            'communeId'
        ));
        if ($args instanceof Response)
            return $args;
        
        return new ViewModel(array(
            'paginator' => $this->getServiceLocator()
                ->get('Sbm\Db\Vue\Stations')
                ->paginator($args['where']),
            't_nb_inscrits' => $this->getServiceLocator()
                ->get('Sbm\Db\Eleve\Effectif')
                ->byStation(),
            'page' => $this->params('page', 1),
            'nb_pagination' => $this->getNbPagination('nb_stations', 10),
            'criteres_form' => $args['form']
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
            'data' => $this->getServiceLocator()
                ->get('Sbm\Db\Circuit\Liste')
                ->stationsNonDesservies(),
            't_nb_inscrits' => $this->getServiceLocator()
                ->get('Sbm\Db\Eleve\Effectif')
                ->byStation(),
            'page' => $currentPage
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
        $form = new FormStation();
        $form->setValueOptions('communeId', $this->getServiceLocator()
            ->get('Sbm\Db\Select\Communes')
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
        
        $r = $this->editData($params);
        if ($r instanceof Response) {
            return $r;
        } else {
            switch ($r->getStatus()) {
                case 'error':
                case 'warning':
                case 'success':
                    return $this->redirect()->toRoute('sbmgestion/transport', array(
                        'action' => StdLib::getParam('origine', $r->getPost()),
                        'page' => $currentPage
                    ));
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
     * @todo : Vérifier qu'il n'y a pas d'élève inscrit avant de supprimer la fiche
     *      
     * @return \Zend\View\Model\ViewModel
     */
    public function stationSupprAction()
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
                'alias' => 'Sbm\Db\Table\Stations',
                'id' => 'stationId'
            ),
            'form' => $form
        );
        $vueStations = $this->getServiceLocator()->get('Sbm\Db\Vue\Stations');
        $r = $this->supprData($params, function ($id, $tableStations) use($vueStations) {
            return array(
                'id' => $id,
                'data' => $vueStations->getRecord($id)
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
                        'action' => StdLib::getParam('origine', $r->getPost()),
                        'page' => $currentPage
                    ));
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
                        'data' => StdLib::getParam('data', $r->getResult()),
                        'stationId' => StdLib::getParam('id', $r->getResult())
                    ));
                    break;
            }
        }
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
        $form = new FormStation();
        $form->setValueOptions('communeId', $this->getServiceLocator()
            ->get('Sbm\Db\Select\Communes')
            ->desservies());
        $params = array(
            'data' => array(
                'table' => 'stations',
                'type' => 'table',
                'alias' => 'Sbm\Db\Table\Stations'
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
                return $this->redirect()->toRoute('sbmgestion/transport', array(
                    'action' => 'station-liste',
                    'page' => $currentPage
                ));
                break;
            default:
                return new ViewModel(array(
                    'form' => $form->prepare(),
                    'page' => $currentPage,
                    'stationId' => null
                ));
                break;
        }
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
            'data' => $this->getServiceLocator()
                ->get('Sbm\Db\Eleve\Liste')
                ->byStation($this->getFromSession('millesime'), $stationId, array(
                'nom',
                'prenom'
            )),
            // 'paginator' => $table_eleves->paginator(),
            'station' => $this->getServiceLocator()
                ->get('Sbm\Db\Vue\Stations')
                ->getRecord($stationId),
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
            'data' => $this->getServiceLocator()
                ->get('Sbm\Db\Circuit\Liste')
                ->byStation($stationId),
            // 'paginator' => $table_eleves->paginator(),
            't_nb_inscrits' => $this->getServiceLocator()
                ->get('Sbm\Db\Eleve\Effectif')
                ->byServiceGivenStation($stationId),
            'station' => $this->getServiceLocator()
                ->get('Sbm\Db\Vue\Stations')
                ->getRecord($stationId),
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
            ->setParam('orderBy', array(
            'commune',
            'nom'
        ))
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
     * @return ViewModel
     */
    public function transporteurListeAction()
    {
        $args = $this->initListe('transporteurs');
        if ($args instanceof Response)
            return $args;
        
        return new ViewModel(array(
            'paginator' => $this->getServiceLocator()
                ->get('Sbm\Db\Vue\Transporteurs')
                ->paginator($args['where']),
            't_nb_inscrits' => $this->getServiceLocator()
                ->get('Sbm\Db\Eleve\Effectif')
                ->bytransporteur(),
            'page' => $this->params('page', 1),
            'nb_pagination' => $this->getNbPagination('nb_transporteurs', 15),
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
        $form->setValueOptions('communeId', $this->getServiceLocator()
            ->get('Sbm\Db\Select\Communes')
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
        
        $r = $this->editData($params);
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
        $vuetransporteurs = $this->getServiceLocator()->get('Sbm\Db\Vue\Transporteurs');
        $r = $this->supprData($params, function ($id, $tabletransporteurs) use($vuetransporteurs) {
            return array(
                'id' => $id,
                'data' => $vuetransporteurs->getRecord($id)
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
        $form->setValueOptions('communeId', $this->getServiceLocator()
            ->get('Sbm\Db\Select\Communes')
            ->visibles());
        $params = array(
            'data' => array(
                'table' => 'transporteurs',
                'type' => 'table',
                'alias' => 'Sbm\Db\Table\Transporteurs'
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
        $transporteurId = StdLib::getParam('transporteurId', $args, - 1);
        if ($transporteurId == - 1) {
            $this->flashMessenger()->addErrorMessage('Action interdite.');
            return $this->redirect()->toRoute('sbmgestion/transport', array(
                'action' => 'transporteur-liste',
                'page' => $currentPage
            ));
        }
        
        return new ViewModel(array(
            'data' => $this->getServiceLocator()
                ->get('Sbm\Db\Eleve\Liste')
                ->bytransporteur($this->getFromSession('millesime'), $transporteurId, array(
                'nom',
                'prenom'
            )),
            // 'paginator' => $table_eleves->paginator(),
            'transporteur' => $this->getServiceLocator()
                ->get('Sbm\Db\Table\Transporteurs')
                ->getRecord($transporteurId),
            'page' => $currentPage,
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
        $transporteurId = StdLib::getParam('transporteurId', $args, - 1);
        if ($transporteurId == - 1) {
            $this->flashMessenger()->addErrorMessage('Action interdite.');
            return $this->redirect()->toRoute('sbmgestion/transport', array(
                'action' => 'transporteur-liste',
                'page' => $currentPage
            ));
        }
        
        return new ViewModel(array(
            'data' => $this->getServiceLocator()
                ->get('Sbm\Db\Table\Services')
                ->fetchAll(array(
                'transporteurId' => $transporteurId
            ), array(
                'serviceId'
            )),
            // 'paginator' => $table_eleves->paginator(),
            't_nb_inscrits' => $this->getServiceLocator()
                ->get('Sbm\Db\Eleve\Effectif')
                ->transporteurByService($transporteurId),
            'transporteur' => $this->getServiceLocator()
                ->get('Sbm\Db\Table\Transporteurs')
                ->getRecord($transporteurId),
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
            ->setParam('orderBy', array(
            'nom'
        ))
            ->renderPdf();
        
        $this->flashMessenger()->addSuccessMessage("Création d'un pdf.");
    }
}