<?php
/**
 * Controller du module SbmParent permettant de gérer les inscriptions des enfants
 *
 * @project sbm
 * @package SbmParent/Controller
 * @filesource IndexController.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 4 sept. 2016
 * @version 2016-2.2.0
 */
namespace SbmParent\Controller;

use Zend\Db\Sql\Where;
use Zend\Db\Sql\Predicate\PredicateSet;
use Zend\Http\PhpEnvironment\Response;
use Zend\View\Model\ViewModel;
use SbmBase\Model\Session;
use SbmBase\Model\StdLib;
use SbmCommun\Form\ButtonForm;
use SbmCommun\Model\Mvc\Controller\AbstractActionController;
use SbmCommun\Model\Strategy\Semaine;
use SbmFront\Model\Responsable\Exception as CreateResponsableException;
use SbmGestion\Model\Db\Filtre\Eleve\Filtre as FiltreEleve;
use SbmParent\Form;
use SbmParent\Model\Db\Service\Query;
use SbmParent\Model\OutilsInscription;

class IndexController extends AbstractActionController
{

    public function indexAction()
    {
        try {
            $responsable = $this->responsable->get();
        } catch (\Exception $e) {
            if ($this->authenticate->by()->hasIdentity() && (($e instanceof CreateResponsableException) || ($e->getPrevious() instanceof CreateResponsableException))) {
                // il faut créer un responsable associé car la demande vient d'un gestionnaire ou autre administrateur
                $this->flashMessenger()->addErrorMessage('Il faut compléter la fiche du responsable');
                $retour = $this->url()->fromRoute('sbmparent');
                return $this->redirectToOrigin()
                    ->setBack($retour)
                    ->toRoute('sbmparentconfig', [
                    'action' => 'create'
                ]);
            } else {
                return $this->redirect()->toRoute('login', [
                    'action' => 'logout'
                ]);
            }
        }
        $query = $this->db_manager->get('Sbm\Db\Query\ElevesScolarites');
        $paiements = $this->db_manager->get('Sbm\Db\Vue\Paiements');
        $tCalendar = $this->db_manager->get('Sbm\Db\System\Calendar');
        return new ViewModel([
            'etatSite' => $tCalendar->etatDuSite(),
            'permanences' => $tCalendar->getPermanences(),
            'inscrits' => $query->getElevesInscrits($responsable->responsableId),
            'preinscrits' => $query->getElevesPreinscrits($responsable->responsableId),
            'montant' => $this->db_manager->get('Sbm\Db\Table\Tarifs')->getMontant('inscription'),
            'paiements' => $paiements->fetchAll([
                'responsableId' => $responsable->responsableId
            ]),
            'affectations' => $this->db_manager->get('Sbm\Db\Query\AffectationsServicesStations'),
            'client' => $this->client,
            'accueil' => $this->accueil,
            'adresse' => array_filter([
                $responsable->adresseL1,
                $responsable->adresseL2,
                $responsable->codePostal . ' ' . $responsable->commune,
                implode(' ; ', array_filter([
                    $responsable->telephoneF,
                    $responsable->telephoneP,
                    $responsable->telephoneT
                ]))
            ])
        ]);
    }

    public function inscriptionEleveAction()
    {
        try {
            $responsable = $this->responsable->get();
            $authUserId = $this->authenticate->by()->getUserId();
        } catch (Exception $e) {
            return $this->redirect()->toRoute('login', [
                'action' => 'logout'
            ]);
        }
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        }
        $args = (array) $prg;
        if (array_key_exists('cancel', $args)) {
            return $this->redirect()->toRoute('sbmparent');
        }
        $isPost = array_key_exists('submit', $args);
        $outils = new OutilsInscription($this->db_manager, $responsable->responsableId, $authUserId);
        $form = $this->form_manager->get(Form\Enfant::class);
        $form->setAttribute('action', $this->url()
            ->fromRoute('sbmparent', [
            'action' => 'inscription-eleve'
        ]));
        $form->setValueOptions('etablissementId', $this->db_manager->get('Sbm\Db\Select\Etablissements')
            ->visibles())
            ->setValueOptions('classeId', $this->db_manager->get('Sbm\Db\Select\Classes'))
            ->setValueOptions('joursTransport', Semaine::getJours())
            ->setData([
            'responsable1Id' => $responsable->responsableId
        ]);
        // Le formulaire de garde alterné est prévu complet pour une saisie
        $formga = $this->form_manager->get(Form\Responsable2Complet::class);
        if (array_key_exists('submit', $args)) {
            $form->setData($args);
            $hasGa = StdLib::getParam('ga', $args, false);
            if ($hasGa) {
                $formga->setData($args);
            }
            // Dans form->isValid(), on refuse si existence d'un élève de même nom, prénom, dateN et responsable 1 (ou 2).
            // formga->isValid() n'est regardé que si hasGa.
            if ($form->isValid() && ! ($hasGa && ! $formga->isValid())) {
                // Enregistrement du responsable2 en premier (si on a le droit)
                if ($hasGa) {
                    $responsable2Id = $outils->saveResponsable($formga->getData());
                } else {
                    $responsable2Id = null;
                }
                // Enregistrement de l'élève
                $eleveId = $outils->saveEleve($form->getData(), $hasGa, $responsable2Id);
                // Enregistre la scolarité
                if ($outils->saveScolarite($form->getData(), $eleveId)) {
                    $majDistances = $this->local_manager->get('Sbm\CartographieManager')->get('Sbm\CalculDroitsTransport');
                    $majDistances->majDistancesDistrict($eleveId);
                }
                if ($args['fa']) {
                    $this->flashMessenger()->addSuccessMessage('L\'enfant est inscrit.');
                } else {
                    $this->flashMessenger()->addSuccessMessage('L\'enfant est enregistré.');
                    $this->flashMessenger()->addWarningMessage('Son inscription ne sera prise en compte que lorsque le paiement aura été reçu.');
                }
                return $this->redirect()->toRoute('sbmparent');
            }
        }
        $formga->setValueOptions('r2communeId', $this->db_manager->get('Sbm\Db\Select\Communes')
            ->visibles());
        return new ViewModel([
            'form' => $form->prepare(),
            'formga' => $formga->prepare(),
            'responsable' => $responsable,
            'ga' => StdLib::getParam('ga', $args, 0),
            'userId' => $authUserId
        ]);
    }

    /**
     * Attention à la gestion du SbmParent\Form\Responsable2 ($formga)
     * - les noms d'éléments sont préfixés par r2.
     * Ils le sont aussi dans le POST donc dans args[].
     * - par contre, la méthode setData() permet de placer directement des données, que leurs index soient préfixés on non
     * - et getData() supprime le préfixe r2
     * Cela permet de charger le formulaire par setData() indifféremment depuis la table responsables (sans préfixe)
     * ou depuis le post (avec préfixe). Cela permet également de retrouver les données sans préfixe pour les envoyer dans
     * un objectData() de la table responsables.
     *
     * @return \Zend\Http\Response|\Zend\Http\PhpEnvironment\Response|\Zend\View\Model\ViewModel
     */
    public function editEleveAction()
    {
        try {
            $responsable = $this->responsable->get();
            $authUserId = $this->authenticate->by()->getUserId();
        } catch (Exception $e) {
            return $this->redirect()->toRoute('login', [
                'action' => 'logout'
            ]);
        }
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            $args = Session::get('post', false, $this->getSessionNamespace());
            if ($args === false) {
                Session::remove('responsable2', $this->getSessionNamespace());
                Session::remove('post', $this->getSessionNamespace());
                return $this->redirect()->toRoute('login', [
                    'action',
                    'logout'
                ]);
            }
        } else {
            $args = $prg;
            if (array_key_exists('cancel', $args)) {
                Session::remove('responsable2', $this->getSessionNamespace());
                Session::remove('post', $this->getSessionNamespace());
                return $this->redirect()->toRoute('sbmparent');
            }
            if (array_key_exists('modifier', $args)) {
                Session::set('post', $args, $this->getSessionNamespace());
            } else {
                $args['id'] = $args['eleveId'];
            }
        }
        $isPost = array_key_exists('submit', $args);
        $eleveId = $args['id'];
        $outils = new OutilsInscription($this->db_manager, $responsable->responsableId, $authUserId, $eleveId);
        $form = $this->form_manager->get(Form\Enfant::class);
        $form->setAttribute('action', $this->url()
            ->fromRoute('sbmparent', [
            'action' => 'edit-eleve'
        ]));
        $form->setValueOptions('etablissementId', $this->db_manager->get('Sbm\Db\Select\Etablissements')
            ->visibles())
            ->setValueOptions('classeId', $this->db_manager->get('Sbm\Db\Select\Classes'))
            ->setValueOptions('joursTransport', Semaine::getJours());
        // pour la garde alternée, on doit déterminer si le formulaire sera complet ou non
        // afin d'adapter ses validateurs. S'il n'est pas complet, on passera tout de même
        // responsableId (attention ! dans le post, les champs sont préfixés par r2)
        $formgaComplet = true;
        if ($isPost) {
            $hasGa = StdLib::getParam('ga', $args, false);
            if ($hasGa) {
                $formgaComplet = $owner = $outils->isOwner($args['r2responsable2Id']);
            }
            // s'il n'y a pas de garde alternée, on prévoit le formulaire complet pour le cas
            // où l'utilisateur déciderait d'en rajouter une.
        }
        $formga = $this->form_manager->get($formgaComplet ? Form\Responsable2Complet::class : Form\Responsable2Restreint::class);
        
        if ($isPost) {
            $form->setData($args);
            if ($hasGa) {
                $formga->setData($args);
            }
            // Dans form->isValid(), on refuse si existence d'un élève de même nom, prénom, dateN et n° différent.
            // formga->isValid() n'est regardé que si hasGa.
            if ($form->isValid() && ! ($hasGa && ! $formga->isValid())) {
                // Enregistrement du responsable2 en premier (si on a le droit)
                if ($hasGa) {
                    if ($owner) {
                        $responsable2Id = $outils->saveResponsable($formga->getData());
                    } else {
                        $responsable2Id = $args['r2responsable2Id'];
                    }
                } else {
                    $responsable2Id = null;
                }
                // Enregistrement de l'élève
                $outils->saveEleve($form->getData(), $hasGa, $responsable2Id);
                // Enregistrement de sa scolarité
                if ($outils->saveScolarite($form->getData())) {
                    $majDistances = $this->local_manager->get('Sbm\CartographieManager')->get('Sbm\CalculDroitsTransport');
                    $majDistances->majDistancesDistrict($eleveId);
                }
                Session::remove('responsable2', $this->getSessionNamespace());
                Session::remove('post', $this->getSessionNamespace());
                $this->flashMessenger()->addSuccessMessage('La fiche a été mise à jour.');
                return $this->redirect()->toRoute('sbmparent');
            }
            $responsable2 = Session::get('responsable2', null, $this->getSessionNamespace());
        } else {
            $data = $this->db_manager->get('Sbm\Db\Query\ElevesScolarites')->getEleve($eleveId);
            $hasGa = ! is_null($data['responsable2Id']);
            $data['ga'] = $hasGa ? 1 : 0;
            $form->setData($data);
            if ($hasGa) {
                try {
                    $responsable2 = $this->db_manager->get('Sbm\Db\Vue\Responsables')
                        ->getRecord($data['responsable2Id'])
                        ->getArrayCopy();
                    $owner = $outils->isOwner($data['responsable2Id']);
                    if ($owner) {
                        $formga->setValueOptions('r2communeId', $this->db_manager->get('Sbm\Db\Select\Communes')
                            ->visibles());
                        $formga->setData(array_merge($data, $responsable2));
                    } else {
                        $formga->setData($data);
                    }
                    $responsable2['owner'] = $owner;
                } catch (\SbmCommun\Model\Db\Service\Table\Exception $e) {
                    // on a perdu le responsable2 mais le formulaire va demander de le recréer ou de supprimer la ga
                    $responsable2 = null;
                }
            } else {
                $responsable2 = null;
            }
            Session::set('responsable2', $responsable2, $this->getSessionNamespace());
        }
        try {
            $formga->setValueOptions('r2communeId', $this->db_manager->get('Sbm\Db\Select\Communes')
                ->visibles());
        } catch (\Zend\Form\Exception\InvalidElementException $e) {}
        return new ViewModel([
            'form' => $form->prepare(),
            'formga' => $formga->prepare(),
            'responsable' => $responsable,
            'hasGa' => $hasGa,
            'responsable2' => $responsable2
        ]);
    }

    /**
     * Change l'état d'attente d'un enfant.
     *
     * L'état d'attente est enregistré dans le champ selection de la table scolarites
     * Pas de vue. Renvoie sur la liste une fois le changement effectué
     *
     * @return \Zend\Http\PhpEnvironment\Response
     */
    public function attenteEleveAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        }
        $args = (array) $prg;
        if (array_key_exists('id', $args) && array_key_exists('attente', $args)) {
            // effectuer le changement
            $tscolarite = $this->db_manager->get('Sbm\Db\Table\Scolarites');
            $scolarite = $tscolarite->getRecord([
                'millesime' => Session::get('millesime'),
                'eleveId' => $args['id']
            ]);
            $scolarite->selection = 1 - $scolarite->selection;
            $message = $scolarite->selection ? 'Mise en attente d\'un enfant.' : 'Reprise d\'un enfant.';
            $tscolarite->saveRecord($scolarite);
            $this->flashMessenger()->addSuccessMessage($message);
        }
        return $this->redirect()->toRoute('sbmparent');
    }

    /**
     * Demande confirmation avant de supprimer un enregistrement
     *
     * @return Response
     */
    public function supprEleveAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            $args = Session::get('post', false, $this->getSessionNamespace());
            if ($args === false) {
                return $this->redirect()->toRoute('login', [
                    'action',
                    'logout'
                ]);
            }
        } else {
            $args = $prg;
            if (array_key_exists('supprimer', $args)) {
                Session::set('post', $args, $this->getSessionNamespace());
            } else {
                Session::remove('post', $this->getSessionNamespace());
            }
        }
        if (array_key_exists('supprnon', $args) || ! array_key_exists('id', $args)) {
            return $this->redirect()->toRoute('sbmparent');
        }
        $form = new ButtonForm([
            'id' => $args['id']
        ], [
            'supproui' => [
                'class' => 'confirm',
                'value' => 'Confirmer'
            ],
            'supprnon' => [
                'class' => 'confirm',
                'value' => 'Abandonner'
            ]
        ]);
        $millesime = Session::get('millesime');
        if (array_key_exists('supproui', $args)) {
            $where = new Where();
            $where->equalTo('millesime', $millesime)->equalTo('eleveId', $args['id']);
            $this->db_manager->get('Sbm\Db\Table\Affectations')->deleteRecord($where);
            $this->db_manager->get('Sbm\Db\Table\Scolarites')->deleteRecord($where);
            $this->flashMessenger()->addSuccessMessage('Suppression effectuée.');
            return $this->redirect()->toRoute('sbmparent');
        }
        
        return new ViewModel([
            'form' => $form->prepare(),
            'eleve' => $this->db_manager->get('Sbm\Db\Query\ElevesScolarites')->getEleve($args['id']),
            'affectations' => $this->db_manager->get('Sbm\Db\Query\AffectationsServicesStations')->getCorrespondances($args['id'])
        ]);
    }

    /**
     * Cette méthode n'est pas utilisée pour SystemPay car elle n'ouvre pas correctement la page de paiement.
     *
     * Doit lancer un évènement
     * - identifiant : 'SbmPaiement\AppelPlateforme'
     * - évènement : 'appelPaiement'
     * - target : objet enregistré sous 'SbmPaiement\Plugin\Plateforme'
     * - params : array(
     * 'montant' => ..., // en euros
     * 'count' => 1, // 1 pour un règlement comptant (sinon, le nombre d'échéances)
     * 'first' => montant, // égal au montant en euros pour un paiement comptant
     * 'period' => 1, // peu importe pour un paiement comptant
     * 'email' => ..., // du responsable
     * 'responsableId' => ...,
     * 'nom' => ..., // du responsable
     * 'prenom' => ..., // du responsable
     * 'eleveIds' => array(eleveId, eleveId, ...) // tableau simple des eleveId concernés
     * )
     *
     * @return \Zend\Http\PhpEnvironment\Response
     */
    public function payerAction()
    {
        try {
            $responsable = $this->responsable->get();
        } catch (Exception $e) {
            return $this->redirect()->toRoute('login', [
                'action' => 'logout'
            ]);
        }
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            return $this->redirect()->toRoute('sbmparent');
        }
        $args = (array) $prg;
        // args = array('montant' => ..., 'payer' => ...)
        $preinscrits = $this->db_manager->get('Sbm\Db\Query\ElevesScolarites')->getElevesPreinscrits($responsable->responsableId);
        $elevesIds = [];
        foreach ($preinscrits as $row) {
            if (! $row['selectionScolarite']) {
                $elevesIds[] = $row['eleveId'];
            }
        }
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
        $this->getEventManager()->addIdentifiers('SbmPaiement\AppelPlateforme');
        $this->getEventManager()->trigger('appelPaiement', $this->local_manager->get('SbmPaiement\Plugin\Plateforme'), $params);
        return $this->redirect()->toUrl('https://paiement.systempay.fr/vads-payment/');
    }

    public function horairesAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            $args = $this->getFromSession('post', false, $this->getSessionNamespace());
            if ($args === false) {
                return $this->redirect()->toRoute('sbmparent');
            }
        } else {
            $args = $prg;
            if (! array_key_exists('circuit1Id', $args)) {
                return $this->redirect()->toRoute('sbmparent');
            }
            $this->setToSession('post', $args, $this->getSessionNamespace());
        }
        $tCircuits = $this->db_manager->get('Sbm\Db\Vue\Circuits');
        $rEffectifs = $this->db_manager->get('Sbm\Db\Eleve\Effectif')->byCircuit(true);
        $rListe = $this->db_manager->get('Sbm\Db\Eleve\Liste');
        $nbInscrits = [];
        $circuits = [];
        $eleves = [];
        for ($i = 1; array_key_exists('circuit' . $i . 'Id', $args); $i ++) {
            $circuitId = $args['circuit' . $i . 'Id'];
            $circuits[$i] = $tCircuits->getRecord($circuitId);
            $nbInscrits[$i] = $rEffectifs[$circuitId]['total'];
            $result = $rListe->query(Session::get('millesime'), FiltreEleve::byCircuit($circuits[$i]->serviceId, $circuits[$i]->stationId, true), [
                'nom',
                'prenom'
            ]);
            foreach ($result as $row) {
                $classe = $row['classe'];
                if (is_numeric($classe)) {
                    $classe .= '°';
                }
                $eleves[$i][] = sprintf('%s %s - %s', $row['prenom'], $row['nom'], $classe);
            }
            $serviceId = $circuits[$i]->serviceId; // on gardera le dernier trouvé
        }
        // ajout de l'arrêt à l'établissement
        $stationId = $this->db_manager->get('Sbm\Db\Table\EtablissementsServices')->getRecord([
            'etablissementId' => $args['etablissementId'],
            'serviceId' => $serviceId
        ])->stationId;
        $circuits[$i] = $tCircuits->getCircuit(Session::get('millesime'), $serviceId, $stationId);
        return new ViewModel([
            'enfant' => $args['enfant'],
            'circuits' => $circuits,
            'eleves' => $eleves,
            't_nb_inscrits' => $nbInscrits
        ]);
    }

    public function reinscriptionEleveAction()
    {
        try {
            $auth_responsable = $this->responsable->get();
            $authUserId = $this->authenticate->by()->getUserId();
        } catch (Exception $e) {
            return $this->redirect()->toRoute('login', [
                'action' => 'logout'
            ]);
        }
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            $args = $this->getFromSession('post', false, $this->getSessionNamespace());
            if ($args === false) {
                Session::remove('responsable2', $this->getSessionNamespace());
                Session::remove('post', $this->getSessionNamespace());
                return $this->redirect()->toRoute('sbmparent');
            }
        } else {
            $args = $prg;
            if (array_key_exists('cancel', $args)) {
                Session::remove('responsable2', $this->getSessionNamespace());
                Session::remove('post', $this->getSessionNamespace());
                return $this->redirect()->toRoute('sbmparent');
            }
            if (array_key_exists('inscrire', $args)) {
                Session::set('post', $args, $this->getSessionNamespace());
            } else {
                $args['id'] = StdLib::getParam('eleveId', $args);
                if (is_null($args['id'])) {
                    Session::remove('responsable2', $this->getSessionNamespace());
                    Session::remove('post', $this->getSessionNamespace());
                    return $this->redirect()->toRoute('sbmparent');
                }
            }
        }
        $phase = StdLib::getParam('phase', $args, 1);
        if ($phase == 1) {
            $aReinscrire = $this->db_manager->get(Query\Eleves::class)->aReinscrire($auth_responsable->responsableId);
            if ($aReinscrire->count() == 0) {
                Session::remove('responsable2', $this->getSessionNamespace());
                Session::remove('post', $this->getSessionNamespace());
                return $this->redirect()->toRoute('sbmparent', [
                    'action' => 'inscription-eleve'
                ]);
            }
            $responsable2 = [];
            $hasGa = false;
            $form = null;
            $formga = null;
        } else {
            $aReinscrire = [];
            $isPost = array_key_exists('submit', $args);
            $eleveId = $args['id'];
            $outils = new OutilsInscription($this->db_manager, $auth_responsable->responsableId, $authUserId, $eleveId);
            $form = $this->form_manager->get(Form\Enfant::class);
            $form->setAttribute('action', $this->url()
                ->fromRoute('sbmparent', [
                'action' => 'reinscription-eleve'
            ]))
                ->add([
                'type' => 'hidden',
                'name' => 'phase',
                'attributes' => [
                    'value' => $phase
                ]
            ]);
            $form->setValueOptions('etablissementId', $this->db_manager->get('Sbm\Db\Select\Etablissements')
                ->visibles())
                ->setValueOptions('classeId', $this->db_manager->get('Sbm\Db\Select\Classes'))
                ->setValueOptions('joursTransport', Semaine::getJours());
            // pour la garde alternée, on doit déterminer si le formulaire sera complet ou non
            // afin d'adapter ses validateurs. S'il n'est pas complet, on passera tout de même
            // responsableId (attention ! dans le post, les champs sont préfixés par r2)
            $formgaComplet = true;
            if ($isPost) {
                $hasGa = StdLib::getParam('ga', $args, false);
                if ($hasGa) {
                    $formgaComplet = $owner = $outils->isOwner($args['r2responsable2Id']);
                }
                // s'il n'y a pas de garde alternée, on prévoit le formulaire complet pour le cas
                // où l'utilisateur déciderait d'en rajouter une.
            }
            $formga = $this->form_manager->get($formgaComplet ? Form\Responsable2Complet::class : Form\Responsable2Restreint::class);
            
            if ($isPost) {
                $form->setData($args);
                $hasGa = StdLib::getParam('ga', $args, false);
                if ($hasGa) {
                    $formga->setData($args);
                }
                // Dans form->isValid(), on refuse si existence d'un élève de même nom, prénom, dateN et n° différent.
                // formga->isValid() n'est regardé que si hasGa.
                if ($form->isValid() && ! ($hasGa && ! $formga->isValid())) {
                    // Enregistrement du responsable2 en premier (si on a le droit)
                    if ($hasGa) {
                        if ($owner) {
                            $responsable2Id = $outils->saveResponsable($formga->getData());
                        } else {
                            $responsable2Id = $args['r2responsable2Id'];
                        }
                    } else {
                        $responsable2Id = null;
                    }
                    // Enregistrement de l'élève
                    $outils->saveEleve($form->getData(), $hasGa, $responsable2Id);
                    // Enregistrement de sa scolarité
                    if ($outils->saveScolarite($form->getData(), $eleveId)) {
                        $majDistances = $this->local_manager->get('Sbm\CartographieManager')->get('Sbm\CalculDroitsTransport');
                        $majDistances->majDistancesDistrict($eleveId);
                    }
                    // affectation si l'adresse et la scolarité n'ont pas changé
                    $outils->repriseAffectations();
                    // compte-rendu et nettoyage de la session
                    Session::remove('responsable2', $this->getSessionNamespace());
                    Session::remove('post', $this->getSessionNamespace());
                    if ($args['fa']) {
                        $this->flashMessenger()->addSuccessMessage('L\'enfant est inscrit.');
                    } else {
                        $this->flashMessenger()->addSuccessMessage('L\'enfant est enregistré.');
                        $this->flashMessenger()->addWarningMessage('Son inscription ne sera prise en compte que lorsque le paiement aura été reçu.');
                    }
                    return $this->redirect()->toRoute('sbmparent');
                }
                $responsable2 = Session::get('responsable2', null, $this->getSessionNamespace());
            } else {
                // initialisation des formulaires
                $data = $this->db_manager->get(Query\Eleves::class)->getEleve($eleveId);
                unset($data['classeId']);
                $hasGa = ! is_null($data['responsable2Id']);
                $data['ga'] = $hasGa ? 1 : 0;
                if ($auth_responsable->responsableId == StdLib::getParam('responsable2Id', $data, 0)) {
                    // L'inscription est réalisée par l'ancien responsable2 qui passe responsable1
                    $r1 = [
                        'responsableId' => $data['responsable1Id'],
                        'x1' => $data['x1'],
                        'y1' => $data['y1']
                    ];
                    $data['responsable1Id'] = $data['responsable2Id'];
                    $data['x1'] = $data['x2'];
                    $data['y1'] = $data['y2'];
                    // et l'ancien responsable1 passe responsable 2
                    $data['responsable2Id'] = $r1['responsableId'];
                    $data['x2'] = $r1['x2'];
                    $data['y2'] = $r1['y2'];
                }
                if ($hasGa) {
                    try {
                        $responsable2 = $this->db_manager->get('Sbm\Db\Vue\Responsables')
                            ->getRecord($data['responsable2Id'])
                            ->getArrayCopy();
                        $owner = $outils->isOwner($data['responsable2Id']);
                        if ($owner) {
                            $formga->setValueOptions('r2communeId', $this->db_manager->get('Sbm\Db\Select\Communes')
                                ->visibles());
                            $formga->setData(array_merge($data, $responsable2));
                        } else {
                            $formga->setData($data);
                        }
                        $responsable2['owner'] = $owner;
                    } catch (\SbmCommun\Model\Db\Service\Table\Exception $e) {
                        // on a perdu le responsable2 mais le formulaire va demander de le recréer ou de supprimer la ga
                        $responsable2 = null;
                    }
                } else {
                    $responsable2 = null;
                }
                $form->setData($data);
                Session::set('responsable2', $responsable2, $this->getSessionNamespace());
            }
            try {
                $formga->setValueOptions('r2communeId', $this->db_manager->get('Sbm\Db\Select\Communes')
                    ->visibles());
            } catch (\Zend\Form\Exception\InvalidElementException $e) {}
        }
        return new ViewModel([
            'phase' => $phase,
            'aReinscrire' => $aReinscrire,
            'responsable' => $auth_responsable,
            'responsable2' => $responsable2,
            'hasGa' => $hasGa,
            'form' => $form,
            'formga' => $formga
        ]);
    }
} 