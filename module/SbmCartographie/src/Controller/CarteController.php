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
 * @date 9 nov. 2020
 * @version 2020-2.6.1
 */
namespace SbmCartographie\Controller;

use SbmBase\Model\Session;
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
        } elseif ($prg) {
            $filtre = $this->params('filtre', 1);
            Session::set('filtre', $filtre, $this->getSessionNamespace());
        } else {
            // un appel par GET (sauf F5) conduit à filtrer les établissements desservis
            $filtre = $this->user ? Session::get('filtre', 1, $this->getSessionNamespace()) : 1;
            $prg = [];
        }
        if (array_key_exists('back', $prg)) {
            $this->redirectToOrigin()->setBack($prg['back']);
        }
        if (array_key_exists('cancel', $prg)) {
            Session::remove('filtre');
            try {
                $this->redirectToOrigin()->back();
            } catch (\SbmCommun\Model\Mvc\Controller\Plugin\Exception\ExceptionInterface $e) {
                return $this->homePage();
            }
        }
        $condition = $filtre == 1 ? 'desservie = 1' : null;
        $tEtablissements = $this->db_manager->get('Sbm\Db\Vue\Etablissements');
        $ptEtablissements = [];
        foreach ($tEtablissements->fetchAll($condition) as $etablissement) {
            $pt = new Point($etablissement->x, $etablissement->y);
            $pt->setAttribute('etablissement', $etablissement);
            $ptEtablissements[] = $this->projection->xyzVersgRGF93($pt);
        }

        return new ViewModel(
            [
                'scheme' => $this->getRequest()
                    ->getUri()
                    ->getScheme(),
                'ptEtablissements' => $ptEtablissements,
                'config' => StdLib::getParam('etablissement', $this->config_cartes),
                'url_api' => $this->url_api,
                'filtre' => $filtre
            ]);
    }

    public function stationsAction()
    {
        $prg = $this->prg();
        if ($prg instanceof Response) {
            return $prg;
        }
        $args = $prg ?: [];
        if (array_key_exists('back', $args)) {
            $this->redirectToOrigin()->setBack($args['back']);
        }
        if (array_key_exists('cancel', $args)) {
            try {
                $this->redirectToOrigin()->back();
            } catch (\SbmCommun\Model\Mvc\Controller\Plugin\Exception\ExceptionInterface $e) {
                return $this->homePage();
            }
        }
        $tStations = $this->db_manager->get('Sbm\Db\Query\Stations');
        $ptStations = [];
        foreach ($tStations->getArrayDesserteStations() as $station) {
            $pt = new Point($station->x, $station->y);
            $pt->setAttribute('station', $station);
            $ptStations[] = $this->projection->xyzVersgRGF93($pt);
        }

        return new ViewModel(
            [
                'scheme' => $this->getRequest()
                    ->getUri()
                    ->getScheme(),
                'ptStations' => $ptStations,
                'config' => StdLib::getParam('station', $this->config_cartes),
                'url_api' => $this->url_api
            ]);
    }

    public function stationsFrameAction()
    {
        $r = $this->stationsAction()->setTerminal(true);
        return $r;
    }
}