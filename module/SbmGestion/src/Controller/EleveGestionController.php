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
 * @date 29 mars 2020
 * @version 2020-2.6.0
 */
namespace SbmGestion\Controller;

use SbmBase\Model\Session;
use SbmBase\Model\StdLib;
use SbmCartographie\GoogleMaps;
use SbmCartographie\Model\Point;
use SbmCommun\Form;
use SbmCommun\Model\Db\Sql\Predicate\Not;
use SbmCommun\Model\Mvc\Controller\AbstractActionController;
use SbmGestion\Form as FormGestion;
use Zend\Db\Sql\Where;
use Zend\Http\PhpEnvironment\Response;
use Zend\View\Model\ViewModel;

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
            $args = Session::get('criteres', []);
        } else {
            $args = $prg;
            Session::set('criteres', $args);
        }
        $form = new \Zend\Form\Form('criteres');
        $form->setAttribute('method', 'post');
        $form->add(
            [
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
        $form->add(
            [
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
        $query = $this->db_manager->get('Sbm\Db\Query\ElevesScolarites');
        return new ViewModel(
            [
                'criteres_form' => $form,
                'inscrit' => $choix == 'inscrit',
                'paginator' => $choix == 'inscrit' ? $query->paginatorInscritsNonAffectes() : $query->paginatorPreinscritsNonAffectes(),
                'count_per_page' => $this->getPaginatorCountPerPage('nb_eleves', 10),
                'page' => $this->params('page', 1),
                'title' => $choix == 'inscrit' ? 'inscrits' : 'préinscrits'
            ]);
    }

    /**
     * Le paramètre 'op' de POST prend les valeurs 1 ou 2. 1 : entrée dans le processus
     * d'affectation. On prépare un formulaire formDecision et les points ptElv et ptEta 2
     * : sortie par post du formulaire formDecision - cancel : retour à affecter-liste -
     * submit : traitement du formulaire, enregistrement de la décision et passage au
     * formulaire formAffectation - ni l'un, ni l'autre : F5 ou back() de l'internaute 3 :
     * sortie du formulaire formAffectation - cancel : retour à affecter-liste - back :
     * passage au cas n°2 - submit : traitement du formulaire, enregistrement de
     * l'affectation et retour à affecter-liste - aucun ce ceux la : F5 ou back() de
     * l'internaute
     *
     * @return \Zend\Http\PhpEnvironment\Response|\Zend\Http\Response|\Zend\View\Model\ViewModel
     */
    public function affecterAction()
    {
        $page = $this->params('page', 1);
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            $args = Session::get('post', false, $this->getSessionNamespace());
            if (! $args) {
                $this->flashMessenger()->addErrorMessage(
                    'Action formellement interdite !');
                return $this->redirect()->toRoute('login', [
                    'action' => 'logout'
                ]); // deny de service
            }
        } else {
            $args = $prg;
            if (array_key_exists('cancel', $args)) {
                return $this->redirect()->toRoute('sbmgestion/gestioneleve',
                    [
                        'action' => 'affecter-liste',
                        'page' => $page
                    ]);
            }
            if ($args['op'] == 2 && array_key_exists('back', $args)) {
                $args = Session::get('post', false, $this->getSessionNamespace());
            } else {
                $postSession = $args;
                unset($postSession['submit'], $postSession['back']);
                Session::set('post', $postSession, $this->getSessionNamespace());
                unset($postSession);
            }
        }
        // ici, on doit avoir un bon $arg qui contient les valeurs postées

        $formDecision = new FormGestion\AffectationDecision($args['trajet'], $args['op']);
        $formDecision->setAttribute('action',
            $this->url()
                ->fromRoute('sbmgestion/gestioneleve',
                [
                    'action' => 'affecter',
                    'page' => $page
                ]));
        if ($args['op'] == 2) {
            $values_options1 = $this->db_manager->get('Sbm\Db\Select\Stations')->ouvertes();
            $values_options2 = $this->db_manager->get('Sbm\Db\Select\Services')->tout();
            $formDecision->setValueOptions('station1Id', $values_options1)
                ->setValueOptions('station2Id', $values_options1)
                ->setValueOptions('service1Id', $values_options2)
                ->setValueOptions('service2Id', $values_options2);
        }
        die(var_dump($args, $values_options2));
        if (array_key_exists('submit', $args)) {
            $formDecision->setData($args);
            if ($formDecision->isValid()) {
                if ($args['op'] == 1) {
                    $decision = $formDecision->getData();
                    $refus = $args['accordR' . $args['trajet']] == 0;
                    if ($refus) {
                        // enregistrer la décision
                        $table = $this->db_manager->get('Sbm\Db\Table\Scolarites');
                        $oData = $table->getObjData();
                        $oData->exchangeArray($decision);
                        $table->saveRecord($oData);
                        $this->flashMessenger()->addSuccessMessage(
                            'La décision est enregistrée. Précisez le circuit et le point d\'arrêt.');
                        return $this->redirect()->toRoute('sbmgestion/gestioneleve',
                            [
                                'action' => 'affecter-liste',
                                'page' => $page
                            ]);
                    } else {
                        // le trajet est accordé. Il faut le préciser. On l'enregistrera
                        // en phase2. Pour le moment, mettre la décision en session
                        Session::set('decision', $decision);
                        $formDecision = new FormGestion\AffectationDecision(
                            $args['trajet'], 2);
                        $values_options1 = $this->db_manager->get(
                            'Sbm\Db\Select\Stations')->ouvertes();
                        $values_options2 = $this->db_manager->get(
                            'Sbm\Db\Select\Services')->tout();
                        $formDecision->setValueOptions('station1Id', $values_options1)
                            ->setValueOptions('station2Id', $values_options1)
                            ->setValueOptions('service1Id', $values_options2)
                            ->setValueOptions('service2Id', $values_options2);
                        $args['op'] = 2;
                    }
                } else {
                    // on crée l'affectation
                    $table = $this->db_manager->get('Sbm\Db\Table\Affectations');
                    $oData = $table->getObjData();
                    $oData->exchangeArray($formDecision->getData());
                    $table->saveRecord($oData);
                    // on enregistre la décision qui est en session
                    $decision = Session::get('decision');
                    $table = $this->db_manager->get('Sbm\Db\Table\Scolarites');
                    $oData = $table->getObjData();
                    $oData->exchangeArray($decision);
                    $table->saveRecord($oData);
                    // et on sort
                    $this->flashMessenger()->addSuccessMessage('Affectation terminée.');
                    return $this->redirect()->toRoute('sbmgestion/gestioneleve',
                        [
                            'action' => 'affecter-liste',
                            'page' => $page
                        ]);
                }
            } else {
                $this->flashMessenger()->addWarningMessage('Données invalides');
            }
        }

        $eleve = $this->db_manager->get('Sbm\Db\Query\ElevesScolarites')
            ->getEleveAdresse($args['eleveId'], $args['trajet'])
            ->current();
        $formDecision->setData(array_merge($eleve->getArrayCopy(), $args));

        $oDistanceMatrix = $this->cartographie_manager->get(
            GoogleMaps\DistanceMatrix::class);
        $point = new Point($eleve['x'], $eleve['y']);
        $ptElv = $oDistanceMatrix->getProjection()->xyzVersgRGF93($point);
        $point = new Point($eleve['xeta'], $eleve['yeta']);
        $ptEta = $oDistanceMatrix->getProjection()->xyzVersgRGF93($point);
        $configCarte = StdLib::getParam('gestion',
            $this->cartographie_manager->get('cartes'));
        return new ViewModel(
            [
                'scheme' => $this->getRequest()
                    ->getUri()
                    ->getScheme(),
                'decision' => $formDecision->prepare(),
                'op' => $args['op'],
                'page' => $page,
                'eleve' => $eleve,
                'ptElv' => $ptElv,
                'ptEta' => $ptEta,
                'config' => $configCarte,
                'url_api' => $this->cartographie_manager->get('google_api_browser')['js']
            ]);
    }

    public function gaLocalisationListeAction()
    {
        $paginator = $this->db_manager->get('Sbm\Db\Query\ElevesScolarites')->paginatorDemandeGaDistanceR2Zero();
        return new ViewModel(
            [
                'paginator' => $paginator,
                'count_per_page' => $this->getPaginatorCountPerPage('nb_eleves', 10),
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
            return $this->redirect()->toRoute('sbmgestion/gestioneleve',
                [
                    'action' => 'ga-localisation-liste',
                    'page' => $this->params('page', 1)
                ]);
        } else {
            $args = $prg;
            if (array_key_exists('cancel', $args)) {
                $this->flashMessenger()->addWarningMessage('Localisation abandonnée.');
                return $this->redirect()->toRoute('sbmgestion/gestioneleve',
                    [
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
        $oDistanceMatrix = $this->cartographie_manager->get(
            GoogleMaps\DistanceMatrix::class);
        $responsableId = $args['responsableId'];
        $tResponsables = $this->db_manager->get('Sbm\Db\Table\Responsables');
        // nécessaire pour valider lat et lng
        $configCarte = StdLib::getParam('gestion',
            $this->cartographie_manager->get('cartes'));
        $form = new Form\LatLng([
            'responsableId' => [
                'id' => 'responsableId'
            ]
        ],
            [
                'submit' => [
                    'class' => 'button default submit left-95px',
                    'value' => 'Enregistrer la localisation'
                ],
                'cancel' => [
                    'class' => 'button default cancel left-10px',
                    'value' => 'Abandonner'
                ]
            ], $configCarte['valide']);
        $form->setAttribute('action',
            $this->url()
                ->fromRoute('sbmgestion/gestioneleve',
                [
                    'action' => 'ga-localisation-bymap',
                    'page' => $this->params('page', 1)
                ]));
        if (array_key_exists('submit', $args)) {
            $form->setData($args);
            if ($form->isValid()) {
                // transforme les coordonnées
                $pt = new Point($args['lng'], $args['lat'], 0, 'degré');
                $point = $oDistanceMatrix->getProjection()->gRGF93versXYZ($pt);
                // enregistre dans la fiche responsable
                $oData = $tResponsables->getObjData();
                $oData->exchangeArray(
                    [
                        'responsableId' => $responsableId,
                        'x' => $point->getX(),
                        'y' => $point->getY()
                    ]);
                $tResponsables->saveRecord($oData);
                $this->flashMessenger()->addSuccessMessage(
                    'La localisation du domicile est enregistrée.');
                // Met à jour les fiches des enfants dans scolarites
                $msg = $this->cartographie_manager->get('Sbm\MajDistances')->pour(
                    $responsableId);
                if ($msg) {
                    $this->flashMessenger()->addWarningMessage($msg);
                }
                return $this->redirect()->toRoute('sbmgestion/gestioneleve',
                    [
                        'action' => 'ga-localisation-liste',
                        'page' => $this->params('page', 1)
                    ]);
            }
        }
        $responsable = $tResponsables->getRecord($responsableId);
        // préparer le nom de la commune selon les règes de la méthode
        // GoogleMaps\Geocoder::geocode
        $commune = $this->db_manager->get('Sbm\Db\table\Communes')->getRecord(
            $responsable->communeId);
        $sa = new \SbmCommun\Filter\SansAccent();
        $lacommune = $sa->filter($commune->alias);
        if ($responsable->x == 0.0 && $responsable->y == 0.0) {
            // essayer de localiser par l'adresse avant de présenter la carte
            $array = $this->cartographie_manager->get(GoogleMaps\Geocoder::class)->geocode(
                $responsable->adresseL1, $responsable->codePostal, $lacommune);
            $pt = new Point($array['lng'], $array['lat'], 0, 'degré');
            $pt->setLatLngRange($configCarte['valide']['lat'],
                $configCarte['valide']['lng']);
            if (! $pt->isValid() && ! empty($responsable->adresseL2)) {
                $array = $this->cartographie_manager->get(GoogleMaps\Geocoder::class)->geocode(
                    $responsable->adresseL2, $responsable->codePostal, $lacommune);
                $pt->setLatitude($array['lat']);
                $pt->setLongitude($array['lng']);
                if (! $pt->isValid() && ! empty($responsable->adresseL3)) {
                    $array = $this->cartographie_manager->get(GoogleMaps\Geocoder::class)->geocode(
                        $responsable->adresseL3, $responsable->codePostal, $lacommune);
                    $pt->setLatitude($array['lat']);
                    $pt->setLongitude($array['lng']);
                    if (! $pt->isValid()) {
                        $pt->setLatitude($configCarte['centre']['lat']);
                        $pt->setLongitude($configCarte['centre']['lng']);
                    }
                }
            }
            $description = $array['adresse'];
        } else {
            $point = new Point($responsable->x, $responsable->y);
            $pt = $oDistanceMatrix->getProjection()->xyzVersgRGF93($point);
            $description = nl2br(
                trim(
                    implode("\n",
                        array_filter(
                            [
                                $responsable->adresseL1,
                                $responsable->adresseL2,
                                $responsable->adresseL3
                            ]))));
            $description .= '<br>' . $responsable->codePostal . ' ' . $commune->alias;
        }
        $form->setData(
            [
                'responsableId' => $responsableId,
                'lat' => $pt->getLatitude(),
                'lng' => $pt->getLongitude()
            ]);
        // localisation du R1
        $point = new Point($args['xr1'], $args['yr1']);
        $ptR1 = $oDistanceMatrix->getProjection()->xyzVersgRGF93($point);
        $ptR1->setAttribute('description', $args['descriptionr1']);
        // localisation de l'établissement
        $point = new Point($args['xeta'], $args['yeta']);
        $ptEta = $oDistanceMatrix->getProjection()->xyzVersgRGF93($point);
        $ptEta->setAttribute('description', $args['descriptioneta']);
        return new ViewModel(
            [
                'scheme' => $this->getRequest()
                    ->getUri()
                    ->getScheme(),
                'ptR1' => $ptR1,
                'ptEta' => $ptEta,
                'form' => $form->prepare(),
                'description' => $description,
                'responsable' => [
                    $responsable->titre . ' ' . $responsable->nom . ' ' .
                    $responsable->prenom,
                    nl2br(
                        trim(
                            implode("\n",
                                array_filter(
                                    [
                                        $responsable->adresseL1,
                                        $responsable->adresseL2,
                                        $responsable->adresseL3
                                    ])))),
                    $responsable->codePostal . ' ' . $commune->nom
                ],
                'config' => $configCarte,
                'url_api' => $this->cartographie_manager->get('google_api_browser')['js']
            ]);
    }

    /**
     * Ne traite que les cartes de NatureCartes == 1
     *
     * @return \Zend\Http\PhpEnvironment\Response|\Zend\Http\Response|\Zend\View\Model\ViewModel
     */
    public function cartesAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        }
        $vue = true;
        $args = $prg ?: [];
        if (array_key_exists('cancel', $args)) {
            return $this->redirect()->toRoute('sbmgestion/eleve',
                [
                    'action' => 'eleve-liste',
                    'page' => $this->params('page', 1)
                ]);
        }
        $millesime = Session::get('millesime');
        $tCalendar = $this->db_manager->get('Sbm\Db\System\Calendar');
        $dateDebut = $tCalendar->getEtatDuSite()['dateDebut']->format('Y-m-d');
        $form1 = new Form\ButtonForm([],
            [
                'nouvelle' => [
                    'class' => 'button default submit left-95px',
                    'value' => 'Préparer une nouvelle édition'
                ]
            ]);
        $form2 = $this->form_manager->get(FormGestion\SelectionCartes::class);
        $form2->setValueOptions('dateReprise',
            $this->db_manager->get('Sbm\Db\Select\DatesCartes')
                ->cartesPapier())
            ->setData(
            [
                'selection' => 'nouvelle',
                'critere' => 'tous',
                'document' => 'Liste de contrôle des cartes'
            ]);
        // initialisation des documentId à utiliser pour les cartes, étiquettes, liste de
        // controle
        $tLibelles = $this->db_manager->get('Sbm\Db\System\Libelles');
        $where = new Where();
        $where->equalTo('nature', 'ImpressionCartes')->greaterThanOrEqualTo('code', 1);
        $aLibellesImpressionCartes = $tLibelles->fetchAll($where, 'code')->toArray();
        $form2->setDocumentValueOptions($aLibellesImpressionCartes, $this->db_manager);
        // -------- fin de l'initialisation des documentId -------
        if (array_key_exists('nouvelle', $args)) {
            $this->db_manager->get(\SbmGestion\Model\Cartes\Cartes::class)->nouveauLot(
                $millesime, $dateDebut, 1);
            $form2->setValueOptions('dateReprise',
                $this->db_manager->get('Sbm\Db\Select\DatesCartes')
                    ->cartesPapier());
        } elseif (array_key_exists('submit', $args)) {
            $form2->setData($args);
            if ($form2->isValid()) {
                $where = new Where();
                $criteres = [];
                $expression = [
                    'demandeR1 != 1',
                    'demandeR2 != 1'
                ];
                switch ($args['critere']) {
                    case 'inscrits':
                        $expression[] = '(paiementR1 = 1 OR gratuit > 0)';
                        $where->equalTo('inscrit', 1)
                            ->nest()
                            ->equalTo('paiementR1', 1)->or->greaterThan(
                            'gratuit', 0)->unnest();
                        break;
                    case 'preinscrits':
                        $where1 = new Where();
                        $where1->equalTo('paiementR1', 1)->or->greaterThan(
                            'gratuit', 0);
                        $where->equalTo('inscrit', 1)->addPredicate(new Not($where1));
                        $expression[] = 'paiementR1 = 0';
                        $expression[] = 'gratuit = 0';
                        break;
                    default: // tous
                        break;
                }
                switch ($args['selection']) {
                    case 'nouvelle':
                        $lastDateCarte = $this->db_manager->get('Sbm\Db\Table\Scolarites')->getLastDateCarte();
                        $expression[] = "dateCarteR1 = '$lastDateCarte'";
                        $where->equalTo('dateCarteR1', $lastDateCarte);
                        break;
                    case 'reprise':
                        $dateReprise = $args['dateReprise'];
                        $expression[] = "dateCarteR1 = '$dateReprise'";
                        $where->equalTo('dateCarteR1', $dateReprise);
                        break;
                    case 'selection':
                        // il s'agit ici de la colonne `selection` de la table `eleves`
                        $expression = [
                            'selection = 1'
                        ];
                        $where->literal('selection = 1');
                        break;
                }
                $call_pdf = $this->RenderPdfService;
                $call_pdf->setParam('documentId', $args['document'])
                    ->setParam('where', $where)
                    ->setParam('criteres', $criteres)
                    ->setParam('strict', [
                    'empty' => [],
                    'not empty' => []
                ])
                    ->setParam('expression', $expression)
                    ->setEndOfScriptFunction(
                    function () {
                        $this->flashMessenger()
                            ->addSuccessMessage("Édition de cartes.");
                    })
                    ->renderPdf();
            }
        }
        if ($vue) {
            $lastDateCarte = $this->db_manager->get('Sbm\Db\Table\Scolarites')->getLastDateCarte();
            if ($lastDateCarte < $dateDebut) {
                $e = $form2->get('selection');
                $e->unsetValueOption('nouvelle')->unsetValueOption('reprise');
                $form2->setData([
                    'selection' => 'selection'
                ]);
            }
            return new ViewModel(
                [
                    'form1' => $form1,
                    'form2' => $form2,
                    'lastDateCarte' => $lastDateCarte,
                    'dateDebut' => $dateDebut,
                    'page' => $this->params('page', 1),
                    'natureCartes' => $tLibelles->getLibelle('NatureCartes', 1)
                ]);
        } else {
            die();
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
        $args = $prg ?: [];
        if (array_key_exists('origine', $args)) {
            Session::set('origine', $args['origine'], $this->getSessionNamespace());
        } else {
            $args['origine'] = Session::get('origine', null, $this->getSessionNamespace());
        }
        if (array_key_exists('cancel', $args) || ! array_key_exists('eleveId', $args)) {
            return $this->redirect()->toUrl($args['origine']);
        }
        $millesime = Session::get('millesime');
        $vue = true;
        $tLibelles = $this->db_manager->get('Sbm\Db\System\Libelles');
        $documentName = $tLibelles->getLibelle('ImpressionCartes', 0);
        $documentId = $this->db_manager->get('Sbm\Db\System\Documents')->getDocumentId(
            $documentName);
        $configLabel = $this->db_manager->get('Sbm\Db\System\DocLabels')->getConfig(
            $documentId);
        $fistSublabel = current($configLabel);
        $form = new FormGestion\PlancheEtiquettesForm('duplicata',
            [
                'nbcols' => $fistSublabel['cols_number'],
                'nbrows' => $fistSublabel['rows_number']
            ]);
        $form->add(
            [
                'type' => 'hidden',
                'name' => 'eleveId',
                'attributes' => [
                    'value' => $args['eleveId']
                ]
            ]);
        $form->add(
            [
                'type' => 'hidden',
                'name' => 'origine',
                'attributes' => [
                    'value' => $args['origine']
                ]
            ]);
        $form->add(
            [
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
                $where->equalTo('millesime', $millesime)->equalTo('eleveId',
                    $args['eleveId']);
                $call_pdf = $this->RenderPdfService;
                $call_pdf->setParam('documentId', $documentName)
                    ->setParam('where', $where)
                    ->setParam('criteres', [])
                    ->setParam('expression',
                    [
                        'eleveId = ' . $args['eleveId']
                    ])
                    ->setParam('position', $position)
                    ->setParam('filigrane', true)
                    ->setEndOfScriptFunction(
                    function () use ($millesime, $args) {
                        if (empty($args['gratuit'])) {
                            $this->db_manager->get('Sbm\Db\Table\Scolarites')
                                ->addDuplicata($millesime, $args['eleveId']);
                        }
                        $this->flashMessenger()
                            ->addSuccessMessage("Édition d'un duplicata.");
                    })
                    ->renderPdf();
            }
        }
        if ($vue) {
            return new ViewModel(
                [
                    'form' => $form,
                    'eleveId' => $args['eleveId'],
                    'origine' => $args['origine']
                ]);
        } else {
            die();
        }
    }

    public function photosAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        }
        $vue = true;
        $args = $prg ?: [];
        if (array_key_exists('cancel', $args)) {
            return $this->redirect()->toRoute('sbmgestion/eleve',
                [
                    'action' => 'eleve-liste',
                    'page' => $this->params('page', 1)
                ]);
        }
        $millesime = Session::get('millesime');
        $tCalendar = $this->db_manager->get('Sbm\Db\System\Calendar');
        $dateDebut = $tCalendar->getEtatDuSite()['dateDebut']->format('Y-m-d');
        $form1 = new Form\ButtonForm([],
            [
                'nouvelle' => [
                    'class' => 'button default submit left-95px',
                    'value' => 'Préparer un nouveau lot de photos'
                ]
            ]);
        $form2 = $this->form_manager->get(FormGestion\SelectionPhotos::class);
        $form2->setValueOptions('dateReprise',
            $this->db_manager->get('Sbm\Db\Select\DatesCartes')
                ->extractionsPhotos())
            ->setData(
            [
                'selection' => 'nouvelle',
                'document' => 'Liste de contrôle des cartes'
            ]);
        // initialisation des documentId à utiliser pour les cartes, étiquettes, liste de
        // controle
        $tLibelles = $this->db_manager->get('Sbm\Db\System\Libelles');
        $where = new Where();
        $where->equalTo('nature', 'ImpressionCartes')->greaterThanOrEqualTo('code', 1);
        $aLibellesImpressionCartes = $tLibelles->fetchAll($where, 'code')->toArray();
        $form2->setDocumentValueOptions($aLibellesImpressionCartes, $this->db_manager);
        // -------- fin de l'initialisation des documentId -------
        if (array_key_exists('nouvelle', $args)) {
            $this->db_manager->get(\SbmGestion\Model\Photos\Photos::class)->nouveauLot(
                $millesime, $dateDebut, 2);
            $form2->setValueOptions('dateReprise',
                $this->db_manager->get('Sbm\Db\Select\DatesCartes')
                    ->extractionsPhotos());
        } elseif (array_key_exists('submit', $args)) {
            $form2->setData($args);
            if ($form2->isValid()) {
                $where = new Where();
                $criteres = [];
                $expression = [
                    "millesime = $millesime",
                    'inscrit=1'
                ];
                switch ($args['selection']) {
                    case 'nouvelle':
                        $lastDateCarte = $this->db_manager->get(
                            'Sbm\Db\Table\ElevesPhotos')->getLastDateExtraction();
                        $expression[] = "dateExtraction = '$lastDateCarte'";
                        $where->equalTo('dateExtraction', $lastDateCarte);
                        break;
                    case 'reprise':
                        $dateReprise = $args['dateReprise'];
                        $expression[] = "dateExtraction = '$dateReprise'";
                        $where->equalTo('dateExtraction', $dateReprise);
                        break;
                    case 'ele.selection':
                        // il s'agit ici de la colonne `selection` de la table `eleves`
                        $expression = [
                            "millesime = $millesime",
                            'selection = 1'
                        ];
                        $where->literal('selection = 1');
                        break;
                }
                if ($args['document'] == 'extraction') {
                    return $this->db_manager->get(\SbmGestion\Model\Photos\Photos::class)->renderZip(
                        $where);
                    die('---- photos demandées ----');
                } else {
                    $where->equalTo('millesime', $millesime);
                    $call_pdf = $this->RenderPdfService;
                    $call_pdf->setParam('documentId', $args['document'])
                        ->setParam('where', $where)
                        ->setParam('criteres', $criteres)
                        ->setParam('strict', [
                        'empty' => [],
                        'not empty' => []
                    ])
                        ->setParam('expression', $expression)
                        ->setEndOfScriptFunction(
                        function () {
                            $this->flashMessenger()
                                ->addSuccessMessage("Création d'un pdf.");
                        })
                        ->renderPdf();
                }
            }
        }
        if ($vue) {
            $lastDateCarte = $this->db_manager->get('Sbm\Db\Table\ElevesPhotos')->getLastDateExtraction();
            if ($lastDateCarte < $dateDebut) {
                $e = $form2->get('selection');
                $e->unsetValueOption('nouvelle')->unsetValueOption('reprise');
                $form2->setData([
                    'selection' => 'selection'
                ]);
            }
            return new ViewModel(
                [
                    'form1' => $form1,
                    'form2' => $form2,
                    'lastDateCarte' => $lastDateCarte,
                    'dateDebut' => $dateDebut,
                    //'natureCartes' => $tLibelles->getLibelle('NatureCartes', 2),
                    'page' => $this->params('page', 1),
                ]);
        } else {
            die();
        }
    }
}