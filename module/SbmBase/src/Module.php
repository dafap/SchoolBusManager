<?php
/**
 * Module de base du projet qui définie le modèle de chargement des modules et
 * diverses classes proposant des méthodes statiques générales.
 *
 * @project sbm
 * @package SbmBase\Module
 * @filesource AbstractModule.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 16 fév.2019
 * @version 2019-2.5.0
 */
namespace SbmBase;

use SbmBase\Model\StdLib;
use Zend\EventManager\EventInterface;
use Zend\ModuleManager\Feature\BootstrapListenerInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\ServiceProviderInterface;
use Zend\Session\Container;
use Zend\Session\SessionManager;

class Module implements BootstrapListenerInterface, ConfigProviderInterface,
    ServiceProviderInterface
{

    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }

    public function getServiceConfig()
    {
        return [
            'factories' => [
                'SbmAuthentificationManager' => function ($sm) {
                    $config = $sm->get('config');
                    if (isset($config['sbm_session'])) {
                        $session = $config['sbm_session'];
                        $sessionConfig = null;
                        if (isset($session['config'])) {
                            $class = isset($session['config']['class']) ? $session['config']['class'] : 'Zend\Session\Config\StandardConfig';
                            $options = isset($session['config']['options']) ? $session['config']['options'] : [];
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
                            // cette classe doit être récupérée par le service manager car son
                            // constructeur a des arguments
                            $sessionSaveHandler = $sm->get($session['save_handler']);
                        }
                        $sessionManager = new SessionManager($sessionConfig,
                            $sessionStorage, $sessionSaveHandler);
                    } else {
                        $sessionManager = new SessionManager();
                    }
                    Container::setDefaultManager($sessionManager);
                    return $sessionManager;
                }
            ]
        ];
    }

    public function onBootstrap(EventInterface $e)
    {
        $this->bootstrapSession($e);
    }

    /**
     * Mise en place des sessions
     *
     * @param EventInterface $e
     */
    private function bootstrapSession(EventInterface $e)
    {
        $session = $e->getApplication()
            ->getServiceManager()
            ->get('SbmAuthentificationManager');
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
            try {
                $session->regenerateId(true);
            } catch (\Exception $e) {
                // try catch nécessaire pour les tests unitaires avec AbstractControllerTestCase
            }
            $container->init = 1;
            if ($request instanceof \Zend\Http\PhpEnvironment\Request) {
                $container->remoteAddr = $request->getServer()->get('REMOTE_ADDR');
                $container->httpUserAgent = $request->getServer()->get('HTTP_USER_AGENT');
            }

            $config = $serviceManager->get('config');
            if (! StdLib::array_keys_exists([
                'sbm_session',
                'validators'
            ], $config)) {
                return;
            }

            $validators = $config['sbm_session']['validators'];
            $chain = $session->getValidatorChain();
            foreach ($validators as $validator) {
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
                $chain->attach('session.validate', [
                    $validator,
                    'isValid'
                ]);
            }
        }
    }
}
