<?php
/**
 * Controller permettant de créer les cartes etablissements et stations
 *
 * Compatible ZF3
 * 
 * @project sbm
 * @package SbmCartographie/Controller
 * @filesource CarteController.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 18 sept. 2018
 * @version 2018-2.4.5
 */
namespace SbmCartographie\Controller;

use SbmBase\Model\StdLib;
use SbmCartographie\Model\Point;
use SbmCommun\Model\Mvc\Controller\AbstractActionController;
use Zend\Http\PhpEnvironment\Response;
use Zend\View\Model\ViewModel;

class CarteController extends AbstractActionController
{

    public function indexAction()
    {
        return new ViewModel();
    }

    public function etablissementsAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        }
        $args = (array) $prg;
        if (array_key_exists('back', $args)) {
            $this->redirectToOrigin()->setBack($args['back']);
        }
        if (array_key_exists('cancel', $args)) {
            try {
                $this->redirectToOrigin()->back();
            } catch (\SbmCommun\Model\Mvc\Controller\Plugin\Exception $e) {
                return $this->redirect()->toRoute('home');
            }
        }
        $tEtablissements = $this->db_manager->get('Sbm\Db\Vue\Etablissements');
        $ptEtablissements = [];
        foreach ($tEtablissements->fetchAll() as $etablissement) {
            $pt = new Point($etablissement->x, $etablissement->y);
            $pt->setAttribute('etablissement', $etablissement);
            $ptEtablissements[] = $this->projection->xyzVersgRGF93($pt);
        }

        return new ViewModel(
            [
                'ptEtablissements' => $ptEtablissements,
                'config' => StdLib::getParam('etablissements', $this->config_cartes),
                'url_api' => $this->url_api
            ]);
    }

    public function stationsAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        }
        $args = (array) $prg;
        if (array_key_exists('back', $args)) {
            $this->redirectToOrigin()->setBack($args['back']);
        }
        if (array_key_exists('cancel', $args)) {
            try {
                $this->redirectToOrigin()->back();
            } catch (\SbmCommun\Model\Mvc\Controller\Plugin\Exception $e) {
                return $this->redirect()->toRoute('home');
            }
        }
        $tStations = $this->db_manager->get('Sbm\Db\Vue\Stations');
        $ptStations = [];
        foreach ($tStations->fetchAll() as $station) {
            $pt = new Point($station->x, $station->y);
            $pt->setAttribute('station', $station);
            $ptStations[] = $this->projection->xyzVersgRGF93($pt);
        }

        return new ViewModel(
            [
                'ptStations' => $ptStations,
                // on utilise la même configuration (centre, zoom) que pour les établissements
                'config' => StdLib::getParam('etablissements', $this->config_cartes),
                'url_api' => $this->url_api
            ]);
    }
} 