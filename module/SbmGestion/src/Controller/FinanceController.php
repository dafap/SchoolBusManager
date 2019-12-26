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
 * @date 22 déc. 2019
 * @version 2019-2.5.4
 */
namespace SbmGestion\Controller;

use SbmBase\Model\DateLib;
use SbmBase\Model\Session;
use SbmBase\Model\StdLib;
use SbmCommun\Form;
use SbmCommun\Model\Db\ObjectData\Criteres as ObjectDataCriteres;
use SbmCommun\Model\Mvc\Controller\AbstractActionController;
use SbmGestion\Form\Finances;
use Zend\Db\Sql\Where;
use Zend\Http\PhpEnvironment\Response;
use Zend\View\Model\ViewModel;
use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\ArrayAdapter;

class FinanceController extends AbstractActionController
{

    /**
     * Menu de gestion financière (non-PHPdoc)
     *
     * @see \Zend\Mvc\Controller\AbstractActionController::indexAction()
     */
    public function indexAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        }
        $this->redirectToOrigin()->reset(); // on s'assure que la pile des retours est
                                            // vide
        $millesime = Session::get('millesime');
        $tPaiements = $this->db_manager->get('Sbm\Db\Table\Paiements');
        $tLibelles = $this->db_manager->get('Sbm\Db\System\Libelles');
        $codesCaisse = $tLibelles->getCodes('Caisse');
        $codesModeDePaiement = $tLibelles->getCodes('ModeDePaiement');
        $resultats = [
            'encoursTotal' => $tPaiements->sommeBordereau(null),
            1 => [
                'as' => $tPaiements->totalAnneeScolaire($millesime),
                'exercice1' => $tPaiements->totalExercice($millesime),
                'exercice2' => $tPaiements->totalExercice($millesime + 1)
            ]
        ];
        foreach ($codesCaisse as $caisse => $codeC) {
            $resultats[2]['as'][$caisse] = $tPaiements->totalAnneeScolaire($millesime, $codeC);
            $resultats[2]['exercice1'][$caisse] =  $tPaiements->totalExercice($millesime, $codeC);
            $resultats[2]['exercice2'][$caisse] =  $tPaiements->totalExercice($millesime + 1, $codeC);
        }
        foreach ($codesModeDePaiement as $modeDePaiement => $codeModeDeP) {
            $resultats['dateBordereau'][$modeDePaiement] = $tPaiements->dateDernierBordereau(
                $codeModeDeP);
            $resultats['dateDernierPaiement'][$modeDePaiement] = $tPaiements->dateDernierPaiement(
                $codeModeDeP); // date('Y-m-d')
            $resultats['encours'][$modeDePaiement] = $tPaiements->sommeBordereau(
                $codeModeDeP);
            // année scolaire
            $resultats[2]['as'][$modeDePaiement] = $tPaiements->totalAnneeScolaire(
                $millesime,null, $codeModeDeP);
            foreach ($codesCaisse as $caisse => $codeC) {
                $resultats[3]['as'][$caisse][$modeDePaiement] = $tPaiements->totalAnneeScolaire(
                    $millesime, $codeC, $codeModeDeP);
            }
            // exercice 1
            $resultats[2]['exercice1'][$modeDePaiement] = $tPaiements->totalExercice(
                $millesime, null, $codeModeDeP);
            foreach ($codesCaisse as $caisse => $codeC) {
                $resultats[3]['exercice1'][$caisse][$modeDePaiement] = $tPaiements->totalExercice(
                    $millesime, $codeC, $codeModeDeP);
            }
            // exercice 2
            $resultats[2]['exercice2'][$modeDePaiement] = $tPaiements->totalExercice(
                $millesime + 1, null, $codeModeDeP);
            foreach ($codesCaisse as $caisse => $codeC) {
                $resultats[3]['exercice2'][$caisse][$modeDePaiement] = $tPaiements->totalExercice(
                    $millesime + 1, $codeC, $codeModeDeP);
            }
        }
        return new ViewModel(
            [
                'millesime' => $millesime,
                'codesCaisse' => $codesCaisse,
                'codesModeDePaiement' => $codesModeDePaiement,
                'resultats' =>$resultats
            ]);
    }

    public function paiementListeAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            $is_post = false;
            if ($this->params('id', '') == 'tous') {
                // appel depuis finances : pas de post, éventuellement des criteres
                $args = [];
                Session::remove('post', $this->getSessionNamespace());
            } else {
                $args = Session::get('post', [], $this->getSessionNamespace());
            }
        } else {
            // suite à un post, l'appel provient du formulaire de criteres ou de la liste
            // des responsables ou de la sortie d'un paiement-ajout ou d'un paiement-edit
            // ou d'un eleve-edit. Séparer les criteres et le post en session
            $is_post = true;
            if (array_key_exists('op', $prg)) {
                // arrive de la liste des responsables ou d'une fiche élève
                if ($prg['op'] == 'eleve-edit') {
                    // pour compatibilité des appels depuis la fiche élève
                    $args = $prg;
                    $args['url1_retour'] = $args['origine'];
                } else {
                    $args = Session::get('post', [], $this->getSessionNamespace());
                    $args = array_merge($args, $prg);
                }
            } else {
                // vient du formulaire des critères ou de la sortie d'un paiement-ajout ou
                // d'un paiement-edit
                $args = $prg;
            }
            Session::set('post', $args, $this->getSessionNamespace());
            Session::set('nsArgsFacture', $this->getSessionNamespace()); // en
                                                                         // SBM_DG_SESSION
        }

        // la page vient de la route (compatibilité du paginateur)
        $currentPage = $this->params('page', 1);
        // le reste vient de $args
        $responsableId = array_key_exists('responsableId', $args) ? $args['responsableId'] : - 1;
        $url1_retour = array_key_exists('url1_retour', $args) ? $args['url1_retour'] : $this->url()->fromRoute(
            'sbmgestion/finance');
        $url2_retour = array_key_exists('url2_retour', $args) ? $args['url2_retour'] : null;
        $op = array_key_exists('op', $args) ? $args['op'] : '';
        if ($retour_n2 = ($op == 'retour')) {
            // le résultat du test est utilisé plus loin sous le nom de $retour_n2 (retour
            // de niveau 2)
            $responsableId = - 1;
            $url2_retour = null;
        }
        // ouvrir la vue Sql
        $tablePaiements = $this->db_manager->get('Sbm\Db\Vue\Paiements');
        $order = 'datePaiement DESC';
        // configuration du paginator
        $nb_paiements = $this->getPaginatorCountPerPage('nb_paiements', 15);
        if ($responsableId == - 1) {
            // pas de $responsableId - gestion de tous les paiements
            $this->plugin_plateforme->majnotification([]); // toutes
            $criteres_form = new Form\CriteresForm('paiements');
            $value_options = $this->db_manager->get('Sbm\Db\Select\Libelles')->caisse();
            $criteres_form->setValueOptions('codeCaisse', $value_options);
            $value_options = $this->db_manager->get('Sbm\Db\Select\Libelles')->modeDePaiement();
            $criteres_form->setValueOptions('codeModeDePaiement', $value_options);
            $criteres_obj = new ObjectDataCriteres($criteres_form->getElementNames());
            // récupère les données du post pour les mettre en session si ce n'est pas un
            // retour de niveau 2
            if (! $retour_n2 && $is_post) {
                $criteres_form->setData($args);
                if ($criteres_form->isValid()) {
                    $criteres_obj->exchangeArray($criteres_form->getData());
                    Session::set('criteres', $criteres_obj->getArrayCopy());
                }
            }
            // récupère les données de la session si le post n'a pas été validé dans le
            // formulaire (pas de post ou invalide)
            $criteres_data = Session::get('criteres');
            if (! $criteres_form->hasValidated() && ! empty($criteres_data)) {
                $criteres_obj->exchangeArray($criteres_data);
                $criteres_form->setData($criteres_obj->getArrayCopy());
            }
            // ici, on n'appelle pas l'impression des factures donc 'namespacectrl' n'est
            // pas nécessaire
            return new ViewModel(
                [

                    'namespacectrl' => null,
                    'paginator' => $tablePaiements->paginator(
                        $criteres_obj->getWhere([
                            'codeCaisse',
                            'codeModeDePaiement'
                        ]), $order),
                    'count_per_page' => $nb_paiements,
                    'criteres_form' => $criteres_form,
                    'h2' => false,
                    'responsable' => null,
                    'page' => $currentPage,
                    'responsableId' => $responsableId,
                    'url1_retour' => $url1_retour,
                    'url2_retour' => $url2_retour
                ]);
        } else {
            // gestion des paiements du $responsableId.
            // L'appel peut provenir de la liste des responsables, de la fiche d'un
            // responsable, de la fiche d'un eleve ou de la liste des paiements.
            // Ici, on ne présente pas le formulaire de critères (pas nécessaire)
            $this->plugin_plateforme->majnotification(
                [
                    'responsableId' => $responsableId
                ]);
            $tResponsables = $this->db_manager->get('Sbm\Db\Table\Responsables');
            // calcul des montants dus, payés et du solde
            $resultats = $this->db_manager->get(
                \SbmCommun\Model\Db\Service\Query\Paiement\Calculs::class)->getResultats(
                $responsableId);
            // condition pour le paginator
            $where = new Where();
            $where->equalTo('responsableId', $responsableId)->equalTo('anneeScolaire',
                Session::get('as')['libelle']);
            return new ViewModel(
                [

                    'namespacectrl' => md5('nsArgsFacture'),
                    'paginator' => $tablePaiements->paginator($where, $order),
                    'count_per_page' => $nb_paiements,
                    'criteres_form' => null,
                    'h2' => true,
                    'responsable' => $tResponsables->getNomPrenom($responsableId, true),
                    'resultats' => $resultats,
                    'page' => $currentPage,
                    'responsableId' => $responsableId,
                    'url1_retour' => $url1_retour,
                    'url2_retour' => $url2_retour
                ]);
        }
    }

    public function paiementAjoutAction()
    {
        /*
         * Reçoit en post les données suivantes à utiliser pour le retour : h2,
         * responsableId, responsable, url1_retour et url2_retour Reçoit dans la route la
         * page de la liste d'où l'on vient
         */
        $prg = $this->prg();
        if ($prg instanceof Response) {
            // transforme un post en une redirection 303 avec le contenu de post en
            // session
            // 'prg_post1' (Expire_Hops = 1)
            return $prg;
        } elseif ($prg === false) {
            // ce n'était pas un post. Cette entrée est illégale et conduit à un retour à
            // la liste
            return $this->redirect()->toRoute('sbmgestion/finance',
                [
                    'action' => 'paiement-liste',
                    'page' => $this->params('page', 1)
                ]);
        }
        // ici, on a eu un post qui a été transformé en rediretion 303. Les données du
        // post sont
        // dans $prg (à récupérer en un seul appel à cause de Expire_Hops)
        $args = $prg;
        // si $args contient la clé 'cancel' c'est un abandon de l'action
        if (\array_key_exists('cancel', $args)) {
            $this->flashMessenger()->addWarningMessage(
                "L'enregistrement n'a pas été modifié.");
            return $this->redirect()->toRoute('sbmgestion/finance',
                [
                    'action' => 'paiement-liste',
                    'page' => $this->params('page', 1)
                ]);
        }
        // on ouvre la table des paiements
        $tablePaiements = $this->db_manager->get('Sbm\Db\Table\Paiements');
        // on détermine si le responsable est fixé ou s'il faudra le choisir
        if (\array_key_exists('h2', $args)) {
            Session::set('responsable_attributes',
                [
                    'h2' => $args['h2'],
                    'responsable' => $args['responsable']
                ], $this->getSessionNamespace());
        } else {
            $responsable_attributes = Session::get('responsable_attributes', [],
                $this->getSessionNamespace());
            $args = \array_merge($args, $responsable_attributes);
        }
        if ($args['h2']) {
            $hidden_responsableId = true; // le responsable est fixé
        } else {
            $hidden_responsableId = false; // il faudra choisir le responsable
        }
        // on ouvre le formulaire, l'adapte et le lie à l'échange de données
        $form = new Form\Paiement(
            [
                'responsableId' => $hidden_responsableId,
                'note' => false
            ]);
        $form->setAttribute('action',
            $this->url()
                ->fromRoute('sbmgestion/finance',
                [
                    'action' => 'paiement-ajout',
                    'page' => $this->params('page', 1)
                ]))
            ->setValueOptions('codeCaisse',
            $this->db_manager->get('Sbm\Db\Select\Libelles')
                ->caisse())
            ->setValueOptions('codeModeDePaiement',
            $this->db_manager->get('Sbm\Db\Select\Libelles')
                ->modeDePaiement())
            ->setMaxLength($this->db_manager->getMaxLengthArray('paiements', 'table'));
        if (! $hidden_responsableId) {
            $form->setValueOptions('responsableId',
                $this->db_manager->get('Sbm\Db\Select\Responsables'));
        }
        $form->bind($tablePaiements->getObjData());
        if (array_key_exists('submit', $args)) {
            $form->setData($args);
            if ($form->isValid()) {
                $montant = $form->getData()->montant;
                // validation des paiements dans les fiches scolarites
                if (! empty($args['eleveId'])) {
                    $responsableId = $form->getData()->responsableId;
                    $resultats = $this->db_manager->get(
                        \SbmCommun\Model\Db\Service\Query\Paiement\Calculs::class)->getResultats(
                        $responsableId, $args['eleveId']);
                    if ($montant >= $resultats->getSolde('liste')) {
                        $tScolarites = $this->db_manager->get('Sbm\Db\Table\Scolarites');
                        $tScolarites->setPaiement(Session::get('millesime'),
                            $args['eleveId']);
                    }
                    $msg = 'Les abonnements ont été mis à jour.';
                } else {
                    $msg = '';
                }
                // sauvegarde après avoir validé les datas
                if ($montant > 0) {
                    $tablePaiements->saveRecord($form->getData());
                    $this->flashMessenger()->addSuccessMessage(
                        implode(' ', [
                            "Le paiement est enregistré.",
                            $msg
                        ]));
                } elseif ($msg) {
                    $this->flashMessenger()->addWarningMessage($msg);
                } else {
                    $this->flashMessenger()->addErrorMessage(
                        'Le montant doit être strictement positif.');
                }
                // retour à la liste

                return $this->redirect()->toRoute('sbmgestion/finance',
                    [
                        'action' => 'paiement-liste',
                        'page' => $this->params('page', 1)
                    ]);
            }
        } else {
            $millesime = Session::get('millesime');
            $as = $millesime . '-' . ($millesime + 1);
            $libelles = $this->db_manager->get('Sbm\Libelles');
            $init_form = [
                'codeCaisse' => $libelles->getCode('caisse', 'régisseur'),
                'datePaiement' => date('Y-m-d H:i:s'),
                'exercice' => date('Y'),
                'anneeScolaire' => $as
            ];
            if ($hidden_responsableId) {
                $init_form['responsableId'] = $args['responsableId'];
            }
            $form->setData($init_form);
        }
        return new ViewModel(
            [

                'form' => $form->prepare(),
                'page' => $this->params('page', 1),
                'paiementId' => null,
                'hidden_responsableId' => $hidden_responsableId,
                'responsable' => $args['responsable']
            ]);
    }

    /**
     * Reçoit en post les données suivantes à utiliser pour le retour : paiementId, h2,
     * responsableId, responsable, url1_retour et url2_retour (seuls paiementId et
     * responsable sont utiles ici - les autres sont présents en raison de la
     * compatibilité du formulaire avec 'groupe') Reçoit dans la route la page de la liste
     * d'où l'on vient
     *
     * @return \Zend\Http\PhpEnvironment\Response|\Zend\Http\Response|\Zend\View\Model\ViewModel
     */
    public function paiementEditAction()
    {
        $currentPage = $this->params('page', 1);
        $hidden_responsableId = false; // mettre true pour obtenir en hidden ; mettre
                                       // false pour
                                       // obtenir un select
        $form = new Form\Paiement(
            [
                'responsableId' => $hidden_responsableId,
                'note' => true
            ]);
        $form->setAttribute('action',
            $this->url()
                ->fromRoute('sbmgestion/finance',
                [
                    'action' => 'paiement-edit',
                    'page' => $currentPage
                ]))
            ->setValueOptions('codeCaisse',
            $this->db_manager->get('Sbm\Db\Select\Libelles')
                ->caisse())
            ->setValueOptions('codeModeDePaiement',
            $this->db_manager->get('Sbm\Db\Select\Libelles')
                ->modeDePaiement());
        if (! $hidden_responsableId) {
            $form->setValueOptions('responsableId',
                $this->db_manager->get('Sbm\Db\Select\Responsables'));
        }
        $params = [
            'data' => [
                'table' => 'paiements',
                'type' => 'table',
                'alias' => 'Sbm\Db\Table\Paiements',
                'id' => 'paiementId'
            ],
            'form' => $form
        ];
        $sessionNS = $this->getSessionNamespace();
        $r = $this->editData($this->db_manager, $params,
            function ($post) use ($sessionNS) {
                if (array_key_exists('responsable', $post)) {
                    $responsable = $post['responsable'];
                    Session::set('responsable', $responsable, $sessionNS);
                } else {
                    $responsable = Session::get('responsable', '', $sessionNS);
                }
                return [
                    'paiementId' => $post['paiementId'],
                    'responsable' => $responsable,
                    'h2' => isset($post['h2']) ? $post['h2'] : null
                ];
            });
        if ($r instanceof Response) {
            return $r;
        } else {
            switch ($r->getStatus()) {
                case 'error':
                case 'warning':
                case 'success':
                    return $this->redirect()->toRoute('sbmgestion/finance',
                        [
                            'action' => 'paiement-liste',
                            'page' => $currentPage
                        ]);
                    break;
                default:
                    return new ViewModel(
                        [

                            'form' => $form->prepare(),
                            'page' => $currentPage,
                            'paiementId' => StdLib::getParam('paiementId', $r->getResult()),
                            'responsable' => StdLib::getParam('responsable',
                                $r->getResult()),
                            'h2' => StdLib::getParam('h2', $r->getResult()),
                            'hidden_responsableId' => $hidden_responsableId
                        ]);
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
        // attention, la suppression d'un paiement déposé chez le comptable doit être
        // accompagnée d'un archivage. Mettre en place un trigger.
        // Si des élèves sont inscrits, la suppression provoquera une alerte.
        $prg = $this->prg();
        if ($prg instanceof Response) {
            // transforme un post en une redirection 303 avec le contenu de post en
            // session 'prg_post1' (Expire_Hops = 1)
            return $prg;
        } elseif ($prg === false) {
            // ce n'était pas un post. Cette entrée est illégale et conduit à un retour à
            // la liste
            $this->flashMessenger()->addErrorMessage("Action interdite.");
            return $this->redirect()->toRoute('sbmgestion/finance',
                [
                    'action' => 'paiement-liste',
                    'page' => $this->params('page', 1)
                ]);
        }
        $args = $prg;
        // si $args contient la clé 'cancel' (ou si paiementId n'est pas défini) c'est un
        // abandon de l'action
        if (array_key_exists('cancel', $args) || ! array_key_exists('paiementId', $args)) {
            $this->flashMessenger()->addWarningMessage(
                "L'enregistrement n'a pas été supprimé.");
            return $this->redirect()->toRoute('sbmgestion/finance',
                [
                    'action' => 'paiement-liste',
                    'page' => $this->params('page', 1)
                ]);
        }
        // Si responsable est passé, on le met en session afin de le retrouver si
        // nécessaire (cas d'un formulaire non validé)
        if (\array_key_exists('responsable', $args)) {
            Session::set('responsable', $args['responsable'], $this->getSessionNamespace());
        }
        $paiementId = StdLib::getParam('paiementId', $args, false);
        if (! $paiementId) {
            $this->flashMessenger()->addErrorMessage(
                'La référence du paiement est inconnue.');
            return $this->redirect()->toRoute('sbmgestion/finance',
                [
                    'action' => 'paiement-liste',
                    'page' => $this->params('page', 1)
                ]);
        }
        $tablePaiements = $this->db_manager->get('Sbm\Db\Table\Paiements');
        $data = $tablePaiements->getRecord($paiementId);
        $responsableId = $data->responsableId;
        $relationResponsableEleves = new Where(null, Where::COMBINED_BY_OR);
        $relationResponsableEleves->equalTo('responsable1Id', $responsableId)->equalTo(
            'responsable2Id', $responsableId);
        $form = $this->form_manager->get(Finances\FinancePaiementSuppr::class);
        $form->setAttribute('action',
            $this->url()
                ->fromRoute('sbmgestion/finance',
                [
                    'action' => 'paiement-suppr',
                    'page' => $this->params('page', 1)
                ]))
            ->setValueOptions('eleveIds',
            $this->db_manager->get('Sbm\Db\Select\Eleves')
                ->elevesAbonnes($relationResponsableEleves));
        if (array_key_exists('submit', $args)) { // suppression confirmée
            $form->setData($args);
            if ($form->isValid()) {
                $data->note = $args['note'];
                $tableScolarites = $this->db_manager->get('Sbm\Db\Table\Scolarites');
                $tableScolarites->setPaiement(Session::get('millesime'),
                    StdLib::getParam('eleveIds', $args, []), false);
                $tablePaiements->saveRecord($data);
                $tablePaiements->deleteRecord($paiementId);
                $this->flashMessenger()->addSuccessMessage(
                    "L'enregistrement a été supprimé.");
                return $this->redirect()->toRoute('sbmgestion/finance',
                    [
                        'action' => 'paiement-liste',
                        'page' => $this->params('page', 1)
                    ]);
            } else {
                $args['responsable'] = Session::get('responsable');
            }
        } else {
            $form->setData($data->getArrayCopy());
        }
        return new ViewModel(
            [

                'data' => $data,
                'form' => $form->prepare(),
                'page' => $this->params('page', 1),
                'paiementId' => $paiementId,
                'responsable' => $args['responsable'],
                'libelles' => $this->db_manager->get('Sbm\Libelles')
            ]);
    }

    private function paiementRectificationData(
        \SbmCommun\Model\Paiements\Historique\Historique $historique, int $exercice)
    {
        $data = [];
        foreach ($this->db_manager->get('Sbm\Db\Query\History')->getPaiementsChanges(
            $exercice) as $row) {
            $historique->setRecord($row);
            $modif = $historique->getAction() == $historique::GET_ACTION_UPDATE;
            if ($modif) {
                // filtrage des données utiles
                $detail = $historique->updateDetail();
                unset($detail['codeCaisse']);
                if (empty($detail)) {
                    continue;
                }
            }
            $aMontant = [];
            $aResponsable = [];
            $aTitulaire = [];
            $aModePaiement = [];
            $aCaisse = [];
            $aBanque = [];
            if ($modif) {
                // update
                $item1 = $historique->getMontant();
                $item2 = $historique->getNewMontant();
                if ($item1 == $item2) {
                    $aMontant[] = $item1;
                } else {
                    $aMontant[] = $item1;
                    $aMontant[] = $item2;
                }
                $item1 = $historique->getResponsable();
                $item2 = $historique->getNewResponsable();
                if ($item1 == $item2) {
                    $aResponsable[] = $item1;
                } else {
                    $aResponsable[] = $item1;
                    $aResponsable[] = $item2;
                }
                $item1 = $historique->getTitulaire();
                $item2 = $historique->getNewTitulaire();
                if ($item1 == $item2) {
                    $aTitulaire[] = $item1;
                } else {
                    $aTitulaire[] = $item1;
                    $aTitulaire[] = $item2;
                }
                $item1 = $historique->getModePaiement();
                $item2 = $historique->getNewModePaiement();
                if ($item1 == $item2) {
                    $aModePaiement[] = $item1;
                } else {
                    $aModePaiement[] = $item1;
                    $aModePaiement[] = $item2;
                }
                $item1 = $historique->getCaisse();
                $item2 = $historique->getNewCaisse();
                if ($item1 == $item2) {
                    $aCaisse[] = $item1;
                } else {
                    $aCaisse[] = $item1;
                    $aCaisse[] = $item2;
                }
                $item1 = $historique->getBanque();
                $item2 = $historique->getNewBanque();
                if ($item1 == $item2) {
                    $aBanque[] = $item1;
                } else {
                    $aBanque[] = $item1;
                    $aBanque[] = $item2;
                }
            } else {
                // delete
                $aMontant[] = $historique->getMontant();
                $aResponsable[] = $historique->getResponsable();
                $aTitulaire[] = $historique->getTitulaire();
                $aModePaiement[] = $historique->getModePaiement();
                $aCaisse[] = $historique->getCaisse();
                $aBanque[] = $historique->getBanque();
            }
            $data[] = [
                'date' => $historique->getDate(),
                'action' => $historique->getAction(),
                'datePaiement' => $historique->getDateValeur(),
                'dateDepot' => $historique->getDateDepot(),
                'aMontant' => $aMontant,
                'aResponsable' => $aResponsable,
                'aTitulaire' => $aTitulaire,
                'aModePaiement' => $aModePaiement,
                'aCaisse' => $aCaisse,
                'aBanque' => $aBanque,
                'note' => $historique->getNote()
            ];
        }
        return $data;
    }

    public function paiementRectificationsAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        }
        if (array_key_exists('cancel', (array) $prg)) {
            return $this->redirect()->toRoute('sbmgestion/finance',
                [
                    'action' => 'paiement-liste',
                    'page' => $this->params('page', 1)
                ]);
        }
        $form = new \SbmGestion\Form\Finances\ChoixExercice();
        $form->setValueOptions('exercice',
            $this->db_manager->get('Sbm\Db\Select\History')
                ->paiementExercices());
        $form->setData((array) $prg);
        $exercice = null;
        if (array_key_exists('rectifications', (array) $prg)) {
            Session::remove('exercice',
                $this->getSessionNamespace('paiement-rectifications'));
        } else {
            $exercice = Session::get('exercice', null,
                $this->getSessionNamespace('paiement-rectifications'));
            if (! $exercice) {
                $form->setData((array) $prg);
                if ($form->isValid()) {
                    $exercice = $form->getData()['exercice'];
                    Session::set('exercice', $exercice,
                        $this->getSessionNamespace('paiement-rectifications'));
                }
            }
        }
        if (! $exercice) {
            $originePage = $this->params('page', 1);
            $form->setAttribute('action',
                $this->url()
                    ->fromRoute('sbmgestion/finance',
                    [
                        'action' => 'paiement-rectifications',
                        'id' => $originePage
                    ]));
            $view = new ViewModel([
                'form' => $form,
                'id' => $originePage
            ]);
            return $view->setTemplate('sbm-gestion/finance/paiement-choix-exercice.phtml');
        }
        $currentPage = $this->params('page', 1);
        $originePage = $this->params('id', 1);
        $historique = new \SbmCommun\Model\Paiements\Historique\Historique();
        $historique->setTResponsables($this->db_manager->get('Sbm\Db\Table\Responsables'));
        $historique->setCaisses(
            $this->db_manager->get('Sbm\Db\Select\Libelles')
                ->caisse());
        $historique->setModesDePaiement(
            $this->db_manager->get('Sbm\Db\Select\Libelles')
                ->modeDepaiement());

        return new ViewModel(
            [
                'exercice' => $exercice,
                'page' => $currentPage,
                'id' => $originePage,
                'paginator' => new Paginator(
                    new ArrayAdapter(
                        $this->paiementRectificationData($historique, $exercice))),
                'count_per_page' => $this->getPaginatorCountPerPage('nb_rectifications',
                    15),
                'historique' => $historique
            ]);
    }

    /**
     * Edition d'un pdf reprenant la liste des rectifications pour l'exercice précisé
     *
     * @return \Zend\Http\PhpEnvironment\Response|\Zend\Http\Response
     */
    public function paiementRectificationsPdfAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } else {
            $args = $prg ?: [];
            if (! array_key_exists('documentId', $args)) {
                $this->flashMessenger()->addErrorMessage(
                    'Le document à imprimer n\'a pas été indiqué.');
                return $this->redirect()->toRoute('sbmgestion/finance',
                    [
                        'action' => 'paiement-rectifications',
                        'page' => $this->params('page', 1)
                    ]);
            }
            $documentId = $args['documentId'];
        }
        $exercice = Session::get('exercice', null,
            $this->getSessionNamespace('paiement-rectifications'));
        if (! $exercice) {
            return $this->redirect()->toRoute('sbmgestion/finance',
                [
                    'action' => 'paiement-rectifications',
                    'page' => $this->params('page', 1)
                ]);
        }
        $historique = new \SbmCommun\Model\Paiements\Historique\Historique();
        $historique->setTResponsables($this->db_manager->get('Sbm\Db\Table\Responsables'));
        $historique->setCaisses(
            $this->db_manager->get('Sbm\Db\Select\Libelles')
                ->caisse());
        $historique->setModesDePaiement(
            $this->db_manager->get('Sbm\Db\Select\Libelles')
                ->modeDepaiement());

        $this->RenderPdfService->setParam('documentId', $documentId)
            ->setParam('docaffectationId', $this->params('id', 0))
            ->setData($this->paiementRectificationData($historique, $exercice))
            ->renderPdf();
    }

    public function paiementDepotAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } else {
            $args = $prg ?: [];
            $page = $this->params('page', 1);
            if (array_key_exists('cancel', $args)) {
                $this->flashMessenger()->addWarningMessage('Action abandonnée.');
                return $this->redirect()->toRoute('sbmgestion/finance',
                    [
                        'action' => 'paiement-liste',
                        'page' => $page
                    ]);
            }
        }
        $tPaiements = $this->db_manager->get('Sbm\Db\Table\Paiements');
        $sBordereaux = $this->db_manager->get('Sbm\Db\Select\Bordereaux');
        $bordereauxClotures = $sBordereaux->clotures();
        $bordereauxEnCours = $sBordereaux->encours();
        $nouveauxPossibles = $this->db_manager->get('Sbm\Db\Select\Libelles')->modeDePaiement();
        foreach ($bordereauxEnCours as $key => $libelle) {
            $aKey = $sBordereaux->decode($key);
            unset($nouveauxPossibles[$aKey['codeModeDePaiement']]);
        }
        unset($libelle);
        $form1 = new \SbmGestion\Form\Finances\BordereauRemiseValeurChoix();
        $form1->setAttribute('id', 'bordereau-en-cours');
        $form1->setValueOptions('bordereau', $bordereauxEnCours);
        $editerSubmit = $form1->get('editer');
        $editerSubmit->setAttribute('formaction',
            $this->url()
                ->fromRoute('sbmgestion/finance',
                [
                    'action' => 'paiement-pdf',
                    'page' => $page,
                    'id' => 1
                ]));
        $exporterSubmit = $form1->get('exporter');
        $exporterSubmit->setAttribute('formaction',
            $this->url()
                ->fromRoute('sbmgestion/finance',
                [
                    'action' => 'paiement-exporter',
                    'page' => $page,
                    'id' => 1
                ]));
        $form2 = new \SbmGestion\Form\Finances\BordereauRemiseValeurCreer();
        $form2->setAttribute('id', 'bordereau-preparer');
        $form2->setValueOptions('codeModeDePaiement', $nouveauxPossibles)->setValueOptions(
            'codeCaisse', $this->db_manager->get('Sbm\Db\Select\Libelles')
                ->caisse());
        $form3 = new \SbmGestion\Form\Finances\BordereauRemiseValeurChoix();
        $form3->setAttribute('id', 'bordereau-cloture');
        $form3->setValueOptions('bordereau', $bordereauxClotures);
        $editerSubmit = $form3->get('editer');
        $editerSubmit->setAttribute('formaction',
            $this->url()
                ->fromRoute('sbmgestion/finance',
                [
                    'action' => 'paiement-pdf',
                    'page' => $page,
                    'id' => 3
                ]));
        $exporterSubmit = $form3->get('exporter');
        $exporterSubmit->setAttribute('formaction',
            $this->url()
                ->fromRoute('sbmgestion/finance',
                [
                    'action' => 'paiement-exporter',
                    'page' => $page,
                    'id' => 3
                ]));
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
                $nb_bordereau = 0;
                $tPaiements = $this->db_manager->get('Sbm\Db\Table\Paiements');
                $bloque = false;
                $typeMessage = 'success';
                try {
                    $dateDernierBordereau = DateLib::formatDateFromMysql(
                        $tPaiements->dateDernierBordereau($args['codeModeDePaiement']));
                    $bloque = $dateDernierBordereau == DateLib::today();
                } catch (\Exception $e) {
                }
                if ($bloque) {
                    $message = "Impossible ! Un bordereau de ce type de paiement a déjà été créé aujourd'hui.";
                    $typeMessage = 'warning';
                } else {
                    do {
                        if ($nb_bordereau) {
                            sleep(2); // pour imposer un changement dans $now
                        }
                        $nb_bordereau ++;
                        $now = DateLib::nowToMysql();
                        $n = $tPaiements->marqueBordereau($now,
                            $args['codeModeDePaiement'], $args['codeCaisse'],
                            $args['exercice'], $args['anneeScolaire']);
                    } while ($n == $tPaiements::MAXI);
                    if ($n == 0) {
                        $n = 200;
                        $nb_bordereau --;
                    }
                    if ($nb_bordereau <= 1) {
                        if ($nb_bordereau) {
                            $message = sprintf(
                                'Un bordereau daté du %s a été créé. Il contient %d enregistrements.',
                                DateLib::formatDateTimeFromMysql($now), $n);
                        } else {
                            $message = "Il n'y avait pas de paiements de ce type en attente de dépôt.";
                        }
                    } else {
                        $message = sprintf(
                            '%d bordereaux datés du %s ont été créés. Ils contienent %d enregistrements.',
                            $nb_bordereau, DateLib::formatDateTimeFromMysql($now),
                            $n + ($nb_bordereau - 1) * $tPaiements::MAXI);
                    }
                }
                $this->flashMessenger()->addMessage($message, $typeMessage);
                return $this->redirect()->toRoute('sbmgestion/finance',
                    [
                        'action' => 'paiement-depot'
                    ]);
            }
        } elseif (array_key_exists('supprimer', $args)) {
            $form1->setData($args);
            if ($form1->isValid()) {
                $args = $form1->getData();
                $aKey = $sBordereaux->decode($args['bordereau']);
                $n = $tPaiements->annuleBordereau($aKey['dateBordereau'],
                    $aKey['codeModeDePaiement']);
                $format = "Le bordereau de %s a été supprimé. Il contient %d paiements qui sont à nouveau disponibles pour le prochain bordereau.";
                $message = sprintf($format, $bordereauxEnCours[$args['bordereau']], $n);
                $this->flashMessenger()->addSuccessMessage($message);
                return $this->redirect()->toRoute('sbmgestion/finance',
                    [
                        'action' => 'paiement-depot'
                    ]);
            }
        } elseif (array_key_exists('cloturer', $args)) {
            $form1->setData($args);
            if ($form1->isValid()) {
                $args = $form1->getData();
                $aKey = $sBordereaux->decode($args['bordereau']);
                $n = $tPaiements->clotureDepot($aKey['dateBordereau'],
                    $aKey['codeModeDePaiement'],
                    $this->db_manager->get('Sbm\Db\System\Libelles')
                        ->getCode('Caisse', 'comptable'));
                $format = "Le bordereau de %s a été clôturé. Les %d paiements qu'il contient sont maintenant dans la caisse du comptable.";
                $message = sprintf($format, $bordereauxEnCours[$args['bordereau']], $n);
                $this->flashMessenger()->addSuccessMessage($message);
                return $this->redirect()->toRoute('sbmgestion/finance',
                    [
                        'action' => 'paiement-depot'
                    ]);
            }
        } else {
            $form2->setData(
                [
                    'codeCaisse' => $this->db_manager->get('Sbm\Db\System\Libelles')
                        ->getCode('Caisse', 'régisseur')
                ]);
        }
        if (substr($this->params('id', ''), - 5) == 'error') {
            if ($this->params('id', '1error') == '3error') {
                $form3->setData([])->isValid();
            } else {
                $form1->setData([])->isValid();
            }
        }
        return new ViewModel(
            [

                'form1' => $form1, // bordereaux en cours
                'voirForm1' => ! empty($bordereauxEnCours),
                'form2' => $form2, // nouveau bordereau
                'voirForm2' => ! empty($nouveauxPossibles),
                'form3' => $form3, // bordereaux clôturés
                'voirForm3' => ! empty($bordereauxClotures)
            ]);
    }

    /**
     * Choix d'un bordereau (par formulaire) puis exportation
     *
     * @return \Zend\Http\PhpEnvironment\Response|\Zend\Http\Response
     */
    public function paiementExporterAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        }
        $args = $prg ?: [];
        $page = $this->params('page', 1);
        $id = $this->params('id', 1);
        if (! array_key_exists('exporter', $args)) {
            $this->flashMessenger()->addWarningMessage('Action abandonnée.');
            return $this->redirect()->toRoute('sbmgestion/finance',
                [
                    'action' => 'paiement-liste',
                    'page' => $page
                ]);
        }
        $sBordereaux = $this->db_manager->get('Sbm\Db\Select\Bordereaux');
        $bordereauxClotures = $sBordereaux->clotures();
        $bordereauxEnCours = $sBordereaux->encours();
        $nouveauxPossibles = $this->db_manager->get('Sbm\Db\Select\Libelles')->modeDePaiement();
        foreach ($bordereauxEnCours as $key => $libelle) {
            $aKey = $sBordereaux->decode($key);
            unset($nouveauxPossibles[$aKey['codeModeDePaiement']]);
        }
        unset($libelle);
        $form = $this->form_manager->get(Finances\BordereauRemiseValeurChoix::class);
        if ($id == 1) {
            $form->setValueOptions('bordereau', $bordereauxEnCours);
        } else {
            $form->setValueOptions('bordereau', $bordereauxClotures);
        }
        $form->setData($args);
        if ($form->isValid()) {
            $args = $form->getData();
            $columns = [
                'Mode de paiement' => 'modeDePaiement',
                'Date' => 'dateValeur',
                'Titulaire' => 'titulaire',
                'Banque' => 'banque',
                'Référence' => 'reference',
                'Montant' => 'montant',
                'Responsable' => 'responsable'
            ];
            // $aKey est un tableau de la forme :
            // ['dateBordereau' => date, 'codeModeDePaiement' => code]
            $aKey = $sBordereaux->decode($args['bordereau']);
            $where = new Where();
            $where->equalTo('dateBordereau', $aKey['dateBordereau'])->equalTo(
                'codeModeDePaiement', $aKey['codeModeDePaiement']);
            // lancement de la requête et construction d'un tabeau des datas
            $data = [];
            foreach ($this->db_manager->get('Sbm\Db\Vue\Paiements')->fetchAll($where,
                [
                    'dateValeur',
                    'titulaire'
                ]) as $paiement) {
                $aPaiement = $paiement->getArrayCopy();
                $ligne = [];
                foreach ($columns as $value) {
                    $ligne[] = $aPaiement[$value];
                }
                $data[] = $ligne;
            }
            // exportation du résultat de la requête selon la composition du tableau
            // $columns
            return $this->csvExport('bordereau-de-paiement.csv', array_keys($columns),
                $data);
        }
        // le formulaire ne valide pas. Il s'agit du select qui est vide.
        return $this->redirect()->toRoute('sbmgestion/finance',
            [
                'action' => 'paiement-depot',
                'page' => $page,
                'id' => $id . 'error'
            ]);
    }

    /**
     * On reçoit en post les arguments suivants : url1_retour, url2_retour, h2,
     * responsableId, responsable - h2 vaut 1 (sinon, pas de bouton pour arriver à cette
     * action) - responsableId est un entier > 1 - responsable contient le titre, le nom
     * et le prénom du responsable (sous forme d'une chaine)
     */
    public function paiementDetailAction()
    {
        $page = $this->params('page', 1);
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false || empty($prg['responsableId'])) {
            $args = Session::get('post', false, $this->getSessionNamespace());
            if ($args === false) {
                return $this->redirect()->toRoute('sbmgestion/finance',
                    [
                        'action' => 'paiement-liste',
                        'page' => $page
                    ]);
            }
        } else {
            $args = $prg;
            Session::set('post', $args, $this->getSessionNamespace());
            Session::set('nsArgsFacture', $this->getSessionNamespace()); // en
                                                                         // SBM_DG_SESSION
        }
        $responsableId = $args['responsableId'];
        $resultats = $this->db_manager->get(
            \SbmCommun\Model\Db\Service\Query\Paiement\Calculs::class)->getResultats(
            $responsableId);
        return new ViewModel(
            [

                'namespacectrl' => md5('nsArgsFacture'),
                'args' => $args,
                'resultats' => $resultats,
                'factures' => $this->db_manager->get('Sbm\Db\Table\Factures')->fetchAll(
                    [
                        'millesime' => Session::get('millesime'),
                        'responsableId' => $responsableId
                    ], 'date')
            ]);
    }

    /**
     * On arrive ici depuis la page de choix des impressions à réaliser (méthode
     * paiementDepotAction). Ici, le paramètre page correspond au numéro du formulaire et
     * sert à mettre en place les bons valueOptions. Le formulaire de choix n'est pas un
     * ObjectData, aussi on n'utilise pas la méthode documentPdf du parent
     * AbstactActionController. Le where est construit dans la méthode.
     *
     * @return \Zend\Http\PhpEnvironment\Response|\Zend\Http\Response
     */
    public function paiementPdfAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        }
        $page = $this->params('page', 1);
        $id = $this->params('id', 1);
        $args = $prg ?: [];
        if (! array_key_exists('editer', $args)) {
            $this->flashMessenger()->addWarningMessage('Action abandonnée.');
            return $this->redirect()->toRoute('sbmgestion/finance',
                [
                    'action' => 'paiement-depot',
                    'page' => $page
                ]);
        }
        $sBordereaux = $this->db_manager->get('Sbm\Db\Select\Bordereaux');
        $bordereauxClotures = $sBordereaux->clotures();
        $bordereauxEnCours = $sBordereaux->encours();
        $nouveauxPossibles = $this->db_manager->get('Sbm\Db\Select\Libelles')->modeDePaiement();
        foreach ($bordereauxEnCours as $key => $libelle) {
            $aKey = $sBordereaux->decode($key);
            unset($nouveauxPossibles[$aKey['codeModeDePaiement']]);
        }
        unset($libelle);
        $form = $this->form_manager->get(Finances\BordereauRemiseValeurChoix::class);
        if ($id == 1) {
            $form->setValueOptions('bordereau', $bordereauxEnCours);
        } else {
            $form->setValueOptions('bordereau', $bordereauxClotures);
        }
        $form->setData($args);
        if ($form->isValid()) {
            $args = $form->getData();
            $call_pdf = $this->RenderPdfService;
            $call_pdf->setParam('documentId', 'Bordereau de remise de valeurs');
            $aKey = $sBordereaux->decode($args['bordereau']); // tableau de la forme
                                                              // ['dateBordereau' => date,
                                                              // 'codeModeDePaiement' =>
                                                              // code]
            $where = new Where();
            $where->equalTo('dateBordereau', $aKey['dateBordereau'])->equalTo(
                'codeModeDePaiement', $aKey['codeModeDePaiement']);
            $call_pdf->setParam('where', $where)
                ->setEndOfScriptFunction(
                function () {
                    $this->flashMessenger()
                        ->addSuccessMessage("Création d'un pdf.");
                })
                ->renderPdf();
        }
        // le formulaire ne valide pas. Il s'agit du select qui est vide.
        return $this->redirect()->toRoute('sbmgestion/finance',
            [
                'action' => 'paiement-depot',
                'page' => $page,
                'id' => $id . 'error'
            ]);

        $criteresObject = [
            '\SbmCommun\Model\Db\ObjectData\Criteres',
            [
                'expressions' => [
                    'active' => 'Literal:active = 0'
                ]
            ]
        ];
        $criteresForm = [
            '\SbmCommun\Form\CriteresForm',
            'users'
        ];
        $documentId = null;
        $retour = [
            'route' => 'sbmadmin',
            'action' => 'user-liste'
        ];
        return $this->documentPdf($criteresObject, $criteresForm, $documentId, $retour);
    }

    /**
     * Liste des tarifs (avec pagination)
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function tarifListeAction()
    {
        $args = $this->initListe('tarifs',
            function ($config, $form) {
                $table = $config['db_manager']->get('Sbm\Db\Table\Tarifs');
                $form->setValueOptions('rythme', $table->getRythmes());
                $form->setValueOptions('grille', $table->getGrilles());
                $form->setValueOptions('mode', $table->getModes());
            }, [
                'rythme',
                'grille',
                'mode'
            ]);
        if ($args instanceof Response)
            return $args;
        $effectifTarifs = $this->db_manager->get('Sbm\Db\Eleve\EffectifTarifs');
        $effectifTarifs->init();
        return new ViewModel(
            [

                'paginator' => $this->db_manager->get('Sbm\Db\Vue\Tarifs')->paginator(
                    $args['where'], [
                        'grille',
                        'nom'
                    ]),
                'effectifTarifs' => $effectifTarifs,
                'page' => $this->params('page', 1),
                'count_per_page' => $this->getPaginatorCountPerPage('nb_tarifs', 10),
                'criteres_form' => $args['form']
            ]);
    }

    /**
     * Modification d'une fiche de tarif (la validation porte sur un champ csrf)
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function tarifEditAction()
    {
        $currentPage = $this->params('page', 1);
        $tableTarifs = $this->db_manager->get('Sbm\Db\Table\Tarifs');
        $form = $this->form_manager->get(Form\Tarif::class);
        $form->setValueOptions('rythme',
            array_combine($tableTarifs->getRythmes(), $tableTarifs->getRythmes()))
            ->setValueOptions('grille',
            array_combine($tableTarifs->getGrilles(), $tableTarifs->getGrilles()))
            ->setValueOptions('mode',
            array_combine($tableTarifs->getModes(), $tableTarifs->getModes()));
        $params = [
            'data' => [
                'table' => 'tarifs',
                'type' => 'table',
                'alias' => 'Sbm\Db\Table\Tarifs',
                'id' => 'tarifId'
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
                    return $this->redirect()->toRoute('sbmgestion/finance',
                        [
                            'action' => 'tarif-liste',
                            'page' => $currentPage
                        ]);
                    break;
                default:
                    return new ViewModel(
                        [

                            'form' => $form->prepare(),
                            'page' => $currentPage,
                            'tarifId' => $r->getResult()
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
    public function tarifSupprAction()
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
                'alias' => 'Sbm\Db\Table\Tarifs',
                'id' => 'tarifId'
            ],
            'form' => $form
        ];
        try {
            $r = $this->supprData($this->db_manager, $params,
                function ($id, $tableTarifs) {
                    return [
                        'id' => $id,
                        'data' => $tableTarifs->getRecord($id)
                    ];
                });
        } catch (\Zend\Db\Adapter\Exception\InvalidQueryException $e) {
            $this->flashMessenger()->addWarningMessage(
                'Impossible de supprimer ce tarif parce qu\'il est affecté à certains élèves.');
            return $this->redirect()->toRoute('sbmgestion/finance',
                [
                    'action' => 'tarif-liste',
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
                    return $this->redirect()->toRoute('sbmgestion/finance',
                        [
                            'action' => 'tarif-liste',
                            'page' => $currentPage
                        ]);
                    break;
                default:
                    return new ViewModel(
                        [

                            'form' => $form->prepare(),
                            'page' => $currentPage,
                            'data' => StdLib::getParam('data', $r->getResult()),
                            'tarifId' => StdLib::getParam('id', $r->getResult())
                        ]);
                    break;
            }
        }
    }

    /**
     * Ajout d'une nouvelle fiche de tarif (la validation porte sur un champ csrf)
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function tarifAjoutAction()
    {
        $currentPage = $this->params('page', 1);
        $tableTarifs = $this->db_manager->get('Sbm\Db\Table\Tarifs');
        $form = $this->form_manager->get(Form\Tarif::class);
        $form->setValueOptions('rythme',
            array_combine($tableTarifs->getRythmes(), $tableTarifs->getRythmes()))
            ->setValueOptions('grille',
            array_combine($tableTarifs->getGrilles(), $tableTarifs->getGrilles()))
            ->setValueOptions('mode',
            array_combine($tableTarifs->getModes(), $tableTarifs->getModes()));
        $params = [
            'data' => [
                'table' => 'tarifs',
                'type' => 'table',
                'alias' => 'Sbm\Db\Table\Tarifs'
            ],
            // 'id' => 'tarifId'
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
                return $this->redirect()->toRoute('sbmgestion/finance',
                    [
                        'action' => 'tarif-liste',
                        'page' => $currentPage
                    ]);
                break;
            default:
                return new ViewModel(
                    [

                        'form' => $form->prepare(),
                        'page' => $currentPage,
                        'tarifId' => null
                    ]);
                break;
        }
    }

    /**
     * renvoie la liste des élèves inscrits pour une grille tarifaire donnée
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function tarifGroupAction()
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
        $tarifId = StdLib::getParam('tarifId', $args, - 1);
        $grilleTarif = StdLib::getParam('grilleTarif', $args);
        if (empty($grilleTarif) || $tarifId == - 1) {
            $this->flashMessenger()->addErrorMessage('Action interdite.');
            return $this->redirect()->toRoute('sbmgestion/finance',
                [
                    'action' => 'tarif-liste',
                    'page' => $pageRetour
                ]);
        } else {
            $grilleTarifId = $this->db_manager->get('Sbm\Db\Table\Tarifs')
                ->getStrategie('grille')
                ->extract($grilleTarif);
        }
        return new ViewModel(
            [

                'paginator' => $this->db_manager->get('Sbm\Db\Eleve\Liste')->paginatorGroup(
                    Session::get('millesime'), [
                        'sco.grilleTarif' => $grilleTarifId
                    ], [
                        'nom',
                        'prenom'
                    ]),
                'count_per_page' => $this->getPaginatorCountPerPage('nb_eleves', 15),
                'tarif' => $this->db_manager->get('Sbm\Db\Table\Tarifs')->getRecord(
                    $tarifId),
                'page' => $currentPage,
                'pageRetour' => $pageRetour
            ]);
    }

    /**
     * envoie un evenement contenant les paramètres de création d'un document pdf (le
     * listener SbmPdf\Listener\PdfListener lancera la création du pdf) Il n'y a pas de
     * vue associée à cette action puisque la response html est créée par \TCPDF
     */
    public function tarifPdfAction()
    {
        $criteresObject = [
            '\SbmCommun\Model\Db\ObjectData\Criteres',
            [
                'strict' => []
            ]
        ];
        $criteresForm = [
            '\SbmCommun\Form\CriteresForm',
            'tarifs'
        ];
        $documentId = null;
        $retour = [
            'route' => 'sbmgestion/finance',
            'action' => 'tarif-liste'
        ];
        return $this->documentPdf($criteresObject, $criteresForm, $documentId, $retour,
            [
                'effectifClassName' => 'Sbm\Db\Eleve\EffectifTarifs'
            ]);
    }

    public function tarifGroupPdfAction()
    {
        $strategy = $this->db_manager->get('Sbm\Db\Table\Tarifs')->getStrategie('grille');
        $criteresObject = [
            'SbmCommun\Model\Db\ObjectData\Criteres',
            null,
            function ($where, $args) use ($strategy) {
                // $tarifId = StdLib::getParam('tarifId', $args, - 1);
                $grilleTarif = StdLib::getParam('grilleTarif', $args);
                if ($grilleTarif) {
                    $grilleTarifId = $strategy->extract($grilleTarif);
                }
                $where = new Where();
                $where->equalTo('grilleTarif', $grilleTarifId);
                return $where;
            }
        ];
        $criteresForm = 'SbmCommun\Form\CriteresForm';
        $documentId = null;
        $retour = [
            'route' => 'sbmgestion/transport',
            'action' => 'tarif-group'
        ];
        return $this->documentPdf($criteresObject, $criteresForm, $documentId, $retour);
    }

    // Gestion des organismes payeurs
    public function organismeListeAction()
    {
        $args = $this->initListe('organismes');
        if ($args instanceof Response)
            return $args;
        $effectifOrganismes = $this->db_manager->get('Sbm\Db\Eleve\EffectifOrganismes');
        $effectifOrganismes->init();
        return new ViewModel(
            [

                'paginator' => $this->db_manager->get('Sbm\Db\Vue\Organismes')->paginator(
                    $args['where']),
                'effectifOrganismes' => $effectifOrganismes,
                'page' => $this->params('page', 1),
                'count_per_page' => $this->getPaginatorCountPerPage('nb_organismes', 15),
                'criteres_form' => $args['form']
            ]);
    }

    public function organismeAjoutAction()
    {
        $currentPage = $this->params('page', 1);
        $form = $this->form_manager->get(Form\Organisme::class);
        $form->setValueOptions('communeId',
            $this->db_manager->get('Sbm\Db\Select\Communes')
                ->visibles());
        $params = [
            'data' => [
                'table' => 'organismes',
                'type' => 'table',
                'alias' => 'Sbm\Db\Table\Organismes'
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
                return $this->redirect()->toRoute('sbmgestion/finance',
                    [
                        'action' => 'organisme-liste',
                        'page' => $currentPage
                    ]);
                break;
            default:
                return new ViewModel(
                    [

                        'form' => $form->prepare(),
                        'page' => $currentPage,
                        'organismeId' => null
                    ]);
                break;
        }
    }

    public function organismeEditAction()
    {
        $currentPage = $this->params('page', 1);
        $form = $this->form_manager->get(Form\Organisme::class);
        $form->setValueOptions('communeId',
            $this->db_manager->get('Sbm\Db\Select\Communes')
                ->visibles());
        $params = [
            'data' => [
                'table' => 'organismes',
                'type' => 'table',
                'alias' => 'Sbm\Db\Table\Organismes',
                'id' => 'organismeId'
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
                    return $this->redirect()->toRoute('sbmgestion/finance',
                        [
                            'action' => 'organisme-liste',
                            'page' => $currentPage
                        ]);
                    break;
                default:
                    return new ViewModel(
                        [

                            'form' => $form->prepare(),
                            'page' => $currentPage,
                            'organismeId' => $r->getResult()
                        ]);
                    break;
            }
        }
    }

    public function organismeSupprAction()
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
                'alias' => 'Sbm\Db\Table\Organismes',
                'id' => 'organismeId'
            ],
            'form' => $form
        ];
        $vueorganismes = $this->db_manager->get('Sbm\Db\Vue\Organismes');
        try {
            $r = $this->supprData($this->db_manager, $params,
                function ($id, $tableorganismes) use ($vueorganismes) {
                    return [
                        'id' => $id,
                        'data' => $vueorganismes->getRecord($id)
                    ];
                });
        } catch (\Exception $e) {
            $this->flashMessenger()->addWarningMessage(
                'Impossible de supprimer cet organisme car il est utilisé.');
            return $this->redirect()->toRoute('sbmgestion/finance',
                [
                    'action' => 'organisme-liste',
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
                    return $this->redirect()->toRoute('sbmgestion/finance',
                        [
                            'action' => 'organisme-liste',
                            'page' => $currentPage
                        ]);
                    break;
                default:
                    return new ViewModel(
                        [

                            'form' => $form->prepare(),
                            'page' => $currentPage,
                            'data' => StdLib::getParam('data', $r->getResult()),
                            'organismeId' => StdLib::getParam('id', $r->getResult())
                        ]);
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
        $organismeId = StdLib::getParam('organismeId', $args, - 1);
        if ($organismeId == - 1) {
            $this->flashMessenger()->addErrorMessage('Action interdite.');
            return $this->redirect()->toRoute('sbmgestion/finance',
                [
                    'action' => 'organisme-liste',
                    'page' => $pageRetour
                ]);
        }
        return new ViewModel(
            [

                'paginator' => $this->db_manager->get('Sbm\Db\Eleve\Liste')->paginatorGroup(
                    Session::get('millesime'), [
                        'organismeId' => $organismeId
                    ], [
                        'nom',
                        'prenom'
                    ]),
                'count_per_page' => $this->getPaginatorCountPerPage('nb_eleves', 15),
                'organisme' => $this->db_manager->get('Sbm\Db\Vue\Organismes')->getRecord(
                    $organismeId),
                'page' => $currentPage,
                'pageRetour' => $pageRetour,
                'organismeId' => $organismeId
            ]);
    }

    public function organismeGroupPdfAction()
    {
        $criteresObject = [
            'SbmCommun\Model\Db\ObjectData\Criteres',
            null,
            function ($where, $args) {
                $organismeId = StdLib::getParam('organismeId', $args, - 1);
                $where = new Where();
                $where->equalTo('organismeId', $organismeId);
                return $where;
            }
        ];
        $criteresForm = 'SbmCommun\Form\CriteresForm';
        $documentId = null;
        $retour = [
            'route' => 'sbmgestion/transport',
            'action' => 'organisme-group'
        ];
        return $this->documentPdf($criteresObject, $criteresForm, $documentId, $retour);
    }

    /**
     * envoie un evenement contenant les paramètres de création d'un document pdf (le
     * listener SbmPdf\Listener\PdfListener lancera la création du pdf) Il n'y a pas de
     * vue associée à cette action puisque la response html est créée par \TCPDF
     */
    public function organismePdfAction()
    {
        $criteresObject = [
            '\SbmCommun\Model\Db\ObjectData\Criteres',
            [
                'strict' => []
            ]
        ];
        $criteresForm = [
            '\SbmCommun\Form\CriteresForm',
            'tarifs'
        ];
        $documentId = null;
        $retour = [
            'route' => 'sbmgestion/finance',
            'action' => 'organisme-liste'
        ];
        return $this->documentPdf($criteresObject, $criteresForm, $documentId, $retour,
            [
                'effectifClassName' => $this->db_manager->get(
                    'Sbm\Db\Eleve\EffectifOrganismes')
            ]);
    }
}