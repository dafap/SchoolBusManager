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
use Zend\Db\Sql\Where;
use SbmCommun\Model\Mvc\Controller\AbstractActionController;
use SbmCommun\Form\ButtonForm;
use SbmCommun\Form\Paiement as FormPaiement;
use SbmCommun\Form\Tarif as FormTarif;
use SbmCommun\Form\CriteresForm;
use SbmCommun\Model\Db\ObjectData\Criteres as ObjectDataCriteres;
use SbmCommun\Model\Db\Service\Table\AbstractSbmTable;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Http\PhpEnvironment\Response;
use Zend\View\View;
use SbmGestion\Form\FinancePaiementSuppr;

class FinanceController extends AbstractActionController
{

    /**
     * Menu de gestion financière
     *
     * (non-PHPdoc)
     *
     * @see \Zend\Mvc\Controller\AbstractActionController::indexAction()
     */
    public function indexAction()
    {
        return new ViewModel();
    }

    public function paiementListeAction()
    {
        /*
         * On commence par un PostRedirectGet pour régler les passages de paramètres provenant de $_POST ou d'une redirection.
         * En effet, lorsqu'on lance une redirection pour revenir sur dans la liste après une action (ajouter, supprimer, modifier)
         * les paramètres ne peuvent être passés que dans la route. C'est pas bien commode puisqu'ils sont alors vu dans la barre d'adresse.
         * Pour éviter cela, on passe les paramètres pas methode post mais la redirection ne le permet pas. On utilise alors le prg.
         * Cela règle en même temps le problème du F5 sur une page contenant un formulaire (voulez-vous renvoyer les données du formulaire ?).
         */
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
            $is_post = true;
            $args = $prg;
            $this->setToSession('post', $args, $this->getSessionNamespace());
        }
        // ici, $args contient ce qu'il y avait dans $_POST ou dans un $_POST précédemment mis en session.
        
        // la page vient de la route (comaptibilité du paginateur)
        $currentPage = $this->params('page', 1);
        // le reste vient de $args
        $responsableId = array_key_exists('responsableId', $args) ? $args['responsableId'] : - 1;
        $url1_retour = array_key_exists('url1_retour', $args) ? $args['url1_retour'] : $this->url()->fromRoute('sbmgestion/finance');
        $url2_retour = array_key_exists('url2_retour', $args) ? $args['url2_retour'] : null;
        $op = array_key_exists('op', $args) ? $args['op'] : '';
        if ($retour_n2 = ($op == 'retour')) {
            // le résultat du test est utilisé plus loin sous le nom de $retour_n2 (retour de niveau 2)
            $responsableId = - 1;
            $url2_retour = null;
        }
        // ouvrir la vue Sql
        $tablePaiements = $this->getServiceLocator()->get('Sbm\Db\Vue\Paiements');
        $order = 'datePaiement DESC';
        // configuration du paginator
        $config = $this->getServiceLocator()->get('Config');
        $nb_paiement_pagination = $config['liste']['paginator']['nb_paiement_pagination'];
        
        if ($responsableId == - 1) {
            // pas de $responsableId - gestion de tous les paiements
            $criteres_form = new CriteresForm('paiements');
            $value_options = $this->getServiceLocator()->get('Sbm\Libelles\Caisse');
            $criteres_form->setValueOptions('codeCaisse', $value_options);
            $value_options = $this->getServiceLocator()->get('Sbm\Libelles\ModeDePaiement');
            $criteres_form->setValueOptions('codeModeDePaiement', $value_options);
            $criteres_obj = new ObjectDataCriteres($criteres_form->getElementNames());
            // récupère les données du post pour les mettre en session si ce n'est pas un retour de niveau 2
            if (! $retour_n2 && $is_post) {
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
                'paginator' => $tablePaiements->paginator($criteres_obj->getWhere(), $order),
                'nb_paiement_pagination' => $nb_paiement_pagination,
                'criteres_form' => $criteres_form,
                'h2' => false,
                'responsable' => null,
                'page' => $currentPage,
                'responsableId' => $responsableId,
                'url1_retour' => $url1_retour,
                'url2_retour' => $url2_retour
            ));
        } else {
            // gestion des paiements du $responsableId.
            // L'appel peut provenir de la liste des responsables, de la fiche d'un responsable ou de la liste des paiements.
            // Ici, on ne présente pas le formulaire de cirtères (pas nécessaire)
            $tableResponsables = $this->getServiceLocator()->get('Sbm\Db\Table\Responsables');
            $where = new Where();
            $where->expression('responsableId = ?', $responsableId);
            
            return new ViewModel(array(
                'paginator' => $tablePaiements->paginator($where, $order),
                'nb_paiement_pagination' => $nb_paiement_pagination,
                'criteres_form' => null,
                'h2' => true,
                'responsable' => $tableResponsables->getNomPrenom($responsableId, true),
                'page' => $currentPage,
                'responsableId' => $responsableId,
                'url1_retour' => $url1_retour,
                'url2_retour' => $url2_retour
            ));
        }
    }

    public function paiementAjoutAction()
    {
        /*
         * Reçoit en post les données suivantes à utiliser pour le retour : h2, responsableId, responsable, url1_retour et url2_retour
         * Reçoit dans la route la page de la liste d'où l'on vient
         */
        $prg = $this->prg();
        if ($prg instanceof Response) {
            // transforme un post en une redirection 303 avec le contenu de post en session 'prg_post1' (Expire_Hops = 1)
            return $prg;
        } elseif ($prg === false) {
            // ce n'était pas un post. Cette entrée est illégale et conduit à un retour à la liste
            return $this->redirect()->toRoute('sbmgestion/finance', array(
                'action' => 'paiement-liste',
                'page' => $this->params('page', 1)
            ));
        }
        // ici, on a eu un post qui a été transformé en rediretion 303. Les données du post sont dans $prg (à récupérer en un seul appel à cause de Expire_Hops)
        $args = $prg;
        // si $args contient la clé 'cancel' c'est un abandon de l'action
        if (\array_key_exists('cancel', $args)) {
            $this->flashMessenger()->addWarningMessage("L'enregistrement n'a pas été modifié.");
            return $this->redirect()->toRoute('sbmgestion/finance', array(
                'action' => 'paiement-liste',
                'page' => $this->params('page', 1)
            ));
        }
        // on ouvre la table des paiements
        $tablePaiements = $this->getServiceLocator()->get('Sbm\Db\Table\Paiements');
        $db = $this->getServiceLocator()->get('Sbm\Db\DbLib');
        // on détermine si le responsable est fixé ou s'il faudra le choisir
        if (\array_key_exists('h2', $args)) {
            $this->setToSession('responsable_attributes', array(
                'h2' => $args['h2'],
                'responsable' => $args['responsable']
            ), $this->getSessionNamespace());
        } else {
            $responsable_attributes = $this->getFromSession('responsable_attributes', array(), $this->getSessionNamespace());
            $args = \array_merge($args, $responsable_attributes);
        }
        if ($args['h2']) {
            $hidden_responsableId = true; // le responsable est fixé
        } else {
            $hidden_responsableId = false; // il faudra choisir le responsable
        }
        // on ouvre le formulaire, l'adapte et le lie à l'échange de données
        $form = new FormPaiement(array(
            'responsableId' => $hidden_responsableId,
            'note' => false
        ));
        $form->setAttribute('action', $this->url()
            ->fromRoute('sbmgestion/finance', array(
            'action' => 'paiement-ajout',
            'page' => $this->params('page', 1)
        )))
            ->setValueOptions('codeCaisse', $this->getServiceLocator()
            ->get('Sbm\Libelles\Caisse'))
            ->setValueOptions('codeModeDePaiement', $this->getServiceLocator()
            ->get('Sbm\Libelles\ModeDePaiement'))
            ->setMaxLength($db->getMaxLengthArray('paiements', 'table'));
        if (! $hidden_responsableId) {
            $form->setValueOptions('responsableId', $this->getServiceLocator()
                ->get('Sbm\Db\Select\Responsables'));
        }
        $form->bind($tablePaiements->getObjData());
        if (array_key_exists('submit', $args)) {
            $form->setData($args);
            if ($form->isValid()) {
                // sauvegarde après avoir validé les datas
                $tablePaiements->saveRecord($form->getData());
                $this->flashMessenger()->addSuccessMessage("Les modifications ont été enregistrées.");
                // retour à la liste
                return $this->redirect()->toRoute('sbmgestion/finance', array(
                    'action' => 'paiement-liste',
                    'page' => $this->params('page', 1)
                ));
            }
        } else {
            $millesime = $this->getFromSession('millesime');
            $as = $millesime . '-' . ($millesime + 1);
            $libelles = $this->getServiceLocator()->get('Sbm\Libelles');
            $init_form = array(
                'codeCaisse' => $libelles->getCode('caisse', 'régisseur'),
                'datePaiement' => date('Y-m-d H:i:s'),
                'exercice' => date('Y'),
                'anneeScolaire' => $as
            );
            if ($hidden_responsableId) {
                $init_form['responsableId'] = $args['responsableId'];
            }
            $form->setData($init_form);
        }
        return new ViewModel(array(
            'form' => $form,
            'page' => $this->params('page', 1),
            'paiementId' => null,
            'hidden_responsableId' => $hidden_responsableId,
            'responsable' => $args['responsable']
        ));
    }

    public function paiementEditAction()
    {
        /*
         * Reçoit en post les données suivantes à utiliser pour le retour : paiementId, h2, responsableId, url1_retour et url2_retour
         * (seul paiementId est utile ici - les autres sont présents en raison de la compatibilité du formulaire avec 'groupe')
         * Reçoit dans la route la page de la liste d'où l'on vient
         */
        $prg = $this->prg();
        if ($prg instanceof Response) {
            // transforme un post en une redirection 303 avec le contenu de post en session 'prg_post1' (Expire_Hops = 1)
            return $prg;
        } elseif ($prg === false) {
            // ce n'était pas un post. Cette entrée est illégale et conduit à un retour à la liste
            return $this->redirect()->toRoute('sbmgestion/finance', array(
                'action' => 'paiement-liste',
                'page' => $this->params('page', 1)
            ));
        }
        // Si on est la c'est qu'on a eu un post et que les données sont dans $prg (à récupérer en un seul appel à cause de Expire_Hops)
        // on les copie dans $args. Il y a paiementId, h2, responsableId, url1_retour et url2_retour
        // Il y a sans doute des choses inutiles mais c'est nécessaire pour groupe qui se traite autrement
        $args = $prg;
        // si $args contient la clé 'cancel' (ou si paiementId n'est pas défini) c'est un abandon de l'action
        if (array_key_exists('cancel', $args) || ! \array_key_exists('paiementId', $args)) {
            $this->flashMessenger()->addWarningMessage("L'enregistrement n'a pas été modifié.");
            return $this->redirect()->toRoute('sbmgestion/finance', array(
                'action' => 'paiement-liste',
                'page' => $this->params('page', 1)
            ));
        }
        // Si responsable est passé, on le met en session afin de le retrouver si nécessaire (cas d'un formulaire non validé)
        if (\array_key_exists('responsable', $args)) {
            $this->setToSession('responsable', $args['responsable'], $this->getSessionNamespace());
        }
        $paiementId = $args['paiementId'];
        // on ouvre la table des données
        $tablePaiements = $this->getServiceLocator()->get('Sbm\Db\Table\Paiements');
        $db = $this->getServiceLocator()->get('Sbm\Db\DbLib');
        // on ouvre le formulaire, l'adapte et le lie à l'échange de données
        $hidden_responsableId = false; // mettre true pour obtenir en hidden ; mettre false pour obtenir un select
        $form = new FormPaiement(array(
            'responsableId' => $hidden_responsableId,
            'note' => true
        ));
        $form->setAttribute('action', $this->url()
            ->fromRoute('sbmgestion/finance', array(
            'action' => 'paiement-edit',
            'page' => $this->params('page', 1)
        )))
            ->setValueOptions('codeCaisse', $this->getServiceLocator()
            ->get('Sbm\Libelles\Caisse'))
            ->setValueOptions('codeModeDePaiement', $this->getServiceLocator()
            ->get('Sbm\Libelles\ModeDePaiement'))
            ->setMaxLength($db->getMaxLengthArray('paiements', 'table'));
        if (! $hidden_responsableId) {
            $form->setValueOptions('responsableId', $this->getServiceLocator()
                ->get('Sbm\Db\Select\Responsables'));
        }
        $form->bind($tablePaiements->getObjData());
        if (array_key_exists('submit', $args)) {
            $form->setData($args);
            if ($form->isValid()) {
                // sauvegarde après avoir validé les datas
                $tablePaiements->saveRecord($form->getData());
                $this->flashMessenger()->addSuccessMessage("Les modifications ont été enregistrées.");
                // retour à la liste
                return $this->redirect()->toRoute('sbmgestion/finance', array(
                    'action' => 'paiement-liste',
                    'page' => $this->params('page', 1)
                ));
            } else {
                $args['responsable'] = $this->getFromSession('responsable');
            }
        } else {
            $form->setData($tablePaiements->getRecord($paiementId)
                ->getArrayCopy());
        }
        return new ViewModel(array(
            'form' => $form,
            'page' => $this->params('page', 1),
            'paiementId' => $paiementId,
            'hidden_responsableId' => $hidden_responsableId,
            'responsable' => $args['responsable']
        ));
    }

    public function paiementSupprAction()
    {
        // attention, la suppression d'un paiement déposé chez le comptable doit être accompagnée d'un archivage. Mettre en place un trigger.
        // Si des élèves sont inscrits, la suppression provoquera une alerte.
        $prg = $this->prg();
        if ($prg instanceof Response) {
            // transforme un post en une redirection 303 avec le contenu de post en session 'prg_post1' (Expire_Hops = 1)
            return $prg;
        } elseif ($prg === false) {
            // ce n'était pas un post. Cette entrée est illégale et conduit à un retour à la liste
            return $this->redirect()->toRoute('sbmgestion/finance', array(
                'action' => 'paiement-liste',
                'page' => $this->params('page', 1)
            ));
        }
        $args = $prg;
        // si $args contient la clé 'cancel' (ou si paiementId n'est pas défini) c'est un abandon de l'action
        if (array_key_exists('cancel', $args) || ! array_key_exists('paiementId', $args)) {
            $this->flashMessenger()->addWarningMessage("L'enregistrement n'a pas été supprimé.");
            return $this->redirect()->toRoute('sbmgestion/finance', array(
                'action' => 'paiement-liste',
                'page' => $this->params('page', 1)
            ));
        }
        // Si responsable est passé, on le met en session afin de le retrouver si nécessaire (cas d'un formulaire non validé)
        if (\array_key_exists('responsable', $args)) {
            $this->setToSession('responsable', $args['responsable'], $this->getSessionNamespace());
        }
        
        $paiementId = $args['paiementId'];
        $form = new FinancePaiementSuppr();
        $form->setAttribute('action', $this->url()
            ->fromRoute('sbmgestion/finance', array(
            'action' => 'paiement-suppr',
            'page' => $this->params('page', 1)
        )));
        
        $tablePaiements = $this->getServiceLocator()->get('Sbm\Db\Table\Paiements');
        
        if (array_key_exists('submit', $args)) { // suppression confirmée
            $form->setData($args);
            if ($form->isValid()) {
                $data = $tablePaiements->getRecord($paiementId);
                $data->note = $args['note'];
                $tablePaiements->saveRecord($data);
                $tablePaiements->deleteRecord($paiementId);
                $this->flashMessenger()->addSuccessMessage("L'enregistrement a été supprimé.");
                return $this->redirect()->toRoute('sbmgestion/finance', array(
                    'action' => 'paiement-liste',
                    'page' => $this->params('page', 1)
                ));
            } else {
                $args['responsable'] = $this->getFromSession('responsable');
            }
        } else {
            $form->setData($tablePaiements->getRecord($paiementId)
                ->getArrayCopy());
        }
        return new ViewModel(array(
            'data' => $tablePaiements->getRecord($paiementId),
            'form' => $form,
            'page' => $this->params('page', 1),
            'paiementId' => $paiementId,
            'responsable' => $args['responsable'],
            'libelles' => $this->getServiceLocator()->get('Sbm\Libelles')
        ));
    }

    public function paiementPdfAction()
    {
        // ici, on présentera un écran avec la liste des états qui peuvent être tirés.
    }

    /**
     * Liste des tarifs
     * (avec pagination)
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function tarifListeAction()
    {
        $currentPage = $this->params('page', 1);
        $tableTarifs = $this->getServiceLocator()->get('Sbm\Db\Table\Tarifs');
        
        $criteres_form = new CriteresForm('tarifs');
        $criteres_form->setValueOptions('rythme', $tableTarifs->getRythmes());
        $criteres_form->setValueOptions('grille', $tableTarifs->getGrilles());
        $criteres_form->setValueOptions('mode', $tableTarifs->getModes());
        
        $criteres_obj = new ObjectDataCriteres($criteres_form->getElementNames());
        
        $config = $this->getServiceLocator()->get('Config');
        $nb_tarif_pagination = $config['liste']['paginator']['nb_tarif_pagination'];
        
        // récupère les données du post et met en session
        $session = new SessionContainer($this->getSessionNamespace());
        $request = $this->getRequest();
        if ($request->isPost()) {
            $criteres_form->setData($request->getPost());
            if ($criteres_form->isValid()) {
                $criteres_obj->exchangeArray($criteres_form->getData());
                $session->criteres = $criteres_obj->getArrayCopy();
            } else {
                $criteres_form->reset(); // nécessaire pour remettre en place les control, submit et cancel du formulaire qui peuvent être écrasés par le post
            }
        }
        // récupère les données de la session si le post n'a pas validé
        if (! $criteres_form->hasValidated() && isset($session->criteres)) {
            $criteres_obj->exchangeArray($session->criteres);
            $criteres_form->setData($criteres_obj->getArrayCopy());
        }
        
        return new ViewModel(array(
            'paginator' => $tableTarifs->paginator($criteres_obj->getWhere(), 'nom'),
            'page' => $currentPage,
            'nb_tarif_pagination' => $nb_tarif_pagination,
            'criteres_form' => $criteres_form
        ));
    }

    /**
     * Modification d'une fiche de tarif
     * (la validation porte sur un champ csrf)
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function tarifEditAction()
    {
        $currentPage = $this->params('page', 1);
        $tarifId = $this->params('id', - 1);
        if ($tarifId == - 1) {
            return $this->redirect()->toRoute('sbmgestion/finance', array(
                'action' => 'tarif-liste',
                'page' => $currentPage
            ));
        }
        $tableTarifs = $this->getServiceLocator()->get('Sbm\Db\Table\Tarifs');
        $db = $this->getServiceLocator()->get('Sbm\Db\DbLib');
        
        $form = new FormTarif();
        $form->setValueOptions('rythme', array_combine($tableTarifs->getRythmes(), $tableTarifs->getRythmes()));
        $form->setValueOptions('grille', array_combine($tableTarifs->getGrilles(), $tableTarifs->getGrilles()));
        $form->setValueOptions('mode', array_combine($tableTarifs->getModes(), $tableTarifs->getModes()));
        $form->setMaxLength($db->getMaxLengthArray('tarifs', 'table'));
        
        $form->bind($tableTarifs->getObjData());
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($request->getPost('cancel', false)) {
                $this->flashMessenger()->addWarningMessage("L'enregistrement n'a pas été modifié.");
                return $this->redirect()->toRoute('sbmgestion/finance', array(
                    'action' => 'tarif-liste',
                    'page' => $currentPage
                ));
            }
            $form->setData($request->getPost());
            if ($form->isValid()) { // controle le csrf
                $tableTarifs->saveRecord($form->getData());
                $this->flashMessenger()->addSuccessMessage("Les modifications ont été enregistrées.");
                return $this->redirect()->toRoute('sbmgestion/finance', array(
                    'action' => 'tarif-liste',
                    'page' => $currentPage
                ));
            }
        } else {
            $form->setData($tableTarifs->getRecord($tarifId)
                ->getArrayCopy());
        }
        return new ViewModel(array(
            'form' => $form,
            'page' => $currentPage,
            'tarifId' => $tarifId
        ));
    }

    /**
     * Suppression d'une fiche avec confirmation
     *
     * @todo : Vérifier qu'il n'y a pas d'élève inscrit avant de supprimer la fiche
     *      
     * @return \Zend\View\Model\ViewModel
     */
    public function tarifSupprAction()
    {
        $currentPage = $this->params('page', 1);
        $tarifId = $this->params('id', - 1); // GET
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
            'id' => $tarifId
        ));
        $tableTarifs = $this->getServiceLocator()->get('Sbm\Db\Table\Tarifs');
        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($request->getPost('supproui', false)) { // confirmation
                $tarifId = $this->params()->fromPost('id', false); // POST
                if ($tarifId) {
                    $tableTarifs->deleteRecord($tarifId);
                    $this->flashMessenger()->addSuccessMessage("L'enregistrement a été supprimé.");
                } else {
                    $this->flashMessenger()->addErrorMessage("Pas d'enregistrement à supprimer.");
                }
            } else { // abandon
                $this->flashMessenger()->addWarningMessage("L'enregistrement n'a pas été supprimé.");
            }
            return $this->redirect()->toRoute('sbmgestion/finance', array(
                'action' => 'tarif-liste',
                'page' => $currentPage
            ));
        } else {
            if ($tarifId) {
                $form->setData(array(
                    'id' => $tarifId
                ));
            } else {
                $this->flashMessenger()->addErrorMessage("Pas d'enregistrement à supprimer.");
                return $this->redirect()->toRoute('sbmgestion/finance', array(
                    'action' => 'tarif-liste',
                    'page' => $currentPage
                ));
            }
        }
        
        return new ViewModel(array(
            'data' => $tableTarifs->getRecord($tarifId),
            'form' => $form,
            'page' => $currentPage,
            'tarifId' => $tarifId
        ));
    }

    /**
     * Ajout d'une nouvelle fiche de tarif
     * (la validation porte sur un champ csrf)
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function tarifAjoutAction()
    {
        $currentPage = $this->params('page', 1);
        $tarifId = null;
        $tableTarifs = $this->getServiceLocator()->get('Sbm\Db\Table\Tarifs');
        $db = $this->getServiceLocator()->get('Sbm\Db\DbLib');
        
        $form = new FormTarif();
        $form->setValueOptions('rythme', array_combine($tableTarifs->getRythmes(), $tableTarifs->getRythmes()));
        $form->setValueOptions('grille', array_combine($tableTarifs->getGrilles(), $tableTarifs->getGrilles()));
        $form->setValueOptions('mode', array_combine($tableTarifs->getModes(), $tableTarifs->getModes()));
        $form->setMaxLength($db->getMaxLengthArray('tarifs', 'table'));
        
        $form->bind($tableTarifs->getObjData());
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($request->getPost('cancel', false)) {
                $this->flashMessenger()->addWarningMessage("L'enregistrement n'a pas été modifié.");
                return $this->redirect()->toRoute('sbmgestion/finance', array(
                    'action' => 'tarif-liste',
                    'page' => $currentPage
                ));
            }
            $form->setData($request->getPost());
            if ($form->isValid()) { // controle le csrf
                $tableTarifs->saveRecord($form->getData());
                $this->flashMessenger()->addSuccessMessage("Les modifications ont été enregistrées.");
                return $this->redirect()->toRoute('sbmgestion/finance', array(
                    'action' => 'tarif-liste',
                    'page' => $currentPage
                ));
            }
        }
        return new ViewModel(array(
            'form' => $form,
            'page' => $currentPage,
            'tarifId' => $tarifId
        ));
    }

    /**
     * renvoie la liste des élèves inscrits pour un tarif donné
     *
     * @todo : à faire
     *      
     * @return \Zend\View\Model\ViewModel
     */
    public function tarifGroupAction()
    {
        $currentPage = $this->params('page', 1);
        $tarifId = $this->params('id', - 1); // GET
        $tableTarifs = $this->getServiceLocator()->get('Sbm\Db\Table\Tarifs');
        return new ViewModel(array(
            'data' => $tableTarifs->getRecord($tarifId),
            // 'paginator' => $table_eleves->paginator(),
            'page' => $currentPage,
            'tarifId' => $tarifId
        ));
    }

    /**
     * envoie un evenement contenant les paramètres de création d'un document pdf
     * (le listener DafapTcpdf\Listener\PdfListener lancera la création du pdf)
     * Il n'y a pas de vue associée à cette action puisque la response html est créée par \TCPDF
     */
    public function tarifPdfAction()
    {
        $currentPage = $this->params('page', 1);
        
        $criteres_form = new CriteresForm('tarifs');
        $criteres_obj = new ObjectDataCriteres($criteres_form->getElementNames());
        $session = new SessionContainer(str_replace('pdf', 'liste', $this->getSessionNamespace()));
        if (isset($session->criteres)) {
            $criteres_obj->exchangeArray($session->criteres);
        }
        $call_pdf = $this->getServiceLocator()->get('RenderPdfService');
        $call_pdf->setParam('documentId', 6)
            ->setParam('recordSource', 'Sbm\Db\Table\Tarifs')
            ->setParam('where', $criteres_obj->getWhere())
            ->setParam('orderBy', array(
            'grille',
            'mode',
            'rythme',
            'nom'
        ))
            ->renderPdf();
        
        $this->flashMessenger()->addSuccessMessage("Création d'un pdf.");
    }
}