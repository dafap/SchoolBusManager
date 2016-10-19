<?php
/**
 * Aide de vue permettant d'afficher les stations d'un élève dans la liste des élèves
 *
 * (à déclarer dans module.config.php)
 * 
 * @project sbm
 * @package SbmGestion/Model/View/Helper
 * @filesource Stations.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 17 août 2016
 * @version 2016-2.2.0
 */
namespace SbmGestion\Model\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;
use SbmBase\Model\Session;

class Stations extends AbstractHelper implements FactoryInterface
{

    protected $db_manager;

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $this->db_manager = $serviceLocator->getServiceLocator()->get('Sbm\DbManager');
        return $this;
    }

    public function getServiceLocator()
    {
        return $this->db_manager;
    }

    /**
     * Renvoie le texte à afficher dans la colonne Services de la liste d'élèves
     *
     * @param int $eleveId            
     * @param int $trajet
     *            1 ou 2
     *            
     * @return string
     */
    public function __invoke($eleveId, $trajet)
    {
        $millesime = Session::get('millesime');
        $where = new Where();
        $where->equalTo('millesime', $millesime)
            ->equalTo('eleveId', $eleveId)
            ->equalTo('trajet', $trajet);
        $sql = new Sql($this->db_manager->getDbAdapter());
        $select = $sql->select()
            ->from(array(
            'aff' => $this->db_manager->getCanonicName('affectations', 'table')
        ))
            ->join(array(
            'sta1' => $this->db_manager->getCanonicName('stations', 'table')
        ), 'sta1.stationId=aff.station1Id', array(
            'station1' => 'nom'
        ))
            ->join(array(
            'sta2' => $this->db_manager->getCanonicName('stations', 'table')
        ), 'sta2.stationId=aff.station2Id', array(
            'station2' => 'nom'
        ), Select::JOIN_LEFT);
        $statement = $sql->prepareStatementForSqlObject($select->where($where));
        $resultset = $statement->execute();
        $content = array();
        foreach ($resultset as $affectation) {        
            $station1Id = $affectation['station1'];
            $content[$station1Id] = $station1Id;
            $station2Id = $affectation['station2'];
            if (!empty($station2Id)) {
                $content[$station2Id] = $station2Id;
            }
        }
        return implode('<br>', $content);
    }
}