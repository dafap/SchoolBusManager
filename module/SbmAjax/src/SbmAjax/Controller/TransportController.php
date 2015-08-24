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
 * @date 7 mai 2015
 * @version 2015-1
 */
namespace SbmAjax\Controller;
 
use Zend\View\Model\ViewModel;
use Zend\Json\Json;
use DafapSession\Model\Session;

class TransportController extends AbstractActionController
{
    const ROUTE = 'sbmajaxtransport';

    /**
     * ajax - cocher la case sélection des circuits
     *
     * @method GET
     * @return dataType json
     */
    public function checkselectioncircuitAction()
    {
        try {
            $circuitId = $this->params('circuitId');
            $this->getServiceLocator()
            ->get('Sbm\Db\Table\Circuits')
            ->setSelection($circuitId, 1);
            return $this->getResponse()->setContent(Json::encode(array(
                'success' => 1
            )));
        } catch (\Exception $e) {
            return $this->getResponse()->setContent(Json::encode(array(
                'cr' => $e->getMessage(),
                'success' => 0
            )));
        }
    }
    /**
     * ajax - décocher la case sélection des circuits
     *
     * @method GET
     * @return dataType json
     */
    public function uncheckselectioncircuitAction()
    {
        try {
            $circuitId = $this->params('circuitId');
            $this->getServiceLocator()
            ->get('Sbm\Db\Table\Circuits')
            ->setSelection($circuitId, 0);
            return $this->getResponse()->setContent(Json::encode(array(
                'success' => 1
            )));
        } catch (\Exception $e) {
            return $this->getResponse()->setContent(Json::encode(array(
                'cr' => $e->getMessage(),
                'success' => 0
            )));
        }
    }
    
    /**
     * ajax - cocher la case sélection des classes
     *
     * @method GET
     * @return dataType json
     */
    public function checkselectionclasseAction()
    {
        try {
            $classeId = $this->params('classeId');
            $this->getServiceLocator()
            ->get('Sbm\Db\Table\classes')
            ->setSelection($classeId, 1);
            return $this->getResponse()->setContent(Json::encode(array(
                'success' => 1
            )));
        } catch (\Exception $e) {
            return $this->getResponse()->setContent(Json::encode(array(
                'cr' => $e->getMessage(),
                'success' => 0
            )));
        }
    }
    /**
     * ajax - décocher la case sélection des classes
     *
     * @method GET
     * @return dataType json
     */
    public function uncheckselectionclasseAction()
    {
        try {
            $classeId = $this->params('classeId');
            $this->getServiceLocator()
            ->get('Sbm\Db\Table\classes')
            ->setSelection($classeId, 0);
            return $this->getResponse()->setContent(Json::encode(array(
                'success' => 1
            )));
        } catch (\Exception $e) {
            return $this->getResponse()->setContent(Json::encode(array(
                'cr' => $e->getMessage(),
                'success' => 0
            )));
        }
    }
    
    /**
     * ajax - cocher la case sélection des communes
     *
     * @method GET
     * @return dataType json
     */
    public function checkselectioncommuneAction()
    {
        try {
            $communeId = $this->params('communeId');
            $this->getServiceLocator()
            ->get('Sbm\Db\Table\Communes')
            ->setSelection($communeId, 1);
            return $this->getResponse()->setContent(Json::encode(array(
                'success' => 1
            )));
        } catch (\Exception $e) {
            return $this->getResponse()->setContent(Json::encode(array(
                'cr' => $e->getMessage(),
                'success' => 0
            )));
        }
    }
    /**
     * ajax - décocher la case sélection des communes
     *
     * @method GET
     * @return dataType json
     */
    public function uncheckselectioncommuneAction()
    {
        try {
            $communeId = $this->params('communeId');
            $this->getServiceLocator()
            ->get('Sbm\Db\Table\Communes')
            ->setSelection($communeId, 0);
            return $this->getResponse()->setContent(Json::encode(array(
                'success' => 1
            )));
        } catch (\Exception $e) {
            return $this->getResponse()->setContent(Json::encode(array(
                'cr' => $e->getMessage(),
                'success' => 0
            )));
        }
    }
    
    /**
     * ajax - cocher la case sélection des établissements
     *
     * @method GET
     * @return dataType json
     */
    public function checkselectionetablissementAction()
    {
        try {
            $etablissementId = $this->params('etablissementId');
            $this->getServiceLocator()
            ->get('Sbm\Db\Table\Etablissements')
            ->setSelection($etablissementId, 1);
            return $this->getResponse()->setContent(Json::encode(array(
                'success' => 1
            )));
        } catch (\Exception $e) {
            return $this->getResponse()->setContent(Json::encode(array(
                'cr' => $e->getMessage(),
                'success' => 0
            )));
        }
    }
    /**
     * ajax - décocher la case sélection des établissements
     *
     * @method GET
     * @return dataType json
     */
    public function uncheckselectionetablissementAction()
    {
        try {
            $etablissementId = $this->params('etablissementId');
            $this->getServiceLocator()
            ->get('Sbm\Db\Table\Etablissements')
            ->setSelection($etablissementId, 0);
            return $this->getResponse()->setContent(Json::encode(array(
                'success' => 1
            )));
        } catch (\Exception $e) {
            return $this->getResponse()->setContent(Json::encode(array(
                'cr' => $e->getMessage(),
                'success' => 0
            )));
        }
    }
    
    /**
     * ajax - cocher la case visible des établissements
     *
     * @method GET
     * @return dataType json
     */
    public function checkvisibleetablissementAction()
    {
        try {
            $etablissementId = $this->params('etablissementId');
            $this->getServiceLocator()
            ->get('Sbm\Db\Table\Etablissements')
            ->setVisible($etablissementId, 1);
            return $this->getResponse()->setContent(Json::encode(array(
                'success' => 1
            )));
        } catch (\Exception $e) {
            return $this->getResponse()->setContent(Json::encode(array(
                'cr' => $e->getMessage(),
                'success' => 0
            )));
        }
    }
    /**
     * ajax - décocher la case visible des établissements
     *
     * @method GET
     * @return dataType json
     */
    public function uncheckvisibleetablissementAction()
    {
        try {
            $etablissementId = $this->params('etablissementId');
            $this->getServiceLocator()
            ->get('Sbm\Db\Table\Etablissements')
            ->setVisible($etablissementId, 0);
            return $this->getResponse()->setContent(Json::encode(array(
                'success' => 1
            )));
        } catch (\Exception $e) {
            return $this->getResponse()->setContent(Json::encode(array(
                'cr' => $e->getMessage(),
                'success' => 0
            )));
        }
    }

    /**
     * ajax - cocher la case desservie des établissements
     *
     * @method GET
     * @return dataType json
     */
    public function checkdesservietablissementAction()
    {
        try {
            $etablissementId = $this->params('etablissementId');
            $this->getServiceLocator()
            ->get('Sbm\Db\Table\Etablissements')
            ->setDesservie($etablissementId, 1);
            return $this->getResponse()->setContent(Json::encode(array(
                'success' => 1
            )));
        } catch (\Exception $e) {
            return $this->getResponse()->setContent(Json::encode(array(
                'cr' => $e->getMessage(),
                'success' => 0
            )));
        }
    }
    /**
     * ajax - décocher la case desservie des établissements
     *
     * @method GET
     * @return dataType json
     */
    public function uncheckdesservietablissementAction()
    {
        try {
            $etablissementId = $this->params('etablissementId');
            $this->getServiceLocator()
            ->get('Sbm\Db\Table\Etablissements')
            ->setDesservie($etablissementId, 0);
            return $this->getResponse()->setContent(Json::encode(array(
                'success' => 1
            )));
        } catch (\Exception $e) {
            return $this->getResponse()->setContent(Json::encode(array(
                'cr' => $e->getMessage(),
                'success' => 0
            )));
        }
    }
        
    /**
     * ajax - cocher la case sélection des services
     *
     * @method GET
     * @return dataType json
     */
    public function checkselectionserviceAction()
    {
        try {
            $serviceId = $this->params('serviceId');
            $this->getServiceLocator()
            ->get('Sbm\Db\Table\Services')
            ->setSelection($serviceId, 1);
            return $this->getResponse()->setContent(Json::encode(array(
                'success' => 1
            )));
        } catch (\Exception $e) {
            return $this->getResponse()->setContent(Json::encode(array(
                'cr' => $e->getMessage(),
                'success' => 0
            )));
        }
    }
    /**
     * ajax - décocher la case sélection des services
     *
     * @method GET
     * @return dataType json
     */
    public function uncheckselectionserviceAction()
    {
        try {
            $serviceId = $this->params('serviceId');
            $this->getServiceLocator()
            ->get('Sbm\Db\Table\Services')
            ->setSelection($serviceId, 0);
            return $this->getResponse()->setContent(Json::encode(array(
                'success' => 1
            )));
        } catch (\Exception $e) {
            return $this->getResponse()->setContent(Json::encode(array(
                'cr' => $e->getMessage(),
                'success' => 0
            )));
        }
    }
    
    /**
     * ajax - cocher la case sélection ses stations
     *
     * @method GET
     * @return dataType json
     */
    public function checkselectionstationAction()
    {
        try {
            $stationId = $this->params('stationId');
            $this->getServiceLocator()
            ->get('Sbm\Db\Table\Stations')
            ->setSelection($stationId, 1);
            return $this->getResponse()->setContent(Json::encode(array(
                'success' => 1
            )));
        } catch (\Exception $e) {
            return $this->getResponse()->setContent(Json::encode(array(
                'cr' => $e->getMessage(),
                'success' => 0
            )));
        }
    }
    /**
     * ajax - décocher la case sélection des stations
     *
     * @method GET
     * @return dataType json
     */
    public function uncheckselectionstationAction()
    {
        try {
            $stationId = $this->params('stationId');
            $this->getServiceLocator()
            ->get('Sbm\Db\Table\Stations')
            ->setSelection($stationId, 0);
            return $this->getResponse()->setContent(Json::encode(array(
                'success' => 1
            )));
        } catch (\Exception $e) {
            return $this->getResponse()->setContent(Json::encode(array(
                'cr' => $e->getMessage(),
                'success' => 0
            )));
        }
    }
    public function getcircuitstationsAction()
    {
        $millesime = Session::get('millesime');
        $queryStations = $this->getServiceLocator()->get('Sbm\Db\Select\Stations');
        $stations = $queryStations->surcircuit($this->params('serviceId'), $millesime);
        return $this->getResponse()->setContent(Json::encode(array(
            'data' => array_flip($stations), // échange key/value pour conserver le tri
            'success' => 1
        )));
    }
    
    /**
     * ajax - cocher la case sélection des transporteurs
     *
     * @method GET
     * @return dataType json
     */
    public function checkselectiontransporteurAction()
    {
        try {
            $transporteurId = $this->params('transporteurId');
            $this->getServiceLocator()
            ->get('Sbm\Db\Table\Transporteurs')
            ->setSelection($transporteurId, 1);
            return $this->getResponse()->setContent(Json::encode(array(
                'success' => 1
            )));
        } catch (\Exception $e) {
            return $this->getResponse()->setContent(Json::encode(array(
                'cr' => $e->getMessage(),
                'success' => 0
            )));
        }
    }
    /**
     * ajax - décocher la case sélection des transporteurs
     *
     * @method GET
     * @return dataType json
     */
    public function uncheckselectiontransporteurAction()
    {
        try {
            $transporteurId = $this->params('transporteurId');
            $this->getServiceLocator()
            ->get('Sbm\Db\Table\Transporteurs')
            ->setSelection($transporteurId, 0);
            return $this->getResponse()->setContent(Json::encode(array(
                'success' => 1
            )));
        } catch (\Exception $e) {
            return $this->getResponse()->setContent(Json::encode(array(
                'cr' => $e->getMessage(),
                'success' => 0
            )));
        }
    }
}