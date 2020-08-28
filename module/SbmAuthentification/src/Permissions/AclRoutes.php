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
 * @package SbmAuthentification/Permissions
 * @filesource AclRoutes.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 28 août 2020
 * @version 2020-2.6.0
 */
namespace SbmAuthentification\Permissions;

use SbmBase\Model\StdLib;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\RouteMatch as BaseRouteMatch;
use Zend\Mvc\Router\Http\RouteMatch;
use Zend\Permissions\Acl\Acl;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class AclRoutes implements FactoryInterface
{
    use \SbmCommun\Model\Traits\DebugTrait;

    /**
     * Rôle par défaut
     */
    const DEFAULT_ROLE = 'guest';

    /**
     * Route de redirection par défaut
     */
    const DEFAULT_REDIRECT_TO = 'home';

    /**
     * Est créé dans le createService
     *
     * @var \Zend\Permissions\Acl\Acl
     */
    protected $acl;

    /**
     * Est initialisé par la config_application dans le createService
     *
     * @var array
     */
    private $acl_config = [];

    /**
     * Est initialisé par la service manager dans le createService
     *
     * @var \SbmAuthentification\Authentication\AuthenticationService
     */
    protected $authenticationService;

    /**
     * Route vers laquelle on redirige si les accès ne sont pas valides (dépend de la
     * catégorie de l'utilisateur ou prend la valeur par défaut)
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
     * (non-PHPdoc)
     *
     * @see \Zend\ServiceManager\FactoryInterface::createService()
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $this->authenticationService = $serviceLocator->get(
            'SbmAuthentification\Authentication')->by('email');
        $this->acl_config = $serviceLocator->get('config')['acl'];
        $this->acl = new Acl();
        if (array_key_exists('roleId', $this->acl_config)) {
            $this->roleId = $this->acl_config['roleId'];
            if (array_key_exists('roles', $this->acl_config)) {
                foreach ($this->acl_config['roles'] as $role => $parents) {
                    $this->acl->addRole($role, $parents);
                }
            }
        }
        if ($this->authenticationService->hasIdentity() &&
            array_key_exists('redirectTo', $this->acl_config)) {
            $key = $this->authenticationService->getCategorieId();
            if (array_key_exists($key, $this->roleId)) {
                $role = $this->roleId[$key];
                while (! is_null($role) &&
                    ! array_key_exists($role, $this->acl_config['redirectTo'])) {
                    $role = $this->acl_config['roles'][$role];
                }
                $this->redirectTo = $this->acl_config['redirectTo'][$role];
                return $this;
            }
        }
        $this->redirectTo = self::DEFAULT_REDIRECT_TO;
        return $this;
    }

    public function dispatch(MvcEvent $e)
    {
        $routeMatch = $e->getRouteMatch();
        if ($routeMatch instanceof RouteMatch) {
            $this->build($routeMatch);

            // Récupération du rôle de l'utilisateur courant
            if (! $this->authenticationService->hasIdentity()) {
                $role = self::DEFAULT_ROLE;
            } else {
                $role = $this->roleId[$this->authenticationService->getCategorieId()];
            }

            // Si l'utilisateur n'est pas autorisé, on le redirige vers la page par
            // défaut, ou une
            // autre page si elle a été spécifiée
            // dans le fichier de configuration
            $matchedRouteName = $e->getRouteMatch()->getMatchedRouteName();
            $resource = $matchedRouteName . '::' . $e->getRouteMatch()->getParam('action');
            if (! $this->acl->isAllowed($role, $resource)) {
                $this->redirect($e, $this->redirectTo);
            }
        }
    }

    /**
     *
     * @param \Zend\Mvc\MvcEvent $e
     * @param string $route
     */
    private function redirect(MvcEvent $e, $route)
    {
        if (empty($route)) {
            $route = self::DEFAULT_REDIRECT_TO;
        }
        $url = $e->getRouter()->assemble([], [
            'name' => $route
        ]);
        $response = $e->getResponse();
        $response->getHeaders()->addHeaderLine('Location', $url);
        $response->setStatusCode(302);
        $response->sendHeaders();
        exit();
    }

    /**
     * Le nom de la resource est matchedRouteName::action On va construire toutes les
     * resources parents avec leurs droits
     *
     * @param \Zend\Mvc\Router\RouteMatch $routeMatch
     * @return mixed
     */
    private function build(BaseRouteMatch $routeMatch)
    {
        $matchedRouteName = $routeMatch->getMatchedRouteName();
        $action = $routeMatch->getParam('action');
        $actions = [];
        // Récupération de la configuration
        $resources = StdLib::getParam('resources', $this->acl_config, []);
        $routeParts = explode('/', $matchedRouteName);
        $parentName = null;
        $resourceNameParts = [];
        foreach ($routeParts as $routePart) {
            $resourceNameParts[] = $routePart;
            $resourceName = implode('/', $resourceNameParts);
            $this->acl->addResource($resourceName, $parentName);
            // Par défaut, on interdit l'accès à toute ressource dont l'ACL racine n'a pas
            // été
            // défini
            if (is_null($parentName) && ! array_key_exists($routePart, $resources)) {
                $this->acl->deny(self::DEFAULT_ROLE, $routePart);
                $resources = [];
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
                    $resources = [];
                }
            } else {
                $resources = [];
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