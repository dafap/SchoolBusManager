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
 * @package SbmAuthentification
 * @filesource Module.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 18 août 2016
 * @version 2016-2.2.0
 */
namespace SbmAuthentification;

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
                $sm->get('SbmAuthentification\AclRoutes')
                    ->dispatch($e); // pass to the plugin...
            }, 100);
        }
    }
}
