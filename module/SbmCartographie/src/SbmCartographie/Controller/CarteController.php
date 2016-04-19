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
 * @date 7 avr. 2016
 * @version 2016-2
 */
namespace SbmCartographie\Controller;

use SbmCommun\Model\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use SbmCommun\Model\StdLib;
use SbmCartographie\Model\Point;
use Zend\Http\PhpEnvironment\Response;

class CarteController extends AbstractActionController
{

    private $_initcarto;

    /**
     * projection utilisée
     *
     * @var \SbmCartographie\ConvertSystemGeodetic\Projection\ProjectionInterface
     */
    private $projection;
    
    /**
     * Db manager
     * 
     * @var \Zend\ServiceManager\ServiceManager
     */
    private $db_manager;

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
                $this->redirect()->toRoute('home');
            }
        }
        $this->init();
        $tEtablissements = $this->db_manager->get('Sbm\Db\Vue\Etablissements');
        $ptEtablissements = array();
        foreach ($tEtablissements->fetchAll() as $etablissement) {
            $pt = new Point($etablissement->x, $etablissement->y);
            $pt->setAttribute('etablissement', $etablissement);
            $ptEtablissements[] = $this->projection->xyzVersgRGF93($pt);
        }
        
        return new ViewModel(array(
            'ptEtablissements' => $ptEtablissements,
            'config' => StdLib::getParam('etablissements', $this->config['config_cartes'])
        ));
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
                $this->redirect()->toRoute('home');
            }
        }
        $this->init();
        $tStations = $this->db_manager->get('Sbm\Db\Vue\Stations');
        $ptStations = array();
        foreach ($tStations->fetchAll() as $station) {
            $pt = new Point($station->x, $station->y);
            $pt->setAttribute('station', $station);
            $ptStations[] = $this->projection->xyzVersgRGF93($pt);
        }
        
        return new ViewModel(array(
            'ptStations' => $ptStations,
            // on utilise la même configuration (centre, zoom) que pour les établissements
            'config' => StdLib::getParam('etablissements', $this->config['config_cartes'])
        ));
    }

    private function init()
    {
        if (! $this->_initcarto) {
            $this->projection = $this->config['projection'];
            $this->db_manager = $this->config['db_manager'];
            $this->_init = true;
        }
    }
} 