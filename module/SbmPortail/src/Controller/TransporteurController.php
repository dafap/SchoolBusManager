<?php
/**
 * Controlleur du portail des transporteurs
 *
 * @project sbm
 * @package SbmPortail/src/Controller/Service
 * @filesource TransporteurController.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 14 nov. 2020
 * @version 2020-2.6.1
 */
namespace SbmPortail\Controller;

use SbmAuthentification\Model\CategoriesInterface;
use SbmBase\Model\Session;
use SbmBase\Model\StdLib;
use SbmCommun\Model\Mvc\Controller\AbstractActionController;
use SbmGestion\Model\Db\Filtre\Eleve\Filtre as FiltreEleve;
use Zend\Db\Sql\Where;
use Zend\Http\PhpEnvironment\Response;
use Zend\View\Model\ViewModel;
use Zend\Mvc\Controller\Plugin\FlashMessenger;

class TransporteurController extends AbstractActionController
{
    use \SbmCommun\Model\Traits\DebugTrait;

    private $sansimpayes = true;

    /**
     * Page d'accueil du portail des établissements
     *
     * @return \Zend\Http\Response|\Zend\View\Model\ViewModel
     */
    public function IndexAction()
    {
        ;
    }

    public function carteEtablissementsAction()
    {
        ;
    }

    public function carteStationsAction()
    {
        ;
    }

    public function lignesAction()
    {
        ;
    }



    public function lignesPdfAction()
    {
        ;
    }

    public function lignesDownloadAction()
    {
        ;
    }

    public function servicesAction()
    {

        $millesime = Session::get('millesime');
        $as = $millesime . '-' . ($millesime + 1);
        return new ViewModel(
            [
                'paginator' => null,
                'count_per_page' => $this->getPaginatorCountPerPage('nb_services', 15),
                'page' => $this->params('page', 1),
                'as' => $as,
                'ligneId' => null,
                'effectifServices' => null,
                'criteres_form' => null
            ]);
    }

    public function servicesPdfAction()
    {
        ;
    }

    public function servicesDownloadAction()
    {
        ;
    }

    public function serviceGroupAction()
    {
        ;
    }

    public function serviceGroupPdfAction()
    {
        ;
    }

    public function circuitAction()
    {
        ;
    }

    public function circuitPdfAction()
    {
        ;
    }

    public function circuitDownloadAction()
    {
        ;
    }

    public function circuitGroupAction()
    {
        ;
    }

    public function circuitGroupPdfAction()
    {
        ;
    }

    public function elevesAction()
    {
        ;
    }

    public function elevesPdfAction()
    {
        ;
    }

    public function elevesDownloadAction()
    {
        ;
    }
}