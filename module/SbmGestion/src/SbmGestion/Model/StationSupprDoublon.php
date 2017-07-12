<?php
/**
 * Remplace une station par une autre dans toute la base de données
 *
 * Les tables touchées sont :
 * - sbm_t_circuits
 * - sbm_t_affectations
 * - sbm_t_etablissements_services
 * - sbm_t_stations
 * 
 * @project sbm
 * @package SbmGestion/Model
 * @filesource StationSupprDoublon.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 12 juil. 2017
 * @version 2017-2.3.5
 */
namespace SbmGestion\Model;

use Zend\Crypt\PublicKey\Rsa\PublicKey;
use SbmCommun\Model\Db\Service\DbManager;

class StationSupprDoublon
{

    /**
     * Id de la station à garder
     *
     * @var int
     */
    private $garderId;

    /**
     * Id de la station à supprimer
     *
     * @var int
     */
    private $supprId;

    /**
     * DbManager
     *
     * @var SbmCommun\Model\Db\Service\DbManager
     */
    private $db_manager;

    /**
     *
     * @param SbmCommun\Model\Db\Service\DbManager $db_manager            
     * @param int $stationASupprId            
     * @param int $stationAGarderId            
     */
    public function __construct($db_manager, $stationASupprId, $stationAGarderId)
    {
        $this->db_manager = $db_manager;
        $this->supprId = $stationASupprId;
        $this->garderId = $stationAGarderId;
    }

    public function execute()
    {
        $messages = [];
        $messages[] = sprintf('%d fiches modifiées dans la table des circuits', $this->supprDoublonsDansCircuits());
        $messages[] = sprintf('%d fiches modifiées dans la table des établissements-services', $this->supprDoublonsDansEtablissementsServices());
        $messages[] = sprintf('%d fiches modifiées dans la table des affectations en montée', $this->supprDoublonsDansAffectations1());
        $messages[] = sprintf('%d fiches modifiées dans la table des affectations en descente', $this->supprDoublonsDansAffectations2());
        try {
            $tStations = $this->db_manager->get('Sbm\Db\Table\Stations');
            $tStations->deleteRecord($this->supprId);
            $messages[] = 'La station en double a été supprimée.';
        } catch (\Exception $e) {
            $messages[] = 'Impossible de supprimer cette station car un enregistrement l\'utilise.';
        }
        return $messages;
    }

    private function supprDoublonsDansCircuits()
    {
        $tableGateway = $this->db_manager->get('Sbm\Db\Table\Circuits')->getTableGateway();
        return $tableGateway->update([
            'stationId' => $this->garderId
        ], [
            'stationId' => $this->supprId
        ]);
    }

    private function supprDoublonsDansEtablissementsServices()
    {
        $tableGateway = $this->db_manager->get('Sbm\Db\Table\EtablissementsServices')->getTableGateway();
        return $tableGateway->update([
            'stationId' => $this->garderId
        ], [
            'stationId' => $this->supprId
        ]);
    }

    private function supprDoublonsDansAffectations1()
    {
        $tableGateway = $this->db_manager->get('Sbm\Db\Table\Affectations')->getTableGateway();
        return $tableGateway->update([
            'station1Id' => $this->garderId
        ], [
            'station1Id' => $this->supprId
        ]);
    }

    private function supprDoublonsDansAffectations2()
    {
        $tableGateway = $this->db_manager->get('Sbm\Db\Table\Affectations')->getTableGateway();
        return $tableGateway->update([
            'station2Id' => $this->garderId
        ], [
            'station2Id' => $this->supprId
        ]);
    }
}
 