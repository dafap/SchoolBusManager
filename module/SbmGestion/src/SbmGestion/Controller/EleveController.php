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
use Zend\Http\PhpEnvironment\Response;

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
                return $this->redirect()->toRoute('sbmgestion/eleve', array(
                    'action' => 'eleve-liste',
                    'page' => $currentPage
                ));
            }
            $form->setData($request->getPost());
            if ($form->isValid()) { // controle le csrf
                $tableEleves->saveRecord($form->getData());
                $this->flashMessenger()->addSuccessMessage("Les modifications ont été enregistrées.");
                return $this->redirect()->toRoute('sbmgestion/eleve', array(
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
            return $this->redirect()->toRoute('sbmgestion/eleve', array(
                'action' => 'eleve-liste',
                'page' => $currentPage
            ));
        }
        $tableEleves = $this->getServiceLocator()->get('Sbm\Db\Table\Eleves');
        $db = $this->getServiceLocator()->get('Sbm\Db\DbLib');
        
        $respSelect = $this->getServiceLocator()->get('Sbm\Db\Select\Responsables');
        $form = new FormEleve();
        $form->setValueOptions('responsable1Id', $respSelect)
            ->setValueOptions('responsable2Id', $respSelect)
            ->setValueOptions('responsableFId', $respSelect)
            ->setMaxLength($db->getMaxLengthArray('eleves', 'table'))
            ->bind($tableEleves->getObjData());
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($request->getPost('cancel', false)) {
                $this->flashMessenger()->addWarningMessage("L'enregistrement n'a pas été modifié.");
                return $this->redirect()->toRoute('sbmgestion/eleve', array(
                    'action' => 'eleve-liste',
                    'page' => $currentPage
                ));
            }
            $data = $request->getPost();
            if (empty($data['responsableFId']))
                $data['responsableFId'] = $data['responsable1Id'];
            $form->setData($data);
            if ($form->isValid()) { // controle le csrf
                $tableEleves->saveRecord($form->getData());
                $this->flashMessenger()->addSuccessMessage("Les modifications ont été enregistrées.");
                return $this->redirect()->toRoute('sbmgestion/eleve', array(
                    'action' => 'eleve-liste',
                    'page' => $currentPage
                ));
            } else {
                var_dump($request->getPost());
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
            return $this->redirect()->toRoute('sbmgestion/eleve', array(
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
                return $this->redirect()->toRoute('sbmgestion/eleve', array(
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
        // utilisation de PostRedirectGet pour mesure de sécurité
        $prg = $this->prg();
        if ($prg instanceof Response) {
            // transforme un post en une redirection 303 avec le contenu de post en session 'prg_post1' (Expire_Hops = 1)
            return $prg;
        } elseif ($prg === false) {
            // ce n'était pas un post. Prendre les paramètres dans la route et éventuellement dans la session (cas du paginator)
            $is_post = false;
            $args = $this->getFromSession('post', array(), $this->getSessionNamespace());
        } else {
            // c'est le tableau qui correspond au post après redirection; on le met en session
            $args = $prg;
            $op = array_key_exists('op', $args) ? $args['op'] : '';
            if ($op == 'retour') {
                $is_post = false;
            } else {
                $is_post = true;
                $this->setToSession('post', $args, $this->getSessionNamespace());
            }
        }
        // ici, $args contient ce qu'il y avait dans $_POST ou dans un $_POST précédemment mis en session.
        // la page vient de la route (comaptibilité du paginateur)
        $currentPage = $this->params('page', 1);
        // la configuration du paginateur vient de module.config.php de ce module
        $config = $this->getServiceLocator()->get('Config');
        $nb_responsable_pagination = $config['liste']['paginator']['nb_responsable_pagination'];
        // ouvrir la vue Sql
        $table_responsables = $this->getServiceLocator()->get('Sbm\Db\Vue\Responsables');
        
        // formulaire des critères de recherche
        $criteres_form = new CriteresForm('responsables');
        $criteres_obj = new ObjectDataCriteres($criteres_form->getElementNames());
        
        if ($is_post) {
            $criteres_form->setData($args);
            if ($criteres_form->isValid()) {
                $criteres_obj->exchangeArray($criteres_form->getData());
                $this->setToSession('criteres', $criteres_obj->getArrayCopy());
            }
        }
        // récupère les données de la session si le post n'a pas été validé dans le formulaire (pas de post ou invalide)
        $criteres_data = $this->getFromSession('criteres');
        if (! $criteres_form->hasValidated() && ! empty($criteres_data)) {
            $criteres_obj->exchangeArray($criteres_data);
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
            $this->flashMessenger()->addWarningMessage("L'enregistrement n'a pas été modifié.");
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
        $value_options = $this->getServiceLocator()->get('Sbm\Db\Select\CommunesDesservies');
        $form->setValueOptions('communeId', $value_options)
            ->setValueOptions('ancienCommuneId', $value_options)
            ->setMaxLength($db->getMaxLengthArray('responsables', 'table'));
        unset($value_options);
        
        $form->bind($tableResponsables->getObjData());
        
        if (array_key_exists('submit', $args)) {
            $form->setData($args);
            if ($form->isValid()) {
                // controle le csrf et contrôle les datas
                $tableResponsables->saveRecord($form->getData());
                $this->flashMessenger()->addSuccessMessage("Les modifications ont été enregistrées.");
                return $this->redirect()->toRoute('sbmgestion/eleve', array(
                    'action' => 'responsable-liste',
                    'page' => $this->params('page', 1)
                ));
            }
        }
        return new ViewModel(array(
            'form' => $form,
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
            $this->flashMessenger()->addWarningMessage("L'enregistrement n'a pas été modifié.");
            return $this->redirect()->toRoute('sbmgestion/eleve', array(
                'action' => 'responsable-liste',
                'page' => $this->params('page', 1)
            ));
        }
        // on ouvre la table des données
        $responsableId = $args['responsableId'];
        $tableResponsables = $this->getServiceLocator()->get('Sbm\Db\Table\Responsables');
        $db = $this->getServiceLocator()->get('Sbm\Db\DbLib');
        // on ouvre le formulaire et on l'adapte
        $form = new FormResponsable();
        $value_options = $this->getServiceLocator()->get('Sbm\Db\Select\CommunesDesservies');
        $form->setValueOptions('communeId', $value_options)
            ->setValueOptions('ancienCommuneId', $value_options)
            ->setMaxLength($db->getMaxLengthArray('responsables', 'table'));
        unset($value_options);
        
        $form->bind($tableResponsables->getObjData());
        
        if (\array_key_exists('submit', $args)) {
            $form->setData($args);
            if ($form->isValid()) {
                // controle le csrf et contrôle les datas
                $tableResponsables->saveRecord($form->getData());
                $this->flashMessenger()->addSuccessMessage("Les modifications ont été enregistrées.");
                return $this->redirect()->toRoute('sbmgestion/eleve', array(
                    'action' => 'responsable-liste',
                    'page' => $this->params('page', 1)
                ));
            }
            $demenagement = $args['demenagement'] ?  : false;
        } else {
            $array_data = $tableResponsables->getRecord($responsableId)->getArrayCopy();
            $form->setData($array_data);
            $demenagement = $array_data['demenagement'];
        }
        return new ViewModel(array(
            'form' => $form,
            'page' => $this->params('page', 1),
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
        $call_pdf->setParam('documentId', 8)
            ->setParam('recordSource', 'Sbm\Db\Vue\Responsables')
            ->setParam('where', $criteres_obj->getWhere())
            ->setParam('orderBy', array(
            'nomSA',
            'prenomSA'
        ))
            ->renderPdf();
        
        $this->flashMessenger()->addSuccessMessage("Création d'un pdf.");
    }

    public function responsableSupprAction()
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
            $this->flashMessenger()->addWarningMessage("L'enregistrement n'a pas été modifié.");
            return $this->redirect()->toRoute('sbmgestion/eleve', array(
                'action' => 'responsable-liste',
                'page' => $this->params('page', 1)
            ));
        }
        // on ouvre la table des données
        $responsableId = $args['responsableId'];
        $vueResponsables = $this->getServiceLocator()->get('Sbm\Db\Vue\Responsables');
        $tableEleves = $this->getServiceLocator()->get('Sbm\Db\Table\Eleves'); // table en relation avec responsables
                                                                               // on crée le formulaire
        $form = new ButtonForm(array(
            'submit' => array(
                'class' => 'default submit',
                'value' => 'Confirmer'
            ),
            'cancel' => array(
                'class' => 'default cancel',
                'value' => 'Abandonner'
            )
        ), array(
            'responsableId' => $responsableId
        ));
        $form->setAttribute('action', $this->url()
            ->fromRoute('sbmgestion/eleve', array(
            'action' => 'responsable-suppr',
            'page' => $this->params('page', 1)
        )));
        
        if (array_key_exists('submit', $args)) {
            $form->setData($args);
            if ($form->isValid()) {
                $tableResponsables = $this->getServiceLocator()->get('Sbm\Db\Table\Responsables');
                $tableResponsables->deleteRecord($responsableId);
                $this->flashMessenger()->addSuccessMessage("L'enregistrement a été supprimé.");
                return $this->redirect()->toRoute('sbmgestion/eleve', array(
                    'action' => 'responsable-liste',
                    'page' => $this->params('page', 1)
                ));
            }
        } else {
            $form->setData(array(
                'responsableId' => $responsableId
            ));
        }
        
        return new ViewModel(array(
            'data' => $vueResponsables->getRecord($responsableId),
            'data_dependantes' => $tableEleves->duResponsable($responsableId),
            'form' => $form,
            'page' => $this->params('page', 1),
            'responsableId' => $responsableId
        ));
    }
}