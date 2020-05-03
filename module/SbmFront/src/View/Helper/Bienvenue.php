<?php
/**
 * Aide de vue pour afficher le menu du haut (année scolaire, bienvenue, logout)
 *
 * @project sbm
 * @package SbmFront/View/Helper
 * @filesource Bienvenue.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 30 avr. 2020
 * @version 2020-2.6.0
 */
namespace SbmFront\View\Helper;

use SbmAuthentification\Authentication\AuthenticationService;
use SbmBase\Model\Session;
use Zend\Session\Container;
use Zend\View\Helper\AbstractHelper;

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
            $annee_scolaire = Session::get('as')['libelle'];
            $identity = $this->getAuthService()->getIdentity();
            $bienvenue = $identity['prenom'] . ' ' . $identity['nom'];
            $categorieId = $identity['categorieId'];
            $view = $this->getView();
            $logout = $view->url('login', [
                'action' => 'logout'
            ]);
            $container = new Container('layout');
            $route = $container->home;
            $url_home = $view->url('login', [
                'action' => 'home-page'
            ]);
            $url_compte = $view->url($route, [
                'action' => 'modif-compte'
            ]);
            $url_localisation = $view->url($route, [
                'action' => 'localisation'
            ]);
            $url_mdp = $view->url($route, [
                'action' => 'mdp-change'
            ]);
            $url_email = $view->url($route, [
                'action' => 'email-change'
            ]);
            $url_msg = $view->url($route, [
                'action' => 'message'
            ]);
            $url_mailchimp = $view->url($route,
                [
                    'action' => 'inscription-liste-de-diffusion'
                ]);

            $a_menu_content = [
                "<li><a href=\"$url_home\">Retour à mon espace</a></li>",
                "<li><a href=\"$url_compte\">Mon compte</a></li>",
                "<li><a href=\"$url_mdp\">Changer mon mot de passe</a></li>",
                "<li><a href=\"$url_email\">Changer mon email</a></li>",
                "<li><a href=\"$url_msg\">Ecrire au service de transport</a></li>",
                "<li><a href=\"$url_localisation\">Mon domicile sur la carte</a></li>",
                "<li><a href=\"$url_mailchimp\">S'inscrire à la liste de diffusion</a></li>"
            ];
            if ($categorieId > 99) {
                unset($a_menu_content[6], $a_menu_content[5]);
            }
            if ($categorieId > 199) {
                unset($a_menu_content[4]);
            }
            $menu_content = implode("\n", $a_menu_content);
            return <<<EOT
<div id="menu-haut" class="menu float-right">
   <ul class="menubar">
       <li class="annee-scolaire">Année scolaire $annee_scolaire</li>
       <li class="onglet">Bienvenue $bienvenue
       <ul>$menu_content</ul>
       </li>
       <li>| <a href="$logout"><i class="fam-cancel"></i>déconnexion</a></li>
   </ul>
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
     * @return \SbmFront\View\Helper\Bienvenue
     */
    public function setAuthService(AuthenticationService $authService)
    {
        $this->authService = $authService;
        return $this;
    }
}