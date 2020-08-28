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
 * @date 28 août 2020
 * @version 2020-2.6.0
 */
namespace SbmGestion\Controller;

use SbmAuthentification\Model\CategoriesInterface;
use SbmBase\Model\Session;
use SbmBase\Model\StdLib;
use SbmCommun\Form;
use SbmCommun\Model\Mvc\Controller\AbstractActionController;
use SbmGestion\Form\DupliquerReseau;
use SbmGestion\Form\Simulation;
use Zend\Db\Sql\Where;
use Zend\Http\PhpEnvironment\Response;
use Zend\View\Model\ViewModel;

class AnneeScolaireController extends AbstractActionController
{

    public function indexAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        }
        $simulation_vide = $this->db_manager->get('Sbm\Db\Table\Scolarites')->isEmptyMillesime(
            $this->db_manager->get('simulation')) &&
            $this->db_manager->get('Sbm\Db\Table\Circuits')->isEmptyMillesime(
                $this->db_manager->get('simulation'));

        return new ViewModel(
            [
                'anneesScolaires' => $this->db_manager->get('Sbm\Db\System\Calendar')->getAnneesScolaires(),
                'millesimeActif' => Session::get('millesime', false),
                'simulation_millesime' => $this->db_manager->get('simulation'),
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
                'admin' => $auth->getCategorieId() > CategoriesInterface::GESTION_ID,
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

    /**
     * Assure que les années scolaires sont cohérentes avec les tables lignes, services,
     * etablissements-services et circuits. Il faut que les millesimes utilisés soient les
     * mêmes dans les 4 tables et correspondent à une année scolaire ou à la simulation.
     */
    public function repareAction()
    {
        // @TODO : à écrire;
    }

    public function viderReseauAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        }
        if ($prg) {
            $origine = StdLib::getParam('origine', $prg, false);
            if ($origine) {
                $this->redirectToOrigin()->setBack($origine);
            }
            $millesime = StdLib::getParam('millesime', $prg, false);
            $form = new Form\ButtonForm([
                'id' => null,
                'millesime' => $millesime
            ],
                [
                    'supproui' => [
                        'class' => 'default confirm',
                        'value' => 'Confirmer'
                    ],
                    'supprnon' => [
                        'class' => 'default confirm',
                        'value' => 'Abandonner'
                    ]
                ]);
            $confirme = StdLib::getParam('supproui', $prg, false);
            $cancel = StdLib::getParam('supprnon', $prg, false);
            if (! $cancel && ! $confirme) {
                return new ViewModel(
                    [
                        'form' => $form->prepare(),
                        'as' => $millesime . '-' . ($millesime + 1)
                    ]);
            } elseif ($confirme) {
                $form->setData($prg);
                if ($form->isValid()) {
                    $millesime = $form->getData()['millesime'];
                    try {
                        $cr = [];
                        $cr['circuits'] = $this->db_manager->get('Sbm\Db\Table\Circuits')->viderMillesime(
                            $millesime);
                        $cr['etablissements-services'] = $this->db_manager->get(
                            'Sbm\Db\Table\EtablissementsServices')->viderMillesime(
                            $millesime);
                        $cr['services'] = $this->db_manager->get('Sbm\Db\Table\Services')->viderMillesime(
                            $millesime);
                        $cr['lignes'] = $this->db_manager->get('Sbm\Db\Table\Lignes')->viderMillesime(
                            $millesime);
                        $message = '';
                        foreach ($cr as $key => $value) {
                            $message .= sprintf(
                                "%d enregistrements supprimés dans la table %s \n", $value,
                                $key);
                        }
                        $this->flashMessenger()->addSuccessMessage(
                            'Suppression terminée.');
                        $this->flashMessenger()->addInfoMessage($message);
                    } catch (\Zend\Db\TableGateway\Exception\RuntimeException $e) {
                        $this->flashMessenger()->addErrorMessage(
                            'Suppression impossible. Erreur de table.');
                    } catch (\Zend\Db\Adapter\Exception\InvalidQueryException $e) {
                        $this->flashMessenger()->addErrorMessage(
                            'Suppression impossible. Requête invalide.');
                    }
                } else {
                    $this->flashMessenger()->addErrorMessage(
                        'Suppression impossible. Données invalides.');
                }
            }
        }
        try {
            return $this->redirectToOrigin()->back();
        } catch (\SbmCommun\Model\Mvc\Controller\Plugin\Exception\ExceptionInterface $e) {
            return $this->redirect()->toRoute('sbmgestion/anneescolaire');
        }
    }

    public function dupliquerReseauAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        }
        $args = (array) $prg;
        $ok_millesime = (array_key_exists('millesime_nouveau', $args) &&
            array_key_exists('millesime_source', $args)) ||
            (array_key_exists('millesime', $args) && array_key_exists('origine', $args));
        if (! $ok_millesime || array_key_exists('cancel', $args)) {
            $this->flashMessenger()->addInfoMessage('Action abandonnée');
            try {
                return $this->redirectToOrigin()->back();
            } catch (\SbmCommun\Model\Mvc\Controller\Plugin\Exception\ExceptionInterface $e) {
                return $this->redirect()->toRoute('sbmgestion/anneescolaire');
            }
        } elseif (array_key_exists('origine', $args)) {
            $this->redirectToOrigin()->setBack($args['origine']);
            $args['millesime_nouveau'] = $args['millesime']; // ctrl déjà fait
        }
        $anciens_millesimes = $this->db_manager->get('Sbm\Db\Table\Lignes')->getMillesimes();
        foreach ($anciens_millesimes as &$value) {
            if (is_numeric($value)) {
                $value = sprintf('%d-%d', $value, $value + 1);
            }
        }
        $form = new DupliquerReseau();
        $form->setValueOptions('millesime_source', $anciens_millesimes);
        $form->setData($args);
        if (array_key_exists('submit', $args)) {
            if ($form->isValid()) {
                $data = $form->getData();
                if ($this->db_manager->get('Sbm\Db\Table\Lignes')->dupliquer(
                    $args['millesime_source'], $data['millesime_nouveau']) &&
                    $this->db_manager->get('Sbm\Db\Table\Services')->dupliquer(
                        $args['millesime_source'], $data['millesime_nouveau']) &&
                    $this->db_manager->get('Sbm\Db\Table\EtablissementsServices')->dupliquer(
                        $args['millesime_source'], $data['millesime_nouveau']) &&
                    $this->db_manager->get('Sbm\Db\Table\Circuits')->dupliquer(
                        $args['millesime_source'], $data['millesime_nouveau']) &&
                    $this->db_manager->get('Sbm\Db\Table\Tarifs')->dupliquer(
                        $args['millesime_source'], $data['millesime_nouveau'])) {
                    $this->flashMessenger()->addSuccessMessage(
                        'Les circuits et tarifs de l\'année scolaire ont été créés.');
                } else {
                    $this->flashMessenger()->addErrorMessage(
                        'Impossible car les circuits ou tarifs de l\'année scolaire existent déjà.' .
                        ' Si nécessaire, les vider et recommencer.');
                }
                try {
                    return $this->redirectToOrigin()->back();
                } catch (\SbmCommun\Model\Mvc\Controller\Plugin\Exception\ExceptionInterface $e) {
                    return $this->redirect()->toRoute('sbmgestion/anneescolaire');
                }
            }
        }
        return new ViewModel(
            [
                'form' => $form->prepare(),
                'millesime_nouveau' => $args['millesime_nouveau']
            ]);
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
                    set_time_limit(0);
                    $millesime = $prg['millesime'];
                    $this->db_manager->get('Sbm\Db\Simulation\Prepare')
                        ->setMajDistances(
                        $this->cartographie_manager->get('Sbm\CalculDroitsTransport'))
                        ->duplicateCircuits($millesime,
                        $this->db_manager->get('simulation'))
                        ->duplicateTarifs($millesime, $this->db_manager->get('simulation'))
                        ->duplicateEleves($millesime, $this->db_manager->get('simulation'));
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
                try {
                    $cr = [];
                    $cr['affectations'] = $this->db_manager->get(
                        'Sbm\Db\Table\Affectations')->viderMillesime(
                        $this->db_manager->get('simulation'));
                    $cr['scolarites'] = $this->db_manager->get('Sbm\Db\Table\Scolarites')->viderMillesime(
                        $this->db_manager->get('simulation'));
                    $cr['circuits'] = $this->db_manager->get('Sbm\Db\Table\Circuits')->viderMillesime(
                        $this->db_manager->get('simulation'));
                    $cr['etablissements-services'] = $this->db_manager->get(
                        'Sbm\Db\Table\EtablissementsServices')->viderMillesime(
                        $this->db_manager->get('simulation'));
                    $cr['services'] = $this->db_manager->get('Sbm\Db\Table\Services')->viderMillesime(
                        $this->db_manager->get('simulation'));
                    $cr['lignes'] = $this->db_manager->get('Sbm\Db\Table\Lignes')->viderMillesime(
                        $this->db_manager->get('simulation'));
                    $cr['tarifs'] = $this->db_manager->get('Sbm\Db\Table\Tarifs')->viderMillesime(
                        $this->db_manager->get('simulation'));
                    $message = '';
                    foreach ($cr as $key => $value) {
                        $message .= sprintf(
                            "%d enregistrements supprimés dans la table %s \n", $value,
                            $key);
                    }
                    $this->flashMessenger()->addSuccessMessage(
                        'La simulation a été effacée.');
                    $this->flashMessenger()->addInfoMessage($message);
                } catch (\Zend\Db\TableGateway\Exception\RuntimeException $e) {
                    $this->flashMessenger()->addErrorMessage(
                        'Suppression impossible. Erreur de table.');
                } catch (\Zend\Db\Adapter\Exception\InvalidQueryException $e) {
                    $this->flashMessenger()->addErrorMessage(
                        'Suppression impossible. Requête invalide.');
                }
            }
            return $this->redirect()->toRoute('sbmgestion/anneescolaire');
        }
        return new ViewModel([
            'form' => $form
        ]);
    }
}