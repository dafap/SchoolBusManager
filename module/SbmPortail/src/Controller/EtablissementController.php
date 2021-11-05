<?php
/**
 * Controlleur du portail des établissements
 *
 * @project sbm
 * @package SbmPortail/src/Controller/Service
 * @filesource EtablissementController.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 19 oct. 2021
 * @version 2021-2.6.4
 */
namespace SbmPortail\Controller;

use SbmBase\Model\Session;
use SbmBase\Model\StdLib;
use SbmCommun\Model\Mvc\Controller\AbstractActionController;
use SbmPortail\Form\CriteresEtablissementForm as CriteresForm;
use SbmPortail\Model\Db\ObjectData\CriteresEtablissement as CriteresObject;
use SbmPortail\Model\User\Etablissement as UserFeatures;
use Zend\Db\Sql\Where;
use Zend\Http\PhpEnvironment\Response;
use Zend\Mvc\Controller\Plugin\FlashMessenger;
use Zend\View\Model\ViewModel;

/**
 *
 * @property \SbmCommun\Model\Db\Service\DbManager $db_manager
 * @property \SbmCartographie\ConvertSystemGeodetic\Projection\ProjectionInterface $projection
 * @property array $config_cartes
 * @property string $url_api
 * @property int $categorieId
 * @property int $userId
 *
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 *
 */
class EtablissementController extends AbstractActionController
{
    use \SbmCommun\Model\Traits\ServiceTrait,  \SbmCommun\Model\Traits\DebugTrait;

    private $sansimpayes = true;

    /**
     * Page d'accueil du portail des établissements
     *
     * @return \Zend\Http\Response|\Zend\View\Model\ViewModel
     */
    public function indexAction()
    {
        try {
            $userFeatures = new UserFeatures($this->categorieId, $this->userId,
                $this->db_manager, $this->sansimpayes);
        } catch (\Exception $e) {
            return $this->homePage('Entrée interdite.', FlashMessenger::NAMESPACE_ERROR);
        }
        return new ViewModel(
            [
                'data' => $userFeatures->tableauStatistique(),
                'etablissements' => $userFeatures->listeDesNoms()
            ]);
    }

    /**
     * Présente la carte d'un circuit pour le millesime en cours en mettant en évidence
     * l'une des stations.
     * Reçoit un POST contenant les paramètres :
     * - 'ligneId'
     * - 'sens'
     * - 'moment'
     * - 'ordre'
     * - 'circuitId'
     * - 'stationId' (station à mettre en évidence)
     * - 'origine'
     * - 'action'
     *
     * @return \Zend\Http\PhpEnvironment\Response|\Zend\Http\Response|\Zend\View\Model\ViewModel
     */
    public function carteCircuitAction()
    {
        // reprendre le code de carteStationsAction() en prenant toutes les stations du
        // circuit et en mettant en évidence la station correspondante à stationId reçu en
        // POST
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif (array_key_exists('cancel', (array) $prg)) {
            try {
                return $this->redirectToOrigin()->back();
            } catch (\SbmCommun\Model\Mvc\Controller\Plugin\Exception\ExceptionInterface $e) {
                return $this->homePage();
            }
        } elseif (array_key_exists('origine', (array) $prg)) {
            $this->redirectToOrigin()->setBack($prg['origine']);
            unset($prg['origine']);
            Session::set('post', $prg, $this->getSessionNamespace());
        } elseif (! $prg) {
            $prg = Session::get('post', [], $this->getSessionNamespace());
        }
        try {
            $userFeatures = new UserFeatures($this->categorieId, $this->userId,
                $this->db_manager, $this->sansimpayes);
        } catch (\Exception $e) {
            $this->flashMessenger()->addErrorMessage('Entrée interdite.');
            try {
                return $this->redirectToOrigin()->back();
            } catch (\SbmCommun\Model\Mvc\Controller\Plugin\Exception\ExceptionInterface $e) {
                return $this->homePage();
            }
        }
        $viewmodel = new ViewModel(
            [
                'scheme' => $this->getRequest()
                    ->getUri()
                    ->getScheme(),
                'ptStations' => $userFeatures->getQuery()
                    ->setProjection($this->projection)
                    ->circuitPourCarte($prg['ligneId'], $prg['sens'], $prg['moment'],
                    $prg['ordre'], $prg['stationId']),
                'config' => StdLib::getParam('station', $this->config_cartes),
                'url_api' => $this->url_api,
                'designation' => $this->identifiantService($prg)
            ]);
        $viewmodel->setTemplate('sbm-cartographie/carte/circuit.phtml');
        return $viewmodel;
    }

    public function carteEtablissementsAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif (array_key_exists('cancel', (array) $prg)) {
            return $this->homePage();
        }
        try {
            $userFeatures = new UserFeatures($this->categorieId, $this->userId,
                $this->db_manager, $this->sansimpayes);
        } catch (\Exception $e) {
            return $this->homePage('Entrée interdite.', FlashMessenger::NAMESPACE_ERROR);
        }
        $viewmodel = new ViewModel(
            [
                'scheme' => $this->getRequest()
                    ->getUri()
                    ->getScheme(),
                'ptEtablissements' => $this->db_manager->get(
                    'Sbm\Portail\Etablissement\Query')
                    ->setProjection($this->projection)
                    ->setEtablissementId($userFeatures->getEtablissementIds())
                    ->etablissementsPourCarte(),
                'config' => StdLib::getParam('etablissement', $this->config_cartes),
                'url_api' => $this->url_api
            ]);
        $viewmodel->setTemplate('sbm-cartographie/carte/etablissements.phtml');
        return $viewmodel;
    }

    public function carteStationsAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif (array_key_exists('cancel', (array) $prg)) {
            return $this->homePage();
        }
        try {
            $userFeatures = new UserFeatures($this->categorieId, $this->userId,
                $this->db_manager, $this->sansimpayes);
        } catch (\Exception $e) {
            return $this->homePage('Entrée interdite.', FlashMessenger::NAMESPACE_ERROR);
        }
        $viewmodel = new ViewModel(
            [
                'scheme' => $this->getRequest()
                    ->getUri()
                    ->getScheme(),
                'ptStations' => $this->db_manager->get('Sbm\Portail\Etablissement\Query')
                    ->setProjection($this->projection)
                    ->setEtablissementId($userFeatures->getEtablissementIds())
                    ->stationsPourCarte(),
                'config' => StdLib::getParam('station', $this->config_cartes),
                'url_api' => $this->url_api
            ]);
        $viewmodel->setTemplate('sbm-cartographie/carte/stations.phtml');
        return $viewmodel;
    }

    public function lignesAction()
    {
        $args = $this->initListe('lignes');
        if ($args instanceof Response) {
            return $args;
        } elseif (array_key_exists('cancel', $args)) {
            $this->homePage();
        }
        try {
            $userFeatures = new UserFeatures($this->categorieId, $this->userId,
                $this->db_manager, $this->sansimpayes);
        } catch (\Exception $e) {
            return $this->homePage('Entrée interdite.', FlashMessenger::NAMESPACE_ERROR);
        }
        $millesime = Session::get('millesime');
        $as = $millesime . '-' . ($millesime + 1);
        $critere_form = $args['form'];
        $critere_form->remove('lotId')->remove('selection');
        return new ViewModel(
            [
                'paginator' => $userFeatures->getQuery()->paginatorLignes($args['where'],
                    [
                        'actif DESC',
                        'ligneId'
                    ]),
                'page' => $this->params('page', 1),
                'count_per_page' => $this->getPaginatorCountPerPage('nb_lignes', 15),
                'criteres_form' => $critere_form,
                'millesime' => $millesime,
                'as' => $as,
                'etablissement' => $userFeatures->listeDesNoms()
            ]);
    }

    public function lignesPdfAction()
    {
        $documentId = $this->getRequest()->getPost('documentId');
        try {
            $userFeatures = new UserFeatures($this->categorieId, $this->userId,
                $this->db_manager, $this->sansimpayes);
        } catch (\Exception $e) {
            return $this->homePage('Entrée interdite.', FlashMessenger::NAMESPACE_ERROR);
        }
        $criteres_form = new \SbmCommun\Form\CriteresForm('lignes');
        $criteres = Session::get('post', [],
            str_replace('_pdf', '', $this->getSessionNamespace()));
        $criteres_obj = new \SbmCommun\Model\Db\ObjectData\Criteres(
            $criteres_form->getElementNames());
        $criteres_obj->exchangeArray($criteres);
        $data = [];
        foreach ($userFeatures->getQuery()->listeLignes($criteres_obj->getWhere(),
            [
                'actif DESC',
                'ligneId'
            ]) as $record) {
            $data[] = $this->exportLignes($record);
        }
        $this->RenderPdfService->setParam('documentId', $documentId)
            ->setParam('docaffectationId', $this->params('id', false))
            ->setParam('layout', 'sbm-portail/layout/lignes.phtml')
            ->setData($data)
            ->setEndOfScriptFunction(
            function () {
                $this->flashMessenger()
                    ->addSuccessMessage("Création d'un pdf.");
            })
            ->renderPdf();
    }

    public function lignesDownloadAction()
    {
        try {
            $userFeatures = new UserFeatures($this->categorieId, $this->userId,
                $this->db_manager, $this->sansimpayes);
        } catch (\Exception $e) {
            return $this->homePage('Entrée interdite.', FlashMessenger::NAMESPACE_ERROR);
        }
        $criteres_form = new \SbmCommun\Form\CriteresForm('lignes');
        $criteres = Session::get('post', [],
            str_replace('_download', '', $this->getSessionNamespace()));
        $criteres_obj = new \SbmCommun\Model\Db\ObjectData\Criteres(
            $criteres_form->getElementNames());
        $criteres_obj->exchangeArray($criteres);
        $data = [];
        foreach ($userFeatures->getQuery()->listeLignes($criteres_obj->getWhere(),
            [
                'actif DESC',
                'ligneId'
            ]) as $record) {
            $data[] = $this->exportLignes($record);
        }
        return $this->xlsxExport('lignes', $this->exportLignesDescriptor(), $data, null,
            [], 'Lignes');
    }

    private function exportLignes($record)
    {
        return [
            'ligneId' => $record['ligneId'],
            'operateur' => $record['operateur'],
            'depart' => $record['extremite1'],
            'terminus' => $record['extremite2'],
            'via' => $record['via'],
            'internes' => $record['internes'] ? 'Oui' : 'Non'
        ];
    }

    private function exportLignesDescriptor()
    {
        return [
            [
                'label' => 'Ligne',
                'autosize' => true
            ],
            [
                'label' => 'Opérateur',
                'autosize' => true
            ],
            [
                'label' => 'Départ',
                'autosize' => true
            ],
            [
                'label' => 'Terminus',
                'autosize' => true
            ],
            [
                'label' => 'Via',
                'autosize' => true
            ],
            [
                'label' => 'Réservée aux internes',
                'autosize' => true
            ]
        ];
    }

    public function servicesAction()
    {
        $args = $this->initListe('services',
            function ($config, $form, $args) {
                $form->remove('ligneId')
                    ->remove('selection');
                $form->add([
                    'name' => 'ligneId',
                    'type' => 'hidden'
                ]);
                $form->setValueOptions('transporteurId',
                    $config['db_manager']->get('Sbm\Db\Select\Transporteurs'));
            }, [
                'transporteurId'
            ], null, function ($post) {
                return [
                    'ligneId' => $post['ligneId']
                ];
            });
        if ($args instanceof Response) {
            return $args;
        } elseif (array_key_exists('cancel', $args)) {
            $this->redirectToOrigin()->reset();
            return $this->redirect()->toRoute('sbmportail/communes',
                [
                    'action' => 'lignes',
                    'page' => $this->params('page', 1)
                ]);
        }
        $millesime = Session::get('millesime');
        $as = $millesime . '-' . ($millesime + 1);
        $ligneId = $args['post']['ligneId'];
        $args['where']->equalTo('millesime', $millesime)->equalTo('ligneId', $ligneId);
        $effectifServices = $this->db_manager->get('Sbm\Db\Eleve\EffectifServices');
        $effectifServices->init($this->sansimpayes);
        return new ViewModel(
            [
                'paginator' => $this->db_manager->get('Sbm\Db\Vue\Services')->paginator(
                    $args['where']),
                'count_per_page' => $this->getPaginatorCountPerPage('nb_services', 15),
                'page' => $this->params('page', 1),
                'as' => $as,
                'ligneId' => $ligneId,
                'effectifServices' => $effectifServices,
                'criteres_form' => $args['form']
            ]);
    }

    public function servicesPdfAction()
    {
        $documentId = $this->getRequest()->getPost('documentId');
        $criteres_form = new \SbmCommun\Form\CriteresForm('services');
        $criteres = Session::get('post', [],
            str_replace('_pdf', '', $this->getSessionNamespace()));
        $criteres['millesime'] = Session::get('millesime');
        $criteres_obj = new \SbmCommun\Model\Db\ObjectData\Criteres(
            $criteres_form->getElementNames());
        $criteres_obj->exchangeArray($criteres);
        $where = $criteres_obj->getWhere([
            'ligneId',
            'sens',
            'moment',
            'ordre'
        ])->equalTo('millesime', Session::get('millesime'));
        $effectifServices = $this->db_manager->get('Sbm\Db\Eleve\EffectifServices');
        $effectifServices->init($this->sansimpayes);
        $data = [];
        foreach ($this->db_manager->get('Sbm\Db\Vue\Services')->fetchAll($where) as $record) {
            $data[] = $this->exportServices($record, $effectifServices);
        }
        $this->RenderPdfService->setParam('documentId', $documentId)
            ->setParam('docaffectationId', $this->params('id', false))
            ->setParam('layout', 'sbm-portail/layout/services.phtml')
            ->setData($data)
            ->setEndOfScriptFunction(
            function () {
                $this->flashMessenger()
                    ->addSuccessMessage("Création d'un pdf.");
            })
            ->renderPdf();
    }

    public function servicesDownloadAction()
    {
        $criteres_form = new \SbmCommun\Form\CriteresForm('services');
        $criteres = Session::get('post', [],
            str_replace('_download', '', $this->getSessionNamespace()));
        $criteres['millesime'] = Session::get('millesime');
        $criteres_obj = new \SbmCommun\Model\Db\ObjectData\Criteres(
            $criteres_form->getElementNames());
        $criteres_obj->exchangeArray($criteres);
        $where = $criteres_obj->getWhere([
            'ligneId',
            'sens',
            'moment',
            'ordre'
        ])->equalTo('millesime', Session::get('millesime'));
        $effectifServices = $this->db_manager->get('Sbm\Db\Eleve\EffectifServices');
        $effectifServices->init($this->sansimpayes);
        $data = [];
        foreach ($this->db_manager->get('Sbm\Db\Vue\Services')->fetchAll($where) as $record) {
            $data[] = $this->exportServices($record, $effectifServices);
        }
        $sheet_title = 'Ligne ' . $criteres['ligneId'];
        return $this->xlsxExport('services', $this->exportServicesDescriptor(), $data,
            null, [], $sheet_title);
    }

    private function exportServices($record, $effectifServices)
    {
        return [
            'service' => $this->identifiantService($record->getArrayCopy()),
            'jours' => $record->jours,
            'transporteur' => $record->transporteur,
            'nbPlaces' => $record->nbPlaces,
            'nbInscrits' => $effectifServices->transportes($record->ligneId, $record->sens,
                $record->moment, $record->ordre)
        ];
    }

    private function exportServicesDescriptor()
    {
        return [
            [
                'label' => 'Service',
                'autosize' => true
            ],
            [
                'label' => 'Jours',
                'autosize' => true
            ],
            [
                'label' => 'Transporteur',
                'autosize' => true
            ],
            [
                'label' => 'Capacité',
                'autosize' => true
            ],
            [
                'label' => 'Inscrits',
                'autosize' => true
            ]
        ];
    }

    /**
     * Liste des élèves inscrits sur ce service (en tenant compte du paramétrage de la
     * propriété 'sansimpayes' de ce controler).
     * Reçoit en GET le paramètre :
     * - page (optionnel, du paginateur)
     * Reçoit en POST les paramètres :
     * - ligneId
     * - sens
     * - moment
     * - ordre
     * - origine (url sur la bonne page de la liste des services)
     *
     * @return \Zend\Http\PhpEnvironment\Response|\Zend\View\Model\ViewModel
     */
    public function serviceGroupAction()
    {
        $result = $this->prepareListeEleves('service-group',
            function () {
                return $this->redirect()
                    ->toRoute('sbmportail/etablissement',
                    [
                        'action' => 'services',
                        'page' => $this->params('id')
                    ]);
            });
        if ($result instanceof Response) {
            return $result;
        }
        $form = $result['criteres_form'];
        $form->remove('serviceId');
        $form->get('stationId')->setLabelAttributes([
            'class' => 'sbm-new-line'
        ]);
        $where = $result['criteres_obj']->getWhere();
        $this->adaptWhereForServiceGroup($result['post'], $where);
        $paginator = $result['userFeatures']->getQuery()->paginatorEleves($where,
            [
                'ele.nom',
                'ele.prenom'
            ]);
        $view = new ViewModel(
            [
                'paginator' => $paginator,
                'count_per_page' => $this->getPaginatorCountPerPage('nb_eleves', 10),
                'page' => $this->params('page', 1),
                'etablissement' => $result['userFeatures']->listeDesNoms(),
                'criteres_form' => $result['criteres_form'],
                'sansimpayes' => $this->sansimpayes,
                'ns' => 'service-group',
                'subtitle' => 'Liste des élèves inscrits sur le circuit ' .
                $result['post']['designation']
            ]);
        $view->setTemplate('sbm-portail/etablissement/eleves.phtml');
        return $view;
    }

    public function serviceGroupPdfAction()
    {
        $result = $this->prepareListeEleves('service-group',
            function () {
                return $this->redirect()
                    ->toRoute('sbmportail/etablissement', [
                    'action' => 'index'
                ]);
            });
        if ($result instanceof Response) {
            return $result;
        }
        $where = $result['criteres_obj']->getWhere();
        $this->adaptWhereForServiceGroup($result['post'], $where);
        $data = [];
        foreach ($result['userFeatures']->getQuery()->listeEleves($where,
            [
                'ele.nom',
                'ele.prenom'
            ]) as $record) {
            $data[] = array_map('nl2br', $this->exportEleve($record));
        }
        $this->RenderPdfService->setParam('documentId', $result['post']['documentId'])
            ->setParam('docaffectationId', $this->params('id', false))
            ->setParam('layout', 'sbm-portail/layout/eleves.phtml')
            ->setData($data)
            ->setEndOfScriptFunction(
            function () {
                $this->flashMessenger()
                    ->addSuccessMessage("Création d'un pdf.");
            })
            ->renderPdf();
    }

    public function serviceGroupDownloadAction()
    {
        $result = $this->prepareListeEleves('service-group',
            function () {
                return $this->redirect()
                    ->toRoute('sbmportail/etablissement', [
                    'action' => 'index'
                ]);
            });
        if ($result instanceof Response) {
            return $result;
        }
        $where = $result['criteres_obj']->getWhere();
        $this->adaptWhereForServiceGroup($result['post'], $where);
        $data = [];
        foreach ($result['userFeatures']->getQuery()->listeEleves($where,
            [
                'ele.nom',
                'ele.prenom'
            ]) as $record) {
            $data[] = $this->exportEleve($record);
        }
        return $this->xlsxExport('eleve', $this->exportEleveDescriptor(), $data, null, [],
            'Eleves');
    }

    /**
     * Adapte le where pour les ServiceGroup (liste, pdf, download)
     *
     * @param array $params
     *            c'est le post obtenu après envoi du formulaire de critères
     * @param \Zend\Db\Sql\Where $where
     */
    private function adaptWhereForServiceGroup(array $params, Where $where)
    {
        $where->nest()
            ->nest()
            ->equalTo('affR1.ligne1Id', $params['ligneId'])
            ->equalTo('affR1.sensligne1', $params['sens'])
            ->equalTo('affR1.moment', $params['moment'])
            ->equalTo('affR1.ordreligne1', $params['ordre'])
            ->unnest()->or->nest()
            ->equalTo('affR2.ligne1Id', $params['ligneId'])
            ->equalTo('affR2.sensligne1', $params['sens'])
            ->equalTo('affR2.moment', $params['moment'])
            ->equalTo('affR2.ordreligne1', $params['ordre'])
            ->unnest()
            ->unnest();
    }

    /**
     * Horaire d'un circuit avec effectifs.
     * Reçoit en GET le paramètre :
     * - page (optionnel, du paginateur)
     * Reçoit en POST les paramètres :
     * - ligneId
     * - sens
     * - moment
     * - ordre
     * - origine (url sur la bonne page de la liste des services)
     *
     * @return \Zend\Http\PhpEnvironment\Response|\Zend\Http\Response|\Zend\View\Model\ViewModel
     */
    public function circuitAction()
    {
        $args = $this->initListe('circuits',
            function ($config, $form, $args) {
                $form->remove('selection');
            }, [
                'ligneId',
                'sens',
                'moment',
                'ordre',
                'horaireA'
            ], null,
            function ($post) {
                if (array_key_exists('ligneId', $post)) {
                    return [
                        'ligneId' => $post['ligneId'],
                        'sens' => $post['sens'],
                        'moment' => $post['moment'],
                        'ordre' => $post['ordre']
                    ];
                } else {
                    return [];
                }
            });
        if ($args instanceof Response) {
            return $args;
        } elseif (array_key_exists('cancel', $args)) {
            // ne devrait pas se produire puisque initListe gère le paramètre 'origine'
            $this->redirectToOrigin()->reset();
            return $this->redirect()->toRoute('sbmportail/etablissement');
        }
        try {
            $userFeatures = new UserFeatures($this->categorieId, $this->userId,
                $this->db_manager, $this->sansimpayes);
        } catch (\Exception $e) {
            return $this->homePage('Entrée interdite.', FlashMessenger::NAMESPACE_ERROR);
        }
        $millesime = Session::get('millesime');
        $args['post']['millesime'] = $millesime; // nécessaire pour
                                                 // tableService->getRecord()
        if (array_key_exists('ligneId', $args['post'])) {
            $args['where']->equalTo('ligneId', $args['post']['ligneId'])
                ->equalTo('sens', $args['post']['sens'])
                ->equalTo('moment', $args['post']['moment'])
                ->equalTo('ordre', $args['post']['ordre']);
        }
        // mise en place du calcul d'effectif
        $effectifCircuits = $this->db_manager->get('Sbm\Db\Eleve\EffectifCircuits');
        $effectifCircuits->setSanspreinscrits($this->sansimpayes)
            ->setMillesime($millesime)
            ->setLigneId($args['post']['ligneId'])
            ->setSens($args['post']['sens'])
            ->setMoment($args['post']['moment'])
            ->setOrdre($args['post']['ordre'])
            ->init();
        return new ViewModel(
            [
                'paginator' => $userFeatures->getQuery()->paginatorCircuits(
                    $args['where'], [
                        'horaireD',
                        'horaireA'
                    ]),
                'effectifCircuits' => $effectifCircuits,
                'page' => $this->params('page', 1),
                'count_per_page' => $this->getPaginatorCountPerPage('nb_circuits', 20),
                'criteres_form' => $args['form'],
                'service' => $this->db_manager->get('Sbm\Db\Table\Services')->getRecord(
                    $args['post']),
                'arrayEtablissementIds' => $userFeatures->getEtablissementIds()
            ]);
    }

    public function circuitPdfAction()
    {
        $documentId = $this->getRequest()->getPost('documentId');
        try {
            $userFeatures = new UserFeatures($this->categorieId, $this->userId,
                $this->db_manager, $this->sansimpayes);
        } catch (\Exception $e) {
            return $this->homePage('Entrée interdite.', FlashMessenger::NAMESPACE_ERROR);
        }
        $millesime = Session::get('millesime');
        $criteres_form = new \SbmCommun\Form\CriteresForm('circuits');
        $criteres = Session::get('post', [],
            str_replace('_pdf', '', $this->getSessionNamespace()));
        $criteres_obj = new \SbmCommun\Model\Db\ObjectData\Criteres(
            $criteres_form->getElementNames());
        $criteres_obj->exchangeArray($criteres);
        $effectifCircuits = $this->db_manager->get('Sbm\Db\Eleve\EffectifCircuits');
        $effectifCircuits->setSanspreinscrits($this->sansimpayes)
            ->setMillesime($millesime)
            ->setLigneId($criteres['ligneId'])
            ->setSens($criteres['sens'])
            ->setMoment($criteres['moment'])
            ->setOrdre($criteres['ordre'])
            ->init();
        $data = [];
        foreach ($userFeatures->getQuery()->listeCircuits(
            $criteres_obj->getWhere([
                'ligneId',
                'sens',
                'moment',
                'ordre'
            ])) as $record) {
            $data[] = $this->exportCircuit($record, $effectifCircuits);
        }
        $this->RenderPdfService->setParam('documentId', $documentId)
            ->setParam('docaffectationId', $this->params('id', false))
            ->setParam('layout', 'sbm-portail/layout/circuits.phtml')
            ->setData($data)
            ->setEndOfScriptFunction(
            function () {
                $this->flashMessenger()
                    ->addSuccessMessage("Création d'un pdf.");
            })
            ->renderPdf();
    }

    public function circuitDownloadAction()
    {
        try {
            $userFeatures = new UserFeatures($this->categorieId, $this->userId,
                $this->db_manager, $this->sansimpayes);
        } catch (\Exception $e) {
            return $this->homePage('Entrée interdite.', FlashMessenger::NAMESPACE_ERROR);
        }
        $millesime = Session::get('millesime');
        $criteres_form = new \SbmCommun\Form\CriteresForm('circuits');
        $criteres = Session::get('post', [],
            str_replace('_download', '', $this->getSessionNamespace()));
        $criteres_obj = new \SbmCommun\Model\Db\ObjectData\Criteres(
            $criteres_form->getElementNames());
        $criteres_obj->exchangeArray($criteres);
        $effectifCircuits = $this->db_manager->get('Sbm\Db\Eleve\EffectifCircuits');
        $effectifCircuits->setSanspreinscrits($this->sansimpayes)
            ->setMillesime($millesime)
            ->setLigneId($criteres['ligneId'])
            ->setSens($criteres['sens'])
            ->setMoment($criteres['moment'])
            ->setOrdre($criteres['ordre'])
            ->init();
        $data = [];
        foreach ($userFeatures->getQuery()->listeCircuits(
            $criteres_obj->getWhere([
                'ligneId',
                'sens',
                'moment',
                'ordre'
            ])) as $record) {
            $data[] = $this->exportCircuit($record, $effectifCircuits);
        }
        $sheet_title = substr($this->identifiantService($criteres), 0, 31);
        return $this->xlsxExport('circuit', $this->exportCircuitDescriptor(), $data, null,
            [], $sheet_title);
    }

    private function exportCircuit($record, $effectifCircuits)
    {
        $oSemaine = new \SbmCommun\Model\Strategy\Semaine();
        $jours = $oSemaine->renderSemaine($record['semaine']);
        $nbInscrits = $effectifCircuits->transportes($record['circuitId']);
        return [
            'commune' => $record['lacommuneStation'],
            'station' => $record['station'],
            'emplacement' => $record['emplacement'],
            'horaireA' => (new \DateTime($record['horaireA']))->format('H:i'),
            'horaireD' => (new \DateTime($record['horaireD']))->format('H:i'),
            'semaine' => $jours,
            'montee' => $nbInscrits['montee'],
            'descente' => $nbInscrits['descente'],
            'effectif' => $nbInscrits['effectif_reel']
        ];
    }

    private function exportCircuitDescriptor()
    {
        return [
            [
                'label' => 'Commune',
                'autosize' => true
            ],
            [
                'label' => 'Point d\'arrêt',
                'autosize' => true
            ],
            [
                'label' => 'Emplacement',
                'autosize' => true
            ],
            [
                'label' => 'Arrivée',
                'autosize' => true
            ],
            [
                'label' => 'Départ',
                'autosize' => true
            ],
            [
                'label' => 'Jours',
                'autosize' => true
            ],
            [
                'label' => 'Nb entrants',
                'autosize' => true
            ],
            [
                'label' => 'Nb sortants',
                'autosize' => true
            ],
            [
                'label' => 'Effectif',
                'autosize' => true
            ]
        ];
    }

    /**
     * Liste des élèves d'une station de ce circuit.
     * Reçoit en GET le paramètre :
     * - 'page' (provenant du paginateur)
     * et en POST les paramètres :
     * - 'ligneId'
     * - 'sens'
     * - 'moment'
     * - 'ordre'
     * - 'circuitId'
     * - 'stationId' (station à mettre en évidence)
     * - 'origine'
     * - 'action'
     *
     * @return \Zend\Http\PhpEnvironment\Response|\Zend\View\Model\ViewModel
     */
    public function circuitGroupAction()
    {
        $result = $this->prepareListeEleves('circuit-group',
            function () {
                return $this->redirect()
                    ->toRoute('sbmportail/etablissement',
                    [
                        'action' => 'circuit',
                        'page' => $this->params('id')
                    ]);
            });
        if ($result instanceof Response) {
            return $result;
        }
        $form = $result['criteres_form'];
        $form->remove('serviceId')->remove('stationId');
        $form->get('numero')->setLabelAttributes([
            'class' => 'sbm-new-line'
        ]);
        $where = $result['criteres_obj']->getWhere();
        $this->adaptWhereForCircuitGroup($result['post']['circuitId'], $where);
        $paginator = $result['userFeatures']->getQuery()->paginatorEleves($where,
            [
                'ele.nom',
                'ele.prenom'
            ]);
        $view = new ViewModel(
            [
                'paginator' => $paginator,
                'count_per_page' => $this->getPaginatorCountPerPage('nb_eleves', 10),
                'page' => $this->params('page', 1),
                'criteres_form' => $form,
                'etablissement' => $result['userFeatures']->listeDesNoms(),
                'sansimpayes' => $this->sansimpayes,
                'ns' => 'circuit-group',
                'subtitle' => 'Liste des élèves inscrits sur le circuit ' .
                $result['post']['designation'] . ' à l\'arrêt ' .
                $result['post']['station']
            ]);
        $view->setTemplate('sbm-portail/etablissement/eleves.phtml');
        return $view;
    }

    public function circuitGroupPdfAction()
    {
        $result = $this->prepareListeEleves('circuit-group',
            function () {
                return $this->redirect()
                    ->toRoute('sbmportail/etablissement', [
                    'action' => 'index'
                ]);
            });
        if ($result instanceof Response) {
            return $result;
        }
        $where = $result['criteres_obj']->getWhere();
        $this->adaptWhereForCircuitGroup($result['post']['circuitId'], $where);
        $data = [];
        foreach ($result['userFeatures']->getQuery()->listeEleves($where,
            [
                'ele.nom',
                'ele.prenom'
            ]) as $record) {
            $data[] = array_map('nl2br', $this->exportEleve($record));
        }
        $this->RenderPdfService->setParam('documentId', $result['post']['documentId'])
            ->setParam('docaffectationId', $this->params('id', false))
            ->setParam('layout', 'sbm-portail/layout/eleves.phtml')
            ->setData($data)
            ->setEndOfScriptFunction(
            function () {
                $this->flashMessenger()
                    ->addSuccessMessage("Création d'un pdf.");
            })
            ->renderPdf();
    }

    public function circuitGroupDownloadAction()
    {
        $result = $this->prepareListeEleves('circuit-group',
            function () {
                return $this->redirect()
                    ->toRoute('sbmportail/etablissement', [
                    'action' => 'index'
                ]);
            });
        if ($result instanceof Response) {
            return $result;
        }
        $where = $result['criteres_obj']->getWhere();
        $this->adaptWhereForCircuitGroup($result['post']['circuitId'], $where);

        $data = [];
        foreach ($result['userFeatures']->getQuery()->listeEleves($where,
            [
                'ele.nom',
                'ele.prenom'
            ]) as $record) {
            $data[] = $this->exportEleve($record);
        }
        return $this->xlsxExport('eleve', $this->exportEleveDescriptor(), $data, null, [],
            'Eleves');
    }

    /**
     * Adapte le where pour les CircuitGroup (liste, pdf, download)
     *
     * @param int $circuitId
     * @param \Zend\Db\Sql\Where $where
     */
    private function adaptWhereForCircuitGroup(int $circuitId, Where $where)
    {
        $circuit = $this->db_manager->get('Sbm\Db\Table\Circuits')->getRecord($circuitId);
        $montee = $circuit->moment != 2 && $circuit->moment != 3;
        $clauseStation = $montee ? 'station1Id' : 'station2Id';
        $where->nest()
            ->nest()
            ->equalTo('affR1.ligne1Id', $circuit->ligneId)
            ->equalTo('affR1.sensligne1', $circuit->sens)
            ->equalTo('affR1.moment', $circuit->moment)
            ->equalTo('affR1.ordreligne1', $circuit->ordre)
            ->equalTo("affR1.$clauseStation", $circuit->stationId)
            ->unnest()->or->nest()
            ->equalTo('affR2.ligne1Id', $circuit->ligneId)
            ->equalTo('affR2.sensligne1', $circuit->sens)
            ->equalTo('affR2.moment', $circuit->moment)
            ->equalTo('affR2.ordreligne1', $circuit->ordre)
            ->equalTo("affR2.$clauseStation", $circuit->stationId)
            ->unnest()
            ->unnest();
    }

    public function elevesAction()
    {
        $result = $this->prepareListeEleves('eleves',
            function () {
                return $this->redirect()
                    ->toRoute('sbmportail/etablissement', [
                    'action' => 'index'
                ]);
            });
        if ($result instanceof Response) {
            return $result;
        }
        $paginator = $result['userFeatures']->getQuery()->paginatorEleves(
            $result['criteres_obj']->getWhere(), [
                'ele.nom',
                'ele.prenom'
            ]);
        return new ViewModel(
            [
                'paginator' => $paginator,
                'count_per_page' => $this->getPaginatorCountPerPage('nb_eleves', 10),
                'page' => $this->params('page', 1),
                'etablissement' => $result['userFeatures']->listeDesNoms(),
                'nbEtablissements' => $result['userFeatures']->getNbEtablissements(),
                'criteres_form' => $result['criteres_form'],
                'sansimpayes' => $this->sansimpayes,
                'ns' => 'eleves'
            ]);
    }

    public function elevesPdfAction()
    {
        $result = $this->prepareListeEleves('eleves',
            function () {
                return $this->redirect()
                    ->toRoute('sbmportail/etablissement', [
                    'action' => 'index'
                ]);
            });
        if ($result instanceof Response) {
            return $result;
        }
        $data = [];
        foreach ($result['userFeatures']->getQuery()->listeEleves(
            $result['criteres_obj']->getWhere(), [
                'ele.nom',
                'ele.prenom'
            ]) as $record) {
            $data[] = array_map('nl2br', $this->exportEleve($record));
        }
        $this->RenderPdfService->setParam('documentId', $result['post']['documentId'])
            ->setParam('docaffectationId', $this->params('id', false))
            ->setParam('layout', 'sbm-portail/layout/eleves.phtml')
            ->setData($data)
            ->setEndOfScriptFunction(
            function () {
                $this->flashMessenger()
                    ->addSuccessMessage("Création d'un pdf.");
            })
            ->renderPdf();
    }

    public function elevesDownloadAction()
    {
        // @TODO à finir
        $result = $this->prepareListeEleves('eleves',
            function () {
                return $this->redirect()
                    ->toRoute('sbmportail/etablissement', [
                    'action' => 'index'
                ]);
            });
        if ($result instanceof Response) {
            return $result;
        }
        $data = [];
        foreach ($result['userFeatures']->getQuery()->listeEleves(
            $result['criteres_obj']->getWhere(), [
                'ele.nom',
                'ele.prenom'
            ]) as $record) {
            $data[] = $this->exportEleve($record);
        }
        return $this->xlsxExport('eleve',
            [
                [
                    'label' => 'Numéro',
                    'autosize' => true
                ],
                [
                    'label' => 'Nom',
                    'autosize' => true
                ],
                [
                    'label' => 'Prénom',
                    'autosize' => true
                ],
                [
                    'label' => 'Etablissement',
                    'autosize' => true
                ],
                [
                    'label' => 'Classe',
                    'autosize' => true
                ],
                [
                    'label' => 'Responsable1',
                    'autosize' => true
                ],
                [
                    'label' => 'Adresse1',
                    'width' => 41,
                    'wraptext' => true
                ],
                [
                    'label' => 'Circuits1',
                    'width' => 120,
                    'wraptext' => true
                ],
                [
                    'label' => 'Responsable2',
                    'autosize' => true
                ],
                [
                    'label' => 'Adresse2',
                    'width' => 41,
                    'wraptext' => true
                ],
                [
                    'label' => 'Circuits2',
                    'width' => 120,
                    'wraptext' => true
                ]
            ], $data, null, [], 'Eleves');
    }

    private function prepareListeEleves(string $sessionNameSpace, callable $fncBack)
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false || StdLib::getParam('op', $prg, '') == 'retour') {
            $sbm_isPost = false;
            $args = Session::get('post', [],
                $this->getSessionNamespace($sessionNameSpace));
        } else {
            if (array_key_exists('cancel', $prg)) {
                try {
                    return $this->redirectToOrigin()->back();
                } catch (\SbmCommun\Model\Mvc\Controller\Plugin\Exception\ExceptionInterface $e) {
                    return $fncBack();
                }
            }
            if (array_key_exists('origine', $prg)) {
                $this->redirectToOrigin()->setBack($prg['origine']);
                unset($prg['origine']);
            }
            $args = array_merge(
                Session::get('post', [], $this->getSessionNamespace($sessionNameSpace)),
                $prg);
            $sbm_isPost = true;
            Session::set('post', $args, $this->getSessionNamespace($sessionNameSpace));
        }
        try {
            $userFeatures = new UserFeatures($this->categorieId, $this->userId,
                $this->db_manager, $this->sansimpayes);
        } catch (\Exception $e) {
            return $this->homePage('Entrée interdite.', FlashMessenger::NAMESPACE_ERROR);
        }
        // formulaire des critères de recherche
        $criteres_form = new CriteresForm($userFeatures->getArrayEtablissements());
        $criteres_form->setValueOptions('communeId',
            $this->db_manager->get('Sbm\Db\Select\Communes')
                ->visibles())
            ->setValueOptions('classeId',
            $this->db_manager->get('Sbm\Db\Select\Classes')
                ->tout())
            ->setValueOptions('serviceId',
            $userFeatures->getQuery()
                ->listeServicesForSelect())
            ->setValueOptions('stationId',
            $userFeatures->getQuery()
                ->listeStationsForSelect());
        // CritereObject est un objectData qui contient la méthode getWhere() adhoc
        $criteres_obj = new CriteresObject($criteres_form->getElementNames(),
            $this->sansimpayes);
        if ($sbm_isPost) {
            $criteres_form->setData($args);
            if ($criteres_form->isValid()) {
                $criteres_obj->exchangeArray($criteres_form->getData());
            }
        }
        if (! $criteres_form->hasValidated() && ! empty($args)) {
            $criteres_obj->exchangeArray($args);
            $criteres_form->setData($criteres_obj->getArrayCopy());
        }
        return [
            'post' => $args,
            'userFeatures' => $userFeatures,
            'criteres_form' => $criteres_form,
            'criteres_obj' => $criteres_obj
        ];
    }

    private function exportEleve($record)
    {
        $adresseR1 = implode("\r\n",
            array_filter(
                array_unique(
                    [
                        $record['adresseL1Elv'],
                        $record['adresseL2Elv'],
                        $record['adresseL3Elv'],
                        $record['lacommuneElv']
                    ])));
        $adresseR2 = implode("\r\n",
            array_filter(
                array_unique(
                    [
                        $record['adresseL1R2'],
                        $record['adresseL2R2'],
                        $record['adresseL3R2'],
                        $record['lacommuneR2']
                    ])));
        $circuits1 = $this->itineraires($record['eleveId'], 1);
        $circuits2 = $this->itineraires($record['eleveId'], 2);
        return [
            'numero' => $record['numero'],
            'nom_eleve' => $record['nom_eleve'],
            'prenom_eleve' => $record['prenom_eleve'],
            'etablissement' => $record['etablissement'],
            'classe' => $record['classe'],
            'responsable1NomPrenom' => $record['responsable1NomPrenom'],
            'adresseR1' => $adresseR1,
            'circuits1' => $circuits1,
            'responsable2NomPrenom' => $record['responsable2NomPrenom'],
            'adresseR2' => $adresseR2,
            'circuits2' => $circuits2
        ];
    }

    private function exportEleveDescriptor()
    {
        return [
            [
                'label' => 'Numéro',
                'autosize' => true
            ],
            [
                'label' => 'Nom',
                'autosize' => true
            ],
            [
                'label' => 'Prénom',
                'autosize' => true
            ],
            [
                'label' => 'Etablissement',
                'autosize' => true
            ],
            [
                'label' => 'Classe',
                'autosize' => true
            ],
            [
                'label' => 'Responsable1',
                'autosize' => true
            ],
            [
                'label' => 'Adresse1',
                'width' => 41,
                'wraptext' => true
            ],
            [
                'label' => 'Circuits1',
                'width' => 120,
                'wraptext' => true
            ],
            [
                'label' => 'Responsable2',
                'autosize' => true
            ],
            [
                'label' => 'Adresse2',
                'width' => 41,
                'wraptext' => true
            ],
            [
                'label' => 'Circuits2',
                'width' => 120,
                'wraptext' => true
            ]
        ];
    }

    private function itineraires($eleveId, $trajet)
    {
        $resultset = $this->db_manager->get('Sbm\Db\Query\AffectationsServicesStations')->getItineraires(
            $eleveId, $trajet);
        $content = [];
        foreach ($resultset as $value) {
            $content[] = implode("    ",
                [
                    $value['jours'],
                    StdLib::getParam($value['moment'], $this->getMoment(), ''),
                    $value['ligne1Id'],
                    $value['commune1'],
                    $value['station1'],
                    $value['horaire1'],
                    $value['commune2'],
                    $value['station2'],
                    $value['horaire2']
                ]);
        }
        return implode("\r\n", $content);
    }

    // ===========================================================================================================
    // méthodes du menu Bienvenue
    //
    public function modifCompteAction()
    {
        $retour = $this->url()->fromRoute('sbmportail/etablissement');
        return $this->redirectToOrigin()
            ->setBack($retour)
            ->toRoute('login', [
            'action' => 'modif-compte'
        ]);
    }

    public function localisationAction()
    {
        $this->flashMessenger()->addWarningMessage(
            'La localisation n\'est pas possible pour votre catégorie d\'utilisateurs.');
        return $this->redirect()->toRoute('sbmportail/etablissement');
    }

    public function mdpChangeAction()
    {
        $retour = $this->url()->fromRoute('sbmportail/etablissement');
        return $this->redirectToOrigin()
            ->setBack($retour)
            ->toRoute('login', [
            'action' => 'mdp-change'
        ]);
    }

    public function emailChangeAction()
    {
        $retour = $this->url()->fromRoute('sbmportail/etablissement');
        return $this->redirectToOrigin()
            ->setBack($retour)
            ->toRoute('login', [
            'action' => 'email-change'
        ]);
    }

    public function messageAction()
    {
        $retour = $this->url()->fromRoute('sbmportail/etablissement');
        return $this->redirectToOrigin()
            ->setBack($retour)
            ->toRoute('SbmMail');
    }
}