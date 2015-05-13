<?php
/**
 * Ensemble de pages de retour de la plateforme de paiement
 *
 * Description longue du fichier s'il y en a une
 * 
 * @project sbm
 * @package SbmPaiement/Controller
 * @filesource IndexController.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 31 mars 2015
 * @version 2015-1
 */
namespace SbmPaiement\Controller;

use SbmCommun\Model\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Http\PhpEnvironment\Response;
use SbmCommun\Model\StdLib;
use Zend\View\Model\Zend\View\Model;
use SbmParent\Model\Responsable;

class IndexController extends AbstractActionController
{

    public function formulaireAction()
    {
        try {
            $responsable = new Responsable($this->getServiceLocator());
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
        // prg contient le post
        $args = (array) $prg;
        // $args = array('montant' => ..., 'payer' => ...)
        $preinscrits = $this->getServiceLocator()
            ->get('Sbm\Db\Query\ElevesScolarites')
            ->getElevesPreinscrits($responsable->responsableId);
        $elevesIds = array();
        foreach ($preinscrits as $row) {
            if (! $row['selectionScolarite']) {
                $elevesIds[] = $row['eleveId'];
            }
        }
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
        $objectPlateforme = $this->getServiceLocator()->get('SbmPaiement\Plugin\Plateforme');
        $args = $objectPlateforme->prepareAppel($params);
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
        $table = $this->getServiceLocator()->get('SbmPaiement\Plugin\Table');
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
            'nb_pagination' => $this->getNbPagination('nb_paiements', 15),
            'criteres_form' => $args['form']
        ));
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
        $table = $this->getServiceLocator()->get('SbmPaiement\Plugin\Table');
        return new ViewModel(array(
            'notification' => $table->getRecord($notificationId),
            'page' => $this->params('page', 1)
        ));
    }

    public function notificationAction()
    {
        $plugin = $this->getServiceLocator()->get('SbmPaiement\Plugin\Plateforme');
        $message = $plugin->notification($this->getRequest()
            ->getPost(), $this->getRequest()
            ->getServer()
            ->get('REMOTE_ADDR'));
        if ($message === false) {
            $config = $this->getServiceLocator()->get('Config');
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