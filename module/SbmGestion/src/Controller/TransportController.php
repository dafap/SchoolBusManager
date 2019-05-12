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
 * @date 12 mai 2019
 * @version 2019-2.5.0
 */
namespace SbmGestion\Controller;

use SbmBase\Model\Session;
use SbmBase\Model\StdLib;
use SbmCartographie\GoogleMaps;
use SbmCartographie\Model\Point;
use SbmCartographie\Model\Projection;
use SbmCommun\Form;
use SbmCommun\Model\Strategy;
use SbmCommun\Model\Mvc\Controller\AbstractActionController;
use SbmCommun\Model\Mvc\Controller\EditResponse;
use SbmGestion\Form as FormGestion;
use SbmGestion\Model\StationSupprDoublon;
use SbmGestion\Model\Db\Filtre\Eleve\Filtre as FiltreEleve;
use Zend\Db\Sql\Where;
use Zend\Http\PhpEnvironment\Response;
use Zend\View\Model\ViewModel;

class TransportController extends AbstractActionController
{

    public function indexAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        }
        $this->redirectToOrigin()->reset(); // on s'assure que la pile des retours est
                                            // vide
        return new ViewModel();
    }

    /**
     * ================================ CIRCUITS ==============================
     */

    /**
     * Liste des circuits (avec pagination)
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function circuitListeAction()
    {
        $args = $this->initListe('circuits',
            function ($config, $form) {
                $form->setValueOptions('stationId',
                    $config['db_manager']->get('Sbm\Db\Select\Stations')
                        ->ouvertes());
                $form->setValueOptions('serviceId',
                    $config['db_manager']->get('Sbm\Db\Select\Services'));
            }, [
                'serviceId',
                'stationId'
            ]);
        if ($args instanceof Response) {
            return $args;
        }
        $millesime = Session::get('millesime');
        $as = $millesime . '-' . ($millesime + 1);
        $args['where']->equalTo('millesime', Session::get('millesime'));
        $auth = $this->authenticate->by('email');
        // on cherche si ce millesime a déjà des circuits enregistrés
        $tCircuits = $this->db_manager->get('Sbm\Db\Table\Circuits');
        $resultset = $tCircuits->fetchAll([
            'millesime' => $millesime
        ]);
        $circuitsVides = $resultset->count() == 0;
        // mise en place du calcul d'effectif
        $effectifCircuits = $this->db_manager->get('Sbm\Db\Eleve\EffectifCircuits');
        $effectifCircuits->init();
        return new ViewModel(
            [

                'paginator' => $this->db_manager->get('Sbm\Db\Vue\Circuits')->paginator(
                    $args['where']),
                'effectifCircuits' => $effectifCircuits,
                'page' => $this->params('page', 1),
                'count_per_page' => $this->getPaginatorCountPerPage('nb_circuits', 10),
                'criteres_form' => $args['form'],
                'admin' => $auth->getCategorieId() > 253,
                'as' => $as,
                'circuitsVides' => $circuitsVides
            ]);
    }

    /**
     * Modification d'une fiche de circuit (avec validation des données du formulaire)
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function circuitEditAction()
    {
        $currentPage = $this->params('page', 1);
        $form = $this->form_manager->get(Form\Circuit::class);
        $form->setValueOptions('serviceId',
            $this->db_manager->get('Sbm\Db\Select\Services'))
            ->setValueOptions('stationId',
            $this->db_manager->get('Sbm\Db\Select\Stations')
                ->ouvertes())
            ->setValueOptions('semaine',
            [ // pour le validator
                1 => '',
                2 => '',
                4 => ''
            ])
            ->setHoraires($this->db_manager->get('Sbm\Horaires'));
        $params = [
            'data' => [
                'table' => 'circuits',
                'type' => 'table',
                'alias' => 'Sbm\Db\Table\Circuits',
                'id' => 'circuitId'
            ],
            'form' => $form
        ];

        try {
            $r = $this->editData($this->db_manager, $params);
        } catch (\Zend\Db\Adapter\Exception\InvalidQueryException $e) {
            if (stripos($e->getMessage(), '23000 - 1062 - Duplicate entry') !== false) {
                $this->flashMessenger()->addWarningMessage(
                    'Impossible ! Cet arrêt est déjà sur ce circuit.');
                $r = new EditResponse('warning', []);
            } else {
                throw new \Zend\Db\Adapter\Exception\InvalidQueryException(
                    $e->getMessage(), $e->getCode(), $e->getPrevious());
            }
        }
        if ($r instanceof Response) {
            return $r;
        } else {
            switch ($r->getStatus()) {
                case 'error':
                case 'warning':
                case 'success':
                    return $this->redirect()->toRoute('sbmgestion/transport',
                        [
                            'action' => 'circuit-liste',
                            'page' => $currentPage
                        ]);
                    break;
                default:
                    $circuitId = $r->getResult();
                    $horaires = $this->db_manager->get('Sbm\Horaires')->getTableHoraires(
                        $circuitId);
                    $value_options = [];
                    $nature = [];
                    for ($i = 1; $i <= 3; $i ++) {
                        if (array_key_exists("horaire$i", $horaires)) {
                            $ligne = $horaires["horaire$i"];
                            $value_options[1 << ($i - 1)] = $nature[$i] = $ligne['nature'];
                        } else {
                            $nature[$i] = '';
                        }
                    }
                    $form->setValueOptions('semaine', $value_options);
                    return new ViewModel(
                        [

                            'form' => $form->prepare(),
                            'page' => $currentPage,
                            'circuitId' => $circuitId,
                            'nature' => $nature
                        ]);
                    break;
            }
        }
    }

    /**
     * Suppression d'une fiche avec confirmation
     *
     * @todo : Vérifier qu'il n'y a pas d'élève inscrit avant de supprimer la fiche
     * @return \Zend\View\Model\ViewModel
     */
    public function circuitSupprAction()
    {
        $currentPage = $this->params('page', 1);
        $form = new Form\ButtonForm([
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
                'alias' => 'Sbm\Db\Table\Circuits',
                'id' => 'circuitId'
            ],
            'form' => $form
        ];
        $vue_circuits = $this->db_manager->get('Sbm\Db\Vue\Circuits');
        $r = $this->supprData($this->db_manager, $params,
            function ($id, $tableCircuits) use ($vue_circuits) {
                return [
                    'id' => $id,
                    'data' => $vue_circuits->getRecord($id)
                ];
            });
        if ($r instanceof Response) {
            return $r;
        } else {
            switch ($r->getStatus()) {
                case 'error':
                case 'warning':
                case 'success':
                    return $this->redirect()->toRoute('sbmgestion/transport',
                        [
                            'action' => 'circuit-liste',
                            'page' => $currentPage
                        ]);
                    break;
                default:
                    return new ViewModel(
                        [

                            'form' => $form->prepare(),
                            'page' => $currentPage,
                            'data' => StdLib::getParam('data', $r->getResult()),
                            'circuitId' => StdLib::getParam('id', $r->getResult())
                        ]);
                    break;
            }
        }
    }

    /**
     * Ajout d'une nouvelle fiche de circuit (avec validation des données du formulaire)
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function circuitAjoutAction()
    {
        $currentPage = $this->params('page', 1);
        // $horaires = $this->db_manager->get('Sbm\Horaires');
        $form = $this->form_manager->get(Form\Circuit::class);
        $form->setValueOptions('serviceId',
            $this->db_manager->get('Sbm\Db\Select\Services'))
            ->setValueOptions('stationId',
            $this->db_manager->get('Sbm\Db\Select\Stations')
                ->ouvertes())
            ->setValueOptions('semaine',
            [ // pour le validator
                1 => '',
                2 => '',
                4 => ''
            ])
            ->setHoraires($this->db_manager->get('Sbm\Horaires'));
        ;
        $params = [
            'data' => [
                'table' => 'circuits',
                'type' => 'table',
                'alias' => 'Sbm\Db\Table\Circuits'
            ],
            'form' => $form
        ];
        try {
            $r = $this->addData($this->db_manager, $params);
        } catch (\Zend\Db\Adapter\Exception\InvalidQueryException $e) {
            if (stripos($e->getMessage(), '23000 - 1062 - Duplicate entry') !== false) {
                $this->flashMessenger()->addWarningMessage(
                    'Impossible ! Cet arrêt est déjà sur ce circuit.');
                $r = 'warning';
            } else {
                throw new \Zend\Db\Adapter\Exception\InvalidQueryException(
                    $e->getMessage(), $e->getCode(), $e->getPrevious());
            }
        }
        switch ($r) {
            case $r instanceof Response:
                return $r;
                break;
            case 'error':
            case 'warning':
            case 'success':
                $value_options = [];
                $form->setValueOptions('semaine', $value_options);
                return $this->redirect()->toRoute('sbmgestion/transport',
                    [
                        'action' => 'circuit-liste',
                        'page' => $currentPage
                    ]);
                break;
            default:
                return new ViewModel(
                    [

                        'form' => $form->prepare(),
                        'page' => $currentPage,
                        'circuitId' => null,
                        'horaires' => $this->db_manager->get('Sbm\Horaires')
                    ]);
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
        $args = $prg ?: [];
        if (array_key_exists('cancel', $args)) {
            return $this->redirect()->toRoute('sbmgestion/transport',
                [
                    'action' => 'circuit-liste',
                    'page' => $this->params('page', 1)
                ]);
        }
        $form = new Form\ButtonForm([],
            [
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
                $this->flashMessenger()->addSuccessMessage(
                    'Toutes les arrêts sont désélectionnées.');
                return $this->redirect()->toRoute('sbmgestion/transport',
                    [
                        'action' => 'circuit-liste',
                        'page' => $this->params('page', 1)
                    ]);
            }
        }
        $where = new Where();
        $where->equalTo('selection', 1);
        $view = new ViewModel(
            [

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
        $args = $prg ?: [];
        if (array_key_exists('cancel', $args)) {
            return $this->redirect()->toRoute('sbmgestion/transport',
                [
                    'action' => 'circuit-liste',
                    'page' => $currentPage
                ]);
        }
        $form = new \SbmGestion\Form\ModifHoraires();
        if (array_key_exists('submit', $args)) {
            $form->setData($args);
            if ($form->isValid()) {
                $modifHoraires = new \SbmGestion\Model\ModifHoraires($form->getData(),
                    $this->db_manager->get('Sbm\Db\Table\Circuits'));
                if ($modifHoraires->run()) {
                    $this->flashMessenger()->addSuccessMessage(
                        'Les horaires ont été modifiés.');
                } else {
                    $this->flashMessenger()->addErrorMessage(
                        'Une erreur est survenue lors de la modification des horaires.');
                }
                return $this->redirect()->toRoute('sbmgestion/transport',
                    [
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
     * Reçoit la paramètre circuitId en post Renvoie la liste des élèves inscrits pour un
     * circuit donné
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
            $args = Session::get('post', [], $this->getSessionNamespace());
        } else {
            $args = $prg;
            Session::set('post', $args, $this->getSessionNamespace());
        }
        $circuitId = StdLib::getParam('circuitId', $args, - 1);
        if ($circuitId == - 1) {
            $this->flashMessenger()->addErrorMessage('Action interdite.');
            return $this->redirect()->toRoute('sbmgestion/transport',
                [
                    'action' => 'circuit-liste',
                    'page' => $currentPage
                ]);
        }
        $circuit = $this->db_manager->get('Sbm\Db\Vue\Circuits')->getRecord($circuitId);
        return new ViewModel(
            [

                'data' => $this->db_manager->get('Sbm\Db\Eleve\Liste')->queryGroup(
                    Session::get('millesime'),
                    FiltreEleve::byCircuit($circuit->lotId, $circuit->stationId, false),
                    [
                        'nom',
                        'prenom'
                    ]),
                'circuit' => $circuit,
                'page' => $currentPage,
                'pageRetour' => $this->params('id', 1),
                'circuitId' => $circuitId
            ]);
    }

    /**
     * Lors de la création d'une nouvelle année scolaire, la table des circuits pour ce
     * millesime est vide. Cette action reprend les circuits de la dernière année connue.
     */
    public function circuitDupliquerAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        }
        if ($prg) {
            $origine = StdLib::getParam('origine', $prg, false);
            if ($origine) {
                $this->redirectToOrigin()->setBack($origine);
            }
            $tCircuits = $this->db_manager->get('Sbm\Db\Table\Circuits');
            // millesime en cours pour cette session
            $millesime = Session::get('millesime');
            // on cherche si ce millesime a déjà des circuits enregistrés
            $resultset = $tCircuits->fetchAll([
                'millesime' => $millesime
            ]);
            if ($resultset->count()) {
                $this->flashMessenger()->addErrorMessage(
                    'Impossible de générer les circuits. Il existe déjà des circuits pour cette année scolaire.');
                try {
                    return $this->redirectToOrigin()->back();
                } catch (\SbmCommun\Model\Mvc\Controller\Plugin\Exception\ExceptionInterface $e) {
                    return $this->redirect()->toRoute('sbmgestion/transport',
                        [
                            'action' => 'circuit-liste',
                            'page' => 1
                        ]);
                }
            }
            // on cherche le dernier millesime dans les circuits
            $dernierMillesimeCircuits = $tCircuits->getDernierMillesime();

            $where = new Where();
            $where->equalTo('millesime', $dernierMillesimeCircuits);
            $resultset = $tCircuits->fetchAll($where);
            foreach ($resultset as $row) {
                $row->circuitId = null;
                $row->millesime = $millesime;
                $tCircuits->saveRecord($row);
            }
            $this->flashMessenger()->addSuccessMessage(
                'Les circuits de cette année scolaire viennent d\'être générés.');
        }
        try {
            return $this->redirectToOrigin()->back();
        } catch (\SbmCommun\Model\Mvc\Controller\Plugin\Exception\ExceptionInterface $e) {
            return $this->redirect()->toRoute('sbmgestion/transport',
                [
                    'action' => 'circuit-liste',
                    'page' => 1
                ]);
        }
    }

    /**
     * Supprime les circuits de l'année scolaire en session
     */
    public function circuitViderAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        }
        if ($prg) {
            $origine = StdLib::getParam('origine', $prg, false);
            if ($origine) {
                var_dump($origine);
                $this->redirectToOrigin()->setBack($origine);
            }
            $millesime = Session::get('millesime');
            $form = new Form\ButtonForm([
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
            $confirme = StdLib::getParam('supproui', $prg, false);
            $cancel = StdLib::getParam('supprnon', $prg, false);
            if (! $cancel && ! $confirme) {
                return new ViewModel(
                    [
                        'form' => $form->prepare(),
                        'as' => $millesime . '-' . ($millesime + 1)
                    ]);
            } elseif ($confirme) {
                $form->setData($prg);
                if ($form->isValid()) {
                    $tCircuits = $this->db_manager->get('Sbm\Db\Table\Circuits');
                    $tCircuits->viderMillesime($millesime);
                }
            }
        }
        try {
            return $this->redirectToOrigin()->back();
        } catch (\SbmCommun\Model\Mvc\Controller\Plugin\Exception\ExceptionInterface $e) {
            return $this->redirect()->toRoute('sbmgestion/transport',
                [
                    'action' => 'circuit-liste'
                ]);
        }
    }

    /**
     * envoie un evenement contenant les paramètres de création d'un document pdf (le
     * listener SbmPdf\Listener\PdfListener lancera la création du pdf) Il n'y a pas de
     * vue associée à cette action puisque la response html est créée par \TCPDF
     */
    public function circuitPdfAction()
    {
        $criteresObject = [
            'SbmCommun\Model\Db\ObjectData\Criteres',
            [
                'strict' => [
                    'serviceId',
                    'stationId'
                ]
            ]
        ];
        $criteresForm = [
            'SbmCommun\Form\CriteresForm',
            'circuits'
        ];
        $documentId = null;
        $retour = [
            'route' => 'sbmgestion/transport',
            'action' => 'circuit-liste'
        ];
        return $this->documentPdf($criteresObject, $criteresForm, $documentId, $retour,
            [
                'effectifClassName' => 'Sbm\Db\Eleve\EffectifCircuits'
            ]);
    }

    /**
     * lance la création d'une liste d'élève avec comme filtre le circuitId reçu en post
     */
    public function circuitGroupPdfAction()
    {
        $db_manager = $this->db_manager;
        $criteresObject = [
            'SbmCommun\Model\Db\ObjectData\Criteres',
            null,
            function ($where, $args) use ($db_manager) {
                $circuitId = StdLib::getParam('circuitId', $args, - 1);
                $ocircuit = $db_manager->get('Sbm\Db\Table\Circuits')->getRecord(
                    $circuitId);
                $serviceId = $ocircuit->lotId;
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
        ];
        $criteresForm = 'SbmCommun\Form\CriteresForm';
        $documentId = null;
        $retour = [
            'route' => 'sbmgestion/transport',
            'action' => 'circuit-group'
        ];
        return $this->documentPdf($criteresObject, $criteresForm, $documentId, $retour);
    }

    public function circuitGroupSelectionAction()
    {
        $query = 'queryGroup';
        $filtre = 'byCircuit';
        $idFields = [
            'serviceId',
            'stationId'
        ];
        $hiddens = [
            'serviceId',
            'stationId'
        ];
        $retour = [
            'route' => 'sbmgestion/transport',
            'action' => 'circuit-group'
        ];
        $result = $this->markSelectionEleves($query, $filtre, $idFields, $retour, $hiddens);
        if ($result instanceof ViewModel) {
            $result->setTemplate('sbm-gestion/transport/group-selection.phtml');
        }
        return $result;
    }

    /**
     * ================================ CLASSES ===============================
     */

    /**
     * Liste des classes (avec pagination)
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function classeListeAction()
    {
        $args = $this->initListe('classes');
        if ($args instanceof Response)
            return $args;
        $effectifClasses = $this->db_manager->get('Sbm\Db\Eleve\EffectifClasses');
        $effectifClasses->init();
        return new ViewModel(
            [

                'paginator' => $this->db_manager->get('Sbm\Db\Vue\Classes')->paginator(
                    $args['where'], [
                        'niveau',
                        'rang'
                    ]),
                'effectifClasses' => $effectifClasses,
                'page' => $this->params('page', 1),
                'count_per_page' => $this->getPaginatorCountPerPage('nb_classes', 15),
                'criteres_form' => $args['form']
            ]);
    }

    /**
     * Modification d'une fiche de classe (la validation porte sur un champ csrf)
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function classeEditAction()
    {
        $currentPage = $this->params('page', 1);
        $form = $this->form_manager->get(Form\Classe::class);
        $form->setValueOptions('niveau', Strategy\Niveau::getNiveaux())->setValueOptions(
            'suivantId', $this->db_manager->get('Sbm\Db\Select\Classes')
                ->tout());
        $params = [
            'data' => [
                'table' => 'classes',
                'type' => 'table',
                'alias' => 'Sbm\Db\Table\Classes',
                'id' => 'classeId'
            ],
            'form' => $form
        ];

        $r = $this->editData($this->db_manager, $params);
        if ($r instanceof Response) {
            return $r;
        } else {
            switch ($r->getStatus()) {
                case 'error':
                case 'warning':
                case 'success':
                    return $this->redirect()->toRoute('sbmgestion/transport',
                        [
                            'action' => 'classe-liste',
                            'page' => $currentPage
                        ]);
                    break;
                default:
                    return new ViewModel(
                        [

                            'form' => $form->prepare(),
                            'page' => $currentPage,
                            'classeId' => $r->getResult()
                        ]);
                    break;
            }
        }
    }

    /**
     * Suppression d'une fiche avec confirmation
     *
     * @todo : Vérifier qu'il n'y a pas d'élève inscrit avant de supprimer la fiche
     * @return \Zend\View\Model\ViewModel
     */
    public function classeSupprAction()
    {
        $currentPage = $this->params('page', 1);
        $form = new Form\ButtonForm([
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
                'alias' => 'Sbm\Db\Table\Classes',
                'id' => 'classeId'
            ],
            'form' => $form
        ];

        try {
            $r = $this->supprData($this->db_manager, $params,
                function ($id, $tableClasses) {
                    return [
                        'id' => $id,
                        'data' => $tableClasses->getRecord($id)
                    ];
                });
        } catch (\Zend\Db\Adapter\Exception\InvalidQueryException $e) {
            $this->flashMessenger()->addWarningMessage(
                'Impossible de supprimer cette classe parce que certains élèves y sont inscrits.');
            return $this->redirect()->toRoute('sbmgestion/transport',
                [
                    'action' => 'classe-liste',
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
                    return $this->redirect()->toRoute('sbmgestion/transport',
                        [
                            'action' => 'classe-liste',
                            'page' => $currentPage
                        ]);
                    break;
                default:
                    return new ViewModel(
                        [

                            'form' => $form->prepare(),
                            'page' => $currentPage,
                            'data' => StdLib::getParam('data', $r->getResult()),
                            'classeId' => StdLib::getParam('id', $r->getResult())
                        ]);
                    break;
            }
        }
    }

    /**
     * Ajout d'une nouvelle fiche de classe (la validation porte sur un champ csrf)
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function classeAjoutAction()
    {
        $currentPage = $this->params('page', 1);
        $form = $this->form_manager->get(Form\Classe::class);
        $form->setValueOptions('niveau', Strategy\Niveau::getNiveaux())->setValueOptions(
            'suivantId', $this->db_manager->get('Sbm\Db\Select\Classes')
                ->tout());
        $params = [
            'data' => [
                'table' => 'classes',
                'type' => 'table',
                'alias' => 'Sbm\Db\Table\Classes'
            ],
            'form' => $form
        ];
        $r = $this->addData($this->db_manager, $params);
        switch ($r) {
            case $r instanceof Response:
                return $r;
                break;
            case 'error':
            case 'warning':
            case 'success':
                return $this->redirect()->toRoute('sbmgestion/transport',
                    [
                        'action' => 'classe-liste',
                        'page' => $currentPage
                    ]);
                break;
            default:
                return new ViewModel(
                    [

                        'form' => $form->prepare(),
                        'page' => $currentPage,
                        'classeId' => null
                    ]);
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
            $args = Session::get('post', [], $this->getSessionNamespace());
        } else {
            $args = $prg;
            Session::set('post', $args, $this->getSessionNamespace());
        }
        if ($pageRetour == - 1) {
            $pageRetour = Session::get('pageRetour', 1, $this->getSessionNamespace());
        } else {
            Session::set('pageRetour', $pageRetour, $this->getSessionNamespace());
        }
        $classeId = StdLib::getParam('classeId', $args, - 1);
        if ($classeId == - 1) {
            $this->flashMessenger()->addErrorMessage('Action interdite.');
            return $this->redirect()->toRoute('sbmgestion/transport',
                [
                    'action' => 'classe-liste',
                    'page' => $pageRetour
                ]);
        }
        return new ViewModel(
            [

                'paginator' => $this->db_manager->get('Sbm\Db\Eleve\Liste')->paginatorGroup(
                    Session::get('millesime'), FiltreEleve::byClasse($classeId),
                    [
                        'nom',
                        'prenom'
                    ]),
                'count_per_page' => $this->getPaginatorCountPerPage('nb_eleves', 15),
                'classe' => $this->db_manager->get('Sbm\Db\Table\Classes')->getRecord(
                    $classeId),
                'page' => $currentPage,
                'pageRetour' => $pageRetour,
                'classeId' => $classeId
            ]);
    }

    /**
     * envoie un evenement contenant les paramètres de création d'un document pdf (le
     * listener SbmPdf\Listener\PdfListener lancera la création du pdf) Il n'y a pas de
     * vue associée à cette action puisque la response html est créée par \TCPDF
     */
    public function classePdfAction()
    {
        $criteresObject = 'SbmCommun\Model\Db\ObjectData\Criteres';
        $criteresForm = [
            'SbmCommun\Form\CriteresForm',
            'classes'
        ];
        $documentId = null;
        $retour = [
            'route' => 'sbmgestion/transport',
            'action' => 'classe-liste'
        ];
        return $this->documentPdf($criteresObject, $criteresForm, $documentId, $retour,
            [
                'effectifClassName' => 'Sbm\Db\Eleve\EffectifClasse'
            ]);
    }

    /**
     * lance la création d'une liste d'élève avec comme filtre le classeId reçu en post
     */
    public function classeGroupPdfAction()
    {
        $criteresObject = [
            'SbmCommun\Model\Db\ObjectData\Criteres',
            null,
            function ($where, $args) {
                $classeId = StdLib::getParam('classeId', $args, - 1);
                $where = new Where();
                $where->equalTo('classeId', $classeId);
                return $where;
            }
        ];
        $criteresForm = 'SbmCommun\Form\CriteresForm';
        $documentId = null;
        $retour = [
            'route' => 'sbmgestion/transport',
            'action' => 'classe-group'
        ];
        return $this->documentPdf($criteresObject, $criteresForm, $documentId, $retour);
    }

    public function classeGroupSelectionAction()
    {
        $query = 'queryGroup';
        $filtre = 'byClasse';
        $idField = 'classeId';
        $retour = [
            'route' => 'sbmgestion/transport',
            'action' => 'classe-group'
        ];
        $result = $this->markSelectionEleves($query, $filtre, $idField, $retour);
        if ($result instanceof ViewModel) {
            $result->setTemplate('sbm-gestion/transport/group-selection.phtml');
        }
        return $result;
    }

    /**
     * ================================ COMMUNES ==============================
     */

    /**
     * Liste des communes (avec pagination)
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function communeListeAction()
    {
        // die(var_dump(Session::get('post', 'vide', $this->getSessionNamespace())));
        $args = $this->initListe('communes');

        if ($args instanceof Response)
            return $args;
        $effectifCommunes = $this->db_manager->get('Sbm\Db\Eleve\EffectifCommunes');
        $effectifCommunes->init();
        return new ViewModel(
            [

                'paginator' => $this->db_manager->get('Sbm\Db\Table\Communes')->paginator(
                    $args['where']),
                'effectifCommunes' => $effectifCommunes,
                'page' => $this->params('page', 1),
                'count_per_page' => $this->getPaginatorCountPerPage('nb_communes', 20),
                'criteres_form' => $args['form']
            ]);
    }

    /**
     * Modification d'une fiche de commune (la validation porte sur un champ csrf)
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function communeEditAction()
    {
        $currentPage = $this->params('page', 1);
        $form = $this->form_manager->get(Form\Commune::class);
        $form->modifFormForEdit();
        $params = [
            'data' => [
                'table' => 'communes',
                'type' => 'table',
                'alias' => 'Sbm\Db\Table\Communes',
                'id' => 'communeId'
            ],
            'form' => $form
        ];

        $r = $this->editData($this->db_manager, $params);
        if ($r instanceof Response) {
            return $r;
        } else {
            switch ($r->getStatus()) {
                case 'error':
                case 'warning':
                case 'success':
                    return $this->redirect()->toRoute('sbmgestion/transport',
                        [
                            'action' => 'commune-liste',
                            'page' => $currentPage
                        ]);
                    break;
                default:
                    return new ViewModel(
                        [

                            'form' => $form->prepare(),
                            'page' => $currentPage,
                            'communeId' => $r->getResult()
                        ]);
                    break;
            }
        }
    }

    /**
     * Suppression d'une fiche avec confirmation
     *
     * @todo : Vérifier qu'il n'y a pas d'élève inscrit avant de supprimer la fiche
     * @return \Zend\View\Model\ViewModel
     */
    public function communeSupprAction()
    {
        $currentPage = $this->params('page', 1);
        $form = new Form\ButtonForm([
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
                'alias' => 'Sbm\Db\Table\Communes',
                'id' => 'communeId'
            ],
            'form' => $form
        ];

        try {
            $r = $this->supprData($this->db_manager, $params,
                function ($id, $tableCommunes) {
                    return [
                        'id' => $id,
                        'data' => $tableCommunes->getRecord($id)
                    ];
                });
        } catch (\Zend\Db\Adapter\Exception\InvalidQueryException $e) {
            $this->flashMessenger()->addWarningMessage(
                'Impossible de supprimer cette commune car un enregistrement l\'utilise.');
            return $this->redirect()->toRoute('sbmgestion/transport',
                [
                    'action' => 'commune-liste',
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
                    return $this->redirect()->toRoute('sbmgestion/transport',
                        [
                            'action' => 'commune-liste',
                            'page' => $currentPage
                        ]);
                    break;
                default:
                    return new ViewModel(
                        [

                            'form' => $form->prepare(),
                            'page' => $currentPage,
                            'data' => StdLib::getParam('data', $r->getResult()),
                            'communeId' => StdLib::getParam('id', $r->getResult())
                        ]);
                    break;
            }
        }
    }

    /**
     * Ajout d'une nouvelle fiche de commune (la validation porte sur un champ csrf)
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function communeAjoutAction()
    {
        $currentPage = $this->params('page', 1);
        $form = $this->form_manager->get(Form\Commune::class);
        $params = [
            'data' => [
                'table' => 'communes',
                'type' => 'table',
                'alias' => 'Sbm\Db\Table\Communes'
            ],
            'form' => $form
        ];
        $r = $this->addData($this->db_manager, $params);
        switch ($r) {
            case $r instanceof Response:
                return $r;
                break;
            case 'error':
            case 'warning':
            case 'success':
                return $this->redirect()->toRoute('sbmgestion/transport',
                    [
                        'action' => 'commune-liste',
                        'page' => $currentPage
                    ]);
                break;
            default:
                return new ViewModel(
                    [

                        'form' => $form->prepare(),
                        'page' => $currentPage,
                        'communeId' => null
                    ]);
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
            $args = Session::get('post', [], $this->getSessionNamespace());
        } else {
            $args = $prg;
            Session::set('post', $args, $this->getSessionNamespace());
        }
        if ($pageRetour == - 1) {
            $pageRetour = Session::get('pageRetour', 1, $this->getSessionNamespace());
        } else {
            Session::set('pageRetour', $pageRetour, $this->getSessionNamespace());
        }
        $communeId = StdLib::getParam('communeId', $args, - 1);
        if ($communeId == - 1) {
            $this->flashMessenger()->addErrorMessage('Action interdite.');
            return $this->redirect()->toRoute('sbmgestion/transport',
                [
                    'action' => 'commune-liste',
                    'page' => $pageRetour
                ]);
        }
        return new ViewModel(
            [

                'paginator' => $this->db_manager->get('Sbm\Db\Eleve\Liste')->paginatorGroup(
                    Session::get('millesime'), FiltreEleve::byCommune($communeId),
                    [
                        'nom',
                        'prenom'
                    ]),
                'count_per_page' => $this->getPaginatorCountPerPage('nb_eleves', 15),
                'commune' => $this->db_manager->get('Sbm\Db\Table\Communes')->getRecord(
                    $communeId),
                'page' => $currentPage,
                'pageRetour' => $pageRetour,
                'communeId' => $communeId
            ]);
    }

    /**
     * envoie un evenement contenant les paramètres de création d'un document pdf (le
     * listener SbmPdf\Listener\PdfListener lancera la création du pdf) Il n'y a pas de
     * vue associée à cette action puisque la response html est créée par \TCPDF
     */
    public function communePdfAction()
    {
        $criteresObject = 'SbmCommun\Model\Db\ObjectData\Criteres';
        $criteresForm = [
            'SbmCommun\Form\CriteresForm',
            'communes'
        ];
        $documentId = null;
        $retour = [
            'route' => 'sbmgestion/transport',
            'action' => 'commune-liste'
        ];
        return $this->documentPdf($criteresObject, $criteresForm, $documentId, $retour,
            [
                'effectifClassName' => 'Sbm\Db\Eleve\EffectifCommunes'
            ]);
    }

    /**
     * lance la création d'une liste d'élève avec comme filtre le communeId reçu en post
     */
    public function communeGroupPdfAction()
    {
        $criteresObject = [
            'SbmCommun\Model\Db\ObjectData\Criteres',
            null,
            function ($where, $args) {
                $communeId = StdLib::getParam('communeId', $args, - 1);
                $where = new Where();
                $where->equalTo('communeId', $communeId);
                return $where;
            }
        ];
        $criteresForm = 'SbmCommun\Form\CriteresForm';
        $documentId = null;
        $retour = [
            'route' => 'sbmgestion/transport',
            'action' => 'commune-group'
        ];
        return $this->documentPdf($criteresObject, $criteresForm, $documentId, $retour);
    }

    public function communeGroupSelectionAction()
    {
        $query = 'queryGroup';
        $filtre = 'byCommune';
        $idField = 'communeId';
        $retour = [
            'route' => 'sbmgestion/transport',
            'action' => 'commune-group'
        ];
        $result = $this->markSelectionEleves($query, $filtre, $idField, $retour);
        if ($result instanceof ViewModel) {
            $result->setTemplate('sbm-gestion/transport/group-selection.phtml');
        }
        return $result;
    }

    /**
     * ================================ ETABLISSEMENTS ========================
     */
    /**
     * Critère de sélection commun aux établissements et aux stations. La localisation
     * géographique est dans un rectangle défini dans la config (voir
     * config/autoload/sbm.local.php) (paramètres dans cartes - etablissements - valide)
     *
     * @param string $nature
     *            Prend les valeurs 'etablissement' ou 'station'
     * @return string
     */
    private function critereLocalisation($nature)
    {
        $projection = $this->cartographie_manager->get(Projection::class);
        $rangeX = $projection->getRangeX();
        $rangeY = $projection->getRangeY();
        $pasLocalisaton = 'Not((x Between %d And %d) And (y Between %d And %d))';
        return sprintf($pasLocalisaton, $rangeX[$nature][0], $rangeX[$nature][1],
            $rangeY[$nature][0], $rangeY[$nature][1]);
    }

    /**
     * Liste des etablissements (avec pagination)
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function etablissementListeAction()
    {
        $args = $this->initListe('etablissements', null, [],
            [
                'localisation' => 'Literal:' . $this->critereLocalisation('etablissement')
            ]);
        if ($args instanceof Response)
            return $args;
        $effectifEtablissements = $this->db_manager->get(
            'Sbm\Db\Eleve\EffectifEtablissements');
        $effectifEtablissements->init();
        return new ViewModel(
            [

                'paginator' => $this->db_manager->get('Sbm\Db\Vue\Etablissements')->paginator(
                    $args['where']),
                'effectifEtablissements' => $effectifEtablissements,
                'page' => $this->params('page', 1),
                'count_per_page' => $this->getPaginatorCountPerPage('nb_etablissements',
                    10),
                'criteres_form' => $args['form'],
                'projection' => $this->cartographie_manager->get(Projection::class)
            ]);
    }

    /**
     * Modification d'une fiche d'etablissement (la validation porte sur un champ csrf)
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function etablissementEditAction()
    {
        $currentPage = $this->params('page', 1);
        $form = $this->form_manager->get(Form\Etablissement::class);
        $form->modifFormForEdit()
            ->setValueOptions('jOuverture', Strategy\Semaine::getJours())
            ->setValueOptions('niveau', Strategy\Niveau::getNiveaux())
            ->setValueOptions('rattacheA',
            $this->db_manager->get('Sbm\Db\Select\Etablissements')
                ->visibles())
            ->setValueOptions('communeId',
            $this->db_manager->get('Sbm\Db\Select\Communes')
                ->desservies());
        $params = [
            'data' => [
                'table' => 'etablissements',
                'type' => 'table',
                'alias' => 'Sbm\Db\Table\Etablissements',
                'id' => 'etablissementId'
            ],
            'form' => $form
        ];

        $r = $this->editData($this->db_manager, $params);
        if ($r instanceof Response) {
            return $r;
        } else {
            switch ($r->getStatus()) {
                case 'error':
                case 'warning':
                case 'success':
                    return $this->redirect()->toRoute('sbmgestion/transport',
                        [
                            'action' => 'etablissement-liste',
                            'page' => $currentPage
                        ]);
                    break;
                default:
                    return new ViewModel(
                        [

                            'form' => $form->prepare(),
                            'page' => $currentPage,
                            'etablissementId' => $r->getResult()
                        ]);
                    break;
            }
        }
    }

    /**
     * Suppression d'une fiche avec confirmation
     *
     * @todo : Vérifier qu'il n'y a pas d'élève inscrit avant de supprimer la fiche
     * @return \Zend\View\Model\ViewModel
     */
    public function etablissementSupprAction()
    {
        $currentPage = $this->params('page', 1);
        $form = new Form\ButtonForm([
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
                'alias' => 'Sbm\Db\Table\Etablissements',
                'id' => 'etablissementId'
            ],
            'form' => $form
        ];
        $vueEtablissement = $this->db_manager->get('Sbm\Db\Vue\Etablissements');
        try {
            $r = $this->supprData($this->db_manager, $params,
                function ($id, $tableEtablissements) use ($vueEtablissement) {
                    return [
                        'id' => $id,
                        'data' => $vueEtablissement->getRecord($id)
                    ];
                });
        } catch (\Zend\Db\Adapter\Exception\InvalidQueryException $e) {
            $this->flashMessenger()->addWarningMessage(
                'Impossible de supprimer cet établissement car un enregistrement l\'utilise.');
            return $this->redirect()->toRoute('sbmgestion/transport',
                [
                    'action' => 'etablissement-liste',
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
                    return $this->redirect()->toRoute('sbmgestion/transport',
                        [
                            'action' => 'etablissement-liste',
                            'page' => $currentPage
                        ]);
                    break;
                default:
                    return new ViewModel(
                        [

                            'form' => $form->prepare(),
                            'page' => $currentPage,
                            'data' => StdLib::getParam('data', $r->getResult()),
                            'etablissementId' => StdLib::getParam('id', $r->getResult())
                        ]);
                    break;
            }
        }
    }

    /**
     * Ajout d'une nouvelle fiche d'etablissement (la validation porte sur un champ csrf)
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function etablissementAjoutAction()
    {
        $currentPage = $this->params('page', 1);
        $form = $this->form_manager->get(Form\Etablissement::class);
        $form->setValueOptions('jOuverture', Strategy\Semaine::getJours())
            ->setValueOptions('niveau', Strategy\Niveau::getNiveaux())
            ->setValueOptions('rattacheA',
            $this->db_manager->get('Sbm\Db\Select\Etablissements')
                ->visibles())
            ->setValueOptions('communeId',
            $this->db_manager->get('Sbm\Db\Select\Communes')
                ->desservies());
        $params = [
            'data' => [
                'table' => 'etablissements',
                'type' => 'table',
                'alias' => 'Sbm\Db\Table\Etablissements'
            ],
            'form' => $form
        ];
        $r = $this->addData($this->db_manager, $params);
        switch ($r) {
            case $r instanceof Response:
                return $r;
                break;
            case 'error':
            case 'warning':
                return $this->redirect()->toRoute('sbmgestion/transport',
                    [
                        'action' => 'etablissement-liste',
                        'page' => $currentPage
                    ]);
                break;
            case 'success':
                $viewmodel = $this->etablissementLocalisationAction(
                    $form->getData()->etablissementId, $currentPage);
                $viewmodel->setTemplate(
                    'sbm-gestion/transport/etablissement-localisation.phtml');
                return $viewmodel;
                break;
            default:
                return new ViewModel(
                    [

                        'form' => $form->prepare(),
                        'page' => $currentPage,
                        'etablissementId' => null
                    ]);
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
            $args = Session::get('post', [], $this->getSessionNamespace());
        } else {
            $args = $prg;
            Session::set('post', $args, $this->getSessionNamespace());
        }
        if ($pageRetour == - 1) {
            $pageRetour = Session::get('pageRetour', 1, $this->getSessionNamespace());
        } else {
            Session::set('pageRetour', $pageRetour, $this->getSessionNamespace());
        }
        $etablissementId = StdLib::getParam('etablissementId', $args, - 1);
        if ($etablissementId == - 1) {
            $this->flashMessenger()->addErrorMessage('Action interdite.');
            return $this->redirect()->toRoute('sbmgestion/transport',
                [
                    'action' => 'etablissement-liste',
                    'page' => $pageRetour
                ]);
        }
        return new ViewModel(
            [

                'paginator' => $this->db_manager->get('Sbm\Db\Eleve\Liste')->paginatorGroup(
                    Session::get('millesime'),
                    FiltreEleve::byEtablissement($etablissementId), [
                        'nom',
                        'prenom'
                    ]),
                'count_per_page' => $this->getPaginatorCountPerPage('nb_eleves', 15),
                'etablissement' => $this->db_manager->get('Sbm\Db\Vue\Etablissements')->getRecord(
                    $etablissementId),
                'page' => $currentPage,
                'pageRetour' => $pageRetour,
                'etablissementId' => $etablissementId
            ]);
    }

    /**
     * envoie un evenement contenant les paramètres de création d'un document pdf (le
     * listener SbmPdf\Listener\PdfListener lancera la création du pdf) Il n'y a pas de
     * vue associée à cette action puisque la response html est créée par \TCPDF
     */
    public function etablissementPdfAction()
    {
        $criteresObject = [
            'SbmCommun\Model\Db\ObjectData\Criteres',
            [
                'expressions' => [
                    'localisation' => 'Literal:' .
                    $this->critereLocalisation('etablissement')
                ]
            ]
        ];
        $criteresForm = [
            'SbmCommun\Form\CriteresForm',
            'etablissements'
        ];
        $documentId = null;
        $retour = [
            'route' => 'sbmgestion/transport',
            'action' => 'etablissement-liste'
        ];
        return $this->documentPdf($criteresObject, $criteresForm, $documentId, $retour,
            [
                'effectifClassName' => 'Sbm\Db\Eleve\EffectifEtablissements'
            ]);
    }

    /**
     * lance la création d'une liste d'élève avec comme filtre le etablissementId reçu en
     * post
     */
    public function etablissementGroupPdfAction()
    {
        $criteresObject = [
            'SbmCommun\Model\Db\ObjectData\Criteres',
            null,
            function ($where, $args) {
                $etablissementId = StdLib::getParam('etablissementId', $args, - 1);
                $where = new Where();
                $where->equalTo('etablissementId', $etablissementId);
                return $where;
            }
        ];
        $criteresForm = 'SbmCommun\Form\CriteresForm';
        $documentId = null;
        $retour = [
            'route' => 'sbmgestion/transport',
            'action' => 'etablissement-group'
        ];
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
                return $this->redirect()->toRoute('sbmgestion/transport',
                    [
                        'action' => 'etablissement-liste',
                        'page' => $currentPage
                    ]);
            } else {
                $args = $prg;
                if (array_key_exists('cancel', $args)) {
                    $this->flashMessenger()->addWarningMessage('Localisation abandonnée.');
                    return $this->redirect()->toRoute('sbmgestion/transport',
                        [
                            'action' => 'etablissement-liste',
                            'page' => $currentPage
                        ]);
                }
                if (! array_key_exists('etablissementId', $args)) {
                    $this->flashMessenger()->addErrorMessage('Action  interdite');
                    return $this->redirect()->toRoute('login', [
                        'action' => 'logout'
                    ]);
                }
            }
            $etablissementId = $args['etablissementId'];
        } else {
            $args = [];
        }
        $oDistanceMatrix = $this->cartographie_manager->get(
            GoogleMaps\DistanceMatrix::class);
        $tEtablissements = $this->db_manager->get('Sbm\Db\Table\Etablissements');
        $configCarte = StdLib::getParam('etablissement',
            $this->cartographie_manager->get('cartes'));
        $form = new Form\LatLng([
            'etablissementId' => [
                'id' => 'etablissementId'
            ]
        ],
            [
                'submit' => [
                    'class' => 'button default submit left-95px',
                    'value' => 'Enregistrer la localisation'
                ],
                'cancel' => [
                    'class' => 'button default cancel left-10px',
                    'value' => 'Abandonner'
                ]
            ], $configCarte['valide']);
        $form->setAttribute('action',
            $this->url()
                ->fromRoute('sbmgestion/transport',
                [
                    'action' => 'etablissement-localisation',
                    'page' => $currentPage
                ]));
        if (array_key_exists('submit', $args)) {
            $form->setData($args);
            if ($form->isValid()) {
                // transforme les coordonnées
                $pt = new Point($args['lng'], $args['lat'], 0, 'degré');
                $point = $oDistanceMatrix->getProjection()->gRGF93versXYZ($pt);
                // enregistre dans la fiche etablissement
                $oData = $tEtablissements->getObjData();
                $oData->exchangeArray(
                    [
                        'etablissementId' => $etablissementId,
                        'x' => $point->getX(),
                        'y' => $point->getY()
                    ]);
                $tEtablissements->saveRecord($oData);
                $this->flashMessenger()->addSuccessMessage(
                    'La localisation de l\'établissement est enregistrée.');
                $this->flashMessenger()->addWarningMessage(
                    'Attention ! Les distances des domiciles des élèves à l\'établissement n\'ont pas été mises à jour.');
                return $this->redirect()->toRoute('sbmgestion/transport',
                    [
                        'action' => 'etablissement-liste',
                        'page' => $currentPage
                    ]);
            }
        }
        $etablissement = $tEtablissements->getRecord($etablissementId);
        $description = '<b>' . $etablissement->nom . "</b>\n";
        $commune = $this->db_manager->get('Sbm\Db\table\Communes')->getRecord(
            $etablissement->communeId);
        if ($etablissement->x == 0.0 && $etablissement->y == 0.0) {
            // essayer de localiser par l'adresse avant de présenter la carte
            $array = $this->cartographie_manager->get(GoogleMaps\Geocoder::class)->geocode(
                $etablissement->adresse1, $etablissement->codePostal, $commune->nom);
            $pt = new Point($array['lng'], $array['lat'], 0, 'degré');
            $description .= $array['adresse'];
        } else {
            $point = new Point($etablissement->x, $etablissement->y);
            $pt = $oDistanceMatrix->getProjection()->xyzVersgRGF93($point);
            $description .= trim(
                implode("\n", [
                    $etablissement->adresse1,
                    $etablissement->adresse2
                ]), "\n");
            $description .= "\n" . $etablissement->codePostal . ' ' . $commune->nom;
        }
        $description = str_replace("\n", "", nl2br($description));
        $form->setData(
            [
                'etablissementId' => $etablissementId,
                'lat' => $pt->getLatitude(),
                'lng' => $pt->getLongitude()
            ]);
        $tEtablissements = $this->db_manager->get('Sbm\Db\Vue\Etablissements');
        $ptEtablissements = [];
        foreach ($tEtablissements->fetchAll() as $autreEtablissement) {
            if ($autreEtablissement->etablissementId != $etablissementId) {
                $pt = new Point($autreEtablissement->x, $autreEtablissement->y);
                $pt->setAttribute('etablissement', $autreEtablissement);
                $ptEtablissements[] = $oDistanceMatrix->getProjection()->xyzVersgRGF93(
                    $pt);
            }
        }
        return new ViewModel(
            [

                'form' => $form->prepare(),
                'description' => $description,
                'etablissement' => [
                    $etablissement->nom,
                    nl2br(
                        trim(
                            implode("\n",
                                [
                                    $etablissement->adresse1,
                                    $etablissement->adresse2
                                ]))),
                    $etablissement->codePostal . ' ' . $commune->nom
                ],
                'ptEtablissements' => $ptEtablissements,
                'url_api' => $this->cartographie_manager->get('google_api_browser')['js'],
                'config' => $configCarte
            ]);
    }

    public function etablissementGroupSelectionAction()
    {
        $query = 'queryGroup';
        $filtre = 'byEtablissement';
        $idField = 'etablissementId';
        $retour = [
            'route' => 'sbmgestion/transport',
            'action' => 'etablissement-group'
        ];
        $result = $this->markSelectionEleves($query, $filtre, $idField, $retour);
        if ($result instanceof ViewModel) {
            $result->setTemplate('sbm-gestion/transport/group-selection.phtml');
        }
        return $result;
    }

    /**
     * ======================== ETABLISSEMENTS-SERVICES =======================
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
            $etablissementId = Session::get('etablissementId', false,
                $this->getSessionNamespace());
        } else {
            $args = $prg;
            if (StdLib::getParam('op', $args, '') == 'retour') {
                $etablissementId = null;
                $cancel = true;
            } else {
                $etablissementId = StdLib::getParam('etablissementId', $args, - 1);
                Session::set('etablissementId', $etablissementId,
                    $this->getSessionNamespace());
            }
        }
        $currentPage = $this->params('page', 1);
        $pageRetour = $this->params('id', - 1);
        if ($pageRetour == - 1) {
            $pageRetour = Session::get('pageRetour', 1, $this->getSessionNamespace());
        } else {
            Session::set('pageRetour', $pageRetour, $this->getSessionNamespace());
        }
        if ($etablissementId == - 1) {
            $this->flashMessenger()->addErrorMessage('Action interdite.');
            $cancel = true;
        }
        if ($cancel) {
            return $this->redirect()->toRoute('sbmgestion/transport',
                [
                    'action' => 'etablissement-liste',
                    'page' => $pageRetour
                ]);
        }
        $table = $this->db_manager->get('Sbm\Db\Vue\EtablissementsServices');
        $where = new Where();
        $where->equalTo('etablissementId', $etablissementId)->equalTo('cir_millesime',
            Session::get('millesime'));
        $effectifEtablissementsServices = $this->db_manager->get(
            'Sbm\Db\Eleve\EffectifEtablissementsServices');
        $effectifEtablissementsServices->setCaractereConditionnel($etablissementId)->init();
        return new ViewModel(
            [

                'etablissement' => $this->db_manager->get('Sbm\Db\Vue\Etablissements')->getRecord(
                    $etablissementId),
                'paginator' => $table->paginator($where),
                'count_per_page' => 15,
                'effectifEtablissementsServices' => $effectifEtablissementsServices,
                'page' => $currentPage
            ]);
    }

    /**
     * lance la création d'une liste se services desservant l'établissementId reçu en post
     */
    public function etablissementServicePdfAction()
    {
        $criteresObject = [
            'SbmCommun\Model\Db\ObjectData\Criteres',
            null,
            function ($where, $args) {
                $where->equalTo('etablissementId',
                    StdLib::getParam('etablissementId', $args, - 1))->equalTo(
                    'cir_millesime', Session::get('millesime'));
                return $where;
            }
        ];
        $criteresForm = 'SbmCommun\Form\CriteresForm';
        $documentId = null;
        $retour = [
            'route' => 'sbmgestion/transport',
            'action' => 'etablissement-service'
        ];
        return $this->documentPdf($criteresObject, $criteresForm, $documentId, $retour,
            [
                'effectifClassName' => 'Sbm\Db\Eleve\EffectifEtablissementsServices',
                'caractereConditionnel' => 'etablissementId'
            ]);
    }

    /**
     * lance la création d'une liste d'élève avec comme filtre le couple (etablissementId,
     * serviceId) reçu en post
     */
    public function etablissementServiceGroupPdfAction()
    {
        $criteresObject = [
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
        ];
        $criteresForm = 'SbmCommun\Form\CriteresForm';
        $documentId = null;
        $retour = [
            'route' => 'sbmgestion/transport',
            'action' => 'etablissement-service-group'
        ];
        return $this->documentPdf($criteresObject, $criteresForm, $documentId, $retour);
    }

    public function etablissementServiceGroupSelectionAction()
    {
        $query = 'queryGroupParAffectations';
        $filtre = 'byEtablissementService';
        $idFields = [
            'etablissementId',
            'serviceId'
        ];
        $retour = [
            'route' => 'sbmgestion/transport',
            'action' => 'etablissement-service-group'
        ];
        $result = $this->markSelectionEleves($query, $filtre, $idFields, $retour);
        if ($result instanceof ViewModel) {
            $result->setTemplate('sbm-gestion/transport/group-selection.phtml');
        }
        return $result;
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
            $serviceId = Session::get('serviceId', false, $this->getSessionNamespace());
        } else {
            $args = $prg;
            if (StdLib::getParam('op', $args, '') == 'retour') {
                $serviceId = null;
                $cancel = true;
            } else {
                $serviceId = StdLib::getParam('serviceId', $args, - 1);
                Session::set('serviceId', $serviceId, $this->getSessionNamespace());
            }
        }
        $currentPage = $this->params('page', 1);
        $pageRetour = $this->params('id', - 1);
        if ($pageRetour == - 1) {
            $pageRetour = Session::get('pageRetour', 1, $this->getSessionNamespace());
        } else {
            Session::set('pageRetour', $pageRetour, $this->getSessionNamespace());
        }
        if ($serviceId == - 1) {
            $this->flashMessenger()->addErrorMessage('Action interdite.');
            $cancel = true;
        }
        if ($cancel) {
            return $this->redirect()->toRoute('sbmgestion/transport',
                [
                    'action' => 'service-liste',
                    'page' => $pageRetour
                ]);
        }
        $table = $this->db_manager->get('Sbm\Db\Vue\EtablissementsServices');
        $effectifServicesEtablissements = $this->db_manager->get(
            'Sbm\Db\Eleve\EffectifServicesEtablissements');
        $effectifServicesEtablissements->setCaractereConditionnel($serviceId)->init();
        return new ViewModel(
            [

                'service' => $this->db_manager->get('Sbm\Db\Vue\Services')->getRecord(
                    $serviceId),
                'data' => $table->fetchAll(
                    [
                        'serviceId' => $serviceId,
                        'cir_millesime' => Session::get('millesime')
                    ]),
                'effectifServicesEtablissements' => $effectifServicesEtablissements,
                'page' => $currentPage,
                'pageRetour' => $pageRetour,
                'serviceId' => $serviceId
            ]);
    }

    /**
     * Ajout d'un lien etablissement - service (la validation porte sur un champ csrf)
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function etablissementServiceAjoutAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            $args = Session::get('post', [], $this->getSessionNamespace());
            if (StdLib::getParam('origine', $args, false) === false) {
                $this->flashMessenger()->addErrorMessage('Action interdite');
                return $this->redirect()->toRoute('sbmgestion/transport'); // on n'est pas
                                                                           // capable
                                                                           // de savoir
                                                                           // d'où l'on
                                                                           // vient
            }
        } else {
            $args = $prg;
            Session::set('post', $args, $this->getSessionNamespace());
        }
        $currentPage = $this->params('page', 1);
        $origine = StdLib::getParam('origine', $args, 'index');
        if (! is_null(StdLib::getParam('cancel', $args))) {
            $this->flashMessenger()->addWarningMessage(
                'Abandon de la création d\'une relation entre un service et un établissement.');
            return $this->redirect()->toRoute('sbmgestion/transport',
                [
                    'action' => $origine,
                    'page' => $currentPage
                ]);
        }
        $etablissementId = StdLib::getParam('etablissementId', $args, null);
        $serviceId = StdLib::getParam('serviceId', $args, null);
        $isPost = ! is_null(StdLib::getParam('submit', $args));
        $form = new Form\EtablissementService(
            $origine == 'etablissement-service' ? 'service' : 'etablissement');
        if ($origine == 'etablissement-service') {
            $service = null;
            $etablissement = $this->db_manager->get('Sbm\Db\Vue\Etablissements')->getRecord(
                $etablissementId);
            $form->setValueOptions('serviceId',
                $this->db_manager->get('Sbm\Db\Select\Services'));
        } else {
            $etablissement = null;
            $service = $this->db_manager->get('Sbm\Db\Vue\Services')->getRecord(
                $serviceId);
            $form->setValueOptions('etablissementId',
                $this->db_manager->get('Sbm\Db\Select\Etablissements')
                    ->desservis());
        }
        $table = $this->db_manager->get('Sbm\Db\Table\EtablissementsServices');
        $form->bind($table->getObjData());
        if ($isPost) {
            $form->setData($args);
            if ($form->isValid()) {
                $table->saveRecord($form->getData());
                $this->flashMessenger()->addSuccessMessage(
                    "Une relation entre un service et un établissement a été crée.");
                return $this->redirect()->toRoute('sbmgestion/transport',
                    [
                        'action' => $origine,
                        'page' => $currentPage
                    ]);
            }
        } else {
            $form->setData(
                [
                    'etablissementId' => $etablissementId,
                    'serviceId' => $serviceId,
                    'origine' => $origine
                ]);
        }
        if (! empty($serviceId)) {
            $form->setValueOptions('stationId',
                $this->db_manager->get('Sbm\Db\Select\Stations')
                    ->surcircuit($serviceId, Session::get('millesime')));
        }
        return new ViewModel(
            [

                'origine' => $origine,
                'form' => $form->prepare(),
                'page' => $currentPage,
                'etablissementId' => $etablissementId,
                'serviceId' => $serviceId,
                'etablissement' => $etablissement,
                'service' => $service
            ]);
    }

    /**
     * Suppression d'une relation établissement-service avec confirmation A l'appel, les
     * variables suivantes sont récupérées : $etablissementId, $serviceId, $origine, $op,
     * $supprimer Lors de l'annulation, on a : $etablissementId, $serviceId, $origine,
     * $op, $supprnon Lors de la validation on a : $etablissementId, $serviceId, $origine,
     * $op, $supproui
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function etablissementServiceSupprAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            $args = [];
        } else {
            $args = $prg;
        }
        $origine = StdLib::getParam('origine', $args, 'index');
        $etablissementId = StdLib::getParam('etablissementId', $args, false);
        $serviceId = StdLib::getParam('serviceId', $args, false);
        $cancel = StdLib::getParam('cancel', $args, false);
        if ($origine == 'index' || $etablissementId === false || $serviceId == false) {
            $this->flashMessenger()->addErrorMessage("Action interdite.");
            return $this->redirect()->toRoute('sbmgestion/transport',
                [
                    'action' => 'index',
                    'page' => $this->params('page', 1)
                ]);
        } elseif ($cancel) {
            $this->flashMessenger()->addWarningMessage(
                "L'enregistrement n'a pas été supprimé.");
            return $this->redirect()->toRoute('sbmgestion/transport',
                [
                    'action' => $origine,
                    'page' => $this->params('page', 1)
                ]);
        }
        $form = $this->form_manager->get(FormGestion\EtablissementServiceSuppr::class);
        $form->setAttribute('action',
            $this->url()
                ->fromRoute('sbmgestion/transport',
                [
                    'action' => 'etablissement-service-suppr',
                    'page' => $this->params('page', 1)
                ]));
        $table = $this->db_manager->get('Sbm\Db\Table\EtablissementsServices');
        if (array_key_exists('submit', $args)) { // suppression confirmée
            $form->setData($args);
            if ($form->isValid()) {
                $table->deleteRecord(
                    [
                        'etablissementId' => $etablissementId,
                        'serviceId' => $serviceId
                    ]);
                $this->flashMessenger()->addSuccessMessage(
                    "L'enregistrement a été supprimé.");
                return $this->redirect()->toRoute('sbmgestion/transport',
                    [
                        'action' => $origine,
                        'page' => $this->params('page', 1)
                    ]);
            }
        } else {
            $form->setData(
                [
                    'etablissementId' => $etablissementId,
                    'serviceId' => $serviceId,
                    'origine' => $origine
                ]);
        }
        return new ViewModel(
            [

                'etablissementId' => $etablissementId,
                'serviceId' => $serviceId,
                'origine' => $origine,
                'etablissement' => $this->db_manager->get('Sbm\Db\Vue\Etablissements')->getRecord(
                    $etablissementId),
                'service' => $this->db_manager->get('Sbm\Db\Vue\Services')->getRecord(
                    $serviceId),
                'form' => $form->prepare()
            ]);
    }

    /**
     * renvoie la liste des élèves inscrits pour un etablissement donné et un service
     * donné
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
            $args = Session::get('post', [], $this->getSessionNamespace());
        } else {
            $args = $prg;
            Session::set('post', $args, $this->getSessionNamespace());
        }
        if ($pageRetour == - 1) {
            $pageRetour = Session::get('pageRetour', 1, $this->getSessionNamespace());
        } else {
            Session::set('pageRetour', $pageRetour, $this->getSessionNamespace());
        }
        $etablissementId = StdLib::getParam('etablissementId', $args, - 1);
        $serviceId = StdLib::getParam('serviceId', $args, - 1);
        if ($etablissementId == - 1 || $serviceId == - 1) {
            $this->flashMessenger()->addErrorMessage('Action interdite.');
            return $this->redirect()->toRoute('sbmgestion/transport',
                [
                    'action' => 'etablissement-liste',
                    'page' => $pageRetour
                ]);
        }
        $viewModel = new ViewModel(
            [

                'h1' => 'Groupe des élèves d\'un établissement inscrits sur un service',
                'paginator' => $this->db_manager->get('Sbm\Db\Eleve\Liste')->paginatorGroupParAffectations(
                    Session::get('millesime'),
                    FiltreEleve::byEtablissementService($etablissementId, $serviceId),
                    [
                        'nom',
                        'prenom'
                    ]),
                'count_per_page' => $this->getPaginatorCountPerPage('nb_eleves', 15),
                'etablissement' => $this->db_manager->get('Sbm\Db\Vue\Etablissements')->getRecord(
                    $etablissementId),
                'service' => $this->db_manager->get('Sbm\Db\Vue\Services')->getRecord(
                    $serviceId),
                'page' => $currentPage,
                'pageRetour' => $pageRetour,
                'etablissementId' => $etablissementId,
                'serviceId' => $serviceId,
                'origine' => StdLib::getParam('origine', $args, 'etablissement-service')
            ]);
        return $viewModel;
    }

    /**
     * =============================== LOTS DE MARCHÉ =========================
     */
    public function lotListeAction()
    {
        $args = $this->initListe('lots');
        if ($args instanceof Response)
            return $args;
        try {
            $effectifLots = $this->db_manager->get('Sbm\Db\Eleve\EffectifLots');
            $effectifLots->init();
        } catch (\Exception $e) {
            $effectifLots = null;
        }
        try {
            $effectifLotsServices = $this->db_manager->get('Sbm\Db\Service\EffectifLots');
            $effectifLotsServices->init();
        } catch (\Exception $e) {
            $effectifLotsServices = null;
        }
        return new ViewModel(
            [

                'paginator' => $this->db_manager->get('Sbm\Db\Vue\Lots')->paginator(
                    $args['where'], [
                        'marche',
                        'lot'
                    ]),
                'effectifLotsServices' => $effectifLotsServices,
                'effectifLots' => $effectifLots,
                'page' => $this->params('page', 1),
                'count_per_page' => $this->getPaginatorCountPerPage('nb_lots', 15),
                'criteres_form' => $args['form']
            ]);
    }

    public function lotAjoutAction()
    {
        $currentPage = $this->params('page', 1);
        $form = $this->form_manager->get(Form\Lot::class);
        $form->setValueOptions('transporteurId',
            $this->db_manager->get('Sbm\Db\Select\Transporteurs'));
        $params = [
            'data' => [
                'table' => 'lots',
                'type' => 'table',
                'alias' => 'Sbm\Db\Table\Lots'
            ],
            'form' => $form
        ];
        $r = $this->addData($this->db_manager, $params);
        switch ($r) {
            case $r instanceof Response:
                return $r;
                break;
            case 'error':
            case 'warning':
            case 'success':
                return $this->redirect()->toRoute('sbmgestion/transport',
                    [
                        'action' => 'lot-liste',
                        'page' => $currentPage
                    ]);
                break;
            default:
                return new ViewModel(
                    [

                        'form' => $form->prepare(),
                        'page' => $currentPage,
                        'communeId' => null
                    ]);
                break;
        }
    }

    public function lotEditAction()
    {
        $currentPage = $this->params('page', 1);
        $form = $this->form_manager->get(Form\Lot::class);
        $form->setValueOptions('transporteurId',
            $this->db_manager->get('Sbm\Db\Select\Transporteurs'));
        $params = [
            'data' => [
                'table' => 'lots',
                'type' => 'table',
                'alias' => 'Sbm\Db\Table\Lots',
                'id' => 'lotId'
            ],
            'form' => $form
        ];

        $r = $this->editData($this->db_manager, $params);
        if ($r instanceof Response) {
            return $r;
        } else {
            switch ($r->getStatus()) {
                case 'error':
                case 'warning':
                case 'success':
                    return $this->redirect()->toRoute('sbmgestion/transport',
                        [
                            'action' => 'lot-liste',
                            'page' => $currentPage
                        ]);
                    break;
                default:
                    return new ViewModel(
                        [

                            'form' => $form->prepare(),
                            'page' => $currentPage,
                            'lotId' => $r->getResult()
                        ]);
                    break;
            }
        }
    }

    public function lotSupprAction()
    {
        $currentPage = $this->params('page', 1);
        $form = new Form\ButtonForm([
            'id' => null,
            'origine' => null
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
                'alias' => 'Sbm\Db\Table\Lots',
                'id' => 'lotId'
            ],
            'form' => $form
        ];
        $vueLots = $this->db_manager->get('Sbm\Db\Vue\Lots');
        try {
            $r = $this->supprData($this->db_manager, $params,
                function ($id, $tableLots) use ($vueLots) {
                    return [
                        'id' => $id,
                        'data' => $vueLots->getRecord($id)
                    ];
                });
        } catch (\Zend\Db\Adapter\Exception\InvalidQueryException $e) {
            $this->flashMessenger()->addWarningMessage(
                'Impossible de supprimer ce lot de marché car il existe un service lié.');
            return $this->redirect()->toRoute('sbmgestion/transport',
                [
                    'action' => 'lot-liste',
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
                    return $this->redirect()->toRoute('sbmgestion/transport',
                        [
                            'action' => 'lot-liste',
                            'page' => $currentPage
                        ]);
                    break;
                default:
                    return new ViewModel(
                        [

                            'form' => $form->prepare(),
                            'page' => $currentPage,
                            'data' => StdLib::getParam('data', $r->getResult()),
                            'lotId' => StdLib::getParam('id', $r->getResult())
                        ]);
                    break;
            }
        }
    }

    public function lotPdfAction()
    {
        $criteresObject = 'SbmCommun\Model\Db\ObjectData\Criteres';
        $criteresForm = [
            'SbmCommun\Form\CriteresForm',
            'lots'
        ];
        $documentId = null;
        $retour = [
            'route' => 'sbmgestion/transport',
            'action' => 'lot-liste'
        ];
        return $this->documentPdf($criteresObject, $criteresForm, $documentId, $retour,
            [
                'effectifClassName' => 'Sbm\Db\Eleve\EffectifLots'
            ]);
    }

    public function lotGroupAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            $args = Session::get('post', [], $this->getSessionNamespace());
        } else {
            $args = $prg;
            Session::set('post', $args, $this->getSessionNamespace());
        }
        $currentPage = $this->params('page', 1);
        $pageRetour = $this->params('id', - 1);
        if ($pageRetour == - 1) {
            $pageRetour = Session::get('pageRetour', 1, $this->getSessionNamespace());
        } else {
            Session::set('pageRetour', $pageRetour, $this->getSessionNamespace());
        }
        $lotId = StdLib::getParam('lotId', $args, - 1);
        if ($lotId == - 1) {
            $this->flashMessenger()->addErrorMessage('Action interdite.');
            return $this->redirect()->toRoute('sbmgestion/transport',
                [
                    'action' => 'lot-liste',
                    'page' => $pageRetour
                ]);
        }

        return new ViewModel(
            [

                'paginator' => $this->db_manager->get('Sbm\Db\Eleve\Liste')->paginatorGroupParAffectations(
                    Session::get('millesime'), FiltreEleve::byLot($lotId),
                    [
                        'serviceId',
                        'nom',
                        'prenom'
                    ]),
                'count_per_page' => $this->getPaginatorCountPerPage('nb_eleves', 15),
                'lot' => $this->db_manager->get('Sbm\Db\Vue\Lots')->getRecord($lotId),
                'page' => $currentPage,
                'pageRetour' => $pageRetour,
                'lotId' => $lotId
            ]);
        ;
    }

    public function lotGroupPdfAction()
    {
        $criteresObject = [
            'SbmCommun\Model\Db\ObjectData\Criteres',
            null,
            function ($where, $args) {
                $lotId = StdLib::getParam('lotId', $args, - 1);
                $where = new Where();
                $where->equalTo('lotId', $lotId);
                return $where;
            }
        ];
        $criteresForm = 'SbmCommun\Form\CriteresForm';
        $documentId = null;
        $retour = [
            'route' => 'sbmgestion/transport',
            'action' => 'lot-group'
        ];
        return $this->documentPdf($criteresObject, $criteresForm, $documentId, $retour);
    }

    public function lotGroupSelectionAction()
    {
        $query = 'queryGroupParAffectations';
        $filtre = 'byLot';
        $idField = 'lotId';
        $retour = [
            'route' => 'sbmgestion/transport',
            'action' => 'lot-group'
        ];
        $result = $this->markSelectionEleves($query, $filtre, $idField, $retour);
        if ($result instanceof ViewModel) {
            $result->setTemplate('sbm-gestion/transport/group-selection.phtml');
        }
        return $result;
    }

    public function lotServiceAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            $args = Session::get('post', [], $this->getSessionNamespace());
        } else {
            $args = $prg;
            Session::set('post', $args, $this->getSessionNamespace());
        }
        $currentPage = $this->params('page', 1);
        $pageRetour = $this->params('id', - 1);
        if ($pageRetour == - 1) {
            $pageRetour = Session::get('pageRetour', 1, $this->getSessionNamespace());
        } else {
            Session::set('pageRetour', $pageRetour, $this->getSessionNamespace());
        }
        $lotId = StdLib::getParam('lotId', $args, - 1);
        if ($lotId == - 1) {
            $this->flashMessenger()->addErrorMessage('Action interdite.');
            return $this->redirect()->toRoute('sbmgestion/transport',
                [
                    'action' => 'lot-liste',
                    'page' => $pageRetour
                ]);
        }
        $where = new Where();
        $where->equalTo('lotId', $lotId);
        $effectifLotsServices = $this->db_manager->get(
            'Sbm\Db\Eleve\EffectifLotsServices');
        $effectifLotsServices->setCaractereConditionnel($lotId)->init();
        return new ViewModel(
            [

                'paginator' => $this->db_manager->get('Sbm\Db\Table\Services')->paginator(
                    $where, [
                        'serviceId'
                    ]),
                'count_per_page' => 15,
                'effectifLotsServices' => $effectifLotsServices,
                'lot' => $this->db_manager->get('Sbm\Db\Table\Lots')->getRecord($lotId),
                'page' => $currentPage,
                'pageRetour' => $pageRetour,
                'lotId' => $lotId
            ]);
    }

    public function lotServicePdfAction()
    {
        $criteresObject = [
            'SbmCommun\Model\Db\ObjectData\Criteres',
            [],
            function ($where, $args) {
                $lotId = StdLib::getParam('lotId', $args);
                $where = new Where();
                return $where->equalTo('lotId', $lotId);
            }
        ];
        $criteresForm = 'SbmCommun\Form\CriteresForm';
        $documentId = null;
        $retour = [
            'route' => 'sbmgestion/transport',
            'action' => 'lot-service'
        ];
        return $this->documentPdf($criteresObject, $criteresForm, $documentId, $retour,
            [
                'caractereConditionnel' => 'lotId',
                'effectifClassName' => 'Sbm\Db\Eleve\EffectifLotsServices'
            ]);
    }

    /**
     * =============================== SERVICES ===============================
     */

    /**
     * Liste des services (avec pagination)
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function serviceListeAction()
    {
        $args = $this->initListe('services',
            function ($config, $form) {
                $form->setValueOptions('transporteurId',
                    $config['db_manager']->get('Sbm\Db\Select\Transporteurs'))
                    ->setValueOptions('serviceId',
                    $config['db_manager']->get('Sbm\Db\Select\Services'));
            }, [
                'transporteurId'
            ]);
        if ($args instanceof Response)
            return $args;
        $effectifServices = $this->db_manager->get('Sbm\Db\Eleve\EffectifServices');
        $effectifServices->init();
        return new ViewModel(
            [

                'paginator' => $this->db_manager->get('Sbm\Db\Vue\Services')->paginator(
                    $args['where']),
                'page' => $this->params('page', 1),
                'count_per_page' => $this->getPaginatorCountPerPage('nb_services', 15),
                'criteres_form' => $args['form'],
                'effectifServices' => $effectifServices,
                'natureCartes' => $this->db_manager->get('Sbm\Db\Vue\Services')->getNatureCartes()
            ]);
    }

    /**
     * Modification d'une fiche de service (la validation porte sur un champ csrf)
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function serviceEditAction()
    {
        $currentPage = $this->params('page', 1);
        $form = $this->form_manager->get(Form\Service::class);
        $form->modifFormForEdit()
            ->setValueOptions('lotId', $this->db_manager->get('Sbm\Db\Select\Lots'))
            ->setValueOptions('transporteurId',
            $this->db_manager->get('Sbm\Db\Select\Transporteurs'))
            ->setValueOptions('operateur', $this->operateurs)
            ->setValueOptions('horaire1', Strategy\Semaine::getJours())
            ->setValueOptions('horaire2', Strategy\Semaine::getJours())
            ->setValueOptions('horaire3', Strategy\Semaine::getJours())
            ->setValueOptions('natureCarte',
            $this->db_manager->get('Sbm\Db\Table\Services')
                ->getNatureCartes());
        $params = [
            'data' => [
                'table' => 'services',
                'type' => 'table',
                'alias' => 'Sbm\Db\Table\Services',
                'id' => 'serviceId'
            ],
            'form' => $form
        ];

        $r = $this->editData($this->db_manager, $params);
        if ($r instanceof Response) {
            return $r;
        } else {
            switch ($r->getStatus()) {
                case 'error':
                case 'warning':
                case 'success':
                    return $this->redirect()->toRoute('sbmgestion/transport',
                        [
                            'action' => 'service-liste',
                            'page' => $currentPage
                        ]);
                    break;
                default:
                    return new ViewModel(
                        [

                            'form' => $form->prepare(),
                            'page' => $currentPage,
                            'serviceId' => $r->getResult()
                        ]);
                    break;
            }
        }
    }

    /**
     * Suppression d'une fiche avec confirmation
     *
     * @todo : Vérifier qu'il n'y a pas d'élève inscrit avant de supprimer la fiche
     * @return \Zend\View\Model\ViewModel
     */
    public function serviceSupprAction()
    {
        $currentPage = $this->params('page', 1);
        $form = new Form\ButtonForm([
            'id' => null,
            'origine' => null
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
                'alias' => 'Sbm\Db\Table\Services',
                'id' => 'serviceId'
            ],
            'form' => $form
        ];
        $vueServices = $this->db_manager->get('Sbm\Db\Vue\Services');
        try {
            $r = $this->supprData($this->db_manager, $params,
                function ($id, $tableServices) use ($vueServices) {
                    return [
                        'id' => $id,
                        'data' => $vueServices->getRecord($id)
                    ];
                });
        } catch (\Zend\Db\Adapter\Exception\InvalidQueryException $e) {
            $this->flashMessenger()->addWarningMessage(
                'Impossible de supprimer ce service car un enregistrement l\'utilise.');
            return $this->redirect()->toRoute('sbmgestion/transport',
                [
                    'action' => 'service-liste',
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
                    return $this->redirect()->toRoute('sbmgestion/transport',
                        [
                            'action' => 'service-liste',
                            'page' => $currentPage
                        ]);
                    break;
                default:
                    return new ViewModel(
                        [

                            'form' => $form->prepare(),
                            'page' => $currentPage,
                            'data' => StdLib::getParam('data', $r->getResult()),
                            'serviceId' => StdLib::getParam('id', $r->getResult())
                        ]);
                    break;
            }
        }
    }

    /**
     * Ajout d'une nouvelle fiche de service (la validation porte sur un champ csrf)
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function serviceAjoutAction()
    {
        $currentPage = $this->params('page', 1);
        $form = $this->form_manager->get(Form\Service::class);
        $form->setValueOptions('lotId', $this->db_manager->get('Sbm\Db\Select\Lots'))
            ->setValueOptions('transporteurId',
            $this->db_manager->get('Sbm\Db\Select\Transporteurs'))
            ->setValueOptions('operateur', $this->operateurs)
            ->setValueOptions('horaire1', Strategy\Semaine::getJours())
            ->setValueOptions('horaire2', Strategy\Semaine::getJours())
            ->setValueOptions('horaire3', Strategy\Semaine::getJours())
            ->setValueOptions('natureCarte',
            $this->db_manager->get('Sbm\Db\Table\Services')
                ->getNatureCartes());
        $params = [
            'data' => [
                'table' => 'services',
                'type' => 'table',
                'alias' => 'Sbm\Db\Table\Services'
            ],
            'form' => $form
        ];
        $r = $this->addData($this->db_manager, $params);
        switch ($r) {
            case $r instanceof Response:
                return $r;
                break;
            case 'error':
            case 'warning':
            case 'success':
                return $this->redirect()->toRoute('sbmgestion/transport',
                    [
                        'action' => 'service-liste',
                        'page' => $currentPage
                    ]);
                break;
            default:
                return new ViewModel(
                    [

                        'form' => $form->prepare(),
                        'page' => $currentPage,
                        'serviceId' => null
                    ]);
                break;
        }
    }

    /**
     * renvoie la liste des élèves inscrits pour un service donné Reçoit en get : - id :
     * pageRetour - page : page du paginateur interne Reçoit en post : - serviceId -
     * origine
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
            $args = Session::get('post', [], $this->getSessionNamespace());
        } else {
            $args = $prg;
            Session::set('post', $args, $this->getSessionNamespace());
        }
        if ($pageRetour == - 1) {
            $pageRetour = Session::get('pageRetour', 1, $this->getSessionNamespace());
        } else {
            Session::set('pageRetour', $pageRetour, $this->getSessionNamespace());
        }
        $serviceId = StdLib::getParam('serviceId', $args, - 1);
        if ($serviceId == - 1) {
            $this->flashMessenger()->addErrorMessage('Action interdite.');
            return $this->redirect()->toRoute('sbmgestion/transport',
                [
                    'action' => 'service-liste',
                    'page' => $pageRetour
                ]);
        }

        return new ViewModel(
            [

                'h1' => 'Groupe des élèves inscrits sur un service',
                'paginator' => $this->db_manager->get('Sbm\Db\Eleve\Liste')->paginatorGroup(
                    Session::get('millesime'), FiltreEleve::byService($serviceId),
                    [
                        'nom',
                        'prenom'
                    ]),
                'count_per_page' => $this->getPaginatorCountPerPage('nb_eleves', 15),
                'service' => $this->db_manager->get('Sbm\Db\Vue\Services')->getRecord(
                    $serviceId),
                'page' => $currentPage,
                'pageRetour' => $pageRetour,
                'serviceId' => $serviceId,
                'origine' => StdLib::getParam('origine', $args, 'service-liste')
            ]);
    }

    /**
     * envoie un evenement contenant les paramètres de création d'un document pdf (le
     * listener SbmPdf\Listener\PdfListener lancera la création du pdf) Il n'y a pas de
     * vue associée à cette action puisque la response html est créée par \TCPDF
     */
    public function servicePdfAction()
    {
        $criteresObject = 'SbmCommun\Model\Db\ObjectData\Criteres';
        $criteresForm = [
            'SbmCommun\Form\CriteresForm',
            'services'
        ];
        $documentId = null;
        $retour = [
            'route' => 'sbmgestion/transport',
            'action' => 'service-liste'
        ];
        return $this->documentPdf($criteresObject, $criteresForm, $documentId, $retour,
            [
                'effectifClassName' => 'Sbm\Db\Eleve\EffectifServices'
            ]);
    }

    /**
     * lance la création d'une liste d'établissements desservis le serviceId reçu en post
     */
    public function serviceEtablissementPdfAction()
    {
        $criteresObject = [
            'SbmCommun\Model\Db\ObjectData\Criteres',
            null,
            function ($where, $args) {
                $where->equalTo('serviceId', StdLib::getParam('serviceId', $args, - 1))->equalTo(
                    'cir_millesime', Session::get('millesime'));
                return $where;
            }
        ];
        $criteresForm = 'SbmCommun\Form\CriteresForm';
        $documentId = null;
        $retour = [
            'route' => 'sbmgestion/transport',
            'action' => 'service-etablissement'
        ];
        return $this->documentPdf($criteresObject, $criteresForm, $documentId, $retour,
            [
                'effectifClassName' => 'Sbm\Db\Eleve\EffectifServicesEtablissements',
                'caractereConditionnel' => 'serviceId'
            ]);
    }

    /**
     * lance la création d'une liste d'élève avec comme filtre le serviceId reçu en post
     */
    public function serviceGroupPdfAction()
    {
        $criteresObject = [
            'SbmCommun\Model\Db\ObjectData\Criteres',
            null,
            function ($where, $args) {
                $serviceId = StdLib::getParam('serviceId', $args, - 1);
                $where = new Where();
                $where->equalTo('millesime', Session::get('millesime'))->equalTo(
                    'serviceId', $serviceId);
                return $where;
            }
        ];
        $criteresForm = 'SbmCommun\Form\CriteresForm';
        $documentId = null;
        $retour = [
            'route' => 'sbmgestion/transport',
            'action' => 'service-group'
        ];
        return $this->documentPdf($criteresObject, $criteresForm, $documentId, $retour);
    }

    public function serviceGroupSelectionAction()
    {
        $query = 'queryGroup';
        $filtre = 'byService';
        $idField = 'serviceId';
        $retour = [
            'route' => 'sbmgestion/transport',
            'action' => 'service-group'
        ];
        $result = $this->markSelectionEleves($query, $filtre, $idField, $retour);
        if ($result instanceof ViewModel) {
            $result->setTemplate('sbm-gestion/transport/group-selection.phtml');
        }
        return $result;
    }

    /**
     * =============================== STATIONS ===============================
     */

    /**
     * Liste des stations (avec pagination)
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function stationListeAction()
    {
        $args = $this->initListe('stations',
            function ($config, $form) {
                $form->setValueOptions('communeId',
                    $config['db_manager']->get('Sbm\Db\Select\Communes')
                        ->desservies());
            }, [
                'communeId'
            ], [
                'localisation' => 'Literal:' . $this->critereLocalisation('station')
            ]);
        if ($args instanceof Response)
            return $args;
        $effectifStations = $this->db_manager->get('Sbm\Db\Eleve\EffectifStations');
        $effectifStations->init();
        return new ViewModel(
            [

                'paginator' => $this->db_manager->get('Sbm\Db\Vue\Stations')->paginator(
                    $args['where']),
                'effectifStations' => $effectifStations,
                'page' => $this->params('page', 1),
                'count_per_page' => $this->getPaginatorCountPerPage('nb_stations', 10),
                'criteres_form' => $args['form'],
                'projection' => $this->cartographie_manager->get(Projection::class)
            ]);
    }

    /**
     * Liste des stations non desservies (sans pagination)
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

        $effectifStations = $this->db_manager->get('Sbm\Db\Eleve\EffectifStations');
        $effectifStations->init();
        return new ViewModel(
            [

                'data' => $this->db_manager->get('Sbm\Db\Circuit\Liste')->stationsNonDesservies(),
                'effectifStations' => $effectifStations,
                'page' => $currentPage
            ]);
    }

    /**
     * Demande l'envoi d'un document contenant les stations non desservies
     */
    public function stationsNonDesserviesPdfAction()
    {
        $criteresObject = [
            'SbmCommun\Model\Db\ObjectData\Criteres'
        ];
        $criteresForm = [
            'SbmCommun\Form\CriteresForm'
        ];
        $documentId = null;
        $retour = [
            'route' => 'sbmgestion/transport',
            'action' => 'stations-non-desservies'
        ];
        return $this->documentPdf($criteresObject, $criteresForm, $documentId, $retour,
            [
                'effectifClassName' => 'Sbm\Db\Eleve\EffectifStations'
            ]);
    }

    /**
     * Modification d'une fiche de station (la validation porte sur un champ csrf)
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
        $form = $this->form_manager->get(Form\Station::class);
        $form->setValueOptions('communeId',
            $this->db_manager->get('Sbm\Db\Select\Communes')
                ->desservies());
        $params = [
            'data' => [
                'table' => 'stations',
                'type' => 'table',
                'alias' => 'Sbm\Db\Table\Stations',
                'id' => 'stationId'
            ],
            'form' => $form
        ];

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
                    $form->add(
                        [
                            'name' => 'origine',
                            'type' => 'hidden',
                            'attributes' => [
                                'value' => StdLib::getParam('origine', $r->getPost())
                            ]
                        ]);
                    return new ViewModel(
                        [

                            'form' => $form->prepare(),
                            'page' => $currentPage,
                            'stationId' => $r->getResult()
                        ]);
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
        $form = new Form\ButtonForm([
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
                'alias' => 'Sbm\Db\Table\Stations',
                'id' => 'stationId'
            ],
            'form' => $form
        ];
        $vueStations = $this->db_manager->get('Sbm\Db\Vue\Stations');
        try {
            $r = $this->supprData($this->db_manager, $params,
                function ($id, $tableStations) use ($vueStations) {
                    return [
                        'id' => $id,
                        'data' => $vueStations->getRecord($id)
                    ];
                });
        } catch (\Zend\Db\Adapter\Exception\InvalidQueryException $e) {
            $this->flashMessenger()->addWarningMessage(
                'Impossible de supprimer cette station car un enregistrement l\'utilise.');
            try {
                return $this->redirectToOrigin()->back();
            } catch (\SbmCommun\Model\Mvc\Controller\Plugin\Exception\ExceptionInterface $e) {
                return $this->redirect()->toRoute('sbmgestion/transport',
                    [
                        'action' => 'station-liste',
                        'page' => $currentPage
                    ]);
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
                    } catch (\SbmCommun\Model\Mvc\Controller\Plugin\Exception\ExceptionInterface $e) {
                        return $this->redirect()->toRoute('sbmgestion/transport',
                            [
                                'action' => StdLib::getParam('origine', $r->getPost()),
                                'page' => $currentPage
                            ]);
                    }
                    break;
                default:
                    return new ViewModel(
                        [

                            'form' => $form->prepare(),
                            'page' => $currentPage,
                            'data' => StdLib::getParam('data', $r->getResult()),
                            'stationId' => StdLib::getParam('id', $r->getResult())
                        ]);
                    break;
            }
        }
    }

    /**
     * Montre la carte des stations. Un clic dans la carte permet de placer la nouvelle
     * station. On enregistre la position. Le formulaire prérempli est présenté avec la
     * commune, l'adresse (N° + rue) et les coordonnées X et Y On peut alors changer le
     * nom de la station avant d'enregistrer la fiche.
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
            $args = [];
        } else {
            $args = $prg ?: [];
            $isPost1 = array_key_exists('phase', $args);
            $isPost2 = array_key_exists('csrf', $args);
            $cancel = StdLib::getParam('cancel', $args, false);
            // unset($args['submit']);
            // unset($args['cancel']);
        }
        if ($cancel) {
            $this->flashMessenger()->addWarningMessage(
                "Abandon de la création d'une nouvelle station.");
            return $this->redirect()->toRoute('sbmgestion/transport',
                [
                    'action' => 'station-liste',
                    'page' => $currentPage
                ]);
        }
        $table = $this->db_manager->get('Sbm\Db\Table\Stations');
        // même configuration de carte que pour les etablissements
        $configCarte = StdLib::getParam('station',
            $this->cartographie_manager->get('cartes'));
        $oDistanceMatrix = $this->cartographie_manager->get(
            GoogleMaps\DistanceMatrix::class);
        $formCarte = new Form\LatLng(
            [
                'phase' => 1,
                'lat' => [
                    'id' => 'lat'
                ],
                'lng' => [
                    'id' => 'lng'
                ]
            ],
            [
                'submit' => [
                    'class' => 'button default submit left-95px',
                    'value' => 'Enregistrer la localisation'
                ],
                'cancel' => [
                    'class' => 'button default cancel left-10px',
                    'value' => 'Abandonner'
                ]
            ], $configCarte['valide']);
        if ($isPost1 || $isPost2) {
            $form = $this->form_manager->get(Form\Station::class);
            $form->setValueOptions('communeId',
                $this->db_manager->get('Sbm\Db\Select\Communes')
                    ->desservies())
                ->setMaxLength($this->db_manager->getMaxLengthArray('stations', 'table'));

            $form->bind($table->getObjData());
            if ($isPost1) {
                $formCarte->setData($args);
                if (! $formCarte->isValid()) {
                    $this->flashMessenger()->addWarningMessage(
                        "La nouvelle station n'est pas dans la zone autorisée.");
                    return $this->redirect()->toRoute('sbmgestion/transport',
                        [
                            'action' => 'station-liste',
                            'page' => $currentPage
                        ]);
                }
                // transforme les coordonnées
                $pt = new Point($args['lng'], $args['lat'], 0, 'degré');
                $point = $oDistanceMatrix->getProjection()->gRGF93versXYZ($pt);
                // initialise le formulaire de la station
                $geocode = $this->cartographie_manager->get(GoogleMaps\Geocoder::class);
                $lieu = $geocode->reverseGeocoding($args['lat'], $args['lng']);
                $form->setData(
                    [
                        'communeId' => $this->db_manager->get('Sbm\Db\Table\Communes')
                            ->getCommuneId($lieu['commune']),
                        'nom' => implode(' ',
                            [
                                $lieu['numero'],
                                $lieu['rue'],
                                $lieu['lieu-dit']
                            ]),
                        'x' => $point->getX(),
                        'y' => $point->getY()
                    ]);
            } elseif ($isPost2) {
                $form->setData($args);
                if ($form->isValid()) {
                    $table->saveRecord($form->getData());
                    $this->flashMessenger()->addSuccessMessage(
                        "Un nouvel enregistrement a été ajouté.");
                    return $this->redirect()->toRoute('sbmgestion/transport',
                        [
                            'action' => 'station-liste',
                            'page' => $currentPage
                        ]);
                }
            } else {
                $defauts = $this->db_manager->getColumnDefaults('stations', 'table');
                unset($defauts['x'], $defauts['y']);
                $form->setData($defauts);
            }
            $view = new ViewModel(
                [

                    'form' => $form->prepare(),
                    'page' => $currentPage,
                    'stationId' => null
                ]);
            $view->setTemplate('sbm-gestion/transport/station-ajout.phtml');
        } else {
            $formCarte->setAttribute('action',
                $this->url()
                    ->fromRoute('sbmgestion/transport',
                    [
                        'action' => 'station-ajout',
                        'page' => $this->params('page', $currentPage)
                    ]));
            $tStations = $this->db_manager->get('Sbm\Db\Vue\Stations');
            $ptStations = [];
            foreach ($tStations->fetchAll() as $station) {
                $pt = new Point($station->x, $station->y);
                $pt->setAttribute('station', $station);
                $ptStations[] = $oDistanceMatrix->getProjection()->xyzVersgRGF93($pt);
            }
            $view = new ViewModel(
                [

                    'form' => $formCarte->prepare(),
                    'description' => '<b>Nouvelle station</b>',
                    'station' => [
                        'Création d\'une nouvelle station'
                    ],
                    'ptStations' => $ptStations,
                    'url_api' => $this->cartographie_manager->get('google_api_browser')['js'],
                    'config' => $configCarte
                ]);
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
            $args = Session::get('post', [], $this->getSessionNamespace());
        } else {
            $args = $prg;
            Session::set('post', $args, $this->getSessionNamespace());
        }
        $stationId = StdLib::getParam('stationId', $args, - 1);
        if ($stationId == - 1) {
            $this->flashMessenger()->addErrorMessage('Action interdite.');
            return $this->redirect()->toRoute('sbmgestion/transport',
                [
                    'action' => 'station-liste',
                    'page' => $currentPage
                ]);
        }

        return new ViewModel(
            [

                'data' => $this->db_manager->get('Sbm\Db\Eleve\Liste')->queryGroup(
                    Session::get('millesime'), FiltreEleve::byStation($stationId),
                    [
                        'nom',
                        'prenom'
                    ]),
                // 'paginator' => $table_eleves->paginator(),
                'station' => $this->db_manager->get('Sbm\Db\Vue\Stations')->getRecord(
                    $stationId),
                'page' => $currentPage,
                'stationId' => $stationId,
                'origine' => StdLib::getParam('origine', $args)
            ]);
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
            $args = Session::get('post', [], $this->getSessionNamespace());
        } else {
            $args = $prg;
            Session::set('post', $args, $this->getSessionNamespace());
        }
        if ($pageRetour == - 1) {
            $pageRetour = Session::get('pageRetour', 1, $this->getSessionNamespace());
        } else {
            Session::set('pageRetour', $pageRetour, $this->getSessionNamespace());
        }
        $stationId = StdLib::getParam('stationId', $args, - 1);
        if ($stationId == - 1) {
            $circuitId = StdLib::getParam('circuitId', $args, - 1);
            $circuit = $this->db_manager->get('Sbm\Db\Table\Circuits')->getRecord(
                $circuitId);
            if (! empty($circuit)) {
                $stationId = $circuit->stationId;
            }
        }
        if ($stationId == - 1) {
            $this->flashMessenger()->addErrorMessage('Action interdite.');
            return $this->redirect()->toRoute('sbmgestion/transport',
                [
                    'action' => 'station-liste',
                    'page' => $pageRetour
                ]);
        }
        $effectifStationsServices = $this->db_manager->get(
            'Sbm\Db\Eleve\EffectifStationsServices');
        $effectifStationsServices->setCaractereConditionnel($stationId)->init();
        return new ViewModel(
            [

                'data' => $this->db_manager->get('Sbm\Db\Vue\Circuits')->fetchAll(
                    [
                        'millesime' => Session::get('millesime'),
                        'stationId' => $stationId
                    ], 'serviceId'),
                'effectifStationsServices' => $effectifStationsServices,
                'station' => $this->db_manager->get('Sbm\Db\Vue\Stations')->getRecord(
                    $stationId),
                'page' => $currentPage,
                'pageRetour' => $pageRetour,
                'stationId' => $stationId
            ]);
    }

    public function stationServiceGroupAction()
    {
        $currentPage = $this->params('page', 1);
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            $args = Session::get('post', [], $this->getSessionNamespace());
        } else {
            $args = $prg;
            Session::set('post', $args, $this->getSessionNamespace());
        }
        $stationId = StdLib::getParam('stationId', $args, - 1);
        $serviceId = StdLib::getParam('serviceId', $args, false);
        $millesime = Session::get('millesime');
        if ($stationId == - 1 || ! $serviceId) {
            $this->flashMessenger()->addErrorMessage('Action interdite.');
            return $this->redirect()->toRoute('sbmgestion/transport',
                [
                    'action' => StdLib::getParam('origine', $args, 'station-service'),
                    'page' => $currentPage
                ]);
        }
        $circuit = $this->db_manager->get('Sbm\Db\Vue\Circuits')->getRecord(
            [
                'stationId' => $stationId,
                'serviceId' => $serviceId,
                'millesime' => $millesime
            ]);
        $view = new ViewModel(
            [

                'data' => $this->db_manager->get('Sbm\Db\Eleve\Liste')->queryGroup(
                    $millesime, FiltreEleve::byCircuit($serviceId, $stationId, false),
                    [
                        'nom',
                        'prenom'
                    ]),
                'circuit' => $circuit,
                'page' => $currentPage,
                'circuitId' => $circuit->circuitId,
                'origine' => StdLib::getParam('origine', $args, 'station-service')
            ]);
        $view->setTemplate('sbm-gestion/transport/circuit-group.phtml');
        return $view;
    }

    public function stationServiceGroupSelectionAction()
    {
        $query = 'queryGroup';
        $filtre = 'byCircuit';
        $idFields = [
            'serviceId',
            'stationId'
        ];
        $retour = [
            'route' => 'sbmgestion/transport',
            'action' => 'station-service-group'
        ];
        $result = $this->markSelectionEleves($query, $filtre, $idFields, $retour);
        if ($result instanceof ViewModel) {
            $result->setTemplate('sbm-gestion/transport/group-selection.phtml');
        }
        return $result;
    }

    /**
     * envoie un evenement contenant les paramètres de création d'un document pdf (le
     * listener SbmPdf\Listener\PdfListener lancera la création du pdf) Il n'y a pas de
     * vue associée à cette action puisque la response html est créée par \TCPDF
     */
    public function stationPdfAction()
    {
        $criteresObject = [
            'SbmCommun\Model\Db\ObjectData\Criteres',
            [
                'strict' => [
                    'communeId'
                ],
                'expressions' => [
                    'localisation' => 'Literal:' . $this->critereLocalisation('station')
                ]
            ]
        ];
        $criteresForm = [
            'SbmCommun\Form\CriteresForm',
            'stations'
        ];
        $documentId = null;
        $retour = [
            'route' => 'sbmgestion/transport',
            'action' => 'station-liste'
        ];
        return $this->documentPdf($criteresObject, $criteresForm, $documentId, $retour,
            [
                'effectifClassName' => 'Sbm\Db\Eleve\EffectifStations'
            ]);
    }

    /**
     * Demande le document contenant lea services passant par une station
     *
     * @return \Zend\Http\PhpEnvironment\Response|\Zend\Http\Response
     */
    public function stationServicePdfAction()
    {
        $criteresObject = [
            'SbmCommun\Model\Db\ObjectData\Criteres',
            [],
            function ($where, $args) {
                $stationId = StdLib::getParam('stationId', $args);
                $where = new Where();
                return $where->equalTo('stationId', $stationId)->equalTo('millesime',
                    Session::get('millesime'));
            }
        ];
        $criteresForm = 'SbmCommun\Form\CriteresForm';
        $documentId = null;
        $retour = [
            'route' => 'sbmgestion/transport',
            'action' => 'station-service'
        ];
        return $this->documentPdf($criteresObject, $criteresForm, $documentId, $retour,
            [
                'effectifClassName' => 'Sbm\Db\Eleve\EffectifStationsServices',
                'caractereConditionnel' => 'stationId'
            ]);
    }

    /**
     * lance la création d'une liste d'élève avec comme filtre le circuitId reçu en post
     */
    public function stationGroupPdfAction()
    {
        $criteresObject = [
            'SbmCommun\Model\Db\ObjectData\Criteres',
            null,
            function ($where, $args) {
                $stationId = StdLib::getParam('stationId', $args, - 1);
                $where = new Where();
                $where->nest()->equalTo('station1Id', $stationId)->OR->equalTo(
                    'station2Id', $stationId)->unnest();
                return $where;
            }
        ];
        $criteresForm = 'SbmCommun\Form\CriteresForm';
        $documentId = null;
        $retour = [
            'route' => 'sbmgestion/transport',
            'action' => 'station-group'
        ];
        return $this->documentPdf($criteresObject, $criteresForm, $documentId, $retour);
    }

    public function stationGroupSelectionAction()
    {
        $query = 'queryGroup';
        $filtre = 'byStation';
        $idField = 'stationId';
        $retour = [
            'route' => 'sbmgestion/transport',
            'action' => 'station-group'
        ];
        $result = $this->markSelectionEleves($query, $filtre, $idField, $retour);
        if ($result instanceof ViewModel) {
            $result->setTemplate('sbm-gestion/transport/group-selection.phtml');
        }
        return $result;
    }

    /**
     * Localisation d'une station sur la carte et enregistrement de ses coordonnées Toutes
     * les stations sont affichées. La station à localiser est repérée par un bulet rouge.
     */
    public function stationLocalisationAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            $this->flashMessenger()->addWarningMessage('Recommencez.');
            return $this->redirect()->toRoute('sbmgestion/transport',
                [
                    'action' => 'station-liste',
                    'page' => $this->params('page', 1)
                ]);
        } else {
            $args = $prg;
            if (array_key_exists('cancel', $args)) {
                $this->flashMessenger()->addWarningMessage('Localisation abandonnée.');
                return $this->redirect()->toRoute('sbmgestion/transport',
                    [
                        'action' => 'station-liste',
                        'page' => $this->params('page', 1)
                    ]);
            }
            if (! array_key_exists('stationId', $args)) {
                $this->flashMessenger()->addErrorMessage('Action  interdite');
                return $this->redirect()->toRoute('login', [
                    'action' => 'logout'
                ]);
            }
        }
        $oDistanceMatrix = $this->cartographie_manager->get(
            GoogleMaps\DistanceMatrix::class);
        $stationId = $args['stationId'];
        $tStations = $this->db_manager->get('Sbm\Db\Table\Stations');
        // même configuration de carte que pour les etablissements
        $configCarte = StdLib::getParam('station',
            $this->cartographie_manager->get('cartes'));
        $form = new Form\LatLng(
            [
                'stationId' => [
                    'id' => 'stationId'
                ],
                'lat' => [
                    'id' => 'lat'
                ],
                'lng' => [
                    'id' => 'lng'
                ]
            ],
            [
                'submit' => [
                    'class' => 'button default submit left-95px',
                    'value' => 'Enregistrer la localisation'
                ],
                'cancel' => [
                    'class' => 'button default cancel left-10px',
                    'value' => 'Abandonner'
                ]
            ], $configCarte['valide']);
        $form->setAttribute('action',
            $this->url()
                ->fromRoute('sbmgestion/transport',
                [
                    'action' => 'station-localisation',
                    'page' => $this->params('page', 1)
                ]));
        if (array_key_exists('submit', $args)) {
            $form->setData($args);
            if ($form->isValid()) {
                // transforme les coordonnées
                $pt = new Point($args['lng'], $args['lat'], 0, 'degré');
                $point = $oDistanceMatrix->getProjection()->gRGF93versXYZ($pt);
                // enregistre dans la fiche station
                $oData = $tStations->getObjData();
                $oData->exchangeArray(
                    [
                        'stationId' => $stationId,
                        'x' => $point->getX(),
                        'y' => $point->getY()
                    ]);
                $tStations->saveRecord($oData);
                $this->flashMessenger()->addSuccessMessage(
                    'La localisation de la station est enregistrée.');
                // $this->flashMessenger()->addWarningMessage('Attention ! Les distances
                // des
                // domiciles des élèves à l\'établissement n\'ont pas été mises à jour.');
                return $this->redirect()->toRoute('sbmgestion/transport',
                    [
                        'action' => 'station-liste',
                        'page' => $this->params('page', 1)
                    ]);
            }
        }
        $station = $tStations->getRecord($stationId);
        $commune = $this->db_manager->get('Sbm\Db\table\Communes')->getRecord(
            $station->communeId);
        $description = '<b>' . $station->nom . '</b></br>' . $commune->codePostal . ' ' .
            $commune->nom;
        if ($station->x == 0.0 && $station->y == 0.0) {
            // essayer de localiser par l'adresse avant de présenter la carte
            $array = $this->cartographie_manager->get(GoogleMaps\Geocoder::class)->geocode(
                $station->nom, $commune->codePostal, $commune->nom);
            $pt = new Point($array['lng'], $array['lat'], 0, 'degré');
        } else {
            $point = new Point($station->x, $station->y);
            $pt = $oDistanceMatrix->getProjection()->xyzVersgRGF93($point);
        }
        $form->setData(
            [
                'stationId' => $stationId,
                'lat' => $pt->getLatitude(),
                'lng' => $pt->getLongitude()
            ]);
        $tStations = $this->db_manager->get('Sbm\Db\Vue\Stations');
        $ptStations = [];
        foreach ($tStations->fetchAll() as $autreStation) {
            if ($autreStation->stationId != $stationId) {
                $pt = new Point($autreStation->x, $autreStation->y);
                $pt->setAttribute('station', $autreStation);
                $ptStations[] = $oDistanceMatrix->getProjection()->xyzVersgRGF93($pt);
            }
        }
        return new ViewModel(
            [

                'form' => $form->prepare(),
                'description' => $description,
                'station' => [
                    $station->nom,
                    $commune->codePostal . ' ' . $commune->nom
                ],
                'ptStations' => $ptStations,
                'url_api' => $this->cartographie_manager->get('google_api_browser')['js'],
                'config' => $configCarte
            ]);
    }

    /**
     * Suppression d'une station en double
     */
    public function stationDoublonAction()
    {
        $currentPage = $this->params('page', 1);
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        }
        $arg = $prg ?: [];
        $stationsDesservies = $this->db_manager->get('Sbm\Db\Select\Stations')->ouvertes();
        $form = new FormGestion\StationDoublon();
        $form->setValueOptions('stationASupprId', $stationsDesservies)->setValueOptions(
            'stationAGarderId', $stationsDesservies);
        if (array_key_exists('submit', $arg)) {
            $form->setData($arg);
            if ($form->isValid()) {
                // traitement
                $supprDoublon = new StationSupprDoublon($this->db_manager,
                    $arg['stationASupprId'], $arg['stationAGarderId']);
                $cr = $supprDoublon->execute();
                $this->flashMessenger()->addWarningMessage(implode(' ; ', $cr));
                return $this->redirect()->toRoute('sbmgestion/transport',
                    [
                        'action' => 'station-liste',
                        'page' => $currentPage
                    ]);
            }
        } elseif (array_key_exists('cancel', $arg)) {
            return $this->redirect()->toRoute('sbmgestion/transport',
                [
                    'action' => 'station-liste',
                    'page' => $currentPage
                ]);
        }
        return new ViewModel([

            'form' => $form->prepare(),
            'page' => $currentPage
        ]);
    }

    /**
     * ============================= TRANSPORTEURS ============================
     */

    /**
     * Liste des transporteurs (avec pagination)
     *
     * @return ViewModel
     */
    public function transporteurListeAction()
    {
        $args = $this->initListe('transporteurs');
        if ($args instanceof Response)
            return $args;
        $effectifTransporteurs = $this->db_manager->get(
            'Sbm\Db\Eleve\EffectifTransporteurs');
        $effectifTransporteurs->init();
        return new ViewModel(
            [

                'paginator' => $this->db_manager->get('Sbm\Db\Vue\Transporteurs')->paginator(
                    $args['where']),
                'effectifTransporteurs' => $effectifTransporteurs,
                'page' => $this->params('page', 1),
                'count_per_page' => $this->getPaginatorCountPerPage('nb_transporteurs', 15),
                'criteres_form' => $args['form']
            ]);
    }

    /**
     * Modification d'une fiche de transporteur (la validation porte sur un champ csrf)
     *
     * @return ViewModel
     */
    public function transporteurEditAction()
    {
        $currentPage = $this->params('page', 1);
        $form = $this->form_manager->get(Form\Transporteur::class);
        $form->setValueOptions('communeId',
            $this->db_manager->get('Sbm\Db\Select\Communes')
                ->visibles());
        $params = [
            'data' => [
                'table' => 'transporteurs',
                'type' => 'table',
                'alias' => 'Sbm\Db\Table\Transporteurs',
                'id' => 'transporteurId'
            ],
            'form' => $form
        ];

        $r = $this->editData($this->db_manager, $params);
        if ($r instanceof Response) {
            return $r;
        } else {
            switch ($r->getStatus()) {
                case 'error':
                case 'warning':
                case 'success':
                    return $this->redirect()->toRoute('sbmgestion/transport',
                        [
                            'action' => 'transporteur-liste',
                            'page' => $currentPage
                        ]);
                    break;
                default:
                    return new ViewModel(
                        [

                            'form' => $form->prepare(),
                            'page' => $currentPage,
                            'transporteurId' => $r->getResult()
                        ]);
                    break;
            }
        }
    }

    /**
     * Suppression d'une fiche avec confirmation
     *
     * @todo : Vérifier qu'il n'y a pas de service attribué avant de supprimer la fiche
     * @return ViewModel
     */
    public function transporteurSupprAction()
    {
        $currentPage = $this->params('page', 1);
        $form = new Form\ButtonForm([
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
                'alias' => 'Sbm\Db\Table\Transporteurs',
                'id' => 'transporteurId'
            ],
            'form' => $form
        ];
        $vuetransporteurs = $this->db_manager->get('Sbm\Db\Vue\Transporteurs');
        try {
            $r = $this->supprData($this->db_manager, $params,
                function ($id, $tabletransporteurs) use ($vuetransporteurs) {
                    return [
                        'id' => $id,
                        'data' => $vuetransporteurs->getRecord($id)
                    ];
                });
        } catch (\Exception $e) {
            $this->flashMessenger()->addWarningMessage(
                'Impossible de supprimer ce transporteur car il a un service.');
            return $this->redirect()->toRoute('sbmgestion/transport',
                [
                    'action' => 'transporteur-liste',
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
                    return $this->redirect()->toRoute('sbmgestion/transport',
                        [
                            'action' => 'transporteur-liste',
                            'page' => $currentPage
                        ]);
                    break;
                default:
                    return new ViewModel(
                        [

                            'form' => $form->prepare(),
                            'page' => $currentPage,
                            'data' => StdLib::getParam('data', $r->getResult()),
                            'transporteurId' => StdLib::getParam('id', $r->getResult())
                        ]);
                    break;
            }
        }
    }

    /**
     * Ajout d'une nouvelle fiche de transporteur (la validation porte sur un champ csrf)
     *
     * @return ViewModel
     */
    public function transporteurAjoutAction()
    {
        $currentPage = $this->params('page', 1);
        $form = $this->form_manager->get(Form\Transporteur::class);
        $form->setValueOptions('communeId',
            $this->db_manager->get('Sbm\Db\Select\Communes')
                ->visibles());
        $params = [
            'data' => [
                'table' => 'transporteurs',
                'type' => 'table',
                'alias' => 'Sbm\Db\Table\Transporteurs'
            ],
            'form' => $form
        ];
        $r = $this->addData($this->db_manager, $params);
        switch ($r) {
            case $r instanceof Response:
                return $r;
                break;
            case 'error':
            case 'warning':
            case 'success':
                return $this->redirect()->toRoute('sbmgestion/transport',
                    [
                        'action' => 'transporteur-liste',
                        'page' => $currentPage
                    ]);
                break;
            default:
                return new ViewModel(
                    [

                        'form' => $form->prepare(),
                        'page' => $currentPage,
                        'transporteurId' => null
                    ]);
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
            $args = Session::get('post', [], $this->getSessionNamespace());
        } else {
            $args = $prg;
            Session::set('post', $args, $this->getSessionNamespace());
        }
        $currentPage = $this->params('page', 1);
        $pageRetour = $this->params('id', - 1);
        if ($pageRetour == - 1) {
            $pageRetour = Session::get('pageRetour', 1, $this->getSessionNamespace());
        } else {
            Session::set('pageRetour', $pageRetour, $this->getSessionNamespace());
        }
        $transporteurId = StdLib::getParam('transporteurId', $args, - 1);
        if ($transporteurId == - 1) {
            $this->flashMessenger()->addErrorMessage('Action interdite.');
            return $this->redirect()->toRoute('sbmgestion/transport',
                [
                    'action' => 'transporteur-liste',
                    'page' => $pageRetour
                ]);
        }

        return new ViewModel(
            [

                'paginator' => $this->db_manager->get('Sbm\Db\Eleve\Liste')->paginatorGroupParAffectations(
                    Session::get('millesime'),
                    FiltreEleve::byTransporteur($transporteurId),
                    [
                        'serviceId',
                        'nom',
                        'prenom'
                    ]),
                'count_per_page' => $this->getPaginatorCountPerPage('nb_eleves', 15),
                'transporteur' => $this->db_manager->get('Sbm\Db\Table\Transporteurs')->getRecord(
                    $transporteurId),
                'page' => $currentPage,
                'pageRetour' => $pageRetour,
                'transporteurId' => $transporteurId
            ]);
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
            $args = Session::get('post', [], $this->getSessionNamespace());
        } else {
            $args = $prg;
            Session::set('post', $args, $this->getSessionNamespace());
        }
        $currentPage = $this->params('page', 1);
        $pageRetour = $this->params('id', - 1);
        if ($pageRetour == - 1) {
            $pageRetour = Session::get('pageRetour', 1, $this->getSessionNamespace());
        } else {
            Session::set('pageRetour', $pageRetour, $this->getSessionNamespace());
        }
        $transporteurId = StdLib::getParam('transporteurId', $args, - 1);
        if ($transporteurId == - 1) {
            $this->flashMessenger()->addErrorMessage('Action interdite.');
            return $this->redirect()->toRoute('sbmgestion/transport',
                [
                    'action' => 'transporteur-liste',
                    'page' => $pageRetour
                ]);
        }
        $where = new Where();
        $where->equalTo('transporteurId', $transporteurId);
        $effectifTransporteursServices = $this->db_manager->get(
            'Sbm\Db\Eleve\EffectifTransporteursServices');
        $effectifTransporteursServices->setCaractereConditionnel($transporteurId)->init();
        return new ViewModel(
            [

                'paginator' => $this->db_manager->get('Sbm\Db\Table\Services')->paginator(
                    $where, [
                        'serviceId'
                    ]),
                'count_per_page' => 15,
                'effectifTransporteursServices' => $effectifTransporteursServices,
                'transporteur' => $this->db_manager->get('Sbm\Db\Table\Transporteurs')->getRecord(
                    $transporteurId),
                'page' => $currentPage,
                'pageRetour' => $pageRetour,
                'transporteurId' => $transporteurId
            ]);
    }

    /**
     * envoie un evenement contenant les paramètres de création d'un document pdf (le
     * listener SbmPdf\Listener\PdfListener lancera la création du pdf) Il n'y a pas de
     * vue associée à cette action puisque la response html est créée par \TCPDF
     */
    public function transporteurPdfAction()
    {
        $criteresObject = 'SbmCommun\Model\Db\ObjectData\Criteres';
        $criteresForm = [
            'SbmCommun\Form\CriteresForm',
            'transporteurs'
        ];
        $documentId = null;
        $retour = [
            'route' => 'sbmgestion/transport',
            'action' => 'transporteur-liste'
        ];
        return $this->documentPdf($criteresObject, $criteresForm, $documentId, $retour,
            [
                'effectifClassName' => 'Sbm\Db\Eleve\EffectifTransporteurs'
            ]);
    }

    public function transporteurGroupPdfAction()
    {
        $criteresObject = [
            'SbmCommun\Model\Db\ObjectData\Criteres',
            null,
            function ($where, $args) {
                $transporteurId = StdLib::getParam('transporteurId', $args, - 1);
                $where = new Where();
                $where->equalTo('transporteurId', $transporteurId);
                return $where;
            }
        ];
        $criteresForm = 'SbmCommun\Form\CriteresForm';
        $documentId = null;
        $retour = [
            'route' => 'sbmgestion/transport',
            'action' => 'transporteur-group'
        ];
        return $this->documentPdf($criteresObject, $criteresForm, $documentId, $retour);
    }

    /**
     * Demande le document contenant les services d'un transporteur
     *
     * @return \Zend\Http\PhpEnvironment\Response|\Zend\Http\Response
     */
    public function transporteurServicePdfAction()
    {
        $criteresObject = [
            'SbmCommun\Model\Db\ObjectData\Criteres',
            [],
            function ($where, $args) {
                $transporteurId = StdLib::getParam('transporteurId', $args);
                $where = new Where();
                return $where->equalTo('transporteurId', $transporteurId);
            }
        ];
        $criteresForm = 'SbmCommun\Form\CriteresForm';
        $documentId = null;
        $retour = [
            'route' => 'sbmgestion/transport',
            'action' => 'transporteur-service'
        ];
        return $this->documentPdf($criteresObject, $criteresForm, $documentId, $retour,
            [
                'caractereConditionnel' => 'transporteurId',
                'effectifClassName' => 'Sbm\Db\Eleve\EffectifTransporteursServices'
            ]);
    }

    public function transporteurGroupSelectionAction()
    {
        $query = 'queryGroupParAffectations';
        $filtre = 'byTransporteur';
        $idField = 'transporteurId';
        $retour = [
            'route' => 'sbmgestion/transport',
            'action' => 'transporteur-group'
        ];
        $result = $this->markSelectionEleves($query, $filtre, $idField, $retour);
        if ($result instanceof ViewModel) {
            $result->setTemplate('sbm-gestion/transport/group-selection.phtml');
        }
        return $result;
    }
}