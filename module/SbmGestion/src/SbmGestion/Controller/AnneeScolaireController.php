<?php
/**
 * Controller principal du module SbmGestion
 * Méthodes utilisées pour la gestion de l'année scolaire
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

use SbmCommun\Model\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use SbmCommun\Model\Db\DbLib;
use SbmCommun\Form\Calendar as FormCalendar;
use Zend\Http\PhpEnvironment\Response;

class AnneeScolaireController extends AbstractActionController
{

    public function indexAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        }
        $table_calendar = $this->getServiceLocator()->get('Sbm\Db\System\Calendar');
        return new ViewModel(array(
            'anneesScolaires' => $table_calendar->getAnneesScolaires(),
            'millesimeActif' => $this->getFromSession('millesime', false)
        ));
    }

    public function activeAction()
    {
        $millesime = $this->params('millesime', 0);
        if (!empty($millesime)) {
            $this->setToSession('millesime', $millesime);
        }
        $this->flashMessenger()->addSuccessMessage('L\'année active a changé.');
        return $this->redirect()->toRoute('sbmgestion/anneescolaire');
    }

    public function editAction()
    {
        $calendarId = $this->params('id', 0);
        $millesime = $this->params('millesime', 0);
        if (empty($calendarId) || empty($millesime)) {
            return $this->redirect()->toRoute('sbmgestion/anneescolaire');
        }
        
        $table_calendar = $this->getServiceLocator()->get('Sbm\Db\System\Calendar');
        $db = $this->getServiceLocator()->get('Sbm\Db\DbLib');
        
        $form = new FormCalendar();
        $form->setMaxLength($db->getMaxLengthArray('calendar', 'system'));
        $form->bind($table_calendar->getObjData());
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($request->getPost('cancel', false)) {
                $this->flashMessenger()->addWarningMessage("L'enregistrement n'a pas été modifié.");
                return $this->redirect()->toRoute('sbmgestion/anneescolaire', array(
                    'action' => 'voir',
                    'millesime' => $millesime
                ));
            }
            $form->setData($request->getPost());
            if ($form->isValid()) { // controle le csrf
                $table_calendar->saveRecord($form->getData());
                $this->flashMessenger()->addSuccessMessage("Les modifications ont été enregistrées.");
                return $this->redirect()->toRoute('sbmgestion/anneescolaire', array(
                    'action' => 'voir',
                    'millesime' => $millesime
                ));
            }
            $data = $request->getPost();
        } else {
            $data = $table_calendar->getRecord($calendarId)->getArrayCopy();
            $form->setData($data);
        }
        return new ViewModel(array(
            'form' => $form->prepare(),
            'millesime' => $millesime,
            'calendarId' => $calendarId,
            'data' => $data
        ));
    }

    public function voirAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        }
        $millesime = $this->params('millesime', 0);
        if (empty($millesime)) {
            return $this->redirect()->toRoute('sbmgestion/anneescolaire');
        }
        $as_libelle = sprintf("%s-%s", $millesime, $millesime + 1);
        $table_calendar = $this->getServiceLocator()->get('Sbm\Db\System\Calendar');
        $auth = $this->getServiceLocator()
        ->get('Dafap\Authenticate')
        ->by('email');
        return new ViewModel(array(
            'as_libelle' => $as_libelle,
            'millesime' => $millesime,
            'table' => $table_calendar->getMillesime($millesime),
            'admin' => $auth->getCategorieId() > 253
        ));
    }

    public function ouvrirAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        }
        if (!array_key_exists('millesime', $prg) || !array_key_exists('ouvert', $prg)) {
            return $this->redirect()->toRoute('sbmgestion/anneescolaire');
        }
        $tCalendar = $this->getServiceLocator()->get('Sbm\Db\System\Calendar');
        if ($tCalendar->changeEtat($prg['millesime'], $prg['ouvert'])) {
            $this->flashMessenger()->addSuccessMessage('L\'état de cette année scolaire a été modifié.');
        } else {
            $this->flashMessenger()->addErrorMessage('Impossible de modifier l\'état de cette année scolaire.');
        }
        return $this->redirect()->toRoute('sbmgestion/anneescolaire', array('action' => 'voir', 'millesime' => $prg['millesime']));
    }
    
    public function newAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        }
        $config = include __DIR__ . '/../Model/Modele.inc.php';
        $config = $config['annee-scolaire'];
        
        $table_calendar = $this->getServiceLocator()->get('Sbm\Db\System\Calendar');
        $millesime = $table_calendar->getDernierMillesime();
        if ($table_calendar->isValidMillesime($millesime)) {
            $millesime ++;
            $as_libelle = sprintf("%s-%s", $millesime, $millesime + 1);
            
            $data = $this->getServiceLocator()->get('Sbm\Db\SysObjectData\Calendar');
            foreach ($config['modele'] as $element) {
                $data->exchangeArray(array(
                    'calendarId' => null,
                    'millesime' => $millesime,
                    'ordinal' => $element['ordinal'],
                    'nature' => $element['nature'],
                    'rang' => $element['rang'],
                    'libelle' => str_replace('%as%', $as_libelle, $element['libelle']),
                    'description' => str_replace(array(
                        '%as%',
                        '%libelle%'
                    ), array(
                        $as_libelle,
                        $element['libelle']
                    ), $element['description']),
                    'dateDebut' => NULL,
                    'dateFin' => NULL,
                    'echeance' => NULL,
                    'exercice' => str_replace(array(
                        '%ex1%',
                        '%ex2%'
                    ), array(
                        $millesime,
                        $millesime + 1
                    ), $element['exercice'])
                ));
                $table_calendar->saveRecord($data);
            }
        } else {
            $as_libelle = sprintf("%s-%s", $millesime, $millesime + 1);
        }
        
        $viewmodel = new ViewModel(array(
            'as_libelle' => $as_libelle,
            'millesime' => $millesime,
            'table' => $table_calendar->getMillesime($millesime)
        ));
        $viewmodel->setTemplate('sbm-gestion/annee-scolaire/voir.phtml');
        return $viewmodel;
    }
}