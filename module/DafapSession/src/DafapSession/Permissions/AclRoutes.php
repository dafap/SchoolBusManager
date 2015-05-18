<?php
/**
 * Définit un dispositif d'autorisation basé sur les droits à des routes et des actions pour un utilisateur identifié
 *
 * Les rôles et les autorisations sont décrits dans les fichiers module.config.php
 * Les resources sont des parties de routes (début ou complète) suivie de l'action.
 * Les autorisations sont basées sur la hiérarchie du nom de la route.
 * Par exemple: deny sur gestion entraine deny sur gestion/eleve::eleve-liste
 * Par contre: deny sur gestion et allow sur gestion/eleve::eleve-liste est possible
 * 
 * @project sbm
 * @package DafapSession/Permissions
 * @filesource AclRoutes.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 14 mai 2015
 * @version 2015-1
 */
namespace DafapSession\Permissions;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Permissions\Acl\Acl;
use Zend\Mvc\MvcEvent;
// use Zend\Mvc\Controller\Plugin\PluginInterface;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\Mvc\Router\Http\RouteMatch;
use SbmCommun\Model\StdLib;

class AclRoutes extends AbstractPlugin
{

    /**
     * Rôle par défaut
     */
    const DEFAULT_ROLE = 'guest';

    /**
     * Route de redirection par défaut
     */
    const DEFAULT_REDIRECT_TO = 'home';

    /**
     *
     * @var \Zend\Permissions\Acl\Acl
     */
    protected $acl;

    /**
     *
     * @var \Zend\ServiceManager\ServiceLocatorInterface
     */
    protected $serviceLocator;

    /**
     *
     * @var \DafapSession\Authentication\AuthenticationService
     */
    protected $authenticationService;

    /**
     * Route vers laquelle on redirige si les accès ne sont pas valides
     *
     * @var string
     */
    protected $redirectTo;

    /**
     * Correspondance entre le role et la catégorie
     *
     * @var array
     */
    protected $roleId;

    /**
     * Création du service
     *
     * (non-PHPdoc)
     *
     * @see \Zend\ServiceManager\FactoryInterface::createService()
     */
    public function init()
    {
        $this->serviceLocator = $this->controller->getServiceLocator(); // $servicePlugin->getServiceLocator();
        $this->authenticationService = $this->serviceLocator->get('Dafap\Authenticate')->by('email');
        $this->acl = new Acl();
        $config = $this->serviceLocator->get('config')['acl'];
        if (array_key_exists('roleId', $config)) {
            $this->roleId = $config['roleId'];
            if (array_key_exists('roles', $config)) {
                $roles = $config['roles'];
                foreach ($roles as $role => $parents) {
                    $this->acl->addRole($role, $parents);
                }
            }
        }
        if ($this->authenticationService->hasIdentity() && array_key_exists('redirectTo', $config)) {
            $key = $this->authenticationService->getCategorieId();
            if (array_key_exists($key, $this->roleId)) {
                $this->redirectTo = $config['redirectTo'][$this->roleId[$key]];
                return;
            }
        }
        $this->redirectTo = self::DEFAULT_REDIRECT_TO;
        return $this;
    }

    public function dispatch(MvcEvent $e)
    {
        $this->init();
        $this->build($e->getRouteMatch());
        
        // Récupération du rôle de l'utilisateur courant
        if (! $this->authenticationService->hasIdentity()) {
            $role = self::DEFAULT_ROLE;
        } else {
            $role = $this->roleId[$this->authenticationService->getCategorieId()];
        }
        
        // Si l'utilisateur n'est pas autorisé, on le redirige vers la page par défaut, ou une autre page si elle a été spécifiée
        // dans le fichier de configuration
        $matchedRouteName = $e->getRouteMatch()->getMatchedRouteName();
        $resource = $matchedRouteName . '::' . $e->getRouteMatch()->getParam('action');
        if (! $this->acl->isAllowed($role, $resource)) {
            $this->redirect($e, $this->redirectTo);
        }
    }

    /**
     *
     * @param \Zend\Mvc\MvcEvent $e            
     * @param string $route            
     */
    private function redirect(MvcEvent $e, $route)
    {
        $url = $e->getRouter()->assemble(array(), array(
            'name' => $route
        ));
        $response = $e->getResponse();
        $response->getHeaders()->addHeaderLine('Location', $url);
        $response->setStatusCode(302);
        $response->sendHeaders();
        exit();
    }

    /**
     * Le nom de la resource est matchedRouteName::action
     * On va construire toutes les resources parents avec leurs droits
     *
     * @param \Zend\Mvc\Router\RouteMatch $routeMatch            
     * @return mixed
     */
    private function build(RouteMatch $routeMatch)
    {
        $matchedRouteName = $routeMatch->getMatchedRouteName();
        $action = $routeMatch->getParam('action');
        $actions = array();
        // Récupération de la configuration
        $config = $this->serviceLocator->get('config')['acl'];
        $resources = StdLib::getParamR(array(
            'acl',
            'resources'
        ), $this->serviceLocator->get('config'), array());
        $routeParts = explode('/', $matchedRouteName);
        $parentName = null;
        $resourceNameParts = array();
        foreach ($routeParts as $routePart) {
            $resourceNameParts[] = $routePart;
            $resourceName = implode('/', $resourceNameParts);            
            $this->acl->addResource($resourceName, $parentName);
            // Par défaut, on interdit l'accès à toute ressource dont l'ACL racine n'a pas été défini
            if (is_null($parentName) && ! array_key_exists($routePart, $resources)) {
                $this->acl->deny(self::DEFAULT_ROLE, $routePart);
                $resources = array();
            }
            if (array_key_exists($routePart, $resources)) {
                $resources = $resources[$routePart];               
                if (array_key_exists('allow', $resources)) {
                    $allow = $resources['allow'];
                    $assertion = $this->buildAssertion($allow);
                    $this->acl->allow($allow['roles'], $resourceName, null, $assertion);
                }
                if (array_key_exists('deny', $resources)) {
                    $deny = $resources['deny'];
                    $assertion = $this->buildAssertion($deny);
                    $this->acl->deny($deny['roles'], $resourceName, null, $assertion);
                }        
                if (array_key_exists('redirect_to', $resources)) {
                    $this->redirectTo = $resources['redirect_to'];
                }               
                // Y a-t-il des enfants ?
                if (array_key_exists('child_resources', $resources)) {
                    $resources = $resources['child_resources'];
                } else {
                    if (array_key_exists('actions', $resources)) {
                        $actions = $resources['actions'];
                    }
                    $resources = array();
                }               
            } else {
                $resource = array();
            }
            $parentName = $resourceName;
        }        
        // On définit la route "complète" comme enfant de la dernière ressource 
        // afin d'hériter de ses autorisations et d'ajouter éventuellement les siennes
        $resourceName = $matchedRouteName . '::' . $action;
        $this->acl->addResource($resourceName, $matchedRouteName);
        // et les droits relatifs à cette action s'il y en a
        if (array_key_exists($action, $actions)) {
            $resources = $actions[$action];
            if (array_key_exists('allow', $resources)) {
                $allow = $resources['allow'];
                $assertion = $this->buildAssertion($allow);
                $this->acl->allow($allow['roles'], $resourceName, null, $assertion);
            } 
            if (array_key_exists('deny', $resources)) {
                $deny = $resources['deny'];
                $assertion = $this->buildAssertion($deny);
                $this->acl->deny($deny['roles'], $resourceName, null, $assertion);
            }
            if (array_key_exists('redirect_to', $resources)) {
                $this->redirectTo = $resources['redirect_to'];
            }
        }
    }

    /**
     *
     * @param array $resourcePart            
     * @return null
     */
    private function buildAssertion(array $resourcePart)
    {
        if (isset($resourcePart['assertion'])) {
            return new $resourcePart['assertion']();
        }
        
        return null;
    }
}