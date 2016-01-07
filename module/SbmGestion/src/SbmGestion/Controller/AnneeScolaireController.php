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

use Zend\View\Model\ViewModel;
use Zend\Http\PhpEnvironment\Response;
use Zend\Db\Sql\Where;
use SbmCommun\Model\Mvc\Controller\AbstractActionController;
use SbmCommun\Model\Db\DbLib;
use SbmCommun\Form\Calendar as FormCalendar;
use SbmCommun\Form\ButtonForm;
use SbmGestion\Form\Simulation;

class AnneeScolaireController extends AbstractActionController
{

    const SIMULATION = 2000;

    public function indexAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        }
        $simulation_vide = $this->getServiceLocator()
            ->get('Sbm\Db\Table\Scolarites')
            ->isEmptyMillesime(self::SIMULATION) && $this->getServiceLocator()
            ->get('Sbm\Db\Table\Circuits')
            ->isEmptyMillesime(self::SIMULATION);
        
        return new ViewModel(array(
            'anneesScolaires' => $this->getServiceLocator()
                ->get('Sbm\Db\System\Calendar')
                ->getAnneesScolaires(),
            'millesimeActif' => $this->getFromSession('millesime', false),
            'simulation_millesime' => self::SIMULATION,
            'simulation_vide' => $simulation_vide
        ));
    }

    public function activeAction()
    {
        $millesime = $this->params('millesime', 0);
        if (! empty($millesime)) {
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
        if (! array_key_exists('millesime', $prg) || ! array_key_exists('ouvert', $prg)) {
            return $this->redirect()->toRoute('sbmgestion/anneescolaire');
        }
        $tCalendar = $this->getServiceLocator()->get('Sbm\Db\System\Calendar');
        if ($tCalendar->changeEtat($prg['millesime'], $prg['ouvert'])) {
            $this->flashMessenger()->addSuccessMessage('L\'état de cette année scolaire a été modifié.');
        } else {
            $this->flashMessenger()->addErrorMessage('Impossible de modifier l\'état de cette année scolaire.');
        }
        return $this->redirect()->toRoute('sbmgestion/anneescolaire', array(
            'action' => 'voir',
            'millesime' => $prg['millesime']
        ));
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

    public function simulationPreparerAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        }
        $form = new Simulation();
        if ($prg !== false) {
            if (array_key_exists('cancel', $prg)) {
                $this->flashMessenger()->addInfoMessage('Aucun changement');
                return $this->redirect()->toRoute('sbmgestion/anneescolaire');
            }
            if (array_key_exists('submit', $prg) && array_key_exists('millesime', $prg)) {
                $form->setData($prg);
                if ($form->isValid()) {
                    $millesime = $prg['millesime'];
                    $this->getServiceLocator()
                        ->get('Sbm\Db\Simulation\Prepare')
                        ->duplicateCircuits($millesime, self::SIMULATION)
                        ->duplicateEleves($millesime, self::SIMULATION);
                    $this->flashMessenger()->addSuccessMessage("La simulation a été préparée à partir de l'année $millesime.");
                    return $this->redirect()->toRoute('sbmgestion/anneescolaire');
                }
            }
        }
        $where1 = new Where();
        $where1->isNotNull('suivantId');
        $where2 = new Where();
        $where2->isNull('suivantId');
        return new ViewModel(array(
            'repris' => $this->getServiceLocator()
                ->get('Sbm\Db\Vue\Classes')
                ->fetchAll($where1, array(
                'niveau',
                'nom'
            )),
            'non_repris' => $this->getServiceLocator()
                ->get('Sbm\Db\Vue\Classes')
                ->fetchAll($where2, array(
                'niveau',
                'nom'
            )),
            'form' => $form->prepare()
        ));
    }

    public function simulationViderAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        }
        $form = new ButtonForm(array(
            'id' => null
        ), array(
            'supproui' => array(
                'class' => 'confirm default',
                'value' => 'Confirmer'
            ),
            'supprnon' => array(
                'class' => 'confirm default',
                'value' => 'Abandonner'
            )
        ));
        if ($prg !== false) {
            if (array_key_exists('supproui', $prg)) {
                $this->getServiceLocator()
                    ->get('Sbm\Db\Table\Affectations')
                    ->viderMillesime(self::SIMULATION);
                $this->getServiceLocator()
                    ->get('Sbm\Db\Table\Scolarites')
                    ->viderMillesime(self::SIMULATION);
                $this->getServiceLocator()
                    ->get('Sbm\Db\Table\Circuits')
                    ->viderMillesime(self::SIMULATION);
                $this->flashMessenger()->addSuccessMessage('La simulation a été effacée.');
            }
            return $this->redirect()->toRoute('sbmgestion/anneescolaire');
        }
        return new ViewModel(array(
            'form' => $form
        ));
    }
}