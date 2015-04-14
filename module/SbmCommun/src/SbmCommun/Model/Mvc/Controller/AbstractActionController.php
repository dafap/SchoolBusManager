<?php
/**
 * Extension de la classe Zend\Mvc\Controller\AbstractActionController pour le projet School Bus Manager
 *
 * @todo: Il faudra supprimer les méthodes getFromSession() et setToSession() pour les remplacer par 
 *   DafapSession\Model\Session::get($param, $default, $ns) et
 *   DafapSession\Model\Session::set($param, $value, $ns)
 *
 * @project sbm
 * @package SbmCommun/Model/Mvc/Controller
 * @filesource AbstractActionController.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 20 mai 2014
 * @version 2014-1
 */
namespace SbmCommun\Model\Mvc\Controller;

use Zend\Mvc\Controller\AbstractActionController as ZendAbstractActionController;
use Zend\Http\PhpEnvironment\Response;
use Zend\View\Model\ViewModel;
use Zend\Session\Container as SessionContainer;
use Zend\Db\Sql\Ddl\Column\Boolean;
use DafapSession\Model\Session;
use SbmCommun\Model\StdLib;
use SbmCommun\Form\CriteresForm;
use SbmCommun\Model\Db\ObjectData\Criteres as ObjectDataCriteres;

/**
 * Quelques méthodes utiles
 *
 * @author admin
 *        
 */
abstract class AbstractActionController extends ZendAbstractActionController
{

    const SBM_DG_SESSION = 'sbm_dg_session';

    /**
     * Booléen qui prend sa valeur lors de l'utilisation de postRedirectGet dans les méthode initListe, initAjout, initEdit, initSuppr
     *
     * @var Boolean
     */
    protected $sbm_isPost;

    /**
     * Get Base Url
     *
     * Get Base App Url
     */
    protected function getBaseUrl()
    {
        $uri = $this->getRequest()->getUri();
        return sprintf('%s://%s', $uri->getScheme(), $uri->getHost());
    }

    /**
     * initListe est une méthode de contrôle d'entrée dans les xxxListeAction()
     * - si c'est un post, renvoie une redirection 303
     * - si c'est un get ou un retour d'action, renvoie array(paginator, form, retour) à partir des paramètres en session
     * - si c'est une redirection 303, renvoie array(paginator, form, retour) à partir du post initial
     *
     * @param string $formName
     *            string : Le nom du formulaire est le nom de la table (ou de la vue) sur laquelle il porte.
     *            array : Tableau de définition des éléments à créer dans le formulaire 
     * @param closure $initForm
     *            Fonction anonyme lancée juste après la création du formulaire avec comme paramètres le service manager et le formulaire. 
     *            Elle sert à initialiser les champs du formulaire, en particulier les listes déroulantes.
     * @param array $strictWhere
     *            Liste des champs du formulaire pour lesquels l'égalité est recherché. Pour les autres, on fait un Like
     *            
     * @return <b>\SbmCommun\Model\Mvc\Controller\Response | array</b>
     *         Il faut tester si c'est un Response. Sinon, le tableau est de la forme array('paginator' => ..., 'form' => ..., 'retour' => boolean)
     */
    protected function initListe($formName, $initForm = null, $strictWhere = array())
    {
        $retour = false;
        $prg = $this->prg();
        if ($prg instanceof Response) {
            // renvoie redirection 303 avec le contenu de post en session 'prg_post1' (Expire_Hops = 1)
            return $prg;
        } elseif ($prg === false) {
            // ce n'était pas un post. Prendre les paramètres éventuellement dans la session (cas du paginator)
            $this->sbm_isPost = false;
            $args = Session::get('post', array(), $this->getSessionNamespace());
        } else {
            // c'est le tableau qui correspond au post après redirection 303; on le met en session
            $args = $prg;
            $retour = StdLib::getParam('op', $args, '') == 'retour';
            if ($retour) {
                // dans ce cas, il s'agit du retour d'une action de type suppr, ajout ou edit. Comme pour un get, on récupère ce qui est en session.
                $this->sbm_isPost = false;
                $args = Session::get('post', array(), $this->getSessionNamespace());
            } else {
                $this->sbm_isPost = true;
                Session::set('post', $args, $this->getSessionNamespace());
            }
        }
        // formulaire des critères de recherche
        $criteres_form = new CriteresForm($formName);
        if (! is_null($initForm)) {
            $initForm($this->getServiceLocator(), $criteres_form);
        }
        $criteres_obj = new ObjectDataCriteres($criteres_form->getElementNames());
        
        if ($this->sbm_isPost) {
            $criteres_form->setData($args);
            if ($criteres_form->isValid()) {
                $criteres_obj->exchangeArray($criteres_form->getData());
            }
        }
        // récupère les données de la session si le post n'a pas été validé dans le formulaire (pas de post ou invalide)
        if (! $criteres_form->hasValidated() && ! empty($args)) {
            $criteres_obj->exchangeArray($args);
            $criteres_form->setData($criteres_obj->getArrayCopy());
        }
        return array(
            'where' => $criteres_obj->getWhere($strictWhere),
            'form' => $criteres_form,
            'retour' => $retour
        );
    }

    /**
     * Partie commune de traitement de l'ajout d'un enregistrement.
     * Le formulaire, le nom de la table, son type et son alias sont passés dans le paramètre $params
     * Le paramètre $renvoyer permet de retourner des données de POST
     *
     * @param array $params
     *            Tableau associatif dont les clés principales sont 'form' et 'data'.
     *            La clé 'form' contient l'objet formulaire ;
     *            la clé 'data' est un tableau associatif dont les clés sont 'table', 'type' et 'alias'
     * @param callable|null $renvoyer
     *            Fonction perpettant d'extraire des données de POST ($args après PostRedirectGet)
     *            
     * @return \Zend\Http\PhpEnvironment\Response|string|int renvoie une redirection 303 si c'est un post, ou une chaine de compte-rendu parmi {'error', 'warning', 'success'} ou un id,
     *         ou le résultat de la fonction $renvoyer (souvent une fonction anonyme)
     */
    protected function addData($params, $renvoyer = null)
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            $this->flashMessenger()->addErrorMessage("Action interdite.");
            return 'error';
        } else {
            $args = $prg;
            $isPost = StdLib::getParam('submit', $args, false);
            $cancel = StdLib::getParam('cancel', $args, false);
            unset($args['submit']);
            unset($args['cancel']);
        }
        if ($cancel) {
            $this->flashMessenger()->addWarningMessage("Aucun enregistrement n'a été ajouté.");
            return 'warning';
        }
        $table = $this->getServiceLocator()->get($params['data']['alias']);
        $db = $this->getServiceLocator()->get('Sbm\Db\DbLib');
        $form = $params['form'];
        $form->setMaxLength($db->getMaxLengthArray($params['data']['table'], $params['data']['type']));
        $form->bind($table->getObjData());
        if ($isPost) {
            $form->setData($args);
            if ($form->isValid()) {
                $table->saveRecord($form->getData());
                $this->flashMessenger()->addSuccessMessage("Un nouvel enregistrement a été ajouté.");
                return 'success';
            }
        } else {
            $form->setData($db->getColumnDefaults($params['data']['table'], $params['data']['type']));
        }
        if (is_callable($renvoyer)) {
            return $renvoyer($args);
        } else {
            return 'formulaire';
        }
    }

    /**
     * Partie commune à la modifiction d'un enregistrement dans une table
     *
     * @param array $params
     *            tableau associatif dont les clés sont 'form' et 'data'.
     *            La clé 'data' est elle-même associée à un tableau associatif dont les clés sont 'table', 'type', 'alias' et 'id'
     * @param string $renvoyer
     *            Fonction de construction de la réponse. Son paramètre sont $args (paramètres fournis en post ou en session)
     *            
     * @return \Zend\Http\PhpEnvironment\Response|string|int renvoie une redirection 303 si c'est un post, ou une chaine de compte-rendu parmi {'error', 'warning', 'success'} ou un id,
     *         ou le résultat de la fonction $renvoyer (souvent une fonction anonyme)
     */
    protected function editData($params, $renvoyer = null)
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            // on aura le droit de rentrer en get que si un args a été sauvegardé en session avec un id de la donnée à modifier
            $args = $this->getFromSession('post', array(), 'sbm_edit_' . $params['data']['table']);
            $isPost = false;
            $cancel = false;
        } else {
            $args = $prg;
            $isPost = StdLib::getParam('submit', $args, false);
            $cancel = StdLib::getParam('cancel', $args, false);
            unset($args['submit']);
            unset($args['cancel']);
            $this->setToSession('post', $args, 'sbm_edit_' . $params['data']['table']);
        }
        $id = StdLib::getParam($params['data']['id'], $args, - 1);
        if ($id == - 1) {
            $this->flashMessenger()->addErrorMessage("Action interdite.");
            return new EditResponse('error', $args);
        } elseif ($cancel) {
            $this->flashMessenger()->addWarningMessage("L'enregistrement n'a pas été modifié.");
            return new EditResponse('warning', $args);
        }
        $table = $this->getServiceLocator()->get($params['data']['alias']);
        $db = $this->getServiceLocator()->get('Sbm\Db\DbLib');
        
        $form = $params['form'];
        $form->setMaxLength($db->getMaxLengthArray($params['data']['table'], $params['data']['type']));
        $form->bind($table->getObjData());
        
        if ($isPost) {
            $form->setData($args);
            if ($form->isValid()) {
                $table->saveRecord($form->getData());
                $this->flashMessenger()->addSuccessMessage("Les modifications ont été enregistrées.");
                return new EditResponse('success', $args);
            }
        } else {
            $form->setData($table->getRecord($id)
                ->getArrayCopy());
        }
        if (is_callable($renvoyer)) {
            return new EditResponse('default', $args, $renvoyer($args));
        } else {
            return new EditResponse('default', $args, $id);
        }
    }

    /**
     * Partie commune à la suppression d'un enregistrement dans une table
     *
     * @param array $params
     *            tableau associatif dont les clés sont 'form' et 'data'.
     *            La clé 'data' est elle-même associée à un tableau associatif dont les clés sont 'alias' et 'id'
     * @param string $renvoyer
     *            Fonction de construction de la réponse. Ses paramètres sont $id (valeur de l'id) et $table (table dont l'alias est donné)
     *            
     * @return \Zend\Http\PhpEnvironment\Response|string|int renvoie une redirection 303 si c'est un post, ou une chaine de compte-rendu parmi {'error', 'warning', 'success'} ou un id,
     *         ou le résultat de la fonction $renvoyer (souvent une fonction anonyme)
     */
    protected function supprData($params, $renvoyer = null)
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            $this->flashMessenger()->addErrorMessage("Action interdite.");
            return new EditResponse('error', array());
        } else {
            $args = $prg;
            $confirme = StdLib::getParam('supproui', $args, false);
            $cancel = StdLib::getParam('supprnon', $args, false);
            if ($id = StdLib::getParam($params['data']['id'], $args, false)) {
                $this->setToSession($params['data']['id'], $id, 'sbm_suppr');
            } else {
                // ici, je controle si l'id en session est bien celui reçu par post (via prg). On ne sait jamais !!!
                $id = $this->getFromSession($params['data']['id'], - 1, 'sbm_suppr');
                $ctrl = StdLib::getParam('id', $args, - 1);
                if ($id != $ctrl)
                    $id = null;
            }
        }
        $table = $this->getServiceLocator()->get($params['data']['alias']);
        if (is_null($id) || ! $table->getObjData()->isValidId($id)) {
            $this->flashMessenger()->addErrorMessage("Action interdite.");
            return new EditResponse('error', $args);
        } elseif ($cancel) {
            $this->flashMessenger()->addWarningMessage("L'enregistrement n'a pas été supprimé.");
            return new EditResponse('warning', $args);
        } else {
            // pour les primary key composées de plusieurs champs, id est une chaine où les champs sont séparés par des |
            // id est transformé ici en tableau associatif
            // pour les primary key composées d'un seul champ, id est conservé en l'état
            $id = $table->getObjData()->getValidId($id);
        }
        
        if ($confirme) {
            $table->deleteRecord($id);
            $this->flashMessenger()->addSuccessMessage("La suppression a été faite.");
            return new EditResponse('success', $args);
        } else {
            $form = $params['form'];
            if (is_array($id)) {
                $form->setData(array(
                    'id' => implode('|', $id)
                ));
            } else {
                $form->setData(array(
                    'id' => $id
                ));
            }
        }
        if (is_callable($renvoyer)) {
            return new EditResponse('default', $args, $renvoyer($id, $table));
        } else {
            return new EditResponse('default', $args, $id);
        }
    }

    /**
     * Renvoie le nombre de lignes de résultats pour le paginateur
     *
     * @param string $paginateurId            
     * @param int $default            
     * @return int
     */
    protected function getNbPagination($paginateurId, $default)
    {
        $config = $this->getServiceLocator()->get('Config');
        return (int) StdLib::getParamR(array(
            'liste',
            'paginator',
            $paginateurId
        ), $config, $default);
    }

    /**
     * Renvoie une chaine de la forme 'module_controller_action_item'
     *
     * @param string|null $action
     *            Si $action est null alors on prend l'action indiquée dans la route courante
     * @param string|null $item
     *            Ce que l'on veut rajouter
     *            
     * @return string
     */
    protected function getSessionNamespace($action = null, $item = null)
    {
        $args = array(
            $this->getModuleControllerName(),
            $action ?  : $this->getCurrentActionFromRoute()
        );
        if (! is_null($item)) {
            $args[] = $item;
        }
        return str_replace('-', '_', implode('_', $args));
    }

    /**
     * Renvoie une chaine de la forme 'module_controller'
     * exemple : sbmfront_index
     *
     * @return string
     */
    public function getModuleControllerName()
    {
        $parts = explode('\\', strtolower(get_class($this))); // de la forme {'sbmfront', 'controller', 'indexcontroller'}
        unset($parts[1]); // supprime 'controller'
        return substr_replace(implode('_', $parts), '', - 10); // supprime 'controller' à la fin
    }

    /**
     * Renvoie le nom de l'action ou index par défaut
     *
     * @return string
     */
    protected function getCurrentActionFromRoute()
    {
        return $this->params('action', 'index');
    }

    /**
     * Renvoie le paramètre en session ou la valeur par défaut s'il n'est pas défini
     *
     * @param $param Nom
     *            du paramètre demandé
     * @param $default Valeur
     *            à renvoyer si le paramètre n'est pas défini
     * @param string|null $sessionNamespace
     *            namespace de la session (par défaut valeur fixée par le constante de cette classe SBM_DG_SESSION)
     *            
     * @return int|boolean
     */
    protected function getFromSession($param, $default = null, $sessionNamespace = self::SBM_DG_SESSION)
    {
        return Session::get($param, $default, $sessionNamespace);
    }

    /**
     * Place la valeur en session dans le paramètre indiqué
     *
     * @param string $param
     *            nom du paramètre
     * @param mixed $value
     *            valeur à mettre en session
     * @param string|null $sessionNamespace
     *            namespace de la session (par défaut valeur fixée par le constante de cette classe SBM_DG_SESSION)
     */
    protected function setToSession($param, $value, $sessionNamespace = self::SBM_DG_SESSION)
    {
        Session::set($param, $value, $sessionNamespace);
    }
}