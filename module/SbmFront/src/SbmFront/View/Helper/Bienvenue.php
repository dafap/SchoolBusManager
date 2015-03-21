<?php
/**
 * Description courte du fichier
 *
 * Description longue du fichier s'il y en a une
 * 
 * @project project_name
 * @package package_name
 * @filesource Bienvenue.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 13 févr. 2015
 * @version 2015-1
 */
namespace SbmFront\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Zend\View\Helper\Url;
use SbmFront\Model\Authenticate;
use Zend\Session\Container;

class Bienvenue extends AbstractHelper
{

    /**
     *
     * @var AuthenticationService
     */
    protected $authService;

    public function __invoke()
    {
        if ($this->getAuthService()->hasIdentity()) {
            $identity = $this->getAuthService()->getIdentity();
            $bienvenue = $identity['prenom'] . ' ' . $identity['nom'];
            $view = $this->getView();
            $logout = $view->url('login', array(
                'action' => 'logout'
            ));
            $container = new Container('layout');
            $route = $container->home;
            $url_compte = $view->url($route, array('action' => 'modif-compte'));
            $url_mdp = $view->url($route, array('action' => 'mdp-change'));
            $url_email = $view->url($route, array('action' => 'email-change'));
            $url_msg = $view->url($route, array('action' => 'message'));
            return <<<EOT
<div id="bienvenue" class="bienvenue">
   <div>
   <ul id="menu">
       <li>Bienvenue $bienvenue
       <ul>
           <li><a href="$url_compte">Mon compte</a></li>
           <li><a href="$url_mdp">Changer mon mot de passe</a></li>
           <li><a href="$url_email">Changer mon email</a></li>
           <li><a href="$url_msg">Mes messages</a></li>
       </ul> 
       </li>        
       <li>| <a href="$logout"><i class="fam-door-out"></i>déconnexion</a></li>
   </ul>
   </div>  
</div>
EOT;
        } else {
            return '';
        }
    }

    /**
     * Get authService.
     *
     * @return AuthenticationService
     */
    public function getAuthService()
    {
        return $this->authService;
    }

    /**
     * Set authService.
     *
     * @param AuthenticationService $authService            
     * @return \ZfcUser\View\Helper\ZfcUserIdentity
     */
    public function setAuthService(Authenticate $authService)
    {
        $this->authService = $authService;
        return $this;
    }
}