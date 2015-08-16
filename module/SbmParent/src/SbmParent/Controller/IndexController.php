<?php
/**
 * Controller du module SbmParent permettant de gérer les inscriptions des enfants
 *
 * @project sbm
 * @package SbmParent/Controller
 * @filesource IndexController.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 4 févr. 2015
 * @version 2015-1
 */
namespace SbmParent\Controller;

use Zend\Db\Sql\Where;
use Zend\Http\PhpEnvironment\Response;
use Zend\View\Model\ViewModel;
use DafapSession\Model\Session;
use SbmCommun\Form\ButtonForm;
use SbmCommun\Model\Mvc\Controller\AbstractActionController;
use SbmCommun\Model\StdLib;
use SbmCommun\Model\Strategy\Semaine;
use SbmParent\Form\Enfant;
use SbmParent\Form\Responsable2 as FormResponsable2;
use SbmParent\Model\Responsable;
use SbmParent\Model\Exception as CreateResponsableException;

class IndexController extends AbstractActionController
{

    public function indexAction()
    {
        try {
            $responsable = new Responsable($this->getServiceLocator());
        } catch (CreateResponsableException $e) {
            if ($this->getServiceLocator()
                ->get('Dafap\Authenticate')
                ->by()
                ->hasIdentity()) {
                // il faut créer un responsable associé car la demande vient d'un gestionnaire ou autre administrateur
                $this->flashMessenger()->addErrorMessage('Il faut compléter la fiche du responsable');
                $retour = $this->url()->fromRoute('sbmparent');
                return $this->redirectToOrigin()
                    ->setBack($retour)
                    ->toRoute('sbmparentconfig', array(
                    'action' => 'create'
                ));
            } else {
                return $this->redirect()->toRoute('login', array(
                    'action' => 'logout'
                ));
            }
        }
        $query = $this->getServiceLocator()->get('Sbm\Db\Query\ElevesScolarites');
        $paiements = $this->getServiceLocator()->get('Sbm\Db\Vue\Paiements');
        $tCalendar = $this->getServiceLocator()->get('Sbm\Db\System\Calendar');
        return new ViewModel(array(
            'etatSite' => $tCalendar->etatDuSite(),
            'permanences' => $tCalendar->getPermanences(),
            'inscrits' => $query->getElevesInscrits($responsable->responsableId),
            'preinscrits' => $query->getElevesPreinscrits($responsable->responsableId),
            'montant' => $this->getServiceLocator()
                ->get('Sbm\Db\Table\Tarifs')
                ->getMontant('inscription'),
            'paiements' => $paiements->fetchAll(array(
                'responsableId' => $responsable->responsableId
            )),
            'affectations' => $this->getServiceLocator()->get('Sbm\Db\Query\AffectationsServicesStations'),
            'client' => StdLib::getParamR(array(
                'sbm',
                'client'
            ), $this->getServiceLocator()->get('config')),
            'accueil' => StdLib::getParamR(array(
                'sbm',
                'layout',
                'accueil'
            ), $this->getServiceLocator()->get('config'))
        ));
    }

    public function inscriptionEleveAction()
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
        }
        $args = (array) $prg;
        if (array_key_exists('cancel', $args)) {
            return $this->redirect()->toRoute('sbmparent');
        }
        $isPost = array_key_exists('submit', $args);
        
        $form = new Enfant($this->getServiceLocator());
        $form->setAttribute('action', $this->url()
            ->fromRoute('sbmparent', array(
            'action' => 'inscription-eleve'
        )));
        $form->setValueOptions('etablissementId', $this->getServiceLocator()
            ->get('Sbm\Db\Select\EtablissementsVisibles'))
            ->setValueOptions('classeId', $this->getServiceLocator()
            ->get('Sbm\Db\Select\Classes'))
            ->setValueOptions('joursTransport', Semaine::getJours())
            ->setData(array(
            'responsable1Id' => $responsable->responsableId
        ));
        // Le formulaire de garde alterné est prévu complet pour une saisie
        $formga = new FormResponsable2(true);
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
                    $tResponsables = $this->getServiceLocator()->get('Sbm\Db\Table\Responsables');
                    $oData = $tResponsables->getObjData();
                    $oData->exchangeArray($formga->getData());
                    if (! $oData->userId) {
                        $oData->userId = $this->getServiceLocator()
                            ->get('Dafap\Authenticate')
                            ->by()
                            ->getUserId();
                    }
                    if ($tResponsables->saveRecord($oData)) {
                        // on s'assure de rendre cette commune visible
                        $this->getServiceLocator()
                            ->get('Sbm\Db\table\Communes')
                            ->setVisible($oData->communeId);
                    }
                    // on récupère le responsableId qui vient d'être enregistré,
                    // que ce soit un insert, un update ou la reprise d'un autre responsable par son email
                    $responsable2Id = $tResponsables->getLastResponsableId();
                }
                
                // Enregistrement de l'élève
                $tEleves = $this->getServiceLocator()->get('Sbm\Db\Table\Eleves');
                $oData = $tEleves->getObjData();
                if ($hasGa) {
                    $oData->exchangeArray(array_merge($form->getData(), array(
                        'responsable1Id' => $responsable->responsableId,
                        'responsable2Id' => $responsable2Id
                    )));
                } else {
                    $oData->exchangeArray(array_merge($form->getData(), array(
                        'responsable1Id' => $responsable->responsableId
                    )));
                }
                $tEleves->saveRecord($oData);
                $eleveId = $oData->eleveId;
                if (empty($eleveId)) {
                    $eleveId = $tEleves->getTableGateway()->getLastInsertValue();
                }
                // Enregistre la scolarité
                $tScolarites = $this->getServiceLocator()->get('Sbm\Db\Table\Scolarites');
                $oData = $tScolarites->getObjData();
                $oData->exchangeArray(array_merge($form->getData(), array(
                    'millesime' => Session::get('millesime'),
                    'eleveId' => $eleveId,
                    'paiement' => 0,
                    'tarifId' => $this->getServiceLocator()
                        ->get('Sbm\Db\Table\Tarifs')
                        ->getTarifId('inscription')
                )));
                $tScolarites->saveRecord($oData);
                $majDistances = $this->getServiceLocator()->get('Sbm\CalculDroitsTransport');
                $majDistances->majDistancesDistrict($eleveId);
                if ($oData->fa) {
                    $this->flashMessenger()->addSuccessMessage('L\'enfant est inscrit.');
                } else {
                    $this->flashMessenger()->addSuccessMessage('L\'enfant est enregistré.');
                    $this->flashMessenger()->addWarningMessage('Son inscription ne sera prise en compte que lorsque le paiement aura été reçu.');
                }
                return $this->redirect()->toRoute('sbmparent');
            }
        }
        $formga->setValueOptions('r2communeId', $this->getServiceLocator()
            ->get('Sbm\Db\Select\Communes')
            ->visibles());
        return new ViewModel(array(
            'form' => $form->prepare(),
            'formga' => $formga->prepare(),
            'responsable' => $responsable,
            'ga' => StdLib::getParam('ga', $args, 0),
            'userId' => $this->getServiceLocator()
                ->get('Dafap\Authenticate')
                ->by()
                ->getUserId()
        ));
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
            $responsable = new Responsable($this->getServiceLocator());
            $authUserId = $this->getServiceLocator()
                ->get('Dafap\Authenticate')
                ->by()
                ->getUserId();
        } catch (Exception $e) {
            return $this->redirect()->toRoute('login', array(
                'action' => 'logout'
            ));
        }
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            $args = Session::get('post', false, $this->getSessionNamespace());
            if ($args === false) {
                return $this->redirect()->toRoute('login', array(
                    'action',
                    'logout'
                ));
            }
        } else {
            $args = $prg;
            if (array_key_exists('cancel', $args)) {
                $this->redirect()->toRoute('sbmparent');
            }
            if (array_key_exists('modifier', $args)) {
                Session::set('post', $args, $this->getSessionNamespace());
            } else {
                $args['id'] = $args['eleveId'];
            }
        }
        $isPost = array_key_exists('submit', $args);
        $eleveId = $args['id'];
        $form = new Enfant($this->getServiceLocator());
        $form->setAttribute('action', $this->url()
            ->fromRoute('sbmparent', array(
            'action' => 'edit-eleve'
        )));
        $form->setValueOptions('etablissementId', $this->getServiceLocator()
            ->get('Sbm\Db\Select\EtablissementsVisibles'))
            ->setValueOptions('classeId', $this->getServiceLocator()
            ->get('Sbm\Db\Select\Classes'))
            ->setValueOptions('joursTransport', Semaine::getJours());
        // pour la garde alternée, on doit déterminer si le formulaire sera complet ou non
        // afin d'adapter ses validateurs. S'il n'est pas complet, on passera tout de même
        // responsableId (attention ! dans le post, les champs sont préfixés par r2)
        $formgaComplet = true;
        if ($isPost) {
            $hasGa = StdLib::getParam('ga', $args, false);
            if ($hasGa) {
                // il faut aller chercher le userId du responsable pour déterminer le owner.
                $tResponsables = $this->getServiceLocator()->get('Sbm\Db\Table\Responsables');
                try {
                    $userId = $tResponsables->getRecord($args['r2responsable2Id'])->userId;
                    $owner = $userId == $authUserId;
                    $formgaComplet = $owner;
                } catch (\SbmCommun\Model\Db\Service\Table\Exception $e) {
                    $formgaComplet = $owner = true;
                }
            }
            // s'il n'y a pas de garde alternée, on prévoit le formulaire complet pour le cas
            // où l'utilisateur déciderait d'en rajouter une.
        }
        $formga = new FormResponsable2($formgaComplet);
        
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
                        $tResponsables = $this->getServiceLocator()->get('Sbm\Db\Table\Responsables');
                        $oData = $tResponsables->getObjData();
                        $oData->exchangeArray($formga->getData());
                        if (! $oData->userId) {
                            $oData->userId = $this->getServiceLocator()
                                ->get('Dafap\Authenticate')
                                ->by()
                                ->getUserId();
                        }
                        if ($tResponsables->saveRecord($oData)) {
                            // on s'assure de rendre cette commune visible
                            $this->getServiceLocator()
                                ->get('Sbm\Db\table\Communes')
                                ->setVisible($oData->communeId);
                        }
                        // on récupère le responsableId qui vient d'être enregistré,
                        // que ce soit un insert, un update ou la reprise d'un autre responsable par son email
                        $responsable2Id = $tResponsables->getLastResponsableId();
                    } else {
                        $responsable2Id = $args['r2responsable2Id'];
                    }
                }
                // Enregistrement de l'élève
                $tEleves = $this->getServiceLocator()->get('Sbm\Db\Table\Eleves');
                $oData = $tEleves->getObjData();
                if ($hasGa) {
                    $oData->exchangeArray(array_merge($form->getData(), array(
                        'responsable2Id' => $responsable2Id
                    )));
                } else {
                    $oData->exchangeArray($form->getData());
                }
                $tEleves->saveRecord($oData);
                // $eleveId = $tEleves->getTableGateway()->getLastInsertValue();
                // Enregistrement de sa scolarité
                $tScolarites = $this->getServiceLocator()->get('Sbm\Db\Table\Scolarites');
                $oData = $tScolarites->getObjData();
                $oData->exchangeArray(array_merge($form->getData(), array(
                    'millesime' => Session::get('millesime')
                )));
                $recalcul = $tScolarites->saveRecord($oData);
                if ($recalcul) {
                    $majDistances = $this->getServiceLocator()->get('Sbm\CalculDroitsTransport');
                    $majDistances->majDistancesDistrict($eleveId);
                }
                Session::remove('responsable2', $this->getSessionNamespace());
                $this->flashMessenger()->addSuccessMessage('La fiche a été mise à jour.');
                Session::remove('post', $this->getSessionNamespace());
                return $this->redirect()->toRoute('sbmparent');
            }
            $responsable2 = Session::get('responsable2', null, $this->getSessionNamespace());
        } else {
            $data = $this->getServiceLocator()
                ->get('Sbm\Db\Query\ElevesScolarites')
                ->getEleve($eleveId);
            $hasGa = ! is_null($data['responsable2Id']);
            $data['ga'] = $hasGa ? 1 : 0;
            $form->setData($data);
            if ($hasGa) {
                try {
                    $responsable2 = $this->getServiceLocator()
                        ->get('Sbm\Db\Vue\Responsables')
                        ->getRecord($data['responsable2Id'])
                        ->getArrayCopy();
                    $owner = $responsable2['userId'] == $authUserId;
                    if ($owner) {
                        $formga->setValueOptions('r2communeId', $this->getServiceLocator()
                            ->get('Sbm\Db\Select\Communes')
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
            $formga->setValueOptions('r2communeId', $this->getServiceLocator()
                ->get('Sbm\Db\Select\Communes')
                ->visibles());
        } catch (\Zend\Form\Exception\InvalidElementException $e) {}
        return new ViewModel(array(
            'form' => $form->prepare(),
            'formga' => $formga->prepare(),
            'responsable' => $responsable,
            'hasGa' => $hasGa,
            'responsable2' => $responsable2
        ));
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
            $tscolarite = $this->getServiceLocator()->get('Sbm\Db\Table\Scolarites');
            $scolarite = $tscolarite->getRecord(array(
                'millesime' => Session::get('millesime'),
                'eleveId' => $args['id']
            ));
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
                return $this->redirect()->toRoute('login', array(
                    'action',
                    'logout'
                ));
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
        $form = new ButtonForm(array(
            'id' => $args['id']
        ), array(
            'supproui' => array(
                'class' => 'confirm',
                'value' => 'Confirmer'
            ),
            'supprnon' => array(
                'class' => 'confirm',
                'value' => 'Abandonner'
            )
        ));
        $millesime = Session::get('millesime');
        if (array_key_exists('supproui', $args)) {
            $where = new Where();
            $where->equalTo('millesime', $millesime)->equalTo('eleveId', $args['id']);
            $this->getServiceLocator()
                ->get('Sbm\Db\Table\Affectations')
                ->deleteRecord($where);
            $this->getServiceLocator()
                ->get('Sbm\Db\Table\Scolarites')
                ->deleteRecord($where);
            $this->flashMessenger()->addSuccessMessage('Suppression effectuée.');
            $this->redirect()->toRoute('sbmparent');
        }
        
        return new ViewModel(array(
            'form' => $form->prepare(),
            'eleve' => $this->getServiceLocator()
                ->get('Sbm\Db\Query\ElevesScolarites')
                ->getEleve($args['id']),
            'affectations' => $this->getServiceLocator()
                ->get('Sbm\Db\Query\AffectationsServicesStations')
                ->getCorrespondances($args['id'])
        ));
    }

    /**
     * Cette méthode n'est pas utilisée pour SystemPay car elle n'ouvre pas correctement la page de paiement.
     *
     * Doit lancer un évènement
     * - identifiant : 'SbmPaiement\AppelPlateforme'
     * - évènement : 'appelPaiement'
     * - target : service manager
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
        $args = (array) $prg;
        // args = array('montant' => ..., 'payer' => ...)
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
        $this->getEventManager()->addIdentifiers('SbmPaiement\AppelPlateforme');
        $this->getEventManager()->trigger('appelPaiement', $this->getServiceLocator(), $params);
        // return $this->redirect()->toRoute('sbmparent');
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
        $tCircuits = $this->getServiceLocator()->get('Sbm\Db\Vue\Circuits');
        $rEffectifs = $this->getServiceLocator()
            ->get('Sbm\Db\Eleve\Effectif')
            ->byCircuit();
        $rListe = $this->getServiceLocator()->get('Sbm\Db\Eleve\Liste');
        $nbInscrits = array();
        $circuits = array();
        $eleves = array();
        for ($i = 1; array_key_exists('circuit' . $i . 'Id', $args); $i ++) {
            $circuitId = $args['circuit' . $i . 'Id'];
            $circuits[$i] = $tCircuits->getRecord($circuitId);
            $nbInscrits[$i] = $rEffectifs[$circuitId]['total'];
            $result = $rListe->byCircuit(Session::get('millesime'), array(
                array(
                    'service1Id' => $circuits[$i]->serviceId,
                    'station1Id' => $circuits[$i]->stationId
                ),
                'or',
                array(
                    'service2Id' => $circuits[$i]->serviceId,
                    'station2Id' => $circuits[$i]->stationId
                )
            ), array('nom', 'prenom'));
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
        $stationId = $this->getServiceLocator()
            ->get('Sbm\Db\Table\EtablissementsServices')
            ->getRecord(array(
            'etablissementId' => $args['etablissementId'],
            'serviceId' => $serviceId
        ))->stationId;
        $circuits[$i] = $tCircuits->getCircuit(Session::get('millesime'), $serviceId, $stationId);
        return new ViewModel(array(
            'enfant' => $args['enfant'],
            'circuits' => $circuits,
            'eleves' => $eleves,
            't_nb_inscrits' => $nbInscrits
        ));
    }
} 