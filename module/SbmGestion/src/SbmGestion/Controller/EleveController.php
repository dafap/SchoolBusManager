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
 * @date 12 févr. 2014
 * @version 2014-1
 */
namespace SbmGestion\Controller;

use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Zend\Json\Json;
use Zend\Http\PhpEnvironment\Response;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Predicate;
use DafapSession\Model\Session;
use SbmCartographie\Model\Point;
use SbmCommun\Model\Mvc\Controller\AbstractActionController;
use SbmCommun\Model\Db\DbLib;
use SbmCommun\Model\StdLib;
use SbmCommun\Model\Strategy\Semaine;
use SbmCommun\Form\ButtonForm;
use SbmCommun\Form\LatLng as LatLngForm;
use SbmGestion\Form\Eleve\EditForm as FormEleve;
use SbmCommun\Form\Responsable as FormResponsable;
use SbmCommun\Form\SbmCommun\Form;
use DafapMail\Model\Template as MailTemplate;

class EleveController extends AbstractActionController
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
            $args = Session::get('post', array(), $this->getSessionNamespace());
        } else {
            // c'était un post ; on le met en session si ce n'est pas un retour ou un cancel
            $args = $prg;
            $retour = StdLib::getParam('op', $args, '') == 'retour';
            if ($retour) {
                // dans ce cas, il s'agit du retour d'une action de type suppr, ajout ou edit. Comme pour un get, on récupère ce qui est en session.
                $this->sbm_isPost = false;
                $args = Session::get('post', array(), $this->getSessionNamespace());
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
        $criteres_form->setValueOptions('etablissementId', $this->getServiceLocator()
            ->get('Sbm\Db\Select\EtablissementsDesservis'))
            ->setValueOptions('classeId', $this->getServiceLocator()
            ->get('Sbm\Db\Select\Classes'));
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
        return new ViewModel(array(
            'paginator' => $this->getServiceLocator()
                ->get('Sbm\Db\Query\ElevesResponsables')
                ->paginatorScolaritesR2($criteres_obj->getWhere(), array(
                'nom',
                'prenom'
            )),
            'page' => $this->params('page', 1),
            'nb_pagination' => $this->getNbPagination('nb_eleves', 10),
            'criteres_form' => $criteres_form
        ));
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
            return $this->redirect()->toRoute('sbmgestion/eleve', array(
                'action' => 'eleve-liste',
                'page' => $this->params('page', 1)
            ));
        }
        $form = new ButtonForm(array(), array(
            'confirmer' => array(
                'class' => 'confirm',
                'value' => 'Confirmer',
                'title' => 'Déseletionner toutes les fiches élèves.'
            ),
            'cancel' => array(
                'class' => 'confirm',
                'value' => 'Abandonner'
            )
        ), 'Confirmation', true);
        $televes = $this->getServiceLocator()->get('Sbm\Db\Table\Eleves');
        if (array_key_exists('confirmer', $args)) {
            $form->setData($args);
            if ($form->isValid()) {
                $televes->clearSelection();
                $this->flashMessenger()->addSuccessMessage('Toutes les fiches sont désélectionnées.');
                return $this->redirect()->toRoute('sbmgestion/eleve', array(
                    'action' => 'eleve-liste',
                    'page' => $this->params('page', 1)
                ));
            }
        }
        $where = new Where();
        $where->equalTo('selection', 1);
        return new ViewModel(array(
            'form' => $form,
            'nbSelection' => $televes->fetchAll($where)->count()
        ));
    }

    /**
     * Si on arrive par post, on passera :
     * - orinine : url d'origine de l'appel pour assurer un retour par redirectToOrigin()->back()
     * à la fin de l'opération (en général dans eleveEditAction()).
     * Si on arrive par get, on s'assurera que redirectToOrigin()->setBack() a bien été fait avant.
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
        if (array_key_exists('origine', $args)) {
            $this->redirectToOrigin()->setBack($args['origine']);
            // par la suite, on ne s'occupe plus de 'origine' mais on ressort par un redirectToOrigin()->back()
            unset($args['origine']);
        }
        if (array_key_exists('cancel', $args)) {
            $this->flashMessenger()->addInfoMessage('Saisie abandonnée.');
            try {
                return $this->redirectToOrigin()->back();
            } catch (\SbmCommun\Model\Mvc\Controller\Plugin\Exception $e) {
                return $this->redirect()->toRoute('sbmgestion/eleve', array(
                    'action' => 'eleve-liste',
                    'page' => $page
                ));
            }
        } elseif (array_key_exists('submit', $args)) {
            $ispost = true;
            // pour un retour éventuel par F5 ou back en 22
            $this->setToSession('post', $args, $this->getSessionNamespace('ajout', 1));
        } else {
            $ispost = false;
        }
        $eleveId = null;
        $db = $this->getServiceLocator()->get('Sbm\Db\DbLib');
        
        $form = new \SbmGestion\Form\Eleve\AddElevePhase1();
        $value_options = $this->getServiceLocator()->get('Sbm\Db\Select\Responsables');
        $form->setAttribute('action', $this->url()
            ->fromRoute('sbmgestion/eleve', array(
            'action' => 'eleve-ajout',
            'page' => $page
        )))
            ->setValueOptions('responsable1Id', $value_options)
            ->setValueOptions('responsable2Id', $value_options)
            ->setMaxLength($db->getMaxLengthArray('eleves', 'table'))
            ->bind($this->getServiceLocator()
            ->get('Sbm\Db\Table\Eleves')
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
                $resultset = $this->getServiceLocator()
                    ->get('Sbm\Db\Query\ElevesResponsables')
                    ->withR2($where);
                if ($resultset->count() == 0) {
                    // pas d'homonyme. On crée cet élève (22)
                    return $this->eleveAjout22Action($odata);
                }
                $form = null;
            }
        }
        return new ViewModel(array(
            'page' => $page,
            // form est le formulaire si les données ne sont pas validées (ou pas de données)
            'form' => is_null($form) ? $form : $form->prepare(),
            // liste est null ou est un resultset à parcourir pour montrer la liste
            'eleves' => $resultset,
            // data = null ou contient les données validées à passer à nouveau en post
            'data' => $odata
        ));
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
                    return $this->redirect()->toRoute('sbmgestion/eleve', array(
                        'action' => 'eleve-liste',
                        'page' => $page
                    ));
                }
            }
        } else {
            $this->setToSession('post', $prg, $this->getSessionNamespace('ajout', 2)); // pour une retour éventuel par F5 ou back
        }
        $info = stdlib::getParam('info', $prg, '');
        $eleveId = $prg['eleveId'];
        $tScolarites = $this->getServiceLocator()->get('Sbm\Db\Table\Scolarites');
        $id = array(
            'millesime' => Session::get('millesime'),
            'eleveId' => $eleveId
        );
        if ($tScolarites->is_newRecord($id)) {
            $viewmodel = $this->eleveAjout31Action($eleveId, $info);
            $viewmodel->setTemplate('sbm-gestion/eleve/eleve-ajout31.phtml');
        } else {
            $args = array(
                'eleveId' => $eleveId,
                'info' => $info,
                'op' => 'ajouter'
            );
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
        $tEleves = $this->getServiceLocator()->get('Sbm\Db\Table\Eleves');
        if (is_null($odata)) {
            $prg = $this->prg();
            if ($prg instanceof Response) {
                return $prg;
            } elseif ($prg === false) {
                // retour vers le point d'entrée d'un F5 ou d'un back
                return $this->redirect()->toRoute('sbmgestion/eleve', array(
                    'action' => 'eleve-ajout',
                    'page' => $page
                ));
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
                return $this->redirect()->toRoute('sbmgestion/eleve', array(
                    'action' => 'eleve-ajout21',
                    'page' => $page
                ));
            } else {
                // c'est le traitement du retour par POST du formulaire après prg
                $args = $prg;
                if (array_key_exists('cancel', $args)) {
                    $this->flashMessenger()->addInfoMessage('Saisie abandonnée.');
                    try {
                        return $this->redirectToOrigin()->back();
                    } catch (\SbmCommun\Model\Mvc\Controller\Plugin\Exception $e) {
                        return $this->redirect()->toRoute('sbmgestion/eleve', array(
                            'action' => 'eleve-liste',
                            'page' => $page
                        ));
                    }
                }
                // on récupère eleveId et info
                $eleveId = StdLib::getParam('eleveId', $prg, false);
                $info = StdLib::getParam('info', $args, '');
                if (! $eleveId) {
                    // on a perdu la donnée essentielle : il faut tout recommencer
                    return $this->redirect()->toRoute('sbmgestion/eleve', array(
                        'action' => 'eleve-ajout',
                        'page' => $page
                    ));
                }
                $ispost = array_key_exists('submit', $args);
            }
        }
        // ici on a un eleveId qui possède une fiche dans la table eleves et pour lequel on doit saisir la scolarite
        $tableScolarites = $this->getServiceLocator()->get('Sbm\Db\Table\Scolarites');
        $form = new \SbmGestion\Form\Eleve\AddElevePhase2();
        
        $value_options = $this->getServiceLocator()->get('Sbm\Db\Select\Responsables');
        $form->setAttribute('action', $this->url()
            ->fromRoute('sbmgestion/eleve', array(
            'action' => 'eleve-ajout31',
            'page' => $page
        )))
            ->setValueOptions('etablissementId', $this->getServiceLocator()
            ->get('Sbm\Db\Select\EtablissementsDesservis'))
            ->setValueOptions('classeId', $this->getServiceLocator()
            ->get('Sbm\Db\Select\Classes'))
            ->setValueOptions('joursTransport', Semaine::getJours())
            ->bind($tableScolarites->getObjData());
        if ($ispost) {
            $form->setData($args);
            if ($form->isValid()) {
                $odata = $form->getData();
                $odata->millesime = Session::get('millesime');
                $odata->tarifId = $this->getServiceLocator()
                    ->get('Sbm\Db\Table\Tarifs')
                    ->getTarifId('inscription');
                $tableScolarites->saveRecord($odata);
                $viewModel = $this->eleveEditAction(array(
                    'eleveId' => $eleveId,
                    'info' => $info,
                    'op' => 'ajouter'
                ));
                $viewModel->setTemplate('sbm-gestion/eleve/eleve-edit.phtml');
                return $viewModel;
            }
        }
        // initialisation du formulaire
        $where = new Where();
        $where->equalTo('eleveId', $eleveId);
        $data = $this->getServiceLocator()
            ->get('Sbm\Db\Query\ElevesResponsables')
            ->withR2($where)
            ->current();
        $form->setData(array(
            'eleveId' => $eleveId,
            'responsable1Id' => $data['responsable1Id'],
            'responsable2Id' => isset($data['responsable2Id']) ? $data['responsable2Id'] : '',
            'dateDebut' => Session::get('as')['dateDebut'],
            'dateFin' => Session::get('as')['dateFin'],
            'demandeR1' => 1,
            'demandeR2' => 0
        ));
        return new ViewModel(array(
            'page' => $page,
            'form' => $form->prepare(),
            'info' => $info,
            'data' => $data
        ));
    }

    /**
     * Cette méthode est généralement appelée par post et reçoit
     * - eleveId
     * - info
     * - origine (optionnel)
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
            } elseif ($prg === false) {
                $args = $this->getFromSession('post', false);
                if ($args === false) {
                    $this->flashMessenger()->addErrorMessage('Action interdite');
                    try {
                        return $this->redirectToOrigin()->back();
                    } catch (\SbmCommun\Model\Mvc\Controller\Plugin\Exception $e) {
                        return $this->redirect()->toRoute('login', array(
                            'action' => 'logout'
                        ));
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
                        return $this->redirect()->toRoute('sbmgestion/eleve', array(
                            'action' => 'eleve-liste',
                            'page' => $currentPage
                        ));
                    }
                }
                if (array_key_exists('origine', $args)) {
                    $this->redirectToOrigin()->setBack($args['origine']);
                    unset($args['origine']);
                    $this->setToSession('post', $args);
                }
            }
        } else {
            if (isset($args['origine'])) {
                $this->redirectToOrigin()->setBack($args['origine']);
                unset($args['origine']);
            }
            $this->setToSession('post', $args);
        }
        $eleveId = $args['eleveId'];
        if ($eleveId == - 1) {
            try {
                return $this->redirectToOrigin()->back();
            } catch (\SbmCommun\Model\Mvc\Controller\Plugin\Exception $e) {
                return $this->redirect()->toRoute('sbmgestion/eleve', array(
                    'action' => 'eleve-liste',
                    'page' => $currentPage
                ));
            }
        }
        $db = $this->getServiceLocator()->get('Sbm\Db\DbLib');
        $tEleves = $this->getServiceLocator()->get('Sbm\Db\Table\Eleves');
        $tScolarites = $this->getServiceLocator()->get('Sbm\Db\Table\Scolarites');
        $tTarifs = $this->getServiceLocator()->get('Sbm\Db\Table\Tarifs');
        $qAffectations = $this->getServiceLocator()->get('Sbm\Db\Query\AffectationsServicesStations');
        // les invariants
        $invariants = array();
        $historique = array();
        $odata0 = $tEleves->getRecord($eleveId);
        if ($odata0->dateN == '0000-00-00') {
            $odata0->dateN = '1900-01-01';
        }
        $historique['eleve']['dateCreation'] = $odata0->dateCreation;
        $historique['eleve']['dateModification'] = $odata0->dateModification;
        $invariants['numero'] = $odata0->numero;
        $odata1 = $tScolarites->getRecord(array(
            'millesime' => Session::get('millesime'),
            'eleveId' => $eleveId
        ));
        if ($odata1->inscrit) {
            $inscrit = $odata1->paiement;
            $inscrit |= $odata1->fa;
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
        $historique['scolarite']['inscription'] = $tTarifs->getMontant('inscription');
        $historique['scolarite']['duplicata'] = $odata1->duplicata;
        
        $respSelect = $this->getServiceLocator()->get('Sbm\Db\Select\Responsables');
        $etabSelect = $this->getServiceLocator()->get('Sbm\Db\Select\EtablissementsDesservis');
        $clasSelect = $this->getServiceLocator()->get('Sbm\Db\Select\Classes');
        $form = new FormEleve();
        $form->setAttribute('action', $this->url()
            ->fromRoute('sbmgestion/eleve', array(
            'action' => 'eleve-edit',
            'page' => $currentPage
        )))
            ->setValueOptions('responsable1Id', $respSelect)
            ->setValueOptions('responsable2Id', $respSelect)
            ->setValueOptions('etablissementId', $etabSelect)
            ->setValueOptions('classeId', $clasSelect)
            ->setValueOptions('joursTransport', Semaine::getJours())
            ->setMaxLength($db->getMaxLengthArray('eleves', 'table'));
        if (array_key_exists('submit', $args)) {
            $form->setData($args);
            if ($form->isValid()) { // controle le csrf
                $millesime = Session::get('millesime');
                $dataValid = array_merge(array(
                    'millesime' => $millesime
                ), $form->getData());
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
                $tAffectations = $this->getServiceLocator()->get('Sbm\Db\Table\Affectations');
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
                $recalcul = $tScolarites->saveRecord($tScolarites->getObjData()
                    ->exchangeArray($dataValid));
                // recalcul des droits et des distances en cas de modification de la destination ou d'une origine
                if ($recalcul || $changeR1 || $changeR2) {
                    $majDistances = $this->getServiceLocator()->get('Sbm\CalculDroitsTransport');
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
                    return $this->redirect()->toRoute('sbmgestion/eleve', array(
                        'action' => 'eleve-liste',
                        'page' => $currentPage
                    ));
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
        $r = $this->getServiceLocator()
            ->get('Sbm\Db\Table\Responsables')
            ->getRecord($odata0->responsable1Id);
        $historique['responsable1']['dateCreation'] = $r->dateCreation;
        $historique['responsable1']['dateModification'] = $r->dateModification;
        $historique['responsable1']['dateDemenagement'] = $r->dateDemenagement;
        $tmp = $odata0->responsable2Id;
        if (! empty($tmp)) {
            $r = $this->getServiceLocator()
                ->get('Sbm\Db\Table\Responsables')
                ->getRecord($odata0->responsable2Id);
            $historique['responsable2']['dateCreation'] = $r->dateCreation;
            $historique['responsable2']['dateModification'] = $r->dateModification;
            $historique['responsable2']['dateDemenagement'] = $r->dateDemenagement;
        }
        $affectations = array();
        foreach ($qAffectations->getAffectations($eleveId) as $row) {
            $affectations[] = $row;
        }
        return new ViewModel(array(
            'form' => $form->prepare(),
            'page' => $currentPage, // nécessaire pour la compatibilité des appels
            'eleveId' => $eleveId, // nécessaire pour la compatibilité des appels
            'identite' => $identite, // nécessaire pour la compatibilité des appels
            'data' => $invariants,
            'historique' => $historique,
            'affectations' => $affectations
        ));
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
        return $this->redirect()->toRoute('sbmgestion/eleve', array(
            'action' => 'eleve-ajout21',
            'page' => $this->params('page', 1)
        ));
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
        $tScolarites = $this->getServiceLocator()->get('Sbm\Db\Table\Scolarites');
        $scolarite = $tScolarites->getRecord(array(
            'millesime' => Session::get('millesime'),
            'eleveId' => $prg['eleveId']
        ));
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
                $this->redirect()->toRoute('login', array(
                    'action' => 'logout'
                ));
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
                    return $this->redirect()->toRoute('sbmgestion/eleve', array(
                        'action' => 'eleve-liste',
                        'page' => $currentPage
                    ));
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
        $viewmodel = new ViewModel(array(
            'paginator' => $this->getServiceLocator()
                ->get('Sbm\Db\Query\ElevesResponsables')
                ->paginatorScolaritesR2($where),
            'page' => $this->params('page', 1),
            'nb_pagination' => $this->getNbPagination('nb_eleves', 10),
            'criteres_form' => null,
            'groupe' => $args['op']
        ));
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
        $retour = array(
            'route' => 'sbmgestion/eleve',
            'action' => 'eleve-liste'
        );
        return $this->documentPdf($criteresObject, $criteresForm, $documentId, $retour);
    }

    public function eleveGroupePdfAction()
    {
        $criteresObject = array(
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
        );
        $criteresForm = '\SbmGestion\Form\Eleve\CriteresForm';
        $documentId = null;
        $retour = array(
            'route' => 'sbmgestion/eleve',
            'action' => 'eleve-groupe'
        );
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
                return $this->redirect()->toRoute('sbmgestion/eleve', array(
                    'action' => 'eleve-liste',
                    'page' => $this->params('page', 1)
                ));
            }
        } else {
            $args = $prg;
            if (array_key_exists('cancel', $args) || ! array_key_exists('eleveId', $args)) {
                $this->flashMessenger()->addWarningMessage('Abandon de la suppression.');
                return $this->redirect()->toRoute('sbmgestion/eleve', array(
                    'action' => 'eleve-liste',
                    'page' => $this->params('page', 1)
                ));
            }
            $rayer = array_key_exists('rayer', $args);
            $supprimer = array_key_exists('confirmer', $args);
            unset($args['rayer'], $args['confirmer']);
            $this->setToSession('post', $args, $this->getSessionNamespace());
        }
        $form = new ButtonForm(array(
            'eleveId' => $args['eleveId']
        ), array(
            'confirmer' => array(
                'class' => 'confirm',
                'value' => 'Supprimer',
                'title' => 'Cette action est irréversible.'
            ),
            'rayer' => array(
                'class' => 'confirm',
                'value' => 'Rayer',
                'title' => 'L\'élève ne sera plus inscrit mais restera enregistré dans la base.'
            ),
            'cancel' => array(
                'class' => 'confirm',
                'value' => 'Abandonner'
            )
        ));
        $millesime = Session::get('millesime');
        if ($rayer) {
            $where = new Where();
            $where->equalTo('millesime', $millesime)->equalTo('eleveId', $args['eleveId']);
            $this->getServiceLocator()
                ->get('Sbm\Db\Table\Affectations')
                ->deleteRecord($where);
            $this->getServiceLocator()
                ->get('Sbm\Db\Table\Scolarites')
                ->setInscrit($millesime, $args['eleveId'], 0);
            $this->flashMessenger()->addSuccessMessage('L\'élève a été rayée.');
            return $this->redirect()->toRoute('sbmgestion/eleve', array(
                'action' => 'eleve-liste',
                'page' => $this->params('page', 1)
            ));
        } elseif ($supprimer) {
            $where = new Where();
            $where->equalTo('millesime', $millesime)->equalTo('eleveId', $args['eleveId']);
            $this->getServiceLocator()
                ->get('Sbm\Db\Table\Affectations')
                ->deleteRecord($where);
            $this->getServiceLocator()
                ->get('Sbm\Db\Table\Scolarites')
                ->deleteRecord($where);
            $this->getServiceLocator()
                ->get('Sbm\Db\Table\Eleves')
                ->deleteRecord($args['eleveId']);
            $this->flashMessenger()->addSuccessMessage('L\'inscription a été supprimée.');
            return $this->redirect()->toRoute('sbmgestion/eleve', array(
                'action' => 'eleve-liste',
                'page' => $this->params('page', 1)
            ));
        }
        return new ViewModel(array(
            'form' => $form->prepare(),
            'page' => $this->params('page', 1),
            'eleve' => $this->getServiceLocator()
                ->get('Sbm\Db\Query\ElevesScolarites')
                ->getEleve($args['eleveId']),
            'affectations' => $this->getServiceLocator()
                ->get('Sbm\Db\Query\AffectationsServicesStations')
                ->getCorrespondances($args['eleveId'])
        ));
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
                return $this->redirect()->toRoute('login', array(
                    'action' => 'logout'
                ));
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
                    return $this->redirect()->toRoute('sbmgestion/eleve', array(
                        'action' => 'eleve-edit'
                    ));
                }
            }
        }
        // les outils de travail : formulaire et convertisseur de coordonnées
        // nécessaire pour valider lat et lng
        $configCarte = StdLib::getParamR(array(
            'sbm',
            'cartes',
            'parent'
        ), $this->getServiceLocator()->get('config'));
        // ici, il faut un formulaire permettant de saisir l'adresse particulière d'un élève. Le tout est enregistré dans scolarites
        $form = new \SbmGestion\Form\Eleve\LocalisationAdresse($configCarte['valide']);
        $form->setAttribute('action', $this->url()
            ->fromRoute('sbmgestion/eleve', array(
            'action' => 'eleve-localisation'
        )))
            ->setValueOptions('communeId', $this->getServiceLocator()
            ->get('Sbm\Db\Select\Communes')
            ->desservies());
        $d2etab = $this->getServiceLocator()->get('SbmCarto\DistanceEtablissements');
        // chercher l'élève dans la table
        $eleve = $this->getServiceLocator()
            ->get('Sbm\Db\Query\ElevesScolarites')
            ->getEleve($args['eleveId']);
        // traitement de la réponse
        if (array_key_exists('submit', $args)) {
            $form->setData($args);
            $pt = new Point($args['lng'], $args['lat'], 0, 'degré');
            if ($form->isValid()) {
                // détermine le point. Il est reçu en gRGF93 et sera enregistré en XYZ
                $point = $d2etab->getProjection()->gRGF93versXYZ($pt);
                // enregistre les coordonnées dans la table
                $tableScolarites = $this->getServiceLocator()->get('Sbm\Db\Table\Scolarites');
                $oData = $tableScolarites->getObjData();
                $oData->exchangeArray($form->getData());
                $oData->millesime = Session::get('millesime');
                $oData->x = $point->getX();
                $oData->y = $point->getY();
                // calcul de la distance à l'établissement
                $tableEtablissements = $this->getServiceLocator()->get('Sbm\Db\Table\Etablissements');
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
                    return $this->redirect()->toRoute('sbmgestion/eleve', array(
                        'action' => 'eleve-edit'
                    ));
                }
            }
            // reprendre les données pour charger le formulaire
            $data = $args;
        } elseif (array_key_exists('remove', $args)) {
            // cherche l'établissement
            $tableEtablissements = $this->getServiceLocator()->get('Sbm\Db\Table\Etablissements');
            $etablissement = $tableEtablissements->getRecord($eleve['etablissementId']);
            $pointEtablissement = new Point($etablissement->x, $etablissement->y);
            $ptetab = $d2etab->getProjection()->XYZversgRGF93($pointEtablissement);
            // recherche le responsable1 pour calculer la distance
            $eleveR1 = $this->getServiceLocator()
                ->get('Sbm\Db\Query\ElevesResponsables')
                ->getEleveResponsable1($args['eleveId']);
            $point = new Point($eleveR1['x1'], $eleveR1['y1']);
            $pt = $d2etab->getProjection()->XYZversgRGF93($point);
            $d = $d2etab->calculDistance($pt, $ptetab);
            // supprimer les références à l'adresse perso de l'élève
            $data = array(
                'millesime' => Session::get('millesime'),
                'eleveId' => $args['eleveId'],
                'chez' => null,
                'adresseL1' => null,
                'adresseL2' => null,
                'codePostal' => null,
                'communeId' => null,
                'x' => 0,
                'y' => 0,
                'distanceR1' => round($d / 1000, 1)
            );
            $tableScolarites = $this->getServiceLocator()->get('Sbm\Db\Table\Scolarites');
            $oData = $tableScolarites->getObjData();
            $oData->exchangeArray($data);
            // enregistre
            $tableScolarites->saveRecord($oData);
            $this->flashMessenger()->addSuccessMessage('Cette adresse a été effacée.');
            try {
                return $this->redirectToOrigin()->back();
            } catch (\SbmCommun\Model\Mvc\Controller\Plugin\Exception $e) {
                return $this->redirect()->toRoute('sbmgestion/eleve', array(
                    'action' => 'eleve-edit'
                ));
            }
        } else {
            // préparer le Point dans le système gRGF93
            $point = new Point($eleve['x'], $eleve['y']);
            $pt = $d2etab->getProjection()->XYZversgRGF93($point);
            // préparer le tableau de données pour initialiser le formulaire
            $data = array(
                'eleveId' => $args['eleveId'],
                'lat' => $pt->getLatitude(),
                'lng' => $pt->getLongitude(),
                'chez' => $eleve['chez'],
                'adresseL1' => $eleve['adresseEleveL1'],
                'adresseL2' => $eleve['adresseEleveL2'],
                'codePostal' => $eleve['codePostalEleve'],
                'communeId' => $eleve['communeEleveId']
            );
        }
        // charger le formulaire
        $form->setData($data);
        return new ViewModel(array(
            'point' => $pt,
            'form' => $form->prepare(),
            'eleveId' => $args['eleveId'],
            'eleve' => $eleve,
            'config' => $configCarte
        ));
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
        $projection = $this->getServiceLocator()->get('SbmCarto\Projection');
        $rangeX = $projection->getRangeX();
        $rangeY = $projection->getRangeY();
        $pasLocalisaton = 'Literal:Not((x Between %d And %d) And (y Between %d And %d))';
        
        $args = $this->initListe('responsables', null, array(
            'nbEnfants',
            'nbInscrits',
            'nbPreinscrits'
        ), array(
            'nomSA' => 'res.nomSA',
            'selection' => 'res.selection',
            'inscrits' => 'Literal:count(ins.eleveId) > 0',
            'preinscrits' => 'Literal:count(pre.eleveId) > 0',
            'localisation' => sprintf($pasLocalisaton, $rangeX['parent'][0], $rangeX['parent'][1], $rangeY['parent'][0], $rangeY['parent'][1])
        ));
        if ($args instanceof Response)
            return $args;
        
        return new ViewModel(array(
            'paginator' => $this->getServiceLocator()
                ->get('Sbm\Db\Query\Responsables')
                ->paginator($args['where'], array(
                'nom',
                'prenom'
            )),
            'page' => $this->params('page', 1),
            'nb_pagination' => $this->getNbPagination('nb_responsables', 10),
            'criteres_form' => $args['form'],
            'projection' => $this->getServiceLocator()->get('SbmCarto\Projection')
        ));
    }

    public function responsableAjoutAction()
    {
        // utilisation de PostRedirectGet par mesure de sécurité
        $prg = $this->prg();
        if ($prg instanceof Response) {
            // transforme un post en une redirection 303 avec le contenu de post en session 'prg_post1' (Expire_Hops = 1)
            return $prg;
        } elseif ($prg === false) {
            // ce n'était pas un post. Cette entrée est illégale et conduit à un retour à la liste
            return $this->redirect()->toRoute('sbmgestion/eleve', array(
                'action' => 'responsable-liste',
                'page' => $this->params('page', 1)
            ));
        }
        // ici, on a eu un post qui a été transformé en rediretion 303. Les données du post sont dans $prg (à récupérer en un seul appel à cause de Expire_Hops)
        $args = $prg;
        // si $args contient la clé 'cancel' c'est un abandon de l'action
        if (\array_key_exists('cancel', $args)) {
            $this->flashMessenger()->addWarningMessage("L'enregistrement n'a pas été enregistré.");
            return $this->redirect()->toRoute('sbmgestion/eleve', array(
                'action' => 'responsable-liste',
                'page' => $this->params('page', 1)
            ));
        }
        // on ouvre la table des responsables
        $responsableId = null;
        $tableResponsables = $this->getServiceLocator()->get('Sbm\Db\Table\Responsables');
        $db = $this->getServiceLocator()->get('Sbm\Db\DbLib');
        // on ouvre le formulaire et on l'adapte
        $form = new FormResponsable();
        $value_options = $this->getServiceLocator()
            ->get('Sbm\Db\Select\Communes')
            ->desservies();
        $form->setValueOptions('communeId', $value_options)
            ->setValueOptions('ancienCommuneId', $value_options)
            ->setMaxLength($db->getMaxLengthArray('responsables', 'table'));
        unset($value_options);
        
        $form->bind($tableResponsables->getObjData());
        
        if (array_key_exists('submit', $args)) {
            $form->setData($args);
            if ($form->isValid()) {
                $oData = $form->getData();
                if ($tableResponsables->saveRecord($oData)) {
                    // on s'assure de rendre cette commune visible
                    $this->getServiceLocator()
                        ->get('Sbm\Db\table\Communes')
                        ->setVisible($oData->communeId);
                }
                $this->flashMessenger()->addSuccessMessage("La fiche a été enregistrée.");
                return $this->redirect()->toRoute('sbmgestion/eleve', array(
                    'action' => 'responsable-liste',
                    'page' => $this->params('page', 1)
                ));
            }
        }
        return new ViewModel(array(
            'form' => $form->prepare(),
            'page' => $this->params('page', 1),
            'responsableId' => $responsableId,
            'demenagement' => false
        ));
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
                return $this->redirect()->toRoute('login', array(
                    'action' => 'logout'
                ));
            }
        } else {
            $args = $prg;
            if (array_key_exists('cancel', $args)) {
                $this->flashMessenger()->addWarningMessage("L'enregistrement n'a pas été modifié.");
                try {
                    return $this->redirectToOrigin()->back();
                } catch (\SbmCommun\Model\Mvc\Controller\Plugin\Exception $e) {
                    return $this->redirect()->toRoute('sbmgestion/eleve', array(
                        'action' => 'responsable-liste',
                        'page' => $this->params('page', 1)
                    ));
                }
            } elseif (array_key_exists('origine', $args)) {
                $this->redirectToOrigin()->setBack($args['origine']);
                unset($args['origine']);
                $this->setToSession('post', $args);
            }
        }
        // on ouvre la table des données
        $responsableId = $args['responsableId'];
        $tableResponsables = $this->getServiceLocator()->get('Sbm\Db\Table\Responsables');
        $db = $this->getServiceLocator()->get('Sbm\Db\DbLib');
        // on ouvre le formulaire et on l'adapte
        $form = new FormResponsable();
        $value_options = $this->getServiceLocator()
            ->get('Sbm\Db\Select\Communes')
            ->desservies();
        $form->setValueOptions('communeId', $value_options)
            ->setValueOptions('ancienCommuneId', $value_options)
            ->setMaxLength($db->getMaxLengthArray('responsables', 'table'));
        unset($value_options);
        
        $form->bind($tableResponsables->getObjData());
        
        if (\array_key_exists('submit', $args)) {
            $form->setData($args);
            if ($form->isValid()) {
                $oData = $form->getData();
                if ($tableResponsables->saveRecord($oData)) {
                    // on s'assure de rendre cette commune visible
                    $this->getServiceLocator()
                        ->get('Sbm\Db\table\Communes')
                        ->setVisible($oData->communeId);
                }
                $this->flashMessenger()->addSuccessMessage("Les modifications ont été enregistrées.");
                try {
                    return $this->redirectToOrigin()->back();
                } catch (\SbmCommun\Model\Mvc\Controller\Plugin\Exception $e) {
                    return $this->redirect()->toRoute('sbmgestion/eleve', array(
                        'action' => 'responsable-liste',
                        'page' => $this->params('page', 1)
                    ));
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
        return new ViewModel(array(
            'form' => $form->prepare(),
            'page' => $this->params('page', 1),
            'responsableId' => $responsableId,
            'identite' => $identite,
            'demenagement' => $demenagement
        ));
    }

    public function responsableGroupAction()
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
        $responsableId = StdLib::getParam('responsableId', $args, - 1);
        if ($responsableId == - 1) {
            $this->flashMessenger()->addErrorMessage('Action interdite.');
            return $this->redirect()->toRoute('sbmadmin', array(
                'action' => 'libelle-liste',
                'page' => $currentPage
            ));
        }
        $tableEleves = $this->getServiceLocator()->get('Sbm\Db\Query\Eleves');
        $data = array();
        $data['resp1'] = $tableEleves->duResponsable1($responsableId);
        $data['resp2'] = $tableEleves->duResponsable2($responsableId);
        $data['fact'] = $tableEleves->duResponsableFinancier($responsableId);
        return new ViewModel(array(
            'data' => $data,
            'responsable' => $this->getServiceLocator()
                ->get('Sbm\Db\Vue\Responsables')
                ->getRecord($responsableId),
            'page' => $currentPage,
            'responsableId' => $responsableId,
            'query' => $this->getServiceLocator()->get('Sbm\Db\Query\AffectationsServicesStations')
        ));
    }

    public function responsableSupprAction()
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
                'alias' => 'Sbm\Db\Table\Responsables',
                'id' => 'responsableId'
            ),
            'form' => $form
        );
        $vueResponsables = $this->getServiceLocator()->get('Sbm\Db\Vue\Responsables');
        try {
            $r = $this->supprData($params, function ($id, $tableResponsables) use($vueResponsables) {
                return array(
                    'id' => $id,
                    'data' => $vueResponsables->getRecord($id)
                );
            });
        } catch (\Zend\Db\Adapter\Exception\InvalidQueryException $e) {
            $this->flashMessenger()->addWarningMessage('Impossible de supprimer ce responsable car il a des enregistrements (élèves ou paiements) en liaison.');
            return $this->redirect()->toRoute('sbmgestion/eleve', array(
                'action' => 'responsable-liste',
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
                    return $this->redirect()->toRoute('sbmgestion/eleve', array(
                        'action' => 'responsable-liste',
                        'page' => $currentPage
                    ));
                    break;
                default:
                    return new ViewModel(array(
                        'form' => $form->prepare(),
                        'page' => $currentPage,
                        'data' => StdLib::getParam('data', $r->getResult()),
                        'responsableId' => StdLib::getParam('id', $r->getResult()),
                        'data_dependantes' => $this->getServiceLocator()
                            ->get('Sbm\Db\Table\Eleves')
                            ->duResponsable(StdLib::getParam('id', $r->getResult()))
                    ));
                    break;
            }
        }
    }

    public function responsableLocalisationAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            $args = $this->getFromSession('post', false);
            if ($args === false) {
                $this->flashMessenger()->addErrorMessage('Action interdite');
                return $this->redirect()->toRoute('login', array(
                    'action' => 'logout'
                ));
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
                    return $this->redirect()->toRoute('sbmgestion/eleve', array(
                        'action' => 'responsable-liste'
                    ));
                }
            }
        }
        // les outils de travail : formulaire et convertisseur de coordonnées
        // nécessaire pour valider lat et lng
        $configCarte = StdLib::getParamR(array(
            'sbm',
            'cartes',
            'parent'
        ), $this->getServiceLocator()->get('config'));
        $form = new LatLngForm(array(
            'responsableId' => array(
                'id' => 'responsableId'
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
            ->fromRoute('sbmgestion/eleve', array(
            'action' => 'responsable-localisation'
        )));
        
        // traitement de la réponse
        $d2etab = $this->getServiceLocator()->get('SbmCarto\DistanceEtablissements');
        if (array_key_exists('submit', $args)) {
            $form->setData(array(
                'responsableId' => $args['responsableId'],
                'lat' => $args['lat'],
                'lng' => $args['lng']
            ));
            if ($form->isValid()) {
                // détermine le point. Il est reçu en gRGF93 et sera enregistré en XYZ
                $pt = new Point($args['lng'], $args['lat'], 0, 'degré');
                $point = $d2etab->getProjection()->gRGF93versXYZ($pt);
                // enregistre les coordonnées dans la table
                $tableResponsables = $this->getServiceLocator()->get('Sbm\Db\Table\Responsables');
                $oData = $tableResponsables->getObjData();
                $oData->exchangeArray(array(
                    'responsableId' => $args['responsableId'],
                    'x' => $point->getX(),
                    'y' => $point->getY()
                ));
                $tableResponsables->saveRecord($oData);
                $this->flashMessenger()->addSuccessMessage('La localisation de cette adresse est enregistrée.');
                $this->getServiceLocator()
                    ->get('Sbm\MajDistances')
                    ->pour($args['responsableId']);
                try {
                    return $this->redirectToOrigin()->back();
                } catch (\SbmCommun\Model\Mvc\Controller\Plugin\Exception $e) {
                    return $this->redirect()->toRoute('sbmgestion/eleve', array(
                        'action' => 'responsable-liste'
                    ));
                }
            }
        }
        // chercher le responsable dans la table
        $responsable = $this->getServiceLocator()
            ->get('Sbm\Db\Vue\Responsables')
            ->getRecord($args['responsableId']);
        // préparer le Point dans le système gRGF93
        $point = new Point($responsable->x, $responsable->y);
        $pt = $d2etab->getProjection()->XYZversgRGF93($point);
        // charger le formulaire
        $form->setData(array(
            'responsableId' => $args['responsableId'],
            'lat' => $pt->getLatitude(),
            'lng' => $pt->getLongitude()
        ));
        return new ViewModel(array(
            'point' => $pt,
            'form' => $form->prepare(),
            'responsableId' => $args['responsableId'],
            'responsable' => $responsable,
            'config' => $configCarte
        ));
    }

    public function responsablePdfAction()
    {
        $projection = $this->getServiceLocator()->get('SbmCarto\Projection');
        $rangeX = $projection->getRangeX();
        $rangeY = $projection->getRangeY();
        $pasLocalisaton = 'Literal:Not((x Between %d And %d) And (y Between %d And %d))';
        
        $criteresObject = array(
            '\SbmCommun\Model\Db\ObjectData\Criteres',
            array(
                'strict' => array(
                    'nbInscrits',
                    'nbPreinscrits',
                    'selection'
                ),
                'expressions' => array(
                    'nbEnfants' => 'Expression:nbEleves = ?',
                    'inscrits' => 'Literal:0',
                    'preinscrit' => 'Literal:0',
                    'localisation' => sprintf($pasLocalisaton, $rangeX['parent'][0], $rangeX['parent'][1], $rangeY['parent'][0], $rangeY['parent'][1])
                )
            )
        );
        $criteresForm = array(
            '\SbmCommun\Form\CriteresForm',
            'responsables'
        );
        $documentId = null;
        $retour = array(
            'route' => 'sbmgestion/eleve',
            'action' => 'responsable-liste'
        );
        return $this->documentPdf($criteresObject, $criteresForm, $documentId, $retour);
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
            return $this->redirect()->toRoute('sbmgestion/eleve', array(
                'action' => 'responsable-liste',
                'page' => $this->params('page', 1)
            ));
        } else {
            $args = $prg;
            if (array_key_exists('logernon', $args) || ! array_key_exists('responsableId', $args)) {
                return $this->redirect()->toRoute('sbmgestion/eleve', array(
                    'action' => 'responsable-liste',
                    'page' => $this->params('page', 1)
                ));
            }
        }
        $tUsers = $this->getServiceLocator()->get('Sbm\Db\Table\Users');
        $responsable = $this->getServiceLocator()
            ->get('Sbm\Db\Table\Responsables')
            ->getRecord($args['responsableId']);
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
                $form = new ButtonForm(array(
                    'responsableId' => null
                ), array(
                    'logeroui' => array(
                        'class' => 'confirm',
                        'value' => 'Confirmer',
                        'class' => 'button default'
                    ),
                    'logernon' => array(
                        'class' => 'confirm',
                        'value' => 'Abandonner',
                        'class' => 'button default'
                    )
                ));
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
                        $mailTemplate = new MailTemplate('ouverture-compte');
                        $params = array(
                            'to' => array(
                                array(
                                    'email' => $odata->email,
                                    'name' => $odata->nom . ' ' . $odata->prenom
                                )
                            ),
                            'subject' => 'Ouverture d\'un compte',
                            'body' => array(
                                'html' => $mailTemplate->render(array(
                                    'titre' => $odata->titre,
                                    'nom' => $odata->nom,
                                    'prenom' => $odata->prenom,
                                    'url_confirme' => $this->url()
                                        ->fromRoute('login', array(
                                        'action' => 'confirm',
                                        'id' => $odata->token
                                    ), array(
                                        'force_canonical' => true
                                    ))
                                ))
                            )
                        );
                        $this->getEventManager()->addIdentifiers('SbmMail\Send');
                        $this->getEventManager()->trigger('sendMail', $this->getServiceLocator(), $params);
                        $this->flashMessenger()->addInfoMessage('Un mail a été envoyé à l\'adresse indiquée pour donner les instructions d\'accès.');
                        
                        return $this->redirect()->toRoute('sbmgestion/eleve', array(
                            'action' => 'responsable-liste',
                            'page' => $this->params('page', 1)
                        ));
                    }
                }
                $form->setData(array(
                    'responsableId' => $args['responsableId']
                ));
            }
        }
        return new ViewModel(array(
            'form' => is_null($form) ? null : $form->prepare(),
            'info' => $args['info'],
            'msg' => $msg,
            'page' => $this->params('page', 1)
        ));
    }
}
