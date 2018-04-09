<?php
/**
 * Ensemble de pages de retour de la plateforme de paiement
 *
 * Modification le 26 août 2015 dans formulaireAction() pour tenir compte de la dérogation accordée 
 * dans la liste des élèves préinscrits à prendre en compte dans la table `appels` et ajout d'un
 * contrôle du montant du (montant reçu en post = montant à payer pour les eleveIds enregistrés dans `appels`)
 * 
 * Modification le 10 mars 2017 dans formulaireAction() pour tenir compte de la tarification anneeComplete
 * ou 3eme trimestre. Changement de style des array().
 * 
 * @project sbm
 * @package SbmPaiement/Controller
 * @filesource IndexController.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 5 avr. 2018
 * @version 2018-2.4.0
 */
namespace SbmPaiement\Controller;

use SbmCommun\Model\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Http\PhpEnvironment\Response;
use SbmBase\Model\StdLib;
use Zend\View\Model\Zend\View\Model;
use SbmFront\Model\Responsable\Responsable;

class IndexController extends AbstractActionController
{

    public function formulaireAction()
    {
        try {
            $responsable = $this->responsable->get();
        } catch (\Exception $e) {
            return $this->redirect()->toRoute('login', 
                [
                    'action' => 'logout'
                ]);
        }
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            return $this->redirect()->toRoute('sbmparent');
        }
        // prg contient le post ['montant' => ..., 'payer' => ...)
        $args = (array) $prg;
        
        // préparation des données
        $preinscrits = $this->db_manager->get('Sbm\Db\Query\ElevesScolarites')->getElevesPreinscrits(
            $responsable->responsableId);
        $elevesIds = [];
        /**
         * VERSION 2.3.1
         */
        $eleveIdsAnneeComplete = [];
        $eleveIds3emeTrimestre = [];
        /**
         * Fin de l'ajout VERSION 2.3.1
         */
        // ceux qui sont sélectionnés (selectionScolarite : selection dans table scolarites) sont mis en attente. Pas de paiement pour le moment.
        // de même pour ceux qui sont à moins de 1 km et pour ceux qu sont hors district et sans dérogation
        foreach ($preinscrits as $row) {
            if (! $row['selectionScolarite'] &&
                 ($row['distanceR1'] >= 1 || $row['distanceR2'] >= 1) &&
                 ($row['district'] || $row['derogation'])) {
                /**
                 * VERSION 2.3.1
                 */
                if ($row['anneeComplete']) {
                    $eleveIdsAnneeComplete[] = $row['eleveId'];
                } else {
                    $eleveIds3emeTrimestre[] = $row['eleveId'];
                }
                /**
                 * Fin de l'ajout VERSION 2.3.1
                 */
                $elevesIds[] = $row['eleveId'];
            }
        }
        // vérification du montant
        /*
         * ANCIENNE VERSION
         * $montantUnitaire = $this->db_manager
         * ->get('Sbm\Db\Table\Tarifs')
         * ->getMontant('inscription');
         * if ($args['montant'] != $montantUnitaire * count($elevesIds)) {
         * $this->flashMessenger()->addErrorMessage('Problème sur le montant à payer. Contactez l\'organisateur.');
         * return $this->redirect()->toRoute('login', [
         * 'action' => 'home-page'
         * ));
         * }
         *
         * NOUVELLE VERSION 2.3.1
         */
        $tTarifs = $this->db_manager->get('Sbm\Db\Table\Tarifs');
        $tarif1 = $tTarifs->getMontant('tarif1');
        $tarif2 = $tTarifs->getMontant('tarif2');
        $nbElvTarif1 = count($eleveIdsAnneeComplete);
        $nbElvTarif2 = count($eleveIds3emeTrimestre);
        if ($args['montant'] != $tarif1 * $nbElvTarif1 + $tarif2 * $nbElvTarif2) {
            $this->flashMessenger()->addErrorMessage(
                'Problème sur le montant à payer. Contactez l\'organisateur.');
            return $this->redirect()->toRoute('login', 
                [
                    'action' => 'home-page'
                ]);
        }
        /**
         * Fin de l'ajout VERSION 2.3.1
         */
        // préparation des paramètres pour la méthode prepareAppel() de la plateforme
        $params = [
            'montant' => $args['montant'],
            'count' => 1,
            'first' => $args['montant'],
            'period' => 1,
            'email' => $responsable->email,
            'responsableId' => $responsable->responsableId,
            'nom' => $responsable->nom,
            'prenom' => $responsable->prenom,
            'eleveIds' => $elevesIds
        ];
        $objectPlateforme = $this->plugin_plateforme;
        $args = $objectPlateforme->prepareAppel($params);
        
        // enregistrement de l'appel à paiement
        $id = $objectPlateforme->getUniqueId($args);
        $tAppels = $this->db_manager->get('Sbm\Db\Table\Appels');
        $odata = $tAppels->getObjData();
        foreach ($elevesIds as $eleveId) {
            $odata->exchangeArray(
                [
                    'referenceId' => $id,
                    'responsableId' => $responsable->responsableId,
                    'eleveId' => $eleveId
                ]);
            $tAppels->saveRecord($odata);
        }
        
        // préparation du formulaire
        $form = new \Zend\Form\Form('plugin-formulaire');
        foreach ($args as $key => $value) {
            $form->add(
                [
                    'type' => 'hidden',
                    'name' => $key,
                    'attributes' => [
                        'value' => $value
                    ]
                ]);
        }
        $form->setAttribute('action', $objectPlateforme->getUrl());
        return new ViewModel([
            'form' => $form
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
                'count_per_page' => $this->getPaginatorCountPerPage('nb_notifications', 
                    15),
                'criteres_form' => $args['form']
            ]);
    }

    public function pdfAction()
    {
        $table = $this->db_manager->get('SbmPaiement\Plugin\Table');
        $criteresObject = [
            '\SbmCommun\Model\Db\ObjectData\Criteres',
            null,
            function ($where) use($table) {
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
            if (($notificationId = $this->getFromSession('notificationId', false)) ===
                 false) {
                return $this->redirect()->toRoute('login', 
                    [
                        'action' => 'logout'
                    ]);
            }
        } else {
            $args = $prg;
            if (($notificationId = StdLib::getParam('notificationId', $args, false)) ===
                 false) {
                return $this->redirect()->toRoute('login', 
                    [
                        'action' => 'logout'
                    ]);
            } else {
                $this->setToSession('notificationId', $notificationId);
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
            ->getPost(), 
            $this->getRequest()
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
}