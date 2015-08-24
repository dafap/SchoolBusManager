<?php
/**
 * Renvoie un tableau d'effectifs 
 *
 * @project sbm
 * @package SbmGestion/Model/Db/Service/Eleve
 * @filesource Effectif.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 18 mars 2015
 * @version 2015-1
 */
namespace SbmGestion\Model\Db\Service\Eleve;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Expression;
use Zend\Db\ResultSet\ResultSet;
use DafapSession\Model\Session;
use Zend\Db\Sql\Having;

class Effectif implements FactoryInterface
{

    private $millesime;

    private $sql;

    private $tableName = array();

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $this->millesime = Session::get('millesime');
        $db = $serviceLocator->get('Sbm\Db\DbLib');
        $this->tableName['affectations'] = $db->getCanonicName('affectations', 'table');
        $this->tableName['circuits'] = $db->getCanonicName('circuits', 'table');
        $this->tableName['communes'] = $db->getCanonicName('communes', 'table');
        $this->tableName['eleves'] = $db->getCanonicName('eleves', 'table');
        $this->tableName['responsables'] = $db->getCanonicName('responsables', 'table');
        $this->tableName['scolarites'] = $db->getCanonicName('scolarites', 'table');
        $this->tableName['services'] = $db->getCanonicName('services', 'table');
        $this->sql = new Sql($db->getDbAdapter());
        return $this;
    }

    public function byCircuit()
    {
        // SELECT circuitId, count(*) FROM `sbm_t_circuits` c JOIN `sbm_t_affectations` a ON c.serviceId=a.service1Id AND c.stationId=a.station1Id WHERE millesime=2014 GROUP BY circuitId
        $result = array();
        $rowset = $this->requeteCir(1, 'circuitId', array(), 'circuitId');
        foreach ($rowset as $row) {
            $result[$row['column']]['r1'] = $row['effectif'];
        }
        $rowset = $this->requeteCir(2, 'circuitId', array(), 'circuitId');
        foreach ($rowset as $row) {
            $result[$row['column']]['r2'] = $row['effectif'];
        }
        // total
        foreach ($result as $key => &$value) {
            $value['total'] = array_sum($value);
        }
        return $result;
    }

    /**
     * Compte les élèves dans les communes des responsables (1, 2) et de résidence (3)
     *
     * @return array
     */
    public function byCommune()
    {
        // SELECT r.communeId AS `column`, count(*) AS `effectif` FROM `sbm_t_eleves` e JOIN sbm_t_scolarites s ON e.eleveId=s.eleveId JOIN sbm_t_responsables r ON e.responsable1Id=r.responsableId WHERE millesime=2014 GROUP BY r.communeId
        $result = array();
        $rowset = $this->requeteCom(1, 'communeId', array(), 'r.communeId');
        foreach ($rowset as $row) {
            $result[$row['column']]['r1'] = $row['effectif'];
        }
        $rowset = $this->requeteCom(2, 'communeId', array(), 'r.communeId');
        foreach ($rowset as $row) {
            $result[$row['column']]['r2'] = $row['effectif'];
        }
        // SELECT communeId AS `column`, count(*) AS `effectif` FROM sbm_t_scolarites WHERE millesime=2014 GROUP BY communeId HAVING communeId IS NOT NULL
        $rowset = $this->requeteCom(3, 'communeId', array(), 'communeId');
        foreach ($rowset as $row) {
            $result[$row['column']]['r2'] = $row['effectif'];
        }
        // total
        foreach ($result as $key => &$value) {
            $value['total'] = array_sum($value);
        }
        return $result;
    }

    public function byClasse()
    {
        // SELECT classeId, count(*) FROM sbm_t_scolarites GROUP BY classeId
        $result = array();
        $rowset = $this->requeteCl('classeId', array(), array(
            'classeId'
        ));
        foreach ($rowset as $row) {
            $result[$row['classeId']] = $row['effectif'];
        }
        return $result;
    }

    /**
     * Tableau d'effectifs des établissements
     *
     * @param bool $transportes
     *            si true alors ne compte que les élèves ayant au moins un transport
     * @return array
     */
    public function byEtablissement($transportes = false)
    {
        // SELECT etablissementId, count(*) FROM sbm_scolarites GROUP BY etablissementId
        $result = array();
        $rowset = $this->requeteCl('etablissementId', array(), array(
            'etablissementId'
        ), $transportes);
        foreach ($rowset as $row) {
            $result[$row['etablissementId']] = $row['effectif'];
        }
        return $result;
    }

    public function byEtablissementGivenService($serviceId)
    {
        $result = array();
        $rowset = $this->requeteWith('service1Id', array(
            'service1Id' => $serviceId
        ), 'etablissementId');
        foreach ($rowset as $row) {
            $result[$row['etablissementId']]['r1'] = $row['effectif'];
        }
        
        $rowset = $this->requeteWith2('service', array(
            'a.service2Id' => $serviceId
        ), 'etablissementId');
        foreach ($rowset as $row) {
            $result[$row['etablissementId']]['r2'] = $row['effectif'];
        }
        
        // total
        foreach ($result as $key => &$value) {
            $value['total'] = array_sum($value);
        }
        return $result;
    }

    public function byServiceGivenEtablissement($etablissementId)
    {
        $result = array();
        $rowset = $this->requeteWith('service1Id', array(
            'etablissementId' => $etablissementId
        ), 'service1Id');
        foreach ($rowset as $row) {
            if ($row['service1Id'] == '')
                continue;
            $result[$row['service1Id']]['r1'] = $row['effectif'];
        }
        
        $rowset = $this->requeteWith2('service', array(
            'etablissementId' => $etablissementId
        ), 'service2Id');
        foreach ($rowset as $row) {
            if ($row['service2Id'] == '')
                continue;
            $result[$row['service2Id']]['r2'] = $row['effectif'];
        }
        
        // total
        foreach ($result as $key => &$value) {
            $value['total'] = array_sum($value);
        }
        return $result;
    }

    public function byServiceGivenStation($stationId)
    {
        $result = array();
        $rowset = $this->requeteWith('service1Id', array(
            'station1Id' => $stationId
        ), 'service1Id');
        foreach ($rowset as $row) {
            if ($row['service1Id'] == '')
                continue;
            $result[$row['service1Id']]['r1'] = $row['effectif'];
        }
        
        $rowset = $this->requeteWith2('service', array(
            'a.station2Id' => $stationId
        ), 'service2Id');
        foreach ($rowset as $row) {
            if ($row['service2Id'] == '')
                continue;
            $result[$row['service2Id']]['r2'] = $row['effectif'];
        }
        
        // total
        foreach ($result as $key => &$value) {
            $value['total'] = array_sum($value);
        }
        return $result;
    }

    public function byService()
    {
        // SELECT service1Id serviceId, count(*) FROM `sbm_t_affectations` WHERE millesime=2014 GROUP BY service1Id
        $result = array();
        $rowset = $this->requeteSrv('service1Id', array(), 'service1Id');
        foreach ($rowset as $row) {
            $result[$row['column']]['r1'] = $row['effectif'];
        }
        $rowset = $this->requeteSrv2('service', array(), 'service2Id');
        foreach ($rowset as $row) {
            $result[$row['column']]['r2'] = $row['effectif'];
        }
        // total
        foreach ($result as $key => &$value) {
            $value['total'] = array_sum($value);
        }
        return $result;
    }

    public function byStation()
    {
        // SELECT station1Id stationId, count(*) FROM `sbm_t_affectations` WHERE millesime=2014 GROUP BY station1Id
        $result = array();
        $rowset = $this->requeteSrv('station1Id', array(), 'station1Id');
        foreach ($rowset as $row) {
            $result[$row['column']]['r1'] = $row['effectif'];
        }
        $rowset = $this->requeteSrv2('station', array(), 'station2Id');
        foreach ($rowset as $row) {
            $result[$row['column']]['r2'] = $row['effectif'];
        }
        // total
        foreach ($result as $key => &$value) {
            $value['total'] = array_sum($value);
        }
        return $result;
    }

    public function byTarif()
    {
        // SELECT tarifId, count(*) FROM sbm_t_scolarites GROUP BY tarifId
        $result = array();
        $rowset = $this->requeteCl('tarifId', array(), array(
            'tarifId'
        ));
        foreach ($rowset as $row) {
            $result[$row['tarifId']] = $row['effectif'];
        }
        return $result;
    }

    public function transporteurByService($transporteurId)
    {
        // SELECT service1Id serviceId, count(*) FROM `sbm_t_affectations` a JOIN `sbm_t_services` s ON a.service1Id=s.serviceId WHERE millesime=2014 AND transporteurId=1 GROUP BY service1Id
        $result = array();
        $rowset = $this->requeteTr1('serviceId', array(
            'transporteurId' => $transporteurId
        ), 'service1Id');
        foreach ($rowset as $row) {
            $result[$row['serviceId']]['r1'] = $row['effectif'];
        }
        $rowset = $this->requeteTr2('serviceId', array(
            'transporteurId' => $transporteurId
        ), 'service2Id');
        foreach ($rowset as $row) {
            $result[$row['serviceId']]['r2'] = $row['effectif'];
        }
        // total
        foreach ($result as $key => &$value) {
            $value['total'] = array_sum($value);
        }
        return $result;
    }

    public function byTransporteur()
    {
        // SELECT transporteurId, count(*) FROM `sbm_t_affectations` a JOIN sbm_t_services s on a.service1Id=s.serviceId WHERE millesime=2014 GROUP BY transporteurId
        $result = array();
        $rowset = $this->requeteTr1('transporteurId', array(), 'transporteurId');
        foreach ($rowset as $row) {
            $result[$row['transporteurId']]['r1'] = $row['effectif'];
        }
        $rowset = $this->requeteTr2('transporteurId', array(), 'transporteurId');
        foreach ($rowset as $row) {
            $result[$row['transporteurId']]['r2'] = $row['effectif'];
        }
        // total
        foreach ($result as $key => &$value) {
            $value['total'] = array_sum($value);
        }
        return $result;
    }

    /**
     *
     * @param string $colum
     *            une colonne parmi sercice1Id ou service2Id (table affectations)
     * @param string|array|Where $where
     *            description de la requête dans un tableau associatif
     *            
     * @return ResultSet
     */
    private function requeteWith($column, $where, $group)
    {
        $where['s.millesime'] = $this->millesime;
        $select = $this->sql->select();
        $select->from(array(
            's' => $this->tableName['scolarites']
        ))
            ->join(array(
            'a' => $this->tableName['affectations']
        ), 'a.millesime=s.millesime AND a.eleveId=s.eleveId', array(
            $column,
            'effectif' => new Expression('count(*)')
        ))
            ->where($where)
            ->group($group);
        $statement = $this->sql->prepareStatementForSqlObject($select);
        return $statement->execute();
    }

    private function requeteWith2($by, $conditions, $group)
    {
        $select1 = new Select();
        $select1->from(array(
            'a1' => $this->tableName['affectations']
        ))->where(array(
            'millesime' => $this->millesime,
            'correspondance' => 2
        ));
        $column = $by . '2Id';
        $foreign = $by . '1Id';
        $jointure = "a.millesime=correspondances.millesime AND a.eleveId=correspondances.eleveId AND a.trajet=correspondances.trajet AND a.jours=correspondances.jours AND a.sens=correspondances.sens AND a.$column=correspondances.$foreign";
        $where = new Where();
        $where->equalTo('s.millesime', $this->millesime)->isNull('correspondances.millesime');
        foreach ($conditions as $key => $value) {
            $where->equalTo($key, $value);
        }
        $select = $this->sql->select();
        $select->from(array(
            's' => $this->tableName['scolarites']
        ))
            ->join(array(
            'a' => $this->tableName['affectations']
        ), 'a.millesime=s.millesime AND a.eleveId=s.eleveId', array(
            $column,
            'effectif' => new Expression('count(*)')
        ))
            ->join(array(
            'correspondances' => $select1
        ), $jointure, array(), Select::JOIN_LEFT)
            ->where($where)
            ->group($group);
        $statement = $this->sql->prepareStatementForSqlObject($select);
        return $statement->execute();
    }

    /**
     *
     * @param string $column            
     * @param array $where            
     * @param string $group            
     * @return \Zend\Db\Adapter\Driver\ResultInterface
     */
    private function requeteSrv($column, $where, $group)
    {
        $where['a.millesime'] = $this->millesime;
        $select = $this->sql->select();
        $select->from(array(
            'a' => $this->tableName['affectations']
        ))
            ->columns(array(
            'column' => $column,
            'effectif' => new Expression('count(*)')
        ))
            ->where($where)
            ->group($group);
        $statement = $this->sql->prepareStatementForSqlObject($select);
        return $statement->execute();
    }

    private function requeteSrv2($by, $conditions, $group)
    {
        $select1 = new Select();
        $select1->from($this->tableName['affectations'])->where(array(
            'millesime' => $this->millesime,
            'correspondance' => 2
        ));
        $column = $by . '2Id';
        $foreign = $by . '1Id';
        $jointure = "a.millesime=correspondances.millesime AND a.eleveId=correspondances.eleveId AND a.trajet=correspondances.trajet AND a.jours=correspondances.jours AND a.sens=correspondances.sens AND a.$column=correspondances.$foreign";
        $where = new Where();
        $where->equalTo('a.millesime', $this->millesime)->isNull('correspondances.millesime');
        foreach ($conditions as $key => $value) {
            $where->equalTo($key, $value);
        }
        $select = $this->sql->select();
        $select->from(array(
            'a' => $this->tableName['affectations']
        ))
            ->join(array(
            'correspondances' => $select1
        ), $jointure, array(), Select::JOIN_LEFT)
            ->columns(array(
            'column' => $column,
            'effectif' => new Expression('count(*)')
        ))
            ->where($where)
            ->group("a.$group");
        $statement = $this->sql->prepareStatementForSqlObject($select);
        return $statement->execute();
    }

    private function requeteTr1($column, $where, $group)
    {
        $where['millesime'] = $this->millesime;
        $select = $this->sql->select();
        $select->from(array(
            'a' => $this->tableName['affectations']
        ))
            ->join(array(
            's' => $this->tableName['services']
        ), 'a.service1Id=s.serviceId', array(
            $column,
            'effectif' => new Expression('count(*)')
        ))
            ->where($where)
            ->group($group);
        $statement = $this->sql->prepareStatementForSqlObject($select);
        return $statement->execute();
    }

    private function requeteTr2($column, $conditions, $group)
    {
        $select1 = new Select();
        $select1->from(array(
            'a1' => $this->tableName['affectations']
        ))->where(array(
            'millesime' => $this->millesime,
            'correspondance' => 2
        ));
        $jointure = "a.millesime=correspondances.millesime AND a.eleveId=correspondances.eleveId AND a.trajet=correspondances.trajet AND a.jours=correspondances.jours AND a.sens=correspondances.sens AND a.service2Id=correspondances.service1Id";
        $where = new Where();
        $where->equalTo('a.millesime', $this->millesime)->isNull('correspondances.millesime');
        foreach ($conditions as $key => $value) {
            $where->equalTo($key, $value);
        }
        $select = $this->sql->select();
        $select->from(array(
            'a' => $this->tableName['affectations']
        ))
            ->join(array(
            's' => $this->tableName['services']
        ), 'a.service2Id=s.serviceId', array(
            $column,
            'effectif' => new Expression('count(*)')
        ))
            ->join(array(
            'correspondances' => $select1
        ), $jointure, array(), Select::JOIN_LEFT)
            ->where($where)
            ->group($group);
        $statement = $this->sql->prepareStatementForSqlObject($select);
        return $statement->execute();
    }

    private function requeteTrSrv($rang, $column, $where, $group)
    {
        $where['millesime'] = $this->millesime;
        $select = $this->sql->select();
        $select->from(array(
            'a' => $this->tableName['affectations']
        ))
            ->join(array(
            's' => $this->tableName['services']
        ), sprintf('a.service%sId=s.serviceId', $rang), array(
            'serviceId' => $column,
            'effectif' => new Expression('count(*)')
        ))
            ->where($where)
            ->group($group);
        $statement = $this->sql->prepareStatementForSqlObject($select);
        return $statement->execute();
    }

    private function requeteCl($column, $where, $group, $transportes = false)
    {
        $where['s.millesime'] = $this->millesime;
        $select = $this->sql->select();
        $select->from(array(
            's' => $this->tableName['scolarites']
        ))
            ->columns(array(
            $column,
            'effectif' => new Expression('count(*)')
        ))
            ->where($where)
            ->group($group);
        if ($transportes) {
            $select->join(array(
                'a' => $this->tableName['affectations']
            ), 'a.millesime = s.millesime AND a.eleveId = s.eleveId', array());
        }
        $statement = $this->sql->prepareStatementForSqlObject($select);
        return $statement->execute();
    }

    private function requeteCir($rang, $column, $where, $group)
    {
        $where['c.millesime'] = $this->millesime;
        $on = sprintf('c.millesime=a.millesime AND c.serviceId=a.service%sId AND c.stationId=a.station%sId', $rang, $rang);
        $select = $this->sql->select();
        $select->from(array(
            'c' => $this->tableName['circuits']
        ))
            ->join(array(
            'a' => $this->tableName['affectations']
        ), $on, array(
            'effectif' => new Expression('count(*)')
        ))
            ->columns(array(
            'column' => $column
        ))
            ->where($where)
            ->group($group);
        $statement = $this->sql->prepareStatementForSqlObject($select);
        return $statement->execute();
    }

    private function requeteCom($rang, $column, $where, $group)
    {
        $where['millesime'] = $this->millesime;
        $select = $this->sql->select();
        if ($rang == 3) {
            $select->from(array(
                's' => $this->tableName['scolarites']
            ))
                ->columns(array(
                'column' => $column,
                'effectif' => new Expression('count(*)')
            ))
                ->where($where)
                ->group($group)
                ->having(function (Having $where) {
                $where->isNotNull('communeId');
            });
        } else {
            $on = sprintf('e.responsable%sId=r.responsableId', $rang);
            $select->from(array(
                'e' => $this->tableName['eleves']
            ))
                ->join(array(
                's' => $this->tableName['scolarites']
            ), 'e.eleveId=s.eleveId', array())
                ->join(array(
                'r' => $this->tableName['responsables']
            ), $on, array(
                'column' => $column
            ))
                ->columns(array(
                'effectif' => new Expression('count(*)')
            ))
                ->where($where)
                ->group($group);
        }
        $statement = $this->sql->prepareStatementForSqlObject($select);
        return $statement->execute();
    }
}