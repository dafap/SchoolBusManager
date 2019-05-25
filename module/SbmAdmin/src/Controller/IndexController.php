<?php
/**
 * Controller principal du module SbmAdmin
 *
 * Compatible ZF3
 *
 * @project sbm
 * @package module/SbmGestion/src/SbmAdmin/Controller
 * @filesource IndexController.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 25 mai 2019
 * @version 2019-2.5.0
 */
namespace SbmAdmin\Controller;

use SbmAdmin\Form as FormAdmin;
use SbmAdmin\Model\Db\Service\Libelle\Liste;
use SbmAdmin\Model\Db\Service\Responsable\Responsables;
use SbmAdmin\Model\Db\Service\User\Users;
use SbmBase\Model\DateLib;
use SbmBase\Model\Session;
use SbmBase\Model\StdLib;
use SbmCommun\Form;
use SbmCommun\Model\Db\ObjectData\Criteres as ObjectDataCriteres;
use SbmCommun\Model\Mvc\Controller\AbstractActionController;
use SbmCommun\Model\Mvc\Controller\Plugin\Exception\ExceptionInterface as RedirectToOrigineException;
use SbmMail\Form\Mail as FormMail;
use SbmMail\Model\Template as MailTemplate;
use Zend\Db\Sql\Where;
use Zend\Http\PhpEnvironment\Response;
use Zend\View\Model\ViewModel;

class IndexController extends AbstractActionController
{

    public function indexAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        }
        return new ViewModel([
            'theme' => $this->theme
        ]);
    }

    public function libelleListeAction()
    {
        $args = $this->initListe('libelles',
            function ($config, $form) {
                $form->setValueOptions('nature',
                    $config['db_manager']->get('Sbm\Db\Select\Libelles')
                        ->nature());
            }, [
                'nature'
            ]);
        if ($args instanceof Response)
            return $args;

        return new ViewModel(
            [
                'paginator' => $this->db_manager->get('Sbm\Db\System\Libelles')->paginator(
                    $args['where'], [
                        'nature',
                        'code'
                    ]),
                'page' => $this->params('page', 1),
                'count_per_page' => $this->getPaginatorCountPerPage('nb_libelles', 10),
                'criteres_form' => $args['form']
            ]);
    }

    public function libelleAjoutAction()
    {
        $currentPage = $this->params('page', 1);
        $form = $this->form_manager->get(FormAdmin\Libelle::class);
        $params = [
            'data' => [
                'table' => 'libelles',
                'type' => 'system',
                'alias' => 'Sbm\Db\System\Libelles'
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
                return $this->redirect()->toRoute('sbmadmin',
                    [
                        'action' => 'libelle-liste',
                        'page' => $currentPage
                    ]);
                break;
            default:
                return new ViewModel(
                    [
                        'form' => $form->prepare(),
                        'page' => $currentPage
                    ]);
                break;
        }
    }

    public function libelleEditAction()
    {
        $currentPage = $this->params('page', 1);
        $form = $this->form_manager->get(FormAdmin\Libelle::class);

        $params = [
            'data' => [
                'table' => 'libelles',
                'type' => 'system',
                'alias' => 'Sbm\Db\System\Libelles',
                'id' => 'id'
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
                    return $this->redirect()->toRoute('sbmadmin',
                        [
                            'action' => 'libelle-liste',
                            'page' => $currentPage
                        ]);
                    break;
                default:
                    return new ViewModel(
                        [
                            'form' => $form->prepare(),
                            'page' => $currentPage,
                            'id' => $r->getResult()
                        ]);
                    break;
            }
        }
    }

    public function libelleSupprAction()
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
                'alias' => 'Sbm\Db\System\Libelles',
                'id' => 'id'
            ],
            'form' => $form
        ];

        $r = $this->supprData($this->db_manager, $params,
            function ($id, $tableLibelles) {
                return [
                    'id' => implode('|', $id),
                    'data' => $tableLibelles->getRecord($id)
                ];
            });
        if ($r instanceof Response) {
            return $r;
        } else {
            switch ($r->getStatus()) {
                case 'error':
                case 'warning':
                case 'success':
                    return $this->redirect()->toRoute('sbmadmin',
                        [
                            'action' => 'libelle-liste',
                            'page' => $currentPage
                        ]);
                    break;
                default:
                    return new ViewModel(
                        [
                            'form' => $form->prepare(),
                            'page' => $currentPage,
                            'data' => StdLib::getParam('data', $r->getResult()),
                            'id' => StdLib::getParam('id', $r->getResult())
                        ]);
                    break;
            }
        }
    }

    public function libelleGroupAction()
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
        list ($nature, $code) = explode('|',
            StdLib::getParam('id', $args, [
                false,
                false
            ]));
        if ($nature === false) {
            $this->flashMessenger()->addErrorMessage('Action interdite.');
            return $this->redirect()->toRoute('sbmadmin',
                [
                    'action' => 'libelle-liste',
                    'page' => $currentPage
                ]);
        }
        return new ViewModel(
            [
                'data' => $this->db_manager->get(Liste::class)->forNature($nature),
                'page' => $currentPage,
                'nature' => $nature,
                'code' => $code
            ]);
    }

    public function libellePdfAction()
    {
        $criteresObject = [
            ObjectDataCriteres::class,
            [
                'strict' => [
                    'nature',
                    'code'
                ]
            ]
        ];
        $criteresForm = [
            Form\CriteresForm::class,
            'libelles'
        ];
        $documentId = null;
        $retour = [
            'route' => 'sbmadmin',
            'action' => 'libelle-liste'
        ];
        return $this->documentPdf($criteresObject, $criteresForm, $documentId, $retour);
    }

    /**
     * lance la création d'une liste de libellés comme filtre la nature et le code reçus
     * en post
     */
    public function libelleGroupPdfAction()
    {
        $criteresObject = [
            ObjectDataCriteres::class,
            null,
            function ($where, $args) {
                $nature = StdLib::getParam('nature', $args, - 1);
                $where = new Where();
                $where->equalTo('nature', $nature);
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

    /**
     * Gestion des RPI
     */
    public function rpiListeAction()
    {
        $args = $this->initListe(
            [
                [
                    'name' => 'nom',
                    'type' => 'text',
                    'attributes' => [
                        'id' => 'critere-nom',
                        'maxlength' => '30',
                        'class' => 'sbm-width-30c'
                    ],
                    'options' => [
                        'label' => 'Nom',
                        'label_attributes' => [
                            'class' => 'sbm-first'
                        ],
                        'error_attributes' => [
                            'class' => 'sbm-error'
                        ]
                    ]
                ],
                [
                    'type' => 'Zend\Form\Element\Checkbox',
                    'name' => 'selection',
                    'attributes' => [
                        'useHiddenElement' => false,
                        'options' => [
                            'checkedValue' => false,
                            'uncheckedValue' => true
                        ],
                        'class' => 'sbm-checkbox'
                    ],
                    'options' => [
                        'label' => 'Sélectionnés',
                        'label_attributes' => [
                            'class' => ''
                        ],
                        'error_attributes' => [
                            'class' => 'sbm-error'
                        ]
                    ]
                ]
            ]);
        if ($args instanceof Response)
            return $args;

        return new ViewModel(
            [
                'data' => $this->db_manager->get('Sbm\Db\Table\Rpi')->fetchAll(
                    $args['where']),
                'criteres_form' => $args['form']
            ]);
    }

    public function rpiAjoutAction()
    {
        $form = $this->form_manager->get(Form\Rpi::class);
        $params = [
            'data' => [
                'table' => 'rpi',
                'type' => 'table',
                'alias' => 'Sbm\Db\Table\Rpi'
            ],
            'form' => $form
        ];
        $table = $this->db_manager->get($params['data']['alias']);
        $r = $this->addData($this->db_manager, $params, null,
            function ($args) use ($table, $form) {
                $form->setValueOptions('niveau', $table::getNiveaux());
            });
        switch ($r) {
            case $r instanceof Response:
                return $r;
                break;
            case 'error':
            case 'warning':
            case 'success':
                return $this->redirect()->toRoute('sbmadmin', [
                    'action' => 'rpi-liste'
                ]);
                break;
            default:
                return new ViewModel([
                    'form' => $form->prepare(),
                    'rpiId' => null
                ]);
                break;
        }
    }

    public function rpiEditAction()
    {
        $form = $this->form_manager->get(Form\Rpi::class);
        $params = [
            'data' => [
                'table' => 'rpi',
                'type' => 'table',
                'alias' => 'Sbm\Db\Table\Rpi',
                'id' => 'rpiId'
            ],
            'form' => $form
        ];
        $tRpi = $this->db_manager->get($params['data']['alias']);
        $tRpiClasses = $this->db_manager->get('Sbm\Db\Table\RpiClasses');
        $tRpiCommunes = $this->db_manager->get('Sbm\Db\Table\RpiCommunes');

        $tRpiEtablissements = $this->db_manager->get('Sbm\Db\Table\RpiEtablissements');
        $r = $this->editData($this->db_manager, $params, null,
            function ($args) use ($form, $tRpiClasses, $tRpi, $tRpiCommunes,
            $tRpiEtablissements) {
                $form->setValueOptions('niveau', $tRpi::getNiveaux());
            });
        if ($r instanceof Response) {
            return $r;
        } else {
            switch ($r->getStatus()) {
                case 'error':
                case 'warning':
                case 'success':
                    return $this->redirect()->toRoute('sbmadmin',
                        [
                            'action' => 'rpi-liste'
                        ]);
                    break;
                default:
                    $structure = [
                        'communes' => [],
                        'etablissements' => []
                    ];
                    $rcommunes = $tRpiCommunes->getCommunes($r->getPost()['rpiId']);
                    foreach ($rcommunes as $row) {
                        $structure['communes'][] = $row;
                    }
                    $retablissements = $tRpiEtablissements->getEtablissements(
                        $r->getPost()['rpiId']);
                    foreach ($retablissements as $row) {
                        $structure['etablissements'][] = array_merge($row,
                            [
                                'classes' => $tRpiClasses->getClasses(
                                    $row['etablissementId'])
                            ]);
                    }
                    return new ViewModel(
                        [
                            'form' => $form->prepare(),
                            'rpiId' => $r->getResult(),
                            'structure' => $structure
                        ]);
                    break;
            }
        }
    }

    public function rpiSupprAction()
    {
        $form = new Form\ButtonForm([
            'id' => null
        ],
            [
                'supproui' => [
                    'class' => 'confirm default',
                    'value' => 'Confirmer'
                ],
                'supprnon' => [
                    'class' => 'confirm default',
                    'value' => 'Abandonner'
                ]
            ]);
        $params = [
            'data' => [
                'alias' => 'Sbm\Db\Table\Rpi',
                'id' => 'rpiId'
            ],
            'form' => $form
        ];

        $r = $this->supprData($this->db_manager, $params,
            function ($id, $tRpi) {
                return [
                    'id' => $id,
                    'data' => $tRpi->getRecord($id)
                ];
            });

        if ($r instanceof Response) {
            return $r;
        } else {
            switch ($r->getStatus()) {
                case 'error':
                case 'warning':
                case 'success':
                    return $this->redirect()->toRoute('sbmadmin',
                        [
                            'action' => 'rpi-liste'
                        ]);
                    break;
                default:
                    $data = StdLib::getParam('data', $r->getResult());
                    return new ViewModel(
                        [
                            'form' => $form->prepare(),
                            'data' => $data,
                            'rpiId' => StdLib::getParam('id', $r->getResult())
                        ]);
                    break;
            }
        }
    }

    /**
     *
     * @todo à finir
     * @return \Zend\Http\PhpEnvironment\Response|\Zend\Http\Response
     */
    public function rpiPdfAction()
    {
        $criteresObject = [];
        // ObjectDataCriteres::class,
        // [
        // 'expressions' => [
        // 'active' => 'Literal:active = 0'
        // ]
        // ]

        $criteresForm = [];
        // CriteresForm::class,
        // 'rpi'

        $documentId = null;
        $retour = [
            'route' => 'sbmadmin',
            'action' => 'rpi-liste'
        ];
        return $this->documentPdf($criteresObject, $criteresForm, $documentId, $retour);
    }

    /**
     * Gestion des secteurs scolaires des collèges publics
     */
    public function secteurScolaireListeAction()
    {
        $args = $this->initListe('secteursScolairesClgPu',
            function ($config, $form) {
                $form->setValueOptions('etablissementId',
                    $config['db_manager']->get('Sbm\Db\Select\Etablissements')
                        ->clgPu())
                    ->setValueOptions('communeId',
                    $config['db_manager']->get('Sbm\Db\Select\Communes')
                        ->membres());
            }, [
                'etablissementId',
                'communeId'
            ]);
        if ($args instanceof Response)
            return $args;

        return new ViewModel(
            [
                'paginator' => $this->db_manager->get(
                    'Sbm\Db\Query\SecteursScolairesClgPu')->paginatorSS($args['where'],
                    [
                        'communeetab',
                        'etablissement',
                        'commune'
                    ]),
                'page' => $this->params('page', 1),
                'count_per_page' => $this->getPaginatorCountPerPage(
                    'nb_secteurs-scolaires', 20),
                'criteres_form' => $args['form']
            ]);
    }

    public function secteurScolaireAjoutAction()
    {
        $currentPage = $this->params('page', 1);
        $form = $this->form_manager->get(Form\SecteurScolaire::class);
        $form->setValueOptions('etablissementId',
            $this->db_manager->get('Sbm\Db\Select\Etablissements')
                ->clgPu())
            ->setValueOptions('communeId',
            $this->db_manager->get('Sbm\Db\Select\Communes')
                ->desservies());
        $params = [
            'data' => [
                'table' => 'secteurs-scolaires-clg-pu',
                'type' => 'table',
                'alias' => 'Sbm\Db\Table\SecteursScolairesClgPu'
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
                return $this->redirect()->toRoute('sbmadmin',
                    [
                        'action' => 'secteur-scolaire-liste',
                        'page' => $currentPage
                    ]);
                break;
            default:
                return new ViewModel(
                    [
                        'form' => $form->prepare(),
                        'page' => $currentPage
                    ]);
                break;
        }
    }

    public function secteurScolaireSupprAction()
    {
        $currentPage = $this->params('page', 1);
        $form = new Form\ButtonForm([
            'id' => null
        ],
            [
                'supproui' => [
                    'class' => 'confirm default',
                    'value' => 'Confirmer'
                ],
                'supprnon' => [
                    'class' => 'confirm default',
                    'value' => 'Abandonner'
                ]
            ]);
        $params = [
            'data' => [
                'alias' => 'Sbm\Db\Table\SecteursScolairesClgPu',
                'id' => [
                    'etablissementId',
                    'communeId'
                ]
            ],
            'form' => $form
        ];
        $oRequete = $this->db_manager->get('Sbm\Db\Query\SecteursScolairesClgPu');
        try {
            $r = $this->supprData($this->db_manager, $params,
                function ($id, $table) use ($oRequete) {
                    // ici $table n'est pas utilisée et est remplacée par $oRequete
                    return [
                        'id' => $id,
                        'data' => $oRequete->getRecord($id)
                    ];
                });
        } catch (\Zend\Db\Adapter\Exception\InvalidQueryException $e) {
            $this->flashMessenger()->addWarningMessage(
                'Impossible de supprimer cette commune d\'un secteur scolaire.');
            return $this->redirect()->toRoute('sbmadmin',
                [
                    'action' => 'secteur-scolaire-liste',
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
                    return $this->redirect()->toRoute('sbmadmin',
                        [
                            'action' => 'secteur-scolaire-liste',
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

    public function secteurScolairePdfAction()
    {
        $criteresObject = [
            ObjectDataCriteres::class
        ];
        $criteresForm = [
            Form\CriteresForm::class,
            'secteursScolairesClgPu'
        ];
        $documentId = null;
        $retour = [
            'route' => 'sbmadmin',
            'action' => 'secteur-scolaire-liste'
        ];
        return $this->documentPdf($criteresObject, $criteresForm, $documentId, $retour);
    }

    /**
     * Gestion des règles de correspondances d'établissements pour une simulation
     *
     * @return \Zend\Http\PhpEnvironment\Response|\Zend\View\Model\ViewModel
     */
    public function simulationEtablissementListeAction()
    {
        $args = $this->initListe('SimulationEtablissements',
            function ($config, $form) {
                $form->setValueOptions('communeetaborigineId',
                    $config['db_manager']->get('Sbm\Db\Select\Communes')
                        ->desservies())
                    ->setValueOptions('suivantId',
                    $config['db_manager']->get('Sbm\Db\Select\Etablissements')
                        ->tous());
            }, [
                'origineId',
                'suivantId'
            ]);
        if ($args instanceof Response)
            return $args;

        return new ViewModel(
            [
                'paginator' => $this->db_manager->get(
                    'Sbm\Db\Query\SimulationEtablissements')->paginatorSE($args['where'],
                    [
                        'communeetaborigine',
                        'niveauetaborigine',
                        'etablissementorigine'
                    ]),
                'page' => $this->params('page', 1),
                'count_per_page' => $this->getPaginatorCountPerPage(
                    'nb_simulation-etablissements', 20),
                'criteres_form' => $args['form']
            ]);
    }

    public function simulationEtablissementAjoutAction()
    {
        $currentPage = $this->params('page', 1);
        $form = $this->form_manager->get(Form\SimulationEtablissement::class);
        $form->setValueOptions('origineId',
            $this->db_manager->get('Sbm\Db\Select\Etablissements')
                ->desservis())
            ->setValueOptions('suivantId',
            $this->db_manager->get('Sbm\Db\Select\Etablissements')
                ->desservis());
        $params = [
            'data' => [
                'table' => 'simulation-etablissements',
                'type' => 'table',
                'alias' => 'Sbm\Db\Table\SimulationEtablissements'
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
                return $this->redirect()->toRoute('sbmadmin',
                    [
                        'action' => 'simulation-etablissement-liste',
                        'page' => $currentPage
                    ]);
                break;
            default:
                return new ViewModel(
                    [
                        'form' => $form->prepare(),
                        'page' => $currentPage
                    ]);
                break;
        }
    }

    public function simulationEtablissementSupprAction()
    {
        $currentPage = $this->params('page', 1);
        $form = new Form\ButtonForm([
            'id' => null
        ],
            [
                'supproui' => [
                    'class' => 'confirm default',
                    'value' => 'Confirmer'
                ],
                'supprnon' => [
                    'class' => 'confirm default',
                    'value' => 'Abandonner'
                ]
            ]);
        $params = [
            'data' => [
                'alias' => 'Sbm\Db\Table\SimulationEtablissements',
                'id' => 'origineId'
            ],
            'form' => $form
        ];
        $oRequete = $this->db_manager->get('Sbm\Db\Query\SimulationEtablissements');
        try {
            $r = $this->supprData($this->db_manager, $params,
                function ($id, $table) use ($oRequete) {
                    // ici $table n'est pas utilisée et est remplacée par $oRequete
                    return [
                        'id' => $id,
                        'data' => $oRequete->getRecord($id)
                    ];
                });
        } catch (\Zend\Db\Adapter\Exception\InvalidQueryException $e) {
            $this->flashMessenger()->addWarningMessage(
                'Impossible de supprimer cette règle de passage dans un établissement de niveau supérieur.');
            return $this->redirect()->toRoute('sbmadmin',
                [
                    'action' => 'simulation-etablissement-liste',
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
                    return $this->redirect()->toRoute('sbmadmin',
                        [
                            'action' => 'simulation-etablissement-liste',
                            'page' => $currentPage
                        ]);
                    break;
                default:
                    return new ViewModel(
                        [
                            'form' => $form->prepare(),
                            'page' => $currentPage,
                            'data' => StdLib::getParam('data', $r->getResult()),
                            'origineId' => StdLib::getParam('id', $r->getResult())
                        ]);
                    break;
            }
        }
    }

    public function simulationEtablissementPdfAction()
    {
        $criteresObject = [
            ObjectDataCriteres::class
        ];
        $criteresForm = [
            Form\CriteresForm::class,
            'SimulationEtablissements'
        ];
        $documentId = null;
        $retour = [
            'route' => 'sbmadmin',
            'action' => 'simulation-etablissement-liste'
        ];
        return $this->documentPdf($criteresObject, $criteresForm, $documentId, $retour);
    }

    /*
     * Gestion des users
     */
    public function userListeAction()
    {
        $args = $this->initListe('users', null, [
            'categorieId'
        ], [
            'active' => 'Literal:active=0'
        ]);
        if ($args instanceof Response)
            return $args;

        return new ViewModel(
            [
                'paginator' => $this->db_manager->get('Sbm\Db\Table\Users')->paginator(
                    $args['where'], [
                        'categorieId Desc',
                        'nom',
                        'prenom'
                    ]),
                'page' => $this->params('page', 1),
                'count_per_page' => $this->getPaginatorCountPerPage('nb_users', 20),
                'criteres_form' => $args['form']
            ]);
    }

    public function userPdfAction()
    {
        $criteresObject = [
            ObjectDataCriteres::class,
            [
                'expressions' => [
                    'active' => 'Literal:active = 0'
                ]
            ]
        ];
        $criteresForm = [
            Form\CriteresForm::class,
            'users'
        ];
        $documentId = null;
        $retour = [
            'route' => 'sbmadmin',
            'action' => 'user-liste'
        ];
        return $this->documentPdf($criteresObject, $criteresForm, $documentId, $retour);
    }

    public function userAjoutAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } else {
            $args = $prg ?: [];
            if (array_key_exists('cancel', $args)) {
                $this->flashMessenger()->addWarningMessage('Création abandonnée.');
                return $this->redirect()->toRoute('sbmadmin',
                    [
                        'action' => 'user-liste',
                        'page' => $this->params('page', 1)
                    ]);
            }
        }
        $form = $this->form_manager->get(FormAdmin\User::class);
        $tUser = $this->db_manager->get('Sbm\Db\Table\Users');
        $form->setData([
            'userId' => null
        ])->bind($tUser->getObjData());
        if (array_key_exists('submit', $args)) {
            $form->setData($args);
            if ($form->isValid()) {
                $tUser->saveRecord($form->getData()
                    ->completeToCreate());
                $this->flashMessenger()->addSuccessMessage('Compte créé');
                return $this->redirect()->toRoute('sbmadmin',
                    [
                        'action' => 'user-liste',
                        'page' => $this->params('page', 1)
                    ]);
            }
        }
        return new ViewModel(
            [
                'form' => $form->prepare(),
                'page' => $this->params('page', 1)
            ]);
    }

    public function userEditAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            $args = Session::get('post', false, $this->getSessionNamespace());
            if ($args === false || ! array_key_exists('userId', $args)) {
                return $this->redirect()->toRoute('login', [
                    'action' => 'home-page'
                ]);
            }
        } else {
            $args = $prg;
            if (array_key_exists('cancel', $args) || ! array_key_exists('userId', $args)) {
                $this->flashMessenger()->addWarningMessage('Modification abandonnée.');
                return $this->redirect()->toRoute('sbmadmin',
                    [
                        'action' => 'user-liste',
                        'page' => $this->params('page', 1)
                    ]);
            } elseif (! array_key_exists('submit', $args)) {
                Session::set('post', $args, $this->getSessionNamespace());
            }
        }
        $form = $this->form_manager->get(FormAdmin\User::class);
        $tUser = $this->db_manager->get('Sbm\Db\Table\Users');
        $form->bind($tUser->getObjData());
        if (array_key_exists('submit', $args)) {
            $form->setData($args);
            if ($form->isValid()) {
                $tUser->saveRecord($form->getData());
                $this->flashMessenger()->addSuccessMessage('Modification enregistrée');
                return $this->redirect()->toRoute('sbmadmin',
                    [
                        'action' => 'user-liste',
                        'page' => $this->params('page', 1)
                    ]);
            }
        }
        $user = $tUser->getRecord($args['userId']);
        $form->setData($user->getArrayCopy());
        return new ViewModel(
            [
                'form' => $form->prepare(),
                'user' => $user,
                'page' => $this->params('page', 1)
            ]);
    }

    public function userSupprAction()
    {
        $currentPage = $this->params('page', 1);
        $form = new Form\ButtonForm([
            'id' => null
        ],
            [
                'supproui' => [
                    'class' => 'confirm default',
                    'value' => 'Confirmer'
                ],
                'supprnon' => [
                    'class' => 'confirm default',
                    'value' => 'Abandonner'
                ]
            ]);
        $params = [
            'data' => [
                'alias' => 'Sbm\Db\Table\Users',
                'id' => 'userId'
            ],
            'form' => $form
        ];

        $r = $this->supprData($this->db_manager, $params,
            function ($id, $tUsers) {
                return [
                    'id' => $id,
                    'data' => $tUsers->getRecord($id)
                ];
            });

        if ($r instanceof Response) {
            return $r;
        } else {
            switch ($r->getStatus()) {
                case 'error':
                case 'warning':
                case 'success':
                    return $this->redirect()->toRoute('sbmadmin',
                        [
                            'action' => 'user-liste',
                            'page' => $currentPage
                        ]);
                    break;
                default:
                    $data = StdLib::getParam('data', $r->getResult());
                    $autorise = ($this->db_manager->get('Sbm\Db\Table\Responsables')->getRecordByEmail(
                        $data->email) === false);
                    return new ViewModel(
                        [
                            'autorise' => $autorise,
                            'form' => $form->prepare(),
                            'page' => $currentPage,
                            'data' => $data,
                            'userId' => StdLib::getParam('id', $r->getResult()),
                            'page' => $this->params('page', 1)
                        ]);
                    break;
            }
        }
    }

    /**
     * Cette méthode est appelée par post 1/ depuis user-liste.phtml avec les paramètres :
     * - userId - email (n'est plus nécessaire pour retrouver le responsableId car on lit
     * la fiche du user) 2/ depuis user-link.phtml avec les paramètres : - userId -
     * transporteurId ou etablissementId - submit ou cancel
     *
     * @return \Zend\Http\PhpEnvironment\Response|\Zend\Http\Response|\Zend\View\Model\ViewModel
     */
    public function userLinkAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            $args = Session::get('post', false, $this->getSessionNamespace());
            if ($args === false || ! array_key_exists('email', $args)) {
                return $this->redirect()->toRoute('login', [
                    'action' => 'home-page'
                ]);
            }
        } else {
            $args = $prg;
            if (array_key_exists('cancel', $args) || ! array_key_exists('userId', $args)) {
                return $this->redirect()->toRoute('sbmadmin',
                    [
                        'action' => 'user-liste',
                        'page' => $this->params('page', 1)
                    ]);
            } elseif (! array_key_exists('submit', $args)) {
                Session::set('post', $args, $this->getSessionNamespace());
            }
        }
        // récupère la fiche de l'user
        $user = $this->db_manager->get('Sbm\Db\Table\Users')->getRecord($args['userId']);
        switch ($user->categorieId) {
            case 2:
                $tUsersTransporteurs = $this->db_manager->get(
                    'Sbm\Db\Table\UsersTransporteurs');
                if ($tUsersTransporteurs->hasTransporteur($args['userId'])) {
                    $transporteurId = $tUsersTransporteurs->getTransporteurId(
                        $args['userId']);
                    $viewmodel = new ViewModel(
                        [
                            'user' => $user,
                            'transporteur' => $this->db_manager->get(
                                'Sbm\Db\Vue\Transporteurs')->getRecord($transporteurId),
                            'form' => false,
                            'page' => $this->params('page', 1)
                        ]);
                } else {
                    $form = $this->form_manager->get(FormAdmin\UserRelation::class)->getForm(
                        'transporteur');
                    $form->setValueOptions('transporteurId',
                        $this->db_manager->get('Sbm\Db\Select\Transporteurs'))
                        ->bind($tUsersTransporteurs->getObjData());
                    if (array_key_exists('submit', $args)) {
                        $form->setData($args);
                        if ($form->isValid()) {
                            $tUsersTransporteurs->saveRecord($form->getData());
                            $this->flashMessenger()->addSuccessMessage(
                                'Relation crée entre un utilisateur et un transporteur');
                            return $this->redirect()->toRoute('sbmadmin',
                                [
                                    'action' => 'user-liste',
                                    'page' => $this->params('page', 1)
                                ]);
                        }
                    }
                    $form->setData([
                        'userId' => $args['userId']
                    ]);
                    $viewmodel = new ViewModel(
                        [
                            'user' => $user,
                            'transporteur' => false,
                            'form' => $form
                        ]);
                }
                $viewmodel->setTemplate('sbm-admin/index/user-transporteur');
                break;
            case 3:
                $tUsersEtablissements = $this->db_manager->get(
                    'Sbm\Db\Table\UsersEtablissements');
                if ($tUsersEtablissements->hasEtablissement($args['userId'])) {
                    $etablissementId = $tUsersEtablissements->getEtablissementId(
                        $args['userId']);
                    $viewmodel = new ViewModel(
                        [
                            'user' => $user,
                            'etablissement' => $this->db_manager->get(
                                'Sbm\Db\Vue\Etablissements')->getRecord($etablissementId),
                            'form' => false,
                            'page' => $this->params('page', 1)
                        ]);
                } else {
                    $form = $this->form_manager->get(FormAdmin\UserRelation::class)->getForm(
                        'etablissement');
                    $form->setValueOptions('etablissementId',
                        $this->db_manager->get('Sbm\Db\Select\Etablissements')
                            ->desservis())
                        ->bind($tUsersEtablissements->getObjData());
                    if (array_key_exists('submit', $args)) {
                        $form->setData($args);
                        if ($form->isValid()) {
                            $tUsersEtablissements->saveRecord($form->getData());
                            $this->flashMessenger()->addSuccessMessage(
                                'Relation crée entre un utilisateur et un établissement');
                            return $this->redirect()->toRoute('sbmadmin',
                                [
                                    'action' => 'user-liste',
                                    'page' => $this->params('page', 1)
                                ]);
                        }
                    }
                    $form->setData([
                        'userId' => $args['userId']
                    ]);
                    $viewmodel = new ViewModel(
                        [
                            'user' => $user,
                            'etablissement' => false,
                            'form' => $form
                        ]);
                }
                $viewmodel->setTemplate('sbm-admin/index/user-etablissement');
                break;
            default:
                // récupère la fiche d'un responsable par son email (ancien comportement)
                $viewmodel = new ViewModel(
                    [
                        'user' => $user,
                        'responsable' => $this->db_manager->get('Sbm\Db\Vue\Responsables')->getRecordByEmail(
                            $user->email),
                        'page' => $this->params('page', 1)
                    ]);
                break;
        }
        return $viewmodel;
    }

    public function userTransporteurSupprAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        }
        $args = $prg ?: [];
        if (! array_key_exists('userId', $args) ||
            ! array_key_exists('transporteurId', $args)) {
            return $this->redirect()->toRoute('sbmadmin',
                [
                    'action' => 'user-liste',
                    'page' => $this->params('page', 1)
                ]);
        }
        $tUsersTransporteurs = $this->db_manager->get('Sbm\Db\Table\UsersTransporteurs');
        $tUsersTransporteurs->deleteRecord(
            [
                'userId' => $args['userId'],
                'transporteurId' => $args['transporteurId']
            ]);
        $this->flashMessenger()->addSuccessMessage('La relation a été supprimée');
        return $this->redirect()->toRoute('sbmadmin',
            [
                'action' => 'user-liste',
                'page' => $this->params('page', 1)
            ]);
    }

    public function userPrepareNouveauxComptesAction()
    {
        $currentPage = $this->params('page', 1);
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } else {
            $args = $prg ?: [];
            if (array_key_exists('supprnon', $args)) {
                return $this->redirect()->toRoute('sbmadmin',
                    [
                        'action' => 'user-liste',
                        'page' => $currentPage
                    ]);
            }
            $confirme = StdLib::getParam('supproui', $args, false);
        }
        $form = new Form\ButtonForm([
            'id' => null
        ],
            [
                'supproui' => [
                    'class' => 'confirm default',
                    'value' => 'Confirmer'
                ],
                'supprnon' => [
                    'class' => 'confirm default',
                    'value' => 'Abandonner'
                ]
            ]);
        $form->setAttribute('action',
            $this->url()
                ->fromRoute('sbmadmin',
                [
                    'action' => 'user-prepare-nouveaux-comptes',
                    'page' => $currentPage
                ]));
        if ($confirme) {
            $identity = $this->authenticate->by()->getIdentity();
            $nettoyage = $this->db_manager->get(Users::class);
            $nettoyage->deleteParentsNonConfirmes();
            $creation = $this->db_manager->get(Responsables::class);
            $tUsers = $this->db_manager->get('Sbm\Db\Table\Users');
            $oUser = $tUsers->getObjdata();
            $message = sprintf('Compte créé le %s par %s %s', DateLib::today(),
                $identity['nom'], $identity['prenom']);
            $compteur = 0;
            foreach ($creation->getResponsablesSansCompte() as $responsable) {
                $oUser->exchangeArray(
                    [
                        'userId' => null,
                        'categorieid' => 1,
                        'titre' => $responsable['titre'],
                        'nom' => $responsable['nom'],
                        'prenom' => $responsable['prenom'],
                        'email' => $responsable['email'],
                        'note' => $message
                    ])->completeToCreate();
                $tUsers->saveRecord($oUser);
                $compteur ++;
            }
            if ($compteur == 0) {
                $compte_rendu = 'Aucun compte n\'a été créé.';
            } elseif ($compteur == 1) {
                $compte_rendu = 'Un compte a été créé.';
            } else {
                $compte_rendu = sprintf('%d comptes ont été créés.', $compteur);
            }
            $this->flashMessenger()->addSuccessMessage($compte_rendu);
            return $this->redirect()->toRoute('sbmadmin',
                [
                    'action' => 'user-liste',
                    'page' => $currentPage
                ]);
        }
        return new ViewModel([
            'form' => $form->prepare(),
            'page' => $currentPage
        ]);
    }

    /**
     * Envoie un mail à un responsable. Reçoit en post les paramètres 'userId',
     * 'destinataire', 'email', 'origine' où origine est l'url de retour
     *
     * @return \Zend\Http\PhpEnvironment\Response|\Zend\Http\Response|\Zend\View\Model\ViewModel
     */
    public function userMailAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            $destinataire = Session::get('destinataire', [], $this->getSessionNamespace());
            $args = [];
        } else {
            $args = $prg;
            if (array_key_exists('origine', $args)) {
                $this->redirectToOrigin()->setBack($args['origine']);
                unset($args['origine']);
            }
            if (array_key_exists('ecrire', $args) && array_key_exists('email', $args)) {
                $destinataire = [
                    'email' => $args['email'],
                    'alias' => StdLib::getParam('utilisateur', $args)
                ];
                Session::set('destinataire', $destinataire, $this->getSessionNamespace());
                unset($args['email'], $args['responsable']);
            } else {
                $destinataire = Session::get('destinataire', [],
                    $this->getSessionNamespace());
            }
        }
        if (empty($destinataire) || array_key_exists('cancel', $args)) {
            $this->flashMessenger()->addWarningMessage('Aucun message envoyé.');
            try {
                return $this->redirectToOrigin()->back();
            } catch (RedirectToOrigineException $e) {
                $this->redirectToOrigin()->reset();
                return $this->redirect()->toRoute('login', [
                    'action' => 'home-page'
                ]);
            }
        }
        $form = $this->form_manager->get(FormMail::class);
        if (array_key_exists('submit', $args)) {
            $form->setData($args);
            if ($form->isValid()) {
                $data = $form->getData();
                // préparation du corps
                if ($data['body'] == strip_tags($data['body'])) {
                    // c'est du txt
                    $body = nl2br($data['body']);
                } else {
                    // c'est du html
                    $body = $data['body'];
                }
                // préparation des paramètres d'envoi
                $auth = $this->authenticate->by();
                $user = $auth->getIdentity();
                $logo_bas_de_mail = 'bas-de-mail-service-gestion.png';
                $mailTemplate = new MailTemplate(null, 'layout',
                    [
                        'file_name' => $logo_bas_de_mail,
                        'path' => StdLib::getParam('path', $this->img),
                        'img_attributes' => StdLib::getParamR(
                            [
                                'administrer',
                                $logo_bas_de_mail
                            ], $this->img),
                        'client' => $this->client
                    ]);
                $params = [
                    'to' => [
                        [
                            'email' => $destinataire['email'],
                            'name' => $destinataire['alias'] ?: $destinataire['email']
                        ]
                    ],
                    'bcc' => [
                        [
                            'email' => $user['email'],
                            'name' => 'School bus manager'
                        ]
                    ],
                    'subject' => $data['subject'],
                    'body' => [
                        'html' => $mailTemplate->render([

                            'body' => $body
                        ])
                    ]
                ];
                // envoi du mail
                $this->getEventManager()->addIdentifiers('SbmMail\Send');
                $this->getEventManager()->trigger('sendMail', null, $params);
                $this->flashMessenger()->addInfoMessage(
                    'Le message a été envoyé et une copie vous est adressée dans votre messagerie.');
                try {
                    return $this->redirectToOrigin()->back();
                } catch (RedirectToOrigineException $e) {
                    $this->redirectToOrigin()->reset();
                    return $this->redirect()->toRoute('login', [
                        'action' => 'home-page'
                    ]);
                }
            }
        }

        $view = new ViewModel(
            [
                'form' => $form->prepare(),
                'destinataires' => [
                    $destinataire
                ]
            ]);
        $view->setTemplate('sbm-mail/index/send.phtml');
        return $view;
    }

    public function exportAction()
    {
        return new ViewModel();
    }

    public function exportEleveAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        }
        $form = $this->form_manager->get(FormAdmin\Export::class)->getForm('eleve');
        if ($prg !== false) {
            if (array_key_exists('cancel', $prg)) {
                return $this->redirect()->toRoute('sbmadmin', [
                    'action' => 'export'
                ]);
            } else {
                $form->setData($prg);
                if ($form->isValid()) {
                    $where = $form->whereEleve();
                    if ($prg['lot']) {
                        $resultset = $this->db_manager->get(
                            'Sbm\Db\Query\AffectationsServicesStations')->getLocalisation(
                            $where, [
                                'nom_eleve',
                                'prenom_eleve'
                            ]);
                    } else {
                        $resultset = $this->db_manager->get(
                            'Sbm\Db\Query\ElevesResponsables')->getLocalisation($where,
                            [
                                'nom_eleve',
                                'prenom_eleve'
                            ]);
                    }
                    $data = iterator_to_array($resultset);
                    if (! empty($data)) {
                        $fields = array_keys(current($data));
                        return $this->csvExport('eleves.csv', $fields, $data);
                    } else {
                        $this->flashMessenger()->addInfoMessage(
                            'Il n\'y a pas de données correspondant aux critères indiqués.');
                    }
                }
            }
        }

        return new ViewModel([
            'form' => $form
        ]);
    }

    public function exportEtablissementAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        }
        $form = $this->form_manager->get(FormAdmin\Export::class)->getForm(
            'etablissement');
        if ($prg !== false) {
            if (array_key_exists('cancel', $prg)) {
                return $this->redirect()->toRoute('sbmadmin', [
                    'action' => 'export'
                ]);
            } else {
                $form->setData($prg);
                if ($form->isValid()) {
                    $where = $form->whereEtablissement();
                    $resultset = $this->db_manager->get('Sbm\Db\Query\Etablissements')->getLocalisation(
                        $where, [
                            'commune',
                            'nom'
                        ]);
                    $data = iterator_to_array($resultset);
                    if (! empty($data)) {
                        $fields = array_keys(current($data));
                        return $this->csvExport('etablissements.csv', $fields, $data);
                    } else {
                        $this->flashMessenger()->addInfoMessage(
                            'Il n\'y a pas de données correspondant aux critères indiqués.');
                    }
                }
            }
        }

        return new ViewModel([
            'form' => $form
        ]);
    }

    public function exportResponsableAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        }
        $form = $this->form_manager->get(FormAdmin\Export::class)->getForm('responsable');
        if ($prg !== false) {
            if (array_key_exists('cancel', $prg)) {
                return $this->redirect()->toRoute('sbmadmin', [
                    'action' => 'export'
                ]);
            } else {
                $form->setData($prg);
                if ($form->isValid()) {
                    $where = $form->whereResponsable();
                    $resultset = $this->db_manager->get('Sbm\Db\Vue\Responsables')->fetchAll(
                        $where, [
                            'commune',
                            'nom'
                        ]);
                    $data = $resultset->toArray();
                    if (! empty($data)) {
                        $fields = array_keys(current($data));
                        return $this->csvExport('responsables.csv', $fields, $data);
                    } else {
                        $this->flashMessenger()->addInfoMessage(
                            'Il n\'y a pas de données correspondant aux critères indiqués.');
                    }
                }
            }
        }

        return new ViewModel([
            'form' => $form
        ]);
    }

    public function exportStationAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        }
        $form = $this->form_manager->get(FormAdmin\Export::class)->getForm('station');
        if ($prg !== false) {
            if (array_key_exists('cancel', $prg)) {
                return $this->redirect()->toRoute('sbmadmin', [
                    'action' => 'export'
                ]);
            } else {
                $form->setData($prg);
                if ($form->isValid()) {
                    $where = $form->whereStation();
                    $resultset = $this->db_manager->get('Sbm\Db\Query\Stations')->getLocalisation(
                        $where, [
                            'commune',
                            'nom'
                        ]);
                    $data = iterator_to_array($resultset);
                    if (! empty($data)) {
                        $fields = array_keys(current($data));
                        return $this->csvExport('stations.csv', $fields, $data);
                    } else {
                        $this->flashMessenger()->addInfoMessage(
                            'Il n\'y a pas de données correspondant aux critères indiqués.');
                    }
                }
            }
        }

        return new ViewModel([
            'form' => $form
        ]);
    }

    // ===========================================================================================================
    // méthodes du menu Bienvenue
    //
    public function modifCompteAction()
    {
        $retour = $this->url()->fromRoute('sbmadmin');
        return $this->redirectToOrigin()
            ->setBack($retour)
            ->toRoute('login', [
            'action' => 'modif-compte'
        ]);
    }

    public function mdpChangeAction()
    {
        $retour = $this->url()->fromRoute('sbmadmin');
        return $this->redirectToOrigin()
            ->setBack($retour)
            ->toRoute('login', [
            'action' => 'mdp-change'
        ]);
    }

    public function emailChangeAction()
    {
        $retour = $this->url()->fromRoute('sbmadmin');
        return $this->redirectToOrigin()
            ->setBack($retour)
            ->toRoute('login', [
            'action' => 'email-change'
        ]);
    }

    public function messageAction()
    {
        $retour = $this->url()->fromRoute('sbmadmin');
        return $this->redirectToOrigin()
            ->setBack($retour)
            ->toRoute('SbmMail');
    }

    public function localisationAction()
    {
        $this->flashMessenger()->addWarningMessage(
            'La localisation n\'est pas possible pour votre catégorie d\'utilisateurs.');
        return $this->redirect()->toRoute('sbmadmin');
    }
}