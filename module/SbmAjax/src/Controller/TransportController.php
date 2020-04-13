<?php
/**
 * Actions destinées aux réponses à des demandes ajax pour les fichiers annexes :
 * circuits, classes, communes, etablissements, services, stations, transporteurs
 *
 * Le layout est désactivé dans ce module
 * Version pour TRANSDEV ALBERTVILLE
 *
 * @project sbm
 * @package SbmAjax/Controller
 * @filesource TransportController.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 14 avr. 2020
 * @version 2020-2.6.0
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
     * ajax - cocher la case sélection des lots
     *
     * @method GET
     * @return \Zend\Stdlib\ResponseInterface
     */
    public function checkselectionlotAction()
    {
        try {
            $lotId = $this->params('lotId');
            $this->db_manager->get('Sbm\Db\Table\Lots')->setSelection($lotId, 1);
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
     * ajax - décocher la case sélection des lots
     *
     * @method GET
     * @return \Zend\Stdlib\ResponseInterface
     */
    public function uncheckselectionlotAction()
    {
        try {
            $lotId = $this->params('lotId');
            $this->db_manager->get('Sbm\Db\Table\Lots')->setSelection($lotId, 0);
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
            $millesime = Session::get('millesime');
            $objService = $this->db_manager->get('Sbm\Db\Table\Services')->getObjData();
            list ($ligneId, $sens, $moment, $ordre) = array_values(
                $objService->decodeServiceId($this->params('serviceId')));
            $this->db_manager->get('Sbm\Db\Table\Services')->setSelection($millesime,
                $ligneId, $sens, $moment, $ordre, 1);
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
            $millesime = Session::get('millesime');
            $objService = $this->db_manager->get('Sbm\Db\Table\Services')->getObjData();
            list ($ligneId, $sens, $moment, $ordre) = array_values(
                $objService->decodeServiceId($this->params('serviceId')));
            $this->db_manager->get('Sbm\Db\Table\Services')->setSelection($millesime,
                $ligneId, $sens, $moment, $ordre, 0);
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

    /**
     * Ici on passe serviceId, à décoder par les méthodes du trait ServiceTrait
     *
     * @return \Zend\Stdlib\ResponseInterface
     */
    public function getcircuitstationsAction()
    {
        $serviceId = $this->params('serviceId');
        $queryStations = $this->db_manager->get('Sbm\Db\Select\Stations');
        $stations = $queryStations->byServiceId($serviceId);
        return $this->getResponse()->setContent(
            Json::encode(
                [
                    'data' => array_flip($stations), // échange key/value pour conserver
                                                      // le tri
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

/**
 * @ TODO : POUR LA VERSION DE TRANSDEV ALBERTVILLE CETTE METHODE EST INUTILE OU
 * COMPLETEMENT A REVOIR
 *
 * @formatter:off
 * Cette méthode doit recevoir en GET, au choix :<ul>
 * <li>soit le paramètre 'circuitId'</li>
 * <li>soit les paramètres 'serviceId' et 'stationId'</li></ul>
 * En cas de succès, elle renvoie un tableau 4 colonnes x 3 lignes encodé JSON composé
 * de la façon suivante :<ol>
 * <li>première ligne correspond à l'horaire1 défini dans la fiche service</li>
 * <li>deuxième ligne correspond à l'horaire2 défini dans la fiche service</li>
 * <li>troisième ligne correspond à l'horaire3 défini dans la fiche service</li></ol>
 * Chaque ligne est composée de :<ol>
 * <li>horaire décodé de la fiche service</li>
 * <li>horaire de l'aller (matin) ou vide</li>
 * <li>horaire du premier retour ou vide</li>
 * <li>horaire du second retour éventuel ou vide</li></ol>
 * @formatter:on
 *
 * @return \Zend\Stdlib\ResponseInterface
 */
    /*
     * public function tablehorairescircuitAction() { try { $horaires =
     * $this->db_manager->get('Sbm\Horaires'); $circuitId = $this->params('$circuitId',
     * false); if ($circuitId) { $result = $horaires->getTableHoraires($circuitId); return
     * $this->getResponse()->setContent( Json::encode([ 'table' => $result, 'success' => 1
     * ])); } else { $serviceId = $this->params('serviceId', false);
     * $ligneId=$this->params('ligneId', false); $sens = $this->params('sens', 0); $moment
     * = $this->params('moment', 0); $ordre = $this->params('ordre', 0); $stationId =
     * $this->params('stationId', false); if ($ligneId && $sens && $moment && $ordre &&
     * $stationId) { $result = $horaires->getTableHoraires( [ 'serviceId' => $serviceId,
     * 'stationId' => $stationId ]); return $this->getResponse()->setContent(
     * Json::encode([ 'table' => $result, 'success' => 1 ])); } elseif ($serviceId) { //
     * on renvoie un tableau ou seule la première colonne est connue $result =
     * $horaires->getTableHoraires($serviceId); return $this->getResponse()->setContent(
     * Json::encode([ 'table' => $result, 'success' => 1 ])); } else { $msg = 'Impossible
     * de déterminer les horaires car on ne connait pas le service.'; return
     * $this->getResponse()->setContent( Json::encode([ 'cr' => $msg, 'success' => 0 ]));
     * } } } catch (\Exception $e) { return $this->getResponse()->setContent(
     * Json::encode([ 'cr' => $e->getMessage(), 'success' => 0 ])); } }
     */
}