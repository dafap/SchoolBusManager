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
 * @date 23 sept. 2020
 * @version 2020-2.6.0
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
        // un appel par GET conduit à filtrer les établissements desservis
        $args = $prg ?: [
            'filtre' => 1
        ];
        if (array_key_exists('back', $args)) {
            $this->redirectToOrigin()->setBack($args['back']);
        }
        if (array_key_exists('cancel', $args)) {
            try {
                $this->redirectToOrigin()->back();
            } catch (\SbmCommun\Model\Mvc\Controller\Plugin\Exception\ExceptionInterface $e) {
                return $this->redirect()->toRoute('home');
            }
        }
        if ((int) StdLib::getParam('filtre', $args, 0) == 1) {
            $filtre = 'desservie = 1' ;
        } else {
            $filtre = null;
        }
        $tEtablissements = $this->db_manager->get('Sbm\Db\Vue\Etablissements');
        $ptEtablissements = [];
        foreach ($tEtablissements->fetchAll($filtre) as $etablissement) {
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
                'url_api' => $this->url_api
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
                return $this->redirect()->toRoute('home');
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
                // on utilise la même configuration (centre, zoom) que pour les
                // établissements
                'config' => StdLib::getParam('station', $this->config_cartes),
                'url_api' => $this->url_api
            ]);
    }
}