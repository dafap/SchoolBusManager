<?php
/**
 * Actions destinées aux réponses à des demandes ajax pour les fichiers annexes : 
 * circuits, classes, communes, etablissements, services, stations, transporteurs
 *
 * Le layout est désactivé dans ce module
 * 
 * @project sbm
 * @package SbmAjax/Controller
 * @filesource TransportController.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 9 sept. 2018
 * @version 2019-2.5.0
 */
namespace SbmAjax\Controller;

use SbmBase\Model\Session;
use Zend\Json\Json;

class TransportController extends AbstractActionController
{

    const ROUTE = 'sbmajaxtransport';

    /**
     * ajax - cocher la case sélection des circuits
     *
     * @method GET
     * @return \Zend\Stdlib\ResponseInterface
     */
    public function checkselectioncircuitAction()
    {
        try {
            $circuitId = $this->params('circuitId');
            $this->db_manager->get('Sbm\Db\Table\Circuits')->setSelection($circuitId, 1);
            return $this->getResponse()->setContent(Json::encode([
                'success' => 1
            ]));
        } catch (\Exception $e) {
            return $this->getResponse()->setContent(
                Json::encode([
                    'cr' => $e->getMessage(),
                    'success' => 0
                ]));
        }
    }

    /**
     * ajax - décocher la case sélection des circuits
     *
     * @method GET
     * @return \Zend\Stdlib\ResponseInterface
     */
    public function uncheckselectioncircuitAction()
    {
        try {
            $circuitId = $this->params('circuitId');
            $this->db_manager->get('Sbm\Db\Table\Circuits')->setSelection($circuitId, 0);
            return $this->getResponse()->setContent(Json::encode([
                'success' => 1
            ]));
        } catch (\Exception $e) {
            return $this->getResponse()->setContent(
                Json::encode([
                    'cr' => $e->getMessage(),
                    'success' => 0
                ]));
        }
    }

    /**
     * ajax - cocher la case sélection des classes
     *
     * @method GET
     * @return \Zend\Stdlib\ResponseInterface
     */
    public function checkselectionclasseAction()
    {
        try {
            $classeId = $this->params('classeId');
            $this->db_manager->get('Sbm\Db\Table\classes')->setSelection($classeId, 1);
            return $this->getResponse()->setContent(Json::encode([
                'success' => 1
            ]));
        } catch (\Exception $e) {
            return $this->getResponse()->setContent(
                Json::encode([
                    'cr' => $e->getMessage(),
                    'success' => 0
                ]));
        }
    }

    /**
     * ajax - décocher la case sélection des classes
     *
     * @method GET
     * @return \Zend\Stdlib\ResponseInterface
     */
    public function uncheckselectionclasseAction()
    {
        try {
            $classeId = $this->params('classeId');
            $this->db_manager->get('Sbm\Db\Table\classes')->setSelection($classeId, 0);
            return $this->getResponse()->setContent(Json::encode([
                'success' => 1
            ]));
        } catch (\Exception $e) {
            return $this->getResponse()->setContent(
                Json::encode([
                    'cr' => $e->getMessage(),
                    'success' => 0
                ]));
        }
    }

    /**
     * ajax - cocher la case sélection des communes
     *
     * @method GET
     * @return \Zend\Stdlib\ResponseInterface
     */
    public function checkselectioncommuneAction()
    {
        try {
            $communeId = $this->params('communeId');
            $this->db_manager->get('Sbm\Db\Table\Communes')->setSelection($communeId, 1);
            return $this->getResponse()->setContent(Json::encode([
                'success' => 1
            ]));
        } catch (\Exception $e) {
            return $this->getResponse()->setContent(
                Json::encode([
                    'cr' => $e->getMessage(),
                    'success' => 0
                ]));
        }
    }

    /**
     * ajax - décocher la case sélection des communes
     *
     * @method GET
     * @return \Zend\Stdlib\ResponseInterface
     */
    public function uncheckselectioncommuneAction()
    {
        try {
            $communeId = $this->params('communeId');
            $this->db_manager->get('Sbm\Db\Table\Communes')->setSelection($communeId, 0);
            return $this->getResponse()->setContent(Json::encode([
                'success' => 1
            ]));
        } catch (\Exception $e) {
            return $this->getResponse()->setContent(
                Json::encode([
                    'cr' => $e->getMessage(),
                    'success' => 0
                ]));
        }
    }

    /**
     * ajax - cocher la case sélection des établissements
     *
     * @method GET
     * @return \Zend\Stdlib\ResponseInterface
     */
    public function checkselectionetablissementAction()
    {
        try {
            $etablissementId = $this->params('etablissementId');
            $this->db_manager->get('Sbm\Db\Table\Etablissements')->setSelection(
                $etablissementId, 1);
            return $this->getResponse()->setContent(Json::encode([
                'success' => 1
            ]));
        } catch (\Exception $e) {
            return $this->getResponse()->setContent(
                Json::encode([
                    'cr' => $e->getMessage(),
                    'success' => 0
                ]));
        }
    }

    /**
     * ajax - décocher la case sélection des établissements
     *
     * @method GET
     * @return \Zend\Stdlib\ResponseInterface
     */
    public function uncheckselectionetablissementAction()
    {
        try {
            $etablissementId = $this->params('etablissementId');
            $this->db_manager->get('Sbm\Db\Table\Etablissements')->setSelection(
                $etablissementId, 0);
            return $this->getResponse()->setContent(Json::encode([
                'success' => 1
            ]));
        } catch (\Exception $e) {
            return $this->getResponse()->setContent(
                Json::encode([
                    'cr' => $e->getMessage(),
                    'success' => 0
                ]));
        }
    }

    /**
     * ajax - cocher la case visible des établissements
     *
     * @method GET
     * @return \Zend\Stdlib\ResponseInterface
     */
    public function checkvisibleetablissementAction()
    {
        try {
            $etablissementId = $this->params('etablissementId');
            $this->db_manager->get('Sbm\Db\Table\Etablissements')->setVisible(
                $etablissementId, 1);
            return $this->getResponse()->setContent(Json::encode([
                'success' => 1
            ]));
        } catch (\Exception $e) {
            return $this->getResponse()->setContent(
                Json::encode([
                    'cr' => $e->getMessage(),
                    'success' => 0
                ]));
        }
    }

    /**
     * ajax - décocher la case visible des établissements
     *
     * @method GET
     * @return \Zend\Stdlib\ResponseInterface
     */
    public function uncheckvisibleetablissementAction()
    {
        try {
            $etablissementId = $this->params('etablissementId');
            $this->db_manager->get('Sbm\Db\Table\Etablissements')->setVisible(
                $etablissementId, 0);
            return $this->getResponse()->setContent(Json::encode([
                'success' => 1
            ]));
        } catch (\Exception $e) {
            return $this->getResponse()->setContent(
                Json::encode([
                    'cr' => $e->getMessage(),
                    'success' => 0
                ]));
        }
    }

    /**
     * ajax - cocher la case desservie des établissements
     *
     * @method GET
     * @return \Zend\Stdlib\ResponseInterface
     */
    public function checkdesservietablissementAction()
    {
        try {
            $etablissementId = $this->params('etablissementId');
            $this->db_manager->get('Sbm\Db\Table\Etablissements')->setDesservie(
                $etablissementId, 1);
            return $this->getResponse()->setContent(Json::encode([
                'success' => 1
            ]));
        } catch (\Exception $e) {
            return $this->getResponse()->setContent(
                Json::encode([
                    'cr' => $e->getMessage(),
                    'success' => 0
                ]));
        }
    }

    /**
     * ajax - décocher la case desservie des établissements
     *
     * @method GET
     * @return \Zend\Stdlib\ResponseInterface
     */
    public function uncheckdesservietablissementAction()
    {
        try {
            $etablissementId = $this->params('etablissementId');
            $this->db_manager->get('Sbm\Db\Table\Etablissements')->setDesservie(
                $etablissementId, 0);
            return $this->getResponse()->setContent(Json::encode([
                'success' => 1
            ]));
        } catch (\Exception $e) {
            return $this->getResponse()->setContent(
                Json::encode([
                    'cr' => $e->getMessage(),
                    'success' => 0
                ]));
        }
    }

    /**
     * ajax - cocher la case sélection des services
     *
     * @method GET
     * @return \Zend\Stdlib\ResponseInterface
     */
    public function checkselectionserviceAction()
    {
        try {
            $serviceId = $this->params('serviceId');
            $this->db_manager->get('Sbm\Db\Table\Services')->setSelection($serviceId, 1);
            return $this->getResponse()->setContent(Json::encode([
                'success' => 1
            ]));
        } catch (\Exception $e) {
            return $this->getResponse()->setContent(
                Json::encode([
                    'cr' => $e->getMessage(),
                    'success' => 0
                ]));
        }
    }

    /**
     * ajax - décocher la case sélection des services
     *
     * @method GET
     * @return \Zend\Stdlib\ResponseInterface
     */
    public function uncheckselectionserviceAction()
    {
        try {
            $serviceId = $this->params('serviceId');
            $this->db_manager->get('Sbm\Db\Table\Services')->setSelection($serviceId, 0);
            return $this->getResponse()->setContent(Json::encode([
                'success' => 1
            ]));
        } catch (\Exception $e) {
            return $this->getResponse()->setContent(
                Json::encode([
                    'cr' => $e->getMessage(),
                    'success' => 0
                ]));
        }
    }

    /**
     * ajax - cocher la case sélection ses stations
     *
     * @method GET
     * @return \Zend\Stdlib\ResponseInterface
     */
    public function checkselectionstationAction()
    {
        try {
            $stationId = $this->params('stationId');
            $this->db_manager->get('Sbm\Db\Table\Stations')->setSelection($stationId, 1);
            return $this->getResponse()->setContent(Json::encode([
                'success' => 1
            ]));
        } catch (\Exception $e) {
            return $this->getResponse()->setContent(
                Json::encode([
                    'cr' => $e->getMessage(),
                    'success' => 0
                ]));
        }
    }

    /**
     * ajax - décocher la case sélection des stations
     *
     * @method GET
     * @return \Zend\Stdlib\ResponseInterface
     */
    public function uncheckselectionstationAction()
    {
        try {
            $stationId = $this->params('stationId');
            $this->db_manager->get('Sbm\Db\Table\Stations')->setSelection($stationId, 0);
            return $this->getResponse()->setContent(Json::encode([
                'success' => 1
            ]));
        } catch (\Exception $e) {
            return $this->getResponse()->setContent(
                Json::encode([
                    'cr' => $e->getMessage(),
                    'success' => 0
                ]));
        }
    }

    public function getcircuitstationsAction()
    {
        $millesime = Session::get('millesime');
        $queryStations = $this->db_manager->get('Sbm\Db\Select\Stations');
        $stations = $queryStations->surcircuit($this->params('serviceId'), $millesime);
        return $this->getResponse()->setContent(
            Json::encode(
                [
                    'data' => array_flip($stations), // échange key/value pour conserver le tri
                    'success' => 1
                ]));
    }

    /**
     * ajax - cocher la case sélection des transporteurs
     *
     * @method GET
     * @return \Zend\Stdlib\ResponseInterface
     */
    public function checkselectiontransporteurAction()
    {
        try {
            $transporteurId = $this->params('transporteurId');
            $this->db_manager->get('Sbm\Db\Table\Transporteurs')->setSelection(
                $transporteurId, 1);
            return $this->getResponse()->setContent(Json::encode([
                'success' => 1
            ]));
        } catch (\Exception $e) {
            return $this->getResponse()->setContent(
                Json::encode([
                    'cr' => $e->getMessage(),
                    'success' => 0
                ]));
        }
    }

    /**
     * ajax - décocher la case sélection des transporteurs
     *
     * @method GET
     * @return \Zend\Stdlib\ResponseInterface
     */
    public function uncheckselectiontransporteurAction()
    {
        try {
            $transporteurId = $this->params('transporteurId');
            $this->db_manager->get('Sbm\Db\Table\Transporteurs')->setSelection(
                $transporteurId, 0);
            return $this->getResponse()->setContent(Json::encode([
                'success' => 1
            ]));
        } catch (\Exception $e) {
            return $this->getResponse()->setContent(
                Json::encode([
                    'cr' => $e->getMessage(),
                    'success' => 0
                ]));
        }
    }
}