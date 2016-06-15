<?php
/**
 * Controller principal du module SbmGestion
 *
 * Méthodes utilisées pour gérer la localisation des responsables et la création des cartes de transport
 * 
 * @project sbm
 * @package SbmGestion/Controller
 * @filesource EleveGestionController.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 14 juin 2016
 * @version 2016-2.1.5
 */
namespace SbmGestion\Controller;

use Zend\View\Model\ViewModel;
use Zend\Http\PhpEnvironment\Response;
use Zend\Db\Sql\Where;
use SbmCartographie\Model\Point;
use SbmCommun\Form\LatLng;
use SbmCommun\Form\ButtonForm;
use SbmCommun\Model\Mvc\Controller\AbstractActionController;
use SbmCommun\Model\StdLib;
use SbmGestion\Form\AffectationDecision;
use DafapSession\Model\Session;

class EleveGestionController extends AbstractActionController
{

    public function indexAction()
    {
        // retour de liste par post. Evite le 'Confirmer le nouvel envoi du formulaire'
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        }
        return new ViewModel();
    }

    public function affecterListeAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            $args = $this->getFromSession('criteres', []);
        } else {
            $args = $prg;
            $this->setToSession('criteres', $args);
        }
        $form = new \Zend\Form\Form('criteres');
        $form->setAttribute('method', 'post');
        $form->add([
            'type' => 'Zend\Form\Element\Radio',
            'name' => 'choix',
            'attributes' => [
                'id' => 'choix-criteres',
                'class' => 'sbm-radio'
            ],
            'options' => [
                'label' => 'Choisissez',
                'value_options' => [
                    'inscrit' => 'Inscrits',
                    'preinscrit' => 'Préinscrits'
                ]
            ]
        ]);
        $form->add([
            'type' => 'submit',
            'name' => 'submit',
            'attributes' => [
                'title' => 'Rechercher',
                'id' => 'criteres-submit',
                'autofocus' => 'autofocus',
                'class' => 'fam-find button submit'
            ]
        ]);
        if (array_key_exists('choix', $args)) {
            $choix = $args['choix'];
            // var_dump($args);
        } else {
            $choix = 'inscrit';
        }
        $form->setData([
            'choix' => $choix
        ]);
        $query = $this->config['db_manager']->get('Sbm\Db\Query\ElevesScolarites');
        return new ViewModel([
            'criteres_form' => $form,
            'paginator' => $choix == 'inscrit' ? $query->paginatorInscritsNonAffectes() : $query->paginatorPreinscritsNonAffectes(),
            'count_per_page' => $this->getPaginatorCountPerPage('nb_eleves', 10),
            'page' => $this->params('page', 1),
            'title' => $choix == 'inscrit' ? 'inscrits' : 'préinscrits'
        ]);
    }

    /**
     * Le paramètre 'op' de POST prend les valeurs 1 ou 2.
     * 1 : entrée dans le processus d'affectation. On prépare un formulaire formDecision et les points ptElv et ptEta
     * 2 : sortie par post du formulaire formDecision
     * - cancel : retour à affecter-liste
     * - submit : traitement du formulaire, enregistrement de la décision et passage au formulaire formAffectation
     * - ni l'un, ni l'autre : F5 ou back() de l'internaute
     * 3 : sortie du formulaire formAffectation
     * - cancel : retour à affecter-liste
     * - back : passage au cas n°2
     * - submit : traitement du formulaire, enregistrement de l'affectation et retour à affecter-liste
     * - aucun ce ceux la : F5 ou back() de l'internaute
     *
     * @return \Zend\Http\PhpEnvironment\Response|\Zend\Http\Response|\Zend\View\Model\ViewModel
     */
    public function affecterAction()
    {
        $msg = '';
        $page = $this->params('page', 1);
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            $args = $this->getFromSession('post', false, $this->getSessionNamespace());
            if (! $args) {
                $this->flashMessenger()->addErrorMessage('Action formellement interdite !');
                return $this->redirect()->toRoute('login', [
                    'action' => 'logout'
                ]); // deny de service
            }
        } else {
            $args = $prg;
            if (array_key_exists('cancel', $args)) {
                return $this->redirect()->toRoute('sbmgestion/gestioneleve', [
                    'action' => 'affecter-liste',
                    'page' => $page
                ]);
            }
            if ($args['op'] == 2 && array_key_exists('back', $args)) {
                $args = $this->getFromSession('post', false, $this->getSessionNamespace());
            } else {
                $postSession = $args;
                unset($postSession['submit'], $postSession['back']);
                $this->setToSession('post', $postSession, $this->getSessionNamespace());
                unset($postSession);
            }
        }
        // ici, on doit avoir un bon $arg qui contient les valeurs postées
        
        $formDecision = new AffectationDecision($args['trajet'], $args['op']);
        $formDecision->setAttribute('action', $this->url()
            ->fromRoute('sbmgestion/gestioneleve', [
            'action' => 'affecter',
            'page' => $page
        ]));
        if ($args['op'] == 2) {
            $values_options1 = $this->config['db_manager']->get('Sbm\Db\Select\Stations')->ouvertes();
            $values_options2 = $this->config['db_manager']->get('Sbm\Db\Select\Services');
            $formDecision->setValueOptions('station1Id', $values_options1)
                ->setValueOptions('station2Id', $values_options1)
                ->setValueOptions('service1Id', $values_options2)
                ->setValueOptions('service2Id', $values_options2);
        }
        if (array_key_exists('submit', $args)) {
            $formDecision->setData($args);
            if ($formDecision->isValid()) {
                if ($args['op'] == 1) {
                    $decision = $formDecision->getData();
                    $refus = $args['accordR' . $args['trajet']] == 0;
                    if ($refus) {
                        // enregistrer la décision
                        $table = $this->config['db_manager']->get('Sbm\Db\Table\Scolarites');
                        $oData = $table->getObjData();
                        $oData->exchangeArray($decision);
                        $table->saveRecord($oData);
                        $this->flashMessenger()->addSuccessMessage('La décision est enregistrée. Précisez le circuit et le point d\'arrêt.');
                        return $this->redirect()->toRoute('sbmgestion/gestioneleve', [
                            'action' => 'affecter-liste',
                            'page' => $page
                        ]);
                    } else {
                        // le trajet est accordé. Il faut le préciser. On l'enregistrera en phase 2. Pour le moment, mettre la décision en session
                        $this->setToSession('decision', $decision);
                        $formDecision = new AffectationDecision($args['trajet'], 2);
                        $values_options1 = $this->config['db_manager']->get('Sbm\Db\Select\Stations')->ouvertes();
                        $values_options2 = $this->config['db_manager']->get('Sbm\Db\Select\Services');
                        $formDecision->setValueOptions('station1Id', $values_options1)
                            ->setValueOptions('station2Id', $values_options1)
                            ->setValueOptions('service1Id', $values_options2)
                            ->setValueOptions('service2Id', $values_options2);
                        $args['op'] = 2;
                    }
                } else {
                    // on crée l'affectation
                    $table = $this->config['db_manager']->get('Sbm\Db\Table\Affectations');
                    $oData = $table->getObjData();
                    $oData->exchangeArray($formDecision->getData());
                    $table->saveRecord($oData);
                    // on enregistre la décision qui est en session
                    $decision = $this->getFromSession('decision');
                    $table = $this->config['db_manager']->get('Sbm\Db\Table\Scolarites');
                    $oData = $table->getObjData();
                    $oData->exchangeArray($decision);
                    $table->saveRecord($oData);
                    // et on sort
                    $this->flashMessenger()->addSuccessMessage('Affectation terminée.');
                    return $this->redirect()->toRoute('sbmgestion/gestioneleve', [
                        'action' => 'affecter-liste',
                        'page' => $page
                    ]);
                }
            } else {
                $this->flashMessenger()->addWarningMessage('Données invalides');
            }
        }
        
        $eleve = $this->config['db_manager']->get('Sbm\Db\Query\ElevesScolarites')
            ->getEleveAdresse($args['eleveId'], $args['trajet'])
            ->current();
        $formDecision->setData(array_merge($eleve, $args));
        
        $d2etab = $this->config['cartographie_manager']->get('SbmCarto\DistanceEtablissements');
        $point = new Point($eleve['x'], $eleve['y']);
        $ptElv = $d2etab->getProjection()->xyzVersgRGF93($point);
        $point = new Point($eleve['xeta'], $eleve['yeta']);
        $ptEta = $d2etab->getProjection()->xyzVersgRGF93($point);
        return new ViewModel([
            'decision' => $formDecision->prepare(),
            'op' => $args['op'],
            'page' => $page,
            'eleve' => $eleve,
            'ptElv' => $ptElv,
            'ptEta' => $ptEta
        ]);
    }

    public function gaLocalisationListeAction()
    {
        return new ViewModel([
            'data' => $this->config['db_manager']->get('Sbm\Db\Query\ElevesScolarites')->getDemandeGaDistanceR2Zero(),
            'page' => $this->params('page', 1)
        ]);
    }

    public function gaLocalisationBymapAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            $this->flashMessenger()->addWarningMessage('Recommencez.');
            return $this->redirect()->toRoute('sbmgestion/gestioneleve', [
                'action' => 'ga-localisation-liste',
                'page' => $this->params('page', 1)
            ]);
        } else {
            $args = $prg;
            if (array_key_exists('cancel', $args)) {
                $this->flashMessenger()->addWarningMessage('Localisation abandonnée.');
                return $this->redirect()->toRoute('sbmgestion/gestioneleve', [
                    'action' => 'ga-localisation-liste',
                    'page' => $this->params('page', 1)
                ]);
            }
            if (! array_key_exists('responsableId', $args)) {
                $this->flashMessenger()->addErrorMessage('Action  interdite');
                return $this->redirect()->toRoute('login', [
                    'action' => 'logout'
                ]);
            }
        }
        $d2etab = $this->config['cartographie_manager']->get('SbmCarto\DistanceEtablissements');
        $responsableId = $args['responsableId'];
        $tResponsables = $this->config['db_manager']->get('Sbm\Db\Table\Responsables');
        // nécessaire pour valider lat et lng
        $configCarte = StdLib::getParam('parent', $this->config['cartographie_manager']->get('cartes'));
        $form = new LatLng([
            'responsableId' => [
                'id' => 'responsableId'
            ]
        ], [
            'submit' => [
                'class' => 'button default submit left-95px',
                'value' => 'Enregistrer la localisation'
            ],
            'cancel' => [
                'class' => 'button default cancel left-10px',
                'value' => 'Abandonner'
            ]
        ], $configCarte['valide']);
        $form->setAttribute('action', $this->url()
            ->fromRoute('sbmgestion/gestioneleve', [
            'action' => 'ga-localisation-bymap',
            'page' => $this->params('page', 1)
        ]));
        if (array_key_exists('submit', $args)) {
            $form->setData($args);
            if ($form->isValid()) {
                // transforme les coordonnées
                $pt = new Point($args['lng'], $args['lat'], 0, 'degré');
                $point = $d2etab->getProjection()->gRGF93versXYZ($pt);
                // enregistre dans la fiche responsable
                $oData = $tResponsables->getObjData();
                $oData->exchangeArray([
                    'responsableId' => $responsableId,
                    'x' => $point->getX(),
                    'y' => $point->getY()
                ]);
                $tResponsables->saveRecord($oData);
                $this->flashMessenger()->addSuccessMessage('La localisation du domicile est enregistrée.');
                // Met à jour les fiches des enfants dans scolarites
                $majDistances = $this->config['cartographie_manager']->get('Sbm\MajDistances')->pour($responsableId);
                return $this->redirect()->toRoute('sbmgestion/gestioneleve', [
                    'action' => 'ga-localisation-liste',
                    'page' => $this->params('page', 1)
                ]);
            }
        }
        $responsable = $tResponsables->getRecord($responsableId);
        $commune = $this->config['db_manager']->get('Sbm\Db\table\Communes')->getRecord($responsable->communeId);
        if ($responsable->x == 0.0 && $responsable->y == 0.0) {
            // essayer de localiser par l'adresse avant de présenter la carte
            $array = $this->config['cartographie_manager']->get('SbmCarto\Geocoder')->geocode($responsable->adresseL2 ?  : $responsable->adresseL1, $responsable->codePostal, $commune->nom);
            $pt = new Point($array['lng'], $array['lat'], 0, 'degré');
            $description = $array['adresse'];
        } else {
            $point = new Point($responsable->x, $responsable->y);
            $pt = $d2etab->getProjection()->xyzVersgRGF93($point);
            $description = nl2br(trim(implode("\n", [
                $responsable->adresseL1,
                $responsable->adresseL2
            ])));
            $description .= '<br>' . $responsable->codePostal . ' ' . $commune->nom;
        }
        $form->setData([
            'responsableId' => $responsableId,
            'lat' => $pt->getLatitude(),
            'lng' => $pt->getLongitude()
        ]);
        return new ViewModel([
            // 'pt' => $pt,
            'form' => $form->prepare(),
            'description' => $description,
            'responsable' => [
                $responsable->titre . ' ' . $responsable->nom . ' ' . $responsable->prenom,
                nl2br(trim(implode("\n", [
                    $responsable->adresseL1,
                    $responsable->adresseL2
                ]))),
                $responsable->codePostal . ' ' . $commune->nom
            ],
            'config' => $configCarte
        ]);
    }

    public function cartesAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        }
        $vue = true;
        $args = (array) $prg;
        if (array_key_exists('cancel', $args)) {
            return $this->redirect()->toRoute('sbmgestion/eleve', [
                'action' => 'eleve-liste',
                'page' => $this->params('page', 1)
            ]);
        }
        $millesime = Session::get('millesime');
        $tCalendar = $this->config['db_manager']->get('Sbm\Db\System\Calendar');
        $dateDebut = $tCalendar->etatDuSite()['dateDebut']->format('Y-m-d');
        $form1 = new ButtonForm([], [
            'nouvelle' => [
                'class' => 'button default submit left-95px',
                'value' => 'Préparer une nouvelle édition'
            ]
        ]);
        $form2 = new \SbmGestion\Form\SelectionCartes();
        $form2->setValueOptions('dateReprise', $this->config['db_manager']->get('Sbm\Db\Select\DatesCartes'))
            ->setData([
            'selection' => 'nouvelle',
            'critere' => 'tous',
            'document' => 'Liste de contrôle des cartes'
        ]);
        if (array_key_exists('nouvelle', $args)) {
            $this->config['db_manager']->get('Sbm\Db\Table\Scolarites')->prepareDateCarteForNewEdition($millesime, $dateDebut);
            $form2->setValueOptions('dateReprise', $this->config['db_manager']->get('Sbm\Db\Select\DatesCartes'));
        } elseif (array_key_exists('submit', $args)) {
            $form2->setData($args);
            if ($form2->isValid()) {
                $criteres = [];
                $expression = [
                    "millesime = $millesime",
                    'inscrit=1'
                ];
                switch ($args['critere']) {
                    case 'inscrits':
                        $expression[] = '(paiement = 1 OR fa = 1 OR gratuit > 0)';
                        break;
                    case 'preinscrits':
                        $expression[] = 'paiement = 0';
                        $expression[] = 'fa = 0';
                        $expression[] = 'gratuit = 0';
                        break;
                    default: // tous
                        break;
                }
                $where = new Where();
                switch ($args['selection']) {
                    case 'nouvelle':
                        $lastDateCarte = $this->config['db_manager']->get('Sbm\Db\Table\Scolarites')->getLastDateCarte();
                        $expression[] = "dateCarte = '$lastDateCarte'";
                        $where->equalTo('dateCarte', $lastDateCarte);
                        break;
                    case 'reprise':
                        $dateReprise = $args['dateReprise'];
                        $expression[] = "dateCarte = '$dateReprise'";
                        $where->equalTo('dateCarte', $dateReprise);
                        break;
                    case 'selection':
                        $expression = [
                            "millesime = $millesime",
                            'selection = 1'
                        ];
                        $where->equalTo('millesime', $millesime)->literal('selection = 1');
                        break;
                }
                $call_pdf = $this->config['RenderPdfService'];
                $call_pdf->setParam('documentId', $args['document'])
                    ->setParam('where', $where)
                    ->setParam('criteres', $criteres)
                    ->setParam('strict', [
                    'empty' => [],
                    'not empty' => []
                ])
                    ->setParam('expression', $expression)
                    ->renderPdf();
                $this->flashMessenger()->addSuccessMessage("Création d'un pdf.");
                $vue = false; // la http response est lancée par renderPdf()
            }
        }
        if ($vue) {
            $lastDateCarte = $this->config['db_manager']->get('Sbm\Db\Table\Scolarites')->getLastDateCarte();
            if ($lastDateCarte < $dateDebut) {
                $e = $form2->get('selection');
                $e->unsetValueOption('nouvelle')->unsetValueOption('reprise');
                $form2->setData([
                    'selection' => 'selection'
                ]);
            }
            return new ViewModel([
                'form1' => $form1,
                'form2' => $form2,
                'lastDateCarte' => $lastDateCarte,
                'dateDebut' => $dateDebut,
                'page' => $this->params('page', 1)
            ]);
        }
    }

    /**
     * Reçoit nécessairement en post le paramètre eleveId
     *
     * @return \Zend\Http\PhpEnvironment\Response|\Zend\Http\Response
     */
    public function duplicataCarteAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        }
        $vue = true;
        $args = (array) $prg;
        if (array_key_exists('origine', $args)) {
            $this->setToSession('origine', $args['origine'], $this->getSessionNamespace());
        } else {
            $args['origine'] = $this->getFromSession('origine', null, $this->getSessionNamespace());
        }
        if (array_key_exists('cancel', $args) || ! array_key_exists('eleveId', $args)) {
            return $this->redirect()->toUrl($args['origine']);
        }
        $millesime = Session::get('millesime');
        $vue = true;
        $documentId = $this->config['db_manager']->get('Sbm\Db\System\Documents')->getDocumentId('Duplicata carte');
        $configLabel = $this->config['db_manager']->get('Sbm\Db\System\DocLabels')->getConfig($documentId);
        $form = new \SbmGestion\Form\PlancheEtiquettesForm('duplicata', [
            'nbcols' => $configLabel['cols_number'],
            'nbrows' => $configLabel['rows_number']
        ]);
        $form->add([
            'type' => 'hidden',
            'name' => 'eleveId',
            'attributes' => [
                'value' => $args['eleveId']
            ]
        ]);
        $form->add([
            'type' => 'hidden',
            'name' => 'origine',
            'attributes' => [
                'value' => $args['origine']
            ]
        ]);
        $form->add([
            'type' => 'Zend\Form\Element\Checkbox',
            'name' => 'gratuit',
            'attributes' => [],
            'options' => [
                'label' => 'Gratuité exceptionnelle',
                'error_attributes' => [
                    'class' => 'sbm-error'
                ]
            ]
        ]);
        if (array_key_exists('submit', $args)) {
            $form->setData($args);
            if ($form->isValid()) {
                $position = array_combine([
                    'row',
                    'column'
                ], explode('-', $args['planche']));
                $where = new Where();
                $where->equalTo('millesime', $millesime)->equalTo('eleveId', $args['eleveId']);
                $call_pdf = $this->config['RenderPdfService'];
                $call_pdf->setParam('documentId', 'Duplicata carte')
                    ->setParam('where', $where)
                    ->setParam('criteres', [])
                    ->setParam('expression', [
                    "millesime = $millesime",
                    'eleveId = ' . $args['eleveId']
                ])
                    ->setParam('position', $position)
                    ->setParam('duplicata', true)
                    ->renderPdf();
                if (empty($args['gratuit'])) {
                    $this->config['db_manager']->get('Sbm\Db\Table\Scolarites')->addDuplicata($millesime, $args['eleveId']);
                }
                $this->flashMessenger()->addSuccessMessage("Edition d'un duplicata.");
                $vue = false; // la http response est lancée par renderPdf()
            }
        }
        if ($vue) {
            return new ViewModel([
                'form' => $form,
                'eleveid' => $args['eleveId'],
                'origine' => $args['origine']
            ]);
        }
    }
}