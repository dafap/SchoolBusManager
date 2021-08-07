<?php
/**
 * Controller du portail ouvert aux invités en consultation
 *
 * C'est un aiguilleur qui renvoie sur le controlleur adapté pour le rôle de l'utilisateur
 *
 * @project sbm
 * @package SbmPortail/src/Controller/Service
 * @filesource PortailController.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 7 août 2021
 * @version 2021-2.6.3
 */
namespace SbmPortail\Controller;

use SbmAuthentification\Model\CategoriesInterface;
use SbmBase\Model\Session;
use SbmCommun\Model\Mvc\Controller\AbstractActionController;

/**
 *
 * @property int $categorieId
 *
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 *
 */
// use SbmBase\Model\StdLib;
class PortailController extends AbstractActionController
{
    use \SbmCommun\Model\Traits\DebugTrait;

    /**
     * Aiguilleur
     *
     * @return \Zend\Http\Response
     */
    public function dispatchAction()
    {
        // Le filtre programmé va limiter la vue aux données concernant l'utilisateur
        switch ($this->categorieId) {
            case CategoriesInterface::TRANSPORTEUR_ID:
            case CategoriesInterface::GR_TRANSPORTEURS_ID:
                return $this->redirect()->toRoute('sbmportail/transporteur');
                break;
            case CategoriesInterface::ETABLISSEMENT_ID:
            case CategoriesInterface::GR_ETABLISSEMENTS_ID:
                return $this->redirect()->toRoute('sbmportail/etablissement');
                break;
            case CategoriesInterface::COMMUNE_ID:
            case CategoriesInterface::GR_COMMUNES_ID:
                return $this->redirect()->toRoute('sbmportail/commune');
                break;
            case CategoriesInterface::SECRETARIAT_ID:
            case CategoriesInterface::GESTION_ID:
            case CategoriesInterface::ADMINISTRATEUR_ID:
            case CategoriesInterface::SUPER_ADMINISTRATEUR_ID:
                if (Session::get('commune', false, 'enTantQue') !== false) {
                    $route = 'sbmportail/commune';
                } elseif (Session::get('etablissement', false, 'enTantQue') !== false) {
                    $route = 'sbmportail/etablissement';
                } elseif (Session::get('transporteur', false, 'enTantQue') !== false) {
                    $route = 'sbmportail/transporteur';
                } else {
                    $route = 'sbmportail/organisateur';
                }
                return $this->redirect()->toRoute($route);
                break;
            default:
                return $this->redirect()->toRoute('login', [
                    'action' => 'home-page'
                ]);
                break;
        }
    }
}