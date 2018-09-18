<?php
/**
 * Extension du plugin Redirect pour ajouter 2 méthodes :
 * 
 * - setBack($url) mémorise une adresse de retour dans une pile en session. Si $url est null, empile l'url courante.
 * - back() redirige sur l'adresse en haut de la pile et dépile
 *
 * Si la pile est vide, back() ne fait rien.
 * 
 * @project project_name
 * @package package_name
 * @filesource RedirectBack.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 10 sept. 2018
 * @version 2018-2.4.5
 */
namespace SbmCommun\Model\Mvc\Controller\Plugin\Service;

use SbmCommun\Model\Mvc\Controller\Plugin\Exception;
use Zend\Mvc\Controller\Plugin\Redirect;
use Zend\Session\Container;
use Zend\Session\ManagerInterface as Manager;

class RedirectBack extends Redirect
{

    /**
     * 
     * @var \Zend\Session\Container
     */
    protected $container;

    /**
     * 
     * @var \Zend\Session\ManagerInterface
     */
    protected $session;

    /**
     * Vide la pile
     */
    public function reset()
    {
        $container = $this->getContainer();
        while (! empty($container->back)) {
            $index = count($container->back) - 1;
            unset($container->back[$index]);
        }
    }
    
    /**
     * Prend l'url en haut de la pile, dépile et redirige sur cette url
     */
    public function back()
    {
        $container = $this->getContainer();
        if (! empty($container->back)) {
            $index = count($container->back) - 1;
            $url = $container->back[$index];
            unset($container->back[$index]);
            return $this->toUrl($url);
        } else {
            throw new Exception(__METHOD__ . ' - Pile vide');
        }
    }

    /**
     * Empile l'url donnée dans la pile.
     * Si $url est null, on empile l'adresse actuelle
     *
     * @param string $url            
     *
     * @throws \DomainException
     * 
     * @return \SbmCommun\Model\Mvc\Controller\Plugin\Service\RedirectBack
     */
    public function setBack($url = null)
    {
        if (is_null($url)) {
            $controller = $this->getController();
            if (! $controller || ! method_exists($controller, 'plugin')) {
                throw new \DomainException('Redirect plugin requires a controller that defines the plugin() method');
            }
            
            $urlPlugin = $controller->plugin('url');
            $url = $urlPlugin->fromRoute(null, [], [], true);
        }
        $container = $this->getContainer();
        if (! isset($container->back)) {
            $container->back = [$url];
        } else {
            $container->back[] = $url;
        }
        return $this;
    }

    /**
     * Set the session manager. Fluent interface.
     *
     * @param Manager $manager         
     *    
     * @return \SbmCommun\Model\Mvc\Controller\Plugin\Service\RedirectBack
     */
    public function setSessionManager(Manager $manager)
    {
        $this->session = $manager;
        
        return $this;
    }

    /**
     * Retrieve the session manager
     *
     * If none composed, lazy-loads a SessionManager instance
     *
     * @return Manager
     */
    public function getSessionManager()
    {
        if (! $this->session instanceof Manager) {
            $this->setSessionManager(Container::getDefaultManager());
        }
        
        return $this->session;
    }

    /**
     * Get session container for flash messages
     *
     * @return Container
     */
    public function getContainer()
    {
        if ($this->container instanceof Container) {
            return $this->container;
        }
        
        $manager = $this->getSessionManager();
        $this->container = new Container('RedirectBack', $manager);
        
        return $this->container;
    }
}