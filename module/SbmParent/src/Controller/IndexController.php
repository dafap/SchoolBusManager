<?php
/**
 * Controller du module SbmParent permettant de gérer les inscriptions des enfants
 *
 * Version de Millau Grands Causses
 *
 * @project sbm
 * @package SbmParent/Controller
 * @filesource IndexController.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 19 mai 2021
 * @version 2021-2.6.2
 */
namespace SbmParent\Controller;

use SbmBase\Model\Session;
use SbmAuthentification\Model\CategoriesInterface;
use SbmBase\Model\StdLib;
use SbmCommun\Form\ButtonForm;
use SbmCommun\Model\Mvc\Controller\AbstractActionController;
use SbmCommun\Model\Strategy\Semaine;
use SbmFront\Model\Responsable\Exception as CreateResponsableException;
use SbmParent\Form;
use SbmParent\Model\OutilsInscription;
use SbmParent\Model\Db\Service\Query;
use Zend\Db\Sql\Where;
use Zend\Http\PhpEnvironment\Response;
use Zend\Log\Logger;
use Zend\View\Model\ViewModel;
use SbmCartographie\GoogleMaps;
use SbmCartographie\Model\Point;
use SbmCommun\Form\LatLng as LatLngForm;

class IndexController extends AbstractActionController
{
    use \SbmCommun\Model\Traits\DebugTrait;

    /**
     * Place des commentaires sur l'écran suite à une demande de transport ne
     * correspondant pas à un établissement auquel on a droit.
     * Une dérogation est
     * nécessaire.
     *
     * @param array $cr
     */
    private function warningCompteRendu($cr)
    {
        $this->debugInitLog(Stdlib::findParentPath(__DIR__, 'data/logs'), 'sbm_error.log');
        $this->debugLog(__METHOD__);
        $this->debugLog($cr);
    }

    private function controleEtatDuSite()
    {
        if ($this->authenticate->by()->getCategorieId() <
            CategoriesInterface::SUPER_ADMINISTRATEUR_ID) {
            $tCalendar = $this->db_manager->get('Sbm\Db\System\Calendar');
            if (! $tCalendar->getEtatDuSite()['inscription']) {
                throw new \Exception('Inscriptions fermées');
            }
        }
    }

    /**
     * A noter que `paiementenligne` vient de l'objet
     * SbmFront\Model\Responsable\Responsable initialisé par la vue `responsables` de la
     * base de données qui va chercher la valeur dans la table `communes`.
     * A noter aussi
     * la mise en place du contrôle par session (post et nsArgsFacture) afin de récupérer
     * le responsableId lors d'une demande de facture ou de paiement.
     *
     * {@inheritdoc}
     * @see \Zend\Mvc\Controller\AbstractActionController::indexAction()
     */
    public function indexAction()
    {
        $millesime = Session::get('millesime');
        $anterieur = $millesime - 1;
        try {
            $responsable = $this->responsable->get();
            // mise en session pour contrôler la demande de factures et de paiement
            Session::set('post', [
                'responsableId' => $responsable->responsableId
            ], $this->getSessionNamespace());
            Session::set('millesime', $millesime, $this->getSessionNamespace());
            Session::set('nsArgsFacture', $this->getSessionNamespace());
        } catch (\Exception $e) {
            if ($this->authenticate->by()->hasIdentity() &&
                (($e instanceof CreateResponsableException) ||
                ($e->getPrevious() instanceof CreateResponsableException))) {
                // il faut créer un responsable associé car la demande vient d'un
                // gestionnaire ou autre administrateur
                $this->flashMessenger()->addErrorMessage(
                    'Il faut compléter la fiche du responsable');
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
        $categorieId = $this->authenticate->by()->getCategorieId();
        // controle si des paiements en ligne ont été fait sans être notifiés
        if ($this->plugin_plateforme instanceof \SbmPaiement\Plugin\PlateformeInterface) {
            try {
                $this->plugin_plateforme->setResponsable($responsable)->checkPaiement();
            } catch (\Exception $e) {
                $message = $e->getMessage();
                $this->flashMessenger()->addErrorMessage($message);
            }
        }
        // controle des impayés de l'année précédente
        $ctrl = new \SbmParent\Model\VerifSoldeAnterieur($this->db_manager,
            $responsable->responsableId);
        if (! $ctrl->valid($anterieur)) {
            // if (! $ctrl->valid($millesime)) { // pour DEBUG
            return $this->redirect()->toRoute('sbmparent',
                [
                    'action' => 'impayes-anterieurs'
                ]);
        }
        $ctrl->clear();
        // fin du contrôle de paiement en ligne
        $query = $this->db_manager->get('Sbm\Db\Query\ElevesScolarites');
        $tCalendar = $this->db_manager->get('Sbm\Db\System\Calendar');
        $format_telephone = new \SbmCommun\Model\View\Helper\Telephone();
        $as = Session::get('as');
        return new ViewModel(
            [
                'theme' => $this->theme,
                'as_date_debut' => $as['dateDebut'],
                'namespacectrl' => md5('nsArgsFacture'),
                'responsable' => $responsable,
                'calendar' => $tCalendar,
                'inscrits' => $query->getElevesInscrits($responsable->responsableId,
                    $categorieId),
                'preinscrits' => $query->getElevesPreinscritsOuEnAttente(
                    $responsable->responsableId, $categorieId),
                'affectations' => $this->db_manager->get(
                    'Sbm\Db\Query\AffectationsServicesStations'),
                'resultats' => $this->db_manager->get('Sbm\Facture\Calculs')->getResultats(
                    $responsable->responsableId, [], true), // pour forcer le calcul
                'paiements' => $this->db_manager->get('Sbm\Db\Vue\Paiements')->fetchAll(
                    [
                        'responsableId' => $responsable->responsableId,
                        'anneeScolaire' => $as['libelle']
                    ]),
                'factures' => $this->db_manager->get('Sbm\Db\Table\Factures')->fetchAll(
                    [
                        'responsableId' => $responsable->responsableId,
                        'millesime' => $millesime
                    ]),
                'client' => $this->client,
                'accueil' => $this->accueil,
                'adresse' => array_filter(
                    [
                        $responsable->adresseL1,
                        $responsable->adresseL2,
                        $responsable->adresseL3,
                        $responsable->codePostal . ' ' . $responsable->commune,
                        implode(' ; ',
                            array_filter(
                                [
                                    $format_telephone($responsable->telephoneF),
                                    $format_telephone($responsable->telephoneP),
                                    $format_telephone($responsable->telephoneT)
                                ]))
                    ]),
                'sadmin' => $this->authenticate->by()->getCategorieId() ==
                CategoriesInterface::SUPER_ADMINISTRATEUR_ID
            ]);
    }

    public function inscriptionEleveAction()
    {
        $this->debugInitLog(StdLib::findParentPath(__DIR__, 'data/logs'), 'sbm_error.log');
        try {
            $responsable = $this->responsable->get();
            $authUserId = $this->authenticate->by()->getUserId();
            $categorieId = $this->authenticate->by()->getCategorieId();
            $this->controleEtatDuSite();
        } catch (\Exception $e) {
            return $this->redirect()->toRoute('login', [
                'action' => 'logout'
            ]);
        }
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        }
        $args = $prg ?: [];
        if (array_key_exists('cancel', $args)) {
            return $this->redirect()->toRoute('sbmparent');
        }
        $outils = new OutilsInscription($this->local_manager, $responsable->responsableId,
            $authUserId);
        $selectStations = $this->db_manager->get('Sbm\Db\Select\Stations')->toutes();
        if ($categorieId == 1) {
            $selectCommunes = $this->db_manager->get('Sbm\Db\Select\Communes')->desservies();
        } else {
            $selectCommunes = $this->db_manager->get('Sbm\Db\Select\Communes')->visibles();
        }
        $form = $this->form_manager->get(Form\Enfant::class);
        $form->get('classeId')->setEmptyOption('Choisir d\'abord l\'établissement');
        $form->setAttribute('action',
            $this->url()
                ->fromRoute('sbmparent', [
                'action' => 'inscription-eleve'
            ]));
        $form->setValueOptions('etablissementId',
            $this->db_manager->get('Sbm\Db\Select\Etablissements')
                ->visiblesPourParent())
            ->setValueOptions('joursTransportR1', Semaine::getJours())
            ->setValueOptions('communeId', $selectCommunes)
            ->setValueOptions('stationIdR1', $selectStations)
            ->setValueOptions('stationIdR2', $selectStations)
            ->setData([
            'responsable1Id' => $responsable->responsableId
        ]);
        // Le formulaire de garde alterné est prévu complet pour une saisie
        $formga = $this->form_manager->get(Form\Service\Responsable2Complet::class);
        if (array_key_exists('submit', $args)) {
            if (array_key_exists('etablissementId', $args) && $args['etablissementId']) {
                $etablissement = $this->db_manager->get('Sbm\Db\Table\Etablissements')->getRecord(
                    $args['etablissementId']);
                $form->setValueOptions('classeId',
                    $this->db_manager->get('Sbm\Db\Select\Classes')
                        ->niveau($etablissement->niveau, 'in'));
            }
            $form->setData($args);
            $hasGa = StdLib::getParam('ga', $args, false);
            if ($hasGa) {
                $formga->setData($args);
            }
            // Dans form->isValid(), on refuse si existence d'un élève de même nom,
            // prénom, dateN et responsable 1 (ou 2).
            // formga->isValid() n'est regardé que si hasGa.
            if ($form->isValid() && ! ($hasGa && ! $formga->isValid())) {
                if ($categorieId > 1 && $categorieId < 100) {
                    try {
                        $outils->findOrganismeId(); // c'est un organisme
                    } catch (\Exception $e) {
                        $this->flashMessenger()->addErrorMessage(
                            'Ce compte d\'utilisateur devrait être associé à un organisme. Contactez le service.');
                        return $this->redirect()->toRoute('login',
                            [
                                'action' => 'logout'
                            ]);
                    }
                }
                // Enregistrement du responsable2 en premier (si on a le droit)
                if ($hasGa) {
                    $responsable2Id = $outils->saveResponsable($formga->getData());
                } else {
                    $responsable2Id = null;
                }
                // Enregistrement de l'élève
                $outils->saveEleve($form->getData(), $hasGa, $responsable2Id);
                // Ajout des dates de début et de fin de l'année scolaire
                $data = $form->getData();
                $as = Session::get('as');
                $data['dateDebut'] = $as['dateDebut'];
                $data['dateFin'] = $as['dateFin'];
                if (is_null($responsable2Id)) {
                    $data['demandeR2'] = 0;
                }
                // Enregistre la scolarité
                $outils->saveScolarite($data, 'inscription');
                $outils->apresInscription('inscription');
                $cr = $outils->getMessages();
                if (empty($cr)) {
                    $this->flashMessenger()->addSuccessMessage(
                        'L\'enfant est enregistré.');
                    $this->flashMessenger()->addWarningMessage(
                        'Son inscription ne sera prise en compte que lorsque le paiement aura été reçu.');
                } else {
                    $this->warningCompteRendu($cr);
                }
                if ($outils->doitGeolocaliser()) {
                    Session::set('eleve',
                        array_merge($outils->getOEleve()->getArrayCopy(),
                            $outils->getOScolarite()->getArrayCopy()),
                        'eleve-localisation');
                    return $this->redirect()->toRoute('sbmparent',
                        [
                            'action' => 'eleve-localisation'
                        ]);
                } else {
                    return $this->redirect()->toRoute('sbmparent');
                }
            } elseif (getenv('APPLICATION_ENV') == 'development') {
                echo '<h3>Debug:</h3>';
                var_dump($form->getMessages());
            } else {
                $this->debugLog(
                    [
                        __METHOD__,
                        'ligne' => __LINE__,
                        'formulaire invalide' => $form->getMessages()
                    ]);
            }
        }
        $formga->setValueOptions('r2communeId',
            $this->db_manager->get('Sbm\Db\Select\Communes')
                ->visibles());

        $ophoto = new \SbmCommun\Model\Photo\Photo();
        return new ViewModel(
            [
                'url_ts_region' => $this->url_ts_region,
                'form' => $form->prepare(),
                'formga' => $formga->prepare(),
                'categorieId' => $categorieId,
                'responsable' => $responsable,
                'ga' => StdLib::getParam('ga', $args, 0),
                'userId' => $authUserId,
                'dataphoto' => $ophoto->img_src($ophoto->getSansPhotoGifAsString(), 'gif')
            ]);
    }

    /**
     * Attention à la gestion du SbmParent\Form\Responsable2 ($formga) - les noms
     * d'éléments sont préfixés par r2.
     * Ils le sont aussi dans le POST donc dans args[]. -
     * par contre, la méthode setData() permet de placer directement des données, que
     * leurs index soient préfixés on non - et getData() supprime le préfixe r2 Cela
     * permet de charger le formulaire par setData() indifféremment depuis la table
     * responsables (sans préfixe) ou depuis le post (avec préfixe). Cela permet également
     * de retrouver les données sans préfixe pour les envoyer dans un objectData() de la
     * table responsables.
     *
     * @return \Zend\Http\Response|\Zend\Http\PhpEnvironment\Response|\Zend\View\Model\ViewModel
     */
    public function editEleveAction()
    {
        $this->debugInitLog(StdLib::findParentPath(__DIR__, 'data/logs'), 'sbm_error.log');
        try {
            $auth_responsable = $this->responsable->get();
            $authUserId = $this->authenticate->by()->getUserId();
            $categorieId = $this->authenticate->by()->getCategorieId();
            $this->controleEtatDuSite();
        } catch (\Exception $e) {
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
        $outils = new OutilsInscription($this->local_manager,
            $auth_responsable->responsableId, $authUserId, $eleveId);
        $selectStations = $this->db_manager->get('Sbm\Db\Select\Stations')->toutes();
        if ($categorieId == 1) {
            $selectCommunes = $this->db_manager->get('Sbm\Db\Select\Communes')->desservies();
        } else {
            $selectCommunes = $this->db_manager->get('Sbm\Db\Select\Communes')->visibles();
        }
        $form = $this->form_manager->get(Form\Enfant::class);
        $form->setAttribute('action',
            $this->url()
                ->fromRoute('sbmparent', [
                'action' => 'edit-eleve'
            ]));
        $form->setValueOptions('etablissementId',
            $this->db_manager->get('Sbm\Db\Select\Etablissements')
            ->visiblesPourParent())
            ->setValueOptions('classeId',
            $this->db_manager->get('Sbm\Db\Select\Classes')
                ->tout())
            ->setValueOptions('joursTransportR1', Semaine::getJours())
            ->setValueOptions('communeId', $selectCommunes)
            ->setValueOptions('stationIdR1', $selectStations)
            ->setValueOptions('stationIdR2', $selectStations);
        // pour la garde alternée, on doit déterminer si le formulaire sera complet ou non
        // afin d'adapter ses validateurs. S'il n'est pas complet, on passera tout de même
        // responsableId (attention ! dans le post, les champs sont préfixés par r2)
        $formgaComplet = true;
        $hasGa = false;
        if ($isPost) {
            $hasGa = StdLib::getParam('ga', $args, false);
            if ($hasGa) {
                $formgaComplet = $owner = $outils->isOwner($args['r2responsable2Id']);
            }
            // s'il n'y a pas de garde alternée, on prévoit le formulaire complet pour le
            // cas où l'utilisateur déciderait d'en rajouter une.
        }
        $formga = $this->form_manager->get(
            $formgaComplet ? Form\Service\Responsable2Complet::class : Form\Service\Responsable2Restreint::class);

        if ($isPost) {
            if (array_key_exists('etablissementId', $args) && $args['etablissementId']) {
                $etablissement = $this->db_manager->get('Sbm\Db\Table\Etablissements')->getRecord(
                    $args['etablissementId']);
                $form->setValueOptions('classeId',
                    $this->db_manager->get('Sbm\Db\Select\Classes')
                        ->niveau($etablissement->niveau, 'in'));
            }
            $form->setData($args);
            if ($hasGa) {
                $formga->setData($args);
            }
            /**
             * Dans form->isValid(), on refuse si existence d'un élève de même nom,
             * prénom, dateN et n° différent.
             * formga->isValid() n'est regardé que si
             * hasGa.
             */
            if ($form->isValid() && ! ($hasGa && ! $formga->isValid())) {
                if ($categorieId > 1 && $categorieId < 100) {
                    try {
                        $outils->findOrganismeId(); // c'est un organisme
                    } catch (\Exception $e) {
                        $this->flashMessenger()->addErrorMessage(
                            'Ce compte d\'utilisateur devrait être associé à un organisme. Contactez le service.');
                        return $this->redirect()->toRoute('login',
                            [
                                'action' => 'logout'
                            ]);
                    }
                }
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
                $ctrl = $outils->saveEleve($form->getData(), $hasGa, $responsable2Id);
                if ($ctrl != $eleveId) {
                    throw new \Exception('Arrêt du programme. Incohérence des données.');
                }
                // Enregistrement de sa scolarité
                $data = $form->getData();
                if (is_null($responsable2Id)) {
                    $data['demandeR2'] = 0;
                }
                $outils->saveScolarite($data, 'edit');
                $outils->apresInscription('edit');
                $cr = $outils->getMessages();
                Session::remove('responsable2', $this->getSessionNamespace());
                Session::remove('post', $this->getSessionNamespace());
                if (empty($cr)) {
                    $this->flashMessenger()->addSuccessMessage(
                        'La fiche a été mise à jour.');
                } else {
                    $this->warningCompteRendu($cr);
                }
                if ($outils->doitGeolocaliser()) {
                    Session::set('eleve',
                        array_merge($outils->getOEleve()->getArrayCopy(),
                            $outils->getOScolarite()->getArrayCopy()),
                        'eleve-localisation');
                    return $this->redirect()->toRoute('sbmparent',
                        [
                            'action' => 'eleve-localisation'
                        ]);
                } else {
                    return $this->redirect()->toRoute('sbmparent');
                }
            } elseif (getenv('APPLICATION_ENV') == 'development') {
                echo '<h3>Debug:</h3>';
                var_dump($form->getMessages());
            } else {
                $this->debugLog(__METHOD__);
                $this->debugLog($form->getMessages());
            }
            $responsable2 = Session::get('responsable2', null,
                $this->getSessionNamespace());
        } else {
            $data = $this->db_manager->get('Sbm\Db\Query\ElevesScolarites')
                ->getEleve($eleveId)
                ->getArrayCopy();
            // adapte le select classeId
            $etablissement = $this->db_manager->get('Sbm\Db\Table\Etablissements')->getRecord(
                $data['etablissementId']);
            $form->setValueOptions('classeId',
                $this->db_manager->get('Sbm\Db\Select\Classes')
                    ->niveau($etablissement->niveau, 'in'));
            // adresse personnelle de l'élève
            if ($categorieId > 1 || ! empty($data['communeEleveId'])) {
                $data['ap'] = 1;
                $data['adresseL1'] = $data['adresseEleveL1'];
                $data['adresseL2'] = $data['adresseEleveL2'];
                $data['codePostal'] = $data['codePostalEleve'];
                $data['communeId'] = $data['communeEleveId'];
            } else {
                $data['ap'] = 0;
            }
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
                        $formga->setValueOptions('r2communeId',
                            $this->db_manager->get('Sbm\Db\Select\Communes')
                                ->visibles());
                        $formga->setData(array_merge($data, $responsable2));
                    } else {
                        $formga->setData($data);
                    }
                    $responsable2['owner'] = $owner;
                } catch (\SbmCommun\Model\Db\Service\Table\Exception\ExceptionInterface $e) {
                    // on a perdu le responsable2 mais le formulaire va demander de le
                    // recréer ou de supprimer la ga
                    $responsable2 = null;
                }
            } else {
                $responsable2 = null;
            }
            Session::set('responsable2', $responsable2, $this->getSessionNamespace());
        }
        try {
            $formga->setValueOptions('r2communeId',
                $this->db_manager->get('Sbm\Db\Select\Communes')
                    ->visibles());
        } catch (\Zend\Form\Exception\InvalidElementException $e) {
        }
        $ophoto = new \SbmCommun\Model\Photo\Photo();
        try {
            $elevephoto = $this->db_manager->get('Sbm\Db\Table\ElevesPhotos')->getRecord(
                $eleveId);
            $dataphoto = $ophoto->img_src(stripslashes($elevephoto->photo), 'jpeg');
            $flashMessage = '';
        } catch (\Exception $e) {
            $dataphoto = $ophoto->img_src($ophoto->getSansPhotoGifAsString(), 'gif');
            $flashMessage = 'Pas de photo d\'identité.';
        }
        return new ViewModel(
            [
                'url_ts_region' => $this->url_ts_region,
                'form' => $form->prepare(),
                'formga' => $formga->prepare(),
                'categorieId' => $categorieId,
                'responsable' => $auth_responsable,
                'hasGa' => $hasGa,
                'responsable2' => $responsable2,
                'eleveId' => $eleveId,
                'formphoto' => $ophoto->getForm(),
                'dataphoto' => $dataphoto,
                'flashMessage' => $flashMessage
            ]);
    }

    /**
     * Change l'état d'attente d'un enfant.
     * L'état d'attente est enregistré dans le champ
     * selection de la table scolarites Pas de vue. Renvoie sur la liste une fois le
     * changement effectué
     *
     * @return \Zend\Http\PhpEnvironment\Response
     */
    public function attenteEleveAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        }
        $args = $prg ?: [];
        if (array_key_exists('id', $args) && array_key_exists('attente', $args)) {
            // effectuer le changement
            $tscolarite = $this->db_manager->get('Sbm\Db\Table\Scolarites');
            $scolarite = $tscolarite->getRecord(
                [
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
        ],
            [
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

        return new ViewModel(
            [

                'form' => $form->prepare(),
                'eleve' => $this->db_manager->get('Sbm\Db\Query\ElevesScolarites')->getEleve(
                    $args['id']),
                'affectations' => $this->db_manager->get(
                    'Sbm\Db\Query\AffectationsServicesStations')->getCorrespondances(
                    $args['id'])
            ]);
    }

    public function horairesAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            $args = Session::get('post', false, $this->getSessionNamespace());
            if ($args === false) {
                return $this->redirect()->toRoute('sbmparent');
            }
        } else {
            $args = $prg;
            if (! array_key_exists('eleveId', $args)) {
                return $this->redirect()->toRoute('sbmparent');
            }
            Session::set('post', $args, $this->getSessionNamespace());
        }

        return new ViewModel(
            [
                'enfant' => $args['enfant'],
                'circuits' => $this->db_manager->get(
                    'Sbm\Db\Query\AffectationsServicesStations')->getHoraires(
                    $args['eleveId']),
                'lignes' => $this->db_manager->get(
                    'Sbm\Db\Query\AffectationsServicesStations')->getLignes(
                    $args['eleveId'])
            ]);
    }

    /**
     * On vide le champ établissementId du formulaire au début de la réinscription pour
     * forcer à indiquer la scolarité.
     *
     * @return \Zend\Http\Response|\Zend\Http\PhpEnvironment\Response|\Zend\View\Model\ViewModel
     */
    public function reinscriptionEleveAction()
    {
        $this->debugInitLog(StdLib::findParentPath(__DIR__, 'data/logs'), 'sbm_error.log');
        $sansDateN = true;
        try {
            $auth_responsable = $this->responsable->get();
            $categorieId = $this->authenticate->by()->getCategorieId();
            $authUserId = $this->authenticate->by()->getUserId();
            $this->controleEtatDuSite();
        } catch (\Exception $e) {
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
            if ($categorieId == 1) {
                $aReinscrire = $this->db_manager->get(Query\Eleves::class)->aReinscrire(
                    $auth_responsable->responsableId);
            } else {
                $aReinscrire = $this->db_manager->get(Query\Eleves::class)->aReincrireOrganisme(
                    $auth_responsable->responsableId);
            }
            if ($aReinscrire->count() == 0) {
                Session::remove('responsable2', $this->getSessionNamespace());
                Session::remove('post', $this->getSessionNamespace());
                return $this->redirect()->toRoute('sbmparent',
                    [
                        'action' => 'inscription-eleve'
                    ]);
            }
            $responsable2 = [];
            $hasGa = false;
            $form = null;
            $formga = null;
            $dataphoto = null;
            $formphoto = null;
            $eleveId = null;
            $flashMessage = null;
        } else {
            $aReinscrire = [];
            $isPost = array_key_exists('submit', $args);
            $eleveId = $args['id'];
            $outils = new OutilsInscription($this->local_manager,
                $auth_responsable->responsableId, $authUserId, $eleveId);
            $selectStations = $this->db_manager->get('Sbm\Db\Select\Stations')->toutes();
            if ($categorieId == 1) {
                $selectCommunes = $this->db_manager->get('Sbm\Db\Select\Communes')->desservies();
            } else {
                $selectCommunes = $this->db_manager->get('Sbm\Db\Select\Communes')->visibles();
            }
            $form = $this->form_manager->get(Form\Enfant::class);
            $form->get('classeId')->setEmptyOption('Choisir d\'abord l\'établissement');
            $form->setAttribute('action',
                $this->url()
                    ->fromRoute('sbmparent', [
                    'action' => 'reinscription-eleve'
                ]))
                ->add(
                [
                    'type' => 'hidden',
                    'name' => 'phase',
                    'attributes' => [
                        'value' => $phase
                    ]
                ]);
            $form->setValueOptions('etablissementId',
                $this->db_manager->get('Sbm\Db\Select\Etablissements')
                ->visiblesPourParent())
                ->setValueOptions('joursTransportR1', Semaine::getJours())
                ->setValueOptions('communeId', $selectCommunes)
                ->setValueOptions('stationIdR1', $selectStations)
                ->setValueOptions('stationIdR2', $selectStations);
            // pour la garde alternée, on doit déterminer si le formulaire sera complet ou
            // non afin d'adapter ses validateurs. S'il n'est pas complet, on passera tout
            // de même responsableId (attention ! dans le post, les champs sont préfixés
            // par r2)
            $formgaComplet = true;
            if ($isPost) {
                $hasGa = StdLib::getParam('ga', $args, false);
                if ($hasGa) {
                    $formgaComplet = $owner = $outils->isOwner($args['r2responsable2Id']);
                }
                // s'il n'y a pas de garde alternée, on prévoit le formulaire complet pour
                // le cas où l'utilisateur déciderait d'en rajouter une.
            }
            $formga = $this->form_manager->get(
                $formgaComplet ? Form\Service\Responsable2Complet::class : Form\Service\Responsable2Restreint::class);
            if ($isPost) {
                if (array_key_exists('etablissementId', $args) && $args['etablissementId']) {
                    $etablissement = $this->db_manager->get('Sbm\Db\Table\Etablissements')->getRecord(
                        $args['etablissementId']);
                    $form->setValueOptions('classeId',
                        $this->db_manager->get('Sbm\Db\Select\Classes')
                            ->niveau($etablissement->niveau, 'in'));
                }
                $form->setData($args);
                if ($hasGa) {
                    $formga->setData($args);
                }
                /**
                 * Dans form->isValid(), on refuse si existence d'un élève de même nom,
                 * prénom, dateN et n° différent.
                 * formga->isValid() n'est regardé que si
                 * hasGa.
                 */
                if ($form->isValid() && ! ($hasGa && ! $formga->isValid())) {
                    if ($categorieId > 1 && $categorieId < 100) {
                        try {
                            $outils->findOrganismeId(); // c'est un organisme
                        } catch (\Exception $e) {
                            $this->flashMessenger()->addErrorMessage(
                                'Ce compte d\'utilisateur devrait être associé à un organisme. Contactez le service.');
                            return $this->redirect()->toRoute('login',
                                [
                                    'action' => 'logout'
                                ]);
                        }
                    }
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
                    $data = $form->getData();
                    $ctrl = $outils->saveEleve($data, $hasGa, $responsable2Id);
                    if ($ctrl != $eleveId) {
                        throw new \Exception(
                            'Arrêt du programme. Incohérence des données.');
                    }
                    // Ajout des dates de début et de fin de l'année scolaire
                    $as = Session::get('as');
                    $data['dateDebut'] = $as['dateDebut'];
                    $data['dateFin'] = $as['dateFin'];
                    $data['demandeR1'] = 1;
                    if (is_null($responsable2Id)) {
                        $data['demandeR2'] = 0;
                    } else {
                        $data['demandeR2'] = $data['demandeR2'] ? 1 : 0;
                    }
                    // Enregistrement de sa scolarité
                    $outils->saveScolarite($data, 'reinscription');
                    $outils->apresInscription('reinscription');
                    $cr = $outils->getMessages();
                    // compte-rendu et nettoyage de la session
                    Session::remove('responsable2', $this->getSessionNamespace());
                    Session::remove('post', $this->getSessionNamespace());
                    if (empty($cr)) {
                        $this->flashMessenger()->addSuccessMessage(
                            'L\'enfant est enregistré.');
                        $this->flashMessenger()->addWarningMessage(
                            'Son inscription ne sera prise en compte que lorsque le paiement aura été reçu.');
                    } else {
                        $this->warningCompteRendu($cr);
                    }
                    if ($outils->doitGeolocaliser()) {
                        Session::set('eleve',
                            array_merge($outils->getOEleve()->getArrayCopy(),
                                $outils->getOScolarite()->getArrayCopy()),
                            'eleve-localisation');
                        return $this->redirect()->toRoute('sbmparent',
                            [
                                'action' => 'eleve-localisation'
                            ]);
                    } else {
                        return $this->redirect()->toRoute('sbmparent');
                    }
                } elseif (getenv('APPLICATION_ENV') == 'development') {
                    echo '<h3>Debug:</h3>';
                    var_dump($form->getMessages());
                } else {
                    $this->debugLog(__METHOD__);
                    $this->debugLog($form->getMessages());
                }
                // $form->isValid() a échoué
                $responsable2 = Session::get('responsable2', null,
                    $this->getSessionNamespace());
            } else {
                // initialisation des formulaires
                $data = $this->db_manager->get(Query\Eleves::class)->getEleve($eleveId);
                if (! $data) {
                    // n'était pas inscrit l'année précédente
                    $data = $this->db_manager->get('Sbm\Db\Table\Eleves')
                        ->getRecord($eleveId)
                        ->getArrayCopy();
                }
                // adresse personnelle de l'élève
                if ($categorieId > 1 || ! empty($data['communeEleveId'])) {
                    $data['ap'] = 1;
                    $data['adresseL1'] = StdLib::getParam('adresseEleveL1', $data, '');
                    $data['adresseL2'] = StdLib::getParam('adresseEleveL2', $data, '');
                    $data['codePostal'] = StdLib::getParam('codePostalEleve', $data, '');
                    $data['communeId'] = StdLib::getParam('communeEleveId', $data, '');
                } else {
                    $data['ap'] = 0;
                }
                unset($data['classeId'], $data['etablissementId']);
                unset($data['commentaire']); // 2018 pour ne pas afficher le commentaire
                                             // d'importation lors d'une reprise de
                                             // données...
                unset($data['classeId']);
                $hasGa = ! is_null($data['responsable2Id']);
                $data['ga'] = $hasGa ? 1 : 0;
                if ($hasGa) {
                    if ($auth_responsable->responsableId ==
                        StdLib::getParam('responsable2Id', $data, 0)) {
                        // L'inscription est réalisée par l'ancien responsable2 qui passe
                        // responsable1
                        $r1 = [
                            'responsableId' => $data['responsable1Id'],
                            'x' => $data['x1'],
                            'y' => $data['y1']
                        ];
                        $data['responsable1Id'] = $data['responsable2Id'];
                        $data['x1'] = $data['x2'];
                        $data['y1'] = $data['y2'];
                        // et l'ancien responsable1 passe responsable 2
                        $data['responsable2Id'] = $r1['responsableId'];
                        $data['x2'] = $r1['x'];
                        $data['y2'] = $r1['y'];
                    }
                    try {
                        $responsable2 = $this->db_manager->get('Sbm\Db\Vue\Responsables')
                            ->getRecord($data['responsable2Id'])
                            ->getArrayCopy();
                        $owner = $outils->isOwner($data['responsable2Id']);
                        if ($owner) {
                            $formga->setValueOptions('r2communeId',
                                $this->db_manager->get('Sbm\Db\Select\Communes')
                                    ->visibles());
                            $formga->setData(array_merge($data, $responsable2));
                        } else {
                            $formga->setData($data);
                        }
                        $responsable2['owner'] = $owner;
                    } catch (\SbmCommun\Model\Db\Service\Table\Exception\ExceptionInterface $e) {
                        // on a perdu le responsable2 mais le formulaire va demander de le
                        // recréer ou de supprimer la ga
                        $responsable2 = null;
                    }
                } else {
                    $responsable2 = null;
                }
                // ----------------------------------------------
                // pour la reprise des élèves importés sans dateN
                if ($data['dateN'] == '1950-01-01') {
                    $sansDateN = true;
                    $data['dateN'] = null;
                } else {
                    $sansDateN = false;
                }
                // ----------------------------------------------
                $form->setData($data);
                Session::set('responsable2', $responsable2, $this->getSessionNamespace());
            }
            try {
                $formga->setValueOptions('r2communeId',
                    $this->db_manager->get('Sbm\Db\Select\Communes')
                        ->visibles());
            } catch (\Zend\Form\Exception\InvalidElementException $e) {
            }
            $ophoto = new \SbmCommun\Model\Photo\Photo();
            try {
                $elevephoto = $this->db_manager->get('Sbm\Db\Table\ElevesPhotos')->getRecord(
                    $eleveId);
                $dataphoto = $ophoto->img_src(stripslashes($elevephoto->photo), 'jpeg');
                $flashMessage = '';
            } catch (\Exception $e) {
                $dataphoto = $ophoto->img_src($ophoto->getSansPhotoGifAsString(), 'gif');
                $flashMessage = 'Pas de photo d\'identité.';
            }
            $formphoto = $ophoto->getForm();
        }
        $view = new ViewModel(
            [
                'url_ts_region' => $this->url_ts_region,
                'aReinscrire' => $aReinscrire,
                'categorieId' => $categorieId,
                'responsable' => $auth_responsable,
                'responsable2' => $responsable2,
                'hasGa' => $hasGa,
                'form' => $form ? $form->prepare() : $form,
                'formga' => $formga ? $formga->prepare() : $formga,
                'sansDateN' => $sansDateN,
                'eleveId' => $eleveId,
                'formphoto' => $formphoto,
                'dataphoto' => $dataphoto,
                'flashMessage' => $flashMessage
            ]);
        if ($phase == 1) {
            $view->setTemplate('sbm-parent/index/reinscription-eleve-phase1.phtml');
        } else {
            $view->setTemplate('sbm-parent/index/reinscription-eleve-phase2.phtml');
        }
        return $view;
    }

    public function envoiphotoAction()
    {
        $eleveId = $this->getRequest()->getPost('eleveId');
        if (! $eleveId) {
            $this->flashMessenger()->addErrorMessage('Pas d\'identifiant pour l\'élève.');
            $this->redirect()->toRoute('sbmparent');
        }
        $ophoto = new \SbmCommun\Model\Photo\Photo();
        $form = $ophoto->getForm()
            ->setAttribute('action', '/parent/savephoto')
            ->setData([
            'eleveId' => $eleveId
        ]);
        return new ViewModel([

            'formphoto' => $form->prepare()
        ]);
    }

    public function savephotoAction()
    {
        $ophoto = new \SbmCommun\Model\Photo\Photo();
        $ophoto->setFileLog($this->photo_log['path_filelog'], $this->photo_log['filename']);
        $form = $ophoto->getFormWithInputFilter($this->tmpuploads);
        $prg = $this->fileprg($form);
        if ($prg instanceof Response) {
            return $prg;
        } elseif (is_array($prg)) {
            if ($form->isValid()) {
                $data = $form->getData();
                $source = $data['filephoto']['tmp_name'];
                try {
                    $blob = $ophoto->getImageJpegAsString($source);
                    unlink($source);
                    // base de données
                    $tPhotos = $this->db_manager->get('Sbm\Db\Table\ElevesPhotos');
                    $odata = $tPhotos->getObjData();
                    $odata->exchangeArray(
                        [
                            'eleveId' => $data['eleveId'],
                            'photo' => addslashes($blob)
                        ]);
                    $tPhotos->saveRecord($odata);
                    $this->flashMessenger()->addSuccessMessage(
                        'La photo a été enregistrée.');
                } catch (\Exception $e) {
                    // problème de fichier, de format de fichier ou d'image dont le format
                    // n'est pas traité
                    $ophoto->getLogger()->log(Logger::ERR, $e->getMessage());
                    $ophoto->getLogger()->log(Logger::DEBUG, $e->getTraceAsString());
                    $msg = explode('.', $e->getMessage());
                    $this->flashMessenger()->addErrorMessage($msg[0]);
                }
            } else {
                $this->flashMessenger()->addErrorMessage(
                    implode(', ', $ophoto->getMessagesFilePhotoElement()));
            }
        }
        return $this->redirect()->toRoute('sbmparent');
    }

    public function eleveLocalisationAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        }
        $args = $prg ?: [];
        $eleve = Session::get('eleve', [], 'eleve-localisation');
        if (! array_key_exists('eleveId', $eleve) && ! array_key_exists('nom', $eleve) &&
            ! array_key_exists('prenom', $eleve)) {
            $this->flashMessenger()->addErrorMessage('Action interdite');
            return $this->redirect()->toRoute('sbmparent');
        }
        $eleve['lacommune'] = $this->db_manager->get('Sbm\Db\Table\Communes')->getRecord(
            $eleve['communeId'])->alias;
        $configCarte = StdLib::getParam('parent',
            $this->cartographie_manager->get('cartes'));
        $oDistanceMatrix = $this->cartographie_manager->get(
            GoogleMaps\DistanceMatrix::class);
        if (! $args) {
            // vérifier que la personne authentifiée est responsable de l'élève

            // initialisation du formulaire à partir de la fiche de l'élève
            $point = new Point($eleve['x'], $eleve['y']);
            $pt = $oDistanceMatrix->getProjection()->xyzVersgRGF93($point);
            $pt->setLatLngRange($configCarte['valide']['lat'],
                $configCarte['valide']['lng']);
            if (! $pt->isValid()) {
                // essayer de localiser par l'adresse avant de présenter la carte
                $array = $this->cartographie_manager->get(GoogleMaps\Geocoder::class)->geocode(
                    $eleve['adresseL1'], $eleve['codePostal'], $eleve['lacommune']);
                $pt = new Point($array['lng'], $array['lat'], 0, 'degré');
                $pt->setLatLngRange($configCarte['valide']['lat'],
                    $configCarte['valide']['lng']);
                if (! $pt->isValid() && ! empty($eleve['adresseL2'])) {
                    $array = $this->cartographie_manager->get(GoogleMaps\Geocoder::class)->geocode(
                        $eleve['adresseL2'], $eleve['codePostal'], eleve['lacommune']);
                    $pt->setLatitude($array['lat']);
                    $pt->setLongitude($array['lng']);
                    if (! $pt->isValid()) {
                        $pt->setLatitude($configCarte['centre']['lat']);
                        $pt->setLongitude($configCarte['centre']['lng']);
                    }
                }
            }
        } else {
            if (array_key_exists('cancel', $args)) {
                return $this->redirect()->toRoute('sbmparent');
            } elseif (array_key_exists('lng', $args) && array_key_exists('lat', $args)) {
                $pt = new Point($args['lng'], $args['lat'], 0, 'degré');
            } else {
                return $this->redirect()->toRoute('login', [
                    'action' => 'logout'
                ]);
            }
        }
        // ici, le pt est initialisé en lat, lng, degré
        $form = new LatLngForm([
            'eleveId' => [
                'id' => 'eleveId'
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
                ->fromRoute('sbmparent', [
                'action' => 'eleve-localisation'
            ]));
        $form->setData(
            [
                'eleveId' => $eleve['eleveId'],
                'lat' => $pt->getLatitude(),
                'lng' => $pt->getLongitude()
            ]);
        if (array_key_exists('submit', $args)) {
            if ($args['eleveId'] != $eleve['eleveId']) {
                // usurpation d'identité
                return $this->redirect()->toRoute('login', [
                    'action' => 'logout'
                ]);
            }
            // On vérifie qu'on a cliqué dans un rectangle autorisé
            $form->setData($args);
            if ($form->isValid()) {
                $point = $oDistanceMatrix->getProjection()->gRGF93versXYZ($pt);
                $tScolarites = $this->db_manager->get('Sbm\Db\Table\Scolarites');
                $oData = $tScolarites->getObjData();
                $oData->exchangeArray(
                    [
                        'millesime' => Session::get('millesime'),
                        'eleveId' => $eleve['eleveId'],
                        'x' => $point->getX(),
                        'y' => $point->getY()
                    ]);
                $tScolarites->saveRecord($oData);
                $majDistances = $this->cartographie_manager->get(
                    'Sbm\CalculDroitsTransport');
                $majDistances->majDistancesDistrict($eleve['eleveId'], false);
                $this->flashMessenger()->addSuccessMessage(
                    'La localisation du domicile est enregistrée.');
                return $this->redirect()->toRoute('sbmparent');
            }
        }

        return new ViewModel(
            [
                'scheme' => $this->getRequest()
                    ->getUri()
                    ->getScheme(),
                'eleve' => $eleve,
                'form' => $form->prepare(),
                'config' => $configCarte,
                'url_api' => $this->cartographie_manager->get('google_api_browser')['js']
            ]);
    }

    /**
     * Cette méthode ne peut pas récupérer le responsableId par la méthode
     * getResponsableIdFromSession() puisque son accès nest pas en POST.
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function impayesAnterieursAction()
    {
        $millesime = Session::get('millesime');
        $anterieur = $millesime - 1;
        $sessionNamespacePaiement = Session::get('nsArgsFacture');
        Session::set('millesime', $anterieur, $sessionNamespacePaiement);
        $responsableId = Session::get('post', 0, $sessionNamespacePaiement)['responsableId'];
        $ctrl = new \SbmParent\Model\VerifSoldeAnterieur($this->db_manager, $responsableId);
        return new ViewModel(
            [
                'resultats' => $ctrl->getResultats($anterieur),
                'millesime' => $millesime,
                'client' => $this->client,
                'accueil' => $this->accueil,
                'namespacectrl' => md5('nsArgsFacture')
            ]);
    }
}