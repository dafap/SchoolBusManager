<?php
/**
 * Controller principal du module SbmGestion
 * Méthodes utilisées pour la gestion financière
 *
 * @project sbm
 * @package module/SbmGestion/src/SbmGestion/Controller
 * @filesource FinanceController.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 2 nov. 2015
 * @version 2015-1.6.5
 */
namespace SbmGestion\Controller;

use Zend\View\Model\ViewModel;
use Zend\Session\Container as SessionContainer;
use Zend\Db\Sql\Where;
use SbmCommun\Model\Mvc\Controller\AbstractActionController;
use SbmCommun\Form\ButtonForm;
use SbmCommun\Form\Organisme as FormOrganisme;
use SbmCommun\Form\Paiement as FormPaiement;
use SbmCommun\Form\Tarif as FormTarif;
use SbmCommun\Form\CriteresForm;
use SbmCommun\Model\Db\ObjectData\Criteres as ObjectDataCriteres;
use SbmCommun\Model\Db\Service\Table\AbstractSbmTable;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Http\PhpEnvironment\Response;
use Zend\View\View;
use SbmGestion\Form\FinancePaiementSuppr;
use SbmCommun\Form\SbmCommun\Form;
use SbmCommun\Model\StdLib;
use SbmCommun\Model\DateLib;
use DafapSession\Model\Session;

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
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        }
        $this->redirectToOrigin()->reset(); // on s'assure que la pile des retours est vide
        $millesime = Session::get('millesime');
        $tPaiements = $this->getServiceLocator()->get('Sbm\Db\Table\Paiements');
        $tLibelles = $this->getServiceLocator()->get('Sbm\Db\System\Libelles');
        $codeCheques = $tLibelles->getCode('ModeDePaiement', 'chèque');
        $codeEspeces = $tLibelles->getCode('ModeDePaiement', 'espèces');
        $codeCB = $tLibelles->getCode('ModeDePaiement', 'CB');
        $codeTitres = $tLibelles->getCode('ModeDePaiement', 'Titre individuel');
        $codeRegisseur = $tLibelles->getCode('Caisse', 'régisseur');
        $codeComptable = $tLibelles->getCode('Caisse', 'comptable');
        $codeDft = $tLibelles->getCode('Caisse', 'dft');
        return new ViewModel(array(
            'millesime' => $millesime,
            'dateBordereauCheques' => $tPaiements->dateDernierBordereau($codeCheques),
            'datePaiementCheques' => $tPaiements->dateDernierPaiement($codeCheques), // date('Y-m-d'),
            'dateBordereauEspeces' => $tPaiements->dateDernierBordereau($codeEspeces),
            'datePaiementEspeces' => $tPaiements->dateDernierPaiement($codeEspeces),
            'dateBordereauCB' => $tPaiements->dateDernierBordereau($codeCB),
            'datePaiementCB' => $tPaiements->dateDernierPaiement($codeCB),
            'encoursCheques' => $tPaiements->sommeBordereau($codeCheques),
            'encoursEspeces' => $tPaiements->sommeBordereau($codeEspeces),
            'encoursCB' => $tPaiements->sommeBordereau($codeCB),
            'encoursTotal' => $tPaiements->sommeBordereau(null),
            'asCheques' => $tPaiements->totalAnneeScolaire($millesime, $codeRegisseur, $codeCheques),
            'asEspeces' => $tPaiements->totalAnneeScolaire($millesime, $codeRegisseur, $codeEspeces),
            'asRegie' => $tPaiements->totalAnneeScolaire($millesime, $codeRegisseur),
            'asDft' => $tPaiements->totalAnneeScolaire($millesime, $codeDft),
            'asComptable' => $tPaiements->totalAnneeScolaire($millesime, $codeComptable),
            'asTotal' => $tPaiements->totalAnneeScolaire($millesime),
            'montantCheques1' => $tPaiements->totalExercice($millesime, $codeRegisseur, $codeCheques),
            'montantEspeces1' => $tPaiements->totalExercice($millesime, $codeRegisseur, $codeEspeces),
            'totaRegie1' => $tPaiements->totalExercice($millesime, $codeRegisseur),
            'totalDft1' => $tPaiements->totalExercice($millesime, $codeDft),
            'totalComptable1' => $tPaiements->totalExercice($millesime, $codeComptable),
            'total1' => $tPaiements->totalExercice($millesime),
            'montantCheques2' => $tPaiements->totalExercice($millesime + 1, $codeRegisseur, $codeCheques),
            'montantEspeces2' => $tPaiements->totalExercice($millesime + 1, $codeRegisseur, $codeEspeces),
            'totaRegie2' => $tPaiements->totalExercice($millesime + 1, $codeRegisseur),
            'totalDft2' => $tPaiements->totalExercice($millesime + 1, $codeDft),
            'totalComptable2' => $tPaiements->totalExercice($millesime + 1, $codeComptable),
            'total2' => $tPaiements->totalExercice($millesime + 1),
            'titresAs' => $tPaiements->totalAnneeScolaire($millesime + 1, null, $codeTitres),
            'titresExercice1' => $tPaiements->totalExercice($millesime, null, $codeTitres),
            'titresExercice2' => $tPaiements->totalExercice($millesime + 1, null, $codeTitres)
        ));
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
            return $prg;
        } elseif ($prg === false) {
            // ce n'était pas un post.
            $is_post = false;
            // appel depuis finances, paginator, back, F5, redirect de sortie de paiementAjoutAction, paiementEditAction ou paiementSupprAction
            if ($this->params('id', '') == 'tous') {
                // appel depuis finances : pas de post, éventuellement des criteres
                $args = array();
                $this->removeInSession('post', $this->getSessionNamespace());
            } else {
                // F5, back ou un redirect de sortie : reprendre le contexte d'avant en session
                $args = $this->getFromSession('post', array(), $this->getSessionNamespace());
            }
        } else {
            // suite à un post,
            // l'appel provient du formulaire de criteres ou de la liste des responsables ou de la sortie d'un paiement-ajout ou d'un paiement-edit
            // séparer les criteres et le post en session
            $is_post = true;
            if (array_key_exists('op', $prg)) {
                // arrive de la liste des responsables
                $args = $this->getFromSession('post', array(), $this->getSessionNamespace());
                $args = array_merge($args, $prg);
            } else {
                // vient du formulaire des critères ou de la sortie d'un paiement-ajout ou d'un paiement-edit
                $args = $prg;
            }
            $this->setToSession('post', $args, $this->getSessionNamespace());
        }
        
        // la page vient de la route (compatibilité du paginateur)
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
        $nb_paiements = $config['liste']['paginator']['nb_paiements'];
        
        if ($responsableId == - 1) {
            // pas de $responsableId - gestion de tous les paiements
            $criteres_form = new CriteresForm('paiements');
            $value_options = $this->getServiceLocator()
                ->get('Sbm\Db\Select\Libelles')
                ->caisse();
            $criteres_form->setValueOptions('codeCaisse', $value_options);
            $value_options = $this->getServiceLocator()
                ->get('Sbm\Db\Select\Libelles')
                ->modeDePaiement();
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
                'nb_pagination' => $nb_paiements,
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
            // Ici, on ne présente pas le formulaire de critères (pas nécessaire)
            $millesime = Session::get('millesime');
            $as = sprintf('%d-%d', $millesime, $millesime + 1);
            $where = new Where();
            $where->equalTo('responsableId', $responsableId)->equalTo('anneeScolaire', $as);
            $totalPaye = $this->getServiceLocator()
                ->get('Sbm\Db\Table\Paiements')
                ->total($where);
            
            $where = new Where();
            $where->expression('responsableId = ?', $responsableId);
            /*
             * if (array_key_exists('nbPreinscrits', $args)) {
             * $nomPrenom = $this->getServiceLocator()
             * ->get('Sbm\Db\Table\Responsables')
             * ->getNomPrenom($responsableId, true);
             * $nbInscrits = $args['nbInscrits'];
             * $nbPreinscrits = $args['nbPreinscrits'];
             * $nbGratuits = $args['nbGratuits'];
             * $nbFa = $args['nbFa'];
             * } else {
             */
            $responsable = $this->getServiceLocator()
                ->get('Sbm\Db\Query\Responsables')
                ->withEffectifs($where, array(
                'responsableId'
            ))
                ->current();
            $nomPrenom = sprintf('%s %s %s', $responsable['titre'], $responsable['nom'], $responsable['prenom']);
            $nbInscrits = $responsable['nbInscrits'];
            $nbPreinscrits = $responsable['nbPreinscrits'];
            $nbGratuits = $responsable['nbGratuits'];
            $nbFa = $responsable['nbFa'];
            $nbDuplicata = $responsable['nbDuplicata'];
            // }
            
            return new ViewModel(array(
                'paginator' => $tablePaiements->paginator($where, $order),
                'nb_pagination' => $nb_paiements,
                'criteres_form' => null,
                'h2' => true,
                'responsable' => $nomPrenom,
                'totalPaye' => $totalPaye,
                'inscription' => $this->getServiceLocator()
                    ->get('Sbm\Db\Table\Tarifs')
                    ->getMontant('inscription'),
                'duplicata' => $this->getServiceLocator()
                    ->get('Sbm\Db\Table\Tarifs')
                    ->getMontant('duplicata'),
                'nbInscrits' => $nbInscrits,
                'nbPreinscrits' => $nbPreinscrits,
                'nbGratuits' => $nbGratuits,
                'nbFa' => $nbFa,
                'nbDuplicata' => $nbDuplicata,
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
            ->get('Sbm\Db\Select\Libelles')
            ->caisse())
            ->setValueOptions('codeModeDePaiement', $this->getServiceLocator()
            ->get('Sbm\Db\Select\Libelles')
            ->modeDePaiement())
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
                // validation des paiements dans les fiches scolarites
                if (! empty($args['eleveId'])) {
                    $tScolarites = $this->getServiceLocator()->get('Sbm\Db\Table\Scolarites');
                    $tScolarites->setPaiement($this->getFromSession('millesime'), $args['eleveId']);
                }
                // retour à la liste
                $this->flashMessenger()->addSuccessMessage("Les modifications ont été enregistrées.");
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
            'form' => $form->prepare(),
            'page' => $this->params('page', 1),
            'paiementId' => null,
            'hidden_responsableId' => $hidden_responsableId,
            'responsable' => $args['responsable']
        ));
    }

    /**
     * Reçoit en post les données suivantes à utiliser pour le retour : paiementId, h2, responsableId, responsable, url1_retour et url2_retour
     * (seuls paiementId et responsable sont utiles ici - les autres sont présents en raison de la compatibilité du formulaire avec 'groupe')
     * Reçoit dans la route la page de la liste d'où l'on vient
     *
     * @return \Zend\Http\PhpEnvironment\Response|\Zend\Http\Response|\Zend\View\Model\ViewModel
     */
    public function paiementEditAction()
    {
        $currentPage = $this->params('page', 1);
        $tableTarifs = $this->getServiceLocator()->get('Sbm\Db\Table\Tarifs');
        $hidden_responsableId = false; // mettre true pour obtenir en hidden ; mettre false pour obtenir un select
        $form = new FormPaiement(array(
            'responsableId' => $hidden_responsableId,
            'note' => true
        ));
        $form->setAttribute('action', $this->url()
            ->fromRoute('sbmgestion/finance', array(
            'action' => 'paiement-edit',
            'page' => $currentPage
        )))
            ->setValueOptions('codeCaisse', $this->getServiceLocator()
            ->get('Sbm\Db\Select\Libelles')
            ->caisse())
            ->setValueOptions('codeModeDePaiement', $this->getServiceLocator()
            ->get('Sbm\Db\Select\Libelles')
            ->modeDePaiement());
        if (! $hidden_responsableId) {
            $form->setValueOptions('responsableId', $this->getServiceLocator()
                ->get('Sbm\Db\Select\Responsables'));
        }
        $params = array(
            'data' => array(
                'table' => 'paiements',
                'type' => 'table',
                'alias' => 'Sbm\Db\Table\Paiements',
                'id' => 'paiementId'
            ),
            'form' => $form
        );
        $sessionNS = $this->getSessionNamespace();
        $r = $this->editData($params, function ($post) use($sessionNS) {
            if (array_key_exists('responsable', $post)) {
                $responsable = $post['responsable'];
                \DafapSession\Model\Session::set('responsable', $responsable, $sessionNS);
            } else {
                $responsable = \DafapSession\Model\Session::get('responsable', '', $sessionNS);
            }
            return array(
                'paiementId' => $post['paiementId'],
                'responsable' => $responsable,
                'h2' => isset($post['h2']) ? $post['h2'] : null
            );
        });
        if ($r instanceof Response) {
            return $r;
        } else {
            switch ($r->getStatus()) {
                case 'error':
                case 'warning':
                case 'success':
                    return $this->redirect()->toRoute('sbmgestion/finance', array(
                        'action' => 'paiement-liste',
                        'page' => $currentPage
                    ));
                    break;
                default:
                    return new ViewModel(array(
                        'form' => $form->prepare(),
                        'page' => $currentPage,
                        'paiementId' => StdLib::getParam('paiementId', $r->getResult()),
                        'responsable' => StdLib::getParam('responsable', $r->getResult()),
                        'h2' => StdLib::getParam('h2', $r->getResult()),
                        'hidden_responsableId' => $hidden_responsableId
                    ));
                    break;
            }
        }
    }

    /**
     * Cette fonction n'utilise pas la méthode générale supprData().
     *
     * @return \Zend\Http\PhpEnvironment\Response|\Zend\Http\Response|\Zend\View\Model\ViewModel
     */
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
            $this->flashMessenger()->addErrorMessage("Action interdite.");
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
            'form' => $form->prepare(),
            'page' => $this->params('page', 1),
            'paiementId' => $paiementId,
            'responsable' => $args['responsable'],
            'libelles' => $this->getServiceLocator()->get('Sbm\Libelles')
        ));
    }

    public function paiementDepotAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } else {
            $args = (array) $prg;
            $page = $this->params('page', 1);
            if (array_key_exists('cancel', $args)) {
                $this->flashMessenger()->addWarningMessage('Action abandonnée.');
                return $this->redirect()->toRoute('sbmgestion/finance', array(
                    'action' => 'paiement-liste',
                    'page' => $page
                ));
            }
        }
        $tPaiements = $this->getServiceLocator()->get('Sbm\Db\Table\Paiements');
        $sBordereaux = $this->getServiceLocator()->get('Sbm\Db\Select\Bordereaux');
        $bordereauxClotures = $sBordereaux->clotures();
        $bordereauxEnCours = $sBordereaux->encours();
        $nouveauxPossibles = $this->getServiceLocator()
            ->get('Sbm\Db\Select\Libelles')
            ->modeDePaiement();
        foreach ($bordereauxEnCours as $key => $libelle) {
            $aKey = $sBordereaux->decode($key);
            unset($nouveauxPossibles[$aKey['codeModeDePaiement']]);
        }
        
        $form1 = new \SbmGestion\Form\Finances\BordereauRemiseValeurChoix();
        $form1->setValueOptions('bordereau', $bordereauxEnCours);
        $editerSubmit = $form1->get('editer');
        $editerSubmit->setAttribute('formaction', $this->url()
            ->fromRoute('sbmgestion/finance', array(
            'action' => 'paiement-pdf',
            'page' => $page,
            'id' => 1
        )));
        
        $form2 = new \SbmGestion\Form\Finances\BordereauRemiseValeurCreer();
        $form2->setValueOptions('codeModeDePaiement', $nouveauxPossibles)->setValueOptions('codeCaisse', $this->getServiceLocator()
            ->get('Sbm\Db\Select\Libelles')
            ->caisse());
        
        $form3 = new \SbmGestion\Form\Finances\BordereauRemiseValeurChoix();
        $form3->setValueOptions('bordereau', $bordereauxClotures);
        $editerSubmit = $form3->get('editer');
        $editerSubmit->setAttribute('formaction', $this->url()
            ->fromRoute('sbmgestion/finance', array(
            'action' => 'paiement-pdf',
            'page' => $page,
            'id' => 3
        )));
        
        if (array_key_exists('preparer', $args)) {
            $form2->setData($args);
            if ($form2->isValid()) {
                $args = $form2->getData();
                if (empty($args['anneeScolaire'])) {
                    $args['anneeScolaire'] = null;
                }
                if (empty($args['exercice'])) {
                    $args['exercice'] = null;
                }
                $now = DateLib::nowToMysql();
                $n = $this->getServiceLocator()
                    ->get('Sbm\Db\Table\Paiements')
                    ->marqueBordereau($now, $args['codeModeDePaiement'], $args['codeCaisse'], $args['exercice'], $args['anneeScolaire']);
                $message = sprintf('Un bordereau daté du %s a été créé. Il contient %d enregistrements.', DateLib::formatDateTimeFromMysql($now), $n);
                $this->flashMessenger()->addSuccessMessage($message);
                return $this->redirect()->toRoute('sbmgestion/finance', array(
                    'action' => 'paiement-depot'
                ));
            }
        } elseif (array_key_exists('supprimer', $args)) {
            $form1->setData($args);
            if ($form1->isValid()) {
                $args = $form1->getData();
                $aKey = $sBordereaux->decode($args['bordereau']);
                $n = $tPaiements->annuleBordereau($aKey['dateBordereau'], $aKey['codeModeDePaiement']);
                $format = "Le bordereau de %s a été supprimé. Il contient %d paiements qui sont à nouveau disponibles pour le prochain bordereau.";
                $message = sprintf($format, $bordereauxEnCours[$args['bordereau']], $n);
                $this->flashMessenger()->addSuccessMessage($message);
                return $this->redirect()->toRoute('sbmgestion/finance', array(
                    'action' => 'paiement-depot'
                ));
            }
        } elseif (array_key_exists('cloturer', $args)) {
            $form1->setData($args);
            if ($form1->isValid()) {
                $args = $form1->getData();
                $aKey = $sBordereaux->decode($args['bordereau']);
                $n = $tPaiements->clotureDepot($aKey['dateBordereau'], $aKey['codeModeDePaiement'], $this->getServiceLocator()
                    ->get('Sbm\Db\System\Libelles')
                    ->getCode('Caisse', 'comptable'));
                $format = "Le bordereau de %s a été clôturé. Les %d paiements qu'il contient sont maintenant dans la caisse du comptable.";
                $message = sprintf($format, $bordereauxEnCours[$args['bordereau']], $n);
                $this->flashMessenger()->addSuccessMessage($message);
                return $this->redirect()->toRoute('sbmgestion/finance', array(
                    'action' => 'paiement-depot'
                ));
            }
        } else {
            $form2->setData(array(
                'codeCaisse' => $this->getServiceLocator()
                    ->get('Sbm\Db\System\Libelles')
                    ->getCode('Caisse', 'régisseur')
            ));
        }
        if (substr($this->params('id', ''), - 5) == 'error') {
            if ($this->params('id', '1error') == '3error') {
                $form3->setData(array())->isValid();
            } else {
                $form1->setData(array())->isValid();
            }
        }
        return new ViewModel(array(
            'form1' => $form1, // bordereaux en cours
            'voirForm1' => ! empty($bordereauxEnCours),
            'form2' => $form2, // nouveau bordereau
            'voirForm2' => ! empty($nouveauxPossibles),
            'form3' => $form3, // bordereaux clôturés
            'voirForm3' => ! empty($bordereauxClotures)
        ));
    }

    /**
     * On reçoit en post les arguments suivants : url1_retour, url2_retour, h2, responsableId, responsable
     * - h2 vaut 1 (sinon, pas de bouton pour arriver à cette action)
     * - responsableId est un entier > 1
     * - responsable contient le titre, le nom et le prénom du responsable (sous forme d'une chaine)
     */
    public function paiementDetailAction()
    {
        $page = $this->params('page', 1);
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false || empty($prg['responsableId'])) {
            $args = $this->getFromSession('post', false, $this->getSessionNamespace());
            if ($args === false) {
                return $this->redirect()->toRoute('sbmgestion/finance', array(
                    'action' => 'paiement-liste',
                    'page' => $page
                ));
            }
        } else {
            $args = $prg;
            $this->setToSession('post', $args, $this->getSessionNamespace());
        }
        $responsableId = $args['responsableId'];
        $tEleves = $this->getServiceLocator()->get('Sbm\Db\Query\ElevesScolarites');
        $where = new Where();
        $millesime = Session::get('millesime');
        $as = sprintf('%d-%d', $millesime, $millesime + 1);
        $where->equalTo('anneeScolaire', $as)->equalTo('responsableId', $responsableId);
        $totalEncaisse = $this->getServiceLocator()
            ->get('Sbm\Db\Table\Paiements')
            ->total($where);
        // duplicatas
        $nbDuplicatas = $tEleves->getNbDuplicatas($responsableId);
        if ($nbDuplicatas) {
            $montantUnitaire = $this->getServiceLocator()
            ->get('Sbm\Db\Table\Tarifs')
            ->getMontant('duplicata');
            $montantDuplicatas = $nbDuplicatas * $montantUnitaire;
        } else {
            $montantDuplicatas = 0.00;
        }
        return new ViewModel(array(
            'liste' => $tEleves->getElevesPayantsWithMontant($responsableId),
            'args' => $args,
            'totalEncaisse' => $totalEncaisse,
            'montantDuplicatas' => $montantDuplicatas
        ));
    }

    /**
     * On arrive ici depuis la page de choix des impressions à réaliser (méthode paiementDepotAction).
     * Ici, le paramètre page correspond au numéro du formulaire et sert à mettre en place les bons valueOptions.
     * Le formulaire de choix n'est pas un ObjectData, aussi on n'utilise pas la méthode documentPdf du parent AbstactActionController.
     * Le where est construit dans la méthode.
     *
     * @return \Zend\Http\PhpEnvironment\Response|\Zend\Http\Response|Ambigous <\Zend\Http\PhpEnvironment\Response, \Zend\Http\Response>
     */
    public function paiementPdfAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        }
        $page = $this->params('page', 1);
        $id = $this->params('id', 1);
        $args = (array) $prg;
        if (! array_key_exists('editer', $args)) {
            $this->flashMessenger()->addWarningMessage('Action abandonnée.');
            return $this->redirect()->toRoute('sbmgestion/finance', array(
                'action' => 'paiement-depot',
                'page' => $page
            ));
        }
        $sBordereaux = $this->getServiceLocator()->get('Sbm\Db\Select\Bordereaux');
        $bordereauxClotures = $sBordereaux->clotures();
        $bordereauxEnCours = $sBordereaux->encours();
        $nouveauxPossibles = $this->getServiceLocator()
            ->get('Sbm\Db\Select\Libelles')
            ->modeDePaiement();
        foreach ($bordereauxEnCours as $key => $libelle) {
            $aKey = $sBordereaux->decode($key);
            unset($nouveauxPossibles[$aKey['codeModeDePaiement']]);
        }
        
        $form = new \SbmGestion\Form\Finances\BordereauRemiseValeurChoix();
        if ($id == 1) {
            $form->setValueOptions('bordereau', $bordereauxEnCours);
        } else {
            $form->setValueOptions('bordereau', $bordereauxClotures);
        }
        $form->setData($args);
        if ($form->isValid()) {
            $args = $form->getData();
            $call_pdf = $this->getServiceLocator()->get('RenderPdfService');
            $call_pdf->setParam('documentId', 'Bordereau de remise de valeurs');
            $aKey = $sBordereaux->decode($args['bordereau']); // tableau de la forme array('dateBordereau' => date, 'codeModeDePaiement' => code)
            $where = new Where();
            $where->equalTo('dateBordereau', $aKey['dateBordereau'])->equalTo('codeModeDePaiement', $aKey['codeModeDePaiement']);
            $call_pdf->setParam('where', $where);
            $this->flashMessenger()->addSuccessMessage("Création d'un pdf.");
            $view = false;
            $call_pdf->renderPdf();
            die();
        }
        // le formulaire ne valide pas. Il s'agit du select qui est vide.
        return $this->redirect()->toRoute('sbmgestion/finance', array(
            'action' => 'paiement-depot',
            'page' => $page,
            'id' => $id . 'error'
        ));
        
        $criteresObject = array(
            '\SbmCommun\Model\Db\ObjectData\Criteres',
            array(
                'expressions' => array(
                    'active' => 'Literal:active = 0'
                )
            )
        );
        $criteresForm = array(
            '\SbmCommun\Form\CriteresForm',
            'users'
        );
        $documentId = null;
        $retour = array(
            'route' => 'sbmadmin',
            'action' => 'user-liste'
        );
        return $this->documentPdf($criteresObject, $criteresForm, $documentId, $retour);
    }

    /**
     * Liste des tarifs
     * (avec pagination)
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function tarifListeAction()
    {
        $args = $this->initListe('tarifs', function ($sm, $form) {
            $table = $sm->get('Sbm\Db\Table\Tarifs');
            $form->setValueOptions('rythme', $table->getRythmes());
            $form->setValueOptions('grille', $table->getGrilles());
            $form->setValueOptions('mode', $table->getModes());
        });
        if ($args instanceof Response)
            return $args;
        return new ViewModel(array(
            'paginator' => $this->getServiceLocator()
                ->get('Sbm\Db\Table\Tarifs')
                ->paginator($args['where']),
            'page' => $this->params('page', 1),
            'nb_pagination' => $this->getNbPagination('nb_tarifs', 10),
            'criteres_form' => $args['form']
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
        $tableTarifs = $this->getServiceLocator()->get('Sbm\Db\Table\Tarifs');
        $form = new FormTarif();
        $form->setValueOptions('rythme', array_combine($tableTarifs->getRythmes(), $tableTarifs->getRythmes()))
            ->setValueOptions('grille', array_combine($tableTarifs->getGrilles(), $tableTarifs->getGrilles()))
            ->setValueOptions('mode', array_combine($tableTarifs->getModes(), $tableTarifs->getModes()));
        $params = array(
            'data' => array(
                'table' => 'tarifs',
                'type' => 'table',
                'alias' => 'Sbm\Db\Table\Tarifs',
                'id' => 'tarifId'
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
                    return $this->redirect()->toRoute('sbmgestion/finance', array(
                        'action' => 'tarif-liste',
                        'page' => $currentPage
                    ));
                    break;
                default:
                    return new ViewModel(array(
                        'form' => $form->prepare(),
                        'page' => $currentPage,
                        'tarifId' => $r->getResult()
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
    public function tarifSupprAction()
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
                'alias' => 'Sbm\Db\Table\Tarifs',
                'id' => 'tarifId'
            ),
            'form' => $form
        );
        try {
            $r = $this->supprData($params, function ($id, $tableTarifs) {
                return array(
                    'id' => $id,
                    'data' => $tableTarifs->getRecord($id)
                );
            });
        } catch (\Zend\Db\Adapter\Exception\InvalidQueryException $e) {
            $this->flashMessenger()->addWarningMessage('Impossible de supprimer ce tarif parce qu\'il est affecté à certains élèves.');
            return $this->redirect()->toRoute('sbmgestion/finance', array(
                'action' => 'tarif-liste',
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
                    return $this->redirect()->toRoute('sbmgestion/finance', array(
                        'action' => 'tarif-liste',
                        'page' => $currentPage
                    ));
                    break;
                default:
                    return new ViewModel(array(
                        'form' => $form->prepare(),
                        'page' => $currentPage,
                        'data' => StdLib::getParam('data', $r->getResult()),
                        'tarifId' => StdLib::getParam('id', $r->getResult())
                    ));
                    break;
            }
        }
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
        $tableTarifs = $this->getServiceLocator()->get('Sbm\Db\Table\Tarifs');
        $form = new FormTarif();
        $form->setValueOptions('rythme', array_combine($tableTarifs->getRythmes(), $tableTarifs->getRythmes()))
            ->setValueOptions('grille', array_combine($tableTarifs->getGrilles(), $tableTarifs->getGrilles()))
            ->setValueOptions('mode', array_combine($tableTarifs->getModes(), $tableTarifs->getModes()));
        $params = array(
            'data' => array(
                'table' => 'tarifs',
                'type' => 'table',
                'alias' => 'Sbm\Db\Table\Tarifs'
            ),
            // 'id' => 'tarifId'
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
                return $this->redirect()->toRoute('sbmgestion/finance', array(
                    'action' => 'tarif-liste',
                    'page' => $currentPage
                ));
                break;
            default:
                return new ViewModel(array(
                    'form' => $form->prepare(),
                    'page' => $currentPage,
                    'tarifId' => null
                ));
                break;
        }
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
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            $args = $this->getFromSession('post', array(), $this->getSessionNamespace());
        } else {
            $args = $prg;
            $this->setToSession('post', $args, $this->getSessionNamespace());
        }
        $currentPage = $this->params('page', 1);
        $pageRetour = $this->params('id', - 1);
        if ($pageRetour == - 1) {
            $pageRetour = $this->getFromSession('pageRetour', 1, $this->getSessionNamespace());
        } else {
            $this->setToSession('pageRetour', $pageRetour, $this->getSessionNamespace());
        }
        $tarifId = StdLib::getParam('tarifId', $args, - 1);
        if ($tarifId == - 1) {
            $this->flashMessenger()->addErrorMessage('Action interdite.');
            return $this->redirect()->toRoute('sbmgestion/finance', array(
                'action' => 'tarif-liste',
                'page' => $pageRetour
            ));
        }
        return new ViewModel(array(
            'paginator' => $this->getServiceLocator()
                ->get('Sbm\Db\Eleve\Liste')
                ->paginator($this->getFromSession('millesime'), array('tarifId' => $tarifId), array(
                'nom',
                'prenom'
            )),
            'nb_pagination' => $this->getNbPagination('nb_eleves', 15),
            'tarif' => $this->getServiceLocator()
                ->get('Sbm\Db\Table\Tarifs')
                ->getRecord($tarifId),
            'page' => $currentPage,
            'pageRetour' => $pageRetour,
            'tarifId' => $tarifId
        ));
    }

    /**
     * envoie un evenement contenant les paramètres de création d'un document pdf
     * (le listener SbmPdf\Listener\PdfListener lancera la création du pdf)
     * Il n'y a pas de vue associée à cette action puisque la response html est créée par \TCPDF
     */
    public function tarifPdfAction()
    {
        $criteresObject = array(
            '\SbmCommun\Model\Db\ObjectData\Criteres',
            array(
                'strict' => array()
            )
        );
        $criteresForm = array(
            '\SbmCommun\Form\CriteresForm',
            'tarifs'
        );
        $documentId = null;
        $retour = array(
            'route' => 'sbmgestion/finance',
            'action' => 'tarif-liste'
        );
        return $this->documentPdf($criteresObject, $criteresForm, $documentId, $retour);
    }

    public function tarifGroupPdfAction()
    {
        $sm = $this->getServiceLocator();
        $criteresObject = array(
            'SbmCommun\Model\Db\ObjectData\Criteres',
            null,
            function ($where, $args) use($sm) {
                $tarifId = StdLib::getParam('tarifId', $args, - 1);
                $where = new Where();
                $where->equalTo('tarifId', $tarifId);
                return $where;
            }
        );
        $criteresForm = 'SbmCommun\Form\CriteresForm';
        $documentId = null;
        $retour = array(
            'route' => 'sbmgestion/transport',
            'action' => 'tarif-group'
        );
        return $this->documentPdf($criteresObject, $criteresForm, $documentId, $retour);
    }
    
    // Gestion des organismes payeurs
    public function organismeListeAction()
    {
        $args = $this->initListe('organismes');
        if ($args instanceof Response)
            return $args;
        
        return new ViewModel(array(
            'paginator' => $this->getServiceLocator()
                ->get('Sbm\Db\Vue\Organismes')
                ->paginator($args['where']),
            't_nb_inscrits' => $this->getServiceLocator()
                ->get('Sbm\Db\Eleve\Effectif')
                ->byOrganisme(),
            'page' => $this->params('page', 1),
            'nb_pagination' => $this->getNbPagination('nb_organismes', 15),
            'criteres_form' => $args['form']
        ));
    }

    public function organismeAjoutAction()
    {
        $currentPage = $this->params('page', 1);
        $form = new FormOrganisme();
        $form->setValueOptions('communeId', $this->getServiceLocator()
            ->get('Sbm\Db\Select\Communes')
            ->visibles());
        $params = array(
            'data' => array(
                'table' => 'organismes',
                'type' => 'table',
                'alias' => 'Sbm\Db\Table\Organismes'
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
                return $this->redirect()->toRoute('sbmgestion/finance', array(
                    'action' => 'organisme-liste',
                    'page' => $currentPage
                ));
                break;
            default:
                return new ViewModel(array(
                    'form' => $form->prepare(),
                    'page' => $currentPage,
                    'organismeId' => null
                ));
                break;
        }
    }

    public function organismeEditAction()
    {
        $currentPage = $this->params('page', 1);
        $form = new FormOrganisme();
        $form->setValueOptions('communeId', $this->getServiceLocator()
            ->get('Sbm\Db\Select\Communes')
            ->visibles());
        $params = array(
            'data' => array(
                'table' => 'organismes',
                'type' => 'table',
                'alias' => 'Sbm\Db\Table\Organismes',
                'id' => 'organismeId'
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
                    return $this->redirect()->toRoute('sbmgestion/finance', array(
                        'action' => 'organisme-liste',
                        'page' => $currentPage
                    ));
                    break;
                default:
                    return new ViewModel(array(
                        'form' => $form->prepare(),
                        'page' => $currentPage,
                        'organismeId' => $r->getResult()
                    ));
                    break;
            }
        }
    }

    public function organismeSupprAction()
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
                'alias' => 'Sbm\Db\Table\Organismes',
                'id' => 'organismeId'
            ),
            'form' => $form
        );
        $vueorganismes = $this->getServiceLocator()->get('Sbm\Db\Vue\Organismes');
        try {
            $r = $this->supprData($params, function ($id, $tableorganismes) use($vueorganismes) {
                return array(
                    'id' => $id,
                    'data' => $vueorganismes->getRecord($id)
                );
            });
        } catch (Exception $e) {
            $this->flashMessenger()->addWarningMessage('Impossible de supprimer cet organisme car il est utilisé.');
            return $this->redirect()->toRoute('sbmgestion/finance', array(
                'action' => 'organisme-liste',
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
                    return $this->redirect()->toRoute('sbmgestion/finance', array(
                        'action' => 'organisme-liste',
                        'page' => $currentPage
                    ));
                    break;
                default:
                    return new ViewModel(array(
                        'form' => $form->prepare(),
                        'page' => $currentPage,
                        'data' => StdLib::getParam('data', $r->getResult()),
                        'organismeId' => StdLib::getParam('id', $r->getResult())
                    ));
                    break;
            }
        }
    }

    public function organismeGroupAction()
    {
        $pageRetour = $this->params('id', - 1);
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
        if ($pageRetour == - 1) {
            $pageRetour = $this->getFromSession('pageRetour', 1, $this->getSessionNamespace());
        } else {
            $this->setToSession('pageRetour', $pageRetour, $this->getSessionNamespace());
        }
        $organismeId = StdLib::getParam('organismeId', $args, - 1);
        if ($organismeId == - 1) {
            $this->flashMessenger()->addErrorMessage('Action interdite.');
            return $this->redirect()->toRoute('sbmgestion/finance', array(
                'action' => 'organisme-liste',
                'page' => $pageRetour
            ));
        }
        return new ViewModel(array(
            'paginator' => $this->getServiceLocator()
                ->get('Sbm\Db\Eleve\Liste')
                //->paginatorByOrganisme($this->getFromSession('millesime'), $organismeId, array(
                ->paginator($this->getFromSession('millesime'), array('organismeId' => $organismeId), array(
                'nom',
                'prenom'
            )),
            'nb_pagination' => $this->getNbPagination('nb_eleves', 15),
            'organisme' => $this->getServiceLocator()
                ->get('Sbm\Db\Vue\Organismes')
                ->getRecord($organismeId),
            'page' => $currentPage,
            'pageRetour' => $pageRetour,
            'organismeId' => $organismeId
        ));
    }

    public function organismeGroupPdfAction()
    {
        $sm = $this->getServiceLocator();
        $criteresObject = array(
            'SbmCommun\Model\Db\ObjectData\Criteres',
            null,
            function ($where, $args) use($sm) {
                $organismeId = StdLib::getParam('organismeId', $args, - 1);
                $where = new Where();
                $where->equalTo('organismeId', $organismeId);
                return $where;
            }
        );
        $criteresForm = 'SbmCommun\Form\CriteresForm';
        $documentId = null;
        $retour = array(
            'route' => 'sbmgestion/transport',
            'action' => 'organisme-group'
        );
        return $this->documentPdf($criteresObject, $criteresForm, $documentId, $retour);
    }

    /**
     * envoie un evenement contenant les paramètres de création d'un document pdf
     * (le listener SbmPdf\Listener\PdfListener lancera la création du pdf)
     * Il n'y a pas de vue associée à cette action puisque la response html est créée par \TCPDF
     */
    public function organismePdfAction()
    {
        $criteresObject = array(
            '\SbmCommun\Model\Db\ObjectData\Criteres',
            array(
                'strict' => array()
            )
        );
        $criteresForm = array(
            '\SbmCommun\Form\CriteresForm',
            'tarifs'
        );
        $documentId = null;
        $retour = array(
            'route' => 'sbmgestion/finance',
            'action' => 'organisme-liste'
        );
        return $this->documentPdf($criteresObject, $criteresForm, $documentId, $retour);
    }
}