<?php
/**
 * Service fournissant une liste des stations sous la forme d'un tableau de la forme :
 *    stationId => commune - nom_station
 * pour value_options d'un select
 *
 * Les méthodes de la classe permettent de filtrer la table selon quelques critères :
 * - visibles : pour les select destinés aux parents
 * - ouvertes : pour les select destinés au service
 * - surcircuit : pour un serviceId donné
 * 
 * @project sbm
 * @package SbmCommun/Model/Db/Service/Select
 * @filesource StationsForSelect.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 8 mai 2015
 * @version 2015-1
 */
namespace SbmCommun\Model\Db\Service\Select;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Literal;

class StationsForSelect implements FactoryInterface
{

    private $columns;

    private $db;

    private $order;

    private $table_name;

    private $sql;
    
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $this->db = $serviceLocator->get('Sbm\Db\DbLib');
        $this->table_name = $this->db->getCanonicName('stations', 'vue');
        $this->sql = new Sql($this->db->getDbAdapter());
        $libelle = new Literal('concat(commune, " - ", nom)');
        $this->columns = array(
            'stationId',
            'libelle' => $libelle
        );
        $this->order = array(
            'commune',
            'nom'
        );
        return $this;
    }

    public function toutes()
    {
        $where = new Where();
        $select = $this->sql->select($this->table_name);
        $select->columns($this->columns)
        ->order($this->order);
        $statement = $this->sql->prepareStatementForSqlObject($select);
        $rowset = $statement->execute();
        $array = array();
        foreach ($rowset as $row) {
            $array[$row['stationId']] = $row['libelle'];
        }
        return $array;
    }
    
    public function ouvertes()
    {
        $where = new Where();
        $where->literal('ouverte = 1');
        $select = $this->sql->select($this->table_name);
        $select->where($where)
            ->columns($this->columns)
            ->order($this->order);
        $statement = $this->sql->prepareStatementForSqlObject($select);
        $rowset = $statement->execute();
        $array = array();
        foreach ($rowset as $row) {
            $array[$row['stationId']] = $row['libelle'];
        }
        return $array;
    }

    public function visibles()
    {
        $where = new Where();
        $where->literal('visible = 1');
        $select = $this->sql->select($this->table_name);
        $select->where($where)
            ->columns($this->columns)
            ->order($this->order);
        $statement = $this->sql->prepareStatementForSqlObject($select);
        $rowset = $statement->execute();
        $array = array();
        foreach ($rowset as $row) {
            $array[$row['stationId']] = $row['libelle'];
        }
        return $array;
    }

    public function surcircuit($serviceId)
    {
        $where = new Where();
        $where->equalTo('serviceId', $serviceId);
        $select = $this->sql->select();
        $select->from(array('sta' => $this->table_name))
            ->columns($this->columns)
            ->join(array(
            'cir' => $this->db->getCanonicName('circuits', 'table')
        ), 'sta.stationId=cir.stationId', array())
            ->where($where)
            ->order($this->order);        
        $statement = $this->sql->prepareStatementForSqlObject($select);
        $rowset = $statement->execute();
        $array = array();
        foreach ($rowset as $row) {
            $array[$row['stationId']] = $row['libelle'];
        }
        return $array;
    }

}