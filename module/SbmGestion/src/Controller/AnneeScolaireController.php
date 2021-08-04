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
 * @date 2 août 2021
 * @version 2021-2.5.14
 */
namespace SbmGestion\Controller;

use SbmBase\Model\Session;
use SbmBase\Model\StdLib;
use SbmCommun\Form;
use SbmCommun\Model\Mvc\Controller\AbstractActionController;
use SbmGestion\Form\Simulation;
use Zend\Db\Sql\Where;
use Zend\Http\PhpEnvironment\Response;
use Zend\View\Model\ViewModel;

/**
 *
 * @property \SbmCommun\Model\Db\Service\DbManager $db_manager
 * @property \SbmCommun\Model\Service\FormManager $form_manager
 * @property \SbmCartographie\Model\Service\CartographieManager $cartographie_manager
 * @property \SbmAuthentification\Authentication\AuthenticationServiceFactory $authenticate
 * @property array $mail_config
 * @property array $paginator_count_per_page
 *
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 *
 */
class AnneeScolaireController extends AbstractActionController
{

    const SIMULATION = 2000;

    public function indexAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        }
        $simulation_vide = $this->db_manager->get('Sbm\Db\Table\Scolarites')->isEmptyMillesime(
            self::SIMULATION) &&
            $this->db_manager->get('Sbm\Db\Table\Circuits')->isEmptyMillesime(
                self::SIMULATION);

        return new ViewModel(
            [
                'anneesScolaires' => $this->db_manager->get('Sbm\Db\System\Calendar')->getAnneesScolaires(),
                'millesimeActif' => Session::get('millesime', false),
                'simulation_millesime' => self::SIMULATION,
                'simulation_vide' => $simulation_vide
            ]);
    }

    public function activeAction()
    {
        $millesime = $this->params('millesime', 0);
        if (! empty($millesime)) {
            Session::set('millesime', $millesime);
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

        $table_calendar = $this->db_manager->get('Sbm\Db\System\Calendar');

        $form = $this->form_manager->get(Form\Calendar::class);
        $form->setMaxLength($this->db_manager->getMaxLengthArray('calendar', 'system'));
        $form->bind($table_calendar->getObjData());

        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($request->getPost('cancel', false)) {
                $this->flashMessenger()->addWarningMessage(
                    "L'enregistrement n'a pas été modifié.");
                return $this->redirect()->toRoute('sbmgestion/anneescolaire',
                    [
                        'action' => 'voir',
                        'millesime' => $millesime
                    ]);
            }
            // compléter les dates si nécessaire
            $args = $request->getPost();
            if (empty($args['dateFin']['day']) || empty($args['dateFin']['month']) ||
                empty($args['dateFin']['year'])) {
                $args['dateFin'] = $args['dateDebut'];
            }
            if (empty($args['echeance']['day']) || empty($args['echeance']['month']) ||
                empty($args['echeance']['year'])) {
                $args['echeance'] = $args['dateFin'];
            }
            // validation des données
            $form->setData($args);
            if ($form->isValid()) { // controle le csrf
                $table_calendar->saveRecord($form->getData());
                $this->flashMessenger()->addSuccessMessage(
                    "Les modifications ont été enregistrées.");
                return $this->redirect()->toRoute('sbmgestion/anneescolaire',
                    [
                        'action' => 'voir',
                        'millesime' => $millesime
                    ]);
            }
            $data = $request->getPost();
        } else {
            $data = $table_calendar->getRecord($calendarId)->getArrayCopy();
            $form->setData($data);
        }
        return new ViewModel(
            [
                'form' => $form->prepare(),
                'millesime' => $millesime,
                'calendarId' => $calendarId,
                'data' => $data
            ]);
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
        $table_calendar = $this->db_manager->get('Sbm\Db\System\Calendar');
        $auth = $this->authenticate->by('email');
        // on cherche si ce millesime a déjà des circuits enregistrés
        $tCircuits = $this->db_manager->get('Sbm\Db\Table\Circuits');
        $resultset = $tCircuits->fetchAll([
            'millesime' => $millesime
        ]);
        $circuitsVides = $resultset->count() == 0;
        return new ViewModel(
            [
                'as_libelle' => $as_libelle,
                'millesime' => $millesime,
                'table' => $table_calendar->getMillesime($millesime),
                'admin' => $auth->getCategorieId() > 253,
                'circuitsVides' => $circuitsVides
            ]);
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
        $tCalendar = $this->db_manager->get('Sbm\Db\System\Calendar');
        if ($tCalendar->changeEtat($prg['millesime'], $prg['ouvert'])) {
            $this->flashMessenger()->addSuccessMessage(
                'L\'état de cette année scolaire a été modifié.');
        } else {
            $this->flashMessenger()->addErrorMessage(
                'Impossible de modifier l\'état de cette année scolaire.');
        }
        return $this->redirect()->toRoute('sbmgestion/anneescolaire',
            [
                'action' => 'voir',
                'millesime' => $prg['millesime']
            ]);
    }

    public function newAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        }
        $theme = new \SbmInstallation\Model\Theme();
        $config = include StdLib::concatPath($theme->getThemeConfigFolder(),
            'calendar.config.php');
        $tCalendar = $this->db_manager->get('Sbm\Db\System\Calendar');
        $tCommunes = $this->db_manager->get('Sbm\Db\Table\Communes');
        $millesime = $tCalendar->getDernierMillesime();
        if ($tCalendar->isValidMillesime($millesime)) {
            $millesime ++;
            $as_libelle = sprintf("%s-%s", $millesime, $millesime + 1);
            $data = $this->db_manager->get('Sbm\Db\SysObjectData\Calendar');
            foreach ($config as $element) {
                if ($element['nature'] == 'PERM') {
                    $commune = $tCommunes->getRecord($element['libelle']);
                    $element['libelle'] = sprintf('Permanence pour %s', $commune->nom);
                    $element['description'] = $commune->nom_min;
                }
                $data->exchangeArray(
                    [
                        'calendarId' => null,
                        'millesime' => $millesime,
                        'ordinal' => $element['ordinal'],
                        'nature' => $element['nature'],
                        'rang' => $element['rang'],
                        'libelle' => str_replace('%as%', $as_libelle, $element['libelle']),
                        'description' => str_replace([
                            '%as%',
                            '%libelle%'
                        ], [
                            $as_libelle,
                            $element['libelle']
                        ], $element['description']),
                        'dateDebut' => NULL,
                        'dateFin' => NULL,
                        'echeance' => NULL,
                        'exercice' => str_replace([
                            '%ex1%',
                            '%ex2%'
                        ], [
                            $millesime,
                            $millesime + 1
                        ], $element['exercice'])
                    ]);
                $tCalendar->saveRecord($data);
            }
        } else {
            $as_libelle = sprintf("%s-%s", $millesime, $millesime + 1);
        }

        $viewmodel = new ViewModel(
            [
                'as_libelle' => $as_libelle,
                'millesime' => $millesime,
                'table' => $tCalendar->getMillesime($millesime)
            ]);
        $viewmodel->setTemplate('sbm-gestion/annee-scolaire/voir.phtml');
        return $viewmodel;
    }

    public function simulationPreparerAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        }
        $form = $this->form_manager->get(Simulation::class);
        if ($prg !== false) {
            if (array_key_exists('cancel', $prg)) {
                $this->flashMessenger()->addInfoMessage('Aucun changement');
                return $this->redirect()->toRoute('sbmgestion/anneescolaire');
            }
            if (array_key_exists('submit', $prg) && array_key_exists('millesime', $prg)) {
                $form->setData($prg);
                if ($form->isValid()) {
                    $millesime = $prg['millesime'];
                    $this->db_manager->get('Sbm\Db\Simulation\Prepare')
                        ->setMajDistances(
                        $this->cartographie_manager->get('Sbm\CalculDroitsTransport'))
                        ->duplicateCircuits($millesime, self::SIMULATION)
                        ->duplicateEleves($millesime, self::SIMULATION);
                    $this->flashMessenger()->addSuccessMessage(
                        "La simulation a été préparée à partir de l'année $millesime.");
                    return $this->redirect()->toRoute('sbmgestion/anneescolaire');
                }
            }
        }
        $where1 = new Where();
        $where1->isNotNull('suivantId');
        $where2 = new Where();
        $where2->isNull('suivantId');
        return new ViewModel(
            [
                'repris' => $this->db_manager->get('Sbm\Db\Vue\Classes')->fetchAll(
                    $where1, [
                        'niveau',
                        'nom'
                    ]),
                'non_repris' => $this->db_manager->get('Sbm\Db\Vue\Classes')->fetchAll(
                    $where2, [
                        'niveau',
                        'nom'
                    ]),
                'form' => $form->prepare()
            ]);
    }

    public function simulationViderAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        }
        $form = new Form\ButtonForm([
            'id' => null
        ],
            [
                'supproui' => [
                    'class' => 'confirm default',
                    'value' => 'Confirmer'
                ],
                'supprnon' => [
                    'class' => 'confirm default',
                    'value' => 'Abandonner'
                ]
            ]);
        if ($prg !== false) {
            if (array_key_exists('supproui', $prg)) {
                $this->db_manager->get('Sbm\Db\Table\Affectations')->viderMillesime(
                    self::SIMULATION);
                $this->db_manager->get('Sbm\Db\Table\Scolarites')->viderMillesime(
                    self::SIMULATION);
                $this->db_manager->get('Sbm\Db\Table\Circuits')->viderMillesime(
                    self::SIMULATION);
                $this->flashMessenger()->addSuccessMessage('La simulation a été effacée.');
            }
            return $this->redirect()->toRoute('sbmgestion/anneescolaire');
        }
        return new ViewModel([
            'form' => $form
        ]);
    }
}