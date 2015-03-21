<?php
/**
 * Module de configuration des sessions
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
 * @date 15 févr. 2015
 * @version 2015-1
 */
namespace DafapSession;

use Zend\EventManager\EventInterface;
use Zend\Session\SessionManager;
use Zend\Session\Container;
use Zend\Mvc\ModuleRouteListener;

class Module
{
    public function onBootstrap(EventInterface $e)
    {
        $eventManager        = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
        $this->bootstrapSession($e);
    }
    
    public function bootstrapSession(EventInterface $e)
    {
        $session = $e->getApplication()
                     ->getServiceManager()
                     ->get('DafapSessionManager');
        $session->start();

        $container = new Container('initialized');
        if (!isset($container->init)) {
            $serviceManager = $e->getApplication()->getServiceManager();
            $request        = $serviceManager->get('Request');

            $session->regenerateId(true);
            $container->init          = 1;
            $container->remoteAddr    = $request->getServer()->get('REMOTE_ADDR');
            $container->httpUserAgent = $request->getServer()->get('HTTP_USER_AGENT');

            $config = $serviceManager->get('config');
            if (!isset($config['dafap_session'])) {
                return;
            }

            $sessionConfig = $config['dafap_session'];
            if (isset($sessionConfig['validators'])) {
                $chain   = $session->getValidatorChain();

                foreach ($sessionConfig['validators'] as $validator) {
                    switch ($validator) {
                        case 'Zend\Session\Validator\HttpUserAgent':
                            $validator = new $validator($container->httpUserAgent);
                            break;
                        case 'Zend\Session\Validator\RemoteAddr':
                            $validator  = new $validator($container->remoteAddr);
                            break;
                        default:
                            $validator = new $validator();
                    }

                    $chain->attach('session.validate', array($validator, 'isValid'));
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
                            $class = isset($session['config']['class'])  ? $session['config']['class'] : 'Zend\Session\Config\SessionConfig';
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
                },
            ),
        );
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__
                )
            )
        );
    }
}
