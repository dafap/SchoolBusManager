<?php
/**
 * Controller du module SbmParent permettant de gérer le compte de l'utilisateur et de revenir dans l'espace des parents
 *
 * @project sbm
 * @package SbmParent/Controller
 * @filesource ConfigController.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 7 avr. 2015
 * @version 2015-1
 */
namespace SbmParent\Controller;

use SbmCommun\Model\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use SbmCommun\Model\Db\DbLib;
use SbmCommun\Form\Responsable as FormResponsable;
use SbmParent\Model\Responsable;
use Zend\Http\PhpEnvironment\Response;
use SbmCommun\Model\StdLib;

class ConfigController extends AbstractActionController
{

    public function indexAction()
    {
        return new ViewModel();
    }

    public function modifCompteAction()
    {
        try {
            $responsable = new Responsable($this->getServiceLocator());
        } catch (Exception $e) {
            return $this->redirect()->toRoute('login', array('action' => 'logout'));
        }
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            // initialisation du formulaire à partir de l'identité de l'utilisateur autentifié
            $args = $responsable->getArrayCopy();
        } else {
            $args = $prg;
            if (array_key_exists('cancel', $args)) {
                return $this->redirect()->toRoute('login', array('action' => 'home-page'));
            }
        }
        // ici on a le tableau d'initialisation du formulaire dans $args
        $responsableId = $args['responsableId'];
        $tableResponsables = $this->getServiceLocator()->get('Sbm\Db\Table\Responsables');
        $db = $this->getServiceLocator()->get('Sbm\Db\DbLib');
        // on ouvre le formulaire avec l'identité verrouillée et on l'adapte
        $form = new FormResponsable();
        $value_options = $this->getServiceLocator()->get('Sbm\Db\Select\CommunesDesservies');
        $form->setValueOptions('communeId', $value_options)
            ->setValueOptions('ancienCommuneId', $value_options)
            ->setMaxLength($db->getMaxLengthArray('responsables', 'table'));
        unset($value_options);
        
        $form->bind($tableResponsables->getObjData());
        $form->setData($args);
        if (array_key_exists('submit', $args)) {            
            if ($form->isValid()) {
                // controle le csrf et contrôle les datas
                $tableResponsables->saveRecord($form->getData());
                $responsable->refresh();
                return $this->redirect()->toRoute('login', array('action' => 'synchro-compte'));
            }
            $this->flashMessenger()->addWarningMessage('Données invalides');
        }
        return new ViewModel(array(
            'form' => $form,
            'responsableId' => $responsableId,
            'demenagement' => StdLib::getParam('demenagement', $args, false)
        ));
    }

    public function mdpChangeAction()
    {
        $retour = $this->url()->fromRoute('sbmparent');
        return $this->redirect()->toRoute('login', array(
            'action' => 'mdp-change'
        ));
    }

    public function emailChangeAction()
    {
        $retour = $this->url()->fromRoute('sbmparent');
        return $this->redirect()->toRoute('login', array(
            'action' => 'email-change'
        ));
    }

    /**
     * Le retour se fait par un redirectToOrigin()->back() ce qui veut dire qu'il faut avoir défini le redirectToOrigin()
     * avant l'appel.
     * 
     * @return \Zend\Http\PhpEnvironment\Response|\Zend\View\Model\ViewModel
     */
    public function createAction()
    {
        $identity = $this->getServiceLocator()
            ->get('Sbm\Authenticate')
            ->getIdentity();
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            // initialisation du formulaire à partir de l'identité de l'utilisateur autentifié
            $args = $identity;
        } else {
            $args = $prg;
        }
        // ici on a le tableau d'initialisation du formulaire dans $args
        $responsableId = null;
        $tableResponsables = $this->getServiceLocator()->get('Sbm\Db\Table\Responsables');
        $db = $this->getServiceLocator()->get('Sbm\Db\DbLib');
        // on ouvre le formulaire avec l'identité verrouillée et on l'adapte
        $form = new FormResponsable(true);
        $value_options = $this->getServiceLocator()->get('Sbm\Db\Select\CommunesDesservies');
        $form->setValueOptions('communeId', $value_options)
            ->setValueOptions('ancienCommuneId', $value_options)
            ->setMaxLength($db->getMaxLengthArray('responsables', 'table'));
        unset($value_options);
        
        $form->bind($tableResponsables->getObjData());
        $form->setData($args);
        if (array_key_exists('submit', $args)) {            
            if ($form->isValid()) {
                // controle le csrf et contrôle les datas
                $tableResponsables->saveRecord($form->getData());
                $this->flashMessenger()->addSuccessMessage("La fiche a été enregistrée.");
                return $this->redirectToOrigin()->back();
            }
            $this->flashMessenger()->addWarningMessage('Données invalides');
        }
        return new ViewModel(array(
            'form' => $form,
            'responsableId' => $responsableId,
            'demenagement' => false
        ));
    }
} 