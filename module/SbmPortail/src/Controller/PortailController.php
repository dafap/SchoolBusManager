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
 * @date 24 sept. 2020
 * @version 2020-2.6.1
 */
namespace SbmPortail\Controller;

use SbmAuthentification\Model\CategoriesInterface;
use SbmCommun\Model\Mvc\Controller\AbstractActionController;

class PortailController extends AbstractActionController
{
    use \SbmCommun\Model\Traits\DebugTrait;

    /**
     * Indique si le portail des transporteur cache les préinscrits ou pas.
     *
     * @var bool
     */
    private $transporteur_sanspreinscrits = true;

    /**
     * Indique si le portail des établissements cache les préinscrits ou pas.
     *
     * @var bool
     */
    private $etablissement_sanspreinscrits = true;

    /**
     * Indique si le portail des communes cache les préinscrits ou pas.
     *
     * @var boolean
     */
    private $commune_sanspreinscrits = false;

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
                return $this->redirect()->toRoute('sbmportail/organisateur');
                break;
            default:
                return $this->redirect()->toRoute('login', [
                    'action' => 'home-page'
                ]);
                break;
        }
    }
}