<?php
/**
 * Controlleur du portail des communes
 *
 * @project sbm
 * @package SbmPortail/src/Controller/Service
 * @filesource CommuneController.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 25 sept. 2020
 * @version 2020-2.6.1
 */
namespace SbmPortail\Controller;

use SbmAuthentification\Model\CategoriesInterface;
use SbmBase\Model\Session;
use SbmBase\Model\StdLib;
use SbmCommun\Model\Mvc\Controller\AbstractActionController;
use SbmGestion\Model\Db\Filtre\Eleve\Filtre as FiltreEleve;
use Zend\Db\Sql\Where;
use Zend\Http\PhpEnvironment\Response;
use Zend\View\Model\ViewModel;
use SbmPortail\Model\User\Commune as UserFeatures;
use Zend\Mvc\Controller\Plugin\FlashMessenger;
use SbmPortail\Form\CriteresCommuneForm as CriteresForm;
use SbmPortail\Model\Db\ObjectData\CriteresCommune as CriteresObject;

class CommuneController extends AbstractActionController
{
    use \SbmCommun\Model\Traits\DebugTrait;

    private $sansimpayes = false;

    private function homePage(string $message = '',
        string $namespace = FlashMessenger::NAMESPACE_SUCCESS)
    {
        if ($message) {
            $this->flashMessenger()->addMessage($message, $namespace);
        }
        return $this->redirect()->toRoute('login', [
            'action' => 'home-page'
        ]);
    }

    /**
     * Page d'accueil du portail des communes
     *
     * @return \Zend\Http\Response|\Zend\View\Model\ViewModel
     */
    public function IndexAction()
    {
        try {
            $userFeatures = new UserFeatures($this->categorieId, $this->userId,
                $this->db_manager);
        } catch (\Exception $e) {
            return $this->homePage('Entrée interdite.', FlashMessenger::NAMESPACE_ERROR);
        }
        return new ViewModel(
            [
                'data' => $userFeatures->tableauStatistique(),
                'commune' => $userFeatures->listeDesNoms()
            ]);
    }

    /**
     * Présente la carte des établissements fréquentés par les élèves inscrits pour le
     * millesime en cours. On peut ouvrir la fiche de l'établissement.
     *
     * @return \Zend\Http\PhpEnvironment\Response|\Zend\Http\Response|\Zend\View\Model\ViewModel
     */
    public function carteEtablissementsAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif (array_key_exists('cancel', $prg)) {
            return $this->homePage();
        }
        try {
            $userFeatures = new UserFeatures($this->categorieId, $this->userId,
                $this->db_manager);
        } catch (\Exception $e) {
            return $this->homePage('Entrée interdite.', FlashMessenger::NAMESPACE_ERROR);
        }
        $viewmodel = new ViewModel(
            [
                'scheme' => $this->getRequest()
                    ->getUri()
                    ->getScheme(),
                'ptEtablissements' => $this->db_manager->get('Sbm\Portail\Commune\Query')
                    ->setProjection($this->projection)
                    ->setCommuneId($userFeatures->getCommuneIds())
                    ->etablissementsPourCarte(),
                'config' => StdLib::getParam('etablissement', $this->config_cartes),
                'url_api' => $this->url_api
            ]);
        $viewmodel->setTemplate('sbm-cartographie/carte/etablissements.phtml');
        return $viewmodel;
    }

    /**
     * Présente la carte des stations de la commune, en indiquant pour chacune d'elles les
     * circuits qui la desservent. On peut ouvrir la fiche d'une station.
     *
     * @return \Zend\Http\PhpEnvironment\Response|\Zend\Http\Response|\Zend\View\Model\ViewModel
     */
    public function carteStationsAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif (array_key_exists('cancel', $prg)) {
            return $this->homePage();
        }
        try {
            $userFeatures = new UserFeatures($this->categorieId, $this->userId,
                $this->db_manager);
        } catch (\Exception $e) {
            return $this->homePage('Entrée interdite.', FlashMessenger::NAMESPACE_ERROR);
        }
        $viewmodel = new ViewModel(
            [
                'scheme' => $this->getRequest()
                    ->getUri()
                    ->getScheme(),
                'ptStations' => $this->db_manager->get('Sbm\Portail\Commune\Query')
                    ->setProjection($this->projection)
                    ->setCommuneId($userFeatures->getCommuneIds())
                    ->stationsPourCarte(),
                'config' => StdLib::getParam('station', $this->config_cartes),
                'url_api' => $this->url_api
            ]);
        $viewmodel->setTemplate('sbm-cartographie/carte/stations.phtml');
        return $viewmodel;
    }

    /**
     * Présente les circuits passant sur la commune, avec possibilité d'éditer les
     * horaires, de consulter les élèves les fréquentant (par service, par station)
     *
     * @return \Zend\Http\Response|\Zend\Http\PhpEnvironment\Response|\Zend\View\Model\ViewModel
     */
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
                $this->db_manager);
        } catch (\Exception $e) {
            return $this->homePage('Entrée interdite.', FlashMessenger::NAMESPACE_ERROR);
        }
        $millesime = Session::get('millesime');
        $as = $millesime . '-' . ($millesime + 1);
        $critere_form = $args['form'];
        $critere_form->remove('lotId');
        return new ViewModel(
            [
                'paginator' => $this->db_manager->get('Sbm\Portail\Commune\Query')
                    ->setCommuneId($userFeatures->getCommuneIds())
                    ->paginatorLignes($args['where'], [
                    'actif DESC',
                    'ligneId'
                ]),
                'page' => $this->params('page', 1),
                'count_per_page' => $this->getPaginatorCountPerPage('nb_lignes', 15),
                'criteres_form' => $critere_form,
                'millesime' => $millesime,
                'as' => $as,
                'commune' => $userFeatures->listeDesNoms()
            ]);
    }

    public function lignesPdfAction()
    {
        ;
    }

    public function lignesDownloadAction()
    {
        ;
    }

    public function servicesAction()
    {
        $args = $this->initListe('services',
            function ($config, $form, $args) {
                $form->remove('ligneId');
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
        $effectifServices->init();
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
        ;
    }

    public function servicesDownloadAction()
    {
        ;
    }

    /**
     * Reçoit en POST les paramètres 'ligneId', 'sens', 'moment' et 'ordre'. . Le numéro
     * de la page de la liste d'origine est dans le paramètre GET 'id' et le numéro de la
     * page de la liste d'élèves générée par cette action est dans le paramètre GET 'page'
     *
     * @return \Zend\Http\PhpEnvironment\Response|\Zend\View\Model\ViewModel
     */
    public function serviceGroupAction()
    {
        $result = $this->prepareListeEleves('eleves',
            function () {
                return $this->redirect()
                    ->toRoute('sbmportail/commune',
                    [
                        'action' => 'circuit',
                        'page' => $this->params('id')
                    ]);
            });
        if ($result instanceof Response) {
            return $result;
        }
        $view = new ViewModel(
            [
                'paginator' => $this->db_manager->get('Sbm\Db\Eleve\Liste')->paginatorGroup(
                    Session::get('millesime'),
                    FiltreEleve::byService(
                    $result['post']['ligneId'], $result['post']['sens'],
                        $result['post']['moment'], $result['post']['ordre']),
                    [
                        'nom',
                        'prenom'
                    ], 'service', $result['criteres_obj']->getWhere()),
                'count_per_page' => $this->getPaginatorCountPerPage('nb_eleves', 10),
                'page' => $this->params('page', 1),
                'id' => $this->params('id'),
                'commune' => $result['communes'],
                'criteres_form' => $result['criteres_form']
            ]);
        $view->setTemplate('sbm-portail/commune/groupe.phtml');
        return $view;
    }

    public function serviceGroupPdfAction()
    {
        ;
    }

    public function circuitAction()
    {
        $args = $this->initListe('circuits', null,
            [
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
            $this->redirectToOrigin()->reset();
            return $this->redirect()->toRoute('sbmgestion/transport');
        }
        try {
            $userFeatures = new UserFeatures($this->categorieId, $this->userId,
                $this->db_manager);
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
        $where = new Where();
        $where->equalTo('c.millesime', $millesime)
            ->equalTo('c.ligneId', $args['post']['ligneId'])
            ->equalTo('c.sens', $args['post']['sens'])
            ->equalTo('c.moment', $args['post']['moment'])
            ->equalTo('c.ordre', $args['post']['ordre']);
        $effectifCircuits->setSanspreinscrits(false)->init($where);
        return new ViewModel(
            [
                'paginator' => $this->db_manager->get('Sbm\Portail\Commune\Query')->paginatorCircuits(
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
                'arrayCommuneIds' => $userFeatures->getCommuneIds()
            ]);
    }

    public function circuitPdfAction()
    {
        ;
    }

    public function circuitDownloadAction()
    {
        ;
    }

    /**
     * Reçoit en POST le paramètre 'circuitId'. Le numéro de la page de la liste d'origine
     * est dans le paramètre GET 'id' et le numéro de la page de la liste d'élèves générée
     * par cette action est dans le paramètre GET 'page'
     *
     * @return \Zend\Http\PhpEnvironment\Response|\Zend\View\Model\ViewModel
     */
    public function circuitGroupAction()
    {
        $result = $this->prepareListeEleves('eleves',
            function () {
                return $this->redirect()
                    ->toRoute('sbmportail/commune',
                    [
                        'action' => 'circuit',
                        'page' => $this->params('id')
                    ]);
            });
        if ($result instanceof Response) {
            return $result;
        }
        // $result['criteres_obj']->getPredicates();
        $circuit = $this->db_manager->get('Sbm\Db\Table\Circuits')->getRecord(
            $result['post']['circuitId']);
        $view = new ViewModel(
            [
                'paginator' => $this->db_manager->get('Sbm\Db\Eleve\Liste')->paginatorGroup(
                    Session::get('millesime'),
                    FiltreEleve::byCircuit(
                        [
                            'ligneId' => $circuit->ligneId,
                            'sens' => $circuit->sens,
                            'moment' => $circuit->moment,
                            'ordre' => $circuit->ordre
                        ], $circuit->stationId, false), [
                        'nom',
                        'prenom'
                    ], 'service', $result['criteres_obj']->getWhere()),
                'count_per_page' => $this->getPaginatorCountPerPage('nb_eleves', 10),
                'page' => $this->params('page', 1),
                'id' => $this->params('id'),
                'commune' => $result['communes'],
                'criteres_form' => $result['criteres_form']
            ]);
        $view->setTemplate('sbm-portail/commune/groupe.phtml');
        return $view;
    }

    public function circuitGroupPdfAction()
    {
        ;
    }

    public function elevesAction()
    {
        $result = $this->prepareListeEleves('eleves',
            function () {
                return $this->redirect()
                    ->toRoute('sbmportail/commune', [
                    'action' => 'index'
                ]);
            });
        if ($result instanceof Response) {
            return $result;
        }
        $where = $result['criteres_obj']->getWhere();
        $or = false;
        foreach ($result['communeIds'] as $communeId) {
            if ($or) {
                $where->or;
            } else {
                $where = $where->nest();
                $or = true;
            }
            $where->equalTo('r1.communeId', $communeId)->or->equalTo('r2.communeId',
                $communeId);
        }
        if ($or) {
            $where = $where->unnest();
        }
        $paginator = $this->db_manager->get('Sbm\Db\Query\ElevesResponsables')->paginatorScolaritesR2(
            $where, [
                'nom',
                'prenom'
            ]);
        return new ViewModel(
            [
                'paginator' => $paginator,
                'count_per_page' => $this->getPaginatorCountPerPage('nb_eleves', 10),
                'page' => $this->params('page', 1),
                'commune' => $result['communes'],
                'criteres_form' => $result['criteres_form']
            ]);
    }

    public function elevesPdfAction()
    {
        ;
    }

    public function elevesDownloadAction()
    {
        ;
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
                return $fncBack();
            }
            $args = array_merge(
                Session::get('post', [], $this->getSessionNamespace($sessionNameSpace)),
                $prg);
            $sbm_isPost = true;
            Session::set('post', $args, $this->getSessionNamespace($sessionNameSpace));
        }
        try {
            $userFeatures = new UserFeatures($this->categorieId, $this->userId,
                $this->db_manager);
        } catch (\Exception $e) {
            return $this->homePage('Entrée interdite.', FlashMessenger::NAMESPACE_ERROR);
        }
        // formulaire des critères de recherche
        $criteres_form = new CriteresForm($userFeatures->getArrayCommunes());
        $criteres_form->setValueOptions('etablissementId',
            $this->db_manager->get('Sbm\Db\Select\Etablissements')
                ->desservis())
            ->setValueOptions('classeId',
            $this->db_manager->get('Sbm\Db\Select\Classes')
                ->tout());
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
            'communes' => $userFeatures->listeDesNoms(),
            'communeIds' => $userFeatures->getCommuneIds(),
            'criteres_form' => $criteres_form,
            'criteres_obj' => $criteres_obj
        ];
    }
}