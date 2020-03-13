<?php
/**
 * Controller principal du module SbmGestion
 * Méthodes utilisées pour gérer les élèves et les responsables
 *
 * @project sbm
 * @package module/SbmGestion/src/Controller
 * @filesource EleveController.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 12 mars 2020
 * @version 2020-2.6.0
 */
namespace SbmGestion\Controller;

use SbmBase\Model\Session;
use SbmBase\Model\StdLib;
use SbmCartographie\GoogleMaps;
use SbmCartographie\Model\Point;
use SbmCartographie\Model\Projection;
use SbmCommun\Form;
use SbmCommun\Model\Mvc\Controller\AbstractActionController;
use SbmCommun\Model\Mvc\Controller\Plugin\Exception\ExceptionInterface as RedirectToOrigineException;
use SbmCommun\Model\Strategy\Semaine;
use SbmGestion\Form\Eleve as FormEleve;
use SbmMail\Form\Mail as FormMail;
use SbmMail\Model\Template as MailTemplate;
use Zend\Db\Sql\Where;
use Zend\Http\PhpEnvironment\Response;
use Zend\View\Model\ViewModel;

class EleveController extends AbstractActionController
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
     * On ne peut pas utiliser la méthode initListe('eleves') parce que
     * l'objectDataCriteres est différent (méthode getWhere particulière)
     *
     * @return \Zend\Http\PhpEnvironment\Response|\Zend\Http\Response|\Zend\View\Model\ViewModel
     */
    public function eleveListeAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            // ce n'était pas un post. Prendre les paramètres éventuellement dans la
            // session (cas
            // du paginator ou de F5 )
            $this->sbm_isPost = false;
            $args = Session::get('post', [], $this->getSessionNamespace());
        } else {
            // c'était un post ; on le met en session si ce n'est pas un retour ou un
            // cancel
            $args = $prg;
            $retour = StdLib::getParam('op', $args, '') == 'retour';
            if ($retour) {
                // dans ce cas, il s'agit du retour d'une action de type suppr, ajout ou
                // edit.
                // Comme pour un get, on récupère ce qui est en session.
                $this->sbm_isPost = false;
                $args = Session::get('post', [], $this->getSessionNamespace());
            } else {
                if (array_key_exists('cancel', $args)) {
                    try {
                        return $this->redirectToOrigin()->back();
                    } catch (RedirectToOrigineException $e) {
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
        $criteres_form->setValueOptions('etablissementId',
            $this->db_manager->get('Sbm\Db\Select\Etablissements')
                ->desservis())
            ->setValueOptions('classeId',
            $this->db_manager->get('Sbm\Db\Select\Classes')
                ->tout());
        // créer un objectData qui contient la méthode getWhere() adhoc
        $criteres_obj = new \SbmGestion\Model\Db\ObjectData\CriteresEleves(
            $criteres_form->getElementNames());

        if ($this->sbm_isPost) {
            $criteres_form->setData($args);
            if ($criteres_form->isValid()) {
                $criteres_obj->exchangeArray($criteres_form->getData());
            }
        }
        // récupère les données de la session si le post n'a pas été validé dans le
        // formulaire (pas de post ou invalide)
        if (! $criteres_form->hasValidated() && ! empty($args)) {
            $criteres_obj->exchangeArray($args);
            $criteres_form->setData($criteres_obj->getArrayCopy());
        }
        return new ViewModel(
            [
                'paginator' => $this->db_manager->get('Sbm\Db\Query\ElevesResponsables')->paginatorScolaritesR2(
                    $criteres_obj->getWhere(), [
                        'nom',
                        'prenom'
                    ]),
                'page' => $this->params('page', 1),
                'count_per_page' => $this->getPaginatorCountPerPage('nb_eleves', 10),
                'criteres_form' => $criteres_form,
                'dateDebut' => $this->db_manager->get('Sbm\Db\System\Calendar')->getEtatDuSite()['dateDebut']->format(
                    'Y-m-d')
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
        $args = $prg ?: [];
        if (array_key_exists('cancel', $args)) {
            return $this->redirect()->toRoute('sbmgestion/eleve',
                [
                    'action' => 'eleve-liste',
                    'page' => $this->params('page', 1)
                ]);
        }
        $form = new Form\ButtonForm([],
            [
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
                $this->flashMessenger()->addSuccessMessage(
                    'Toutes les fiches sont désélectionnées.');
                return $this->redirect()->toRoute('sbmgestion/eleve',
                    [
                        'action' => 'eleve-liste',
                        'page' => $this->params('page', 1)
                    ]);
            }
        }
        $where = new Where();
        $where->equalTo('selection', 1);
        return new ViewModel(
            [
                'form' => $form,
                'nbSelection' => $televes->fetchAll($where)->count()
            ]);
    }

    /**
     * Si on arrive par post, on passera : - ajouter : uniquement la présence de la clé.
     * En général c'est le nom du bouton submit. - orinine : url d'origine de l'appel pour
     * assurer un retour par redirectToOrigin()->back() à la fin de l'opération (en
     * général dans eleveEditAction()). Si on arrive par get, on s'assurera que
     * redirectToOrigin()->setBack() a bien été fait avant. Lorsqu'on arrive par post, on
     * enregistre en session le paramètre responsableId s'il existe ou 0 sinon. Lorsqu'on
     * arrive par get, on récupère le responsableId en session. Il va permettre
     * d'initialiser le responsable1Id du formulaire.
     *
     *
     * @return \Zend\Http\PhpEnvironment\Response|\Zend\Http\Response|\Zend\View\Model\ViewModel
     */
    public function eleveAjoutAction()
    {
        $page = $this->params('page', 1); // paramètre du retour à la liste à la fin du
                                          // processus
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            // entrée lors d'un retour éventuel par F5 ou back en 22
            $prg = Session::get('post', false, $this->getSessionNamespace('ajout', 1));
        }
        $args = $prg ?: [];
        if (array_key_exists('ajouter', $args)) {
            if (array_key_exists('responsableId', $args)) {
                $responsableId = $args['responsableId'];
            } else {
                $responsableId = 0;
            }
            Session::set('responsableId', $responsableId,
                $this->getSessionNamespace('ajout', 1));
        } else {
            $responsableId = Session::get('responsableId', 0,
                $this->getSessionNamespace('ajout', 1));
        }
        if (array_key_exists('origine', $args)) {
            $this->redirectToOrigin()->setBack($args['origine']);
            // par la suite, on ne s'occupe plus de 'origine' mais on ressort par un
            // redirectToOrigin()->back()
            unset($args['origine']);
        }
        if (array_key_exists('cancel', $args)) {
            Session::remove('post', $this->getSessionNamespace('ajout', 1));
            Session::remove('responsableId', $this->getSessionNamespace('ajout', 1));
            $this->flashMessenger()->addInfoMessage('Saisie abandonnée.');
            try {
                return $this->redirectToOrigin()->back();
            } catch (RedirectToOrigineException $e) {
                return $this->redirect()->toRoute('sbmgestion/eleve',
                    [
                        'action' => 'eleve-liste',
                        'page' => $page
                    ]);
            }
        } elseif (array_key_exists('submit', $args)) {
            $ispost = true;
            // pour un retour éventuel par F5 ou back en 22
            Session::set('post', $args, $this->getSessionNamespace('ajout', 1));
        } else {
            $ispost = false;
        }

        $form = $this->form_manager->get(FormEleve\AddElevePhase1::class);
        $value_options = $this->db_manager->get('Sbm\Db\Select\Responsables');
        $form->setAttribute('action',
            $this->url()
                ->fromRoute('sbmgestion/eleve',
                [
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
                // les valeurs obligatoires sont prises dans odata, responsable2Id est
                // pris dans
                // args pour éviter de gérer les exceptions
                $where = new Where();
                $filtreSA = new \SbmCommun\Filter\SansAccent();
                $where->equalTo('ele.nomSA', $filtreSA->filter($odata->nom))
                    ->equalTo('ele.prenomSA', $filtreSA->filter($odata->prenom))
                    ->nest()
                    ->equalTo('dateN', $odata->dateN)->or->equalTo('responsable1Id',
                    $odata->responsable1Id)->or->equalTo('responsable2Id',
                    $odata->responsable1Id)->or->equalTo('responsable1Id',
                    StdLib::getParam('responsable2Id', $args, - 1))->or->equalTo(
                    'responsable2Id', StdLib::getParam('responsable2Id', $args, - 1))->unnest();
                $resultset = $this->db_manager->get('Sbm\Db\Query\ElevesResponsables')->withR2(
                    $where);
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
        return new ViewModel(
            [
                'page' => $page,
                // form est le formulaire si les données ne sont pas validées (ou pas de
                // données)
                'form' => is_null($form) ? $form : $form->prepare(),
                // liste est null ou est un resultset à parcourir pour montrer la liste
                'eleves' => $resultset,
                // data = null ou contient les données validées à passer à nouveau en post
                'data' => $odata
            ]);
    }

    /**
     * Reçoit un post avec :<ul><li>eleveId d'un élève existant</li><li>info (nom
     * prénom)</li></ul> Met ces informations en session car on reviendra ici en cas
     * d'entrée par GET ultérieure (F5 ou back). Vérifie si la fiche scolarité existe pour
     * cette année courante et oriente sur :<ul><li>si oui : eleveEditAction()</li><li>si
     * non : eleveAjout31Action()</li></ul>On arrive ici obligatoirement par un post. Il
     * n'y a pas de view associée.
     */
    public function eleveAjout21Action()
    {
        $page = $this->params('page');
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false || ! array_key_exists('eleveId', $prg)) {
            $prg = Session::get('post', false, $this->getSessionNamespace('ajout', 2));
            if ($prg === false) {
                $this->flashMessenger()->addErrorMessage('Action interdite.');
                try {
                    return $this->redirectToOrigin()->back();
                } catch (RedirectToOrigineException $e) {
                    return $this->redirect()->toRoute('sbmgestion/eleve',
                        [
                            'action' => 'eleve-liste',
                            'page' => $page
                        ]);
                }
            }
        } else {
            // pour un retour éventuel par F5 ou back
            Session::set('post', $prg, $this->getSessionNamespace('ajout', 2));
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
     * Création de la fiche dans la table eleves et récupération de son eleveId puis
     * passage en eleveAjout31Action(). L'entrée se fait :<ul><li>directement depuis
     * eleveAjoutAction() s'il n'y a pas d'enregistrement ayant ces caractéristiques. Dans
     * ce cas, le paramètre odata porte les informations à enregistrer.</li><li>par appel
     * POST depuis la vue phase 1 si l'utilisateur choisi explicitement de créer une
     * nouvelle fiche. Dans ce cas, les paramètres reçus par POST sont :<ul><li>ceux du
     * formulaire AddElevePhase1</li><li>ceux renvoyé par les hiddens depuis la
     * liste</li></ul></li></ul> Le retour par get est interdit afin d'éviter de recréer
     * cet enregistrement.
     *
     * @param \SbmCommun\Model\Db\ObjectData\ObjectDataInterface $odata
     *
     * @return \Zend\Http\PhpEnvironment\Response
     */
    public function eleveAjout22Action($odata = null)
    {
        $page = $this->params('page', 1); // pour le retour à la liste à la fin du
                                          // processus
        $tEleves = $this->db_manager->get('Sbm\Db\Table\Eleves');
        if (is_null($odata)) {
            $prg = $this->prg();
            if ($prg instanceof Response) {
                return $prg;
            } elseif ($prg === false) {
                // retour vers le point d'entrée d'un F5 ou d'un back
                return $this->redirect()->toRoute('sbmgestion/eleve',
                    [
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
        $viewmodel = $this->eleveAjout31Action($eleveId,
            $odata->nom . ' ' . $odata->prenom);
        $viewmodel->setTemplate('sbm-gestion/eleve/eleve-ajout31.phtml');
        return $viewmodel;
    }

    /**
     * Il s'agit de compléter les informations de scolarité pour un élève existant. Donc
     * en cas de F5 ou back on doit revenir en eleveAjout21Action() car la fiche eleve
     * existe. L'entrée initiale se fait toujours par un appel fonction. On montre le
     * formulaire AddElevePhase2 pour compléter les informations de scolarités. L'entrée
     * par POST correspond au retour du formulaire et contient donc obligatoirement un
     * 'cancel' ou un 'submit' et dans ce dernier cas les données doivent être validées
     * par le formulaire. La scolarité n'étant pas connue, le formulaire proposera une
     * liste déroulante vide pour la classe qui sera mise à jour par ajax en fonction du
     * niveau de l'établissement sélectionné.
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
                // Cela pourrait être F5 ou back du navigateur. Il faut savoir si la fiche
                // a été
                // créée.
                // On reviendra donc toujours à eleveAjout21Action() pour vérifier.
                return $this->redirect()->toRoute('sbmgestion/eleve',
                    [
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
                    } catch (RedirectToOrigineException $e) {
                        return $this->redirect()->toRoute('sbmgestion/eleve',
                            [
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
                    return $this->redirect()->toRoute('sbmgestion/eleve',
                        [
                            'action' => 'eleve-ajout',
                            'page' => $page
                        ]);
                }
                $ispost = array_key_exists('submit', $args);
            }
        }
        // ici on a un eleveId qui possède une fiche dans la table eleves et pour lequel
        // on doit saisir la scolarite
        $tableScolarites = $this->db_manager->get('Sbm\Db\Table\Scolarites');
        $form = $this->form_manager->get(FormEleve\AddElevePhase2::class);
        $form->get('classeId')->setEmptyOption('Choisir d\'abord l\'établissement');
        $form->setAttribute('action',
            $this->url()
                ->fromRoute('sbmgestion/eleve',
                [
                    'action' => 'eleve-ajout31',
                    'page' => $page
                ]))
            ->setValueOptions('etablissementId',
            $this->db_manager->get('Sbm\Db\Select\Etablissements')
                ->desservis())
            ->setValueOptions('joursTransport', Semaine::getJours())
            ->bind($tableScolarites->getObjData());
        if ($ispost) {
            // avant de valider, il faut donner les valeurs possibles pour classeId
            if (array_key_exists('etablissementId', $args) && $args['etablissementId']) {
                $etablissement = $this->db_manager->get('Sbm\Db\Table\Etablissements')->getRecord(
                    $args['etablissementId']);
                $form->setValueOptions('classeId',
                    $this->db_manager->get('Sbm\Db\Select\Classes')
                        ->niveau($etablissement->niveau, 'in'));
            }
            $form->setData($args);
            if ($form->isValid()) {
                $odata = $form->getData();
                $odata->millesime = Session::get('millesime');
                $odata->internet = 0;
                $recalcul = $tableScolarites->saveRecord($odata);
                if ($recalcul) {
                    $majDistances = $this->cartographie_manager->get(
                        'Sbm\CalculDroitsTransport');
                    try {
                        $majDistances->majDistancesDistrict($eleveId);
                    } catch (\Exception $e) {
                        die($e->getMessage());
                    }
                }
                $viewModel = $this->eleveEditAction(
                    [
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
        $form->setData(
            [
                'eleveId' => $eleveId,
                'responsable1Id' => $data['responsable1Id'],
                'responsable2Id' => isset($data['responsable2Id']) ? $data['responsable2Id'] : '',
                'dateDebut' => Session::get('as')['dateDebut'],
                'dateFin' => Session::get('as')['dateFin'],
                'regimeId' => 0,
                'demandeR1' => 1,
                'demandeR2' => 0
            ]);
        $ophoto = new \SbmCommun\Model\Photo\Photo();
        try {
            $elevephoto = $this->db_manager->get('Sbm\Db\Table\ElevesPhotos')->getRecord(
                $eleveId);
            $dataphoto = $ophoto->img_src(stripslashes($elevephoto->photo), 'jpeg');
        } catch (\Exception $e) {
            $dataphoto = $ophoto->img_src($ophoto->getSansPhotoGifAsString(), 'gif');
        }
        return new ViewModel(
            [
                'page' => $page,
                'form' => $form->prepare(),
                'info' => $info,
                'data' => $data,
                'scolarite_precedente' => $this->db_manager->get(
                    'Sbm\Db\Query\ElevesScolarites')->getScolaritePrecedente($eleveId),
                'dataphoto' => $dataphoto
            ]);
    }

    /**
     * Cette méthode est appelée :<ul><li>par post et reçoit :
     * <ul><li>eleveId</li><li>info</li><li>origine (optionnel) ou group
     * (optionnel)</li><li>op = 'modifier' ou 'ajouter'</li></ul></li><li>comme une
     * fonction en passant un paramètre $args qui sera un tableau contenant les 4
     * clés.</li></ul> Si on arrive par eleveAjoutAction() on ne passera pas origine car
     * le redirectToOrigin() est déjà en place. <p>La liste déroulante de Classe doit être
     * initialisée en tenant compte du niveau de l'établissement.</p>
     *
     * @return \Zend\Http\PhpEnvironment\Response|\Zend\Http\Response|\Zend\View\Model\ViewModel
     */
    public function eleveEditAction($args = null)
    {
        $currentPage = $this->params('page', 1);
        $millesime = Session::get('millesime');
        if (is_null($args)) {
            $prg = $this->prg();
            if ($prg instanceof Response) {
                return $prg;
            } elseif ($prg === false || (isset($prg['op']) && $prg['op'] == 'retour')) {
                $args = Session::get('post', false);
                if ($args === false) {
                    $this->flashMessenger()->addErrorMessage('Action interdite');
                    try {
                        return $this->redirectToOrigin()->back();
                    } catch (RedirectToOrigineException $e) {
                        return $this->redirect()->toRoute('login',
                            [
                                'action' => 'logout'
                            ]);
                    }
                }
            } else {
                $args = $prg;
                // !!! important !!! traiter 'cancel' avant 'origine'
                if (array_key_exists('cancel', $args)) {
                    $this->flashMessenger()->addWarningMessage(
                        "L'enregistrement n'a pas été modifié.");
                    try {
                        return $this->redirectToOrigin()->back();
                    } catch (RedirectToOrigineException $e) {
                        return $this->redirect()->toRoute('sbmgestion/eleve',
                            [
                                'action' => 'eleve-liste',
                                'page' => $currentPage
                            ]);
                    }
                }

                if (array_key_exists('group', $args)) {
                    $this->redirectToOrigin()->setBack($args['group']);
                    unset($args['group']);
                    Session::set('post', $args);
                } elseif (array_key_exists('origine', $args)) {
                    $this->redirectToOrigin()->setBack($args['origine']);
                    unset($args['origine']);
                    Session::set('post', $args);
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
            Session::set('post', $args);
        }
        if (! array_key_exists('eleveId', $args)) {
            $this->flashMessenger()->addErrorMessage("Pas d'identifiant élève !");
            try {
                return $this->redirectToOrigin()->back();
            } catch (RedirectToOrigineException $e) {
                return $this->redirect()->toRoute('sbmgestion/eleve',
                    [
                        'action' => 'eleve-liste',
                        'page' => $currentPage
                    ]);
            }
        }
        $eleveId = $args['eleveId'];
        if ($eleveId == - 1) {
            try {
                return $this->redirectToOrigin()->back();
            } catch (RedirectToOrigineException $e) {
                return $this->redirect()->toRoute('sbmgestion/eleve',
                    [
                        'action' => 'eleve-liste',
                        'page' => $currentPage
                    ]);
            }
        }
        $tEleves = $this->db_manager->get('Sbm\Db\Table\Eleves');
        $tScolarites = $this->db_manager->get('Sbm\Db\Table\Scolarites');
        $tTarifs = $this->db_manager->get('Sbm\Db\Table\Tarifs');
        // ATTENTION Pour la version de Millau on n'a besoin que des grilles tarifaires
        $qAffectations = $this->db_manager->get(
            'Sbm\Db\Query\AffectationsServicesStations');
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
        $odata1 = $tScolarites->getRecord(
            [
                'millesime' => $millesime,
                'eleveId' => $eleveId
            ]);
        $subventions = [
            'R1' => $odata1->subventionR1,
            'R2' => $odata1->subventionR2
        ];
        if ($odata1->inscrit) {
            $inscrit = $odata1->paiement;
            $inscrit |= $odata1->fa;
            $inscrit |= $odata1->gratuit > 0;
            $inscrit |= ($odata1->demandeR1 == 2 && $odata1->accordR1 == 0 &&
                $odata1->subventionR1 == 1);
            $inscrit |= ($odata1->demandeR2 == 2 && $odata1->accordR2 == 0 &&
                $odata1->subventionR2 == 1);
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
        $historique['scolarite']['dateCarte'] = $odata1->dateCarte;
        $historique['scolarite']['grilleTarifR1'] = $tTarifs->getGrille(
            $odata1->grilleTarifR1);
        $historique['scolarite']['reductionR1'] = $odata1->reductionR1;
        $historique['scolarite']['grilleCodeR2'] = $odata1->grilleTarifR2;
        $historique['scolarite']['reductionR2']= $odata1->reductionR2;
        $historique['scolarite']['duplicata'] = $odata1->duplicata;
        $historique['scolarite']['internet'] = $odata1->internet;

        $respSelect = $this->db_manager->get('Sbm\Db\Select\Responsables');
        $etabSelect = $this->db_manager->get('Sbm\Db\Select\Etablissements')->desservis();
        $clasSelect = $this->db_manager->get('Sbm\Db\Select\Classes')->tout();
        $form = $this->form_manager->get(FormEleve\EditForm::class);
        $form->setAttribute('action',
            $this->url()
                ->fromRoute('sbmgestion/eleve',
                [
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
            if (array_key_exists('etablissementId', $args) && $args['etablissementId']) {
                $etablissement = $this->db_manager->get('Sbm\Db\Table\Etablissements')->getRecord(
                    $args['etablissementId']);
                $form->setValueOptions('classeId',
                    $this->db_manager->get('Sbm\Db\Select\Classes')
                        ->niveau($etablissement->niveau, 'in'));
            }
            $form->setData($args);
            if ($form->isValid()) { // controle le csrf
                $dataValid = array_merge([
                    'millesime' => $millesime
                ], $form->getData());
                // changeR1 et changeR2 indiquent s'il faut mette à jour en cascade le
                // changement de responsable dans la table affectations
                if (! $dataValid['ga'] && ! empty($dataValid['responsable2Id'])) {
                    $dataValid['responsable2Id'] = '';
                }
                $changeR1 = $dataValid['responsable1Id'] != $odata0->responsable1Id;
                $changeR2 = false;
                if (! is_null($odata0->responsable2Id)) {
                    $changeR2 = empty($dataValid['responsable2Id']) ||
                        $dataValid['responsable2Id'] != $odata0->responsable2Id;
                }
                // enregistrement dans la table eleves

                $tEleves->saveRecord($tEleves->getObjData()
                    ->exchangeArray($dataValid));
                // maj en cascade dans la table affectations
                $tAffectations = $this->db_manager->get('Sbm\Db\Table\Affectations');
                if ($changeR1) {
                    // maj du responsableId
                    $tAffectations->updateResponsableId($millesime, $eleveId,
                        $odata0->responsable1Id, $dataValid['responsable1Id']);
                }
                if ($changeR2) {
                    if (empty($dataValid['responsable2Id'])) {
                        // suppression des affectations relatives à cet élève pour ce
                        // millesime
                        $tAffectations->deleteResponsableId($millesime, $eleveId,
                            $odata0->responsable2Id);
                        $dataValid['demandeR2'] = 0;
                    } else {
                        // maj du responsableId
                        $tAffectations->updateResponsableId($millesime, $eleveId,
                            $odata0->responsable2Id, $dataValid['responsable2Id']);
                    }
                }
                // enregistrement dans la table scolarites
                $odata = $tScolarites->getObjData()->exchangeArray($dataValid);
                $recalcul = $tScolarites->saveRecord($odata);
                // recalcul des droits et des distances en cas de modification de la
                // destination ou d'une origine
                if ($recalcul || $changeR1 || $changeR2 ||
                    $odata->derogation != $odata1->derogation) {
                    $majDistances = $this->cartographie_manager->get(
                        'Sbm\CalculDroitsTransport');
                    if ($odata1->avoirDroits() && $odata->regimeId == $odata1->regimeId &&
                        $odata->derogation == $odata1->derogation) {
                        $majDistances->majDistancesDistrictSansPerte($eleveId);
                    } else {
                        try {
                            $majDistances->majDistancesDistrict($eleveId);
                        } catch (\Exception $e) {
                            die($e->getMessage());
                        }
                    }
                }
                $this->flashMessenger()->addSuccessMessage(
                    "Les modifications ont été enregistrées.");
                try {
                    return $this->redirectToOrigin()->back();
                } catch (RedirectToOrigineException $e) {
                    return $this->redirect()->toRoute('sbmgestion/eleve',
                        [
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
            // adapte le select classeId
            $etablissement = $this->db_manager->get('Sbm\Db\Table\Etablissements')->getRecord(
                $adata1['etablissementId']);
            $form->setValueOptions('classeId',
                $this->db_manager->get('Sbm\Db\Select\Classes')
                    ->niveau($etablissement->niveau, 'in'));
            $form->setData(array_merge($odata0->getArrayCopy(), $adata1));
        }
        // historique des responsables
        $r = $this->db_manager->get('Sbm\Db\Table\Responsables')->getRecord(
            $odata0->responsable1Id);
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
            $r = $this->db_manager->get('Sbm\Db\Table\Responsables')->getRecord(
                $odata0->responsable2Id);
            $historique['responsable2']['dateCreation'] = $r->dateCreation;
            $historique['responsable2']['dateModification'] = $r->dateModification;
            $historique['responsable2']['dateDemenagement'] = $r->dateDemenagement;
            $historique['responsable2']['demenagement'] = $r->demenagement;
        }
        $ophoto = new \SbmCommun\Model\Photo\Photo();
        try {
            $elevephoto = $this->db_manager->get('Sbm\Db\Table\ElevesPhotos')->getRecord(
                $eleveId);
            $historique['photo']['dateExtraction'] = $elevephoto->dateExtraction;
            $dataphoto = $ophoto->img_src(stripslashes($elevephoto->photo), 'jpeg');
            $flashMessage = '';
        } catch (\Exception $e) {
            $flashMessage = 'Pas de photo pour cet élève.';
            $historique['photo']['dateExtraction'] = '';
            $dataphoto = $ophoto->img_src($ophoto->getSansPhotoGifAsString(), 'gif');
        }
        return new ViewModel(
            [
                'form' => $form->prepare(),
                'page' => $currentPage, // nécessaire pour la compatibilité des appels
                'eleveId' => $eleveId, // nécessaire pour la compatibilité des appels
                'identite' => $identite, // nécessaire pour la compatibilité des appels
                'data' => $invariants,
                'historique' => $historique,
                'args_paiement' => $args_paiement,
                'structAffectations' => [
                    1 => [
                        'annee_courante' => \SbmCommun\Model\View\StructureAffectations::get(
                            $qAffectations->getAffectations($eleveId, 1, false)),
                        'annee_precedente' => \SbmCommun\Model\View\StructureAffectations::get(
                            $qAffectations->getAffectations($eleveId, 1, true))
                    ],
                    2 => [
                        'annee_courante' => \SbmCommun\Model\View\StructureAffectations::get(
                            $qAffectations->getAffectations($eleveId, 2, false)),
                        'annee_precedente' => \SbmCommun\Model\View\StructureAffectations::get(
                            $qAffectations->getAffectations($eleveId, 2, true))
                    ]
                ],
                'subventions' => $subventions,
                'scolarite_precedente' => $this->db_manager->get(
                    'Sbm\Db\Query\ElevesScolarites')->getScolaritePrecedente($eleveId),
                'dataphoto' => $dataphoto,
                'formphoto' => $ophoto->getForm(),
                'flashMessage' => $flashMessage
            ]);
    }

    public function eleveInscrireAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {

            $prg = Session::get('post', false, $this->getSessionNamespace('ajout', 2));
            if ($prg == false) {
                try {
                    return $this->redirectToOrigin()->back();
                } catch (RedirectToOrigineException $e) {
                    $this->flashMessenger()->addErrorMessage('Action interdite');
                    return $this->redirect()->toRoute('sbmgestion/eleve');
                }
            }
        } elseif (array_key_exists('origine', $prg)) {
            $this->redirectToOrigin()->setBack($prg['origine']);
            unset($prg['origine']);
        }
        Session::set('post', $prg, $this->getSessionNamespace('ajout', 2));
        return $this->redirect()->toRoute('sbmgestion/eleve',
            [
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
            $prg = Session::get('post', false, $this->getSessionNamespace());
            if ($prg == false) {
                try {
                    return $this->redirectToOrigin()->back();
                } catch (RedirectToOrigineException $e) {
                    $this->flashMessenger()->addErrorMessage('Action interdite');
                    return $this->redirect()->toRoute('sbmgestion/eleve');
                }
            }
        } elseif (array_key_exists('origine', $prg)) {
            $this->redirectToOrigin()->setBack($prg['origine']);
            unset($prg['origine']);
            Session::set('post', $prg, $this->getSessionNamespace());
        }
        if (! array_key_exists('eleveId', $prg)) {
            try {
                return $this->redirectToOrigin()->back();
            } catch (RedirectToOrigineException $e) {
                $this->flashMessenger()->addErrorMessage('Action interdite');
                return $this->redirect()->toRoute('sbmgestion/eleve');
            }
        }
        $tScolarites = $this->db_manager->get('Sbm\Db\Table\Scolarites');
        $scolarite = $tScolarites->getRecord(
            [
                'millesime' => Session::get('millesime'),
                'eleveId' => $prg['eleveId']
            ]);
        $scolarite->inscrit = 1 - $scolarite->inscrit;
        $tScolarites->saveRecord($scolarite);
        $msg = $scolarite->inscrit ? 'La fiche de cet élève a été activée.' : 'Cet élève a été rayé.';
        $this->flashMessenger()->addSuccessMessage($msg);
        try {
            return $this->redirectToOrigin()->back();
        } catch (RedirectToOrigineException $e) {
            return $this->redirect()->toRoute('sbmgestion/eleve');
        }
    }

    public function eleveEnAttenteAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg == false) {
            $prg = Session::get('post', false, $this->getSessionNamespace());
            if ($prg == false) {
                try {
                    return $this->redirectToOrigin()->back();
                } catch (RedirectToOrigineException $e) {
                    $this->flashMessenger()->addErrorMessage('Action interdite');
                    return $this->redirect()->toRoute('sbmgestion/eleve');
                }
            }
        } elseif (array_key_exists('origine', $prg)) {
            $this->redirectToOrigin()->setBack($prg['origine']);
            unset($prg['origine']);
            Session::set('post', $prg, $this->getSessionNamespace());
        }
        if (! array_key_exists('eleveId', $prg)) {
            try {
                return $this->redirectToOrigin()->back();
            } catch (RedirectToOrigineException $e) {
                $this->flashMessenger()->addErrorMessage('Action interdite');
                return $this->redirect()->toRoute('sbmgestion/eleve');
            }
        }
        $tScolarites = $this->db_manager->get('Sbm\Db\Table\Scolarites');
        $scolarite = $tScolarites->getRecord(
            [
                'millesime' => Session::get('millesime'),
                'eleveId' => $prg['eleveId']
            ]);
        $scolarite->selection = 0;
        $tScolarites->saveRecord($scolarite);
        $msg = 'La fiche de cet élève a été reprise.';
        $this->flashMessenger()->addSuccessMessage($msg);
        try {
            return $this->redirectToOrigin()->back();
        } catch (RedirectToOrigineException $e) {
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
            $args = Session::get('post', false);
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
                Session::set('post', $args);
            }
            if (array_key_exists('cancel', $args)) {
                $this->flashMessenger()->addWarningMessage(
                    "L'enregistrement n'a pas été modifié.");
                try {
                    return $this->redirectToOrigin()->back();
                } catch (RedirectToOrigineException $e) {
                    return $this->redirect()->toRoute('sbmgestion/eleve',
                        [
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
            $where->equalTo('responsable1Id', $responsableId)->OR->equalTo(
                'responsable2Id', $responsableId);
            $or = true;
        }
        $oEleve = $this->db_manager->get('Sbm\Db\Table\Eleves')->getRecord(
            $args['eleveId']);
        $viewmodel = new ViewModel(
            [
                'paginator' => $this->db_manager->get('Sbm\Db\Query\ElevesResponsables')->paginatorScolaritesEleveGroup(
                    $where),
                'page' => $this->params('page', 1),
                'count_per_page' => $this->getPaginatorCountPerPage('nb_eleves', 10),
                'criteres_form' => null,
                'groupe' => $args['op'],
                'eleve' => sprintf('%s %s', $oEleve->prenom, $oEleve->nom)
            ]);
        // $viewmodel->setTemplate('sbm-gestion/eleve/eleve-liste.phtml');
        return $viewmodel;
    }

    /**
     * On reçoit par post un paramètre 'documentId' qui peut être numérique (le documentId
     * de la table documents) ou une chaine de caractères. Dans ce cas, cela peut être le
     * name du document ou le libelle de docaffectations et alors le paramètre id passé
     * par post contient docaffectationId. On lit les critères définis dans le formulaire
     * de critères de eleve-liste (en session avec le sessionNameSpace de
     * eleveListeAction). On transmet le where pour les documents basés sur une table ou
     * vue sql et les tableaux expression, criteres et strict pour ceux basés sur une
     * requête SQL.
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
        return $this->documentPdf($criteresObject, $criteresForm, $documentId, $retour,
            [
                'criteres' => true
            ]);
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
                    $where->equalTo('responsable1Id', $responsableId)->OR->equalTo(
                        'responsable2Id', $responsableId);
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

    public function eleveDownloadAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        }
        $columns = [
            'Nom' => 'nom',
            'Prénom' => 'prenom',
            'R1 Identité' => 'responsable1NomPrenom',
            'R1 Adresse ligne 1' => 'adresseL1R1',
            'R1 Adresse ligne 2' => 'adresseL2R1',
            'R1 Adresse ligne 3' => 'adresseL3R1',
            'R1 Commune' => 'communeR1',
            'R1 Téléphone 1' => 'telephoneFR1',
            'R1 Téléphone 2' => 'telephonePR1',
            'R1 Téléphone 3' => 'telephoneTR1',
            'R2 Identité' => 'responsable2NomPrenom',
            'R2 Adresse ligne 1' => 'adresseL1R2',
            'R2 Adresse ligne 2' => 'adresseL2R2',
            'R2 Adresse ligne 3' => 'adresseL3R2',
            'R2 Commune' => 'communeR2',
            'R2 Téléphone 1' => 'telephoneFR2',
            'R2 Téléphone 2' => 'telephonePR2',
            'R2 Téléphone 3' => 'telephoneTR2',
            'Établissement' => 'etablissement',
            'Commune de l\'établissement' => 'communeEtablissement',
            'Classe' => 'classe'
        ];
        // index du tableau $columns correspondant à des n° de téléphones
        $aTelephoneIndexes = [];
        $idx = 0;
        foreach ($columns as $column_field) {
            if (substr($column_field, 0, 9) == 'telephone') {
                $aTelephoneIndexes[] = $idx;
            }
            $idx ++;
        }
        // reprise des critères
        $criteres = Session::get('post', [],
            str_replace('download', 'liste', $this->getSessionNamespace()));
        $criteres_form = new \SbmGestion\Form\Eleve\CriteresForm();
        $criteres_form->setValueOptions('etablissementId',
            $this->db_manager->get('Sbm\Db\Select\Etablissements')
                ->desservis())
            ->setValueOptions('classeId',
            $this->db_manager->get('Sbm\Db\Select\Classes')
                ->tout());
        $criteres_obj = new \SbmGestion\Model\Db\ObjectData\CriteresEleves(
            $criteres_form->getElementNames());
        $criteres_form->setData($criteres);
        if ($criteres_form->isValid()) {
            $criteres_obj->exchangeArray($criteres_form->getData());
        }
        // lancement de la requête et construction d'un tabeau des datas
        $data = [];
        foreach ($this->db_manager->get('Sbm\Db\Query\ElevesResponsables')->withScolaritesR2(
            $criteres_obj->getWhere(), [
                'nom',
                'prenom'
            ]) as $eleve) {
            $aEleve = $eleve->getArrayCopy();
            $ligne = [];
            foreach ($columns as $value) {
                $ligne[] = $aEleve[$value];
            }
            $data[] = $ligne;
        }
        // exportation en formatant les n° de téléphones pour qu'ils soient encadrés par
        // le caractère d'enclosure
        $viewhelper = new \SbmCommun\Model\View\Helper\Telephone();
        return $this->csvExport('eleves.csv', array_keys($columns), $data,
            function ($item) use ($aTelephoneIndexes, $viewhelper) {
                foreach ($aTelephoneIndexes as $idx) {
                    $item[$idx] = $viewhelper($item[$idx]);
                }
                return $item;
            });
    }

    public function eleveSupprAction()
    {
        $prg = $this->prg();
        $rayer = $supprimer = false;
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            $args = Session::get('post', false, $this->getSessionNamespace());
            if ($args === false) {
                $this->flashMessenger()->addErrorMessage('Action interdite.');
                return $this->redirect()->toRoute('sbmgestion/eleve',
                    [
                        'action' => 'eleve-liste',
                        'page' => $this->params('page', 1)
                    ]);
            }
        } else {
            $args = $prg;
            if (array_key_exists('cancel', $args) || ! array_key_exists('eleveId', $args)) {
                $this->flashMessenger()->addWarningMessage('Abandon de la suppression.');
                return $this->redirect()->toRoute('sbmgestion/eleve',
                    [
                        'action' => 'eleve-liste',
                        'page' => $this->params('page', 1)
                    ]);
            }
            $rayer = array_key_exists('rayer', $args);
            $supprimer = array_key_exists('confirmer', $args);
            unset($args['rayer'], $args['confirmer']);
            Session::set('post', $args, $this->getSessionNamespace());
        }
        $form = new Form\ButtonForm([
            'eleveId' => $args['eleveId']
        ],
            [
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
            $this->db_manager->get('Sbm\Db\Table\Scolarites')->setInscrit($millesime,
                $args['eleveId'], 0);
            $this->flashMessenger()->addSuccessMessage('L\'élève a été rayée.');
            return $this->redirect()->toRoute('sbmgestion/eleve',
                [
                    'action' => 'eleve-liste',
                    'page' => $this->params('page', 1)
                ]);
        } elseif ($supprimer) {
            $where = new Where();
            $where->equalTo('millesime', $millesime)->equalTo('eleveId', $args['eleveId']);
            $this->db_manager->get('Sbm\Db\Table\Affectations')->deleteRecord($where);
            $this->db_manager->get('Sbm\Db\Table\Scolarites')->deleteRecord($where);
            try {
                $this->db_manager->get('Sbm\Db\Table\Eleves')->deleteRecord(
                    $args['eleveId']);
            } catch (\Zend\Db\Adapter\Exception\InvalidQueryException $e) {
            }
            $this->flashMessenger()->addSuccessMessage('L\'inscription a été supprimée.');
            return $this->redirect()->toRoute('sbmgestion/eleve',
                [
                    'action' => 'eleve-liste',
                    'page' => $this->params('page', 1)
                ]);
        }
        return new ViewModel(
            [
                'form' => $form->prepare(),
                'page' => $this->params('page', 1),
                'eleve' => $this->db_manager->get('Sbm\Db\Query\ElevesScolarites')->getEleve(
                    $args['eleveId']),
                'affectations' => $this->db_manager->get(
                    'Sbm\Db\Query\AffectationsServicesStations')->getCorrespondances(
                    $args['eleveId'])
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
            } catch (RedirectToOrigineException $e) {
                $this->flashMessenger()->addErrorMessage('Action interdite');
                return $this->redirect()->toRoute('sbmgestion/eleve',
                    [
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
        } catch (RedirectToOrigineException $e) {
            $this->flashMessenger()->addErrorMessage('Action interdite');
            return $this->redirect()->toRoute('sbmgestion/eleve',
                [
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
            $args = Session::get('post', false);
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
                Session::set('post', $args);
            }
            if (array_key_exists('cancel', $args)) {
                $this->flashMessenger()->addWarningMessage(
                    'La localisation de cet élève n\'a pas été enregistrée.');
                try {
                    return $this->redirectToOrigin()->back();
                } catch (RedirectToOrigineException $e) {
                    return $this->redirect()->toRoute('sbmgestion/eleve',
                        [
                            'action' => 'eleve-edit'
                        ]);
                }
            }
        }
        // les outils de travail : formulaire et convertisseur de coordonnées
        // nécessaire pour valider lat et lng
        $configCarte = StdLib::getParam('gestion',
            $this->cartographie_manager->get('cartes'));
        // ici, il faut un formulaire permettant de saisir l'adresse particulière d'un
        // élève. Le
        // tout est enregistré dans scolarites
        $form = new FormEleve\LocalisationAdresse($configCarte['valide']);
        $form->setAttribute('action',
            $this->url()
                ->fromRoute('sbmgestion/eleve', [

                'action' => 'eleve-localisation'
            ]))
            ->setValueOptions('communeId',
            $this->db_manager->get('Sbm\Db\Select\Communes')
                ->desservies());
        $oDistanceMatrix = $this->cartographie_manager->get(
            GoogleMaps\DistanceMatrix::class);
        // chercher l'élève dans la table
        $eleve = $this->db_manager->get('Sbm\Db\Query\ElevesScolarites')->getEleve(
            $args['eleveId']);
        // traitement de la réponse
        if (array_key_exists('submit', $args)) {
            $form->setData($args);
            $pt = new Point($args['lng'], $args['lat'], 0, 'degré');
            if ($form->isValid()) {
                // détermine le point. Il est reçu en gRGF93 et sera enregistré en XYZ
                $point = $oDistanceMatrix->getProjection()->gRGF93versXYZ($pt);
                // enregistre les coordonnées dans la table
                $tableScolarites = $this->db_manager->get('Sbm\Db\Table\Scolarites');
                $oData = $tableScolarites->getObjData();
                $oData->exchangeArray($form->getData());
                $oData->millesime = Session::get('millesime');
                $oData->x = $point->getX();
                $oData->y = $point->getY();
                // calcul de la distance à l'établissement
                $tableEtablissements = $this->db_manager->get(
                    'Sbm\Db\Table\Etablissements');
                $etablissement = $tableEtablissements->getRecord(
                    $eleve['etablissementId']);
                $pointEtablissement = new Point($etablissement->x, $etablissement->y);
                $ptetab = $oDistanceMatrix->getProjection()->XYZversgRGF93(
                    $pointEtablissement);

                try {
                    $d = $oDistanceMatrix->calculDistance($pt, $ptetab);
                } catch (\Exception $e) {
                    $d = 99000;
                    $this->flashMessenger()->addWarningMessage(
                        "Google Maps API ne répond pas. Mettre à jour manuellement la distance entre le domicile et l'établissement.");
                }

                $oData->distanceR1 = round($d / 1000, 1);
                // enregistre
                $tableScolarites->saveRecord($oData);
                $this->flashMessenger()->addSuccessMessage(
                    'Cette adresse est enregistrée avec sa localisation.');
                try {
                    return $this->redirectToOrigin()->back();
                } catch (RedirectToOrigineException $e) {
                    return $this->redirect()->toRoute('sbmgestion/eleve',
                        [
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
            $ptetab = $oDistanceMatrix->getProjection()->XYZversgRGF93(
                $pointEtablissement);

            // recherche le responsable1 pour calculer la distance
            $eleveR1 = $this->db_manager->get('Sbm\Db\Query\ElevesResponsables')->getEleveResponsable1(
                $args['eleveId']);
            $point = new Point($eleveR1['x1'], $eleveR1['y1']);
            $pt = $oDistanceMatrix->getProjection()->XYZversgRGF93($point);
            try {
                $d = $oDistanceMatrix->calculDistance($pt, $ptetab);
            } catch (GoogleMaps\Exception\ExceptionNoAnswer $e) {
                $d = 99000;
                $this->flashMessenger()->addWarningMessage(
                    "Google Maps API ne répond pas. Mettre à jour mauellement la distance entre le domicile et l'établissement.");
            }

            // supprimer les références à l'adresse perso de l'élève
            $data = [
                'millesime' => Session::get('millesime'),
                'eleveId' => $args['eleveId'],
                'url_api' => $this->cartographie_manager->get('google_api_browser')['js'],
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
            } catch (RedirectToOrigineException $e) {
                return $this->redirect()->toRoute('sbmgestion/eleve',
                    [
                        'action' => 'eleve-edit'
                    ]);
            }
        } else {
            // préparer le Point dans le système gRGF93
            $point = new Point($eleve['x'], $eleve['y']);
            $pt = $oDistanceMatrix->getProjection()->XYZversgRGF93($point);
            $pt->setLatLngRange($configCarte['valide']['lat'],
                $configCarte['valide']['lng']);
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
        return new ViewModel(
            [
                'scheme' => $this->getRequest()
                    ->getUri()
                    ->getScheme(),
                'point' => $pt,
                'form' => $form->prepare(),
                'eleveId' => $args['eleveId'],
                'eleve' => $eleve,
                'url_api' => $this->cartographie_manager->get('google_api_browser')['js'],
                'config' => $configCarte
            ]);
    }

    /**
     * Marque sélectionnées les fiches des responsables des élèves filtrés dans la liste
     * des élèves en tenant compte des critères de sélection.
     */
    public function eleveResponsablesAction()
    {
        $request = $this->getRequest();
        if (! $request->isPost() || is_null($request->getPost('responsables'))) {
            return $this->redirect()->toRoute('login', [
                'action' => 'home'
            ]);
        }
        $criteres_form = new \SbmGestion\Form\Eleve\CriteresForm();
        // initialiser le form pour les select ...
        $criteres_form->setValueOptions('etablissementId',
            $this->db_manager->get('Sbm\Db\Select\Etablissements')
                ->desservis())
            ->setValueOptions('classeId',
            $this->db_manager->get('Sbm\Db\Select\Classes')
                ->tout());
        // créer un objectData qui contient la méthode getWhere() adhoc
        $criteres_obj = new \SbmGestion\Model\Db\ObjectData\CriteresEleves(
            $criteres_form->getElementNames());
        $criteres = Session::get('post', [],
            str_replace('responsables', 'liste', $this->getSessionNamespace()));
        if (! empty($criteres)) {
            $criteres_obj->exchangeArray($criteres);
            $criteres_form->setData($criteres_obj->getArrayCopy());
        }
        $tResponsables = $this->db_manager->get('Sbm\Db\Table\Responsables');
        // démarquer les responsables
        $tResponsables->clearSelection();
        // marquer les responsables
        foreach ($this->db_manager->get('Sbm\Db\Query\ElevesResponsables')->withScolaritesR2(
            $criteres_obj->getWhere(), [
                'nom',
                'prenom'
            ]) as $eleve) {
            $tResponsables->setSelection($eleve['responsable1Id'], 1);
            if (! empty($eleve['responsable2Id'])) {
                $tResponsables->setSelection($eleve['responsable2Id'], 1);
            }
        }
        // liste des responsables
        return $this->redirect()->toRoute('sbmgestion/eleve',
            [
                'action' => 'responsable-liste'
            ]);
    }

    /**
     * Liste des responsables Passer nbEnfants, nbInscrits et nbPreinscrits en strict
     * parce qu'on recherche l'égalité et que l'on veut pouvoir compter les "== 0" Ici, le
     * formulaire de critères utilise des alias de champs puisque certains champs doivent
     * être préfixés pour lever les ambiguités (voir requête 'Sbm\Db\Query\Responsables')
     * et d'autres sont des expressions.
     *
     * @return ViewModel
     */
    public function responsableListeAction()
    {
        $retour = false;
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            $this->sbm_isPost = false;
            $args = Session::get('post', [], $this->getSessionNamespace());
        } else {
            $args = $prg;
            $retour = StdLib::getParam('op', $args, '') == 'retour';
            if ($retour) {
                // dans ce cas, il s'agit du retour d'une action de type suppr, ajout ou
                // edit. Comme
                // pour un get, on récupère ce qui est en session.
                $this->sbm_isPost = false;
                $args = Session::get('post', [], $this->getSessionNamespace());
            } else {
                if (array_key_exists('cancel', $args)) {
                    try {
                        return $this->redirectToOrigin()->back();
                    } catch (\SbmCommun\Model\Mvc\Controller\Plugin\Exception\ExceptionInterface $e) {
                        return $args;
                    }
                } elseif (array_key_exists('origine', $args)) {
                    $this->redirectToOrigin()->setBack($args['origine']);
                    unset($args['origine']);
                }
                $this->sbm_isPost = true;
                unset($args['submit']);
                Session::set('post', $args, $this->getSessionNamespace());
            }
        }
        $projection = $this->cartographie_manager->get(Projection::class);
        $rangeX = $projection->getRangeX();
        $rangeY = $projection->getRangeY();
        $nonLocalise = 'Not((x Between %d And %d) And (y Between %d And %d))';
        // formulaire des critères de recherche
        $criteres_form = new \SbmCommun\Form\CriteresForm('responsables');
        // initialiser le form pour les select ...
        $criteres_form->get('demenagement')->setUseHiddenElement(false);

        $criteres_form->get('inscrits')->setUseHiddenElement(false);

        $criteres_form->get('preinscrits')->setUseHiddenElement(false);

        $criteres_form->get('localisation')->setUseHiddenElement(false);

        $criteres_form->get('selection')->setUseHiddenElement(false);
        // créer un objectData qui contient la méthode getWhere() adhoc
        $criteres_obj = new \SbmGestion\Model\Db\ObjectData\CriteresResponsables(
            $criteres_form->getElementNames());
        $criteres_obj->setSansLocalisationCondition(

            sprintf($nonLocalise, $rangeX['gestion'][0], $rangeX['gestion'][1],
                $rangeY['gestion'][0], $rangeY['gestion'][1]));
        if ($this->sbm_isPost) {
            $criteres_form->setData($args);
            if ($criteres_form->isValid()) {
                $criteres_obj->exchangeArray($criteres_form->getData());
            }
        }
        if (! $criteres_form->hasValidated() && ! empty($args)) {
            $criteres_obj->exchangeArray($args);
            $criteres_form->setData($criteres_obj->getArrayCopy());
        }
        return new ViewModel(
            [
                'paginator' => $this->db_manager->get('Sbm\Db\Query\Responsables')->paginatorResponsables(
                    $criteres_obj->getWhere(), [
                        'nom',
                        'prenom'
                    ]),
                'page' => $this->params('page', 1),
                'count_per_page' => $this->getPaginatorCountPerPage('nb_responsables', 10),
                'criteres_form' => $criteres_form,
                'projection' => $this->cartographie_manager->get(Projection::class),
                'oResponsable' => $this->db_manager->get('Sbm\Db\Table\Responsables')->getObjData()
            ]);
    }

    public function responsableAjoutAction()
    {
        $currentPage = $this->params('page', 1);
        $prg = $this->prg();
        if ($prg instanceof Response) {
            // transforme un post en une redirection 303 avec le contenu de post en
            // session
            // 'prg_post1' (Expire_Hops = 1)
            return $prg;
        } elseif ($prg === false) {
            // ce n'était pas un post. Cette entrée est illégale et conduit à un retour à
            // la liste
            return $this->redirect()->toRoute('sbmgestion/eleve',
                [
                    'action' => 'responsable-liste',
                    'page' => $currentPage
                ]);
        }
        // ici, on a eu un post qui a été transformé en rediretion 303. Les données du
        // post sont
        // dans $prg (à récupérer en un seul appel à cause de Expire_Hops)
        $args = $prg;
        // si $args contient la clé 'cancel' c'est un abandon de l'action
        if (array_key_exists('cancel', $args)) {
            $this->flashMessenger()->addWarningMessage(
                "L'enregistrement n'a pas été enregistré.");
            return $this->redirect()->toRoute('sbmgestion/eleve',
                [
                    'action' => 'responsable-liste',
                    'page' => $currentPage
                ]);
        }
        // on ouvre la table des responsables
        $responsableId = null;
        $tableResponsables = $this->db_manager->get('Sbm\Db\Table\Responsables');
        // on ouvre le formulaire et on l'adapte
        $form = $this->form_manager->get(Form\Responsable::class);
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
                if ($tableResponsables->saveRecord($oData)) {
                    // on s'assure de rendre cette commune visible
                    $this->db_manager->get('Sbm\Db\table\Communes')->setVisible(
                        $oData->communeId);
                }
                $this->flashMessenger()->addSuccessMessage("La fiche a été enregistrée.");
                /*
                 * return $this->redirect()->toRoute('sbmgestion/eleve', [ 'action' =>
                 * 'responsable-liste', 'page' => $currentPage ));
                 */
                $viewmodel = $this->responsableLocalisationAction(
                    $tableResponsables->getLastResponsableId(), $currentPage);
                $viewmodel->setTemplate(
                    'sbm-gestion/eleve/responsable-localisation.phtml');
                return $viewmodel;
            }
        }
        $view = new ViewModel(
            [
                'form' => $form->prepare(),
                'page' => $currentPage,
                'responsableId' => $responsableId,
                'demenagement' => false
            ]);
        $view->setTemplate('sbm-gestion/eleve/responsable-edit.phtml');
        return $view;
    }

    public function responsableEditAction()
    {
        // utilisation de PostRedirectGet par mesure de sécurité
        $prg = $this->prg();
        if ($prg instanceof Response) {
            // transforme un post en une redirection 303 avec le contenu de post en
            // session
            // 'prg_post1' (Expire_Hops = 1)
            return $prg;
        } elseif ($prg === false) {
            $args = Session::get('post', false);
            if ($args === false) {
                $this->flashMessenger()->addErrorMessage('Action interdite');
                return $this->redirect()->toRoute('login', [

                    'action' => 'logout'
                ]);
            }
        } else {
            $args = $prg;
            if (array_key_exists('cancel', $args)) {
                $this->flashMessenger()->addWarningMessage(
                    "L'enregistrement n'a pas été modifié.");
                try {
                    return $this->redirectToOrigin()->back();
                } catch (RedirectToOrigineException $e) {
                    return $this->redirect()->toRoute('sbmgestion/eleve',
                        [
                            'action' => 'responsable-liste',
                            'page' => $this->params('page', 1)
                        ]);
                }
            } elseif (array_key_exists('origine', $args)) {
                $this->redirectToOrigin()->setBack($args['origine']);
                unset($args['origine']);
            }
            Session::set('post', $args);
        }
        // on ouvre la table des données
        $responsableId = $args['responsableId'];
        $tableResponsables = $this->db_manager->get('Sbm\Db\Table\Responsables');
        // on ouvre le formulaire et on l'adapte
        $form = $this->form_manager->get(Form\Responsable::class);
        // validateur permettant de s'assurer que l'email proposé n'existe pas
        $validator = new \Zend\Validator\Db\NoRecordExists(
            [
                'table' => $this->db_manager->getCanonicName('responsables', 'table'),
                'adapter' => $this->db_manager->getDbAdapter(),
                'field' => 'email',
                'exclude' => [
                    'field' => 'responsableId',
                    'value' => $responsableId
                ]
            ]);
        $form->getInputFilter()
            ->get('email')
            ->getValidatorChain()
            ->attach($validator);
        // remplissage des listes des éléments select et longueurs maxi des inputs
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
                if ($tableResponsables->saveRecord($oData)) {
                    // on s'assure de rendre cette commune visible
                    $this->db_manager->get('Sbm\Db\table\Communes')->setVisible(
                        $oData->communeId);
                }
                // on synchronise l'email du compte user
                $this->updateUserCompte($this->db_manager, $oData->email,
                    $this->getSessionNamespace());
                $this->flashMessenger()->addSuccessMessage(
                    "Les modifications ont été enregistrées.");
                try {
                    return $this->redirectToOrigin()->back();
                } catch (RedirectToOrigineException $e) {
                    return $this->redirect()->toRoute('sbmgestion/eleve',
                        [
                            'action' => 'responsable-liste',
                            'page' => $this->params('page', 1)
                        ]);
                }
            }
            $demenagement = $args['demenagement'] ?: false;
            $identite = $args['titre'] . ' ' . $args['nom'] . ' ' . $args['prenom'];
            $smsOk = $tableResponsables->getObjData()
                ->exchangeArray($args)
                ->accepteSms();
        } else {
            $oData = $tableResponsables->getRecord($responsableId);
            $form->setData($oData->getArrayCopy());
            $this->hasUserCompte($this->db_manager, $oData->email,
                $this->getSessionNamespace());
            $demenagement = $oData->demenagement;
            $identite = $oData->titre . ' ' . $oData->nom . ' ' . $oData->prenom;
            $smsOk = $oData->accepteSms();
        }
        return new ViewModel(
            [
                'form' => $form->prepare(),
                'page' => $this->params('page', 1),
                'responsableId' => $responsableId,
                'identite' => $identite,
                'demenagement' => $demenagement,
                'accepte_sms' => $smsOk
            ]);
    }

    private function hasUserCompte($db_manager, $email, $sessionNameSpace)
    {
        Session::remove('user', $sessionNameSpace);
        if ($email) {
            $tUsers = $db_manager->get('Sbm\Db\Table\Users');
            try {
                $user = $tUsers->getRecordByEmail($email);
                Session::set('user', $user, $sessionNameSpace);
            } catch (\SbmCommun\Model\Db\Service\Table\Exception\ExceptionInterface $e) {
                Session::set('user', false, $sessionNameSpace);
            }
        } else {
            Session::set('user', false, $sessionNameSpace);
        }
    }

    private function updateUserCompte($db_manager, $email_new, $sessionNameSpace)
    {
        $user = Session::get('user', false, $this->getSessionNamespace());
        if ($user && $user->email != $email_new) {
            $tUsers = $db_manager->get('Sbm\Db\Table\Users');
            if ($email_new) {
                // met à jour l'email dans le compte user
                $oData = $tUsers->getObjData()
                    ->exchangeArray(
                    [
                        'userId' => $user->userId,
                        'token' => null,
                        'tokenalive' => 0,
                        'active' => 1,
                        'email' => $email_new,
                        'dateModification' => null,
                        'note' => null
                    ])
                    ->addNote('Email changé le ' . date('d/m/y') . ' par le gestionnaire')
                    ->completeToModif();
                $tUsers->saveRecord($oData);
            } elseif ($user->categorieId == 1) {
                // supprime le compte user
                $tUsers->deleteRecord($user->userId);
            }
        }
    }

    public function responsableGroupAction()
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
        $responsableId = StdLib::getParam('responsableId', $args, - 1);
        if ($responsableId == - 1) {
            $this->flashMessenger()->addErrorMessage('Action interdite.');
            return $this->redirect()->toRoute('sbmadmin',
                [
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
        $data['fnc_affectations'] = function ($eleveId) use ($controller, $responsableId) {
            return $controller->db_manager->get(
                'Sbm\Db\Query\AffectationsServicesStations')->getServices($eleveId,
                $responsableId);
        };
        $data['fnc_ga'] = function ($responsableId) use ($controller) {
            if (is_null($responsableId)) {
                return '';
            } else {
                $oresponsable = $controller->db_manager->get('Sbm\Db\Table\Responsables')->getRecord(
                    $responsableId);
                return sprintf('%s %s', $oresponsable->nomSA, $oresponsable->prenomSA);
            }
        };
        return new ViewModel(
            [
                'data' => $data,
                'responsable' => $this->db_manager->get('Sbm\Db\Vue\Responsables')->getRecord(
                    $responsableId),
                'page' => $currentPage,
                'responsableId' => $responsableId,
                'dateDebut' => $this->db_manager->get('Sbm\Db\System\Calendar')->getEtatDuSite()['dateDebut']->format(
                    'Y-m-d')
            ]);
    }

    public function responsableSupprAction()
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
                'alias' => 'Sbm\Db\Table\Responsables',
                'id' => 'responsableId'
            ],
            'form' => $form
        ];
        $vueResponsables = $this->db_manager->get('Sbm\Db\Vue\Responsables');
        try {
            $r = $this->supprData($this->db_manager, $params,
                function ($id, $tableResponsables) use ($vueResponsables) {
                    return [
                        'id' => $id,
                        'data' => $vueResponsables->getRecord($id)
                    ];
                });
        } catch (\Zend\Db\Adapter\Exception\InvalidQueryException $e) {
            $this->flashMessenger()->addWarningMessage(
                'Impossible de supprimer ce responsable car il a des enregistrements (élèves ou paiements) en liaison.');
            return $this->redirect()->toRoute('sbmgestion/eleve',
                [
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
                    return $this->redirect()->toRoute('sbmgestion/eleve',
                        [
                            'action' => 'responsable-liste',
                            'page' => $currentPage
                        ]);
                    break;
                default:
                    return new ViewModel(
                        [
                            'form' => $form->prepare(),
                            'page' => $currentPage,
                            'data' => StdLib::getParam('data', $r->getResult()),
                            'responsableId' => StdLib::getParam('id', $r->getResult()),
                            'data_dependantes' => $this->db_manager->get(
                                'Sbm\Db\Table\Eleves')->duResponsable(
                                StdLib::getParam('id', $r->getResult()))
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
                $args = Session::get('post', false);
                if ($args === false) {
                    $this->flashMessenger()->addErrorMessage('Action interdite');
                    return $this->redirect()->toRoute('login', [

                        'action' => 'logout'
                    ]);
                }
            } else {
                $args = $prg;

                // selon l'origine, l'url de retour porte le nom url1_retour (liste des
                // responsables) ou origine (liste des élèves, fiche d'un responsable)
                if (array_key_exists('url1_retour', $args)) {
                    $this->redirectToOrigin()->setBack($args['url1_retour']);
                    unset($args['url1_retour']);
                    Session::set('post', $args);
                } elseif (array_key_exists('origine', $args)) {
                    $this->redirectToOrigin()->setBack($args['origine']);
                    unset($args['origine']);
                    Session::set('post', $args);
                } elseif (array_key_exists('url1_retour', $args)) {
                    $this->redirectToOrigin()->setBack($args['url1_retour']);
                    unset($args['url1_retour']);
                    Session::set('post', $args);
                }
                if (array_key_exists('cancel', $args)) {
                    $this->flashMessenger()->addWarningMessage(
                        'La localisation de cette adresse n\'a pas été enregistrée.');
                    try {
                        return $this->redirectToOrigin()->back();
                    } catch (RedirectToOrigineException $e) {
                        return $this->redirect()->toRoute('sbmgestion/eleve',
                            [
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
        $configCarte = StdLib::getParam('gestion',
            $this->cartographie_manager->get('cartes'));
        $form = new Form\LatLng([

            'responsableId' => [
                'id' => 'responsableId'
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
                ->fromRoute('sbmgestion/eleve', [

                'action' => 'responsable-localisation'
            ]));

        // traitement de la réponse
        $oDistanceMatrix = $this->cartographie_manager->get(
            GoogleMaps\DistanceMatrix::class);
        if (array_key_exists('submit', $args)) {
            $form->setData(
                [
                    'responsableId' => $args['responsableId'],
                    'lat' => $args['lat'],
                    'lng' => $args['lng']
                ]);
            if ($form->isValid()) {
                // détermine le point. Il est reçu en gRGF93 et sera enregistré en XYZ
                $pt = new Point($args['lng'], $args['lat'], 0, 'degré');
                $point = $oDistanceMatrix->getProjection()->gRGF93versXYZ($pt);
                // enregistre les coordonnées dans la table
                $tableResponsables = $this->db_manager->get('Sbm\Db\Table\Responsables');
                $oData = $tableResponsables->getObjData();
                $oData->exchangeArray(
                    [
                        'responsableId' => $args['responsableId'],
                        'x' => $point->getX(),
                        'y' => $point->getY()
                    ]);
                $tableResponsables->saveRecord($oData);
                $this->flashMessenger()->addSuccessMessage(
                    'La localisation de cette adresse est enregistrée.');
                $msg = $this->cartographie_manager->get('Sbm\MajDistances')->pour(
                    $args['responsableId']);
                if ($msg) {
                    $this->flashMessenger()->addWarningMessage($msg);
                }
                try {
                    return $this->redirectToOrigin()->back();
                } catch (RedirectToOrigineException $e) {
                    return $this->redirect()->toRoute('sbmgestion/eleve',
                        [
                            'action' => 'responsable-liste',
                            'page' => $currentPage
                        ]);
                }
            }
        }
        // chercher le responsable dans la table
        $responsable = $this->db_manager->get('Sbm\Db\Vue\Responsables')->getRecord(
            $args['responsableId']);
        // préparer le nom de la commune selon les règes de la méthode
        // GoogleMaps\Geocoder::geocode
        $sa = new \SbmCommun\Filter\SansAccent();
        $responsable->lacommune = $sa->filter($responsable->lacommune);
        // préparer le Point dans le système gRGF93
        $point = new Point($responsable->x, $responsable->y);
        $pt = $oDistanceMatrix->getProjection()->XYZversgRGF93($point);
        // charger le formulaire
        $form->setData(
            [
                'responsableId' => $args['responsableId'],
                'lat' => $pt->getLatitude(),
                'lng' => $pt->getLongitude()
            ]);
        if (! $form->isValid()) {
            // essaie de positionner la marker à partir de l'adresse (L1 ou L2 ou L3)
            $array = $this->cartographie_manager->get(GoogleMaps\Geocoder::class)->geocode(
                $responsable->adresseL1, $responsable->codePostal, $responsable->lacommune);
            $pt = new Point($array['lng'], $array['lat'], 0, 'degré');
            $pt->setLatLngRange($configCarte['valide']['lat'],
                $configCarte['valide']['lng']);
            if (! $pt->isValid() && ! empty($responsable->adresseL2)) {
                $array = $this->cartographie_manager->get(GoogleMaps\Geocoder::class)->geocode(
                    $responsable->adresseL2, $responsable->codePostal,
                    $responsable->lacommune);
                $pt->setLatitude($array['lat']);
                $pt->setLongitude($array['lng']);
                if (! $pt->isValid() && ! empty($responsable->adresseL3)) {
                    $array = $this->cartographie_manager->get(GoogleMaps\Geocoder::class)->geocode(
                        $responsable->adresseL3, $responsable->codePostal,
                        $responsable->lacommune);
                    $pt->setLatitude($array['lat']);
                    $pt->setLongitude($array['lng']);
                    if (! $pt->isValid()) {
                        $pt->setLatitude($configCarte['centre']['lat']);
                        $pt->setLongitude($configCarte['centre']['lng']);
                    }
                }
            }
            $form->setData(
                [
                    'responsableId' => $args['responsableId'],
                    'lat' => $pt->getLatitude(),
                    'lng' => $pt->getLongitude()
                ]);
            $form->isValid();
        }
        return new ViewModel(
            [
                'scheme' => $this->getRequest()
                    ->getUri()
                    ->getScheme(),
                'form' => $form->prepare(),
                'responsable' => $responsable,
                'url_api' => $this->cartographie_manager->get('google_api_browser')['js'],
                'config' => $configCarte
            ]);
    }

    public function responsablePdfAction()
    {
        $projection = $this->cartographie_manager->get(Projection::class);
        $rangeX = $projection->getRangeX();
        $rangeY = $projection->getRangeY();
        $nonLocalise = 'Not((x Between %d And %d) And (y Between %d And %d))';
        $criteresObject = [
            [
                '\SbmGestion\Model\Db\ObjectData\CriteresResponsables',
                'setSansLocalisationCondition',
                sprintf($nonLocalise, $rangeX['gestion'][0], $rangeX['gestion'][1],
                    $rangeY['gestion'][0], $rangeY['gestion'][1])
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

                $where->equalTo('responsableId', $args['responsableId'])
                    ->nest()
                    ->isNull('millesime')->or->literal('millesime = maxmillesime')->unnest();

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
     * Envoie un mail à un responsable. Reçoit en post les paramètres 'responsable',
     * 'email', 'group' où group est l'url de retour
     *
     * @return \Zend\Http\PhpEnvironment\Response|\Zend\Http\Response|\Zend\View\Model\ViewModel
     */
    public function responsableMailAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            $destinataire = Session::get('destinataire', [], $this->getSessionNamespace());
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
                    'alias' => StdLib::getParam('responsable', $args)
                ];
                Session::set('destinataire', $destinataire, $this->getSessionNamespace());
                unset($args['email'], $args['responsable']);
            } elseif (array_key_exists('ecrirer1', $args) &&
                array_key_exists('emailr1', $args)) {
                $destinataire = [
                    'email' => $args['emailr1'],
                    'alias' => StdLib::getParam('responsabler1', $args)
                ];
                Session::set('destinataire', $destinataire, $this->getSessionNamespace());
                unset($args['emailr1'], $args['responsabler1']);
            } elseif (array_key_exists('ecrirer2', $args) &&
                array_key_exists('emailr2', $args)) {
                $destinataire = [
                    'email' => $args['emailr2'],
                    'alias' => StdLib::getParam('responsabler2', $args)
                ];
                Session::set('destinataire', $destinataire, $this->getSessionNamespace());
                unset($args['emailr2'], $args['responsabler2']);
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
                $to = $destinataire['alias'] ?: $destinataire['email'];
                $params = [
                    'to' => [
                        [
                            'email' => $destinataire['email'],
                            'name' => $to
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
                $message = sprintf('Le message a été envoyé à %s. Vous êtes en copie.',
                    $to);
                $this->flashMessenger()->addInfoMessage($message);
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
            return $this->redirect()->toRoute('sbmgestion/eleve',
                [
                    'action' => 'responsable-liste',
                    'page' => $this->params('page', 1)
                ]);
        } else {
            $args = $prg;
            if (array_key_exists('logernon', $args) ||
                ! array_key_exists('responsableId', $args)) {
                return $this->redirect()->toRoute('sbmgestion/eleve',
                    [
                        'action' => 'responsable-liste',
                        'page' => $this->params('page', 1)
                    ]);
            }
        }
        $tUsers = $this->db_manager->get('Sbm\Db\Table\Users');
        $responsable = $this->db_manager->get('Sbm\Db\Table\Responsables')->getRecord(
            $args['responsableId']);
        $email = $responsable->email;
        if (empty($email)) {
            $msg = 'Pour créer un compte il faut que ce responsable ait une adresse email.';
            $form = null;
        } else {
            try {
                $tUsers->getRecordByEmail($responsable->email);
                $msg = 'Ce responsable a déjà un compte.';
                $form = null;
            } catch (\SbmCommun\Model\Db\Service\Table\Exception\ExceptionInterface $e) {
                $msg = '';
                $form = new Form\ButtonForm([
                    'responsableId' => null
                ],
                    [

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
                        $mailTemplate = new MailTemplate('ouverture-compte', 'layout',
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
                                    'email' => $odata->email,
                                    'name' => $odata->nom . ' ' . $odata->prenom
                                ]
                            ],
                            'subject' => 'Ouverture d\'un compte',
                            'body' => [
                                'html' => $mailTemplate->render(
                                    [
                                        'titre' => $odata->titre,
                                        'nom' => $odata->nom,
                                        'prenom' => $odata->prenom,
                                        'url_confirme' => $this->url()
                                            ->fromRoute('login',
                                            [
                                                'action' => 'confirm',
                                                'id' => $odata->token
                                            ], [

                                                'force_canonical' => true
                                            ]),
                                        'client' => $this->client
                                    ])
                            ]
                        ];
                        $this->getEventManager()->addIdentifiers('SbmMail\Send');
                        $this->getEventManager()->trigger('sendMail', null, $params);
                        $this->flashMessenger()->addInfoMessage(
                            'Un mail a été envoyé à l\'adresse indiquée pour donner les instructions d\'accès.');

                        return $this->redirect()->toRoute('sbmgestion/eleve',
                            [
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
        return new ViewModel(
            [
                'form' => is_null($form) ? null : $form->prepare(),
                'info' => $args['info'],
                'msg' => $msg,
                'page' => $this->params('page', 1)
            ]);
    }

    /**
     * GESTION DES PHOTOS Cette méthode affiche le formulaire. Le traitement se fait en
     * AJAX pour - afficher un progressbar - afficher la photo après succès pendant 3
     * secondes - afficher un message pendant 3 secondes si il y a une erreur - quitter si
     * on clique sur le bouton Abandonner La méthode envoiphotoAction() doit récupérer les
     * paramètres POST suivants : - eleveId - info : nom prénom de l'élève (optionnel) -
     * origine ou group : au choix, adresse de retour après envoi (succès ou échec) Les
     * autres paramètres POST ne servent pas : - op : optionnel, vide, ne sert pas - email
     * : optionnel, ne sert pas - responsable : optionnel, ne sert pas Elle dispose en GET
     * de : - page : pas utile puisque c'est déjà dans origine ou dans group - id :
     * optionnel, pas utile non plus
     */
    public function envoiphotoAction()
    {
        $request = $this->getRequest();
        $eleveId = $request->getPost('eleveId');
        if (! $eleveId) {
            $this->flashMessenger()->addErrorMessage('Pas d\'identifiant pour l\'élève.');
            $this->redirect()->toRoute('sbmparent');
        }
        $ophoto = new \SbmCommun\Model\Photo\Photo();
        $form = $ophoto->getForm()->setData([
            'eleveId' => $eleveId
        ]);
        return new ViewModel(
            [
                'formphoto' => $form->prepare(),
                'info' => $request->getPost('info', ''),
                'url_retour' => $request->getPost('group') ?: $request->getPost('origine')
            ]);
    }

    /**
     * Supprime la sélection de toutes les fiches responsables
     */
    public function responsableSelectionAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        }
        $args = $prg ?: [];
        if (array_key_exists('cancel', $args)) {
            return $this->redirect()->toRoute('sbmgestion/eleve',
                [
                    'action' => 'responsable-liste',
                    'page' => $this->params('page', 1)
                ]);
        }
        $form = new Form\ButtonForm([],
            [
                'confirmer' => [
                    'class' => 'confirm',
                    'value' => 'Confirmer',
                    'title' => 'Désélectionner toutes les fiches responsables.'
                ],
                'cancel' => [
                    'class' => 'confirm',
                    'value' => 'Abandonner'
                ]
            ], 'Confirmation', true);
        $tresponsables = $this->db_manager->get('Sbm\Db\Table\Responsables');
        if (array_key_exists('confirmer', $args)) {
            $form->setData($args);
            if ($form->isValid()) {
                $tresponsables->clearSelection();
                $this->flashMessenger()->addSuccessMessage(
                    'Toutes les fiches sont désélectionnées.');
                return $this->redirect()->toRoute('sbmgestion/eleve',
                    [
                        'action' => 'responsable-liste',
                        'page' => $this->params('page', 1)
                    ]);
            }
        }
        $where = new Where();
        $where->equalTo('selection', 1);
        return new ViewModel(
            [
                'form' => $form,
                'nbSelection' => $tresponsables->fetchAll($where)->count()
            ]);
    }
}
