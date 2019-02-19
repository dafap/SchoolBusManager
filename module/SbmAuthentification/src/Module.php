<?php
/**
 * Module de configuration des sessions, des acl, des traductions
 *
 * Le fichier de configuration de ce module contient les paramètres de session dans un tableau 
 * de forme suivante :
 * [
 *   'dafap_session' => [
 *      'config' => [
 *         'classe => string,   // soit \Zend\Session\Config\StandardConfig (par défaut), 
 *                              // soit \Zend\Session\Config\SessionConfig
 *         'options' =>[] // description de la configuration des sessions
 *         ],
 *      'storage' => string,    // alias ou nom de classe enregistrée comme storage dans le service manager
 *      'save_handler' => null, // ou un \Zend\Session\SaveHandler\SaveHandlerInterface si nécessaire
 *      'validators' =>[] // nom des classes enregistrées comme session validator
 *   ]
 * ]
 *
 * @project sbm
 * @package SbmAuthentification
 * @filesource Module.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 28 sept. 2018
 * @version 2019-2.5.0
 */
namespace SbmAuthentification;

use SbmBase\Module\AbstractModule;
use Zend\EventManager\EventInterface;
use Zend\I18n\Translator\Resources;
use Zend\Mvc\MvcEvent;
use Zend\Validator\AbstractValidator;

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
        $eventManager->attach('route', [
            $this,
            'bootstrapPermissions'
        ], 100);
        $this->bootstrapTranslation($e);
    }

    /**
     * Mise en place des traductions
     *
     * @param EventInterface $e
     */
    private function bootstrapTranslation(EventInterface $e)
    {
        $translator = $e->getApplication()
            ->getServiceManager()
            ->get('MvcTranslator');
        $translator->addTranslationFile('phpArray',
            sprintf(Resources::getBasePath() . Resources::getPatternForValidator(), 'fr'));
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
            $sharedManager->attach('Zend\Mvc\Controller\AbstractActionController',
                MvcEvent::EVENT_DISPATCH,
                function ($e) use ($sm) {
                    $sm->get('SbmAuthentification\AclRoutes')
                        ->dispatch($e); // pass to the plugin...
                }, 100);
        }
    }
}
