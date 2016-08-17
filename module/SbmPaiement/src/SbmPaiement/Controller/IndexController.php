<?php
/**
 * Ensemble de pages de retour de la plateforme de paiement
 *
 * Modification le 26 août 2015 dans formulaireAction() pour tenir compte de la dérogation accordée 
 * dans la liste des élèves préinscrits à prendre en compte dans la table `appels` et ajout d'un
 * contrôle du montant du (montant reçu en post = montant à payer pour les eleveIds enregistrés dans `appels`)
 * 
 * @project sbm
 * @package SbmPaiement/Controller
 * @filesource IndexController.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 17 août 2016
 * @version 2016-2.2.0
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
            $responsable = $this->config['responsable']->get();
        } catch (Exception $e) {
            return $this->redirect()->toRoute('login', array(
                'action' => 'logout'
            ));
        }
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            return $this->redirect()->toRoute('sbmparent');
        }
        // prg contient le post array('montant' => ..., 'payer' => ...)
        $args = (array) $prg;
        
        // préparation des données
        $preinscrits = $this->config['db_manager']
            ->get('Sbm\Db\Query\ElevesScolarites')
            ->getElevesPreinscrits($responsable->responsableId);
        $elevesIds = array();
        // ceux qui sont sélectionnés (selectionScolarite : selection dans table scolarites) sont mis en attente. Pas de paiement pour le moment.
        // de même pour ceux qui sont à moins de 1 km et pour ceux qu sont hors district et sans dérogation
        foreach ($preinscrits as $row) {
            if (! $row['selectionScolarite'] && ($row['distanceR1'] >= 1 || $row['distanceR2'] >= 1) && ($row['district'] || $row['derogation'])) {
                $elevesIds[] = $row['eleveId'];
            }
        }
        // vérification du montant
        $montantUnitaire = $this->config['db_manager']
            ->get('Sbm\Db\Table\Tarifs')
            ->getMontant('inscription');
        if ($args['montant'] != $montantUnitaire * count($elevesIds)) {
            $this->flashMessenger()->addErrorMessage('Problème sur le montant à payer. Contactez l\'organisateur.');
            return $this->redirect()->toRoute('login', array(
                'action' => 'home-page'
            ));
        }
        // préparation des paramètres pour la méthode prepareAppel() de la plateforme
        $params = array(
            'montant' => $args['montant'],
            'count' => 1,
            'first' => $args['montant'],
            'period' => 1,
            'email' => $responsable->email,
            'responsableId' => $responsable->responsableId,
            'nom' => $responsable->nom,
            'prenom' => $responsable->prenom,
            'eleveIds' => $elevesIds
        );
        $objectPlateforme = $this->config['plugin_plateforme'];
        $args = $objectPlateforme->prepareAppel($params);
        
        // enregistrement de l'appel à paiement
        $id = $objectPlateforme->getUniqueId($args);
        $tAppels = $this->config['db_manager']->get('Sbm\Db\Table\Appels');
        $odata = $tAppels->getObjData();
        foreach ($elevesIds as $eleveId) {
            $odata->exchangeArray(array(
                'referenceId' => $id,
                'responsableId' => $responsable->responsableId,
                'eleveId' => $eleveId
            ));
            $tAppels->saveRecord($odata);
        }
        
        // préparation du formulaire
        $form = new \Zend\Form\Form('plugin-formulaire');
        foreach ($args as $key => $value) {
            $form->add(array(
                'type' => 'hidden',
                'name' => $key,
                'attributes' => array(
                    'value' => $value
                )
            ));
        }
        $form->setAttribute('action', $objectPlateforme->getUrl());
        return new ViewModel(array(
            'form' => $form
        ));
    }

    public function listeAction()
    {
        $table = $this->config['db_manager']->get('SbmPaiement\Plugin\Table');
        $args = $this->initListe($table->criteres());
        if ($args instanceof Response)
            return $args;
        $order = $table->getIdName() . ' DESC';
        if (method_exists($table, 'adapteWhere')) {
            $table->adapteWhere($args['where']);
        }
        return new ViewModel(array(
            'paginator' => $table->paginator($args['where'], $order),
            'page' => $this->params('page', 1),
            'count_per_page' => $this->getPaginatorCountPerPage('nb_notifications', 15),
            'criteres_form' => $args['form']
        ));
    }

    public function pdfAction()
    {
        $table = $this->config['db_manager']->get('SbmPaiement\Plugin\Table');
        $criteresObject = array(
            '\SbmCommun\Model\Db\ObjectData\Criteres',
            null,
            function ($where) use($table) {
                $table->adapteWhere($where);
                return $where;
            }
        );
        $criteresForm = array(
            '\SbmCommun\Form\CriteresForm',
            $table->criteres()
        );
        $documentId = null;
        $retour = array(
            'route' => 'sbmpaiement',
            'action' => 'liste'
        );
        return $this->documentPdf($criteresObject, $criteresForm, $documentId, $retour);
    }

    public function voirAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            if (($notificationId = $this->getFromSession('notificationId', false)) === false) {
                return $this->redirect()->toRoute('login', array(
                    'action' => 'logout'
                ));
            }
        } else {
            $args = $prg;
            if (($notificationId = StdLib::getParam('notificationId', $args, false)) === false) {
                return $this->redirect()->toRoute('login', array(
                    'action' => 'logout'
                ));
            } else {
                $this->setToSession('notificationId', $notificationId);
            }
        }
        $table = $this->config['db_manager']->get('SbmPaiement\Plugin\Table');
        return new ViewModel(array(
            'notification' => $table->getRecord($notificationId),
            'page' => $this->params('page', 1)
        ));
    }

    public function notificationAction()
    {
        $plugin = $this->config['plugin_plateforme'];
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
}