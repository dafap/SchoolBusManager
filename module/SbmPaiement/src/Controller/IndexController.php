<?php
/**
 * Ensemble de pages de retour de la plateforme de paiement
 *
 * Modification le 26 août 2015 dans formulaireAction() pour tenir compte de la dérogation accordée
 * dans la liste des élèves préinscrits à prendre en compte dans la table `appels` et ajout d'un
 * contrôle du montant du (montant reçu en post = montant à payer pour les eleveIds enregistrés dans `appels`)
 *
 * Modification le 10 mars 2017 dans formulaireAction() pour tenir compte de la tarification anneeComplete
 * ou 3eme trimestre. Changement de style des[].
 *
 * @project sbm
 * @package SbmPaiement/Controller
 * @filesource IndexController.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 7 oct. 2018
 * @version 2019-2.5.0
 */
namespace SbmPaiement\Controller;

use SbmBase\Model\Session;
use SbmBase\Model\StdLib;
use SbmCommun\Model\Mvc\Controller\AbstractActionController;
use SbmPaiement\Form\UploadCsv;
use SbmPaiement\Model\RapprochementCR;
use Zend\Http\PhpEnvironment\Response;
use Zend\View\Model\ViewModel;

class IndexController extends AbstractActionController
{

    /**
     * Dans cette version, le montant à payer n'est pas passé par le POST (c'est un
     * leurre) mais est obtenu à partir de la facture émise ou récupérée si elle existe
     * déjà. Le montant à payer est le solde de la facture.
     *
     * @return \Zend\Http\Response|\Zend\Http\PhpEnvironment\Response|\Zend\View\Model\ViewModel
     */
    public function formulaireAction()
    {
        try {
            $responsable = $this->responsable->get();
            $responsableId = $this->getResponsableIdFromSession('nsArgsFacture');
            // génère une facture ou la récupère si elle existe déjà
            $facture = new \SbmCommun\Model\Paiements\Facture($this->db_manager,
                $this->db_manager->get(
                    \SbmCommun\Model\Db\Service\Query\Paiement\Calculs::class)->getResultats(
                    $responsableId));
        } catch (\Exception $e) {
            return $this->redirect()->toRoute('login', [
                'action' => 'logout'
            ]);
        }
        // préparation des paramètres pour la méthode getForm() de la plateforme
        $elevesIds = [];
        foreach ($facture->getResultats()->getListeEleves() as $eleveId => $row) {
            if (! $row['paiement']) {
                $elevesIds[] = $eleveId;
            }
        }
        $params = [
            'montant' => $facture->getResultats()->getSolde(),
            'count' => 1,
            'first' => $facture->getResultats()->getSolde(),
            'period' => 1,
            'email' => $responsable->email,
            'responsableId' => $responsable->responsableId,
            'nom' => $responsable->nom,
            'prenom' => $responsable->prenom,
            'eleveIds' => $elevesIds
        ];
        // refactoring à partir de la version 2.4.5 pour renvoyer tout ce qui est
        // spécifique à une
        // plateforme dans son plugin. L'enregistrement de la demande d'appel dans la
        // table
        // `appels`, nécessitant un id spécifique au plugin, est réalisé dans le plugin.
        $objectPlateforme = $this->plugin_plateforme;
        return new ViewModel([
            'form' => $objectPlateforme->getForm($params)
        ]);
    }

    public function listeAction()
    {
        $table = $this->db_manager->get('SbmPaiement\Plugin\Table');
        $args = $this->initListe($table->criteres());
        if ($args instanceof Response)
            return $args;
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
        $plugin = $this->plugin_plateforme;
        $message = $plugin->notification($this->getRequest()
            ->getPost(), $this->getRequest()
            ->getServer()
            ->get('REMOTE_ADDR'));
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
        $form = new UploadCsv('upload-form', [
            'tmpuploads' => $tmpuploads
        ]);

        $prg = $this->fileprg($form);
        if ($prg instanceof Response) {
            // renvoie redirection 303 avec le contenu de post en session 'prg_post1'
            // (Expire_Hops
            // = 1)
            return $prg;
        } elseif (is_array($prg) && ! array_key_exists('origine', $prg)) {
            if (array_key_exists('cancel', $prg)) {
                $this->flashMessenger()->addWarningMessage('Abandon de la demande');
                return $this->redirect()->toRoute('sbmpaiement');
            }
            if ($form->isValid()) {
                $data = $form->getData();
                $cr = $this->plugin_plateforme->rapprochement(
                    $data['csvfile']['tmp_name'], $data['firstline'], $data['separator'],
                    $data['enclosure'], $data['escape']);
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
            $form->get('csvfile')->setMessages([]);
            $form->get('firstline')->setMessages([]);
            $form->setData($this->csv['parameters']);
        }
        $view = new ViewModel([
            'form' => $form
        ]);
        return $view;
    }
}