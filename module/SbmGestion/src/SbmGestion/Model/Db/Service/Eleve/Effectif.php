<?php
/**
 * Renvoie un tableau d'effectifs 
 *
 * @project sbm
 * @package SbmGestion/Model/Db/Service/Eleve
 * @filesource Effectif.php
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 3 jan. 2016
 * @version 2016-1.7.0
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

class Effectif extends AbstractQuery implements FactoryInterface
{

    /**
     *
     * @var integer
     */
    private $millesime;

    /**
     *
     * @var \Zend\Db\Adapter\Adapter
     */
    private $dbAdapter;

    /**
     *
     * @var \Zend\Db\Sql\Sql
     */
    private $sql;

    /**
     *
     * @var array
     */
    private $tableName = array();

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $this->millesime = Session::get('millesime');
        $db = $serviceLocator->get('Sbm\Db\DbLib');
        $this->dbAdapter = $db->getDbAdapter();
        $this->tableName['affectations'] = $db->getCanonicName('affectations', 'table');
        $this->tableName['circuits'] = $db->getCanonicName('circuits', 'table');
        $this->tableName['classes'] = $db->getCanonicName('classes', 'table');
        $this->tableName['communes'] = $db->getCanonicName('communes', 'table');
        $this->tableName['eleves'] = $db->getCanonicName('eleves', 'table');
        $this->tableName['etablissements'] = $db->getCanonicName('etablissements', 'table');
        $this->tableName['responsables'] = $db->getCanonicName('responsables', 'table');
        $this->tableName['scolarites'] = $db->getCanonicName('scolarites', 'table');
        $this->tableName['services'] = $db->getCanonicName('services', 'table');
        $this->sql = new Sql($db->getDbAdapter());
        return $this;
    }

    /**
     * Renvoie les effectifs des circuits, station par station, dans un tableau
     *
     * @param bool $sanspreinscrits            
     * @return array
     */
    public function byCircuit($sanspreinscrits = false)
    {
        // SELECT circuitId, count(*) FROM `sbm_t_circuits` c JOIN `sbm_t_affectations` a ON c.serviceId=a.service1Id AND c.stationId=a.station1Id WHERE millesime=2014 GROUP BY circuitId
        $result = array();
        if ($sanspreinscrits) {
            $filtre = array(
                'inscrit' => 1,
                array(
                    'paiement' => 1,
                    'or',
                    'fa' => 1,
                    'or',
                    '>' => array(
                        'gratuit',
                        0
                    )
                )
            );
        } else {
            $filtre = array(
                'inscrit' => 1
            );
        }
        $rowset = $this->requeteCir(1, 'circuitId', $filtre, 'circuitId');
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
     * Le résultat est un tableau associatif où les clés sont des communeId et les valeurs
     * sont des structures de la forme
     * array(
     * 'demandes' => array('r1' => effectif1, 'r2' => effectif2, 'ele' => effectif3),
     * 'transportes' => array('r1' => effectif4, 'r2' => effectif5, 'ele' => effectif6),
     * 'total' => array('demandes' => effectif_des_demandes, 'transportes' => effectif_des_transportes)
     * ) où r1 correspond à la commune du responsable1 lorsque l'élève n'a pas d'adresse perso,
     * r2 à la commune du responsable2
     * ele à la commune de l'élève s'il a une adresse perso
     * Les effectifs sont dans total en distinguant les demandes et les transportés (affectation)
     *
     * @return array
     */
    public function byCommune()
    {
        $result = array();
        // calcul du nombre d'élèves inscrits ou préinscrits ( = sauf rayés)
        // - pour la commune du R1 : inscrit = 1 AND sco.communeId IS NULL AND demandeR1 > 0
        $filtre = array(
            'inscrit' => 1,
            '>' => array(
                'demandeR1',
                0
            ),
            'is null' => array(
                's.communeId'
            )
        );
        $rowset = $this->requeteCom(1, 'communeId', $filtre, 'r.communeId');
        foreach ($rowset as $row) {
            $result[$row['column']]['demandes']['r1'] = $row['effectif'];
        }
        $filtre = array_merge($filtre, array(
            'a.trajet' => 1,
            'a.correspondance' => 1
        ));
        $rowset = $this->requeteCom(4, 'communeId', $filtre, 'r.communeId');
        foreach ($rowset as $row) {
            $result[$row['column']]['transportes']['r1'] = $row['effectif'];
        }
        
        // - pour la commune du R2 : inscrit = 1 AND demandeR2 > 0
        $filtre = array(
            'inscrit' => 1,
            '>' => array(
                'demandeR2',
                0
            )
        );
        $rowset = $this->requeteCom(2, 'communeId', $filtre, 'r.communeId');
        foreach ($rowset as $row) {
            $result[$row['column']]['demandes']['r2'] = $row['effectif'];
        }
        $filtre = array_merge($filtre, array(
            'a.trajet' => 2,
            'a.correspondance' => 1
        ));
        $rowset = $this->requeteCom(5, 'communeId', $filtre, 'r.communeId');
        foreach ($rowset as $row) {
            $result[$row['column']]['transportes']['r2'] = $row['effectif'];
        }
        
        // - pour la commune de l'élève lorsqu'il a une adresse personnelle :
        // inscrit = 1 AND sco.communeId IS NOT NULL AND demandeR1 > 0
        $filtre = array(
            'inscrit' => 1,
            '>' => array(
                'demandeR1',
                0
            ),
            'is not null' => array(
                's.communeId'
            )
        );
        $rowset = $this->requeteCom(3, 'communeId', $filtre, 'communeId');
        foreach ($rowset as $row) {
            $result[$row['column']]['demandes']['ele'] = $row['effectif'];
        }
        $filtre = array_merge($filtre, array(
            'a.trajet' => 1,
            'a.correspondance' => 1
        ));
        $rowset = $this->requeteCom(6, 'communeId', $filtre, 'communeId');
        foreach ($rowset as $row) {
            $result[$row['column']]['transportes']['ele'] = $row['effectif'];
        }
        
        // calcul du nombre d'élèves
        foreach ($result as $key => &$value) {
            $value['total']['demandes'] = array_sum($value['demandes']);
            $value['total']['transportes'] = array_sum($value['transportes']);
        }
        return $result;
    }

    /**
     * Renvoie un tableau d'effectifs des demandes et des élèves transportés par classe
     *
     * @return array
     */
    public function byClasse()
    {
        // SELECT classeId, count(*) FROM sbm_t_scolarites WHERE inscrit = 1 GROUP BY classeId
        $result = array();
        $filtre = array(
            'inscrit' => 1
        );
        $group = array(
            'classeId'
        );
        $rowset = $this->requeteCl('classeId', $filtre, $group, false);
        foreach ($rowset as $row) {
            $result[$row['classeId']]['demandes'] = $row['effectif'];
        }
        $filtre = array(
            'inscrit' => 1,
            'correspondance' => 1,
            array(
                array(
                    '>' => array(
                        'demandeR1',
                        0
                    ),
                    'trajet' => 1
                ),
                'or',
                array(
                    '=' => array(
                        'demandeR1',
                        0
                    ),
                    'trajet' => 2
                )
            )
        );
        $rowset = $this->requeteCl('classeId', $filtre, $group, true);
        foreach ($rowset as $row) {
            $result[$row['classeId']]['transportes'] = $row['effectif'];
        }
        return $result;
    }

    /**
     * Tableau d'effectifs par établissement des demandes et des élèves transportés
     *
     * @return array
     */
    public function byEtablissement()
    {
        // SELECT etablissementId, count(*) FROM sbm_scolarites GROUP BY etablissementId
        $result = array();
        $group = array(
            'etablissementId'
        );
        $filtre = array(
            'inscrit' => 1
        );
        $rowset = $this->requeteCl('etablissementId', $filtre, $group, false);
        foreach ($rowset as $row) {
            $result[$row['etablissementId']]['demandes'] = $row['effectif'];
            $result[$row['etablissementId']]['transportes'] = 0;
        }
        $filtre = array(
            'inscrit' => 1,
            'correspondance' => 1,
            array(
                array(
                    '>' => array(
                        'demandeR1',
                        0
                    ),
                    'trajet' => 1
                ),
                'or',
                array(
                    '=' => array(
                        'demandeR1',
                        0
                    ),
                    'trajet' => 2
                )
            )
        );
        $rowset = $this->requeteCl('etablissementId', $filtre, $group, true);
        foreach ($rowset as $row) {
            $result[$row['etablissementId']]['transportes'] = $row['effectif'];
        }
        return $result;
    }

    public function byEtablissementGivenService($serviceId)
    {
        $result = array();
        $rowset = $this->requeteWith('service1Id', array(
            's.inscrit' => 1,
            'service1Id' => $serviceId
        ), 'etablissementId');
        foreach ($rowset as $row) {
            $result[$row['etablissementId']]['r1'] = $row['effectif'];
        }
        
        $rowset = $this->requeteWith2('service', array(
            's.inscrit' => 1,
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

    public function byOrganisme()
    {
        // SELECT organismeId, count(*) FROM `sbm_t_scolarites` s WHERE millesime=2014 GROUP BY organismeId
        $result = array();
        $group = array(
            'organismeId'
        );
        $filtre = array(
            's.inscrit' => 1
        );
        $rowset = $this->requeteCl('organismeId', $filtre, $group, false);
        foreach ($rowset as $row) {
            $result[$row['organismeId']]['demandes'] = $row['effectif'];
        }
        $filtre = array(
            'inscrit' => 1,
            'correspondance' => 1,
            array(
                array(
                    '>' => array(
                        'demandeR1',
                        0
                    ),
                    'trajet' => 1
                ),
                'or',
                array(
                    '=' => array(
                        'demandeR1',
                        0
                    ),
                    'trajet' => 2
                )
            )
        );
        $rowset = $this->requeteCl('organismeId', $filtre, $group, true);
        foreach ($rowset as $row) {
            $result[$row['organismeId']]['transportes'] = $row['effectif'];
        }
        return $result;
    }

    public function byServiceGivenEtablissement($etablissementId)
    {
        $result = array();
        $rowset = $this->requeteWith('service1Id', array(
            's.inscrit' => 1,
            'etablissementId' => $etablissementId
        ), 'service1Id');
        foreach ($rowset as $row) {
            if ($row['service1Id'] == '')
                continue;
            $result[$row['service1Id']]['r1'] = $row['effectif'];
        }
        
        $rowset = $this->requeteWith2('service', array(
            's.inscrit' => 1,
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
            's.inscrit' => 1,
            'station1Id' => $stationId
        ), 'service1Id');
        foreach ($rowset as $row) {
            if ($row['service1Id'] == '')
                continue;
            $result[$row['service1Id']]['r1'] = $row['effectif'];
        }
        
        $rowset = $this->requeteWith2('service', array(
            's.inscrit' => 1,
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
        $rowset = $this->requeteSrv('service1Id', array(
            'inscrit' => 1
        ), 'service1Id');
        foreach ($rowset as $row) {
            $result[$row['column']]['r1'] = $row['effectif'];
        }
        $rowset = $this->requeteSrv2('service', array(
            'inscrit' => 1
        ), 'service2Id');
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
        $rowset = $this->requeteSrv('station1Id', array(
            'inscrit' => 1
        ), 'station1Id');
        foreach ($rowset as $row) {
            $result[$row['column']]['r1'] = $row['effectif'];
        }
        $rowset = $this->requeteSrv2('station', $this->arrayToWhere(null, array(
            'inscrit' => 1,
            'isNotNull' => array('a.service2Id')
        )), 'station2Id');
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
        $group = array(
            'tarifId'
        );
        $filtre = array(
            's.inscrit' => 1
        );
        $rowset = $this->requeteCl('tarifId', $filtre, $group, false);
        foreach ($rowset as $row) {
            $result[$row['tarifId']]['demandes'] = $row['effectif'];
        }
        $filtre = array(
            'inscrit' => 1,
            'correspondance' => 1,
            array(
                array(
                    '>' => array(
                        'demandeR1',
                        0
                    ),
                    'trajet' => 1
                ),
                'or',
                array(
                    '=' => array(
                        'demandeR1',
                        0
                    ),
                    'trajet' => 2
                )
            )
        );
        $rowset = $this->requeteCl('tarifId', $filtre, $group, true);
        foreach ($rowset as $row) {
            $result[$row['tarifId']]['transportes'] = $row['effectif'];
        }
        return $result;
    }

    public function transporteurByService($transporteurId)
    {
        // SELECT service1Id serviceId, count(*) FROM `sbm_t_affectations` a JOIN `sbm_t_services` s ON a.service1Id=s.serviceId WHERE millesime=2014 AND transporteurId=1 GROUP BY service1Id
        $result = array();
        $rowset = $this->requeteTr1('serviceId', array(
            's.inscrit' => 1,
            'transporteurId' => $transporteurId
        ), 'service1Id');
        foreach ($rowset as $row) {
            $result[$row['serviceId']]['r1'] = $row['effectif'];
        }
        $rowset = $this->requeteTr2('serviceId', array(
            's.inscrit' => 1,
            'transporteurId' => $transporteurId
        ), 'a.service2Id');
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
        $rowset = $this->requeteTr1('transporteurId', array(
            's.inscrit' => 1
        ), 'transporteurId');
        foreach ($rowset as $row) {
            $result[$row['transporteurId']]['r1'] = $row['effectif'];
        }
        $rowset = $this->requeteTr2('transporteurId', array(
            's.inscrit' => 1
        ), 'transporteurId');
        foreach ($rowset as $row) {
            $result[$row['transporteurId']]['r2'] = $row['effectif'];
        }
        // total
        foreach ($result as $key => &$value) {
            $value['total'] = array_sum($value);
        }
        // die(var_dump($result));
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
            ->join(array(
            's' => $this->tableName['scolarites']
        ), 's.millesime=a.millesime AND s.eleveId=a.eleveId', array())
            ->columns(array(
            'column' => $column,
            'effectif' => new Expression('count(*)')
        ))
            ->where($where)
            ->group($group);
        $statement = $this->sql->prepareStatementForSqlObject($select);
        return $statement->execute();
    }

    /**
     *
     * @param string $by            
     * @param Zend\Db\Sql\Where|array $conditions            
     * @param string $group            
     * @return \Zend\Db\Adapter\Driver\ResultInterface
     */
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
        if (is_array($conditions)) {
            $where = new Where();
            $where->equalTo('a.millesime', $this->millesime)->isNull('correspondances.millesime');
            foreach ($conditions as $key => $value) {
                $where->equalTo($key, $value);
            }
        } else {
            $where = $conditions->equalTo('a.millesime', $this->millesime)->isNull('correspondances.millesime');
        }
        $select = $this->sql->select();
        $select->from(array(
            'a' => $this->tableName['affectations']
        ))
            ->join(array(
            'correspondances' => $select1
        ), $jointure, array(), Select::JOIN_LEFT)
            ->join(array(
            's' => $this->tableName['scolarites']
        ), 's.millesime=a.millesime AND s.eleveId=a.eleveId', array())
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
        $where['a.millesime'] = $this->millesime;
        $select = $this->sql->select();
        $select->from(array(
            'a' => $this->tableName['affectations']
        ))
            ->columns(array())
            ->join(array(
            's' => $this->tableName['scolarites']
        ), 's.millesime=a.millesime AND s.eleveId=a.eleveId', array())
            ->join(array(
            'ser' => $this->tableName['services']
        ), 'a.service1Id=ser.serviceId', array(
            $column,
            'effectif' => new Expression('count(*)')
        ))
            ->where($where)
            ->group($group);
        $statement = $this->sql->prepareStatementForSqlObject($select);
        // die($this->getSqlString($select));
        return $statement->execute();
    }

    private function requeteTr2($column, $conditions, $group)
    {
        $select1 = new Select();
        $select1->from(array(
            'a1' => $this->tableName['affectations']
        ))->where(array(
            'a1.millesime' => $this->millesime,
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
            ->columns(array())
            ->join(array(
            's' => $this->tableName['scolarites']
        ), 's.millesime=a.millesime AND s.eleveId=a.eleveId', array())
            ->join(array(
            'ser' => $this->tableName['services']
        ), 'a.service2Id=ser.serviceId', array(
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

    private function requeteCl($column, $filtre, $group, $transportes = false)
    {
        $where = new Where();
        $where->equalTo('s.millesime', $this->millesime);
        $select = $this->sql->select();
        $select->from(array(
            's' => $this->tableName['scolarites']
        ))
            ->columns(array(
            $column,
            'effectif' => new Expression('count(*)')
        ))
            ->where($this->arrayToWhere($where, $filtre))
            ->group($group);
        if ($transportes) {
            $select->join(array(
                'a' => $this->tableName['affectations']
            ), 'a.millesime = s.millesime AND a.eleveId = s.eleveId', array());
        }
        $statement = $this->sql->prepareStatementForSqlObject($select);
        return $statement->execute();
    }

    private function requeteCir($rang, $column, $filtre, $group)
    {
        $where = new Where();
        $where->equalTo('c.millesime', $this->millesime);
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
            ->join(array(
            's' => $this->tableName['scolarites']
        ), 's.millesime = a.millesime AND s.eleveId = a.eleveId', array())
            ->columns(array(
            'column' => $column
        ))
            ->where($this->arrayToWhere($where, $filtre))
            ->group($group);
        $statement = $this->sql->prepareStatementForSqlObject($select);
        return $statement->execute();
    }

    private function requeteCom($rang, $column, $filtre, $group)
    {
        $where = new Where();
        $where->equalTo('s.millesime', $this->millesime);
        $select = $this->sql->select();
        switch ($rang) {
            case 1:
            case 2:
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
                    ->where($this->arrayToWhere($where, $filtre))
                    ->group($group);
                break;
            case 3:
                $select->from(array(
                    's' => $this->tableName['scolarites']
                ))
                    ->columns(array(
                    'column' => $column,
                    'effectif' => new Expression('count(*)')
                ))
                    ->where($this->arrayToWhere($where, $filtre))
                    ->group($group)
                    ->having(function (Having $where) {
                    $where->isNotNull('communeId');
                });
                break;
            case 4:
            case 5:
                $select->from(array(
                    's' => $this->tableName['scolarites']
                ))
                    ->join(array(
                    'a' => $this->tableName['affectations']
                ), 'a.millesime = s.millesime AND a.eleveId=s.eleveId', array())
                    ->join(array(
                    'r' => $this->tableName['responsables']
                ), 'r.responsableId = a.responsableId', array(
                    'column' => $column
                ))
                    ->columns(array(
                    'effectif' => new Expression('count(*)')
                ))
                    ->where($this->arrayToWhere($where, $filtre))
                    ->group($group);
                break;
            case 6:
                $select->from(array(
                    's' => $this->tableName['scolarites']
                ))
                    ->join(array(
                    'a' => $this->tableName['affectations']
                ), 'a.millesime = s.millesime AND a.eleveId=s.eleveId', array())
                    ->columns(array(
                    'column' => $column,
                    'effectif' => new Expression('count(*)')
                ))
                    ->where($this->arrayToWhere($where, $filtre))
                    ->group($group);
                break;
            default:
                throw new \SbmGestion\Model\Db\Service\Exception(__METHOD__ . ' - Mauvais argument `rang`.');
                break;
        }
        $statement = $this->sql->prepareStatementForSqlObject($select);
        return $statement->execute();
    }

    /**
     * Renvoie la chaine de requête (après l'appel de la requête)
     *
     * @param \Zend\Db\Sql\Select $select            
     *
     * @return \Zend\Db\Adapter\mixed
     */
    public function getSqlString($select)
    {
        return $select->getSqlString($this->dbAdapter->getPlatform());
    }

    /**
     * Statistiques
     */
    
    /**
     * Renvoie un tableau statistiques pour l'année en cours et pour l'année précédente
     *
     * @return array <br><code>array('annee_courante' => array(), 'annee_precedente' => array())</code><br>
     *         où chaque tableau est composé de lignes de la forme :<br>
     *         <code>array('nom' => nnn, 'alias' => aaa, 'inscrits' => value, 'internet' => value, 'papier' => value, 'transportes' => value)</code>
     *         <br>où<ul>
     *         <li><b>nom</b> est le nom de la classe</li>
     *         <li><b>alias</b> est l'aliasCG</li>
     *         <li><b>inscrits</b> est le nombre d'inscrits</li>
     *         <li><b>internet</b> est le nombre d'inscrits par internet</li>
     *         <li><b>papier</b> est le nombre d'inscrits par fiche papier</li>
     *         <li><b>transportes</b> est le nombre d'élève transportés</li></ul>
     */
    public function statistiquesParClasse()
    {
        $statement = $this->sql->prepareStatementForSqlObject($this->selectStatistiquesParClasse($this->millesime));
        $result['annee_courante'] = iterator_to_array($statement->execute());
        $statement = $this->sql->prepareStatementForSqlObject($this->selectStatistiquesParClasse($this->millesime - 1));
        $result['annee_precedente'] = iterator_to_array($statement->execute());
        // die(var_dump($result));
        return $result;
    }

    /**
     * Définit une requête pour les statistiques portant sur un millesime
     *
     * @param int $millesime
     *            Millesime sur lequel porte les calculs
     *            
     * @return \Zend\Db\Sql\Select
     */
    private function selectStatistiquesParClasse($millesime)
    {
        // Tous les inscrits
        // SELECT `sbm_t_scolarites`.`classeId`, count(`sbm_t_scolarites`.`eleveId`) AS `inscrits`
        // FROM `sbm_t_scolarites`
        // WHERE inscrit = 1 AND `millesime` = '2015'
        // GROUP BY `sbm_t_scolarites`.`classeId`
        $whereInscrits = new Where();
        $whereInscrits->literal('inscrit = 1')->equalTo('millesime', $millesime);
        $selectInscrits = $this->sql->select($this->tableName['scolarites'])
            ->group(array(
            'classeId'
        ))
            ->columns(array(
            'classeId',
            'inscrits' => new Expression('count(eleveId)')
        ))
            ->where($whereInscrits);
        
        // Inscrits par internet
        // SELECT `sbm_t_scolarites`.`classeId`, count(`sbm_t_scolarites`.`eleveId`) AS `internet`
        // FROM `sbm_t_scolarites`
        // WHERE inscrit = 1 AND internet = 1 AND `millesime` = '2015'
        // GROUP BY `sbm_t_scolarites`.`classeId`
        $whereInternet = new Where();
        $whereInternet->literal('inscrit = 1')
            ->literal('internet = 1')
            ->equalTo('millesime', $millesime);
        $selectInternet = $this->sql->select($this->tableName['scolarites'])
            ->group(array(
            'classeId'
        ))
            ->columns(array(
            'classeId',
            'internet' => new Expression('count(eleveId)')
        ))
            ->where($whereInternet);
        
        // Inscrits par fiche papier
        // SELECT `sbm_t_scolarites`.`classeId`, count(`sbm_t_scolarites`.`eleveId`) AS `papier`
        // FROM `sbm_t_scolarites`
        // WHERE inscrit = 1 AND internet = 0 AND `millesime` = '2015'
        // GROUP BY `sbm_t_scolarites`.`classeId`
        $wherePapier = new Where();
        $wherePapier->literal('inscrit = 1')
            ->literal('internet = 0')
            ->equalTo('millesime', $millesime);
        $selectPapier = $this->sql->select($this->tableName['scolarites'])
            ->group(array(
            'classeId'
        ))
            ->columns(array(
            'classeId',
            'papier' => new Expression('count(eleveId)')
        ))
            ->where($wherePapier);
        
        // SELECT DISTINCT `sbm_t_scolarites`.`classeId`, `sbm_t_scolarites`.`eleveId`
        // FROM `sbm_t_scolarites`
        // INNER JOIN `sbm_t_affectations` ON `sbm_t_affectations`.`millesime`=`sbm_t_scolarites`.`millesime` AND `sbm_t_affectations`.`eleveId`=`sbm_t_scolarites`.`eleveId`
        // WHERE `sbm_t_scolarites`.`millesime` = '2015' AND `sbm_t_scolarites`.`inscrit` = 1
        $whereTransportes = new Where();
        $whereTransportes->equalTo('sco.millesime', $millesime)->literal('inscrit = 1');
        $selectElevesTransportes = $this->sql->select(array(
            'sco' => $this->tableName['scolarites']
        ))
            ->join(array(
            'aff' => $this->tableName['affectations']
        ), 'sco.millesime = aff.millesime AND sco.eleveId = aff.eleveId', array())
            ->columns(array(
            'classeId',
            'eleveId'
        ))
            ->quantifier(Select::QUANTIFIER_DISTINCT)
            ->where($whereTransportes);
        
        // SELECT `aff`.`classeId`, count(`aff`.`eleveId`) AS `transportes`
        // FROM (requete_precedente) AS `aff`
        // GROUP BY `aff`.`classeId`
        $selectTransportes = $this->sql->select(array(
            'aff' => $selectElevesTransportes
        ))
            ->group(array(
            'classeId'
        ))
            ->columns(array(
            'classeId',
            'transportes' => new Expression('count(eleveId)')
        ));
        
        $select = $this->sql->select();
        $select->from(array(
            'cla' => $this->tableName['classes']
        ))
            ->join(array(
            'inscrits' => $selectInscrits
        ), 'inscrits.classeId=cla.classeId', array(
            'inscrits'
        ), Select::JOIN_LEFT)
            ->join(array(
            'internet' => $selectInternet
        ), 'internet.classeId=cla.classeId', array(
            'internet'
        ), Select::JOIN_LEFT)
            ->join(array(
            'papier' => $selectPapier
        ), 'papier.classeId=cla.classeId', array(
            'papier'
        ), Select::JOIN_LEFT)
            ->join(array(
            'transportes' => $selectTransportes
        ), 'transportes.classeId=cla.classeId', array(
            'transportes'
        ), Select::JOIN_LEFT)
            ->columns(array(
            'nom',
            'alias' => 'aliasCG'
        ))
            ->order(array(
            'cla.niveau ASC',
            'cla.nom DESC'
        ));
        // die($this->getSqlString($select));
        return $select;
    }

    /**
     * Renvoie un tableau statistiques pour l'année en cours et pour l'année précédente
     *
     * @return array <br><code>array('annee_courante' => array(), 'annee_precedente' => array())</code><br>
     *         où chaque tableau est composé de lignes de la forme :<br>
     *         <code>array('nom' => nnn, 'alias' => aaa, 'inscrits' => value, 'internet' => value, 'papier' => value, 'transportes' => value)</code>
     *         <br>où<ul>
     *         <li><b>nom</b> est le nom de la classe</li>
     *         <li><b>alias</b> est l'aliasCG</li>
     *         <li><b>inscrits</b> est le nombre d'inscrits</li>
     *         <li><b>internet</b> est le nombre d'inscrits par internet</li>
     *         <li><b>papier</b> est le nombre d'inscrits par fiche papier</li>
     *         <li><b>transportes</b> est le nombre d'élève transportés</li></ul>
     */
    public function statistiquesParCommune()
    {
        $statement = $this->sql->prepareStatementForSqlObject($this->selectStatistiquesParCommune($this->millesime));
        $result['annee_courante'] = iterator_to_array($statement->execute());
        $statement = $this->sql->prepareStatementForSqlObject($this->selectStatistiquesParCommune($this->millesime - 1));
        $result['annee_precedente'] = iterator_to_array($statement->execute());
        // die(var_dump($result));
        return $result;
    }

    private function selectStatistiquesParCommune($millesime)
    {
        // Requête donnant les eleveId sans doublons des élèves transportés pour un millesime donné
        $select_affectations = $this->sql->select($this->tableName['affectations'])
            ->quantifier(Select::QUANTIFIER_DISTINCT)
            ->columns(array(
            'millesime',
            'eleveId'
        ))
            ->where(array(
            'millesime' => $millesime
        ));
        // Requête donnant les élèves inscrits sans doublons d'un millésime donné,
        // avec internet et affectation
        // (affectation est null si pas d'affectation, non null s'il y en a au moins une)
        $select_eleves = $this->sql->select(array(
            'ele' => $this->tableName['eleves']
        ))
            ->join(array(
            'sco' => $this->tableName['scolarites']
        ), 'ele.eleveid = sco.eleveid', array(
            'internet',
            'demandeR1',
            'demandeR2'
        ))
            ->join(array(
            'aff' => $select_affectations
        ), 'sco.millesime = aff.millesime AND sco.eleveid = aff.eleveId', array(
            'affectation' => 'eleveId'
        ), Select::JOIN_LEFT)
            ->columns(array(
            'eleveId',
            'responsable1Id',
            'responsable2Id'
        ))
            ->where(array(
            'sco.millesime' => $millesime,
            'inscrit = 1'
        ));
        // Requête donnant les responsableId avec leur commune
        $select_responsables = $this->sql->select(array(
            'c' => $this->tableName['communes']
        ))
            ->join(array(
            'r' => $this->tableName['responsables']
        ), 'c.communeId = r.communeId', array(
            'responsableId'
        ))
            ->columns(array(
            'commune' => 'nom'
        ));
        // Requête
        $select = $this->sql->select(array(
            'com' => $this->tableName['communes']
        ))
            ->join(array(
            'inscrits' => $this->selectPartielParCommune('inscrits', $select_eleves, $select_responsables)
        ), 'com.nom = inscrits.nom', array(
            'inscrits'
        ), Select::JOIN_LEFT)
            ->join(array(
            'internet' => $this->selectPartielParCommune('internet', $select_eleves, $select_responsables, array(
                'internet' => 1
            ))
        ), 'com.nom = internet.nom', array(
            'internet'
        ), Select::JOIN_LEFT)
            ->join(array(
            'papier' => $this->selectPartielParCommune('papier', $select_eleves, $select_responsables, array(
                'internet' => 0
            ))
        ), 'com.nom = papier.nom', array(
            'papier'
        ), Select::JOIN_LEFT)
            ->join(array(
            'transportes' => $this->selectPartielParCommune('transportes', $select_eleves, $select_responsables, array(
                'affectation Is Not Null'
            ))
        ), 'com.nom = transportes.nom', array(
            'transportes'
        ), Select::JOIN_LEFT)
            ->columns(array(
            'nom'
        ))
            ->where(array(
            'desservie' => 1
        ));
        // die($this->getSqlString($select));
        return $select;
    }

    /**
     * Le champs de la requête rendue sont le nom de la commune et l'effectif sous le nom demandé.
     *
     * @param string $compteur
     *            Nom du champ renvoyant l'effectif
     * @param Select $select_eleves            
     * @param Select $select_responsables            
     * @param array $where
     *            Les conditions peuvent être :<ul>
     *            <li>internet = 1</li>
     *            <li>internet = 0</li>
     *            <li>affectation Is Not Null</li></ul>
     *            
     * @return \Zend\Db\Sql\Select
     */
    private function selectPartielParCommune($compteur, Select $select_eleves, Select $select_responsables, array $where = array())
    {
        // Requête donnant les élèves par commune par le R2
        $select2 = $this->sql->select(array(
            'res' => $select_responsables
        ))
            ->join(array(
            'elv' => $select_eleves
        ), 'elv.responsable2Id = res.responsableId', array(
            'eleveId'
        ))
            ->columns(array(
            'commune'
        ))
            ->where(array_merge([
            'demandeR2 > 0'
        ], $where));
        
        // Requête donnant les élèves par commune par le R1
        $select1 = $this->sql->select(array(
            'res' => $select_responsables
        ))
            ->join(array(
            'elv' => $select_eleves
        ), 'elv.responsable1Id = res.responsableId', array(
            'eleveId'
        ))
            ->columns(array(
            'commune'
        ))
            ->where(array_merge([
            'demandeR1 > 0'
        ], $where));
        
        // union des deux requêtes
        $select1->combine($select2);
        
        // comptage des élèves
        $select = $this->sql->select(array(
            'tmp' => $select1
        ))
            ->group('commune')
            ->columns(array(
            'nom' => 'commune',
            $compteur => new Expression('count(eleveId)')
        ));
        return $select;
    }

    /**
     * Renvoie un tableau statistiques pour l'année en cours et pour l'année précédente<br>
     *
     *
     * @return array <br><code>array('annee_courante' => array(), 'annee_precedente' => array())</code><br>
     *         où chaque tableau est composé de lignes de la forme :<br>
     *         <code>array('nom' => nnn, 'inscrits' => value, 'internet' => value, 'papier' => value)</code>
     *         <br>où<ul>
     *         <li><b>nom</b> est le code du circuit</li>
     *         <li><b>inscrits</b> est le nombre d'élèves transportés</li>
     *         <li><b>internet</b> est le nombre d'inscrits par internet</li>
     *         <li><b>papier</b> est le nombre d'inscrits par fiche papier</li><ul>
     */
    public function statistiquesParCircuit()
    {
        $statement = $this->sql->prepareStatementForSqlObject($this->selectStatistiquesParCircuit($this->millesime));
        $result['annee_courante'] = iterator_to_array($statement->execute());
        $statement = $this->sql->prepareStatementForSqlObject($this->selectStatistiquesParCircuit($this->millesime - 1));
        $result['annee_precedente'] = iterator_to_array($statement->execute());
        // die(var_dump($result));
        return $result;
    }

    private function selectStatistiquesParCircuit($millesime)
    {
        /*
         * SELECT ser.serviceId AS nom, inscrits, internet, papier
         * FROM `sbm_t_services` ser
         * LEFT JOIN (
         * SELECT serviceId, count(sub1.eleveId) AS inscrits
         * FROM (
         * SELECT DISTINCT s.serviceId, aff.eleveId
         * FROM `sbm_t_services` s
         * LEFT JOIN `sbm_t_affectations`aff ON aff.service1Id=s.serviceId OR aff.service2Id=s.serviceId
         * LEFT JOIN `sbm_t_scolarites`sco ON aff.millesime=sco.millesime AND aff.eleveId=sco.eleveId
         * WHERE aff.millesime=2015 AND sco.inscrit=1
         * ) sub1
         * GROUP BY serviceId
         * ) tmp1 ON tmp1.serviceId=ser.serviceId
         * LEFT JOIN (
         * SELECT serviceId, count(sub2.eleveId) AS internet
         * FROM (
         * SELECT DISTINCT s.serviceId, aff.eleveId
         * FROM `sbm_t_services` s
         * LEFT JOIN `sbm_t_affectations`aff ON aff.service1Id=s.serviceId OR aff.service2Id=s.serviceId
         * LEFT JOIN `sbm_t_scolarites`sco ON aff.millesime=sco.millesime AND aff.eleveId=sco.eleveId
         * WHERE aff.millesime=2015 AND sco.inscrit=1 AND sco.internet=1
         * ) sub2
         * GROUP BY serviceId
         * ) tmp2 ON tmp2.serviceId=ser.serviceId
         * LEFT JOIN (
         * SELECT serviceId, count(sub3.eleveId) AS papier
         * FROM (
         * SELECT DISTINCT s.serviceId, aff.eleveId
         * FROM `sbm_t_services` s
         * LEFT JOIN `sbm_t_affectations`aff ON aff.service1Id=s.serviceId OR aff.service2Id=s.serviceId
         * LEFT JOIN `sbm_t_scolarites`sco ON aff.millesime=sco.millesime AND aff.eleveId=sco.eleveId
         * WHERE aff.millesime=2015 AND sco.inscrit=1 AND internet=0
         * ) sub3
         * GROUP BY serviceId
         * ) tmp3 ON tmp3.serviceId=ser.serviceId
         * GROUP BY ser.serviceId
         */
        $result = array();
        $subSelectBase = $this->sql->select([
            's' => $this->tableName['services']
        ])
            ->columns(array(
            'serviceId'
        ))
            ->join([
            'aff' => $this->tableName['affectations']
        ], 'aff.service1Id=s.serviceId OR aff.service2Id=s.serviceId', [
            'eleveId'
        ], Select::JOIN_LEFT)
            ->join([
            'sco' => $this->tableName['scolarites']
        ], 'aff.millesime=sco.millesime AND aff.eleveId=sco.eleveId', [], Select::JOIN_LEFT)
            ->quantifier(Select::QUANTIFIER_DISTINCT);
        // inscrits
        $whereInscrits = new Where();
        $whereInscrits->equalTo('sco.millesime', $millesime)->literal('sco.inscrit = 1');
        $subSelectInscrits = clone $subSelectBase;
        $subSelectInscrits->where($whereInscrits);
        
        // internet
        $whereInternet = clone $whereInscrits;
        $whereInternet->literal('sco.internet = 1');
        $subSelectInternet = clone $subSelectBase;
        $subSelectInternet->where($whereInternet);
        
        // papier
        $wherePapier = clone $whereInscrits;
        $wherePapier->literal('sco.internet = 0');
        $subSelectPapier = clone $subSelectBase;
        $subSelectPapier->where($wherePapier);
        
        // requête
        $select = $this->sql->select([
            'ser' => $this->tableName['services']
        ])
            ->join([
            'tmp1' => $this->subSelectCircuitGroup('sub1', 'inscrits', $subSelectInscrits)
        ], 'tmp1.serviceId=ser.serviceId', [], Select::JOIN_LEFT)
            ->join([
            'tmp2' => $this->subSelectCircuitGroup('sub2', 'internet', $subSelectInternet)
        ], 'tmp2.serviceId=ser.serviceId', [], Select::JOIN_LEFT)
            ->join([
            'tmp3' => $this->subSelectCircuitGroup('sub3', 'papier', $subSelectPapier)
        ], 'tmp3.serviceId=ser.serviceId', [], Select::JOIN_LEFT)
            ->columns([
            'nom' => 'serviceId',
            'inscrits' => new Expression('COALESCE(inscrits, 0)'),
            'internet' => new Expression('COALESCE(internet, 0)'),
            'papier' => new Expression('COALESCE(papier, 0)')
        ])
            ->order([
            'ser.serviceId'
        ]);
        // die($this->getSqlString($select));
        return $select;
    }

    /**
     * Renvoie une sous requête de la forme
     * SELECT serviceId, count($alias.eleveId) AS inscrits
     * FROM $sub AS $alias
     * GROUP BY serviceId
     *
     * @param string $alias
     *            nom de l'alias (les alias doivent être distincts)
     * @param Select $sub
     *            requête dérivée de $subSelectBase
     *            
     * @return \Zend\Db\Sql\Select
     */
    private function subSelectCircuitGroup($alias, $fieldNameCount, Select $sub)
    {
        return $this->sql->select([
            $alias => $sub
        ])
            ->columns([
            'serviceId',
            $fieldNameCount => new Expression('count(eleveId)')
        ])
            ->group(array(
            'serviceId'
        ));
    }

    /**
     * Renvoie un tableau statistiques pour l'année en cours et pour l'année précédente
     *
     * @return array <br><code>array('annee_courante' => array(), 'annee_precedente' => array())</code><br>
     *         où chaque tableau est composé de lignes de la forme :<br>
     *         <code>array('nom' => nnn, 'alias' => aaa, 'inscrits' => value, 'internet' => value, 'papier' => value, 'transportes' => value)</code>
     *         <br>où<ul>
     *         <li><b>nom</b> est le nom de la classe</li>
     *         <li><b>alias</b> est l'aliasCG</li>
     *         <li><b>inscrits</b> est le nombre d'inscrits</li>
     *         <li><b>internet</b> est le nombre d'inscrits par internet</li>
     *         <li><b>papier</b> est le nombre d'inscrits par fiche papier</li>
     *         <li><b>transportes</b> est le nombre d'élève transportés</li></ul>
     */
    public function statistiquesParEtablissement()
    {
        $statement = $this->sql->prepareStatementForSqlObject($this->selectStatistiquesParEtablissement($this->millesime));
        $result['annee_courante'] = iterator_to_array($statement->execute());
        $statement = $this->sql->prepareStatementForSqlObject($this->selectStatistiquesParEtablissement($this->millesime - 1));
        $result['annee_precedente'] = iterator_to_array($statement->execute());
        return $result;
    }

    private function selectStatistiquesParEtablissement($millesime)
    {
        // les inscrits
        $whereInscrits = new Where();
        $whereInscrits->literal('inscrit = 1')->equalTo('millesime', $millesime);
        $selectInscrits = $this->sql->select($this->tableName['scolarites'])
            ->group(array(
            'etablissementId'
        ))
            ->columns(array(
            'etablissementId',
            'inscrits' => new Expression('count(eleveId)')
        ))
            ->where($whereInscrits);
        
        // par internet
        $whereInternet = new Where();
        $whereInternet->literal('inscrit = 1')
            ->literal('internet = 1')
            ->equalTo('millesime', $millesime);
        $selectInternet = $this->sql->select($this->tableName['scolarites'])
            ->group(array(
            'etablissementId'
        ))
            ->columns(array(
            'etablissementId',
            'internet' => new Expression('count(eleveId)')
        ))
            ->where($whereInternet);
        
        // par fiche papier
        $wherePapier = new Where();
        $wherePapier->literal('inscrit = 1')
            ->literal('internet = 0')
            ->equalTo('millesime', $millesime);
        $selectPapier = $this->sql->select($this->tableName['scolarites'])
            ->group(array(
            'etablissementId'
        ))
            ->columns(array(
            'etablissementId',
            'papier' => new Expression('count(eleveId)')
        ))
            ->where($wherePapier);
        
        // transportés
        $whereTransportes = new Where();
        $whereTransportes->equalTo('sco.millesime', $millesime)->equalTo('sco.inscrit', 1);
        $selectElevesTransportes = $this->sql->select(array(
            'sco' => $this->tableName['scolarites']
        ))
            ->join(array(
            'aff' => $this->tableName['affectations']
        ), 'sco.millesime = aff.millesime AND sco.eleveId = aff.eleveId', array())
            ->columns(array(
            'etablissementId',
            'eleveId'
        ))
            ->quantifier(Select::QUANTIFIER_DISTINCT)
            ->where($whereTransportes);
        $selectTransportes = $this->sql->select(array(
            'aff' => $selectElevesTransportes
        ))
            ->group(array(
            'etablissementId'
        ))
            ->columns(array(
            'etablissementId',
            'transportes' => new Expression('count(eleveId)')
        ));
        
        // construction de la requête
        $select = $this->sql->select([
            'eta' => $this->tableName['etablissements']
        ])
            ->columns([
            'nom'
        ])
            ->join([
            'com' => $this->tableName['communes']
        ], 'eta.communeId = com.communeId', [
            'commune' => 'nom'
        ])
            ->join([
            'transportes' => $selectTransportes
        ], 'transportes.etablissementId = eta.etablissementId', [
            'transportes' => new Expression('COALESCE(transportes, 0)')
        ], Select::JOIN_LEFT)
            ->join([
            'inscrits' => $selectInscrits
        ], 'inscrits.etablissementId = eta.etablissementId', [
            'inscrits' => new Expression('COALESCE(inscrits, 0)')
        ], Select::JOIN_LEFT)
            ->join([
            'internet' => $selectInternet
        ], 'internet.etablissementId = eta.etablissementId', [
            'internet' => new Expression('COALESCE(internet, 0)')
        ], Select::JOIN_LEFT)
            ->join([
            'papier' => $selectPapier
        ], 'papier.etablissementId = eta.etablissementId', [
            'papier' => new Expression('COALESCE(papier, 0)')
        ], Select::JOIN_LEFT)
            ->where([
            'eta.desservie' => 1
        ])
            ->order([
            'com.nom',
            'eta.niveau',
            'eta.nom'
        ]);
        // die($this->getSqlString($select));
        return $select;
    }

    /**
     * Renvoie un tableau statistiques pour l'année en cours et pour l'année précédente
     *
     * @return array <br><code>array('annee_courante' => array(), 'annee_precedente' => array())</code><br>
     *         où chaque tableau est composé de lignes de la forme :<br>
     *         <code>array('commune' => nnn, 'circuit' => aaa, 'transportes' => value)</code>
     *         <br>où<ul>
     *         <li><b>commune</b> est le nom de la commune</li>
     *         <li><b>circuit</b> est le code du service</li>
     *         <li><b>transportes</b> est le nombre d'élève transportés</li></ul>
     */
    public function statistiquesParCommuneCircuit()
    {
        $statement = $this->sql->prepareStatementForSqlObject($this->selectStatistiquesParCommuneCircuit($this->millesime));
        $result['annee_courante'] = iterator_to_array($statement->execute());
        $statement = $this->sql->prepareStatementForSqlObject($this->selectStatistiquesParCommuneCircuit($this->millesime - 1));
        $result['annee_precedente'] = iterator_to_array($statement->execute());
        // die(var_dump($result));
        return $result;
    }

    /**
     * Renvoie une requête de la forme
     *
     * SELECT com.nom AS commune, COALESCE(tmp1.serviceId, '') AS circuit, COALESCE(tmp1.inscrits, 0) AS inscrits, COALESCE(tmp2.internet, 0) AS internet, COALESCE(tmp3.papier,0) AS papier
     * FROM `sbm_t_communes` com
     *
     * LEFT JOIN (
     * SELECT serviceId, communeId, count(sub1.eleveId) AS inscrits
     * FROM (
     * SELECT DISTINCT res.communeId, ser.serviceId, aff.eleveId
     * FROM `sbm_t_services` ser
     * LEFT JOIN `sbm_t_affectations`aff ON aff.service1Id=ser.serviceId OR aff.service2Id=ser.serviceId
     * LEFT JOIN `sbm_t_responsables`res ON res.responsableId=aff.responsableId
     * LEFT JOIN `sbm_t_scolarites`sco ON aff.millesime=sco.millesime AND aff.eleveId=sco.eleveId
     * WHERE aff.millesime=2015 AND sco.inscrit=1
     * ) sub1
     * GROUP BY communeId, serviceId
     * ) tmp1 ON tmp1.communeId=com.communeId
     *
     * LEFT JOIN (
     * SELECT serviceId, communeId, count(sub2.eleveId) AS internet
     * FROM (
     * SELECT DISTINCT res.communeId, ser.serviceId, aff.eleveId
     * FROM `sbm_t_services` ser
     * LEFT JOIN `sbm_t_affectations`aff ON aff.service1Id=ser.serviceId OR aff.service2Id=ser.serviceId
     * LEFT JOIN `sbm_t_responsables`res ON res.responsableId=aff.responsableId
     * LEFT JOIN `sbm_t_scolarites`sco ON aff.millesime=sco.millesime AND aff.eleveId=sco.eleveId
     * WHERE aff.millesime=2015 AND sco.inscrit=1 AND sco.internet=1
     * ) sub2
     * GROUP BY communeId, serviceId
     * ) tmp2 ON tmp2.communeId=com.communeId AND tmp2.serviceId=tmp1.serviceId
     *
     * LEFT JOIN (
     * SELECT serviceId, communeId, count(sub3.eleveId) AS papier
     * FROM (
     * SELECT DISTINCT res.communeId, ser.serviceId, aff.eleveId
     * FROM `sbm_t_services` ser
     * LEFT JOIN `sbm_t_affectations`aff ON aff.service1Id=ser.serviceId OR aff.service2Id=ser.serviceId
     * LEFT JOIN `sbm_t_responsables`res ON res.responsableId=aff.responsableId
     * LEFT JOIN `sbm_t_scolarites`sco ON aff.millesime=sco.millesime AND aff.eleveId=sco.eleveId
     * WHERE aff.millesime=2015 AND sco.inscrit=1 AND sco.internet=0
     * ) sub3
     * GROUP BY communeId, serviceId
     * ) tmp3 ON tmp3.communeId=com.communeId AND tmp3.serviceId=tmp1.serviceId
     *
     * WHERE `desservie` = 1
     * ORDER BY com.nom
     *
     * @param int $millesime            
     *
     * @return \Zend\Db\Sql\Select
     */
    private function selectStatistiquesParCommuneCircuit($millesime)
    {
        $result = array();
        $subSelectBase = $this->sql->select([
            's' => $this->tableName['services']
        ])
            ->columns(array(
            'serviceId'
        ))
            ->join([
            'aff' => $this->tableName['affectations']
        ], 'aff.service1Id=s.serviceId OR aff.service2Id=s.serviceId', [
            'eleveId'
        ], Select::JOIN_LEFT)
            ->join([
            'res' => $this->tableName['responsables']
        ], 'res.responsableId=aff.responsableId', [
            'communeId'
        ], Select::JOIN_LEFT)
            ->join([
            'sco' => $this->tableName['scolarites']
        ], 'aff.millesime=sco.millesime AND aff.eleveId=sco.eleveId', [], Select::JOIN_LEFT)
            ->quantifier(Select::QUANTIFIER_DISTINCT);
        // inscrits
        $whereInscrits = new Where();
        $whereInscrits->equalTo('sco.millesime', $millesime)->literal('sco.inscrit = 1');
        $subSelectInscrits = clone $subSelectBase;
        $subSelectInscrits->where($whereInscrits);
        
        // internet
        $whereInternet = clone $whereInscrits;
        $whereInternet->literal('sco.internet = 1');
        $subSelectInternet = clone $subSelectBase;
        $subSelectInternet->where($whereInternet);
        
        // papier
        $wherePapier = clone $whereInscrits;
        $wherePapier->literal('sco.internet = 0');
        $subSelectPapier = clone $subSelectBase;
        $subSelectPapier->where($wherePapier);
        ;
        
        // requête
        $select = $this->sql->select([
            'com' => $this->tableName['communes']
        ])
            ->join([
            'tmp1' => $this->subSelectCommuneCircuitGroup('sub1', 'inscrits', $subSelectInscrits)
        ], 'tmp1.communeId=com.communeId', [], Select::JOIN_LEFT)
            ->join([
            'tmp2' => $this->subSelectCommuneCircuitGroup('sub2', 'internet', $subSelectInternet)
        ], 'tmp2.communeId=com.communeId AND tmp2.serviceId=tmp1.serviceId', [], Select::JOIN_LEFT)
            ->join([
            'tmp3' => $this->subSelectCommuneCircuitGroup('sub3', 'papier', $subSelectPapier)
        ], 'tmp3.communeId=com.communeId AND tmp3.serviceId=tmp1.serviceId', [], Select::JOIN_LEFT)
            ->columns([
            'commune' => 'nom',
            'circuit' => new Expression('COALESCE(tmp1.serviceId, "")'),
            'inscrits' => new Expression('COALESCE(tmp1.inscrits, 0)'),
            'internet' => new Expression('COALESCE(tmp2.internet, 0)'),
            'papier' => new Expression('COALESCE(tmp3.papier, 0)')
        ])
            ->where([
            'desservie' => 1
        ])
            ->order([
            'com.nom',
            'tmp1.serviceId'
        ]);
        // die($this->getSqlString($select));
        return $select;
    }

    private function subSelectCommuneCircuitGroup($alias, $fieldNameCount, Select $sub)
    {
        return $this->sql->select([
            $alias => $sub
        ])
            ->columns([
            'communeId',
            'serviceId',
            $fieldNameCount => new Expression('count(eleveId)')
        ])
            ->group([
            'communeId',
            'serviceId'
        ]);
    }

    /**
     * Renvoie un tableau statistiques pour l'année en cours et pour l'année précédente
     *
     * @return array <br><code>array('annee_courante' => array(), 'annee_precedente' => array())</code><br>
     *         où chaque tableau est composé de lignes de la forme :<br>
     *         <code>array('commune' => nnn, 'circuit' => aaa, 'inscrits' => value, 'internet' => value, 'papier' => value, 'transportes' => value)</code>
     *         <br>où<ul>
     *         <li><b>commune</b> est le nom de la commune</li>
     *         <li><b>circuit</b> est le code du service</li>
     *         <li><b>inscrits</b> est le nombre d'inscrits</li>
     *         <li><b>internet</b> est le nombre d'inscrits par internet</li>
     *         <li><b>papier</b> est le nombre d'inscrits par fiche papier</li>
     *         <li><b>transportes</b> est le nombre d'élève transportés</li></ul>
     */
    public function statistiquesParCircuitCommune()
    {
        $statement = $this->sql->prepareStatementForSqlObject($this->selectStatistiquesParCircuitCommune($this->millesime));
        $result['annee_courante'] = iterator_to_array($statement->execute());
        $statement = $this->sql->prepareStatementForSqlObject($this->selectStatistiquesParCircuitCommune($this->millesime - 1));
        $result['annee_precedente'] = iterator_to_array($statement->execute());
        // die(var_dump($result));
        return $result;
    }

    /**
     * Renvoie une requête SQL de la forme suivante :
     *
     * SELECT ser.serviceId AS circuit, COALESCE(tmp1.commune, '') AS commune, COALESCE(inscrits, 0) AS inscrits, COALESCE(internet, 0) AS internet, COALESCE(papier, 0) AS papier
     * FROM `sbm_t_services` ser
     * LEFT JOIN (
     * SELECT serviceId, commune, count(sub1.eleveId) AS inscrits
     * FROM (
     * SELECT DISTINCT s.serviceId, com.nom AS commune, aff.eleveId
     * FROM `sbm_t_services` s
     * LEFT JOIN `sbm_t_affectations`aff ON aff.service1Id=s.serviceId OR aff.service2Id=s.serviceId
     * LEFT JOIN `sbm_t_responsables`res ON res.responsableId=aff.responsableId
     * LEFT JOIN `sbm_t_communes` com ON res.communeId=com.communeId
     * LEFT JOIN `sbm_t_scolarites`sco ON aff.millesime=sco.millesime AND aff.eleveId=sco.eleveId
     * WHERE aff.millesime=2015 AND sco.inscrit=1
     * ) sub1
     * GROUP BY serviceId, commune
     * ) tmp1 ON tmp1.serviceId=ser.serviceId
     *
     * LEFT JOIN (
     * SELECT serviceId, commune, count(sub2.eleveId) AS internet
     * FROM (
     * SELECT DISTINCT s.serviceId, com.nom AS commune, aff.eleveId
     * FROM `sbm_t_services` s
     * LEFT JOIN `sbm_t_affectations`aff ON aff.service1Id=s.serviceId OR aff.service2Id=s.serviceId
     * LEFT JOIN `sbm_t_responsables`res ON res.responsableId=aff.responsableId
     * LEFT JOIN `sbm_t_communes` com ON res.communeId=com.communeId
     * LEFT JOIN `sbm_t_scolarites`sco ON aff.millesime=sco.millesime AND aff.eleveId=sco.eleveId
     * WHERE aff.millesime=2015 AND sco.inscrit=1 AND sco.internet=1
     * ) sub2
     * GROUP BY serviceId, commune
     * ) tmp2 ON tmp2.serviceId=tmp1.serviceId AND tmp2.commune=tmp1.commune
     *
     * LEFT JOIN (
     * SELECT serviceId, commune, count(sub3.eleveId) AS papier
     * FROM (
     * SELECT DISTINCT s.serviceId, com.nom AS commune, aff.eleveId
     * FROM `sbm_t_services` s
     * LEFT JOIN `sbm_t_affectations`aff ON aff.service1Id=s.serviceId OR aff.service2Id=s.serviceId
     * LEFT JOIN `sbm_t_responsables`res ON res.responsableId=aff.responsableId
     * LEFT JOIN `sbm_t_communes` com ON res.communeId=com.communeId
     * LEFT JOIN `sbm_t_scolarites`sco ON aff.millesime=sco.millesime AND aff.eleveId=sco.eleveId
     * WHERE aff.millesime=2015 AND sco.inscrit=1 AND internet=0
     * ) sub3
     * GROUP BY serviceId, commune
     * ) tmp3 ON tmp3.serviceId=tmp1.serviceId AND tmp3.commune=tmp1.commune
     * ORDER BY ser.serviceId, tmp1.commune
     *
     * où 2015 est remplacé par le millesime donné.
     *
     * @param int $millesime            
     *
     * @return \Zend\Db\Sql\Select
     */
    private function selectStatistiquesParCircuitCommune($millesime)
    {
        $result = array();
        $subSelectBase = $this->sql->select([
            's' => $this->tableName['services']
        ])
            ->columns(array(
            'serviceId'
        ))
            ->join([
            'aff' => $this->tableName['affectations']
        ], 'aff.service1Id=s.serviceId OR aff.service2Id=s.serviceId', [
            'eleveId'
        ], Select::JOIN_LEFT)
            ->join([
            'res' => $this->tableName['responsables']
        ], 'res.responsableId=aff.responsableId', [], Select::JOIN_LEFT)
            ->join([
            'com' => $this->tableName['communes']
        ], 'res.communeId=com.communeId', [
            'commune' => 'nom'
        ], Select::JOIN_LEFT)
            ->join([
            'sco' => $this->tableName['scolarites']
        ], 'aff.millesime=sco.millesime AND aff.eleveId=sco.eleveId', [], Select::JOIN_LEFT)
            ->quantifier(Select::QUANTIFIER_DISTINCT);
        // inscrits
        $whereInscrits = new Where();
        $whereInscrits->equalTo('sco.millesime', $millesime)->literal('sco.inscrit = 1');
        $subSelectInscrits = clone $subSelectBase;
        $subSelectInscrits->where($whereInscrits);
        
        // internet
        $whereInternet = clone $whereInscrits;
        $whereInternet->literal('sco.internet = 1');
        $subSelectInternet = clone $subSelectBase;
        $subSelectInternet->where($whereInternet);
        
        // papier
        $wherePapier = clone $whereInscrits;
        $wherePapier->literal('sco.internet = 0');
        $subSelectPapier = clone $subSelectBase;
        $subSelectPapier->where($wherePapier);
        
        // requête
        $select = $this->sql->select([
            'ser' => $this->tableName['services']
        ])
            ->join([
            'tmp1' => $this->subSelectCircuitCommuneGroup('sub1', 'inscrits', $subSelectInscrits)
        ], 'tmp1.serviceId=ser.serviceId', [], Select::JOIN_LEFT)
            ->join([
            'tmp2' => $this->subSelectCircuitCommuneGroup('sub2', 'internet', $subSelectInternet)
        ], 'tmp2.serviceId=tmp1.serviceId AND tmp2.commune=tmp1.commune', [], Select::JOIN_LEFT)
            ->join([
            'tmp3' => $this->subSelectCircuitCommuneGroup('sub3', 'papier', $subSelectPapier)
        ], 'tmp3.serviceId=tmp1.serviceId AND tmp3.commune=tmp1.commune', [], Select::JOIN_LEFT)
            ->columns([
            'circuit' => 'serviceId',
            'commune' => new Expression('COALESCE(tmp1.commune, "")'),
            'inscrits' => new Expression('COALESCE(inscrits, 0)'),
            'internet' => new Expression('COALESCE(internet, 0)'),
            'papier' => new Expression('COALESCE(papier, 0)')
        ])
            ->order([
            'ser.serviceId',
            'tmp1.commune'
        ]);
        // die($this->getSqlString($select));
        return $select;
    }

    private function subSelectCircuitCommuneGroup($alias, $fieldNameCount, Select $sub)
    {
        return $this->sql->select([
            $alias => $sub
        ])
            ->columns([
            'serviceId',
            'commune',
            $fieldNameCount => new Expression('count(eleveId)')
        ])
            ->group([
            'serviceId',
            'commune'
        ]);
    }

    /**
     * Renvoie un tableau statistiques pour l'année en cours et pour l'année précédente
     *
     * @return array <br><code>array('annee_courante' => array(), 'annee_precedente' => array())</code><br>
     *         où chaque tableau est composé de lignes de la forme :<br>
     *         <code>array('etablissementId' => id, 'etablissement' =>nnn, 'commune' => vvv, 'classe' => ccc, 'inscrits' => value, 'internet' => value, 'papier' => value, 'transportes' => value)</code>
     *         <br>où<ul>
     *         <li><b>etablissementId</b> est l'id de l'établissement</li>
     *         <li><b>etablissement</b> est le nom de l'établissement</li>
     *         <li><b>commune</b> est la commune de l'établissement</li>
     *         <li><b>classe</b> est le nom de la classe</li>
     *         <li><b>inscrits</b> est le nombre d'inscrits</li>
     *         <li><b>internet</b> est le nombre d'inscrits par internet</li>
     *         <li><b>papier</b> est le nombre d'inscrits par fiche papier</li>
     *         <li><b>transportes</b> est le nombre d'élève transportés</li></ul>
     *        
     * @return array
     */
    public function statistiquesParEtablissementClasse()
    {
        $statement = $this->sql->prepareStatementForSqlObject($this->selectStatistiquesParEtablissementClasse($this->millesime));
        $annee_courante = iterator_to_array($statement->execute());
        $statement = $this->sql->prepareStatementForSqlObject($this->selectStatistiquesParEtablissementClasse($this->millesime - 1));
        $annee_precedente = iterator_to_array($statement->execute());
        // résultat par alignement des tableaux
        $result = array();
        for ($etablissementId = '', $ic = 0, $ip = 0; $ic < count($annee_courante) || $ip < count($annee_precedente);) {
            if (empty($etablissementId)) {
                // initialisation à l'entrée d'un établissement
                if (isset($annee_courante[$ic]['etablissementId'])) {
                    $etablissementId = $annee_courante[$ic]['etablissementId'];
                } else {
                    $etablissementId = $annee_precedente[$ip]['etablissementId'];
                }
                /*
                 * $total = [
                 * 'annee_courante' => [
                 * 'etablissement' => '',
                 * 'commune' => 'TOTAL',
                 * 'classe' => '',
                 * 'inscrits' => 0,
                 * 'internet' => 0,
                 * 'papier' => 0,
                 * 'transportes' => 0
                 * ],
                 * 'annee_precedente' => [
                 * 'etablissement' => '',
                 * 'commune' => 'TOTAL',
                 * 'classe' => '',
                 * 'inscrits' => 0,
                 * 'internet' => 0,
                 * 'papier' => 0,
                 * 'transportes' => 0
                 * ]
                 * ];
                 */
            }
            if (isset($annee_courante[$ic]['etablissementId']) && $etablissementId == $annee_courante[$ic]['etablissementId']) {
                if (isset($annee_precedente[$ip]['etablissementId']) && $etablissementId == $annee_precedente[$ip]['etablissementId']) {
                    // même établissement pour les 2 tableaux
                    if ($annee_courante[$ic]['classe'] == $annee_precedente[$ip]['classe']) {
                        // même classe pour les 2 tableaux
                        // $total['annee_courante']['inscrits'] = $annee_courante[$ic]['inscrits'];
                        $result['annee_courante'][] = $annee_courante[$ic ++];
                        // $total['annee_precedente']['inscrits'] = $annee_precedente[$ip]['inscrits'];
                        $result['annee_precedente'][] = $annee_precedente[$ip ++];
                    } elseif ($annee_courante[$ic]['classe'] == '') {
                        // pas de classe dans l'année courante
                        $ic ++;
                        // $result['annee_precedente'][] = $annee_precedente[$ip++];
                    } elseif ($annee_precedente[$ip]['classe'] == '') {
                        // pas de classe dans l'année précédente
                        $ip ++;
                        // $result['annee_courante'][] = $annee_courante[$ic++];
                    } elseif ($annee_courante[$ic]['classe'] < $annee_precedente[$ip]['classe']) {
                        $result['annee_precedente'][] = $annee_precedente[$ip ++];
                    } else {
                        $result['annee_courante'][] = $annee_courante[$ic ++];
                    }
                } else {
                    // l'établissement de l'année précédente a changé
                    // il faut finir l'établissement de l'année en cours
                    $row = $annee_courante[$ic ++];
                    $result['annee_courante'][] = $row;
                    $result['annee_precedente'][] = array_merge($row, [
                        'inscrits' => 0,
                        'internet' => 0,
                        'papier' => 0,
                        'transportes' => 0
                    ]);
                    // $total['annee_courante']['inscrits'] += $row['inscrits'];
                }
            } else {
                if (isset($annee_precedente[$ip]['etablissementId']) && $etablissementId == $annee_precedente[$ip]['etablissementId']) {
                    // il faut finir l'établissement de l'année précédente
                    $row = $annee_precedente[$ip ++];
                    $result['annee_precedente'][] = $row;
                    $result['annee_courante'][] = array_merge($row, [
                        'incrits' => 0,
                        'internet' => 0,
                        'papier' => 0,
                        'transportes' => 0
                    ]);
                    // $total['annee_precedente']['inscrits'] += $row['inscrits'];
                } else {
                    // $etablissementId n'est plus à jour
                    $etablissementId = '';
                    // $result['annee_precedente'][] = $total['annee_precedente'];
                    // $result['annee_courante'][] = $total['annee_courante'];
                }
            }
        }
        return $result;
    }

    /**
     * Renvoie une requête de la forme :
     *
     * SELECT eta.etablissementId, eta.nom AS etablissement, com.nom AS commune, COALESCE(cla.nom, '') AS classe, COALESCE(tmp1.inscrits, 0) AS inscrits, COALESCE(tmp2.internet, 0) AS internet, COALESCE(tmp3.papier,0) AS papier, COALESCE(tmp4.transportes, 0) AS transportes
     * FROM `sbm_t_etablissements` eta
     * JOIN `sbm_t_communes` com ON eta.communeId=com.communeId
     *
     * LEFT JOIN (
     * SELECT sub1.etablissementId, sub1.classeId, count(sub1.eleveId) AS inscrits
     * FROM `sbm_t_scolarites` sub1
     * WHERE sub1.millesime=2015 AND sub1.inscrit=1
     * GROUP BY etablissementId, classeId
     * ) tmp1 ON tmp1.etablissementId=eta.etablissementId
     *
     * LEFT JOIN (
     * SELECT sub2.etablissementId, sub2.classeId, count(sub2.eleveId) AS internet
     * FROM `sbm_t_scolarites` sub2
     * WHERE sub2.millesime=2015 AND sub2.inscrit=1 AND sub2.internet=1
     * GROUP BY etablissementId, classeId
     * ) tmp2 ON tmp2.etablissementId=eta.etablissementId AND tmp2.classeId=tmp1.classeId
     *
     * LEFT JOIN (
     * SELECT sub3.etablissementId, sub3.classeId, count(sub3.eleveId) AS papier
     * FROM `sbm_t_scolarites` sub3
     * WHERE sub3.millesime=2015 AND sub3.inscrit=1 AND sub3.internet=0
     * GROUP BY etablissementId, classeId
     * ) tmp3 ON tmp3.etablissementId=eta.etablissementId AND tmp3.classeId=tmp1.classeId
     *
     * LEFT JOIN (
     * SELECT sub4.etablissementId, sub4.classeId, count(sub4.eleveId) AS transportes
     * FROM (
     * SELECT DISTINCT sco.etablissementId, sco.classeId, sco.eleveId
     * FROM `sbm_t_scolarites` sco
     * JOIN `sbm_t_affectations`aff ON aff.millesime=sco.millesime AND aff.eleveId=sco.eleveId
     * WHERE aff.millesime=2015 AND sco.inscrit=1
     * ) sub4
     * GROUP BY etablissementId, classeId
     * ) tmp4 ON tmp4.etablissementId=eta.etablissementId AND tmp4.classeId=tmp1.classeId
     *
     * LEFT JOIN `sbm_t_classes` cla ON tmp1.classeId=cla.classeId
     * WHERE eta.desservie = 1
     * ORDER BY com.nom, eta.niveau, eta.nom, cla.nom DESC
     *
     * @param int $millesime            
     *
     * @return \Zend\Db\Sql\Select
     */
    private function selectStatistiquesParEtablissementClasse($millesime)
    {
        // subInscrits
        $subInscrits = $this->sql->select([
            'sub1' => $this->tableName['scolarites']
        ])
            ->columns([
            'etablissementId',
            'classeId',
            'inscrits' => new Expression('count(sub1.eleveId)')
        ])
            ->where([
            'sub1.millesime' => $millesime,
            'inscrit' => 1
        ])
            ->group([
            'etablissementId',
            'classeId'
        ]);
        
        // subInternet
        $subInternet = $this->sql->select([
            'sub2' => $this->tableName['scolarites']
        ])
            ->columns([
            'etablissementId',
            'classeId',
            'internet' => new Expression('count(sub2.eleveId)')
        ])
            ->where([
            'sub2.millesime' => $millesime,
            'inscrit' => 1,
            'internet' => 1
        ])
            ->group([
            'etablissementId',
            'classeId'
        ]);
        
        // subPapier
        $subPapier = $this->sql->select([
            'sub3' => $this->tableName['scolarites']
        ])
            ->columns([
            'etablissementId',
            'classeId',
            'papier' => new Expression('count(sub3.eleveId)')
        ])
            ->where([
            'sub3.millesime' => $millesime,
            'inscrit' => 1,
            'internet' => 0
        ])
            ->group([
            'etablissementId',
            'classeId'
        ]);
        
        // subTransportes
        $subTransportes1 = $this->sql->select([
            'sco' => $this->tableName['scolarites']
        ])
            ->join([
            'aff' => $this->tableName['affectations']
        ], 'aff.millesime=sco.millesime AND aff.eleveId=sco.eleveId', [])
            ->columns([
            'etablissementId',
            'classeId',
            'eleveId'
        ])
            ->quantifier(Select::QUANTIFIER_DISTINCT)
            ->where([
            'sco.millesime' => $millesime,
            'sco.inscrit' => 1
        ]);
        
        $subTransportes = $this->sql->select([
            'sub4' => $subTransportes1
        ])
            ->columns([
            'etablissementId',
            'classeId',
            'transportes' => new Expression('count(sub4.eleveId)')
        ])
            ->group([
            'etablissementId',
            'classeId'
        ]);
        
        // construction de la requête
        $select = $this->sql->select([
            'eta' => $this->tableName['etablissements']
        ])
            ->join([
            'com' => $this->tableName['communes']
        ], 'eta.communeId=com.communeId', [
            'commune' => 'nom'
        ])
            ->join([
            'tmp1' => $subInscrits
        ], 'tmp1.etablissementId=eta.etablissementId', [], Select::JOIN_LEFT)
            ->join([
            'tmp2' => $subInternet
        ], 'tmp2.etablissementId=eta.etablissementId AND tmp2.classeId=tmp1.classeId', [], Select::JOIN_LEFT)
            ->join([
            'tmp3' => $subPapier
        ], 'tmp3.etablissementId=eta.etablissementId AND tmp3.classeId=tmp1.classeId', [], Select::JOIN_LEFT)
            ->join([
            'tmp4' => $subTransportes
        ], 'tmp4.etablissementId=eta.etablissementId AND tmp4.classeId=tmp1.classeId', [], Select::JOIN_LEFT)
            ->join([
            'cla' => $this->tableName['classes']
        ], 'tmp1.classeId=cla.classeId', [], Select::JOIN_LEFT)
            ->columns([
            'etablissementId',
            'etablissement' => 'nom',
            'classe' => new Expression("COALESCE(cla.nom, '')"),
            'inscrits' => new Expression("COALESCE(tmp1.inscrits, 0)"),
            'internet' => new Expression("COALESCE(tmp2.internet, 0)"),
            'papier' => new Expression("COALESCE(tmp3.papier,0)"),
            'transportes' => new Expression("COALESCE(tmp4.transportes, 0)")
        ])
            ->where([
            'eta.desservie' => 1
        ])
            ->order([
            'com.nom',
            'eta.niveau',
            'eta.nom',
            'cla.nom DESC'
        ]);
        return $select;
    }

    /**
     * Renvoie un tableau statistiques pour l'année en cours et pour l'année précédente
     *
     * @return array <br><code>array('annee_courante' => array(), 'annee_precedente' => array())</code><br>
     *         où chaque tableau est composé de lignes de la forme :<br>
     *         <code>array('etablissementId' => id, 'etablissement' =>nnn, 'commune' => vvv, 'classe' => ccc, 'inscrits' => value, 'internet' => value, 'papier' => value, 'transportes' => value)</code>
     *         <br>où<ul>
     *         <li><b>classe</b> est le nom de la classe</li>
     *         <li><b>etablissementId</b> est l'id de l'établissement</li>
     *         <li><b>etablissement</b> est le nom de l'établissement</li>
     *         <li><b>commune</b> est la commune de l'établissement</li>
     *         <li><b>inscrits</b> est le nombre d'inscrits</li>
     *         <li><b>internet</b> est le nombre d'inscrits par internet</li>
     *         <li><b>papier</b> est le nombre d'inscrits par fiche papier</li>
     *         <li><b>transportes</b> est le nombre d'élève transportés</li></ul>
     *        
     * @return array
     */
    public function statistiquesParClasseEtablissement()
    {
        $statement = $this->sql->prepareStatementForSqlObject($this->selectStatistiquesParClasseEtablissement($this->millesime));
        $annee_courante = iterator_to_array($statement->execute());
        $statement = $this->sql->prepareStatementForSqlObject($this->selectStatistiquesParClasseEtablissement($this->millesime - 1));
        $annee_precedente = iterator_to_array($statement->execute());
        // résultat par alignement des tableaux
        $result = array();
        for ($classe = '', $ic = 0, $ip = 0; $ic < count($annee_courante) || $ip < count($annee_precedente);) {
            if (empty($classe)) {
                // initialisation à l'entrée d'une classe
                if (isset($annee_courante[$ic]['classe'])) {
                    $classe = $annee_courante[$ic]['classe'];
                } else {
                    $classe = $annee_precedente[$ip]['classe'];
                }
                /*
                 * $total = [
                 * 'annee_courante' => [
                 * 'classe' => '',
                 * 'etablissement' => '',
                 * 'commune' => 'TOTAL',
                 * 'inscrits' => 0,
                 * 'internet' => 0,
                 * 'papier' => 0,
                 * 'transportes' => 0
                 * ],
                 * 'annee_precedente' => [
                 * 'classe' => '',
                 * 'etablissement' => '',
                 * 'commune' => 'TOTAL',
                 * 'inscrits' => 0,
                 * 'internet' => 0,
                 * 'papier' => 0,
                 * 'transportes' => 0
                 * ]
                 * ];
                 */
            }
            if (isset($annee_courante[$ic]['classe']) && $classe == $annee_courante[$ic]['classe']) {
                if (isset($annee_precedente[$ip]['classe']) && $classe == $annee_precedente[$ip]['classe']) {
                    // même classe pour les 2 tableaux
                    if ($annee_courante[$ic]['etablissementId'] == $annee_precedente[$ip]['etablissementId']) {
                        // même établissement pour les 2 tableaux
                        // $total['annee_courante']['inscrits'] += $annee_courante[$ic]['inscrits'];
                        $result['annee_courante'][] = $annee_courante[$ic ++];
                        // $total['annee_precedente']['inscrits'] += $annee_precedente[$ip]['inscrits'];
                        $result['annee_precedente'][] = $annee_precedente[$ip ++];
                    } elseif ($annee_courante[$ic]['etablissementId'] == '') {
                        // pas d'établissement dans l'année courante
                        $ic ++;
                        // $result['annee_precedente'][] = $annee_precedente[$ip++];
                    } elseif ($annee_precedente[$ip]['etablissementId'] == '') {
                        // pas d'établissement dans l'année précédente
                        $ip ++;
                        // $result['annee_courante'][] = $annee_courante[$ic++];
                    } elseif ($annee_courante[$ic]['etablissementId'] < $annee_precedente[$ip]['etablissementId']) {
                        $result['annee_precedente'][] = $annee_precedente[$ip ++];
                    } else {
                        $result['annee_courante'][] = $annee_courante[$ic ++];
                    }
                } else {
                    // la classe de l'année précédente a changé
                    // il faut finir la classe de l'année en cours
                    $row = $annee_courante[$ic ++];
                    $result['annee_courante'][] = $row;
                    $result['annee_precedente'][] = array_merge($row, [
                        'inscrits' => 0,
                        'internet' => 0,
                        'papier' => 0,
                        'transportes' => 0
                    ]);
                    // $total['annee_courante']['inscrits'] += $row['inscrits'];
                }
            } else {
                if (isset($annee_precedente[$ip]['classe']) && $classe == $annee_precedente[$ip]['classe']) {
                    // il faut finir la classe de l'année précédente
                    $row = $annee_precedente[$ip ++];
                    $result['annee_precedente'][] = $row;
                    $result['annee_courante'][] = array_merge($row, [
                        'incrits' => 0,
                        'internet' => 0,
                        'papier' => 0,
                        'transportes' => 0
                    ]);
                    // $total['annee_precedente']['inscrits'] += $row['inscrits'];
                } else {
                    // $classe n'est plus à jour
                    $classe = '';
                    // $result['annee_precedente'][] = $total['annee_precedente'];
                    // $result['annee_courante'][] = $total['annee_courante'];
                }
            }
        }
        return $result;
    }

    /**
     * Renvoie une requête de la forme :
     *
     * SELECT cla.nom AS classe, COALESCE(eta.etablissementId, '') AS etablissementId, COALESCE(eta.nom, '') AS etablissement, COALESCE(com.nom, '') AS commune, COALESCE(tmp1.inscrits, 0) AS inscrits, COALESCE(tmp2.internet, 0) AS internet, COALESCE(tmp3.papier,0) AS papier, COALESCE(tmp4.transportes, 0) AS transportes
     * FROM `sbm_t_classes` cla
     *
     * LEFT JOIN (
     * SELECT sub1.classeId, sub1.etablissementId, count(sub1.eleveId) AS inscrits
     * FROM `sbm_t_scolarites` sub1
     * WHERE sub1.millesime=2015 AND sub1.inscrit=1
     * GROUP BY classeId, etablissementId
     * ) tmp1 ON tmp1.classeId=cla.classeId
     *
     * LEFT JOIN (
     * SELECT sub2.classeId, sub2.etablissementId, count(sub2.eleveId) AS internet
     * FROM `sbm_t_scolarites` sub2
     * WHERE sub2.millesime=2015 AND sub2.inscrit=1 AND sub2.internet=1
     * GROUP BY classeId, etablissementId
     * ) tmp2 ON tmp2.classeId=cla.classeId AND tmp2.etablissementId=tmp1.etablissementId
     *
     * LEFT JOIN (
     * SELECT sub3.classeId, sub3.etablissementId, count(sub3.eleveId) AS papier
     * FROM `sbm_t_scolarites` sub3
     * WHERE sub3.millesime=2015 AND sub3.inscrit=1 AND sub3.internet=0
     * GROUP BY classeId, etablissementId
     * ) tmp3 ON tmp3.classeId=cla.classeId AND tmp3.etablissementId=tmp1.etablissementId
     *
     * LEFT JOIN (
     * SELECT sub4.etablissementId, sub4.classeId, count(sub4.eleveId) AS transportes
     * FROM (
     * SELECT DISTINCT sco.etablissementId, sco.classeId, sco.eleveId
     * FROM `sbm_t_scolarites` sco
     * JOIN `sbm_t_affectations`aff ON aff.millesime=sco.millesime AND aff.eleveId=sco.eleveId
     * WHERE aff.millesime=2015 AND sco.inscrit=1
     * ) sub4
     * GROUP BY classeId, etablissementId
     * ) tmp4 ON tmp4.classeId=cla.classeId AND tmp4.etablissementId=tmp1.etablissementId
     *
     * LEFT JOIN `sbm_t_etablissements` eta ON tmp1.etablissementId=eta.etablissementId
     *
     * LEFT JOIN `sbm_t_communes` com ON eta.communeId=com.communeId
     *
     * WHERE eta.desservie = 1
     * ORDER BY cla.niveau, cla.nom DESC, com.nom, eta.nom
     *
     * @param int $millesime            
     *
     * @return \Zend\Db\Sql\Select
     */
    private function selectStatistiquesParClasseEtablissement($millesime)
    {
        // subInscrits
        $subInscrits = $this->sql->select([
            'sub1' => $this->tableName['scolarites']
        ])
            ->columns([
            'classeId',
            'etablissementId',
            'inscrits' => new Expression('count(sub1.eleveId)')
        ])
            ->where([
            'sub1.millesime' => $millesime,
            'inscrit' => 1
        ])
            ->group([
            'classeId',
            'etablissementId'
        ]);
        
        // subInternet
        $subInternet = $this->sql->select([
            'sub2' => $this->tableName['scolarites']
        ])
            ->columns([
            'classeId',
            'etablissementId',
            'internet' => new Expression('count(sub2.eleveId)')
        ])
            ->where([
            'sub2.millesime' => $millesime,
            'inscrit' => 1,
            'internet' => 1
        ])
            ->group([
            'classeId',
            'etablissementId'
        ]);
        
        // subPapier
        $subPapier = $this->sql->select([
            'sub3' => $this->tableName['scolarites']
        ])
            ->columns([
            'classeId',
            'etablissementId',
            'papier' => new Expression('count(sub3.eleveId)')
        ])
            ->where([
            'sub3.millesime' => $millesime,
            'inscrit' => 1,
            'internet' => 0
        ])
            ->group([
            'classeId',
            'etablissementId'
        ]);
        
        // subTransportes
        $subTransportes1 = $this->sql->select([
            'sco' => $this->tableName['scolarites']
        ])
            ->join([
            'aff' => $this->tableName['affectations']
        ], 'aff.millesime=sco.millesime AND aff.eleveId=sco.eleveId', [])
            ->columns([
            'etablissementId',
            'classeId',
            'eleveId'
        ])
            ->quantifier(Select::QUANTIFIER_DISTINCT)
            ->where([
            'sco.millesime' => $millesime,
            'sco.inscrit' => 1
        ]);
        
        $subTransportes = $this->sql->select([
            'sub4' => $subTransportes1
        ])
            ->columns([
            'classeId',
            'etablissementId',
            'transportes' => new Expression('count(sub4.eleveId)')
        ])
            ->group([
            'classeId',
            'etablissementId'
        ]);
        
        // construction de la requête
        $select = $this->sql->select([
            'cla' => $this->tableName['classes']
        ])
            ->join([
            'tmp1' => $subInscrits
        ], 'tmp1.classeId=cla.classeId', [], Select::JOIN_LEFT)
            ->join([
            'tmp2' => $subInternet
        ], 'tmp2.classeId=cla.classeId AND tmp2.etablissementId=tmp1.etablissementId', [], Select::JOIN_LEFT)
            ->join([
            'tmp3' => $subPapier
        ], 'tmp3.classeId=cla.classeId AND tmp3.etablissementId=tmp1.etablissementId', [], Select::JOIN_LEFT)
            ->join([
            'tmp4' => $subTransportes
        ], 'tmp4.classeId=cla.classeId AND tmp4.etablissementId=tmp1.etablissementId', [], Select::JOIN_LEFT)
            ->join([
            'eta' => $this->tableName['etablissements']
        ], 'tmp1.etablissementId=eta.etablissementId', [], Select::JOIN_LEFT)
            ->join([
            'com' => $this->tableName['communes']
        ], 'eta.communeId=com.communeId', [
            'commune' => 'nom'
        ], Select::JOIN_LEFT)
            ->columns([
            'classe' => 'nom',
            'etablissementId' => new Expression("COALESCE(eta.etablissementId, '')"),
            'etablissement' => new Expression("COALESCE(eta.nom, '')"),
            'commune' => new Expression("COALESCE(com.nom, '')"),
            'inscrits' => new Expression("COALESCE(tmp1.inscrits, 0)"),
            'internet' => new Expression("COALESCE(tmp2.internet, 0)"),
            'papier' => new Expression("COALESCE(tmp3.papier,0)"),
            'transportes' => new Expression("COALESCE(tmp4.transportes, 0)")
        ])
            ->where([
            'eta.desservie' => 1
        ])
            ->order([
            'cla.niveau',
            'cla.nom DESC',
            'com.nom',
            'eta.nom'
        ]);
        return $select;
    }
}