<?php
/**
 * Ensemble de pages de retour de la plateforme de paiement
 *
 * @project sbm
 * @package SbmPaiement/Controller
 * @filesource IndexController.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 9 juin 2020
 * @version 2020-2.6.0
 */
namespace SbmPaiement\Controller;

use SbmBase\Model\Session;
use SbmBase\Model\StdLib;
use SbmCommun\Model\Mvc\Controller\AbstractActionController;
use SbmPaiement\Model\RapprochementCR;
use Zend\Http\PhpEnvironment\Response;
use Zend\View\Model\ViewModel;
use SbmPaiement\Form;

class IndexController extends AbstractActionController
{
    use \SbmCommun\Model\Traits\DebugTrait;

    /**
     * Dans cette version, le montant à payer n'est pas passé par le POST (c'est un
     * leurre) mais est obtenu à partir de la facture émise ou récupérée si elle existe
     * déjà. Le montant à payer est le solde de la facture. On considère que l'appel vient
     * de l'URL \parent (en cas d'erreur).
     *
     * @return \Zend\Http\Response|\Zend\Http\PhpEnvironment\Response|\Zend\View\Model\ViewModel
     */
    public function formulaireAction()
    {
        $prg = $this->getResponsableIdFromSession('nsArgsFacture');
        if ($prg instanceof Response) {
            return $prg;
        }
        $responsableId = $prg;
        try {
            $responsable = $this->responsable->get();
            if ($responsable->responsableId != $responsableId) {
                return $this->redirect()->toRoute('login', [
                    'action' => 'logout'
                ]);
            }
        } catch (\Exception $e) {
            return $this->redirect()->toRoute('login', [
                'action' => 'logout'
            ]);
        }
        try {
            $this->plugin_plateforme->setResponsable($responsable)
                ->setPaiement3Fois($this->params('id', 1))
                ->prepare()
                ->initPaiement();
        } catch (\Exception $e) {
            $message = $e->getMessage();
            $this->flashMessenger()->addErrorMessage($message);
            return $this->redirect()->toRoute('sbmparent');
        }
        return new ViewModel([
            'oPlateforme' => $this->plugin_plateforme
        ]);
    }

    public function formAbandonnerAction()
    {
        try {
            $this->plugin_plateforme->validFormAbandonner($this->getRequest()
                ->getPost());
            $this->flashMessenger()->addInfoMessage('Le paiement a été abandonné.');
        } catch (\Exception $e) {
            $message = $e->getMessage();
            $this->flashMessenger()->addErrorMessage($message);
        }
        return $this->redirect()->toRoute('sbmparent');
    }

    public function listeAction()
    {
        $table = $this->db_manager->get('SbmPaiement\Plugin\Table');
        // les expressions sont définies dans le plugin
        $args = $this->initListe($table->criteres(), null, [], $table->getExpressions());
        if ($args instanceof Response) {
            return $args;
        } elseif (array_key_exists('cancel', $args)) {
            $this->redirectToOrigin()->reset();
            return $this->redirect()->toRoute('sbmpaiement');
        }
        $order = $table->getIdName() . ' DESC';
        if (method_exists($table, 'adapteWhere')) {
            $table->adapteWhere($args['where']);
        }
        return new ViewModel(
            [
                'paginator' => $table->paginator($args['where'], $order),
                'page' => $this->params('page', 1),
                'count_per_page' => $this->getPaginatorCountPerPage('nb_notifications', 15),
                'criteres_form' => $args['form']
            ]);
    }

    public function pdfAction()
    {
        $table = $this->db_manager->get('SbmPaiement\Plugin\Table');
        $criteresObject = [
            '\SbmCommun\Model\Db\ObjectData\Criteres',
            null,
            function ($where) use ($table) {
                $table->adapteWhere($where);
                return $where;
            }
        ];
        $criteresForm = [
            '\SbmCommun\Form\CriteresForm',
            $table->criteres()
        ];
        $documentId = null;
        $retour = [
            'route' => 'sbmpaiement',
            'action' => 'liste'
        ];
        return $this->documentPdf($criteresObject, $criteresForm, $documentId, $retour);
    }

    public function voirAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            if (($notificationId = Session::get('notificationId', false)) === false) {
                return $this->redirect()->toRoute('login', [
                    'action' => 'logout'
                ]);
            }
        } else {
            $args = $prg;
            if (($notificationId = StdLib::getParam('notificationId', $args, false)) ===
                false) {
                return $this->redirect()->toRoute('login', [
                    'action' => 'logout'
                ]);
            } else {
                Session::set('notificationId', $notificationId);
            }
        }
        $table = $this->db_manager->get('SbmPaiement\Plugin\Table');
        return new ViewModel(
            [
                'notification' => $table->getRecord($notificationId),
                'page' => $this->params('page', 1)
            ]);
    }

    public function notificationAction()
    {
        $is_post = $this->getRequest()->isPost();
        $is_get = $this->getRequest()->isGet();
        $message = $is_get || $is_post;
        if ($message) {
            $message = $this->plugin_plateforme->notification($is_post ? 'post' : 'get',
                $is_post ? $this->getRequest()
                    ->getPost() : $this->getRequest()
                    ->getQuery(), $this->getRequest()
                    ->getServer()
                    ->get('REMOTE_ADDR'));
        }
        if ($message === false) {
            return $this->getResponse()
                ->setStatusCode(403)
                ->setContent('Forbidden');
        } else {
            return $this->getResponse()
                ->setContent($message)
                ->setStatusCode(200);
        }
    }

    /**
     * Reçoit en POST soit un idOp, soit un responsableId, soit un eleveId. Interroge la
     * table des appels pour traiter tous les appels non notifiés correspondant à
     * l'attribut trouvé en POST puis interroge le webservice pour mettre éventuellement à
     * jour les paiements. Puis retourne à la page d'où vient cet demande.
     *
     * @return \Zend\Http\PhpEnvironment\Response|\Zend\Http\Response
     */
    public function majnotificationAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            if (($args = Session::get('post', false, $this->getSessionNamespace())) ===
                false) {
            }
        } else {
            $args = $prg;
        }
        if ($args) {
            try {
                $this->plugin_plateforme->majnotification($args);
                $this->flashMessenger()->addSuccessMessage(
                    'Les notifications de paiement en ligne ont été mises à jour.');
            } catch (\Exception $e) {
                $this->flashMessenger()->addErrorMessage('La mise à jour a échoué.');
            }
        }
        $http_referer = $this->getRequest()
            ->getServer()
            ->get('HTTP_REFERER');
        if (array_key_exists('origine', $args)) {
            return $this->redirect()->toUrl($args['origine']);
        } elseif ($http_referer) {
            return $this->redirect()->toUrl($http_referer);
        } else {
            return $this->redirect()->toRoute('login', [
                'action' => 'logout'
            ]);
        }
    }

    /**
     * Charge un fichier csv de transactions remisées. Analyse le fichier (après contrôle)
     * en le rapprochant des paiements enregistrés. Affiche le compte-rendu en indiquant
     * la marche à suivre si des paiements sont absents.
     *
     * @return \Zend\Http\PhpEnvironment\Response
     */
    public function rapprochementAction()
    {
        // formulaire d'upload servant à la fois à la saisie et à la validation
        $tmpuploads = $this->csv['path']['tmpuploads'];
        $form = new Form\UploadXls('upload-form', [
            'tmpuploads' => $tmpuploads
        ]);

        $prg = $this->fileprg($form);
        if ($prg instanceof Response) {
            // renvoie redirection 303 avec le contenu de post en session 'prg_post1'
            // (Expire_Hops = 1)
            return $prg;
        } elseif (is_array($prg) && ! array_key_exists('origine', $prg)) {
            if (array_key_exists('cancel', $prg)) {
                $this->flashMessenger()->addWarningMessage('Abandon de la demande');
                return $this->redirect()->toRoute('sbmpaiement');
            }
            if ($form->isValid()) {
                $cr = $this->plugin_plateforme->rapprochement($form->getData());
                if (empty($cr)) {
                    $this->flashMessenger()->addInfoMessage(
                        'Tous les paiements sont présents.');
                    return $this->redirect()->toRoute('sbmpaiement');
                } else {
                    // construction et envoi d'un CR en pdf pour pouvoir l'imprimer
                    $pdf_doc = new RapprochementCR($cr,
                        $this->plugin_plateforme->rapprochementCrHeader());
                    return $pdf_doc->render_cr();
                }
            }
        } else {
            // ce n'est pas un post. Afficher le formulaire d'upload d'un fichier
            $form->get('xlsfile')->setMessages([]);
            $form->get('firstline')->setMessages([]);
            $form->setData($this->csv['parameters']);
        }
        $view = new ViewModel([
            'form' => $form
        ]);
        $view->setTemplate('sbm-paiement/index/rapprochement-xls.phtml');
        return $view;
    }
}