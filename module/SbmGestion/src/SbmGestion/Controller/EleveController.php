<?php
/**
 * Controller principal du module SbmGestion
 * Méthodes utilisées pour gérer les élèves et les responsables
 *
 * @project sbm
 * @package module/SbmGestion/src/SbmGestion/Controller
 * @filesource EleveController.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 21 juin 2017
 * @version 2017-2.3.4
 */
namespace SbmGestion\Controller;

use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Zend\Json\Json;
use Zend\Http\PhpEnvironment\Response;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Predicate;
use SbmBase\Model\Session;
use SbmBase\Model\StdLib;
use SbmCommun\Model\Mvc\Controller\AbstractActionController;
use SbmCommun\Model\Db\DbLib;
use SbmCommun\Model\Strategy\Semaine;
use SbmCommun\Form\ButtonForm;
use SbmCommun\Form\LatLng as LatLngForm;
use SbmCommun\Form\Responsable as FormResponsable;
use SbmCommun\Form\SbmCommun\Form;
use SbmCartographie\Model\Point;
use SbmMail\Model\Template as MailTemplate;
use SbmMail\Form\Mail as MailForm;
use SbmGestion\Form\Eleve\EditForm as FormEleve;

class EleveController extends AbstractActionController
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
     * On ne peut pas utiliser la méthode initListe('eleves') parce que l'objectDataCriteres est différent (méthode getWhere particulière)
     *
     * @return \Zend\Http\PhpEnvironment\Response|\Zend\Http\Response|\Zend\View\Model\ViewModel
     */
    public function eleveListeAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            // ce n'était pas un post. Prendre les paramètres éventuellement dans la session (cas du paginator ou de F5 )
            $this->sbm_isPost = false;
            $args = Session::get('post', [], $this->getSessionNamespace());
        } else {
            // c'était un post ; on le met en session si ce n'est pas un retour ou un cancel
            $args = $prg;
            $retour = StdLib::getParam('op', $args, '') == 'retour';
            if ($retour) {
                // dans ce cas, il s'agit du retour d'une action de type suppr, ajout ou edit. Comme pour un get, on récupère ce qui est en session.
                $this->sbm_isPost = false;
                $args = Session::get('post', [], $this->getSessionNamespace());
            } else {
                if (array_key_exists('cancel', $args)) {
                    try {
                        return $this->redirectToOrigin()->back();
                    } catch (\SbmCommun\Model\Mvc\Controller\Plugin\Exception $e) {
                        return $this->redirect()->toRoute('sbmgestion');
                    }
                } elseif (array_key_exists('origine', $args)) {
                    $this->redirectToOrigin()->setBack($args['origine']);
                    unset($args['origine']);
                }
                $this->sbm_isPost = true;
                Session::set('post', $args, $this->getSessionNamespace());
            }
        }
        // formulaire des critères de recherche
        $criteres_form = new \SbmGestion\Form\Eleve\CriteresForm();
        // initialiser le form pour les select ...
        $criteres_form->setValueOptions('etablissementId', $this->db_manager->get('Sbm\Db\Select\Etablissements')
            ->desservis())
            ->setValueOptions('classeId', $this->db_manager->get('Sbm\Db\Select\Classes'));
        // créer un objectData qui contient la méthode getWhere() adhoc
        $criteres_obj = new \SbmGestion\Model\Db\ObjectData\CriteresEleves($criteres_form->getElementNames());
        
        if ($this->sbm_isPost) {
            $criteres_form->setData($args);
            if ($criteres_form->isValid()) {
                $criteres_obj->exchangeArray($criteres_form->getData());
            }
        }
        // récupère les données de la session si le post n'a pas été validé dans le formulaire (pas de post ou invalide)
        if (! $criteres_form->hasValidated() && ! empty($args)) {
            $criteres_obj->exchangeArray($args);
            $criteres_form->setData($criteres_obj->getArrayCopy());
        }
        return new ViewModel([
            'paginator' => $this->db_manager->get('Sbm\Db\Query\ElevesResponsables')->paginatorScolaritesR2($criteres_obj->getWhere(), [
                'nom',
                'prenom'
            ]),
            'page' => $this->params('page', 1),
            'count_per_page' => $this->getPaginatorCountPerPage('nb_eleves', 10),
            'criteres_form' => $criteres_form
        ]);
    }

    /**
     * Supprime la sélection de toutes les fiches eleves
     */
    public function eleveSelectionAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        }
        $args = (array) $prg;
        if (array_key_exists('cancel', $args)) {
            return $this->redirect()->toRoute('sbmgestion/eleve', [
                'action' => 'eleve-liste',
                'page' => $this->params('page', 1)
            ]);
        }
        $form = new ButtonForm([], [
            'confirmer' => [
                'class' => 'confirm',
                'value' => 'Confirmer',
                'title' => 'Désélectionner toutes les fiches élèves.'
            ],
            'cancel' => [
                'class' => 'confirm',
                'value' => 'Abandonner'
            ]
        ], 'Confirmation', true);
        $televes = $this->db_manager->get('Sbm\Db\Table\Eleves');
        if (array_key_exists('confirmer', $args)) {
            $form->setData($args);
            if ($form->isValid()) {
                $televes->clearSelection();
                $this->flashMessenger()->addSuccessMessage('Toutes les fiches sont désélectionnées.');
                return $this->redirect()->toRoute('sbmgestion/eleve', [
                    'action' => 'eleve-liste',
                    'page' => $this->params('page', 1)
                ]);
            }
        }
        $where = new Where();
        $where->equalTo('selection', 1);
        return new ViewModel([
            'form' => $form,
            'nbSelection' => $televes->fetchAll($where)->count()
        ]);
    }

    /**
     * Si on arrive par post, on passera :
     * - ajouter : uniquement la présence de la clé.
     * En général c'est le nom du bouton submit.
     * - orinine : url d'origine de l'appel pour assurer un retour par redirectToOrigin()->back()
     * à la fin de l'opération (en général dans eleveEditAction()).
     * Si on arrive par get, on s'assurera que redirectToOrigin()->setBack() a bien été fait avant.
     *
     * Lorsqu'on arrive par post, on enregistre en session le paramètre responsableId s'il existe ou 0 sinon.
     * Lorsqu'on arrive par get, on récupère le responsableId en session. Il va permettre d'initialiser
     * le responsable1Id du formulaire.
     *
     * @return \Zend\Http\PhpEnvironment\Response|\Zend\Http\Response|\Zend\View\Model\ViewModel
     */
    public function eleveAjoutAction()
    {
        $page = $this->params('page', 1); // paramètre du retour à la liste à la fin du processus
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            // entrée lors d'un retour éventuel par F5 ou back en 22
            $prg = $this->getFromSession('post', false, $this->getSessionNamespace('ajout', 1));
        }
        $args = (array) $prg;
        if (array_key_exists('ajouter', $args)) {
            if (array_key_exists('responsableId', $args)) {
                $responsableId = $args['responsableId'];
            } else {
                $responsableId = 0;
            }
            $this->setToSession('responsableId', $responsableId, $this->getSessionNamespace('ajout', 1));
        } else {
            $responsableId = $this->getFromSession('responsableId', 0, $this->getSessionNamespace('ajout', 1));
        }
        // var_dump($responsableId);
        if (array_key_exists('origine', $args)) {
            $this->redirectToOrigin()->setBack($args['origine']);
            // par la suite, on ne s'occupe plus de 'origine' mais on ressort par un redirectToOrigin()->back()
            unset($args['origine']);
        }
        if (array_key_exists('cancel', $args)) {
            $this->removeInSession('post', $this->getSessionNamespace('ajout', 1));
            $this->removeInSession('responsableId', $this->getSessionNamespace('ajout', 1));
            $this->flashMessenger()->addInfoMessage('Saisie abandonnée.');
            try {
                return $this->redirectToOrigin()->back();
            } catch (\SbmCommun\Model\Mvc\Controller\Plugin\Exception $e) {
                return $this->redirect()->toRoute('sbmgestion/eleve', [
                    'action' => 'eleve-liste',
                    'page' => $page
                ]);
            }
        } elseif (array_key_exists('submit', $args)) {
            $ispost = true;
            // pour un retour éventuel par F5 ou back en 22
            $this->setToSession('post', $args, $this->getSessionNamespace('ajout', 1));
        } else {
            $ispost = false;
        }
        $eleveId = null;
        
        $form = new \SbmGestion\Form\Eleve\AddElevePhase1();
        $value_options = $this->db_manager->get('Sbm\Db\Select\Responsables');
        $form->setAttribute('action', $this->url()
            ->fromRoute('sbmgestion/eleve', [
            'action' => 'eleve-ajout',
            'page' => $page
        ]))
            ->setValueOptions('responsable1Id', $value_options)
            ->setValueOptions('responsable2Id', $value_options)
            ->setMaxLength($this->db_manager->getMaxLengthArray('eleves', 'table'))
            ->bind($this->db_manager->get('Sbm\Db\Table\Eleves')
            ->getObjData());
        $resultset = null;
        $odata = null;
        if ($ispost) {
            $form->setData($args);
            if ($form->isValid()) {
                $odata = $form->getData();
                // les valeurs obligatoires sont prises dans odata, responsable2Id est pris dans args pour éviter de gérer les exceptions
                $where = new Where();
                $filtreSA = new \SbmCommun\Filter\SansAccent();
                $where->equalTo('ele.nomSA', $filtreSA->filter($odata->nom))
                    ->equalTo('ele.prenomSA', $filtreSA->filter($odata->prenom))
                    ->nest()
                    ->equalTo('dateN', $odata->dateN)->or->equalTo('responsable1Id', $odata->responsable1Id)->or->equalTo('responsable2Id', $odata->responsable1Id)->or->equalTo('responsable1Id', StdLib::getParam('responsable2Id', $args, - 1))->or->equalTo('responsable2Id', StdLib::getParam('responsable2Id', $args, - 1))->unnest();
                $resultset = $this->db_manager->get('Sbm\Db\Query\ElevesResponsables')->withR2($where);
                if ($resultset->count() == 0) {
                    // pas d'homonyme. On crée cet élève (22)
                    return $this->eleveAjout22Action($odata);
                }
                $form = null;
            }
        }
        if ($form instanceof \SbmGestion\Form\Eleve\AddElevePhase1) {
            if (empty($args['responsable1Id'])) {
                $form->setData([
                    'responsable1Id' => $responsableId
                ]);
            }
        }
        return new ViewModel([
            'page' => $page,
            // form est le formulaire si les données ne sont pas validées (ou pas de données)
            'form' => is_null($form) ? $form : $form->prepare(),
            // liste est null ou est un resultset à parcourir pour montrer la liste
            'eleves' => $resultset,
            // data = null ou contient les données validées à passer à nouveau en post
            'data' => $odata
        ]);
    }

    /**
     * Reçoit un post avec :
     * - eleveId d'un élève existant
     * - info (nom prénom)
     * Met ces informations en session.
     * On reviendra ici en cas d'entrée par GET ultérieure (F5 ou back)
     * Vérifie si la fiche scolarité existe pour cette année courante et oriente sur
     * - si oui : eleveEditAction()
     * - si non : eleveAjout31Action()
     * On arrive ici obligatoirement par un post.
     * Il n'y a pas de view associée.
     */
    public function eleveAjout21Action()
    {
        $page = $this->params('page');
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false || ! array_key_exists('eleveId', $prg)) {
            $prg = $this->getFromSession('post', false, $this->getSessionNamespace('ajout', 2));
            if ($prg === false) {
                $this->flashMessenger()->addErrorMessage('Action interdite.');
                try {
                    return $this->redirectToOrigin()->back();
                } catch (\SbmCommun\Model\Mvc\Controller\Plugin\Exception $e) {
                    return $this->redirect()->toRoute('sbmgestion/eleve', [
                        'action' => 'eleve-liste',
                        'page' => $page
                    ]);
                }
            }
        } else {
            $this->setToSession('post', $prg, $this->getSessionNamespace('ajout', 2)); // pour une retour éventuel par F5 ou back
        }
        $info = stdlib::getParam('info', $prg, '');
        $eleveId = $prg['eleveId'];
        $tScolarites = $this->db_manager->get('Sbm\Db\Table\Scolarites');
        $id = [
            'millesime' => Session::get('millesime'),
            'eleveId' => $eleveId
        ];
        if ($tScolarites->is_newRecord($id)) {
            $viewmodel = $this->eleveAjout31Action($eleveId, $info);
            $viewmodel->setTemplate('sbm-gestion/eleve/eleve-ajout31.phtml');
        } else {
            $args = [
                'eleveId' => $eleveId,
                'info' => $info,
                'op' => 'ajouter'
            ];
            $viewmodel = $this->eleveEditAction($args);
            $viewmodel->setTemplate('sbm-gestion/eleve/eleve-edit.phtml');
        }
        return $viewmodel;
    }

    /**
     * Création de la fiche dans la table eleve et récupération de son eleveId
     * puis passage en deleveAjout31Action()
     * L'entrée se fait :
     * - directement depuis eleveAjoutAction() s'il n'y a pas d'enregistrement ayant ces caractéristiques.
     * Dans ce cas, le paramètre odata porte les informations à enregistrer.
     * - par appel POST depuis la vue phase 1 si l'utilisateur choisi explicitement de créer une nouvelle fiche.
     * Dans ce cas, les paramètres reçus par POST sont :
     * - le contenu du formulaire AddElevePhase1 renvoyé par des hiddens depuis la liste
     * Le retour par get est interdit afin d'éviter de recréer cet enregistrement.
     *
     * @param \SbmCommun\Model\Db\ObjectData\ObjectDataInterface $odata            
     *
     * @return \Zend\Http\PhpEnvironment\Response
     */
    public function eleveAjout22Action($odata = null)
    {
        $page = $this->params('page', 1); // pour le retour à la liste à la fin du processus
        $tEleves = $this->db_manager->get('Sbm\Db\Table\Eleves');
        if (is_null($odata)) {
            $prg = $this->prg();
            if ($prg instanceof Response) {
                return $prg;
            } elseif ($prg === false) {
                // retour vers le point d'entrée d'un F5 ou d'un back
                return $this->redirect()->toRoute('sbmgestion/eleve', [
                    'action' => 'eleve-ajout',
                    'page' => $page
                ]);
            } else {
                $odata = $tEleves->getObjData();
                $odata->exchangeArray($prg);
            }
        }
        // ici, $odata contient les données à insérer dans la table eleves
        $tEleves->saveRecord($odata);
        $eleveId = $tEleves->getTableGateway()->getLastInsertValue();
        $viewmodel = $this->eleveAjout31Action($eleveId, $odata->nom . ' ' . $odata->prenom);
        $viewmodel->setTemplate('sbm-gestion/eleve/eleve-ajout31.phtml');
        return $viewmodel;
    }

    /**
     * Il s'agit de compléter les informations de scolarité pour un élève existant.
     * Donc en cas de F5 ou back
     * on doit revenir en eleveAjout21Action() car la fiche eleve existe.
     *
     * L'entrée initiale se fait toujours par un appel fonction.
     * On montre le formulaire AddElevePhase2 pour compléter les informations de scolarités.
     * L'entrée par POST correspond au retour du formulaire et contient donc obligatoirement
     * un 'cancel' ou un 'submit' et dans ce dernier cas les données doivent être validées par le formulaire.
     *
     * @param string $eleveId            
     * @param string $info            
     * @return \Zend\Http\PhpEnvironment\Response|\Zend\Http\Response|\Zend\View\Model\ViewModel
     */
    public function eleveAjout31Action($eleveId = null, $info = null)
    {
        $page = $this->params('page', 1);
        $ispost = false;
        if (is_null($eleveId)) {
            $prg = $this->prg();
            if ($prg instanceof Response) {
                return $prg;
            } elseif ($prg === false) {
                // Cela pourrait être F5 ou back du navigateur. Il faut savoir si la fiche a été créée.
                // On reviendra donc toujours à eleveAjout21Action() pour vérifier.
                return $this->redirect()->toRoute('sbmgestion/eleve', [
                    'action' => 'eleve-ajout21',
                    'page' => $page
                ]);
            } else {
                // c'est le traitement du retour par POST du formulaire après prg
                $args = $prg;
                if (array_key_exists('cancel', $args)) {
                    $this->flashMessenger()->addInfoMessage('Saisie abandonnée.');
                    try {
                        return $this->redirectToOrigin()->back();
                    } catch (\SbmCommun\Model\Mvc\Controller\Plugin\Exception $e) {
                        return $this->redirect()->toRoute('sbmgestion/eleve', [
                            'action' => 'eleve-liste',
                            'page' => $page
                        ]);
                    }
                }
                // on récupère eleveId et info
                $eleveId = StdLib::getParam('eleveId', $prg, false);
                $info = StdLib::getParam('info', $args, '');
                if (! $eleveId) {
                    // on a perdu la donnée essentielle : il faut tout recommencer
                    return $this->redirect()->toRoute('sbmgestion/eleve', [
                        'action' => 'eleve-ajout',
                        'page' => $page
                    ]);
                }
                $ispost = array_key_exists('submit', $args);
            }
        }
        // ici on a un eleveId qui possède une fiche dans la table eleves et pour lequel on doit saisir la scolarite
        $tableScolarites = $this->db_manager->get('Sbm\Db\Table\Scolarites');
        $form = new \SbmGestion\Form\Eleve\AddElevePhase2();
        
        $value_options = $this->db_manager->get('Sbm\Db\Select\Responsables');
        $form->setAttribute('action', $this->url()
            ->fromRoute('sbmgestion/eleve', [
            'action' => 'eleve-ajout31',
            'page' => $page
        ]))
            ->setValueOptions('etablissementId', $this->db_manager->get('Sbm\Db\Select\Etablissements')
            ->desservis())
            ->setValueOptions('classeId', $this->db_manager->get('Sbm\Db\Select\Classes'))
            ->setValueOptions('joursTransport', Semaine::getJours())
            ->bind($tableScolarites->getObjData());
        if ($ispost) {
            $form->setData($args);
            if ($form->isValid()) {
                $odata = $form->getData();
                $odata->millesime = Session::get('millesime');
                $odata->internet = 0;
                $tTarifs = $this->db_manager->get('Sbm\Db\Table\Tarifs');
                if ($odata->anneeComplete) {
                    $odata->tarifId = $tTarifs->getTarifId('tarif1');
                } else {
                    $odata->tarifId = $tTarifs->getTarifId('tarif2');
                }
                // $odata->tarifId = $this->db_manager->get('Sbm\Db\Table\Tarifs')->getTarifId('inscription');
                $tableScolarites->saveRecord($odata);
                $viewModel = $this->eleveEditAction([
                    'eleveId' => $eleveId,
                    'info' => $info,
                    'op' => 'ajouter'
                ]);
                $viewModel->setTemplate('sbm-gestion/eleve/eleve-edit.phtml');
                return $viewModel;
            }
        }
        // initialisation du formulaire
        $where = new Where();
        $where->equalTo('eleveId', $eleveId);
        $data = $this->db_manager->get('Sbm\Db\Query\ElevesResponsables')
            ->withR2($where)
            ->current();
        $form->setData([
            'eleveId' => $eleveId,
            'responsable1Id' => $data['responsable1Id'],
            'responsable2Id' => isset($data['responsable2Id']) ? $data['responsable2Id'] : '',
            'dateDebut' => Session::get('as')['dateDebut'],
            'dateFin' => Session::get('as')['dateFin'],
            'demandeR1' => 1,
            'demandeR2' => 0
        ]);
        return new ViewModel([
            'page' => $page,
            'form' => $form->prepare(),
            'info' => $info,
            'data' => $data,
            'scolarite_precedente' => $this->db_manager->get('Sbm\Db\Query\ElevesScolarites')->getScolaritePrecedente($eleveId)
        ]);
    }

    /**
     * Cette méthode est généralement appelée par post et reçoit
     * - eleveId
     * - info
     * - origine (optionnel) ou group (optionnel)
     * - op = 'modifier' ou 'ajouter'
     * Elle peut être appelée en passant un paramètre $args qui sera un tableau contenant ces 4 clés.
     * Mais si on arrive par eleveAjoutAction() on ne passera pas origine car le redirectToOrigin()
     * est déjà en place.
     *
     * @return \Zend\Http\PhpEnvironment\Response|\Zend\Http\Response|\Zend\View\Model\ViewModel
     */
    public function eleveEditAction($args = null)
    {
        $currentPage = $this->params('page', 1);
        if (is_null($args)) {
            $prg = $this->prg();
            if ($prg instanceof Response) {
                return $prg;
            } elseif ($prg === false || (isset($prg['op']) && $prg['op'] == 'retour')) {
                $args = $this->getFromSession('post', false);
                if ($args === false) {
                    $this->flashMessenger()->addErrorMessage('Action interdite');
                    try {
                        return $this->redirectToOrigin()->back();
                    } catch (\SbmCommun\Model\Mvc\Controller\Plugin\Exception $e) {
                        return $this->redirect()->toRoute('login', [
                            'action' => 'logout'
                        ]);
                    }
                }
            } else {
                $args = $prg;
                // !!! important !!! traiter 'cancel' avant 'origine'
                if (array_key_exists('cancel', $args)) {
                    $this->flashMessenger()->addWarningMessage("L'enregistrement n'a pas été modifié.");
                    try {
                        return $this->redirectToOrigin()->back();
                    } catch (\SbmCommun\Model\Mvc\Controller\Plugin\Exception $e) {
                        return $this->redirect()->toRoute('sbmgestion/eleve', [
                            'action' => 'eleve-liste',
                            'page' => $currentPage
                        ]);
                    }
                }
                
                if (array_key_exists('group', $args)) {
                    $this->redirectToOrigin()->setBack($args['group']);
                    unset($args['group']);
                    $this->setToSession('post', $args);
                } elseif (array_key_exists('origine', $args)) {
                    $this->redirectToOrigin()->setBack($args['origine']);
                    unset($args['origine']);
                    $this->setToSession('post', $args);
                }
            }
        } else {
            if (isset($args['group'])) {
                $this->redirectToOrigin()->setBack($args['group']);
                unset($args['group']);
            } elseif (isset($args['origine'])) {
                $this->redirectToOrigin()->setBack($args['origine']);
                unset($args['origine']);
            }
            $this->setToSession('post', $args);
        }
        if (! array_key_exists('eleveId', $args)) {
            $this->flashMessenger()->addErrorMessage("Pas d'identifiant élève !");
            try {
                return $this->redirectToOrigin()->back();
            } catch (\SbmCommun\Model\Mvc\Controller\Plugin\Exception $e) {
                return $this->redirect()->toRoute('sbmgestion/eleve', [
                    'action' => 'eleve-liste',
                    'page' => $currentPage
                ]);
            }
        }
        $eleveId = $args['eleveId'];
        if ($eleveId == - 1) {
            try {
                return $this->redirectToOrigin()->back();
            } catch (\SbmCommun\Model\Mvc\Controller\Plugin\Exception $e) {
                return $this->redirect()->toRoute('sbmgestion/eleve', [
                    'action' => 'eleve-liste',
                    'page' => $currentPage
                ]);
            }
        }
        $tEleves = $this->db_manager->get('Sbm\Db\Table\Eleves');
        $tScolarites = $this->db_manager->get('Sbm\Db\Table\Scolarites');
        $tTarifs = $this->db_manager->get('Sbm\Db\Table\Tarifs');
        $aTarifs = $tTarifs->getTarifs();
        $tarifId1 = $tTarifs->getTarifId('tarif1');
        $tarifId2 = $tTarifs->getTarifId('tarif2');
        $qAffectations = $this->db_manager->get('Sbm\Db\Query\AffectationsServicesStations');
        // les invariants
        $invariants = [];
        $historique = [];
        $odata0 = $tEleves->getRecord($eleveId);
        if ($odata0->dateN == '0000-00-00') {
            $odata0->dateN = '1900-01-01';
        }
        $historique['eleve']['dateCreation'] = $odata0->dateCreation;
        $historique['eleve']['dateModification'] = $odata0->dateModification;
        $invariants['numero'] = $odata0->numero;
        $odata1 = $tScolarites->getRecord([
            'millesime' => Session::get('millesime'),
            'eleveId' => $eleveId
        ]);
        if ($odata1->inscrit) {
            $inscrit = $odata1->paiement;
            $inscrit |= $odata1->fa;
            $inscrit |= $odata1->gratuit > 0;
            $inscrit |= ($odata1->demandeR1 == 2 && $odata1->accordR1 == 0 && $odata1->subventionR1 == 1);
            $inscrit |= ($odata1->demandeR2 == 2 && $odata1->accordR2 == 0 && $odata1->subventionR2 == 1);
            $invariants['etat'] = $inscrit ? 'Inscrit' : 'Préinscrit';
        } else {
            $invariants['etat'] = 'Rayé';
        }
        switch ($odata1->gratuit) {
            case 0:
                $invariants['paiement'] = 'Famille';
                break;
            case 1:
                $invariants['paiement'] = 'Gratuit';
                break;
            default:
                $invariants['paiement'] = 'Organisme';
                break;
        }
        $historique['scolarite']['dateInscription'] = $odata1->dateInscription;
        $historique['scolarite']['dateModification'] = $odata1->dateModification;
        $historique['scolarite']['tarifs'] = json_encode($aTarifs);
        $historique['scolarite']['duplicata'] = $odata1->duplicata;
        $historique['scolarite']['internet'] = $odata1->internet;
        
        $respSelect = $this->db_manager->get('Sbm\Db\Select\Responsables');
        $etabSelect = $this->db_manager->get('Sbm\Db\Select\Etablissements')->desservis();
        $clasSelect = $this->db_manager->get('Sbm\Db\Select\Classes');
        $form = new FormEleve();
        $form->setAttribute('action', $this->url()
            ->fromRoute('sbmgestion/eleve', [
            'action' => 'eleve-edit',
            'page' => $currentPage
        ]))
            ->setValueOptions('responsable1Id', $respSelect)
            ->setValueOptions('responsable2Id', $respSelect)
            ->setValueOptions('etablissementId', $etabSelect)
            ->setValueOptions('classeId', $clasSelect)
            ->setValueOptions('joursTransport', Semaine::getJours())
            ->setMaxLength($this->db_manager->getMaxLengthArray('eleves', 'table'));
        if (array_key_exists('submit', $args)) {
            $form->setData($args);
            if ($form->isValid()) { // controle le csrf
                $millesime = Session::get('millesime');
                $dataValid = array_merge([
                    'millesime' => $millesime
                ], $form->getData());
                // changeR1 et changeR2 indiquent s'il faut mette à jour en cascade le changement de responsable dans la table affectations
                if (! $dataValid['ga'] && ! empty($dataValid['responsable2Id'])) {
                    $dataValid['responsable2Id'] = '';
                }
                $changeR1 = $dataValid['responsable1Id'] != $odata0->responsable1Id;
                $changeR2 = false;
                if (! is_null($odata0->responsable2Id)) {
                    $changeR2 = empty($dataValid['responsable2Id']) || $dataValid['responsable2Id'] != $odata0->responsable2Id;
                }
                // enregistrement dans la table eleves
                $tEleves->saveRecord($tEleves->getObjData()
                    ->exchangeArray($dataValid));
                // maj en cascade dans la table affectations
                $tAffectations = $this->db_manager->get('Sbm\Db\Table\Affectations');
                if ($changeR1) {
                    // maj du responsableId
                    $tAffectations->updateResponsableId($millesime, $eleveId, $odata0->responsable1Id, $dataValid['responsable1Id']);
                }
                if ($changeR2) {
                    if (empty($dataValid['responsable2Id'])) {
                        // suppression des affectations relatives à cet élève pour ce millesime
                        $tAffectations->deleteResponsableId($millesime, $eleveId, $odata0->responsable2Id);
                        $dataValid['demandeR2'] = 0;
                    } else {
                        // maj du responsableId
                        $tAffectations->updateResponsableId($millesime, $eleveId, $odata0->responsable2Id, $dataValid['responsable2Id']);
                    }
                }
                // enregistrement dans la table scolarites
                $odata = $tScolarites->getObjData()->exchangeArray($dataValid);
                if ($odata->anneeComplete) {
                    $odata->tarifId = $tarifId1;
                } else {
                    $odata->tarifId = $tarifId2;
                }
                $recalcul = $tScolarites->saveRecord($odata);
                // recalcul des droits et des distances en cas de modification de la destination ou d'une origine
                if ($recalcul || $changeR1 || $changeR2) {
                    $majDistances = $this->cartographie_manager->get('Sbm\CalculDroitsTransport');
                    if ($odata1->district) {
                        $majDistances->majDistancesDistrictSansPerte($eleveId);
                    } else {
                        $majDistances->majDistancesDistrict($eleveId);
                    }
                }
                $this->flashMessenger()->addSuccessMessage("Les modifications ont été enregistrées.");
                try {
                    return $this->redirectToOrigin()->back();
                } catch (\SbmCommun\Model\Mvc\Controller\Plugin\Exception $e) {
                    return $this->redirect()->toRoute('sbmgestion/eleve', [
                        'action' => 'eleve-liste',
                        'page' => $currentPage
                    ]);
                }
            } else {
                $identite = $args['nom'] . ' ' . $args['prenom'];
            }
        } else {
            $identite = $odata0->nom . ' ' . $odata0->prenom;
            $adata1 = $odata1->getArrayCopy();
            if ($odata1->anneeComplete) {
                $adata1['dateDebut'] = Session::get('as')['dateDebut'];
                $adata1['dateFin'] = Session::get('as')['dateFin'];
            }
            $form->setData(array_merge($odata0->getArrayCopy(), $adata1));
        }
        // historique des responsables
        $r = $this->db_manager->get('Sbm\Db\Table\Responsables')->getRecord($odata0->responsable1Id);
        $args_paiement = [
            'responsableId' => $odata0->responsable1Id,
            'responsable' => sprintf('%s %s %s', $r->titre, $r->nom, $r->prenom)
        ];
        $historique['responsable1']['dateCreation'] = $r->dateCreation;
        $historique['responsable1']['dateModification'] = $r->dateModification;
        $historique['responsable1']['dateDemenagement'] = $r->dateDemenagement;
        $historique['responsable1']['demenagement'] = $r->demenagement;
        $tmp = $odata0->responsable2Id;
        if (! empty($tmp)) {
            $r = $this->db_manager->get('Sbm\Db\Table\Responsables')->getRecord($odata0->responsable2Id);
            $historique['responsable2']['dateCreation'] = $r->dateCreation;
            $historique['responsable2']['dateModification'] = $r->dateModification;
            $historique['responsable2']['dateDemenagement'] = $r->dateDemenagement;
            $historique['responsable2']['demenagement'] = $r->demenagement;
        }
        $affectations = [];
        foreach ($qAffectations->getAffectations($eleveId) as $row) {
            $affectations[] = $row;
        }
        return new ViewModel([
            'form' => $form->prepare(),
            'page' => $currentPage, // nécessaire pour la compatibilité des appels
            'eleveId' => $eleveId, // nécessaire pour la compatibilité des appels
            'identite' => $identite, // nécessaire pour la compatibilité des appels
            'data' => $invariants,
            'historique' => $historique,
            'args_paiement' => $args_paiement,
            'affectations' => $affectations,
            'scolarite_precedente' => $this->db_manager->get('Sbm\Db\Query\ElevesScolarites')->getScolaritePrecedente($eleveId)
        ]);
    }

    public function eleveInscrireAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            $prg = $this->getFromSession('post', false, $this->getSessionNamespace('ajout', 2));
            if ($prg == false) {
                try {
                    return $this->redirectToOrigin()->back();
                } catch (\SbmCommun\Model\Mvc\Controller\Plugin\Exception $e) {
                    $this->flashMessenger()->addErrorMessage('Action interdite');
                    return $this->redirect()->toRoute('sbmgestion/eleve');
                }
            }
        } elseif (array_key_exists('origine', $prg)) {
            $this->redirectToOrigin()->setBack($prg['origine']);
            unset($prg['origine']);
        }
        $this->setToSession('post', $prg, $this->getSessionNamespace('ajout', 2));
        return $this->redirect()->toRoute('sbmgestion/eleve', [
            'action' => 'eleve-ajout21',
            'page' => $this->params('page', 1)
        ]);
    }

    public function eleveRayerAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg == false) {
            $prg = $this->getFromSession('post', false, $this->getSessionNamespace());
            if ($prg == false) {
                try {
                    return $this->redirectToOrigin()->back();
                } catch (\SbmCommun\Model\Mvc\Controller\Plugin\Exception $e) {
                    $this->flashMessenger()->addErrorMessage('Action interdite');
                    return $this->redirect()->toRoute('sbmgestion/eleve');
                }
            }
        } elseif (array_key_exists('origine', $prg)) {
            $this->redirectToOrigin()->setBack($prg['origine']);
            unset($prg['origine']);
            $this->setToSession('post', $prg, $this->getSessionNamespace());
        }
        if (! array_key_exists('eleveId', $prg)) {
            try {
                return $this->redirectToOrigin()->back();
            } catch (\SbmCommun\Model\Mvc\Controller\Plugin\Exception $e) {
                $this->flashMessenger()->addErrorMessage('Action interdite');
                return $this->redirect()->toRoute('sbmgestion/eleve');
            }
        }
        $tScolarites = $this->db_manager->get('Sbm\Db\Table\Scolarites');
        $scolarite = $tScolarites->getRecord([
            'millesime' => Session::get('millesime'),
            'eleveId' => $prg['eleveId']
        ]);
        $scolarite->inscrit = 1 - $scolarite->inscrit;
        $tScolarites->saveRecord($scolarite);
        $msg = $scolarite->inscrit ? 'La fiche de cet élève a été activée.' : 'Cet élève a été rayé.';
        $this->flashMessenger()->addSuccessMessage($msg);
        try {
            return $this->redirectToOrigin()->back();
        } catch (\SbmCommun\Model\Mvc\Controller\Plugin\Exception $e) {
            return $this->redirect()->toRoute('sbmgestion/eleve');
        }
    }

    public function eleveGroupAction()
    {
        $currentPage = $this->params('page', 1);
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            $args = $this->getFromSession('post', false);
            if ($args === false) {
                $this->flashMessenger()->addErrorMessage('Action interdite');
                return $this->redirect()->toRoute('login', [
                    'action' => 'logout'
                ]);
            }
        } else {
            $args = $prg;
            if (array_key_exists('origine', $args)) {
                $this->redirectToOrigin()->setBack($args['origine']);
                unset($args['origine']);
                $this->setToSession('post', $args);
            }
            if (array_key_exists('cancel', $args)) {
                $this->flashMessenger()->addWarningMessage("L'enregistrement n'a pas été modifié.");
                try {
                    return $this->redirectToOrigin()->back();
                } catch (\SbmCommun\Model\Mvc\Controller\Plugin\Exception $e) {
                    return $this->redirect()->toRoute('sbmgestion/eleve', [
                        'action' => 'eleve-liste',
                        'page' => $currentPage
                    ]);
                }
            }
        }
        $where = new Where();
        $or = false;
        $tResponsableIds = explode('|', $args['op']);
        foreach ($tResponsableIds as $responsableId) {
            if ($or)
                $where->OR;
            $where->equalTo('responsable1Id', $responsableId)->OR->equalTo('responsable2Id', $responsableId);
            $or = true;
        }
        $viewmodel = new ViewModel([
            'paginator' => $this->db_manager->get('Sbm\Db\Query\ElevesResponsables')->paginatorScolaritesR2($where),
            'page' => $this->params('page', 1),
            'count_per_page' => $this->getPaginatorCountPerPage('nb_eleves', 10),
            'criteres_form' => null,
            'groupe' => $args['op']
        ]);
        $viewmodel->setTemplate('sbm-gestion/eleve/eleve-liste.phtml');
        return $viewmodel;
    }

    /**
     * On reçoit par post un paramètre 'documentId' qui peut être numérique (le documentId de la table documents) ou
     * une chaine de caractères.
     * Dans ce cas, cela peut être le name du document ou le libelle de docaffectations et
     * alors le paramètre id passé par post contient docaffectationId.
     * On lit les critères définis dans le formulaire de critères de eleve-liste (en session avec le sessionNameSpace de eleveListeAction).
     * On transmet le where pour les documents basés sur une table ou vue sql et les tableaux expression, criteres et strict pour
     * ceux basés sur une requête SQL.
     *
     * @return \Zend\Http\PhpEnvironment\Response|\Zend\Http\Response
     */
    public function elevePdfAction()
    {
        $criteresObject = '\SbmGestion\Model\Db\ObjectData\CriteresEleves';
        $criteresForm = '\SbmGestion\Form\Eleve\CriteresForm';
        $documentId = null;
        $retour = [
            'route' => 'sbmgestion/eleve',
            'action' => 'eleve-liste'
        ];
        return $this->documentPdf($criteresObject, $criteresForm, $documentId, $retour);
    }

    public function eleveGroupePdfAction()
    {
        $criteresObject = [
            '\SbmGestion\Model\Db\ObjectData\CriteresEleves',
            null,
            function ($where, $args) {
                $where = new Where();
                $or = false;
                $tResponsableIds = explode('|', $args['op']);
                foreach ($tResponsableIds as $responsableId) {
                    if ($or)
                        $where->OR;
                    $where->equalTo('responsable1Id', $responsableId)->OR->equalTo('responsable2Id', $responsableId);
                    $or = true;
                }
                return $where;
            }
        ];
        $criteresForm = '\SbmGestion\Form\Eleve\CriteresForm';
        $documentId = null;
        $retour = [
            'route' => 'sbmgestion/eleve',
            'action' => 'eleve-groupe'
        ];
        return $this->documentPdf($criteresObject, $criteresForm, $documentId, $retour);
    }

    public function eleveSupprAction()
    {
        $prg = $this->prg();
        $rayer = $supprimer = false;
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            $args = $this->getFromSession('post', false, $this->getSessionNamespace());
            if ($args === false) {
                $this->flashMessenger()->addErrorMessage('Action interdite.');
                return $this->redirect()->toRoute('sbmgestion/eleve', [
                    'action' => 'eleve-liste',
                    'page' => $this->params('page', 1)
                ]);
            }
        } else {
            $args = $prg;
            if (array_key_exists('cancel', $args) || ! array_key_exists('eleveId', $args)) {
                $this->flashMessenger()->addWarningMessage('Abandon de la suppression.');
                return $this->redirect()->toRoute('sbmgestion/eleve', [
                    'action' => 'eleve-liste',
                    'page' => $this->params('page', 1)
                ]);
            }
            $rayer = array_key_exists('rayer', $args);
            $supprimer = array_key_exists('confirmer', $args);
            unset($args['rayer'], $args['confirmer']);
            $this->setToSession('post', $args, $this->getSessionNamespace());
        }
        $form = new ButtonForm([
            'eleveId' => $args['eleveId']
        ], [
            'confirmer' => [
                'class' => 'confirm',
                'value' => 'Supprimer',
                'title' => 'Cette action est irréversible.'
            ],
            'rayer' => [
                'class' => 'confirm',
                'value' => 'Rayer',
                'title' => 'L\'élève ne sera plus inscrit mais restera enregistré dans la base.'
            ],
            'cancel' => [
                'class' => 'confirm',
                'value' => 'Abandonner'
            ]
        ]);
        $millesime = Session::get('millesime');
        if ($rayer) {
            $where = new Where();
            $where->equalTo('millesime', $millesime)->equalTo('eleveId', $args['eleveId']);
            $this->db_manager->get('Sbm\Db\Table\Affectations')->deleteRecord($where);
            $this->db_manager->get('Sbm\Db\Table\Scolarites')->setInscrit($millesime, $args['eleveId'], 0);
            $this->flashMessenger()->addSuccessMessage('L\'élève a été rayée.');
            return $this->redirect()->toRoute('sbmgestion/eleve', [
                'action' => 'eleve-liste',
                'page' => $this->params('page', 1)
            ]);
        } elseif ($supprimer) {
            $where = new Where();
            $where->equalTo('millesime', $millesime)->equalTo('eleveId', $args['eleveId']);
            $this->db_manager->get('Sbm\Db\Table\Affectations')->deleteRecord($where);
            $this->db_manager->get('Sbm\Db\Table\Scolarites')->deleteRecord($where);
            $this->db_manager->get('Sbm\Db\Table\Eleves')->deleteRecord($args['eleveId']);
            $this->flashMessenger()->addSuccessMessage('L\'inscription a été supprimée.');
            return $this->redirect()->toRoute('sbmgestion/eleve', [
                'action' => 'eleve-liste',
                'page' => $this->params('page', 1)
            ]);
        }
        return new ViewModel([
            'form' => $form->prepare(),
            'page' => $this->params('page', 1),
            'eleve' => $this->db_manager->get('Sbm\Db\Query\ElevesScolarites')->getEleve($args['eleveId']),
            'affectations' => $this->db_manager->get('Sbm\Db\Query\AffectationsServicesStations')->getCorrespondances($args['eleveId'])
        ]);
    }

    public function eleveReinscriptionOuiAction()
    {
        return $this->eleveReinscriptionChange(1);
    }

    public function eleveReinscriptionNonAction()
    {
        return $this->eleveReinscriptionChange(0);
    }

    private function eleveReinscriptionChange($flag)
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            $this->flashMessenger()->addErrorMessage('Echec');
            try {
                return $this->redirectToOrigin()->back();
            } catch (\SbmCommun\Model\Mvc\Controller\Plugin\Exception $e) {
                $this->flashMessenger()->addErrorMessage('Action interdite');
                return $this->redirect()->toRoute('sbmgestion/eleve', [
                    'action' => 'responsable-liste',
                    'page' => 1
                ]);
            }
        } elseif (array_key_exists('origine', $prg)) {
            $this->redirectToOrigin()->setBack($prg['origine']);
            unset($prg['origine']);
        }
        if (array_key_exists('eleveId', $prg)) {
            $eleveId = $prg['eleveId'];
            $tEleves = $this->db_manager->get('Sbm\Db\Table\Eleves');
            $tEleves->setMailchimp($eleveId, $flag);
            $this->flashMessenger()->addSuccessMessage('Changement effectué');
        }
        try {
            return $this->redirectToOrigin()->back();
        } catch (\SbmCommun\Model\Mvc\Controller\Plugin\Exception $e) {
            $this->flashMessenger()->addErrorMessage('Action interdite');
            return $this->redirect()->toRoute('sbmgestion/eleve', [
                'action' => 'responsable-liste',
                'page' => 1
            ]);
        }
    }

    public function eleveLocalisationAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            $args = $this->getFromSession('post', false);
            if ($args === false) {
                $this->flashMessenger()->addErrorMessage('Action interdite');
                return $this->redirect()->toRoute('login', [
                    'action' => 'logout'
                ]);
            }
        } else {
            $args = $prg;
            // l'url de retour est dans la clé 'origine'
            if (array_key_exists('origine', $args)) {
                $this->redirectToOrigin()->setBack($args['origine']);
                unset($args['origine']);
                $this->setToSession('post', $args);
            }
            if (array_key_exists('cancel', $args)) {
                $this->flashMessenger()->addWarningMessage('La localisation de cet élève n\'a pas été enregistrée.');
                try {
                    return $this->redirectToOrigin()->back();
                } catch (\SbmCommun\Model\Mvc\Controller\Plugin\Exception $e) {
                    return $this->redirect()->toRoute('sbmgestion/eleve', [
                        'action' => 'eleve-edit'
                    ]);
                }
            }
        }
        // les outils de travail : formulaire et convertisseur de coordonnées
        // nécessaire pour valider lat et lng
        $configCarte = StdLib::getParam('parent', $this->cartographie_manager->get('cartes'));
        // ici, il faut un formulaire permettant de saisir l'adresse particulière d'un élève. Le tout est enregistré dans scolarites
        $form = new \SbmGestion\Form\Eleve\LocalisationAdresse($configCarte['valide']);
        $form->setAttribute('action', $this->url()
            ->fromRoute('sbmgestion/eleve', [
            'action' => 'eleve-localisation'
        ]))
            ->setValueOptions('communeId', $this->db_manager->get('Sbm\Db\Select\Communes')
            ->desservies());
        $d2etab = $this->cartographie_manager->get('SbmCarto\DistanceEtablissements');
        // chercher l'élève dans la table
        $eleve = $this->db_manager->get('Sbm\Db\Query\ElevesScolarites')->getEleve($args['eleveId']);
        // traitement de la réponse
        if (array_key_exists('submit', $args)) {
            $form->setData($args);
            $pt = new Point($args['lng'], $args['lat'], 0, 'degré');
            if ($form->isValid()) {
                // détermine le point. Il est reçu en gRGF93 et sera enregistré en XYZ
                $point = $d2etab->getProjection()->gRGF93versXYZ($pt);
                // enregistre les coordonnées dans la table
                $tableScolarites = $this->db_manager->get('Sbm\Db\Table\Scolarites');
                $oData = $tableScolarites->getObjData();
                $oData->exchangeArray($form->getData());
                $oData->millesime = Session::get('millesime');
                $oData->x = $point->getX();
                $oData->y = $point->getY();
                // calcul de la distance à l'établissement
                $tableEtablissements = $this->db_manager->get('Sbm\Db\Table\Etablissements');
                $etablissement = $tableEtablissements->getRecord($eleve['etablissementId']);
                $pointEtablissement = new Point($etablissement->x, $etablissement->y);
                $ptetab = $d2etab->getProjection()->XYZversgRGF93($pointEtablissement);
                $d = $d2etab->calculDistance($pt, $ptetab);
                $oData->distanceR1 = round($d / 1000, 1);
                // enregistre
                $tableScolarites->saveRecord($oData);
                $this->flashMessenger()->addSuccessMessage('Cette adresse est enregistrée avec sa localisation.');
                try {
                    return $this->redirectToOrigin()->back();
                } catch (\SbmCommun\Model\Mvc\Controller\Plugin\Exception $e) {
                    return $this->redirect()->toRoute('sbmgestion/eleve', [
                        'action' => 'eleve-edit'
                    ]);
                }
            }
            // reprendre les données pour charger le formulaire
            $data = $args;
        } elseif (array_key_exists('remove', $args)) {
            // cherche l'établissement
            $tableEtablissements = $this->db_manager->get('Sbm\Db\Table\Etablissements');
            $etablissement = $tableEtablissements->getRecord($eleve['etablissementId']);
            $pointEtablissement = new Point($etablissement->x, $etablissement->y);
            $ptetab = $d2etab->getProjection()->XYZversgRGF93($pointEtablissement);
            // recherche le responsable1 pour calculer la distance
            $eleveR1 = $this->db_manager->get('Sbm\Db\Query\ElevesResponsables')->getEleveResponsable1($args['eleveId']);
            $point = new Point($eleveR1['x1'], $eleveR1['y1']);
            $pt = $d2etab->getProjection()->XYZversgRGF93($point);
            $d = $d2etab->calculDistance($pt, $ptetab);
            // supprimer les références à l'adresse perso de l'élève
            $data = [
                'millesime' => Session::get('millesime'),
                'eleveId' => $args['eleveId'],
                'url_api' => $this->cartographie_manager->get('google_api')['js'],
                'chez' => null,
                'adresseL1' => null,
                'adresseL2' => null,
                'codePostal' => null,
                'communeId' => null,
                'x' => 0,
                'y' => 0,
                'distanceR1' => round($d / 1000, 1)
            ];
            $tableScolarites = $this->db_manager->get('Sbm\Db\Table\Scolarites');
            $oData = $tableScolarites->getObjData();
            $oData->exchangeArray($data);
            // enregistre
            $tableScolarites->saveRecord($oData);
            $this->flashMessenger()->addSuccessMessage('Cette adresse a été effacée.');
            try {
                return $this->redirectToOrigin()->back();
            } catch (\SbmCommun\Model\Mvc\Controller\Plugin\Exception $e) {
                return $this->redirect()->toRoute('sbmgestion/eleve', [
                    'action' => 'eleve-edit'
                ]);
            }
        } else {
            // préparer le Point dans le système gRGF93
            $point = new Point($eleve['x'], $eleve['y']);
            $pt = $d2etab->getProjection()->XYZversgRGF93($point);
            $pt->setLatLngRange($configCarte['valide']['lat'], $configCarte['valide']['lng']);
            if (! $pt->isValid()) {
                $pt->setLatitude($configCarte['centre']['lat']);
                $pt->setLongitude($configCarte['centre']['lng']);
            }
            // préparer le tableau de données pour initialiser le formulaire
            $data = [
                'eleveId' => $args['eleveId'],
                'lat' => $pt->getLatitude(),
                'lng' => $pt->getLongitude(),
                'chez' => $eleve['chez'],
                'adresseL1' => $eleve['adresseEleveL1'],
                'adresseL2' => $eleve['adresseEleveL2'],
                'codePostal' => $eleve['codePostalEleve'],
                'communeId' => $eleve['communeEleveId']
            ];
        }
        // charger le formulaire
        $form->setData($data);
        return new ViewModel([
            'point' => $pt,
            'form' => $form->prepare(),
            'eleveId' => $args['eleveId'],
            'eleve' => $eleve,
            'url_api' => $this->cartographie_manager->get('google_api')['js'],
            'config' => $configCarte
        ]);
    }

    /**
     * Liste des responsables
     * Passer nbEnfants, nbInscrits et nbPreinscrits en strict parce qu'on recherche l'égalité et que l'on veut pouvoir compter les "== 0"
     * Ici, le formulaire de critères utilise des alias de champs puisque certains champs doivent être préfixés pour lever les ambiguités
     * (voir requête 'Sbm\Db\Query\Responsables') et d'autres sont des expressions.
     *
     * @return ViewModel
     */
    public function responsableListeAction()
    {
        $projection = $this->cartographie_manager->get('SbmCarto\Projection');
        $rangeX = $projection->getRangeX();
        $rangeY = $projection->getRangeY();
        $pasLocalisaton = 'Literal:Not((x Between %d And %d) And (y Between %d And %d))';
        
        $args = $this->initListe('responsables', null, [
            'nbEnfants',
            'nbInscrits',
            'nbPreinscrits'
        ], [
            'nomSA' => 'res.nomSA',
            'selection' => 'res.selection',
            'inscrits' => 'Literal:count(ins.eleveId) > 0',
            'preinscrits' => 'Literal:count(pre.eleveId) > 0',
            'localisation' => sprintf($pasLocalisaton, $rangeX['parent'][0], $rangeX['parent'][1], $rangeY['parent'][0], $rangeY['parent'][1])
        ]);
        if ($args instanceof Response)
            return $args;
        
        return new ViewModel([
            'paginator' => $this->db_manager->get('Sbm\Db\Query\Responsables')->paginator($args['where'], [
                'nom',
                'prenom'
            ]),
            'page' => $this->params('page', 1),
            'count_per_page' => $this->getPaginatorCountPerPage('nb_responsables', 10),
            'criteres_form' => $args['form'],
            'projection' => $this->cartographie_manager->get('SbmCarto\Projection')
        ]);
    }

    public function responsableAjoutAction()
    {
        $currentPage = $this->params('page', 1);
        $prg = $this->prg();
        if ($prg instanceof Response) {
            // transforme un post en une redirection 303 avec le contenu de post en session 'prg_post1' (Expire_Hops = 1)
            return $prg;
        } elseif ($prg === false) {
            // ce n'était pas un post. Cette entrée est illégale et conduit à un retour à la liste
            return $this->redirect()->toRoute('sbmgestion/eleve', [
                'action' => 'responsable-liste',
                'page' => $currentPage
            ]);
        }
        // ici, on a eu un post qui a été transformé en rediretion 303. Les données du post sont dans $prg (à récupérer en un seul appel à cause de Expire_Hops)
        $args = $prg;
        // si $args contient la clé 'cancel' c'est un abandon de l'action
        if (\array_key_exists('cancel', $args)) {
            $this->flashMessenger()->addWarningMessage("L'enregistrement n'a pas été enregistré.");
            return $this->redirect()->toRoute('sbmgestion/eleve', [
                'action' => 'responsable-liste',
                'page' => $currentPage
            ]);
        }
        // on ouvre la table des responsables
        $responsableId = null;
        $tableResponsables = $this->db_manager->get('Sbm\Db\Table\Responsables');
        // on ouvre le formulaire et on l'adapte
        $form = new FormResponsable();
        $value_options = $this->db_manager->get('Sbm\Db\Select\Communes')->desservies();
        $form->setValueOptions('communeId', $value_options)
            ->setValueOptions('ancienCommuneId', $value_options)
            ->setMaxLength($this->db_manager->getMaxLengthArray('responsables', 'table'));
        unset($value_options);
        
        $form->bind($tableResponsables->getObjData());
        
        if (array_key_exists('submit', $args)) {
            $form->setData($args);
            if ($form->isValid()) {
                $oData = $form->getData();
                // die(var_dump($args, $oData->getArrayCopy()));
                if ($tableResponsables->saveRecord($oData)) {
                    // on s'assure de rendre cette commune visible
                    $this->db_manager->get('Sbm\Db\table\Communes')->setVisible($oData->communeId);
                }
                $this->flashMessenger()->addSuccessMessage("La fiche a été enregistrée.");
                /*
                 * return $this->redirect()->toRoute('sbmgestion/eleve', [
                 * 'action' => 'responsable-liste',
                 * 'page' => $currentPage
                 * ));
                 */
                $viewmodel = $this->responsableLocalisationAction($tableResponsables->getLastResponsableId(), $currentPage);
                $viewmodel->setTemplate('sbm-gestion/eleve/responsable-localisation.phtml');
                return $viewmodel;
            }
        }
        return new ViewModel([
            'form' => $form->prepare(),
            'page' => $currentPage,
            'responsableId' => $responsableId,
            'demenagement' => false
        ]);
    }

    public function responsableEditAction()
    {
        // utilisation de PostRedirectGet par mesure de sécurité
        $prg = $this->prg();
        if ($prg instanceof Response) {
            // transforme un post en une redirection 303 avec le contenu de post en session 'prg_post1' (Expire_Hops = 1)
            return $prg;
        } elseif ($prg === false) {
            $args = $this->getFromSession('post', false);
            if ($args === false) {
                $this->flashMessenger()->addErrorMessage('Action interdite');
                return $this->redirect()->toRoute('login', [
                    'action' => 'logout'
                ]);
            }
        } else {
            $args = $prg;
            if (array_key_exists('cancel', $args)) {
                $this->flashMessenger()->addWarningMessage("L'enregistrement n'a pas été modifié.");
                try {
                    return $this->redirectToOrigin()->back();
                } catch (\SbmCommun\Model\Mvc\Controller\Plugin\Exception $e) {
                    return $this->redirect()->toRoute('sbmgestion/eleve', [
                        'action' => 'responsable-liste',
                        'page' => $this->params('page', 1)
                    ]);
                }
            } elseif (array_key_exists('origine', $args)) {
                $this->redirectToOrigin()->setBack($args['origine']);
                unset($args['origine']);
            }
            $this->setToSession('post', $args);
        }
        // on ouvre la table des données
        $responsableId = $args['responsableId'];
        $tableResponsables = $this->db_manager->get('Sbm\Db\Table\Responsables');
        // on ouvre le formulaire et on l'adapte
        $form = new FormResponsable();
        $value_options = $this->db_manager->get('Sbm\Db\Select\Communes')->desservies();
        $form->setValueOptions('communeId', $value_options)
            ->setValueOptions('ancienCommuneId', $value_options)
            ->setMaxLength($this->db_manager->getMaxLengthArray('responsables', 'table'));
        unset($value_options);
        
        $form->bind($tableResponsables->getObjData());
        
        if (\array_key_exists('submit', $args)) {
            $form->setData($args);
            if ($form->isValid()) {
                $oData = $form->getData();
                // die(var_dump($args, $oData->getArrayCopy()));
                if ($tableResponsables->saveRecord($oData)) {
                    // on s'assure de rendre cette commune visible
                    $this->db_manager->get('Sbm\Db\table\Communes')->setVisible($oData->communeId);
                }
                $this->flashMessenger()->addSuccessMessage("Les modifications ont été enregistrées.");
                try {
                    return $this->redirectToOrigin()->back();
                } catch (\SbmCommun\Model\Mvc\Controller\Plugin\Exception $e) {
                    return $this->redirect()->toRoute('sbmgestion/eleve', [
                        'action' => 'responsable-liste',
                        'page' => $this->params('page', 1)
                    ]);
                }
            }
            $demenagement = $args['demenagement'] ?  : false;
            $identite = $args['titre'] . ' ' . $args['nom'] . ' ' . $args['prenom'];
        } else {
            $array_data = $tableResponsables->getRecord($responsableId)->getArrayCopy();
            $form->setData($array_data);
            $demenagement = $array_data['demenagement'];
            $identite = $array_data['titre'] . ' ' . $array_data['nom'] . ' ' . $array_data['prenom'];
        }
        return new ViewModel([
            'form' => $form->prepare(),
            'page' => $this->params('page', 1),
            'responsableId' => $responsableId,
            'identite' => $identite,
            'demenagement' => $demenagement
        ]);
    }

    public function responsableGroupAction()
    {
        $currentPage = $this->params('page', 1);
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            $args = $this->getFromSession('post', [], $this->getSessionNamespace());
        } else {
            $args = $prg;
            $this->setToSession('post', $args, $this->getSessionNamespace());
        }
        $responsableId = StdLib::getParam('responsableId', $args, - 1);
        if ($responsableId == - 1) {
            $this->flashMessenger()->addErrorMessage('Action interdite.');
            return $this->redirect()->toRoute('sbmadmin', [
                'action' => 'libelle-liste',
                'page' => $currentPage
            ]);
        }
        $tableEleves = $this->db_manager->get('Sbm\Db\Query\Eleves');
        $controller = $this;
        $data = [];
        $data['eleves']['resp1'] = $tableEleves->duResponsable1($responsableId);
        $data['eleves']['resp2'] = $tableEleves->duResponsable2($responsableId);
        $data['eleves']['fact'] = $tableEleves->duResponsableFinancier($responsableId);
        $data['fnc_affectations'] = function ($eleveId) use($controller, $responsableId) {
            return $controller->db_manager->get('Sbm\Db\Query\AffectationsServicesStations')->getServices($eleveId, $responsableId);
        };
        $data['fnc_ga'] = function ($responsableId) use($controller) {
            if (is_null($responsableId)) {
                return '';
            } else {
                $oresponsable = $controller->db_manager->get('Sbm\Db\Table\Responsables')->getRecord($responsableId);
                return sprintf('%s %s', $oresponsable->nomSA, $oresponsable->prenomSA);
            }
        };
        return new ViewModel([
            'data' => $data,
            'responsable' => $this->db_manager->get('Sbm\Db\Vue\Responsables')->getRecord($responsableId),
            'page' => $currentPage,
            'responsableId' => $responsableId
        ]);
    }

    public function responsableSupprAction()
    {
        $currentPage = $this->params('page', 1);
        $form = new ButtonForm([
            'id' => null
        ], [
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
                'alias' => 'Sbm\Db\Table\Responsables',
                'id' => 'responsableId'
            ],
            'form' => $form
        ];
        $vueResponsables = $this->db_manager->get('Sbm\Db\Vue\Responsables');
        try {
            $r = $this->supprData($this->db_manager, $params, function ($id, $tableResponsables) use($vueResponsables) {
                return [
                    'id' => $id,
                    'data' => $vueResponsables->getRecord($id)
                ];
            });
        } catch (\Zend\Db\Adapter\Exception\InvalidQueryException $e) {
            $this->flashMessenger()->addWarningMessage('Impossible de supprimer ce responsable car il a des enregistrements (élèves ou paiements) en liaison.');
            return $this->redirect()->toRoute('sbmgestion/eleve', [
                'action' => 'responsable-liste',
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
                    return $this->redirect()->toRoute('sbmgestion/eleve', [
                        'action' => 'responsable-liste',
                        'page' => $currentPage
                    ]);
                    break;
                default:
                    return new ViewModel([
                        'form' => $form->prepare(),
                        'page' => $currentPage,
                        'data' => StdLib::getParam('data', $r->getResult()),
                        'responsableId' => StdLib::getParam('id', $r->getResult()),
                        'data_dependantes' => $this->db_manager->get('Sbm\Db\Table\Eleves')->duResponsable(StdLib::getParam('id', $r->getResult()))
                    ]);
                    break;
            }
        }
    }

    public function responsableLocalisationAction($responsableId = null, $currentPage = 1)
    {
        if (is_null($responsableId)) {
            $prg = $this->prg();
            if ($prg instanceof Response) {
                return $prg;
            } elseif ($prg === false) {
                $args = $this->getFromSession('post', false);
                if ($args === false) {
                    $this->flashMessenger()->addErrorMessage('Action interdite');
                    return $this->redirect()->toRoute('login', [
                        'action' => 'logout'
                    ]);
                }
            } else {
                $args = $prg;
                // selon l'origine, l'url de retour porte le nom url1_retour (liste des responsables) ou origine (liste des élèves, fiche d'un responsable)
                if (array_key_exists('url1_retour', $args)) {
                    $this->redirectToOrigin()->setBack($args['url1_retour']);
                    unset($args['url1_retour']);
                    $this->setToSession('post', $args);
                } elseif (array_key_exists('origine', $args)) {
                    $this->redirectToOrigin()->setBack($args['origine']);
                    unset($args['origine']);
                    $this->setToSession('post', $args);
                } elseif (array_key_exists('url1_retour', $args)) {
                    $this->redirectToOrigin()->setBack($args['url1_retour']);
                    unset($args['url1_retour']);
                    $this->setToSession('post', $args);
                }
                if (array_key_exists('cancel', $args)) {
                    $this->flashMessenger()->addWarningMessage('La localisation de cette adresse n\'a pas été enregistrée.');
                    try {
                        return $this->redirectToOrigin()->back();
                    } catch (\SbmCommun\Model\Mvc\Controller\Plugin\Exception $e) {
                        return $this->redirect()->toRoute('sbmgestion/eleve', [
                            'action' => 'responsable-liste',
                            'page' => $currentPage
                        ]);
                    }
                }
            }
        } else {
            $args = [
                'responsableId' => $responsableId
            ];
        }
        // les outils de travail : formulaire et convertisseur de coordonnées
        // nécessaire pour valider lat et lng
        $configCarte = StdLib::getParam('parent', $this->cartographie_manager->get('cartes'));
        $form = new LatLngForm([
            'responsableId' => [
                'id' => 'responsableId'
            ]
        ], [
            'submit' => [
                'class' => 'button default submit left-95px',
                'value' => 'Enregistrer la localisation'
            ],
            'cancel' => [
                'class' => 'button default cancel left-10px',
                'value' => 'Abandonner'
            ]
        ], $configCarte['valide']);
        $form->setAttribute('action', $this->url()
            ->fromRoute('sbmgestion/eleve', [
            'action' => 'responsable-localisation'
        ]));
        
        // traitement de la réponse
        $d2etab = $this->cartographie_manager->get('SbmCarto\DistanceEtablissements');
        if (array_key_exists('submit', $args)) {
            $form->setData([
                'responsableId' => $args['responsableId'],
                'lat' => $args['lat'],
                'lng' => $args['lng']
            ]);
            if ($form->isValid()) {
                // détermine le point. Il est reçu en gRGF93 et sera enregistré en XYZ
                $pt = new Point($args['lng'], $args['lat'], 0, 'degré');
                $point = $d2etab->getProjection()->gRGF93versXYZ($pt);
                // enregistre les coordonnées dans la table
                $tableResponsables = $this->db_manager->get('Sbm\Db\Table\Responsables');
                $oData = $tableResponsables->getObjData();
                $oData->exchangeArray([
                    'responsableId' => $args['responsableId'],
                    'x' => $point->getX(),
                    'y' => $point->getY()
                ]);
                $tableResponsables->saveRecord($oData);
                $this->flashMessenger()->addSuccessMessage('La localisation de cette adresse est enregistrée.');
                $this->cartographie_manager->get('Sbm\MajDistances')->pour($args['responsableId']);
                try {
                    return $this->redirectToOrigin()->back();
                } catch (\SbmCommun\Model\Mvc\Controller\Plugin\Exception $e) {
                    return $this->redirect()->toRoute('sbmgestion/eleve', [
                        'action' => 'responsable-liste',
                        'page' => $currentPage
                    ]);
                }
            }
        }
        // chercher le responsable dans la table
        $responsable = $this->db_manager->get('Sbm\Db\Vue\Responsables')->getRecord($args['responsableId']);
        // préparer le Point dans le système gRGF93
        $point = new Point($responsable->x, $responsable->y);
        $pt = $d2etab->getProjection()->XYZversgRGF93($point);
        // charger le formulaire
        $form->setData([
            'responsableId' => $args['responsableId'],
            'lat' => $pt->getLatitude(),
            'lng' => $pt->getLongitude()
        ]);
        if (! $form->isValid()) {
            // essaie de positionner la marker à partir de l'adresse
            $array = $this->cartographie_manager->get('SbmCarto\Geocoder')->geocode($responsable->adresseL1, $responsable->codePostal, $responsable->commune);
            $pt = new Point($array['lng'], $array['lat'], 0, 'degré');
            $pt->setLatLngRange($configCarte['valide']['lat'], $configCarte['valide']['lng']);
            if (! $pt->isValid()) {
                $pt->setLatitude($configCarte['centre']['lat']);
                $pt->setLongitude($configCarte['centre']['lng']);
            }
            $form->setData([
                'responsableId' => $args['responsableId'],
                'lat' => $pt->getLatitude(),
                'lng' => $pt->getLongitude()
            ]);
        }
        return new ViewModel([
            'point' => $pt,
            'form' => $form->prepare(),
            'responsableId' => $args['responsableId'],
            'responsable' => $responsable,
            'url_api' => $this->cartographie_manager->get('google_api')['js'],
            'config' => $configCarte
        ]);
    }

    public function responsablePdfAction()
    {
        $projection = $this->cartographie_manager->get('SbmCarto\Projection');
        $rangeX = $projection->getRangeX();
        $rangeY = $projection->getRangeY();
        $pasLocalisaton = 'Literal:Not((x Between %d And %d) And (y Between %d And %d))';
        
        $criteresObject = [
            '\SbmCommun\Model\Db\ObjectData\Criteres',
            [
                'strict' => [
                    'nbInscrits',
                    'nbPreinscrits',
                    'selection'
                ],
                'expressions' => [
                    'nbEnfants' => 'Expression:nbEleves = ?',
                    'inscrits' => 'Literal:0',
                    'preinscrit' => 'Literal:0',
                    'localisation' => sprintf($pasLocalisaton, $rangeX['parent'][0], $rangeX['parent'][1], $rangeY['parent'][0], $rangeY['parent'][1])
                ]
            ]
        ];
        $criteresForm = [
            '\SbmCommun\Form\CriteresForm',
            'responsables'
        ];
        $documentId = null;
        $retour = [
            'route' => 'sbmgestion/eleve',
            'action' => 'responsable-liste'
        ];
        return $this->documentPdf($criteresObject, $criteresForm, $documentId, $retour);
    }

    public function responsableGroupPdfAction()
    {
        $criteresObject = [
            '\SbmCommun\Model\Db\ObjectData\Criteres',
            null,
            function ($where, $args) {
                // responsableId = 376 AND (millesime IS NULL OR millesime = maxmillesime)
                $where = new Where();
                $or = false;
                $where->equalTo('responsableId', $args['responsableId'])
                    ->nest()
                    ->isNull('millesime')->or->literal('millesime = maxmillesime')->unnest();
                $tResponsableIds = explode('|', $args['op']);
                return $where;
            }
        ];
        $criteresForm = [
            '\SbmCommun\Form\CriteresForm',
            'responsables'
        ];
        $documentId = null;
        $retour = [
            'route' => 'sbmgestion/eleve',
            'action' => 'responsable-groupe'
        ];
        return $this->documentPdf($criteresObject, $criteresForm, $documentId, $retour);
    }

    /**
     * Envoie un mail à un responsable.
     * Reçoit en post les paramètres 'responsable', 'email', 'group' où group est l'url de retour
     *
     * @return \Zend\Http\PhpEnvironment\Response|\Zend\Http\Response|\Zend\View\Model\ViewModel
     */
    public function responsableMailAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            $destinataire = $this->getFromSession('destinataire', [], $this->getSessionNamespace());
            $args = [];
        } else {
            $args = $prg;
            if (array_key_exists('group', $args)) {
                $this->redirectToOrigin()->setBack($args['group']);
                unset($args['group']);
            }
            if (array_key_exists('ecrire', $args) && array_key_exists('email', $args)) {
                $destinataire = [
                    'email' => $args['email'],
                    'responsable' => StdLib::getParam('responsable', $args)
                ];
                $this->setToSession('destinataire', $destinataire, $this->getSessionNamespace());
                unset($args['email'], $args['responsable']);
            } elseif (array_key_exists('ecrirer1', $args) && array_key_exists('emailr1', $args)) {
                $destinataire = [
                    'email' => $args['emailr1'],
                    'responsable' => StdLib::getParam('responsabler1', $args)
                ];
                $this->setToSession('destinataire', $destinataire, $this->getSessionNamespace());
                unset($args['emailr1'], $args['responsabler1']);
            } elseif (array_key_exists('ecrirer2', $args) && array_key_exists('emailr2', $args)) {
                $destinataire = [
                    'email' => $args['emailr2'],
                    'responsable' => StdLib::getParam('responsabler2', $args)
                ];
                $this->setToSession('destinataire', $destinataire, $this->getSessionNamespace());
                unset($args['emailr2'], $args['responsabler2']);
            } else {
                $destinataire = $this->getFromSession('destinataire', [], $this->getSessionNamespace());
            }
        }
        if (empty($destinataire) || array_key_exists('cancel', $args)) {
            $this->flashMessenger()->addWarningMessage('Aucun message envoyé.');
            try {
                return $this->redirectToOrigin()->back();
            } catch (\SbmCommun\Model\Mvc\Controller\Plugin\Exception $e) {
                $this->redirectToOrigin()->reset();
                return $this->redirect()->toRoute('login', [
                    'action' => 'home-page'
                ]);
            }
        }
        $form = new MailForm();
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
                $mailTemplate = new MailTemplate(null, 'layout', [
                    'file_name' => $logo_bas_de_mail,
                    'path' => StdLib::getParamR([
                        'img',
                        'path'
                    ], $this->config),
                    'img_attributes' => StdLib::getParamR([
                        'img',
                        'administrer',
                        $logo_bas_de_mail
                    ], $this->config),
                    'client' => StdLib::getParam('client', $this->config)
                ]);
                $params = [
                    'to' => [
                        [
                            'email' => $destinataire['email'],
                            'name' => $destinataire['responsable'] ?  : $destinataire['email']
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
                $this->flashMessenger()->addInfoMessage('Le message a été envoyé et une copie vous est adressée dans votre messagerie.');
                try {
                    return $this->redirectToOrigin()->back();
                } catch (\SbmCommun\Model\Mvc\Controller\Plugin\Exception $e) {
                    $this->redirectToOrigin()->reset();
                    return $this->redirect()->toRoute('login', [
                        'action' => 'home-page'
                    ]);
                }
            }
        }
        
        $view = new ViewModel([
            'form' => $form->prepare(),
            'destinataires' => [
                $destinataire
            ]
        ]);
        $view->setTemplate('sbm-mail/index/send.phtml');
        return $view;
    }

    /**
     * Créer un compte user à un responsable saisi manuellement
     *
     * @return \Zend\Http\PhpEnvironment\Response|\Zend\Http\Response|\Zend\View\Model\ViewModel
     */
    public function responsableLogerAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            return $this->redirect()->toRoute('sbmgestion/eleve', [
                'action' => 'responsable-liste',
                'page' => $this->params('page', 1)
            ]);
        } else {
            $args = $prg;
            if (array_key_exists('logernon', $args) || ! array_key_exists('responsableId', $args)) {
                return $this->redirect()->toRoute('sbmgestion/eleve', [
                    'action' => 'responsable-liste',
                    'page' => $this->params('page', 1)
                ]);
            }
        }
        $tUsers = $this->db_manager->get('Sbm\Db\Table\Users');
        $responsable = $this->db_manager->get('Sbm\Db\Table\Responsables')->getRecord($args['responsableId']);
        $email = $responsable->email;
        if (empty($email)) {
            $msg = 'Pour créer un compte il faut que ce responsable ait une adresse email.';
            $form = null;
        } else {
            try {
                $tUsers->getRecordByEmail($responsable->email);
                $msg = 'Ce responsable a déjà un compte.';
                $form = null;
            } catch (\SbmCommun\Model\Db\Service\Table\Exception $e) {
                $msg = '';
                $form = new ButtonForm([
                    'responsableId' => null
                ], [
                    'logeroui' => [
                        'class' => 'confirm',
                        'value' => 'Confirmer',
                        'class' => 'button default'
                    ],
                    'logernon' => [
                        'class' => 'confirm',
                        'value' => 'Abandonner',
                        'class' => 'button default'
                    ]
                ]);
                if (array_key_exists('logeroui', $args)) {
                    $form->setData($args);
                    if ($form->isValid()) {
                        $odata = $tUsers->getObjData();
                        $adata = $responsable->getArrayCopy();
                        $adata['userId'] = null;
                        unset($adata['dateCreation']);
                        unset($adata['dateModification']);
                        $odata->exchangeArray($adata)->completeToCreate();
                        $tUsers->saveRecord($odata);
                        $this->flashMessenger()->addSuccessMessage('Le compte est créé.');
                        
                        // envoie un email
                        $logo_bas_de_mail = 'bas-de-mail-transport-scolaire.png';
                        $mailTemplate = new MailTemplate('ouverture-compte', 'layout', [
                            'file_name' => $logo_bas_de_mail,
                            'path' => StdLib::getParamR([
                                'img',
                                'path'
                            ], $this->config),
                            'img_attributes' => StdLib::getParamR([
                                'img',
                                'administrer',
                                $logo_bas_de_mail
                            ], $this->config),
                            'client' => StdLib::getParam('client', $this->config)
                        ]);
                        $params = [
                            'to' => [
                                [
                                    'email' => $odata->email,
                                    'name' => $odata->nom . ' ' . $odata->prenom
                                ]
                            ],
                            'subject' => 'Ouverture d\'un compte',
                            'body' => [
                                'html' => $mailTemplate->render([
                                    'titre' => $odata->titre,
                                    'nom' => $odata->nom,
                                    'prenom' => $odata->prenom,
                                    'url_confirme' => $this->url()
                                        ->fromRoute('login', [
                                        'action' => 'confirm',
                                        'id' => $odata->token
                                    ], [
                                        'force_canonical' => true
                                    ]),
                                    'client' => StdLib::getParam('client', $this->config)
                                ])
                            ]
                        ];
                        $this->getEventManager()->addIdentifiers('SbmMail\Send');
                        $this->getEventManager()->trigger('sendMail', null, $params);
                        $this->flashMessenger()->addInfoMessage('Un mail a été envoyé à l\'adresse indiquée pour donner les instructions d\'accès.');
                        
                        return $this->redirect()->toRoute('sbmgestion/eleve', [
                            'action' => 'responsable-liste',
                            'page' => $this->params('page', 1)
                        ]);
                    }
                }
                $form->setData([
                    'responsableId' => $args['responsableId']
                ]);
            }
        }
        return new ViewModel([
            'form' => is_null($form) ? null : $form->prepare(),
            'info' => $args['info'],
            'msg' => $msg,
            'page' => $this->params('page', 1)
        ]);
    }
}
