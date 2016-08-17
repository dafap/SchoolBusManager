<?php
/**
 * Module de configuration des sessions, des acl, des traductions
 *
 * Le fichier de configuration de ce module contient les paramètres de session dans un tableau de forme suivante :
 * array(
 *   'dafap_session' => array(
 *      'config' => array(
 *         'classe => string,   // soit Zend\Session\Config\StandardConfig (par défaut), soit Zend\Session\Config\SessionConfig
 *         'options' => array() // description de la configuration des sessions
 *         ),
 *      'storage' => string,    // alias ou nom de classe enregistrée comme storage dans le service manager
 *      'save_handler' => null, // ou un Zend\Session\SaveHandler\SaveHandlerInterface si nécessaire
 *      'validators' => array() // nom des classes enregistrées comme session validator
 *   )
 * )
 *
 * @project sbm
 * @package DafapSession
 * @filesource Module.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 17 août 2016
 * @version 2016-2.2.0
 */
namespace DafapSession;

use Zend\EventManager\EventInterface;
use Zend\Mvc\MvcEvent;
use Zend\Session\SessionManager;
use Zend\Session\Container;
use Zend\Validator\AbstractValidator;
use Zend\I18n\Translator\Resources;
use Zend\I18n\Translator\Translator;
use SbmBase\Module\AbstractModule;

class Module extends AbstractModule
{
    public function getDir()
    {
        return __DIR__;
    }
    
    public function getNamespace()
    {
        return __NAMESPACE__;
    }
    
    public function onBootstrap(EventInterface $e)
    {
        $this->bootstrapSession($e);
        $eventManager = $e->getApplication()->getEventManager();
        $eventManager->attach('route', array(
            $this,
            'bootstrapPermissions'
        ), 100);
        $this->bootstrapTranslation($e);
    }

    /**
     * Mise en place des traductions
     *
     * @param EventInterface $e            
     */
    private function bootstrapTranslation(EventInterface $e)
    {
        $translator = $e->getApplication()->getServiceManager()->get('MvcTranslator');
        $translator->addTranslationFile('phpArray', sprintf(Resources::getBasePath() . Resources::getPatternForValidator(), 'fr'));
        AbstractValidator::setDefaultTranslator($translator);
    }

    /**
     * Mise en place des permissions
     *
     * @param EventInterface $e            
     */
    public function bootstrapPermissions(EventInterface $e)
    {
        $application = $e->getApplication();
        $sm = $application->getServiceManager();
        $sharedManager = $application->getEventManager()->getSharedManager();
        $router = $sm->get('router');
        $request = $sm->get('request');
        $matchedRoute = $router->match($request);
        if (null !== $matchedRoute) {
            $sharedManager->attach('Zend\Mvc\Controller\AbstractActionController', MvcEvent::EVENT_DISPATCH, function ($e) use($sm) {
                $sm->get('Dafap\AclRoutes')
                    ->dispatch($e); // pass to the plugin...
            }, 100);
        }
    }

    /**
     * Mise en place des sessions
     *
     * @param EventInterface $e            
     */
    public function bootstrapSession(EventInterface $e)
    {
        $session = $e->getApplication()
            ->getServiceManager()
            ->get('DafapSessionManager');
        try {
            $session->start();
        } catch (\Zend\Session\Exception\RuntimeException $e) {
            $session->expireSessionCookie();
            $session->start();
        }
        
        $container = new Container('initialized');
        if (! isset($container->init)) {
            $serviceManager = $e->getApplication()->getServiceManager();
            $request = $serviceManager->get('Request');
            
            $session->regenerateId(true);
            $container->init = 1;
            if ($request instanceof \Zend\Http\PhpEnvironment\Request) {
                $container->remoteAddr = $request->getServer()->get('REMOTE_ADDR');
                $container->httpUserAgent = $request->getServer()->get('HTTP_USER_AGENT');
            }
            
            $config = $serviceManager->get('config');
            if (! isset($config['dafap_session'])) {
                return;
            }
            
            $sessionConfig = $config['dafap_session'];
            if (isset($sessionConfig['validators'])) {
                $chain = $session->getValidatorChain();
                
                foreach ($sessionConfig['validators'] as $validator) {
                    switch ($validator) {
                        case 'Zend\Session\Validator\HttpUserAgent':
                            $validator = new $validator($container->httpUserAgent);
                            break;
                        case 'Zend\Session\Validator\RemoteAddr':
                            $validator = new $validator($container->remoteAddr);
                            break;
                        default:
                            $validator = new $validator();
                    }
                    
                    $chain->attach('session.validate', array(
                        $validator,
                        'isValid'
                    ));
                }
            }
        }
    }

    public function getServiceConfig()
    {
        return array(
            'factories' => array(
                'DafapSessionManager' => function ($sm) {
                    $config = $sm->get('config');
                    if (isset($config['dafap_session'])) {
                        $session = $config['dafap_session'];
                        
                        $sessionConfig = null;
                        if (isset($session['config'])) {
                            $class = isset($session['config']['class']) ? $session['config']['class'] : 'Zend\Session\Config\SessionConfig';
                            $options = isset($session['config']['options']) ? $session['config']['options'] : array();
                            $sessionConfig = new $class();
                            $sessionConfig->setOptions($options);
                        }
                        
                        $sessionStorage = null;
                        if (isset($session['storage'])) {
                            $class = $session['storage'];
                            $sessionStorage = new $class();
                        }
                        
                        $sessionSaveHandler = null;
                        if (isset($session['save_handler'])) {
                            // cette classe doit être récupérée par le service manager car son constructeur a des arguments
                            $sessionSaveHandler = $sm->get($session['save_handler']);
                        }
                        
                        $sessionManager = new SessionManager($sessionConfig, $sessionStorage, $sessionSaveHandler);
                    } else {
                        $sessionManager = new SessionManager();
                    }
                    Container::setDefaultManager($sessionManager);
                    return $sessionManager;
                }
            )
        );
    }
}
