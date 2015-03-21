<?php
/**
 * Controller pour les actions d'authentification
 *
 * Description longue du fichier s'il y en a une
 * 
 * @project sbm
 * @package SbmFront\Controller
 * @filesource LoginController.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 10 févr. 2015
 * @version 2015-1
 */
namespace SbmFront\Controller;

use SbmCommun\Model\Mvc\Controller\AbstractActionController;
use Zend\Http\PhpEnvironment\Response;
use SbmCommun\Model\Db\Service\Table\Exception;
use Zend\View\Model\ViewModel;
use SbmFront\Form\InputFilter\CreerCompte as CreerCompteInputFilter;
use SbmCommun\Model\DateLib;
use Zend\Math\Rand;
use SbmFront\Form\CreerCompte;
use SbmFront\Form\ModifCompte;
use SbmFront\Form\MdpFirst;
use SbmFront\Form\Login;
use Zend\Session\Container;
use SbmFront\Form\MdpDemande;

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
            $form = new Login($this->getServiceLocator());
            $form->setData($args);
            if ($form->isValid()) {
                // isValid() vérifie l'existence de l'email
                $auth = $this->getServiceLocator()->get('Sbm\Authenticate');
                if ($auth->authenticate($form->getData())) {
                    return $this->homePageAction();
                } else {
                    $this->flashMessenger()->addErrorMessage('Mot de passe incorrect ou compte bloqué.');
                    return $this->redirect()->toRoute('home');
                }
            } else {
                $this->flashMessenger()->addErrorMessage('Email inconnu.');
                return $this->redirect()->toRoute('home');
            }
        } elseif (\array_key_exists('signup', $args)) {
            // création de compte demandée - il n'y a pas de paramètres
            $this->redirect()->toRoute('login', array(
                'action' => 'creer-compte'
            ));
        } else {
            return $this->redirect()->toRoute('home');
        }
    }

    /**
     * Entrée pour confirmer l'email lors de la création d'un compte ou de la demande de changement d'email.
     * On ne peut rien faire tant que le mot de passe n'est pas donné.
     */
    public function confirmAction()
    {
        $tableUsers = $this->getServiceLocator()->get('Sbm\Db\Table\Users');
        $form = new MdpFirst();
        $auth = $this->getServiceLocator()->get('Sbm\Authenticate');
        
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg == false) {
            // entrée par get avec le token en id
            if (! $auth->authenticateByToken($this->params('id'))) {
                return $this->redirect()->toRoute('home');
            }
            $form->setData(array(
                'userId' => $auth->getUserId()
            ));
        } else {
            $args = $prg;
            // ici, c'est le traitement du post (mot de passe)
            if (! array_key_exists('submit', $args)) {
                $this->flashMessenger()->addWarningMessage('Entrée abandonnée. Recommencez.');
                return $this->redirect()->toRoute('login', array(
                    'action' => 'logout'
                ));
            }
            $form->setData($args);
            if ($form->isValid() && $args['userId'] == $auth->getUserId()) {
                $authUsr = $auth->getIdentity();
                $odata = $tableUsers->getObjData();
                $odata->exchangeArray($authUsr);
                $odata->setMdp($args['userId'], $args['mdp'], $authUsr['gds']);
                $odata->confirme();
                $odata->completeForLogin();
                $tableUsers->saveRecord($odata);
                $this->flashMessenger()->addSuccessMessage('Votre compte est confirmé. Votre mot de passe est enregistré.');
                return $this->homePageAction();
            }
        }
        return new ViewModel(array(
            'form' => $form
        ));
    }

    public function homePageAction()
    {
        $auth = $this->getServiceLocator()->get('Sbm\Authenticate');
        $container = new Container('layout');
        if ($auth->hasIdentity()) {
            switch ($auth->getCategorieId()) {
                case 1:
                    $container->home = 'sbmuser';
                    return $this->redirect()->toRoute('sbmuser');
                    break;
                case 2:
                    $container->home = 'transporteur';
                    return $this->redirect()->toUrl('transporteur');
                    break;
                case 3:
                    $container->home = 'etablissements';
                    return $this->redirect()->toUrl('etablissements');
                case 253:
                    $container->home = 'sbmgestion/config';
                    return $this->redirect()->toRoute('sbmgestion');
                case 254:
                    $container->home = 'sbmadmin';
                    return $this->redirect()->toRoute('sbmadmin');
                case 255:
                    $container->home = 'sbminstall';
                    return $this->redirect()->toRoute('sbminstall');
                default:
                    $this->flashMessenger()->addErrorMessage('La catégorie de cet utilisateur est inconnue.');
                    $this->redirect()->toRoute('login', array(
                        'action' => 'logout'
                    ));
                    break;
            }
            ;
        } else {
            $this->flashMessenger()->addWarningMessage('Identifiez-vous.');
            $this->redirect()->toRoute('home');
        }
    }

    public function logoutAction()
    {
        $auth = $this->getServiceLocator()->get('Sbm\Authenticate');
        $auth->clearIdentity();
        return $this->redirect()->toRoute('home');
    }

    /**
     * On demande l'email et on envoie un lien pour entrer.
     * A l'entrée on doit donner un nouveau mot de passe avant de continuer.
     *
     * @return \SbmFront\Controller\ViewModel
     */
    public function mdpDemandeAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        }
        $args = (array) $prg;
        if (\array_key_exists('cancel', $args)) {
            $this->flashMessenger()->addErrorMessage('Demande abandonnée.');
            return $this->redirect()->toRoute('home');
        }
        $form = new MdpDemande($this->getServiceLocator());
        $form->setAttribute('action', $this->url()
            ->fromRoute('login', array(
            'action' => 'mdp-demande'
        )));
        if (\array_key_exists('submit', $args) && \array_key_exists('email', $args)) {
            $tUsers = $this->getServiceLocator()->get('Sbm\Db\Table\Users');
            $form->bind($tUsers->getObjData());
            $form->setData($args);
            if ($form->isValid()) {
                $data = $form->getData();
                $odata = $tUsers->getRecordByEmail($data->email);
                $odata->setToken();
                $tUsers->saveRecord($odata);
                $this->flashMessenger()->addSuccessMessage('Demande enregistrée.');
                // envoie l'email
                // @todo à faire
                $this->flashMessenger()->addInfoMessage('Un mail a été envoyé à l\'adresse indiquée. Consultez votre messagerie.');
                // retour
                return $this->redirect()->toRoute('home');
            }
        }
        return new ViewModel(array(
            'form' => $form
        ));
    }

    /**
     * Envoie un lien pour entrer sans mot de passe.
     * A l'entrée on doit donner un nouveau mot de passe avant de continuer.
     * Cette action est utile pour le service, pour dépaner par téléphone.
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
        ;
    }

    /**
     * Permet à l'utilisateur de changer son email.
     * Un lien est adressé sur cet email. Une confirmation est nécessaire pour que le changement prenne effet.
     */
    public function emailChangeAction()
    {
        ;
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
        $args = (array) $prg;
        if (\array_key_exists('cancel', $args)) {
            $this->flashMessenger()->addErrorMessage('Création abandonnée.');
            return $this->redirect()->toRoute('home');
        }
        $form = new CreerCompte($this->getServiceLocator());
        if (\array_key_exists('submit', $args)) {
            $table_users = $this->getServiceLocator()->get('Sbm\Db\Table\Users');
            $form->bind($table_users->getObjData());
            $form->setData($args);
            if ($form->isValid()) {
                // prépare data (c'est un SbmCommun\Model\Db\ObjectData\User qui possède des méthodes qui vont bien)
                $data = $form->getData();
                $data->completeToCreate();
                // enregistre
                $table_users->saveRecord($data);
                $this->flashMessenger()->addSuccessMessage('Création en cours...');
                // envoie l'email
                // @todo à faire
                $this->flashMessenger()->addInfoMessage('Un mail a été envoyé à l\'adresse indiquée. Consultez votre messagerie.');
                // retour
                return $this->redirect()->toRoute('home');
            }
        }
        $form->setAttribute('action', $this->url()
            ->fromRoute('login', array(
            'action' => 'creer-compte'
        )));
        return new ViewModel(array(
            'form' => $form
        ));
    }

    /**
     * Modification d'un compte (civilité, nom, prénom)
     * Selon la catégorie, on ne verra qu'une partie des informations.
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function modifCompteAction()
    {
        $auth = $this->getServiceLocator()->get('Sbm\Authenticate');
        if ($auth->hasIdentity()) {
            $prg = $this->prg();
            if ($prg instanceof Response) {
                return $prg;
            }
            $args = (array) $prg;
            if (\array_key_exists('cancel', $args)) {
                $this->flashMessenger()->addWarningMessage('Modification abandonnée.');
                return $this->redirectToOrigin()->back();
            }
            $identity = $auth->getIdentity();
            $table_users = $this->getServiceLocator()->get('Sbm\Db\Table\Users');
            $form = new ModifCompte($identity['categorieId']);
            $form->bind($table_users->getObjData());
            if (\array_key_exists('submit', $args)) {
                $form->setData($args);
                if ($form->isValid()) {
                    // prépare data (c'est un SbmCommun\Model\Db\ObjectData\User qui possède des méthodes qui vont bien)
                    $data = $form->getData();
                    $data->completeToModif();
                    // enregistre
                    $table_users->saveRecord($data);
                    $auth->refreshIdentity();
                    $this->flashMessenger()->addSuccessMessage('Modification enregistrée');
                    // retour
                    return $this->redirectToOrigin()->back();
                }
            } else {
                $form->setData($identity);
            }
            $form->setAttribute('action', $this->url()
                ->fromRoute('login', array(
                'action' => 'modif-compte'
            )));
            return new ViewModel(array(
                'form' => $form
            ));
        } else {
            $this->flashMessenger()->addWarningMessage('Vous n\'êtes pas identifié.');
            return $this->redirect()->toRoute('home');
        }
    }
}
 