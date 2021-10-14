<?php
/**
 * Extension de la classe Zend\Mvc\Controller\AbstractActionController pour le projet
 * School Bus Manager
 *
 * @project sbm
 * @package SbmCommun/Model/Mvc/Controller
 * @filesource AbstractActionController.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 15 août 2021
 * @version 2021-2.6.3
 */
namespace SbmCommun\Model\Mvc\Controller;

use SbmBase\Model\Session;
use SbmBase\Model\StdLib;
use SbmCommun\Form\ButtonForm;
use SbmCommun\Form\CriteresForm;
use SbmCommun\Model\Exception;
use SbmCommun\Model\Db\ObjectData\Criteres as ObjectDataCriteres;
use SbmGestion\Model\Db\Filtre\Eleve\Filtre;
use Zend\Http\PhpEnvironment\Response;
use Zend\Mvc\Controller\AbstractActionController as ZendAbstractActionController;
use Zend\Mvc\Controller\Plugin\FlashMessenger;
use Zend\View\Model\ViewModel;

/**
 *
 * @method \SbmCommun\Model\Mvc\Controller\Plugin\Service\RedirectBack redirectToOrigin()
 * @method \Zend\Http\PhpEnvironment\Response csvExport(string $filename = null, array
 *         $header = null, array $records = null, callable $callback = null, string
 *         $delimiter = ';', string $enclosure = '"')
 */
abstract class AbstractActionController extends ZendAbstractActionController
{

    /**
     * Booléen qui prend sa valeur lors de l'utilisation de postRedirectGet dans les
     * méthode initListe, initAjout, initEdit, initSuppr
     *
     * @var boolean
     */
    protected $sbm_isPost;

    /**
     *
     * @var array
     */
    protected $config;

    public function __construct($config = [])
    {
        $this->config = $config;
    }

    /**
     * Renvoie la valeur associée à la clé $param de la propriété $config
     *
     * @param string $param
     *
     * @throws \SbmCommun\Model\Exception\OutOfBoundsException
     *
     * @return mixed
     */
    public function __get($param)
    {
        if (array_key_exists($param, $this->config)) {
            return $this->config[$param];
        }
        $message = sprintf(
            'Le paramètre %s n\'est pas une propriété définie par le ControllerFactory.',
            $param);
        throw new Exception\OutOfBoundsException($message);
    }

    /**
     * Retrieve serviceManager instance (provisoire pour la version 2.5 du framework -
     * incompatible avec la version 3)
     *
     * @return \Zend\ServiceManager\ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }

    /**
     * Get Base Url Get Base App Url
     */
    protected function getBaseUrl()
    {
        $uri = $this->getRequest()->getUri();
        return sprintf('%s://%s', $uri->getScheme(), $uri->getHost());
    }

    /**
     * Retourne à la home page de l'utilisateur, en plaçant éventuellement un message en
     * flashMessenger, dans le namespace souhaité (Success par défaut).
     *
     * @param string $message
     * @param string $namespace
     *            Utile si $message n'est pas vide pour donner le namespace à utiliser.
     *            Les namespaces sont des constantes de la classe FlashMessenger.
     * @return \Zend\Http\Response
     */
    protected function homePage(string $message = '',
        string $namespace = FlashMessenger::NAMESPACE_SUCCESS)
    {
        if ($message) {
            $this->flashMessenger()->addMessage($message, $namespace);
        }
        return $this->redirect()->toRoute('login', [
            'action' => 'home-page'
        ]);
    }

    /**
     * Cette procédure marque des fiches élèves 'selection = 1' à partir d'actions
     * présentant un groupe d'élèves.
     * L'appel doit nécessairement se faire depuis un
     * `entityGroupSelectionAction()` car les paramètres sont récupérés en session depuis
     * le namespace `entityGroupAction()`. La procédure présente d'abord une page de
     * confirmation puis marque les fiches si la demande est confirmée.
     *
     * @param string $query
     *            nom de la requête (méthode de \SbmGestion\Model\Db\Service\Eleve\Liste)
     * @param string $filtre
     *            nom de la requête (méthode de \SbmGestion\Model\Db\Filtre\Eleve\Filtre)
     * @param string|array $idField
     *            nom(s) de(s) id de sélection pour le filtre. C'est un scalaire ou un
     *            tableau.
     * @param array $retour
     *            de la forme ['route' => ..., 'action' => ...]
     * @param array $keys_hiddens
     *            liste des noms des hiddens à passer dans le formulaire (reçoivent leur
     *            valeur par post)
     * @return \Zend\Http\PhpEnvironment\Response|\Zend\Http\Response|\Zend\View\Model\ViewModel
     */
    protected function markSelectionEleves($query, $filtre, $idField, $retour,
        $keys_hiddens = [])
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            $args = Session::get('post', [], $this->getSessionNamespace());
            if (empty($args)) {
                return $this->redirect()->toRoute($retour['route'],
                    [
                        'action' => $retour['action'],
                        'page' => $this->params('page', 1),
                        'id' => $this->params('pr', 1)
                    ]);
            }
        } else {
            $args = $prg;
            Session::set('post', $args, $this->getSessionNamespace());
        }
        $hiddens = [];
        foreach ($keys_hiddens as $key) {
            $hiddens[$key] = StdLib::getParam($key, $args);
        }
        $form = new ButtonForm($hiddens,
            [
                'confirmer' => [
                    'class' => 'confirm',
                    'value' => 'Confirmer',
                    'title' => 'Sélectionner les fiches élèves'
                ],
                'cancel' => [
                    'class' => 'confirm',
                    'value' => 'Abandonner'
                ]
            ], 'Confirmation', true);
        if (array_key_exists('cancel', $args)) {
            return $this->redirect()->toRoute($retour['route'],
                [
                    'action' => $retour['action'],
                    'page' => $this->params('page', 1),
                    'id' => $this->params('pr', 1)
                ]);
        }
        if (array_key_exists('confirmer', $args)) {
            // on marque les fiches demandées
            $form->setData($args);
            // uniquement pour vérifier le Csrf
            if ($form->isValid()) {
                // on reprend les critères en session de `entityGroupAction`
                $ns = substr($this->getSessionNamespace(), 0, - strlen('-selection'));
                $post = Session::get('post', [], $ns);
                if (is_array($idField)) {
                    $id = [];
                    foreach ($idField as $value) {
                        $id[$value] = StdLib::getParam($value, $post,
                            StdLib::getParam($value, $args, null));
                        if (is_null($id[$value])) {
                            throw new \Exception("$value n'a pas été trouvé.");
                        }
                    }
                } else {
                    $id = StdLib::getParam($idField, $post, null);
                    if (is_null($id)) {
                        throw new \Exception("$idField n'a pas été trouvé.");
                    }
                }

                // liste des eleveId à sélectionner
                $rowset = $this->db_manager->get('Sbm\Db\Eleve\Liste')->{$query}(
                    Session::get('millesime'), Filtre::{$filtre}($id));
                $ids = [];
                foreach ($rowset as $row) {
                    $ids[] = $row['eleveId'];
                }
                // enregistrement de la sélection
                $tEleves = $this->db_manager->get('Sbm\Db\Table\Eleves');
                $tEleves->clearSelection();
                $affectedRows = $tEleves->markSelection($ids);
                $this->flashMessenger()->addInfoMessage(
                    "$affectedRows fiches élèves sélectionnnées");
                return $this->redirect()->toRoute($retour['route'],
                    [
                        'action' => $retour['action'],
                        'page' => $this->params('page', 1),
                        'id' => $this->params('pr', 1)
                    ]);
            } elseif (getenv('APPLICATION_ENV') == 'development') {
                echo '<h3>Debug:</h3>';
                var_dump($form->getMessages());
            }
        }
        // on affiche le formulaire de confirmation
        return new ViewModel([
            'form' => $form
        ]);
    }

    /**
     * On reçoit au choix :<ul>
     * <li>en paramètre le $documentId</li>
     * <li>par post un paramètre 'documentId'</li> </ul>
     * Dans les deux cas, ce paramètre peut être numérique (le documentId de la table
     * documents), une chaine de caractères ou un tableau.
     * Si le caractère est numérique, c'est le documentId de la table système documents.
     * Dans les autre cas, cela dépend de la présence ou non du paramètre GET 'id'.<ul>
     * <li>s'il est absent, 'documentId' contient le name du document</li>
     * <li>s'il est présent, 'documentId' contient le libelle du menu et 'id' contient
     * 'docaffectationId' de la table système 'docaffectations'. On retrouvera alors le
     * 'documentId' dans la méthode Tcpdf::getDocumentId().</li> </ul>
     * On lit les critères définis dans le formulaire de critères de la liste (en session
     * avec le sessionNameSpace de xxxListeAction). On transmet le where pour les
     * documents basés sur une table ou vue sql et les tableaux 'expression', 'criteres'
     * et 'strict' pour ceux basés sur une requête SQL. Voir pour cela les objets
     * ObjectData qui doivent définir les méthodes getWhere() et getCriteres().
     *
     * ATTENTION AU RETOUR EN CAS DE PB : La pageRetour est indiquée par le paramètre pr
     * (GET) or dans la page d'appel elle est indiquée par le paramètre id.
     *
     * @param string|array $criteresObjectId
     *            nom complet de la classe de l'ObjectData\Criteres si c'est un tableau :
     *            <ul> <li>la première valeur est le nom de la classe,</li> <li>la
     *            deuxième est le paramètre de la méthode getWherePdf</li> <li>la
     *            troisième est une fonction appelée pour modifier éventuellement le
     *            where</li></ul>
     * @param string|array $criteresFormName
     *            nom complet de la classe du formulaire de recherche si c'est un tableau,
     *            la première valeur est le nom de la classe, les autres sont les
     *            paramètres du constructeur
     * @param int|string|null $documentId
     *            identifiant du document à créer
     * @param array $redirectBackParams
     *            tableau ('route' => ..., 'action' => ...) pour le retour en cas d'échec
     * @param array $pdf_params
     *            tableau associatif de paramètres à passer
     * @return \Zend\Http\PhpEnvironment\Response|\Zend\Http\Response
     */
    protected function getPluginPdfParams($criteresObjectId, $criteresFormId,
        $documentId = null, $redirectBackParams = null, $pdf_params = [])
    {
        if (is_null($documentId)) {
            $prg = $this->prg();
            if ($prg instanceof Response) {
                return $prg;
            } else {
                $args = $prg ?: [];
                if (! array_key_exists('documentId', $args)) {
                    $this->flashMessenger()->addErrorMessage(
                        'Le document à imprimer n\'a pas été indiqué.');
                    $routeParams = [
                        'action' => $redirectBackParams['action'],
                        'page' => $this->params('page', 1)
                    ];
                    $id = $this->params('pr');
                    if ($id) {
                        $routeParams['id'] = $id;
                    }
                    return $this->redirect()->toRoute($redirectBackParams['route'],
                        $routeParams);
                }
                $documentId = $args['documentId'];
            }
        }
        try {
            // nom de la classe du formulaire : on s'assure qu'il commence par \
            $criteresFormId = (array) $criteresFormId;
            $criteresFormId[0] = '\\' . ltrim($criteresFormId[0], '\\');
            // paramètre d'appel du constructeur : on s'assure que la clé existe
            if (! isset($criteresFormId[1])) {
                $criteresFormId[1] = null;
            }
            $form = new $criteresFormId[0]($criteresFormId[1]);
            // on s'assure que le nom de la classe de l'object criteres commence par \
            $criteresObjectId = (array) $criteresObjectId;
            // paramètre d'appel de la méthode getWherePdf : on s'assure que la clé du
            // descripteur sera trouvée
            if (! isset($criteresObjectId[1])) {
                $criteresObjectId[1] = null;
            }
            if (is_array($criteresObjectId[0])) {
                $criteresObjectId[0][0] = '\\' . ltrim($criteresObjectId[0][0], '\\');
            } else {
                $criteresObjectId[0] = '\\' . ltrim($criteresObjectId[0], '\\');
            }
            // on crée la structure de l'objet criteres à partir des champs du formulaire
            // on la charge et on l'initialise éventuellement
            if (is_array($criteresObjectId[0])) {
                $criteres_obj = new $criteresObjectId[0][0]($form->getElementNames());
                // initialisation
                $criteres_obj->{$criteresObjectId[0][1]}($criteresObjectId[0][2]);
            } else {
                $criteres_obj = new $criteresObjectId[0]($form->getElementNames());
            }
            $criteres = Session::get('post', [],
                str_replace('pdf', 'liste', $this->getSessionNamespace()));
            if (! empty($criteres)) {
                $criteres_obj->exchangeArray($criteres);
            }
            $where = $criteres_obj->getWherePdf($criteresObjectId[1]);
            // adaptation éventuelle du where si une fonction callback (ou closure) est
            // passée en 3e paramètre dans le tableau $criteresObject. (Utile par exemple
            // pour modifier le format date avant le déclanchement de l'évènement ou pour
            // prendre en compte un autre where pour les groupes).
            if (! empty($criteresObjectId[2]) && is_callable($criteresObjectId[2])) {
                $where = $criteresObjectId[2]($where, $args);
            }
            $params = [
                'classDocument' => 'tableSimple'
            ];

            if ($docaffectationId = $this->params('id', false)) {
                // $docaffectationId par get - $args['documentId'] contient le libellé du
                // menu dans docaffectations
                $params['docaffectationId'] = $docaffectationId;
            }
            $params['documentId'] = $documentId;
            $params['where'] = $where;
            $pageheader_params = $criteres_obj->getPageheaderParams();
            if (array_key_exists('pageheader_title', $pageheader_params)) {
                $params['pageheader_title'] = $pageheader_params['pageheader_title'];
            }
            if (array_key_exists('pageheader_string', $pageheader_params)) {
                $params['pageheader_string'] = $pageheader_params['pageheader_string'];
            }
            if (array_key_exists('caractereConditionnel', $pdf_params)) {
                $key = $pdf_params['caractereConditionnel'];
                $pdf_params['caractereConditionnel'] = StdLib::getParam($key, $args, false);
            }
            if (array_key_exists('criteres', $pdf_params)) {
                $criteres = $criteres_obj->getCriteres();
                $params['criteres'] = $criteres['criteres'];
                $params['strict'] = $criteres['strict'];
                $params['expression'] = $criteres['expression'];
                unset($pdf_params['criteres']);
            }
            foreach ($pdf_params as $key => $value) {
                $params[$key] = $value;
            }
            return $params;
        } catch (\Exception $e) {
            if (getenv('APPLICATION_ENV') == 'development') {
                throw $e;
            }
            $this->flashMessenger()->addErrorMessage($e->getMessage());
            $routeParams = [

                'action' => $redirectBackParams['action'],
                'page' => $this->params('page', 1)
            ];
            $id = $this->params('pr');
            if ($id) {
                $routeParams['id'] = $id;
            }

            return $this->redirect()->toRoute($redirectBackParams['route'], $routeParams);
        }
    }

    /**
     * initListe est une méthode de contrôle d'entrée dans les xxxListeAction() - si c'est
     * un post, renvoie une redirection 303 - si c'est un get ou un retour d'action,
     * renvoie [paginator, form, retour] à partir des paramètres en session - si c'est une
     * redirection 303, renvoie [paginator, form, retour] à partir du post initial
     *
     * @param string|array $formName
     *            string : Le nom du formulaire est le nom de la table (ou de la vue) sur
     *            laquelle il porte. array : Tableau de définition des éléments à créer
     *            dans le formulaire
     * @param \Closure $initForm
     *            Fonction anonyme lancée juste après la création du formulaire avec comme
     *            paramètres la propriété config, le formulaire et les paramètres passés
     *            en post. Elle sert à initialiser les champs du formulaire, en
     *            particulier les listes déroulantes.
     * @param array $strictWhere
     *            Liste des champs du formulaire pour lesquels l'égalité est recherché.
     *            Pour les autres, on fait un Like
     * @param array $aliasWhere
     *            Liste des champs du formulaire qui sont des alias
     * @param \Closure $getArgs
     *            Fonction anomyme renvoyant un tableau extrait du tableau $args
     * @see \SbmCommun\Model\Db\ObjectData\Criteres::getWhere() pour plus d'explications.
     * @return <b>\SbmCommun\Model\Mvc\Controller\Response | array</b> Il faut tester si
     *         c'est un Response. Sinon, le tableau est de la forme ['paginator' => ...,
     *         'form' => ..., 'post' => [...], 'retour' => boolean]
     */
    protected function initListe($formName, $initForm = null, $strictWhere = [],
        $aliasWhere = [], $getArgs = null)
    {
        $retour = false;
        $prg = $this->prg();
        if ($prg instanceof Response) {
            // renvoie redirection 303 avec le contenu de post en session 'prg_post1'
            // (Expire_Hops= 1)
            return $prg;
        } elseif ($prg === false) {
            // ce n'était pas un post. Prendre les paramètres éventuellement dans la
            // session (cas du paginator)
            $this->sbm_isPost = false;
            $args = Session::get('post', [], $this->getSessionNamespace());
        } else {
            // c'est le tableau qui correspond au post après redirection 303; on le met en
            // session
            $args = $prg;
            $retour = StdLib::getParam('op', $args, '') == 'retour';
            if ($retour) {
                // dans ce cas, il s'agit du retour d'une action de type suppr, ajout ou
                // edit.
                // Comme pour un get, on récupère ce qui est en session.
                $this->sbm_isPost = false;
                $args = Session::get('post', [], $this->getSessionNamespace());
            } else {
                if (array_key_exists('cancel', $args)) {
                    try {
                        return $this->redirectToOrigin()->back();
                    } catch (\SbmCommun\Model\Mvc\Controller\Plugin\Exception\ExceptionInterface $e) {
                        return $args;
                    }
                } elseif (array_key_exists('origine', $args)) {
                    $this->redirectToOrigin()->setBack($args['origine']);
                    unset($args['origine']);
                }
                $this->sbm_isPost = true;
                unset($args['submit']);
                Session::set('post', $args, $this->getSessionNamespace());
            }
        }
        // formulaire des critères de recherche
        $criteres_form = new CriteresForm($formName);
        if (! is_null($initForm)) {
            $initForm($this->config, $criteres_form, $args);
        }
        $criteres_obj = new ObjectDataCriteres($criteres_form->getElementNames());
        if ($this->sbm_isPost) {
            $criteres_form->setData($args);
            if ($criteres_form->isValid()) {
                $criteres_obj->exchangeArray($criteres_form->getData());
            } elseif (getenv('APPLICATION_ENV') == 'development') {
                echo '<h3>Debug:</h3>';
                var_dump($criteres_form->getMessages());
            }
        }
        // récupère les données de la session si le post n'a pas été validé dans le
        // formulaire (pas de post ou invalide)
        if (! $criteres_form->hasValidated() && ! empty($args)) {
            $criteres_obj->exchangeArray($args);
            $criteres_form->setData($criteres_obj->getArrayCopy());
        }
        return [
            'where' => $criteres_obj->getWhere($strictWhere, $aliasWhere),
            'form' => $criteres_form,
            'post' => is_callable($getArgs) ? $getArgs($args) : [],
            'retour' => $retour
        ];
    }

    /**
     * Partie commune de traitement de l'ajout d'un enregistrement.
     * Le formulaire, le nom
     * de la table, son type et son alias sont passés dans le paramètre $params. Le
     * paramètre $renvoyer permet de retourner des données de POST. Les champs 'millesime'
     * des formulaires sont initialisés de manière automatique par la méthode setData() en
     * prenant la valeur en session.
     *
     * @param array $params
     *            Tableau associatif dont les clés principales sont 'form' et 'data'. La
     *            clé 'form' contient l'objet formulaire ; la clé 'data' est un tableau
     *            associatif dont les clés sont 'table', 'type' et 'alias'
     * @param callable|null $renvoyer
     *            Fonction perpettant d'extraire des données de POST ($args après
     *            PostRedirectGet)
     * @param callable|null $initform
     *            Fonction d'initialisation du formulaire. Son paramètre est $args
     *            (tableau des paramètres fournis en post ou en session)
     * @return \Zend\Http\PhpEnvironment\Response|string|int renvoie une redirection 303
     *         si c'est un post, ou une chaine de compte-rendu parmi {'error', 'warning',
     *         'success'} ou un id, ou le résultat de la fonction $renvoyer (souvent une
     *         fonction anonyme)
     */
    protected function addData($params, $renvoyer = null, $initform = null)
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
            $this->flashMessenger()->addWarningMessage(
                "Aucun enregistrement n'a été ajouté.");
            return 'warning';
        }
        $table = $this->db_manager->get($params['data']['alias']);
        $form = $params['form'];
        $form->setMaxLength(
            $this->db_manager->getMaxLengthArray($params['data']['table'],
                $params['data']['type']));
        if (is_callable($initform)) {
            $initform($args);
        }
        $form->bind($table->getObjData());
        if ($isPost) {
            $form->setData($args);
            if ($form->isValid()) {
                $table->saveRecord($form->getData());
                $this->flashMessenger()->addSuccessMessage(
                    "Un nouvel enregistrement a été ajouté.");
                return 'success';
            } elseif (getenv('APPLICATION_ENV') == 'development') {
                echo '<h3>Debug:</h3>';
                var_dump($form->getMessages());
            }
        } else {
            $form->setData(
                $this->db_manager->getColumnDefaults($params['data']['table'],
                    $params['data']['type']));
            if (is_callable($initform)) {
                $initform($args);
            }
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
     *            tableau associatif dont les clés sont 'form' et 'data'. La clé 'data'
     *            est elle-même associée à un tableau associatif dont les clés sont
     *            'table', 'type', 'alias' et 'id'
     * @param callable|null $renvoyer
     *            Fonction de construction de la réponse. Son paramètre est $args (tableau
     *            des paramètres fournis en post ou en session)
     * @param callable|null $initform
     *            Fonction d'initialisation du formulaire. Son paramètre est $args
     *            (tableau des paramètres fournis en post ou en session)
     * @param callable|null $prepareData
     *            Fonction qui prépare un premier objData contenant les anciennes primary
     *            keys et un autre objData avec les données modifiées
     * @return \Zend\Http\PhpEnvironment\Response|string|int renvoie une redirection 303
     *         si c'est un post, ou un \SbmCommun\Model\Mvc\Controller\EditResponse
     *         contenant les données à renvoyer
     */
    protected function editData($params, $renvoyer = null, $initform = null,
        $prepareData = null)
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            // on aura le droit de rentrer en get que si un args a été sauvegardé en
            // session avec un id de la donnée à modifier
            $args = Session::get('post', [], 'sbm_edit_' . $params['data']['table']);
            $isPost = false;
            $cancel = false;
        } else {
            $args = $prg;
            $isPost = StdLib::getParam('submit', $args, false);
            $cancel = StdLib::getParam('cancel', $args, false);
            unset($args['submit']);
            unset($args['cancel']);
            Session::set('post', $args, 'sbm_edit_' . $params['data']['table']);
        }
        if (is_array($params['data']['id'])) {
            $id = [];
            $interdit = false;
            foreach ($params['data']['id'] as $item) {
                if ($item == 'millesime') {
                    $id[$item] = Session::get('millesime');
                } else {
                    $id[$item] = StdLib::getParam($item, $args, - 1);
                    $interdit |= $id[$item] == - 1;
                }
            }
        } else {
            $id = StdLib::getParam($params['data']['id'], $args, - 1);
            $interdit = $id == - 1;
        }
        if ($interdit) {
            $this->flashMessenger()->addErrorMessage("Action interdite.");
            return new EditResponse('error', $args);
        } elseif ($cancel) {
            $this->flashMessenger()->addWarningMessage(
                "L'enregistrement n'a pas été modifié.");
            return new EditResponse('warning', $args);
        }
        $table = $this->db_manager->get($params['data']['alias']);

        $form = $params['form'];
        $form->setMaxLength(
            $this->db_manager->getMaxLengthArray($params['data']['table'],
                $params['data']['type']));
        if (is_callable($initform)) {
            $initform($args);
        }
        $form->bind($table->getObjData());
        if ($isPost) {
            if (is_callable($prepareData)) {
                $objDataWithPk = $table->getObjData()->exchangeArray($args);
                $form->setData($prepareData($args));
                if ($form->isValid()) {
                    $table->updateRecord($objDataWithPk, $form->getData());
                    $this->flashMessenger()->addSuccessMessage(
                        "Les modifications ont été enregistrées.");
                    return new EditResponse('success', $args);
                } else {
                    if (getenv('APPLICATION_ENV') == 'development') {
                        echo '<h3>Debug:</h3>';
                        var_dump($form->getMessages());
                    }
                    // remettre en place les hiddens
                    $args = array_merge($args, $objDataWithPk->getArrayCopy());
                    $form->setData($args);
                }
            } else {
                $form->setData($args);
                if ($form->isValid()) {
                    $table->saveRecord($form->getData());
                    $this->flashMessenger()->addSuccessMessage(
                        "Les modifications ont été enregistrées.");
                    return new EditResponse('success', $args);
                } elseif (getenv('APPLICATION_ENV') == 'development') {
                    echo '<h3>Debug:</h3>';
                    var_dump($form->getMessages());
                }
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
     *            tableau associatif dont les clés sont 'form' et 'data'. La clé 'data'
     *            est elle-même associée à un tableau associatif dont les clés sont
     *            'alias' et 'id' (sa cle 'id' donne l'id passé en post)
     * @param string $renvoyer
     *            Fonction de construction de la réponse. Ses paramètres sont $id (valeur
     *            de l'id) et $table (table dont l'alias est donné)
     * @return \Zend\Http\PhpEnvironment\Response|string|int renvoie une redirection 303
     *         si c'est un post, ou une chaine de compte-rendu parmi {'error', 'warning',
     *         'success'} ou un id, ou le résultat de la fonction $renvoyer (souvent une
     *         fonction anonyme)
     */
    protected function supprData($params, $renvoyer = null)
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            $this->flashMessenger()->addErrorMessage("Action interdite.");
            return new EditResponse('error', []);
        } else {
            $args = $prg;
            $confirme = StdLib::getParam('supproui', $args, false);
            $cancel = StdLib::getParam('supprnon', $args, false);
            if (is_array($params['data']['id'])) {
                $id = [];
                $interdit = false;
                foreach ($params['data']['id'] as $item) {
                    if ($item == 'millesime') {
                        $id[$item] = Session::get('millesime');
                    } elseif ($id[$item] = StdLib::getParam($item, $args, false)) {
                        Session::set($item, $id[$item], 'sbm_suppr');
                    } else {
                        $id[$item] = Session::get($item, - 1, 'sbm_suppr');
                    }
                    $interdit |= $id[$item] == - 1;
                }
                if ($interdit) {
                    $id = null;
                }
            } else {
                if ($id = StdLib::getParam($params['data']['id'], $args, false)) {
                    Session::set($params['data']['id'], $id, 'sbm_suppr');
                } else {
                    // ici, je controle si l'id en session est bien celui reçu par post
                    // (via prg).
                    // On ne sait jamais !!!
                    $id = Session::get($params['data']['id'], - 1, 'sbm_suppr');
                    $ctrl = StdLib::getParam('id', $args, - 1);
                    if ($id != $ctrl)
                        $id = null;
                }
            }
        }
        $table = $this->db_manager->get($params['data']['alias']);
        if (is_null($id) || ! $table->getObjData()->isValidId($id)) {
            $this->flashMessenger()->addErrorMessage("Action interdite.");
            return new EditResponse('error', $args);
        } elseif ($cancel) {
            $this->flashMessenger()->addWarningMessage(
                "L'enregistrement n'a pas été supprimé.");
            return new EditResponse('warning', $args);
        } else {
            // pour les primary key composées de plusieurs champs, id est une chaine où
            // les champs sont séparés par des |
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
                $form->setData([
                    'id' => implode('|', $id)
                ]);
            } else {
                $form->setData([
                    'id' => $id
                ]);
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
    protected function getPaginatorCountPerPage($paginateurId, $default)
    {
        try {
            return (int) StdLib::getParam($paginateurId, $this->paginator_count_per_page,
                $default);
        } catch (Exception\ExceptionInterface $e) {
            return $default;
        }
    }

    /**
     * Renvoie une chaine de la forme 'module_controller_action_item'
     *
     * @param string|null $action
     *            Si $action est null alors on prend l'action indiquée dans la route
     *            courante
     * @param string|null $item
     *            Ce que l'on veut rajouter
     * @return string
     */
    protected function getSessionNamespace($action = null, $item = null)
    {
        $args = [
            $this->getModuleControllerName(),
            $action ?: $this->getCurrentActionFromRoute()
        ];
        if (! is_null($item)) {
            $args[] = $item;
        }
        return str_replace('-', '_', implode('_', $args));
    }

    /**
     * Renvoie une chaine de la forme 'module_controller' exemple : sbmfront_index
     *
     * @return string
     */
    public function getModuleControllerName()
    {
        $parts = explode('\\', strtolower(get_class($this))); // de la forme {'sbmfront',
                                                              // 'controller',
                                                              // 'indexcontroller'}
        unset($parts[1]); // supprime 'controller'
        return substr_replace(implode('_', $parts), '', - 10); // supprime 'controller' à
                                                               // la fin
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
     * Retrouve le responsableId enregistré en session dans l'action origine de l'appel
     * L'appel doit se faire en POST en passant l'argument 'namespacectrl' qui contient la
     * valeur : md5($nsArgCtrl).
     * Si ce n'est pas cette valeur alors il y a imposture. Si
     * c'est bon, on cherche le paramètre $nsArgCtrl en session dans SBM_DG_SESSION qui
     * indique le namespace de session dans lequel on trouvera 'post', un tableau
     * contenant une clé 'responsableId'. En cas d'erreur, on arrête tout par un die()
     * puisque l'appel a du être fait dans une nouvelle fenêtre (target = _blank)
     *
     * @return int
     */
    protected function getResponsableIdFromSession($nsArgCtrl)
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            $args = Session::get('post', false, $this->getSessionNamespace());
        } else {
            $args = $prg;
            Session::set('post', $args, $this->getSessionNamespace());
        }
        $namespacectrl = StdLib::getParam('namespacectrl', $args, '');
        if ($namespacectrl != md5($nsArgCtrl)) {
            die('Imposteur !');
        } else {
            $nsArgsFacture = Session::get($nsArgCtrl, '');
            $args = Session::get('post', false, $nsArgsFacture);
        }
        // on a récupéré le post de l'origine de l'appel qui doit contenir le
        // responsableId
        $responsableId = StdLib::getParam('responsableId', $args, false);
        if ($responsableId === false) {
            die('Le destinataire de la facture est inconnu.');
        }
        return $responsableId;
    }
}