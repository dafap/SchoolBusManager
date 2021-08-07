<?php
/**
 * Controller pour les actions d'authentification
 *
 * @project sbm
 * @package SbmFront\Controller
 * @filesource LoginController.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 7 août 2021
 * @version 2021-2.6.3
 */
namespace SbmFront\Controller;

use SbmAuthentification\Model\CategoriesInterface;
use SbmBase\Model\Session;
use SbmBase\Model\StdLib;
use SbmCartographie\Model\Point;
use SbmCommun\Model\Mvc\Controller\AbstractActionController;
use SbmFront\Form;
use SbmFront\Model\Responsable\Exception as CreateResponsableException;
use SbmMail\Model\Template as MailTemplate;
use Zend\Http\PhpEnvironment\Response;
use Zend\View\Model\ViewModel;

/**
 *
 * @property \SbmInstallation\Model\Theme $theme
 * @property \SbmCommun\Model\Db\Service\DbManager $db_manager
 * @property \SbmCommun\Model\Service\FormManager $form_manager
 * @property \SbmAuthentification\Authentication\AuthenticationServiceFactory $authenticate
 * @property \SbmFront\Model\Responsable\Service\ResponsableManager $responsable
 * @property \SbmCartographie\GoogleMaps\DistanceMatrix $oDistanceMatrix
 * @property array $config_cartes
 * @property array $mail_config
 * @property array $img
 * @property array $client
 * @property string $accueil
 *
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 *
 */
class LoginController extends AbstractActionController
{

    /**
     * Aiguillage - pas de view associée
     *
     * @return \Zend\Http\PhpEnvironment\Response|\Zend\Http\Response
     */
    public function loginAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg == false) {
            return $this->redirect()->toRoute('home');
        }
        $args = $prg;
        if (\array_key_exists('signin', $args)) {
            // login demandé
            $form = $this->form_manager->get(Form\Login::class);
            $form->setData($args);
            if ($form->isValid()) {
                // isValid() vérifie l'existence de l'email
                $data = $form->getData();
                $auth = $this->authenticate->by('email');
                $auth->getAdapter()
                    ->setIdentity($data)
                    ->setCredential($data);
                $result = $auth->authenticate();
                if ($result->getCode() > 0) {
                    return $this->homePageAction();
                } else {
                    foreach ($result->getMessages() as $msg)
                        $this->flashMessenger()->addErrorMessage($msg);
                    return $this->redirect()->toRoute('home');
                }
            } else {
                $this->flashMessenger()->addErrorMessage('Email inconnu.');
                return $this->redirect()->toRoute('home');
            }
        } elseif (\array_key_exists('signup', $args)) {
            // création de compte demandée - il n'y a pas de paramètres
            return $this->redirect()->toRoute('login', [
                'action' => 'creer-compte'
            ]);
        } else {
            return $this->redirect()->toRoute('home');
        }
    }

    /**
     * Entrée pour confirmer l'email lors de la création d'un compte ou de la demande de
     * changement d'email.
     * On ne peut rien faire tant que le mot de passe n'est pas donné.
     */
    public function confirmAction()
    {
        $tableUsers = $this->db_manager->get('Sbm\Db\Table\Users');
        $form = $this->form_manager->get(Form\MdpFirst::class);
        $auth = $this->authenticate->by('token');

        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            // entrée par get avec le token en id
            $auth->getAdapter()->setIdentity($this->params('id'));
            if (! $auth->authenticate()->getCode() > 0) {
                return $this->redirect()->toRoute('home');
            }
            $form->setData([
                'userId' => $auth->getUserId()
            ]);
        } else {
            $args = $prg;
            // ici, c'est le traitement du post (mot de passe)
            if (! array_key_exists('submit', $args)) {
                $this->flashMessenger()->addWarningMessage(
                    'Entrée abandonnée. Recommencez.');
                return $this->redirect()->toRoute('login', [
                    'action' => 'logout'
                ]);
            }
            $form->setData($args);
            if ($form->isValid() && $args['userId'] == $auth->getUserId()) {
                $authUsr = $auth->getIdentity();
                try {
                    $odata = $tableUsers->getObjData()
                        ->exchangeArray($authUsr)
                        ->setMdp($args['userId'], $args['mdp'], $authUsr['gds'])
                        ->confirme()
                        ->completeForLogin();
                    $tableUsers->saveRecord($odata);
                    $this->flashMessenger()->addSuccessMessage(
                        'Votre compte est confirmé. Votre mot de passe est enregistré.');
                } catch (\SbmCommun\Model\Db\ObjectData\Exception\ExceptionInterface $e) {
                    $this->flashMessenger()->addErrorMessage(
                        'Ce lien ne peut être utilisé qu\'une seule fois. Identifiez-vous ou cliquez sur mot de passe oublié.');
                }
                return $this->homePageAction();
            }
        }
        if (! $auth->hasIdentity()) {
            $this->flashMessenger()->addErrorMessage(
                'Ce lien est invalide ou a déjà été utilisé. Identifiez-vous ou cliquez sur mot de passe oublié.');
            return $this->redirect()->toRoute('home');
        }
        return new ViewModel([
            'form' => $form->prepare()
        ]);
    }

    /**
     * Entrée pour annuler une création d'un compte par token
     */
    public function annulerAction()
    {
        $tableUsers = $this->db_manager->get('Sbm\Db\Table\Users');
        if ($tableUsers->deleteRecordByToken($this->params('id'))) {
            $this->flashMessenger()->addInfoMessage('Le compte a été supprimé. Merci.');
        } else {
            $this->flashMessenger()->addInfoMessage(
                'Désolé ! Le compte est actif ou a déjà été supprimé.');
        }
        return $this->redirect()->toRoute('home');
    }

    /**
     * En fonction de la catégorie de l'utilisateur, - redirige vers une route par défaut
     * (suivre redirect()) - fixe une route pour le menu du layout (bienvenue) (suivre
     * $container->home)
     *
     * @return \Zend\Http\Response
     */
    public function homePageAction()
    {
        $auth = $this->authenticate->by('email');
        if ($auth->hasIdentity()) {
            switch ($auth->getCategorieId()) {
                case CategoriesInterface::PARENT_ID:
                    try {
                        try {
                            $responsable = $this->responsable->get();
                        } catch (\Zend\ServiceManager\Exception\ServiceNotCreatedException $e) {
                            throw $e->getPrevious();
                        }
                        // contrôle de la commune
                        if (! $responsable->inscriptionenligne) {
                            // page d'information indiquant que les inscriptions en ligne
                            // ne sont pas autorisées pour les parents de cette commune.
                            $commune = $responsable->commune;
                            $message = <<<EOT
                            Vous ne pouvez pas inscrire vos enfants car ce service en ligne n'est pas ouvert aux habitants
                            de votre commune.
                            EOT;
                            $this->flashMessenger()->addErrorMessage($message);
                            $this->logout();
                            return $this->redirect()->toRoute('home',
                                [
                                    'action' => 'hors-zone',
                                    'id' => $commune
                                ]);
                        }
                        if (! $responsable->paiementenligne) {
                            // indiquer que les préinscriptions sont autorisées mais que
                            // le paiement en ligne n'est pas permi pour les parents de
                            // cette commune
                            $message = <<<EOT
                            Vous pouvez préinscrire vos enfants mais le paiement en ligne n'est pas ouvert aux habitants
                            de votre commune.'
                            EOT;
                            $this->flashMessenger()->addInfoMessage($message);
                        }
                        // contrôle de position géographique
                        $point = new Point($responsable->x, $responsable->y);
                        $pt = $this->oDistanceMatrix->getProjection()->xyzVersgRGF93(
                            $point);
                        $configCarte = StdLib::getParam('parent', $this->config_cartes);
                        $pt->setLatLngRange($configCarte['valide']['lat'],
                            $configCarte['valide']['lng']);
                        // if (! $pt->isValid()) {
                        if (! $this->isValid($pt, $configCarte['centre'])) {
                            return $this->redirect()->toRoute('sbmparentconfig',
                                [
                                    'action' => 'localisation'
                                ]);
                        }
                    } catch (CreateResponsableException $e) {
                        $this->flashMessenger()->addErrorMessage(
                            'Il faut compléter la fiche du responsable');
                        $retour = $this->url()->fromRoute('login',
                            [
                                'action' => 'home-page'
                            ]);
                        return $this->redirectToOrigin()
                            ->setBack($retour)
                            ->toRoute('sbmparentconfig', [
                            'action' => 'create'
                        ]);
                    }
                    Session::set('home', 'sbmparentconfig', 'layout');
                    return $this->redirect()->toRoute('sbmparent');
                    break;
                case CategoriesInterface::ORGANISME_ID:
                    Session::set('home', 'sbmparentconfig', 'layout');
                    return $this->redirect()->toRoute('sbmparent');
                    break;
                case CategoriesInterface::COMMUNE_ID:
                case CategoriesInterface::GR_COMMUNES_ID:
                case CategoriesInterface::ETABLISSEMENT_ID:
                case CategoriesInterface::GR_ETABLISSEMENTS_ID:
                case CategoriesInterface::TRANSPORTEUR_ID:
                case CategoriesInterface::GR_TRANSPORTEURS_ID:
                    return $this->redirect()->toRoute('sbmportail');
                    break;
                case CategoriesInterface::SECRETARIAT_ID:
                    Session::remove('commune', 'enTantQue');
                    Session::remove('etablissement', 'enTantQue');
                    Session::remove('transporteur', 'enTantQue');
                    return $this->redirect()->toRoute('sbmportail');
                    break;
                case CategoriesInterface::GESTION_ID:
                    Session::remove('commune', 'enTantQue');
                    Session::remove('etablissement', 'enTantQue');
                    Session::remove('transporteur', 'enTantQue');
                    Session::set('home', 'sbmgestion/config', 'layout');
                    return $this->redirect()->toRoute('sbmgestion');
                case CategoriesInterface::ADMINISTRATEUR_ID:
                    Session::remove('commune', 'enTantQue');
                    Session::remove('etablissement', 'enTantQue');
                    Session::remove('transporteur', 'enTantQue');
                    Session::set('home', 'sbmadmin', 'layout');
                    return $this->redirect()->toRoute('sbmadmin');
                case CategoriesInterface::SUPER_ADMINISTRATEUR_ID:
                    Session::remove('commune', 'enTantQue');
                    Session::remove('etablissement', 'enTantQue');
                    Session::remove('transporteur', 'enTantQue');
                    Session::set('home', 'sbminstall', 'layout');
                    return $this->redirect()->toRoute('sbminstall');
                default:
                    $this->flashMessenger()->addErrorMessage(
                        'La catégorie de cet utilisateur est inconnue.');
                    return $this->redirect()->toRoute('login', [
                        'action' => 'logout'
                    ]);
                    break;
            }
        } else {
            $this->flashMessenger()->addWarningMessage('Identifiez-vous.');
            return $this->redirect()->toRoute('home');
        }
    }

    /**
     * Pour ARLYSERE, les responsables sont acceptés de partout.
     * Les coordonnées ne sont
     * pas nulles et ne sont pas celles par défaut sur la carte.
     *
     * @param \SbmCartographie\Model\Point $pt
     * @param array $centreCarte
     * @return boolean
     */
    private function isValid(Point $pt, array $centreCarte)
    {
        $centre = [
            $centreCarte['lat'],
            $centreCarte['lng']
        ];
        $zero = [
            0,
            0
        ];
        $coordonnees = [
            $pt->getLatitude(),
            $pt->getLongitude()
        ];
        return ($coordonnees != $centre) && ($coordonnees != $zero);
    }

    private function logout()
    {
        try {
            $this->responsable->get()->clear();
        } catch (\Exception $e) {
        }
        $auth = $this->authenticate->by();
        $auth->clearIdentity();
        Session::remove('millesime');
        Session::remove('commune', 'enTantQue');
    }

    /**
     * Déconnexion
     *
     * @return \Zend\Http\Response
     */
    public function logoutAction()
    {
        $this->logout();
        return $this->redirect()->toRoute('home');
    }

    /**
     * On demande l'email et on envoie un lien pour entrer.
     * A l'entrée on doit donner un
     * nouveau mot de passe avant de continuer.
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function mdpDemandeAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        }
        $args = $prg ?: [];
        if (\array_key_exists('cancel', $args)) {
            $this->flashMessenger()->addErrorMessage('Demande abandonnée.');
            return $this->redirect()->toRoute('home');
        }
        $form = $this->form_manager->get(Form\MdpDemande::class);
        $form->setAttribute('action',
            $this->url()
                ->fromRoute('login', [
                'action' => 'mdp-demande'
            ]));
        if (\array_key_exists('submit', $args) && \array_key_exists('email', $args)) {
            $tUsers = $this->db_manager->get('Sbm\Db\Table\Users');
            $form->bind($tUsers->getObjData());
            $form->setData($args);
            if ($form->isValid()) {
                $data = $form->getData();
                $odata = $tUsers->getRecordByEmail($data->email)->setToken();
                if ($odata->active) {
                    $tUsers->saveRecord($odata);
                    $this->flashMessenger()->addSuccessMessage('Demande enregistrée.');
                    // envoie l'email
                    $logo_bas_de_mail = 'bas-de-mail-transport-scolaire.png';
                    $mailTemplate = new MailTemplate('oubli-mdp', 'layout',
                        [
                            'file_name' => $logo_bas_de_mail,
                            'path' => StdLib::getParamR([
                                'img',
                                'path'
                            ], $this->config),
                            'img_attributes' => StdLib::getParamR(
                                [
                                    'img',
                                    'administrer',
                                    $logo_bas_de_mail
                                ], $this->config),
                            'client' => StdLib::getParam('client', $this->config)
                        ]);
                    $params = [
                        'to' => [
                            [
                                'email' => $data->email,
                                'name' => $odata->nom . ' ' . $odata->prenom
                            ]
                        ],
                        'subject' => 'Lien pour entrer dans le service d\'inscription',
                        'body' => [
                            'html' => $mailTemplate->render(
                                [
                                    'titre' => $odata->titre,
                                    'nom' => $odata->nom,
                                    'prenom' => $odata->prenom,
                                    'url_confirme' => $this->url()
                                        ->fromRoute('login',
                                        [
                                            'action' => 'confirm',
                                            'id' => $odata->token
                                        ], [
                                            'force_canonical' => true
                                        ]),
                                    'client' => StdLib::getParam('client', $this->config)
                                ])
                        ]
                    ];
                    $this->getEventManager()->addIdentifiers('SbmMail\Send');
                    $this->getEventManager()->trigger('sendMail', null, $params);
                    $this->flashMessenger()->addInfoMessage(
                        'Une réponse a été envoyée à l\'adresse indiquée. Consultez votre messagerie.');
                } else {
                    $this->flashMessenger()->addWarningMessage(
                        'Votre compte a été bloqué. Prenez contact avec le service organisateur.');
                }
                // retour
                return $this->redirect()->toRoute('home');
            }
        }
        return new ViewModel([
            'form' => $form->prepare()
        ]);
    }

    /**
     * Envoie un lien pour entrer sans mot de passe.
     * A l'entrée on doit donner un nouveau
     * mot de passe avant de continuer. Cette action est utile pour le service, pour
     * dépaner par téléphone.
     */
    public function mdpResetAction()
    {
        ;
    }

    /**
     * On donne l'ancien mot de passe, le nouveau puis on confirme le nouveau.
     */
    public function mdpChangeAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        }
        $args = $prg ?: [];
        $form = $this->form_manager->get(Form\MdpChange::class);
        $form->setAttribute('action',
            $this->url()
                ->fromRoute('login', [
                'action' => 'mdp-change'
            ]));
        if (\array_key_exists('submit', $args) && \array_key_exists('mdp_old', $args) &&
            \array_key_exists('mdp_new', $args)) {
            $auth = $this->authenticate->by('email');
            $identity = $auth->getIdentity();
            $auth->getAdapter()
                ->setIdentity($identity['email'])
                ->setCredential($args['mdp_old']);
            if ($auth->authenticate()->getCode() > 0) {
                if ($args['mdp_old'] == $args['mdp_new']) {
                    Session::set('post', []);
                    $this->flashMessenger()->addInfoMessage(
                        'Le mot de passe est inchangé.');
                    return $this->homePageAction();
                }
                // ici, on change le mot de passe
                $tUsers = $this->db_manager->get('Sbm\Db\Table\Users');
                $form->setData($args);
                if ($form->isValid()) {
                    $mdp = $form->getData()['mdp_new'];
                    $oData = $tUsers->getObjData()
                        ->exchangeArray(
                        [
                            'userId' => null,
                            'token' => null,
                            'tokenalive' => 0,
                            'mdp' => null,
                            'dateModification' => null,
                            'note' => null
                        ])
                        ->setMdp($identity['userId'], $mdp, $identity['gds'])
                        ->addNote('Mdp changé le ' . date('d/m/y'))
                        ->completeToModif();
                    $tUsers->saveRecord($oData);
                    $this->flashMessenger()->addSuccessMessage(
                        'Le mot de passe a été changé.');
                    // return $this->redirectToOrigin()->back();
                    return $this->homePageAction();
                }
            } else {
                $this->flashMessenger()->addErrorMessage(
                    'Le mot de passe donné est faux.');
                return $this->homePageAction();
            }
        } elseif (array_key_exists('cancel', $args)) {
            $this->flashMessenger()->addWarningMessage('Demande abandonnée.');
            return $this->homePageAction();
        }
        return new ViewModel([
            'form' => $form->prepare()
        ]);
    }

    /**
     * Permet à l'utilisateur de changer son email.
     * Un lien est adressé sur cet email. Une
     * confirmation est nécessaire pour que le changement prenne effet.
     */
    public function emailChangeAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        }
        $args = $prg ?: [];
        $auth = $this->authenticate->by('email');
        $identity = $auth->getIdentity();
        $email_old = $identity['email'];
        $form = $this->form_manager->get(Form\EmailChange::class);
        $form->setAttribute('action',
            $this->url()
                ->fromRoute('login', [
                'action' => 'email-change'
            ]));
        if (array_key_exists('submit', $args) && array_key_exists('mdp', $args) &&
            array_key_exists('email_new', $args) && array_key_exists('email_ctrl', $args)) {
            $auth->getAdapter()
                ->setIdentity($email_old)
                ->setCredential($args['mdp']);
            if ($auth->authenticate()->getCode() > 0) {
                if ($email_old == $args['email_new']) {
                    Session::set('post', []);
                    $this->flashMessenger()->addInfoMessage('L\'email est inchangé.');
                    return $this->homePageAction();
                }
                // ici, on change l'email
                $form->setData($args);
                if ($form->isValid()) {
                    // données validées
                    $email_new = $form->getData()['email_new'];
                    $tUsers = $this->db_manager->get('Sbm\Db\Table\Users');
                    $oData = $tUsers->getObjData()
                        ->exchangeArray(
                        [
                            'userId' => $identity['userId'],
                            'token' => null,
                            'tokenalive' => 0,
                            'email' => $email_new,
                            'dateModification' => null,
                            'note' => null
                        ])
                        ->addNote('Email changé le ' . date('d/m/y'))
                        ->completeToModif();
                    $tUsers->saveRecord($oData);
                    // modifie l'email dans la table des responsables si nécessaire
                    $tResponsables = $this->db_manager->get('Sbm\Db\Table\Responsables');
                    $tResponsables->changeEmail($email_old, $email_new);
                    $auth->refreshIdentity();
                    // retour
                    $this->flashMessenger()->addSuccessMessage('Modification enregistrée');
                    return $this->homePageAction();
                }
            } else {
                $this->flashMessenger()->addErrorMessage(
                    'Le mot de passe donné est faux.');
                return $this->homePageAction();
            }
        } elseif (array_key_exists('cancel', $args)) {
            $this->flashMessenger()->addWarningMessage('Demande abandonnée.');
            return $this->homePageAction();
        }
        return new ViewModel([
            'form' => $form->prepare(),
            'email' => $email_old
        ]);
    }

    /**
     * Création d'un compte pour un utilisateur anonyme
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function creerCompteAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        }
        $args = $prg ?: [];
        if (\array_key_exists('cancel', $args)) {
            $this->flashMessenger()->addErrorMessage('Création abandonnée.');
            return $this->redirect()->toRoute('home');
        }
        $form = $this->form_manager->get(Form\CreerCompte::class);
        if (\array_key_exists('submit', $args)) {
            $table_users = $this->db_manager->get('Sbm\Db\Table\Users');
            $form->bind($table_users->getObjData());
            $form->setData($args);
            if ($form->isValid()) {
                // prépare data (c'est un \SbmCommun\Model\Db\ObjectData\User qui possède
                // des
                // méthodes qui vont bien)
                $odata = $form->getData()->completeToCreate();
                $table_users->saveRecord($odata);
                $this->flashMessenger()->addSuccessMessage('Création en cours...');
                // envoie l'email
                $logo_bas_de_mail = 'bas-de-mail-transport-scolaire.png';
                $mailTemplate = new MailTemplate('nouveau-compte', 'layout',
                    [
                        'file_name' => $logo_bas_de_mail,
                        'path' => StdLib::getParamR([
                            'img',
                            'path'
                        ], $this->config),
                        'img_attributes' => StdLib::getParamR(
                            [
                                'img',
                                'administrer',
                                $logo_bas_de_mail
                            ], $this->config),
                        'client' => StdLib::getParam('client', $this->config)
                    ]);

                $params = [
                    'to' => [
                        [
                            'email' => $odata->email,
                            'name' => $odata->nom . ' ' . $odata->prenom
                        ]
                    ],
                    'subject' => 'Lien pour entrer dans le service d\'inscription',
                    'body' => [
                        'html' => $mailTemplate->render(
                            [
                                'titre' => $odata->titre,
                                'nom' => $odata->nom,
                                'prenom' => $odata->prenom,
                                'url_confirme' => $this->url()
                                    ->fromRoute('login',
                                    [
                                        'action' => 'confirm',
                                        'id' => $odata->token
                                    ], [
                                        'force_canonical' => true
                                    ]),
                                'url_annule' => $this->url()
                                    ->fromRoute('login',
                                    [
                                        'action' => 'annuler',
                                        'id' => $odata->token
                                    ], [
                                        'force_canonical' => true
                                    ]),
                                'client' => StdLib::getParam('client', $this->config)
                            ])
                    ]
                ];
                $this->getEventManager()->addIdentifiers('SbmMail\Send');
                $this->getEventManager()->trigger('sendMail', null, $params);
                $this->flashMessenger()->addInfoMessage(
                    'Un mail a été envoyé à l\'adresse indiquée. Consultez votre messagerie.');
                // retour
                return $this->redirect()->toRoute('home');
            }
        }
        $form->setAttribute('action',
            $this->url()
                ->fromRoute('login', [
                'action' => 'creer-compte'
            ]));
        return new ViewModel([
            'form' => $form->prepare()
        ]);
    }

    /**
     * Modification d'un compte (civilité, nom, prénom) Selon la catégorie, on ne verra
     * qu'une partie des informations.
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function modifCompteAction()
    {
        $auth = $this->authenticate->by();
        if ($auth->hasIdentity()) {
            $prg = $this->prg();
            if ($prg instanceof Response) {
                return $prg;
            }
            $args = $prg ?: [];
            if (\array_key_exists('cancel', $args)) {
                $this->flashMessenger()->addWarningMessage('Modification abandonnée.');
                return $this->homePageAction();
            }
            $identity = $auth->getIdentity();
            $table_users = $this->db_manager->get('Sbm\Db\Table\Users');
            $form = $this->form_manager->get(Form\ModifCompte::class);
            $form->bind($table_users->getObjData());
            if (\array_key_exists('submit', $args)) {
                $form->setData($args);
                if ($form->isValid()) {
                    // prépare data (c'est un \SbmCommun\Model\Db\ObjectData\User qui
                    // possède des
                    // méthodes qui vont bien)
                    $oUser = $form->getData()->completeToModif();
                    $table_users->saveRecord($oUser);
                    $auth->refreshIdentity();
                    $this->flashMessenger()->addSuccessMessage('Modification enregistrée');
                    // retour
                    return $this->homePageAction();
                }
            } else {
                $form->setData($identity);
            }
            $form->setAttribute('action',
                $this->url()
                    ->fromRoute('login', [
                    'action' => 'modif-compte'
                ]));
            return new ViewModel(
                [
                    'form' => $form->prepare(),
                    'email' => $identity['email']
                ]);
        } else {
            $this->flashMessenger()->addWarningMessage('Vous n\'êtes pas identifié.');
            return $this->redirect()->toRoute('home');
        }
    }

    /**
     * Synchronise l'identité du user autentifié sur le responsable en session
     */
    public function synchroCompteAction()
    {
        $auth = $this->authenticate->by();
        if ($auth->hasIdentity()) {
            try {
                $responsable = $this->responsable->get();
            } catch (CreateResponsableException $e) {
                $this->flashMessenger()->addErrorMessage('Action interdite');
                return $this->redirect()->toRoute('login', [
                    'action' => 'logout'
                ]);
            }
            $identity = $auth->getIdentity();
            $responsableArray = $responsable->getArrayCopy();
            $responsableArray['userId'] = $identity['userId'];
            $table_users = $this->db_manager->get('Sbm\Db\Table\Users');
            $oUser = $table_users->getObjData()
                ->exchangeArray($responsableArray)
                ->completeToModif();
            $table_users->saveRecord($oUser);
            $auth->refreshIdentity();
            $this->flashMessenger()->addSuccessMessage("La fiche a été enregistrée.");
            return $this->homePageAction();
        } else {
            $this->flashMessenger()->addWarningMessage('Vous n\'êtes pas identifié.');
            return $this->redirect()->toRoute('home');
        }
    }

    public function contactAction()
    {
        $auth = $this->authenticate->by('email');
        if ($auth->hasIdentity()) {
            $this->redirectToOrigin()->setBack(
                $this->getRequest()
                    ->getHeader('Referer')
                    ->uri()
                    ->getPath());
            return $this->redirect()->toRoute('SbmMail');
        }
        return new ViewModel(
            [
                'theme' => $this->theme,
                'accueil' => $this->accueil,
                'client' => $this->client
            ]);
    }
}
