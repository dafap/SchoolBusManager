<?php
/**
 * Aide de vue pour afficher le menu du haut (année scolaire, bienvenue, logout)
 *
 * @project sbm
 * @package SbmFront/View/Helper
 * @filesource Bienvenue.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 9 mars 2021
 * @version 2021-2.6.1
 */
namespace SbmFront\View\Helper;

use SbmAuthentification\Authentication\AuthenticationService;
use SbmBase\Model\Session;
use Zend\View\Helper\AbstractHelper;

class Bienvenue extends AbstractHelper
{

    /**
     *
     * @var AuthenticationService
     */
    protected $authService;

    protected $home_route;

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
            $url_home = $view->url('login', [
                'action' => 'home-page'
            ]);
            $url_compte = $view->url($this->getHomeRoute(), [
                'action' => 'modif-compte'
            ]);
            $url_localisation = $view->url($this->getHomeRoute(),
                [
                    'action' => 'localisation'
                ]);
            $url_mdp = $view->url($this->getHomeRoute(), [
                'action' => 'mdp-change'
            ]);
            $url_email = $view->url($this->getHomeRoute(), [
                'action' => 'email-change'
            ]);
            $url_msg = $view->url($this->getHomeRoute(), [
                'action' => 'message'
            ]);
            $url_mailchimp = $view->url($this->getHomeRoute(),
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

    /**
     * Route par défaut pour la catégorie de l'utilisateur courant
     *
     * @return string
     */
    public function getHomeRoute()
    {
        return $this->home_route;
    }

    /**
     * Renvoie la route pour construire les url en fonction de la catégorie
     *
     * @param string $home_route
     * @return \SbmFront\View\Helper\Bienvenue
     */
    public function setHomeRoute(string $home_route)
    {
        $this->home_route = $home_route;
        return $this;
    }
}